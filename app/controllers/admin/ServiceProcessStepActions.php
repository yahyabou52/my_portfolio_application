<?php

trait ServiceProcessStepActions {
    // SERVICE PROCESS STEP MANAGEMENT
    public function serviceProcessSteps() {
        $this->userModel->requireAuth();

        $services = $this->serviceModel->all('sort_order ASC, title ASC');
        $formattedServices = array_map([$this, 'formatServiceForFeatureManager'], $services);

        $globalService = $this->formatGlobalProcessService();
        array_unshift($formattedServices, $globalService);

        $initialServiceId = null;
        $initialService = $globalService;

        $initialSteps = $this->serviceProcessModel->getByService(null, true);
        $normalizedSteps = array_map(function (array $step) use ($initialService) {
            return $this->formatProcessStepForManager($step, $initialService);
        }, $initialSteps);

        $routes = [
            'fetch' => url('admin/services/process/list'),
            'store' => url('admin/services/process'),
            'update_template' => url('admin/services/process/__ID__/update'),
            'delete_template' => url('admin/services/process/__ID__/delete'),
            'toggle_template' => url('admin/services/process/__ID__/toggle'),
            'reorder' => url('admin/services/process/reorder')
        ];

        $data = [
            'title' => 'Design Process Steps - Admin',
            'page' => 'admin-service-process',
            'services' => $formattedServices,
            'initial_service_id' => $initialServiceId,
            'services_json' => htmlspecialchars(json_encode($formattedServices, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8'),
            'steps_json' => htmlspecialchars(json_encode($normalizedSteps, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8'),
            'process_routes' => $routes
        ];

        $this->render('admin/service-process', 'admin', $data);
    }

    public function fetchServiceProcessSteps() {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Invalid request method.'
            ], 405);
            return;
        }

        $serviceId = $this->normalizeProcessServiceId($_POST['service_id'] ?? null);
        $service = null;

        if ($serviceId !== null) {
            $service = $this->serviceModel->find($serviceId);
            if (!$service) {
                $this->json([
                    'success' => false,
                    'message' => 'Service not found.'
                ], 404);
                return;
            }
        }

        $steps = $this->serviceProcessModel->getByService($serviceId, true);
        $formattedSteps = array_map(function (array $step) use ($serviceId, $service) {
            $serviceMeta = $serviceId !== null
                ? $this->formatServiceForFeatureManager($service)
                : $this->formatGlobalProcessService();

            return $this->formatProcessStepForManager($step, $serviceMeta);
        }, $steps);

        $this->json([
            'success' => true,
            'service' => $serviceId !== null
                ? $this->formatServiceForFeatureManager($service)
                : $this->formatGlobalProcessService(),
            'steps' => $formattedSteps,
            'message' => 'Process steps loaded.'
        ]);
    }

    public function storeServiceProcessStep() {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Invalid request method.'
            ], 405);
            return;
        }

        $serviceId = $this->normalizeProcessServiceId($_POST['service_id'] ?? null);
        $service = null;

        if ($serviceId !== null) {
            $service = $this->serviceModel->find($serviceId);
            if (!$service) {
                $this->json([
                    'success' => false,
                    'message' => 'Service not found.'
                ], 404);
                return;
            }
        }

        $input = $this->sanitizeProcessStepInput($_POST, [], $serviceId);
        $errors = $this->validateProcessStepInput($input);

        if (!empty($errors)) {
            $this->json([
                'success' => false,
                'message' => 'Please fix the highlighted fields.',
                'errors' => $errors
            ], 422);
            return;
        }

        $existing = $this->serviceProcessModel->getByService($serviceId, true);
        $requestedOrder = isset($input['step_order']) ? (int)$input['step_order'] : null;
        $desiredPosition = $requestedOrder && $requestedOrder > 0 ? $requestedOrder : count($existing) + 1;

        $payload = [
            'service_id' => $serviceId,
            'title' => $input['title'],
            'description' => $input['description'],
            'icon_class' => $input['icon_class'] !== '' ? $input['icon_class'] : null,
            'display' => $input['display'] ? 1 : 0,
            'step_order' => $this->serviceProcessModel->getNextOrder($serviceId)
        ];

        try {
            $stepId = (int)$this->serviceProcessModel->create($payload);

            if ($desiredPosition !== count($existing) + 1) {
                $orderedIds = array_column($existing, 'id');
                $desiredPosition = max(1, min($desiredPosition, count($existing) + 1));
                array_splice($orderedIds, $desiredPosition - 1, 0, $stepId);
                $this->serviceProcessModel->reorderForService($serviceId, $orderedIds);
            }
        } catch (Exception $exception) {
            error_log('Failed to create process step: ' . $exception->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Unable to add the process step. Please try again.'
            ], 500);
            return;
        }

        $record = $this->serviceProcessModel->find($stepId);
        $serviceMeta = $serviceId !== null
            ? $this->formatServiceForFeatureManager($service)
            : $this->formatGlobalProcessService();
        $formatted = $this->formatProcessStepForManager($record, $serviceMeta);

        $this->json([
            'success' => true,
            'message' => 'Process step added successfully.',
            'step' => $formatted
        ]);
    }

    public function updateServiceProcessStep($id) {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Invalid request method.'
            ], 405);
            return;
        }

        $stepId = (int)$id;
        if ($stepId <= 0) {
            $this->json([
                'success' => false,
                'message' => 'Process step not found.'
            ], 404);
            return;
        }

        $existing = $this->serviceProcessModel->find($stepId);
        if (!$existing) {
            $this->json([
                'success' => false,
                'message' => 'Process step not found.'
            ], 404);
            return;
        }

        $serviceId = isset($existing['service_id']) && $existing['service_id'] !== null
            ? (int)$existing['service_id']
            : null;

        $service = null;
        if ($serviceId !== null) {
            $service = $this->serviceModel->find($serviceId);
            if (!$service) {
                $this->json([
                    'success' => false,
                    'message' => 'Service not found.'
                ], 404);
                return;
            }
        }

        $input = $this->sanitizeProcessStepInput($_POST, $existing, $serviceId);
        $errors = $this->validateProcessStepInput($input);

        if (!empty($errors)) {
            $this->json([
                'success' => false,
                'message' => 'Please fix the highlighted fields.',
                'errors' => $errors
            ], 422);
            return;
        }

        $payload = [
            'title' => $input['title'],
            'description' => $input['description'],
            'icon_class' => $input['icon_class'] !== '' ? $input['icon_class'] : null,
            'display' => $input['display'] ? 1 : 0
        ];

        if (isset($input['step_order']) && $input['step_order'] > 0) {
            $payload['step_order'] = (int)$input['step_order'];
        }

        try {
            $this->serviceProcessModel->update($stepId, $payload);

            if (isset($payload['step_order'])) {
                $all = $this->serviceProcessModel->getByService($serviceId, true);
                $orderedIds = array_column($all, 'id');

                $currentIndex = array_search($stepId, $orderedIds, true);
                if ($currentIndex !== false) {
                    array_splice($orderedIds, $currentIndex, 1);
                }

                $desiredPosition = max(1, min((int)$payload['step_order'], count($orderedIds) + 1));
                array_splice($orderedIds, $desiredPosition - 1, 0, $stepId);
                $this->serviceProcessModel->reorderForService($serviceId, $orderedIds);
            }
        } catch (Exception $exception) {
            error_log('Failed to update process step: ' . $exception->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Unable to update the process step. Please try again.'
            ], 500);
            return;
        }

        $record = $this->serviceProcessModel->find($stepId);
        $serviceMeta = $serviceId !== null
            ? $this->formatServiceForFeatureManager($service)
            : $this->formatGlobalProcessService();
        $formatted = $this->formatProcessStepForManager($record, $serviceMeta);

        $this->json([
            'success' => true,
            'message' => 'Process step updated successfully.',
            'step' => $formatted
        ]);
    }

    public function deleteServiceProcessStep($id) {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Invalid request method.'
            ], 405);
            return;
        }

        $stepId = (int)$id;
        if ($stepId <= 0) {
            $this->json([
                'success' => false,
                'message' => 'Process step not found.'
            ], 404);
            return;
        }

        $existing = $this->serviceProcessModel->find($stepId);
        if (!$existing) {
            $this->json([
                'success' => false,
                'message' => 'Process step not found.'
            ], 404);
            return;
        }

        try {
            $deleted = $this->serviceProcessModel->delete($stepId);
        } catch (Exception $exception) {
            error_log('Failed to delete process step: ' . $exception->getMessage());
            $deleted = false;
        }

        if (!$deleted) {
            $this->json([
                'success' => false,
                'message' => 'Unable to remove the process step. Please try again.'
            ], 500);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'Process step removed.'
        ]);
    }

    public function toggleServiceProcessStep($id) {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Invalid request method.'
            ], 405);
            return;
        }

        $stepId = (int)$id;
        if ($stepId <= 0) {
            $this->json([
                'success' => false,
                'message' => 'Process step not found.'
            ], 404);
            return;
        }

        $existing = $this->serviceProcessModel->find($stepId);
        if (!$existing) {
            $this->json([
                'success' => false,
                'message' => 'Process step not found.'
            ], 404);
            return;
        }

        $serviceId = isset($existing['service_id']) && $existing['service_id'] !== null
            ? (int)$existing['service_id']
            : null;

        $service = null;
        if ($serviceId !== null) {
            $service = $this->serviceModel->find($serviceId);
        }

        $display = isset($_POST['display']) ? (int)$_POST['display'] : 1;
        $desired = $display === 1;

        try {
            $this->serviceProcessModel->toggleDisplay($stepId, $desired);
        } catch (Exception $exception) {
            error_log('Failed to toggle process step: ' . $exception->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Unable to update visibility. Please try again.'
            ], 500);
            return;
        }

        $record = $this->serviceProcessModel->find($stepId);
        $serviceMeta = $serviceId !== null
            ? $this->formatServiceForFeatureManager($service)
            : $this->formatGlobalProcessService();

        $this->json([
            'success' => true,
            'message' => $desired ? 'Process step is now visible.' : 'Process step hidden.',
            'step' => $this->formatProcessStepForManager($record, $serviceMeta)
        ]);
    }

    public function reorderServiceProcessSteps() {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Invalid request method.'
            ], 405);
            return;
        }

        $serviceId = $this->normalizeProcessServiceId($_POST['service_id'] ?? null);
        $service = null;

        if ($serviceId !== null) {
            $service = $this->serviceModel->find($serviceId);
            if (!$service) {
                $this->json([
                    'success' => false,
                    'message' => 'Service not found.'
                ], 404);
                return;
            }
        }

        $orderPayload = $_POST['order'] ?? '[]';
        $orderedIds = json_decode($orderPayload, true);

        if (!is_array($orderedIds) || empty($orderedIds)) {
            $this->json([
                'success' => false,
                'message' => 'Provide the new step order.'
            ], 422);
            return;
        }

        try {
            $this->serviceProcessModel->reorderForService($serviceId, $orderedIds);
        } catch (Exception $exception) {
            error_log('Failed to reorder process steps: ' . $exception->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Unable to save the new order.'
            ], 500);
            return;
        }

        $steps = $this->serviceProcessModel->getByService($serviceId, true);
        $serviceMeta = $serviceId !== null
            ? $this->formatServiceForFeatureManager($service)
            : $this->formatGlobalProcessService();

        $formattedSteps = array_map(function (array $step) use ($serviceMeta) {
            return $this->formatProcessStepForManager($step, $serviceMeta);
        }, $steps);

        $this->json([
            'success' => true,
            'message' => 'Process order updated.',
            'steps' => $formattedSteps
        ]);
    }

    private function sanitizeProcessStepInput(array $source, array $existing = [], ?int $serviceId = null): array {
        $titleSource = $source['title'] ?? ($existing['title'] ?? '');
        $title = trim((string)$titleSource);
        $title = mb_substr($title, 0, 255);

        $descriptionSource = $source['description'] ?? ($existing['description'] ?? '');
        $description = trim((string)$descriptionSource);
        $description = mb_substr($description, 0, 4000);

        $iconSource = $source['icon_class'] ?? ($existing['icon_class'] ?? '');
        $iconSanitized = trim((string)$iconSource);
        $iconSanitized = preg_replace('/[^a-z0-9\s\-_:]/i', '', $iconSanitized);
        $iconSanitized = trim(preg_replace('/\s+/', ' ', $iconSanitized ?? ''));
        $iconSanitized = mb_substr($iconSanitized, 0, 100);

        $sortRaw = $source['step_order'] ?? null;
        $stepOrder = null;
        if ($sortRaw !== null && $sortRaw !== '') {
            $stepOrder = (int)$sortRaw;
            if ($stepOrder <= 0) {
                $stepOrder = null;
            }
        }

        $displayRaw = $source['display'] ?? ($existing['display'] ?? 1);
        if (is_string($displayRaw)) {
            $displayNormalized = strtolower(trim($displayRaw));
            $display = !in_array($displayNormalized, ['0', 'false', 'no'], true);
        } else {
            $display = (bool)$displayRaw;
        }

        return [
            'service_id' => $serviceId,
            'title' => $title,
            'description' => $description,
            'icon_class' => $iconSanitized,
            'step_order' => $stepOrder,
            'display' => $display
        ];
    }

    private function validateProcessStepInput(array $data): array {
        $errors = [];

        if (mb_strlen($data['title'] ?? '') < 3) {
            $errors[] = 'Step title must be at least 3 characters long.';
        }

        if ($data['step_order'] !== null && $data['step_order'] <= 0) {
            $errors[] = 'Step order must be a positive number when provided.';
        }

        if (isset($data['icon_class']) && mb_strlen($data['icon_class']) > 100) {
            $errors[] = 'Icon class is too long.';
        }

        return $errors;
    }

    private function formatProcessStepForManager(array $step, array $service = []): array {
        $icon = trim((string)($step['icon_class'] ?? ''));
        $description = (string)($step['description'] ?? '');

        return [
            'id' => (int)($step['id'] ?? 0),
            'service_id' => isset($step['service_id']) && $step['service_id'] !== null ? (int)$step['service_id'] : null,
            'step_order' => (int)($step['step_order'] ?? 0),
            'icon_class' => $icon,
            'title' => (string)($step['title'] ?? ''),
            'description' => $description,
            'excerpt' => mb_substr($description, 0, 120),
            'display' => isset($step['display']) ? (int)$step['display'] : 1,
            'service' => $service
        ];
    }

    private function normalizeProcessServiceId($value): ?int {
        if ($value === null || $value === '' || $value === 'null') {
            return null;
        }

        $id = (int)$value;
        return $id > 0 ? $id : null;
    }

    private function formatGlobalProcessService(): array {
        return [
            'id' => null,
            'icon' => 'bi-diagram-3',
            'title' => 'All Services',
            'description' => 'Steps shown on the Services page timeline.',
            'price_label' => '',
            'price_amount' => null,
            'is_visible' => 1
        ];
    }
}
