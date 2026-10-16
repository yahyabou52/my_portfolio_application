<?php

trait PricingPlanActions {
    // PRICING PLAN MANAGEMENT
    public function pricingPlans() {
        $this->userModel->requireAuth();

        $plans = $this->getFormattedPricingPlans();
        $stats = $this->buildPricingPlanStats($plans);

        $routes = [
            'fetch' => url('admin/services/pricing/list'),
            'store' => url('admin/services/pricing'),
            'update_template' => url('admin/services/pricing/__ID__/update'),
            'delete_template' => url('admin/services/pricing/__ID__/delete'),
            'highlight_template' => url('admin/services/pricing/__ID__/highlight'),
            'reorder' => url('admin/services/pricing/reorder')
        ];

        $data = [
            'title' => 'Pricing Plans - Admin',
            'page' => 'admin-pricing-plans',
            'plans_json' => htmlspecialchars(json_encode($plans, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8'),
            'routes_json' => htmlspecialchars(json_encode($routes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8'),
            'stats' => $stats
        ];

        $this->render('admin/pricing-plans', 'admin', $data);
    }

    public function fetchPricingPlans() {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Invalid request method.'
            ], 405);
            return;
        }

        $plans = $this->getFormattedPricingPlans();
        $stats = $this->buildPricingPlanStats($plans);

        $this->json([
            'success' => true,
            'message' => 'Pricing plans refreshed.',
            'plans' => $plans,
            'stats' => $stats
        ]);
    }

    public function storePricingPlan() {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Invalid request method.'
            ], 405);
            return;
        }

        $input = $this->sanitizePricingPlanInput($_POST);
        $errors = $this->validatePricingPlanInput($input);

        if (!empty($errors)) {
            $this->json([
                'success' => false,
                'message' => 'Please review the highlighted fields.',
                'errors' => $errors
            ], 422);
            return;
        }

        $currentPlans = $this->pricingPlanModel->allForManager();
        $sortOrder = count($currentPlans);

        $payload = [
            'title' => $input['title'],
            'subtitle' => $input['subtitle'] !== '' ? $input['subtitle'] : null,
            'price_amount' => $input['price_amount'] !== null ? $input['price_amount'] : 0,
            'price_period' => $input['price_period'] !== '' ? $input['price_period'] : null,
            'badge_text' => $input['badge_text'] !== '' ? $input['badge_text'] : null,
            'cta_label' => $input['cta_label'] !== '' ? $input['cta_label'] : 'Start Project',
            'cta_url' => $input['cta_url'] !== '' ? $input['cta_url'] : '/contact',
            'features' => $input['features_text'] !== '' ? $input['features_text'] : null,
            'visible' => $input['visible'] ? 1 : 0,
            'highlight' => 0,
            'sort_order' => $sortOrder
        ];

        try {
            $planId = (int)$this->pricingPlanModel->create($payload);
        } catch (Exception $exception) {
            error_log('Failed to create pricing plan: ' . $exception->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Unable to create the pricing plan. Please try again.'
            ], 500);
            return;
        }

        $plans = $this->getFormattedPricingPlans();
        $stats = $this->buildPricingPlanStats($plans);
        $createdPlan = null;

        foreach ($plans as $plan) {
            if ($plan['id'] === $planId) {
                $createdPlan = $plan;
                break;
            }
        }

        $this->json([
            'success' => true,
            'message' => 'Pricing plan created successfully.',
            'plan' => $createdPlan,
            'plans' => $plans,
            'stats' => $stats
        ]);
    }

    public function updatePricingPlan($id) {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Invalid request method.'
            ], 405);
            return;
        }

        $planId = (int)$id;
        if ($planId <= 0) {
            $this->json([
                'success' => false,
                'message' => 'Pricing plan not found.'
            ], 404);
            return;
        }

        $existing = $this->pricingPlanModel->findWithFeatures($planId);
        if (!$existing) {
            $this->json([
                'success' => false,
                'message' => 'Pricing plan not found.'
            ], 404);
            return;
        }

        $input = $this->sanitizePricingPlanInput($_POST, $existing);
        $errors = $this->validatePricingPlanInput($input);

        if (!empty($errors)) {
            $this->json([
                'success' => false,
                'message' => 'Please review the highlighted fields.',
                'errors' => $errors
            ], 422);
            return;
        }

        $payload = [
            'title' => $input['title'],
            'subtitle' => $input['subtitle'] !== '' ? $input['subtitle'] : null,
            'price_amount' => $input['price_amount'] !== null ? $input['price_amount'] : 0,
            'price_period' => $input['price_period'] !== '' ? $input['price_period'] : null,
            'badge_text' => $input['badge_text'] !== '' ? $input['badge_text'] : null,
            'cta_label' => $input['cta_label'] !== '' ? $input['cta_label'] : 'Start Project',
            'cta_url' => $input['cta_url'] !== '' ? $input['cta_url'] : '/contact',
            'features' => $input['features_text'] !== '' ? $input['features_text'] : null,
            'visible' => $input['visible'] ? 1 : 0
        ];

        if (!$payload['visible'] && !empty($existing['highlight'])) {
            $payload['highlight'] = 0;
        }

        try {
            $this->pricingPlanModel->update($planId, $payload);
        } catch (Exception $exception) {
            error_log('Failed to update pricing plan: ' . $exception->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Unable to update the pricing plan. Please try again.'
            ], 500);
            return;
        }

        $this->ensurePricingPlanHighlightIntegrity();

        $plans = $this->getFormattedPricingPlans();
        $stats = $this->buildPricingPlanStats($plans);
        $updatedPlan = null;

        foreach ($plans as $plan) {
            if ($plan['id'] === $planId) {
                $updatedPlan = $plan;
                break;
            }
        }

        $this->json([
            'success' => true,
            'message' => 'Pricing plan updated successfully.',
            'plan' => $updatedPlan,
            'plans' => $plans,
            'stats' => $stats
        ]);
    }

    public function deletePricingPlan($id) {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Invalid request method.'
            ], 405);
            return;
        }

        $planId = (int)$id;
        if ($planId <= 0) {
            $this->json([
                'success' => false,
                'message' => 'Pricing plan not found.'
            ], 404);
            return;
        }

        $existing = $this->pricingPlanModel->find($planId);
        if (!$existing) {
            $this->json([
                'success' => false,
                'message' => 'Pricing plan not found.'
            ], 404);
            return;
        }

        try {
            $this->pricingPlanModel->delete($planId);
        } catch (Exception $exception) {
            error_log('Failed to delete pricing plan: ' . $exception->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Unable to delete the pricing plan. Please try again.'
            ], 500);
            return;
        }

        if (!empty($existing['highlight'])) {
            $this->ensurePricingPlanHighlightIntegrity();
        }

        $plans = $this->getFormattedPricingPlans();
        $stats = $this->buildPricingPlanStats($plans);

        $this->json([
            'success' => true,
            'message' => 'Pricing plan removed.',
            'plans' => $plans,
            'stats' => $stats
        ]);
    }

    public function togglePricingPlan($id) {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Invalid request method.'
            ], 405);
            return;
        }

        $planId = (int)$id;
        if ($planId <= 0) {
            $this->json([
                'success' => false,
                'message' => 'Pricing plan not found.'
            ], 404);
            return;
        }

        $this->json([
            'success' => false,
            'message' => 'Visibility updates are managed when editing the plan.'
        ], 405);
    }

    public function highlightPricingPlan($id) {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Invalid request method.'
            ], 405);
            return;
        }

        $planId = (int)$id;
        if ($planId <= 0) {
            $this->json([
                'success' => false,
                'message' => 'Pricing plan not found.'
            ], 404);
            return;
        }

        $existing = $this->pricingPlanModel->findWithFeatures($planId);
        if (!$existing) {
            $this->json([
                'success' => false,
                'message' => 'Pricing plan not found.'
            ], 404);
            return;
        }

        $shouldHighlight = isset($_POST['highlight']) ? (int)$_POST['highlight'] === 1 : true;

        try {
            if ($shouldHighlight) {
                $this->pricingPlanModel->updateHighlight($planId);
            } else {
                $this->pricingPlanModel->clearHighlight($planId);
                $this->ensurePricingPlanHighlightIntegrity();
            }
        } catch (Exception $exception) {
            error_log('Failed to highlight pricing plan: ' . $exception->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Unable to highlight the selected plan. Please try again.'
            ], 500);
            return;
        }

        $plans = $this->getFormattedPricingPlans();
        $stats = $this->buildPricingPlanStats($plans);

        $highlightedPlan = null;
        foreach ($plans as $plan) {
            if ($plan['id'] === $planId) {
                $highlightedPlan = $plan;
                break;
            }
        }

        $this->json([
            'success' => true,
            'message' => $shouldHighlight ? 'Highlighted plan updated.' : 'Plan highlight cleared.',
            'plan' => $highlightedPlan,
            'plans' => $plans,
            'stats' => $stats
        ]);
    }

    public function reorderPricingPlans() {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Invalid request method.'
            ], 405);
            return;
        }

        $orderPayload = $_POST['order'] ?? '[]';
        $orderedIds = json_decode($orderPayload, true);

        if (!is_array($orderedIds) || empty($orderedIds)) {
            $this->json([
                'success' => false,
                'message' => 'Provide the new plan order.'
            ], 422);
            return;
        }

        $orderedIds = array_map('intval', $orderedIds);
        $orderedIds = array_filter($orderedIds, static fn ($value) => $value > 0);

        if (empty($orderedIds)) {
            $this->json([
                'success' => false,
                'message' => 'Provide the new plan order.'
            ], 422);
            return;
        }

        try {
            $this->pricingPlanModel->reorder(array_values($orderedIds));
        } catch (Exception $exception) {
            error_log('Failed to reorder pricing plans: ' . $exception->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Unable to save the new plan order.'
            ], 500);
            return;
        }

        $plans = $this->getFormattedPricingPlans();
        $stats = $this->buildPricingPlanStats($plans);

        $this->json([
            'success' => true,
            'message' => 'Plan order updated.',
            'plans' => $plans,
            'stats' => $stats
        ]);
    }
}
