<?php

trait SkillsActions {
    // SKILLS MANAGEMENT
    public function skills() {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleSkillsStructureSave();
            return;
        }

        $categories = $this->skillModel->getCategoriesWithSkills(true);

        $data = [
            'title' => 'Skills & Tools Preview - Admin',
            'page' => 'admin-skills-preview',
            'skill_categories' => $categories
        ];

        $this->render('admin/skills-preview', 'admin', $data);
    }

    private function handleSkillsStructureSave(): void {
        $payloadRaw = $_POST['skills_structure'] ?? '';
        $decoded = json_decode($payloadRaw, true);

        if (!is_array($decoded)) {
            $this->setFlash('error', 'Invalid skills structure payload received.');
            $this->redirect('admin/skills');
            return;
        }

        [$structure, $errors] = $this->normalizeSkillsStructure($decoded);

        if (!empty($errors)) {
            $this->setFlash('error', 'Please correct the highlighted issues before saving.');
            $this->setFlash('form_errors', $errors);
            $this->redirect('admin/skills');
            return;
        }

        try {
            $this->skillModel->syncStructure($structure);
            $this->setFlash('success', 'Skills & Tools updated successfully.');
        } catch (Exception $exception) {
            error_log('Skills sync failed: ' . $exception->getMessage());
            $this->setFlash('error', 'Failed to update skills structure.');
        }

        $this->redirect('admin/skills');
    }

    private function normalizeSkillsStructure(array $payload): array {
        $normalized = [];
        $errors = [];

        foreach ($payload as $categoryData) {
            if (!is_array($categoryData)) {
                continue;
            }

            $title = trim((string)($categoryData['title'] ?? ''));

            if (mb_strlen($title) < 2) {
                $errors[] = 'Each category needs a title with at least 2 characters.';
                continue;
            }

            $iconRaw = trim((string)($categoryData['icon_class'] ?? ''));
            $iconSanitized = preg_replace('/[^a-z0-9\s\-_:]/i', '', $iconRaw);
            $iconSanitized = trim(preg_replace('/\s+/', ' ', $iconSanitized ?? ''));
            $iconSanitized = mb_substr($iconSanitized, 0, 120);

            $skills = [];
            $skillEntries = isset($categoryData['skills']) && is_array($categoryData['skills'])
                ? $categoryData['skills']
                : [];

            foreach ($skillEntries as $skillData) {
                if (!is_array($skillData)) {
                    continue;
                }

                $skillName = trim((string)($skillData['name'] ?? ''));
                if (mb_strlen($skillName) < 2) {
                    $errors[] = sprintf('Skill names must be at least 2 characters long (issue found in "%s").', $title);
                    continue;
                }

                $proficiency = (int)($skillData['proficiency_level'] ?? $skillData['proficiency'] ?? 0);
                $proficiency = max(0, min(100, $proficiency));

                $skills[] = [
                    'id' => (int)($skillData['id'] ?? 0),
                    'name' => mb_substr($skillName, 0, 150),
                    'proficiency_level' => $proficiency,
                    'is_visible' => !empty($skillData['is_visible']) ? 1 : 0
                ];
            }

            if (empty($skills)) {
                $errors[] = sprintf('Category "%s" must include at least one skill.', $title);
            }

            $normalized[] = [
                'id' => (int)($categoryData['id'] ?? 0),
                'title' => mb_substr($title, 0, 150),
                'icon_class' => $iconSanitized,
                'is_visible' => !empty($categoryData['is_visible']) ? 1 : 0,
                'skills' => $skills
            ];
        }

        if (empty($normalized)) {
            $errors[] = 'Add at least one category with visible skills before saving.';
        }

        return [$normalized, $errors];
    }

    private function handleServicesStructureSave(): void {
        $isAjax = $this->isAjaxRequest();
        $payloadRaw = $_POST['services_structure'] ?? '';
        $decoded = json_decode($payloadRaw, true);

        if (!is_array($decoded)) {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid services payload received.'
                ], 422);
                return;
            }

            $this->setFlash('error', 'Invalid services payload received.');
            $this->redirect('admin/services');
            return;
        }

        [$structure, $errors] = $this->normalizeServicesStructure($decoded);

        if (!empty($errors)) {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Please resolve the highlighted service issues before saving.',
                    'errors' => $errors
                ], 422);
                return;
            }

            $this->setFlash('error', 'Please resolve the highlighted service issues before saving.');
            $this->setFlash('form_errors', $errors);
            $this->redirect('admin/services');
            return;
        }

        try {
            $this->serviceModel->syncServices($structure);

            $services = $this->serviceModel->getAllWithFeatures(true);
            $normalized = array_map([$this, 'formatServiceForManager'], $services);

            if ($isAjax) {
                $this->json([
                    'success' => true,
                    'message' => 'Services updated successfully.',
                    'services' => $normalized
                ]);
                return;
            }

            $this->setFlash('success', 'Services updated successfully.');
        } catch (Exception $exception) {
            error_log('Failed to sync services: ' . $exception->getMessage());

            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Unable to update services. Please try again.'
                ], 500);
                return;
            }

            $this->setFlash('error', 'Unable to update services. Please try again.');
        }

        $this->redirect('admin/services');
    }

    private function normalizeServicesStructure(array $payload): array {
        $normalized = [];
        $errors = [];

        foreach ($payload as $serviceData) {
            if (!is_array($serviceData)) {
                continue;
            }

            $title = trim((string)($serviceData['title'] ?? ''));
            if (mb_strlen($title) < 3) {
                $errors[] = 'Each service needs a title with at least 3 characters.';
                continue;
            }

            $description = trim((string)($serviceData['description'] ?? ''));
            if (mb_strlen($description) < 12) {
                $errors[] = sprintf('Add a short description for "%s" (minimum 12 characters).', $title);
                continue;
            }

            $iconRaw = trim((string)($serviceData['icon'] ?? ''));
            $iconSanitized = preg_replace('/[^a-z0-9\s\-_:]/i', '', $iconRaw);
            $iconSanitized = trim(preg_replace('/\s+/', ' ', $iconSanitized ?? ''));
            $iconSanitized = mb_substr($iconSanitized, 0, 120);

            $priceLabel = trim((string)($serviceData['price_label'] ?? ''));
            $priceLabel = mb_substr($priceLabel, 0, 120);

            $priceAmount = null;
            $priceAmountRaw = $serviceData['price_amount'] ?? null;
            if ($priceAmountRaw !== null && $priceAmountRaw !== '') {
                if (!is_numeric($priceAmountRaw)) {
                    $errors[] = sprintf('Price amount for "%s" must be a valid number.', $title);
                } else {
                    $priceAmount = round((float)$priceAmountRaw, 2);
                }
            }

            $featuresRaw = $serviceData['features'] ?? [];
            if (is_string($featuresRaw)) {
                $featuresRaw = preg_split('/\r?\n/', $featuresRaw);
            }
            if (!is_array($featuresRaw)) {
                $featuresRaw = [];
            }

            $features = [];
            foreach ($featuresRaw as $feature) {
                $text = trim((string)$feature);
                if ($text === '') {
                    continue;
                }
                $features[] = mb_substr($text, 0, 255);
            }

            if (empty($features)) {
                $errors[] = sprintf('Add at least one feature bullet for "%s".', $title);
            }

            $normalized[] = [
                'id' => (int)($serviceData['id'] ?? 0),
                'icon' => $iconSanitized,
                'title' => mb_substr($title, 0, 200),
                'description' => mb_substr($description, 0, 2000),
                'price_label' => $priceLabel,
                'price_amount' => $priceAmount,
                'is_visible' => !empty($serviceData['is_visible']) ? 1 : 0,
                'features' => $features
            ];
        }

        if (empty($normalized)) {
            $errors[] = 'Add at least one service before saving.';
        }

        return [$normalized, $errors];
    }

    private function formatServiceForFeatureManager(array $service): array {
        $priceAmount = null;
        if (isset($service['price_amount']) && $service['price_amount'] !== '' && $service['price_amount'] !== null) {
            $priceAmount = (float)$service['price_amount'];
        }

        return [
            'id' => (int)($service['id'] ?? 0),
            'title' => (string)($service['title'] ?? ''),
            'description' => (string)($service['description'] ?? ''),
            'icon' => (string)($service['icon'] ?? ''),
            'price_label' => (string)($service['price_label'] ?? ''),
            'price_amount' => $priceAmount,
            'is_visible' => (int)(isset($service['is_visible'])
                ? $service['is_visible']
                : (($service['status'] ?? 'draft') === 'published' ? 1 : 0))
        ];
    }

    private function formatServiceFeatureForManager(array $feature, array $service = []): array {
        return [
            'id' => (int)($feature['id'] ?? 0),
            'service_id' => (int)($feature['service_id'] ?? ($service['id'] ?? 0)),
            'feature_text' => (string)($feature['feature_text'] ?? ''),
            'icon_class' => (string)($feature['icon_class'] ?? ''),
            'sort_order' => (int)($feature['sort_order'] ?? 0),
            'display' => isset($feature['display']) ? (int)$feature['display'] : 1
        ];
    }

    private function sanitizeServiceFeatureInput(array $source, array $existing = []): array {
        $textSource = $source['feature_text'] ?? ($existing['feature_text'] ?? '');
        $featureText = trim((string)$textSource);
        $featureText = mb_substr($featureText, 0, 255);

        $iconSource = $source['icon_class'] ?? ($existing['icon_class'] ?? '');
        $iconSanitized = trim((string)$iconSource);
        $iconSanitized = preg_replace('/[^a-z0-9\s\-_:]/i', '', $iconSanitized);
        $iconSanitized = trim(preg_replace('/\s+/', ' ', $iconSanitized ?? ''));
        $iconSanitized = mb_substr($iconSanitized, 0, 100);

        $sortRaw = $source['sort_order'] ?? null;
        $sortOrder = null;
        if ($sortRaw !== null && $sortRaw !== '') {
            $sortOrder = (int)$sortRaw;
            if ($sortOrder <= 0) {
                $sortOrder = null;
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
            'feature_text' => $featureText,
            'icon_class' => $iconSanitized,
            'sort_order' => $sortOrder,
            'display' => $display
        ];
    }

    private function validateServiceFeatureInput(array $data): array {
        $errors = [];

        if (mb_strlen($data['feature_text'] ?? '') < 3) {
            $errors[] = 'Feature text must be at least 3 characters long.';
        }

        if ($data['sort_order'] !== null && $data['sort_order'] <= 0) {
            $errors[] = 'Sort order must be a positive number when provided.';
        }

        if (isset($data['icon_class']) && mb_strlen($data['icon_class']) > 100) {
            $errors[] = 'Icon class is too long.';
        }

        return $errors;
    }

    private function formatServiceForManager(array $service): array {
        $priceAmount = null;
        if (isset($service['price_amount']) && $service['price_amount'] !== '' && $service['price_amount'] !== null) {
            $priceAmount = (float)$service['price_amount'];
        }

        $features = [];
        if (!empty($service['features']) && is_array($service['features'])) {
            foreach ($service['features'] as $feature) {
                if (is_array($feature)) {
                    $text = trim((string)($feature['feature_text'] ?? ''));
                    if ($text !== '') {
                        $features[] = $text;
                    }
                } elseif (is_scalar($feature)) {
                    $features[] = (string)$feature;
                }
            }
        }

        return [
            'id' => (int)($service['id'] ?? 0),
            'icon' => (string)($service['icon'] ?? ''),
            'title' => (string)($service['title'] ?? ''),
            'description' => (string)($service['description'] ?? ''),
            'price_label' => (string)($service['price_label'] ?? ''),
            'price_amount' => $priceAmount,
            'is_visible' => (int)($service['is_visible'] ?? (($service['status'] ?? '') === 'published' ? 1 : 0)),
            'features' => $features,
            'sort_order' => (int)($service['sort_order'] ?? 0)
        ];
    }
}
