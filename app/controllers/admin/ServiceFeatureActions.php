<?php

trait ServiceFeatureActions {
    // SERVICE FEATURE MANAGEMENT
    public function serviceFeatures() {
        $this->userModel->requireAuth();

        $services = $this->serviceModel->all('sort_order ASC, title ASC');
        $formattedServices = array_map([$this, 'formatServiceForFeatureManager'], $services);

        $initialService = $formattedServices[0] ?? null;
        $initialServiceId = (int)($initialService['id'] ?? 0);

        $initialFeatures = $initialServiceId > 0
            ? $this->serviceFeatureModel->getByService($initialServiceId, true)
            : [];

        $normalizedFeatures = array_map(function (array $feature) use ($initialService) {
            return $this->formatServiceFeatureForManager($feature, $initialService ?? []);
        }, $initialFeatures);

        $routes = [
            'fetch' => url('admin/services/features/list'),
            'store' => url('admin/services/features'),
            'update_template' => url('admin/services/features/__ID__/update'),
            'delete_template' => url('admin/services/features/__ID__/delete'),
            'toggle_template' => url('admin/services/features/__ID__/toggle'),
            'reorder' => url('admin/services/features/reorder')
        ];

        $data = [
            'title' => 'Service Features - Admin',
            'page' => 'admin-service-features',
            'services' => $formattedServices,
            'initial_service_id' => $initialServiceId,
            'services_json' => htmlspecialchars(json_encode($formattedServices, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8'),
            'features_json' => htmlspecialchars(json_encode($normalizedFeatures, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8'),
            'feature_routes' => $routes
        ];

        $this->render('admin/service-features', 'admin', $data);
    }

    public function fetchServiceFeatures() {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Invalid request method.'
            ], 405);
            return;
        }

        $serviceId = (int)($_POST['service_id'] ?? 0);
        if ($serviceId <= 0) {
            $this->json([
                'success' => false,
                'message' => 'Select a service before managing features.'
            ], 422);
            return;
        }

        $service = $this->serviceModel->find($serviceId);
        if (!$service) {
            $this->json([
                'success' => false,
                'message' => 'Service not found.'
            ], 404);
            return;
        }

        $features = $this->serviceFeatureModel->getByService($serviceId, true);
        $formattedFeatures = array_map(function (array $feature) use ($service) {
            return $this->formatServiceFeatureForManager($feature, $this->formatServiceForFeatureManager($service));
        }, $features);

        $this->json([
            'success' => true,
            'service' => $this->formatServiceForFeatureManager($service),
            'features' => $formattedFeatures
        ]);
    }

    public function storeServiceFeature() {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Invalid request method.'
            ], 405);
            return;
        }

        $serviceId = (int)($_POST['service_id'] ?? 0);
        if ($serviceId <= 0) {
            $this->json([
                'success' => false,
                'message' => 'A service reference is required.'
            ], 422);
            return;
        }

        $service = $this->serviceModel->find($serviceId);
        if (!$service) {
            $this->json([
                'success' => false,
                'message' => 'Service not found.'
            ], 404);
            return;
        }

        $input = $this->sanitizeServiceFeatureInput($_POST);
        $errors = $this->validateServiceFeatureInput($input);

        if (!empty($errors)) {
            $this->json([
                'success' => false,
                'message' => 'Please fix the highlighted fields.',
                'errors' => $errors
            ], 422);
            return;
        }

        $existing = $this->serviceFeatureModel->getByService($serviceId, true);
        $requestedOrder = isset($input['sort_order']) ? (int)$input['sort_order'] : null;
        $desiredPosition = $requestedOrder && $requestedOrder > 0 ? $requestedOrder : count($existing) + 1;

        $payload = [
            'service_id' => $serviceId,
            'feature_text' => $input['feature_text'],
            'icon_class' => $input['icon_class'] !== '' ? $input['icon_class'] : null,
            'display' => $input['display'] ? 1 : 0,
            'sort_order' => count($existing) + 1
        ];

        try {
            $featureId = (int)$this->serviceFeatureModel->create($payload);

            if ($desiredPosition !== count($existing) + 1) {
                $orderedIds = array_column($existing, 'id');
                $desiredPosition = max(1, min($desiredPosition, count($existing) + 1));
                array_splice($orderedIds, $desiredPosition - 1, 0, $featureId);
                $this->serviceFeatureModel->reorderForService($serviceId, $orderedIds);
            }
        } catch (Exception $exception) {
            error_log('Failed to create service feature: ' . $exception->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Unable to add the feature. Please try again.'
            ], 500);
            return;
        }

        $record = $this->serviceFeatureModel->find($featureId);
        $formatted = $this->formatServiceFeatureForManager($record, $this->formatServiceForFeatureManager($service));

        $this->json([
            'success' => true,
            'message' => 'Feature added successfully.',
            'feature' => $formatted
        ]);
    }

    public function updateServiceFeature($id) {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Invalid request method.'
            ], 405);
            return;
        }

        $featureId = (int)$id;
        if ($featureId <= 0) {
            $this->json([
                'success' => false,
                'message' => 'Feature not found.'
            ], 404);
            return;
        }

        $existing = $this->serviceFeatureModel->find($featureId);
        if (!$existing) {
            $this->json([
                'success' => false,
                'message' => 'Feature not found.'
            ], 404);
            return;
        }

        $serviceId = (int)($existing['service_id'] ?? 0);
        $service = $serviceId > 0 ? $this->serviceModel->find($serviceId) : null;
        if (!$service) {
            $this->json([
                'success' => false,
                'message' => 'Service not found.'
            ], 404);
            return;
        }

        $input = $this->sanitizeServiceFeatureInput($_POST, $existing);
        $errors = $this->validateServiceFeatureInput($input);

        if (!empty($errors)) {
            $this->json([
                'success' => false,
                'message' => 'Please fix the highlighted fields.',
                'errors' => $errors
            ], 422);
            return;
        }

        $payload = [
            'feature_text' => $input['feature_text'],
            'icon_class' => $input['icon_class'] !== '' ? $input['icon_class'] : null,
            'display' => $input['display'] ? 1 : 0
        ];

        if (isset($input['sort_order']) && $input['sort_order'] > 0) {
            $payload['sort_order'] = (int)$input['sort_order'];
        }

        try {
            $this->serviceFeatureModel->update($featureId, $payload);

            if (isset($payload['sort_order'])) {
                $all = $this->serviceFeatureModel->getByService($serviceId, true);
                $orderedIds = array_column($all, 'id');

                $currentIndex = array_search($featureId, $orderedIds, true);
                if ($currentIndex !== false) {
                    array_splice($orderedIds, $currentIndex, 1);
                }

                $desiredPosition = max(1, min((int)$payload['sort_order'], count($orderedIds) + 1));
                array_splice($orderedIds, $desiredPosition - 1, 0, $featureId);
                $this->serviceFeatureModel->reorderForService($serviceId, $orderedIds);
            }
        } catch (Exception $exception) {
            error_log('Failed to update service feature: ' . $exception->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Unable to update the feature. Please try again.'
            ], 500);
            return;
        }

        $record = $this->serviceFeatureModel->find($featureId);
        $formatted = $this->formatServiceFeatureForManager($record, $this->formatServiceForFeatureManager($service));

        $this->json([
            'success' => true,
            'message' => 'Feature updated successfully.',
            'feature' => $formatted
        ]);
    }

    public function deleteServiceFeature($id) {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Invalid request method.'
            ], 405);
            return;
        }

        $featureId = (int)$id;
        if ($featureId <= 0) {
            $this->json([
                'success' => false,
                'message' => 'Feature not found.'
            ], 404);
            return;
        }

        $existing = $this->serviceFeatureModel->find($featureId);
        if (!$existing) {
            $this->json([
                'success' => false,
                'message' => 'Feature not found.'
            ], 404);
            return;
        }

        try {
            $deleted = $this->serviceFeatureModel->delete($featureId);
        } catch (Exception $exception) {
            error_log('Failed to delete service feature: ' . $exception->getMessage());
            $deleted = false;
        }

        if (!$deleted) {
            $this->json([
                'success' => false,
                'message' => 'Unable to remove the feature. Please try again.'
            ], 500);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'Feature removed.'
        ]);
    }

    public function toggleServiceFeature($id) {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Invalid request method.'
            ], 405);
            return;
        }

        $featureId = (int)$id;
        if ($featureId <= 0) {
            $this->json([
                'success' => false,
                'message' => 'Feature not found.'
            ], 404);
            return;
        }

        $existing = $this->serviceFeatureModel->find($featureId);
        if (!$existing) {
            $this->json([
                'success' => false,
                'message' => 'Feature not found.'
            ], 404);
            return;
        }

        $display = isset($_POST['display']) ? (bool)$_POST['display'] : !(bool)($existing['display'] ?? 1);

        try {
            $this->serviceFeatureModel->toggleDisplay($featureId, $display);
        } catch (Exception $exception) {
            error_log('Failed to toggle feature visibility: ' . $exception->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Unable to update visibility. Please try again.'
            ], 500);
            return;
        }

        $record = $this->serviceFeatureModel->find($featureId);
        $service = $this->serviceModel->find((int)($existing['service_id'] ?? 0));

        $this->json([
            'success' => true,
            'message' => $display ? 'Feature is now visible.' : 'Feature hidden from the site.',
            'feature' => $this->formatServiceFeatureForManager($record ?? [], $this->formatServiceForFeatureManager($service ?? []))
        ]);
    }

    public function reorderServiceFeatures() {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Invalid request method.'
            ], 405);
            return;
        }

        $serviceId = (int)($_POST['service_id'] ?? 0);
        $orderPayload = $_POST['order'] ?? '[]';

        if ($serviceId <= 0) {
            $this->json([
                'success' => false,
                'message' => 'Service reference missing.'
            ], 422);
            return;
        }

        $service = $this->serviceModel->find($serviceId);
        if (!$service) {
            $this->json([
                'success' => false,
                'message' => 'Service not found.'
            ], 404);
            return;
        }

        if (!is_array($orderPayload)) {
            $decoded = json_decode((string)$orderPayload, true);
            $orderPayload = is_array($decoded) ? $decoded : [];
        }

        $orderedIds = array_values(array_filter(array_map('intval', $orderPayload), function ($value) {
            return $value > 0;
        }));

        if (empty($orderedIds)) {
            $this->json([
                'success' => false,
                'message' => 'Provide a valid order payload.'
            ], 422);
            return;
        }

        try {
            $this->serviceFeatureModel->reorderForService($serviceId, $orderedIds);
        } catch (Exception $exception) {
            error_log('Failed to reorder service features: ' . $exception->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Unable to reorder features right now.'
            ], 500);
            return;
        }

        $features = $this->serviceFeatureModel->getByService($serviceId, true);
        $formatted = array_map(function (array $feature) use ($service) {
            return $this->formatServiceFeatureForManager($feature, $this->formatServiceForFeatureManager($service));
        }, $features);

        $this->json([
            'success' => true,
            'message' => 'Feature order updated.',
            'features' => $formatted
        ]);
    }
    
    public function createService() {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $serviceData = [
                'title' => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'icon' => trim($_POST['icon'] ?? ''),
                'features' => json_encode($this->parseTextareaList($_POST['features'] ?? '')),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'sort_order' => (int)($_POST['sort_order'] ?? 0)
            ];
            
            if ($this->serviceModel->create($serviceData)) {
                $this->setFlash('success', 'Service created successfully!');
            } else {
                $this->setFlash('error', 'Failed to create service.');
            }
            
            $this->redirect('admin/services');
            return;
        }
        
        $data = [
            'title' => 'Create Service - Admin',
            'page' => 'admin-service-create'
        ];
        
        $this->render('admin/service-form', 'admin', $data);
    }
    
    public function editService($id) {
        $this->userModel->requireAuth();
        
        $service = $this->serviceModel->find($id);
        if (!$service) {
            $this->setFlash('error', 'Service not found.');
            $this->redirect('admin/services');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $serviceData = [
                'title' => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'icon' => trim($_POST['icon'] ?? ''),
                'features' => json_encode($this->parseTextareaList($_POST['features'] ?? '')),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'sort_order' => (int)($_POST['sort_order'] ?? 0)
            ];
            
            if ($this->serviceModel->update($id, $serviceData)) {
                $this->setFlash('success', 'Service updated successfully!');
            } else {
                $this->setFlash('error', 'Failed to update service.');
            }
            
            $this->redirect('admin/services');
            return;
        }
        
        $data = [
            'title' => 'Edit Service - Admin',
            'page' => 'admin-service-edit',
            'service' => $service
        ];
        
        $this->render('admin/service-form', 'admin', $data);
    }
    
    public function deleteService($id) {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/services');
            return;
        }
        
        if ($this->serviceModel->delete($id)) {
            $this->setFlash('success', 'Service deleted successfully!');
        } else {
            $this->setFlash('error', 'Failed to delete service.');
        }
        
        $this->redirect('admin/services');
    }
}
