<?php

trait FaqActions {
    // FAQ MANAGEMENT
    public function faqs() {
            $this->userModel->requireAuth();

            $faqs = $this->getFormattedFaqs();
            $stats = $this->buildFaqStats($faqs);

            $routes = [
                'fetch' => url('admin/services/faqs/list'),
                'store' => url('admin/services/faqs'),
                'update_template' => url('admin/services/faqs/__ID__/update'),
                'delete_template' => url('admin/services/faqs/__ID__/delete'),
                'toggle_template' => url('admin/services/faqs/__ID__/toggle'),
                'reorder' => url('admin/services/faqs/reorder')
            ];

            $data = [
                'title' => 'FAQs - Admin',
                'page' => 'admin-faqs',
                'faqs_json' => htmlspecialchars(json_encode($faqs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8'),
                'routes_json' => htmlspecialchars(json_encode($routes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8'),
                'stats' => $stats
            ];

            $this->render('admin/faqs', 'admin', $data);
        }

        public function fetchFaqs() {
            $this->userModel->requireAuth();

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid request method.'
                ], 405);
                return;
            }

            $faqs = $this->getFormattedFaqs();
            $stats = $this->buildFaqStats($faqs);

            $this->json([
                'success' => true,
                'message' => 'FAQs refreshed.',
                'faqs' => $faqs,
                'stats' => $stats
            ]);
        }

        public function storeFaq() {
            $this->userModel->requireAuth();

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid request method.'
                ], 405);
                return;
            }

            $input = $this->sanitizeFaqInput($_POST);
            $errors = $this->validateFaqInput($input);

            if (!empty($errors)) {
                $this->json([
                    'success' => false,
                    'message' => 'Please review the highlighted fields.',
                    'errors' => $errors
                ], 422);
                return;
            }

            try {
                $existing = $this->faqModel->allForManager();
            } catch (Throwable $throwable) {
                error_log('Failed to load FAQs before create: ' . $throwable->getMessage());
                $this->json([
                    'success' => false,
                    'message' => 'Unable to load existing FAQs. Please run the database updater and try again.'
                ], 500);
                return;
            }
            $payload = [
                'question' => $input['question'],
                'answer' => $input['answer'],
                'sort_order' => count($existing),
                'visible' => $input['visible'] ? 1 : 0
            ];

            try {
                $faqId = (int)$this->faqModel->create($payload);
            } catch (Exception $exception) {
                error_log('Failed to create FAQ: ' . $exception->getMessage());
                $this->json([
                    'success' => false,
                    'message' => 'Unable to create the FAQ. Please try again.'
                ], 500);
                return;
            }

            $faqs = $this->getFormattedFaqs();
            $stats = $this->buildFaqStats($faqs);
            $created = null;

            foreach ($faqs as $faq) {
                if ($faq['id'] === $faqId) {
                    $created = $faq;
                    break;
                }
            }

            $this->json([
                'success' => true,
                'message' => 'FAQ created successfully.',
                'faq' => $created,
                'faqs' => $faqs,
                'stats' => $stats
            ]);
        }

        public function updateFaq($id) {
            $this->userModel->requireAuth();

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid request method.'
                ], 405);
                return;
            }

            $faqId = (int)$id;
            if ($faqId <= 0) {
                $this->json([
                    'success' => false,
                    'message' => 'FAQ not found.'
                ], 404);
                return;
            }

            try {
                $existing = $this->faqModel->find($faqId);
            } catch (Throwable $throwable) {
                error_log('Failed to load FAQ for update: ' . $throwable->getMessage());
                $this->json([
                    'success' => false,
                    'message' => 'Unable to load the FAQ. Please try again.'
                ], 500);
                return;
            }
            if (!$existing) {
                $this->json([
                    'success' => false,
                    'message' => 'FAQ not found.'
                ], 404);
                return;
            }

            $input = $this->sanitizeFaqInput($_POST, $existing);
            $errors = $this->validateFaqInput($input);

            if (!empty($errors)) {
                $this->json([
                    'success' => false,
                    'message' => 'Please review the highlighted fields.',
                    'errors' => $errors
                ], 422);
                return;
            }

            $payload = [
                'question' => $input['question'],
                'answer' => $input['answer'],
                'visible' => $input['visible'] ? 1 : 0
            ];

            try {
                $this->faqModel->update($faqId, $payload);
            } catch (Exception $exception) {
                error_log('Failed to update FAQ: ' . $exception->getMessage());
                $this->json([
                    'success' => false,
                    'message' => 'Unable to update the FAQ. Please try again.'
                ], 500);
                return;
            }

            $faqs = $this->getFormattedFaqs();
            $stats = $this->buildFaqStats($faqs);
            $updated = null;

            foreach ($faqs as $faq) {
                if ($faq['id'] === $faqId) {
                    $updated = $faq;
                    break;
                }
            }

            $this->json([
                'success' => true,
                'message' => 'FAQ updated successfully.',
                'faq' => $updated,
                'faqs' => $faqs,
                'stats' => $stats
            ]);
        }

        public function deleteFaq($id) {
            $this->userModel->requireAuth();

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid request method.'
                ], 405);
                return;
            }

            $faqId = (int)$id;
            if ($faqId <= 0) {
                $this->json([
                    'success' => false,
                    'message' => 'FAQ not found.'
                ], 404);
                return;
            }

            try {
                $existing = $this->faqModel->find($faqId);
            } catch (Throwable $throwable) {
                error_log('Failed to load FAQ for delete: ' . $throwable->getMessage());
                $this->json([
                    'success' => false,
                    'message' => 'Unable to load the FAQ. Please try again.'
                ], 500);
                return;
            }
            if (!$existing) {
                $this->json([
                    'success' => false,
                    'message' => 'FAQ not found.'
                ], 404);
                return;
            }

            try {
                $this->faqModel->delete($faqId);
            } catch (Exception $exception) {
                error_log('Failed to delete FAQ: ' . $exception->getMessage());
                $this->json([
                    'success' => false,
                    'message' => 'Unable to delete the FAQ. Please try again.'
                ], 500);
                return;
            }

            $faqs = $this->getFormattedFaqs();
            $stats = $this->buildFaqStats($faqs);

            $this->json([
                'success' => true,
                'message' => 'FAQ removed.',
                'faqs' => $faqs,
                'stats' => $stats
            ]);
        }

        public function toggleFaq($id) {
            $this->userModel->requireAuth();

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid request method.'
                ], 405);
                return;
            }

            $faqId = (int)$id;
            if ($faqId <= 0) {
                $this->json([
                    'success' => false,
                    'message' => 'FAQ not found.'
                ], 404);
                return;
            }

            try {
                $existing = $this->faqModel->find($faqId);
            } catch (Throwable $throwable) {
                error_log('Failed to load FAQ for toggle: ' . $throwable->getMessage());
                $this->json([
                    'success' => false,
                    'message' => 'Unable to load the FAQ. Please try again.'
                ], 500);
                return;
            }
            if (!$existing) {
                $this->json([
                    'success' => false,
                    'message' => 'FAQ not found.'
                ], 404);
                return;
            }

            $visible = isset($_POST['visible']) ? (int)$_POST['visible'] === 1 : true;

            try {
                $this->faqModel->toggleVisibility($faqId, $visible);
            } catch (Exception $exception) {
                error_log('Failed to toggle FAQ visibility: ' . $exception->getMessage());
                $this->json([
                    'success' => false,
                    'message' => 'Unable to update FAQ visibility. Please try again.'
                ], 500);
                return;
            }

            $faqs = $this->getFormattedFaqs();
            $stats = $this->buildFaqStats($faqs);
            $updated = null;

            foreach ($faqs as $faq) {
                if ($faq['id'] === $faqId) {
                    $updated = $faq;
                    break;
                }
            }

            $this->json([
                'success' => true,
                'message' => $visible ? 'FAQ is now visible.' : 'FAQ hidden from the Services page.',
                'faq' => $updated,
                'faqs' => $faqs,
                'stats' => $stats
            ]);
        }

        public function reorderFaqs() {
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
                    'message' => 'Provide the new FAQ order.'
                ], 422);
                return;
            }

            $orderedIds = array_map('intval', $orderedIds);
            $orderedIds = array_filter($orderedIds, static fn ($value) => $value > 0);

            if (empty($orderedIds)) {
                $this->json([
                    'success' => false,
                    'message' => 'Provide the new FAQ order.'
                ], 422);
                return;
            }

            try {
                $this->faqModel->reorder(array_values($orderedIds));
            } catch (Exception $exception) {
                error_log('Failed to reorder FAQs: ' . $exception->getMessage());
                $this->json([
                    'success' => false,
                    'message' => 'Unable to save the new FAQ order.'
                ], 500);
                return;
            }

            $faqs = $this->getFormattedFaqs();
            $stats = $this->buildFaqStats($faqs);

            $this->json([
                'success' => true,
                'message' => 'FAQ order updated.',
                'faqs' => $faqs,
                'stats' => $stats
            ]);
        }
    private function getFormattedPricingPlans(): array {
        $records = $this->pricingPlanModel->allForManager();
        return array_map([$this, 'formatPricingPlanForManager'], $records);
    }

    private function formatPricingPlanForManager(array $plan): array {
        $priceAmount = isset($plan['price_amount']) ? (float)$plan['price_amount'] : 0.0;
        $priceAmount = round($priceAmount, 2);
        $hasPrice = $priceAmount > 0;
        $priceDisplay = $hasPrice ? ('$' . number_format($priceAmount, (fmod($priceAmount, 1.0) === 0.0 ? 0 : 2))) : '';

        $featuresText = trim((string)($plan['features'] ?? ''));
        $featuresList = $this->collectPricingPlanFeatures($featuresText);

        $ctaLabel = trim((string)($plan['cta_label'] ?? 'Start Project'));
        if ($ctaLabel === '') {
            $ctaLabel = 'Start Project';
        }

        $ctaUrl = $this->normalizePricingPlanUrl((string)($plan['cta_url'] ?? '/contact'));
        $ctaResolved = navbar_build_nav_url($ctaUrl);

        return [
            'id' => (int)($plan['id'] ?? 0),
            'title' => (string)($plan['title'] ?? ''),
            'subtitle' => (string)($plan['subtitle'] ?? ''),
            'price_amount' => $hasPrice ? $priceAmount : null,
            'price_period' => (string)($plan['price_period'] ?? ''),
            'price_display' => $priceDisplay,
            'badge_text' => (string)($plan['badge_text'] ?? ''),
            'cta_label' => $ctaLabel,
            'cta_url' => $ctaUrl,
            'cta_url_resolved' => $ctaResolved,
            'features_text' => $featuresText,
            'features_list' => $featuresList,
            'highlight' => !empty($plan['highlight']) ? 1 : 0,
            'visible' => !empty($plan['visible']) ? 1 : 0,
            'sort_order' => (int)($plan['sort_order'] ?? 0),
            'created_at' => $plan['created_at'] ?? null,
            'updated_at' => $plan['updated_at'] ?? null
        ];
    }

    private function buildPricingPlanStats(array $plans): array {
        $total = count($plans);
        $visible = 0;
        $highlighted = 0;
        $highlightedId = null;

        foreach ($plans as $plan) {
            if (!empty($plan['visible'])) {
                $visible++;
            }

            if (!empty($plan['highlight'])) {
                $highlighted++;
                if ($highlightedId === null) {
                    $highlightedId = $plan['id'];
                }
            }
        }

        return [
            'total' => $total,
            'visible' => $visible,
            'hidden' => max(0, $total - $visible),
            'highlighted' => $highlighted,
            'highlighted_id' => $highlightedId
        ];
    }

    private function collectPricingPlanFeatures(string $features): array {
        if ($features === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $features);
        if (!is_array($lines)) {
            return [];
        }

        $lines = array_map(static function ($line) {
            return trim((string)$line);
        }, $lines);

        $lines = array_filter($lines, static function ($line) {
            return $line !== '';
        });

        return array_values($lines);
    }

    private function sanitizePricingPlanInput(array $source, array $existing = []): array {
        $title = trim((string)($source['title'] ?? ($existing['title'] ?? '')));
        $title = mb_substr($title, 0, 255);

        $subtitle = trim((string)($source['subtitle'] ?? ($existing['subtitle'] ?? '')));
        $subtitle = mb_substr($subtitle, 0, 255);

        $priceAmount = $this->normalizePricingPlanPrice($source['price_amount'] ?? ($existing['price_amount'] ?? null));

        $pricePeriod = trim((string)($source['price_period'] ?? ($existing['price_period'] ?? '')));
        $pricePeriod = mb_substr($pricePeriod, 0, 50);

        $badgeText = trim((string)($source['badge_text'] ?? ($existing['badge_text'] ?? '')));
        $badgeText = mb_substr($badgeText, 0, 120);

        $ctaLabel = trim((string)($source['cta_label'] ?? ($existing['cta_label'] ?? 'Start Project')));
        $ctaLabel = mb_substr($ctaLabel, 0, 120);

        $ctaUrl = trim((string)($source['cta_url'] ?? ($existing['cta_url'] ?? '/contact')));
        $ctaUrl = mb_substr($ctaUrl, 0, 255);
        $ctaUrl = $this->normalizePricingPlanUrl($ctaUrl);

        $featuresRaw = $source['features'] ?? ($source['features_text'] ?? ($existing['features'] ?? ''));
        $featuresText = $this->normalizePricingPlanFeatures((string)$featuresRaw);

        $visibleRaw = $source['visible'] ?? ($existing['visible'] ?? 1);
        $visible = $this->normalizeBooleanInput($visibleRaw, true);

        $highlightRaw = $source['highlight'] ?? ($existing['highlight'] ?? 0);
        $highlight = $this->normalizeBooleanInput($highlightRaw, false);

        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'price_amount' => $priceAmount,
            'price_period' => $pricePeriod,
            'badge_text' => $badgeText,
            'cta_label' => $ctaLabel,
            'cta_url' => $ctaUrl,
            'features_text' => $featuresText,
            'visible' => $visible,
            'highlight' => $highlight
        ];
    }

    private function validatePricingPlanInput(array $input): array {
        $errors = [];

        if (mb_strlen($input['title']) < 3) {
            $errors[] = 'Plan title must be at least 3 characters long.';
        }

        if ($input['badge_text'] !== '' && mb_strlen($input['badge_text']) < 3) {
            $errors[] = 'Badge text must be at least 3 characters or leave it blank.';
        }

        if ($input['price_period'] !== '' && mb_strlen($input['price_period']) < 2) {
            $errors[] = 'Price period must be at least 2 characters or leave it blank.';
        }

        if ($input['cta_label'] === '') {
            $errors[] = 'CTA label cannot be empty.';
        }

        if ($input['price_amount'] !== null && $input['price_amount'] < 0) {
            $errors[] = 'Price amount cannot be negative.';
        }

        if ($input['features_text'] !== '') {
            $features = $this->collectPricingPlanFeatures($input['features_text']);
            if (count($features) > 20) {
                $errors[] = 'Limit each plan to 20 features or fewer.';
            }
        }

        return $errors;
    }

    private function normalizePricingPlanPrice($value): ?float {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $value = str_replace([',', ' '], '', $value);
        }

        if (!is_numeric($value)) {
            return null;
        }

        $amount = (float)$value;
        if (!is_finite($amount)) {
            return null;
        }

        return round($amount, 2);
    }

    private function normalizePricingPlanFeatures(string $features): string {
        if ($features === '') {
            return '';
        }

        $lines = preg_split('/\r\n|\r|\n/', $features);
        if (!is_array($lines)) {
            return '';
        }

        $lines = array_map(static function ($line) {
            return trim((string)$line);
        }, $lines);

        $lines = array_filter($lines, static function ($line) {
            return $line !== '';
        });

        if (empty($lines)) {
            return '';
        }

        return implode("\n", $lines);
    }

    private function normalizeBooleanInput($value, bool $default): bool {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int)$value === 1;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }

            if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
                return false;
            }
        }

        return $default;
    }

    private function normalizePricingPlanUrl(string $url): string {
        if ($url === '') {
            return '/contact';
        }

        if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0 || strpos($url, '/') === 0) {
            return $url;
        }

        return '/' . ltrim($url, '/');
    }

    private function ensurePricingPlanHighlightIntegrity(): void {
        $plans = $this->pricingPlanModel->allForManager();
        if (empty($plans)) {
            return;
        }

        $highlighted = null;
        $firstVisible = null;

        foreach ($plans as $plan) {
            if ($firstVisible === null && !empty($plan['visible'])) {
                $firstVisible = $plan;
            }

            if (!empty($plan['highlight'])) {
                $highlighted = $plan;
                break;
            }
        }

        if ($highlighted === null || !empty($highlighted['visible'])) {
            return;
        }

        if ($firstVisible === null || empty($firstVisible['id'])) {
            return;
        }

        try {
            $this->pricingPlanModel->updateHighlight((int)$firstVisible['id']);
        } catch (Throwable $throwable) {
            error_log('Failed to transfer pricing plan highlight: ' . $throwable->getMessage());
        }
    }

    private function getFormattedFaqs(): array {
        try {
            $records = $this->faqModel->allForManager();
        } catch (Throwable $throwable) {
            error_log('Failed to load FAQs: ' . $throwable->getMessage());
            return [];
        }

        return array_map([$this, 'formatFaqForManager'], $records);
    }

    private function formatFaqForManager(array $faq): array {
        $question = trim((string)($faq['question'] ?? ''));
        $answer = trim((string)($faq['answer'] ?? ''));

        $excerpt = strip_tags($answer);
        if (mb_strlen($excerpt) > 140) {
            $excerpt = mb_substr($excerpt, 0, 140) . '…';
        }

        return [
            'id' => (int)($faq['id'] ?? 0),
            'question' => $question,
            'answer' => $answer,
            'answer_excerpt' => $excerpt,
            'sort_order' => (int)($faq['sort_order'] ?? 0),
            'visible' => !empty($faq['visible']) ? 1 : 0,
            'created_at' => $faq['created_at'] ?? null,
            'updated_at' => $faq['updated_at'] ?? null
        ];
    }

    private function buildFaqStats(array $faqs): array {
        $total = count($faqs);
        $visible = 0;

        foreach ($faqs as $faq) {
            if (!empty($faq['visible'])) {
                $visible++;
            }
        }

        return [
            'total' => $total,
            'visible' => $visible,
            'hidden' => max(0, $total - $visible)
        ];
    }

    private function sanitizeFaqInput(array $source, array $existing = []): array {
        $question = trim((string)($source['question'] ?? ($existing['question'] ?? '')));
        $question = mb_substr($question, 0, 255);

        $answer = trim((string)($source['answer'] ?? ($existing['answer'] ?? '')));

        $visibleRaw = $source['visible'] ?? ($existing['visible'] ?? 1);
        $visible = $this->normalizeBooleanInput($visibleRaw, true);

        return [
            'question' => $question,
            'answer' => $answer,
            'visible' => $visible
        ];
    }

    private function validateFaqInput(array $input): array {
        $errors = [];

        if (mb_strlen($input['question']) < 5) {
            $errors[] = 'Question must be at least 5 characters.';
        }

        if (mb_strlen(strip_tags($input['answer'])) < 5) {
            $errors[] = 'Answer must be at least 5 characters.';
        }

        return $errors;
    }
    
    // UTILITY METHODS
    private function sanitizeProjectFormInput(array $source, array $current = []): array {
        $description = $source['description'] ?? ($source['full_description'] ?? ($current['description'] ?? ''));
        $status = $source['status'] ?? ($source['is_active'] ?? '');
        if (is_string($status)) {
            $status = strtolower(trim($status));
        }
        $status = $status === 'published' || $status === 'draft'
            ? $status
            : ((isset($source['is_active']) && $source['is_active']) ? 'published' : 'draft');
        
        return [
            'title' => trim($source['title'] ?? ($current['title'] ?? '')),
            'short_description' => trim($source['short_description'] ?? ($current['short_description'] ?? '')),
            'description' => trim($description),
            'category' => trim($source['category'] ?? ($current['category'] ?? '')),
            'client' => trim($source['client'] ?? ($current['client'] ?? '')),
            'client_visibility' => in_array(($source['client_visibility'] ?? ($current['client_visibility'] ?? 'yes')), ['yes', 'no'], true)
                ? ($source['client_visibility'] ?? ($current['client_visibility'] ?? 'yes'))
                : 'yes',
            'image_url' => trim($source['image_url'] ?? ($current['image_url'] ?? '')),
            'project_url' => trim($source['project_url'] ?? ($current['project_url'] ?? '')),
            'github_url' => trim($source['github_url'] ?? ($current['github_url'] ?? '')),
            'sort_order' => (int)($source['sort_order'] ?? ($current['sort_order'] ?? 0)),
            'featured' => isset($source['featured']) || isset($source['is_featured']) ? 1 : 0,
            'status' => $status,
            'technologies' => trim($source['technologies'] ?? ''),
            'gallery' => trim($source['gallery'] ?? ''),
            'slug' => trim($source['slug'] ?? ($current['slug'] ?? ''))
        ];
    }
    
    private function mapProjectFormToData(array $formInput, array $current = null): array {
        $technologies = $this->parseCommaList($formInput['technologies'] ?? '');
        $gallery = $this->parseTextareaList($formInput['gallery'] ?? '');
        
        $data = [
            'title' => $formInput['title'],
            'short_description' => $formInput['short_description'],
            'description' => $formInput['description'],
            'category' => $formInput['category'],
            'client' => $formInput['client'],
            'client_visibility' => $formInput['client_visibility'] ?? 'yes',
            'image_url' => $formInput['image_url'],
            'project_url' => $formInput['project_url'],
            'github_url' => $formInput['github_url'],
            'sort_order' => $formInput['sort_order'],
            'featured' => $formInput['featured'],
            'status' => $formInput['status'],
            'technologies' => !empty($technologies) ? json_encode($technologies) : null,
            'gallery' => !empty($gallery) ? json_encode($gallery) : null
        ];
        
        if (!empty($formInput['slug'])) {
            $data['slug'] = $this->sanitizeSlug($formInput['slug']);
        }
        
        return $data;
    }

    private function handleProjectImageInput(array $formInput, array $current = []): array {
        $errors = [];

        if (!empty($_FILES['image_file']['name']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            try {
                $currentUser = $this->userModel->getCurrentUser();
                $fileId = $this->mediaModel->uploadFile($_FILES['image_file'], $currentUser['id'] ?? null);
                $file = $this->mediaModel->find($fileId);
                if ($file && !empty($file['file_path'])) {
                    $formInput['image_url'] = $file['file_path'];
                }
            } catch (Exception $e) {
                $errors['image_url'] = 'Failed to upload image: ' . $e->getMessage();
            }
        } elseif (!empty($_FILES['image_file']['name']) && $_FILES['image_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $errors['image_url'] = 'Image upload failed. Please try again.';
        } elseif (!empty($current['image_url']) && empty($formInput['image_url'])) {
            $formInput['image_url'] = $current['image_url'];
        }

        return [$formInput, $errors];
    }
    
    private function sanitizeTestimonialInput(array $source, array $current = []): array {
        $rawToggle = $source['display_toggle'] ?? ($source['is_visible'] ?? null);

        if ($rawToggle === null) {
            if (isset($source['status'])) {
                $rawToggle = $source['status'];
            } elseif (isset($current['status'])) {
                $rawToggle = $current['status'];
            } else {
                $rawToggle = 1;
            }
        }

        $normalizedToggle = is_string($rawToggle) ? strtolower(trim($rawToggle)) : $rawToggle;
        $isVisible = !in_array((string)$normalizedToggle, ['0', 'off', 'false', 'no', 'draft'], true) && !empty($normalizedToggle);

        $status = $isVisible ? 'published' : 'draft';

        return [
            'client_name' => trim((string)($source['client_name'] ?? ($current['client_name'] ?? ''))),
            'client_position' => trim((string)($source['client_position'] ?? ($current['client_position'] ?? ''))),
            'client_company' => trim((string)($source['client_company'] ?? ($current['client_company'] ?? ''))),
            'testimonial_text' => trim((string)($source['testimonial_text'] ?? ($current['testimonial_text'] ?? ''))),
            'rating' => max(1, min(5, (int)($source['rating'] ?? ($current['rating'] ?? 5)))),
            'status' => $status,
            'image_path' => isset($source['existing_image_path']) && $source['existing_image_path'] !== ''
                ? trim((string)$source['existing_image_path'])
                : ($current['image_path'] ?? ''),
            'sort_order' => isset($source['sort_order']) ? (int)$source['sort_order'] : (int)($current['sort_order'] ?? 0)
        ];
    }

    private function validateTestimonialInput(array $data): array {
        $errors = [];

        if (mb_strlen($data['client_name']) < 2) {
            $errors[] = 'Client name must be at least 2 characters long.';
        }

        if (mb_strlen($data['testimonial_text']) < 12) {
            $errors[] = 'Testimonial text must be at least 12 characters long.';
        }

        if ($data['rating'] < 1 || $data['rating'] > 5) {
            $errors[] = 'Rating must be between 1 and 5 stars.';
        }

        return $errors;
    }

    private function handleTestimonialImageUpload(array $input, array $current = []): array {
        $errors = [];

        $fileField = $_FILES['client_image_file'] ?? null;
        $removeRequested = !empty($_POST['remove_client_image']);

        if ($removeRequested) {
            $input['image_path'] = '';
        }

        if (!$fileField || empty($fileField['name'])) {
            if (empty($input['image_path']) && !empty($current['image_path']) && !$removeRequested) {
                $input['image_path'] = $current['image_path'];
            }

            return [$input, $errors];
        }

        if ($fileField['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Image upload failed. Please try again.';
            return [$input, $errors];
        }

        try {
            $currentUser = $this->userModel->getCurrentUser();
            $fileId = $this->mediaModel->uploadFile($fileField, $currentUser['id'] ?? null);
            $file = $this->mediaModel->find($fileId);

            if ($file && !empty($file['file_path'])) {
                $input['image_path'] = $file['file_path'];
            }
        } catch (Exception $exception) {
            $errors[] = 'Failed to upload image: ' . $exception->getMessage();
        }

        return [$input, $errors];
    }

    private function formatTestimonialForManager(array $testimonial, string $fallbackImage): array {
        $imagePath = $testimonial['image_path'] ?? '';
        $imageUrl = $imagePath ? media_url($imagePath) : $fallbackImage;

        $position = trim(($testimonial['client_position'] ?? '') ?: '');
        $company = trim(($testimonial['client_company'] ?? '') ?: '');
        $meta = trim($position . ($position && $company ? ' · ' : '') . $company);

        return [
            'id' => (int)($testimonial['id'] ?? 0),
            'client_name' => $testimonial['client_name'] ?? '',
            'client_position' => $testimonial['client_position'] ?? '',
            'client_company' => $testimonial['client_company'] ?? '',
            'testimonial_text' => $testimonial['testimonial_text'] ?? '',
            'rating' => (int)($testimonial['rating'] ?? 5),
            'is_visible' => ($testimonial['status'] ?? '') === 'published' ? 1 : 0,
            'status' => $testimonial['status'] ?? (($testimonial['is_visible'] ?? 0) ? 'published' : 'draft'),
            'sort_order' => (int)($testimonial['sort_order'] ?? 0),
            'image_path' => $imagePath,
            'image_url' => $imageUrl,
            'meta' => $meta,
            'created_at' => $testimonial['created_at'] ?? null,
            'updated_at' => $testimonial['updated_at'] ?? null
        ];
    }

    private function getTestimonialPlaceholderImage(): string {
        return asset('images/placeholder.svg');
    }

    private function parseJsonInput(): array {
        $payloadRaw = file_get_contents('php://input');
        if ($payloadRaw === false || trim($payloadRaw) === '') {
            return [];
        }

        $decoded = json_decode($payloadRaw, true);
        return is_array($decoded) ? $decoded : [];
    }
    
    private function parseTextareaList(string $input): array {
        $items = preg_split('/\r\n|\r|\n/', trim($input));
        if ($items === false) {
            return [];
        }
        
        $items = array_map('trim', $items);
        return array_values(array_filter($items, static fn($item) => $item !== ''));
    }
    
    private function parseCommaList(string $input): array {
        $items = array_map('trim', explode(',', $input));
        return array_values(array_filter($items, static fn($item) => $item !== ''));
    }
    
    private function sanitizeSlug(string $slug): string {
        $slug = strtolower(trim($slug));
        $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}
