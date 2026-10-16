<?php

trait HomeCtaActions {
    // HOME CTA MANAGEMENT
    public function homePageCta() {
        $this->userModel->requireAuth();

        $page = $this->ensureHomePage();
        $sections = $this->decodePageSections($page['sections'] ?? []);
        $defaults = $this->getDefaultHomePageCta();

        $rawCta = isset($sections['page_cta']) && is_array($sections['page_cta'])
            ? $sections['page_cta']
            : [];

        $cta = $this->sanitizeHomePageCtaInput($rawCta, true);
        $cta = array_merge($defaults, array_intersect_key($cta, $defaults));

        $initialJson = json_encode($cta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $data = [
            'title' => 'Home Page CTA - Admin',
            'page' => 'admin-home-cta',
            'cta_form' => $cta,
            'cta_preview' => $cta,
            'cta_routes' => $this->getInternalRouteOptions(),
            'cta_initial_json' => $initialJson,
            'cta_update_url' => url('admin/home/page-cta')
        ];

        $this->render('admin/home-cta', 'admin', $data);
    }

    public function updateHomePageCta() {
        $this->userModel->requireAuth();

        $isAjax = $this->isAjaxRequest();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid request method.'
                ], 405);
                return;
            }

            $this->redirect('admin/home/page-cta');
            return;
        }

        $input = $this->sanitizeHomePageCtaInput($_POST, false);
        $errors = $this->validateHomePageCtaInput($input);

        if (!empty($errors)) {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Please fix the highlighted errors.',
                    'errors' => $errors
                ], 422);
                return;
            }

            $this->setFlash('error', implode(' ', $errors));
            $this->redirect('admin/home/page-cta');
            return;
        }

        $page = $this->ensureHomePage();
        $sections = $this->decodePageSections($page['sections'] ?? []);
        $sections['page_cta'] = $input;

        $payload = [
            'sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        ];

        $updated = $this->pageModel->update($page['id'], $payload);

        if ($updated) {
            if ($isAjax) {
                $this->json([
                    'success' => true,
                    'message' => 'Homepage CTA updated successfully.',
                    'cta' => $input
                ]);
                return;
            }

            $this->setFlash('success', 'Homepage CTA updated successfully.');
            $this->redirect('admin/home/page-cta');
            return;
        }

        if ($isAjax) {
            $this->json([
                'success' => false,
                'message' => 'Failed to update homepage CTA. Please try again.'
            ], 500);
            return;
        }

        $this->setFlash('error', 'Failed to update homepage CTA.');
        $this->redirect('admin/home/page-cta');
    }

    public function storeHeroStat() {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/hero');
            return;
        }

        $label = trim($_POST['label'] ?? '');
        $value = trim($_POST['value'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($label === '' || $value === '') {
            $this->setFlash('error', 'Both label and value are required for hero stats.');
            $this->redirect('admin/hero');
            return;
        }

        $hero = $this->ensureHeroSection();
        $heroId = (int)($hero['id'] ?? 1);
        $sortOrder = $this->heroStatModel->getMaxSortOrder($heroId) + 1;

        $created = $this->heroStatModel->create([
            'hero_id' => $heroId,
            'label' => $label,
            'value' => $value,
            'is_active' => $isActive,
            'sort_order' => $sortOrder
        ]);

        if ($created) {
            $this->setFlash('success', 'Hero stat added successfully.');
        } else {
            $this->setFlash('error', 'Failed to add hero stat.');
        }

        $this->redirect('admin/hero');
    }

    public function updateHeroStat($id) {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid request method.'
                ], 405);
            }

            $this->redirect('admin/hero');
            return;
        }

        $isAjax = $this->isAjaxRequest();

        $stat = $this->heroStatModel->find($id);
        if (!$stat) {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Hero stat not found.'
                ], 404);
            }

            $this->setFlash('error', 'Hero stat not found.');
            $this->redirect('admin/hero');
            return;
        }

        $label = trim($_POST['label'] ?? '');
        $value = trim($_POST['value'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($label === '' || $value === '') {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Both label and value are required for hero stats.'
                ], 422);
            }

            $this->setFlash('error', 'Both label and value are required for hero stats.');
            $this->redirect('admin/hero');
            return;
        }

        $payload = [
            'label' => $label,
            'value' => $value,
            'is_active' => $isActive
        ];

        $updated = $this->heroStatModel->update($stat['id'], $payload);

        if ($updated) {
            if ($isAjax) {
                $freshStat = $this->heroStatModel->find($stat['id']);

                $this->json([
                    'success' => true,
                    'message' => 'Hero stat updated successfully.',
                    'stat' => [
                        'id' => (int)($freshStat['id'] ?? $stat['id']),
                        'label' => $freshStat['label'] ?? $payload['label'],
                        'value' => $freshStat['value'] ?? $payload['value'],
                        'is_active' => (int)($freshStat['is_active'] ?? $payload['is_active']),
                        'sort_order' => (int)($freshStat['sort_order'] ?? ($stat['sort_order'] ?? 0)),
                        'update_url' => url('admin/hero/stats/' . ((int)($freshStat['id'] ?? $stat['id'])) . '/edit'),
                        'delete_url' => url('admin/hero/stats/' . ((int)($freshStat['id'] ?? $stat['id'])) . '/delete')
                    ]
                ]);
            }

            $this->setFlash('success', 'Hero stat updated successfully.');
        } else {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to update hero stat.'
                ], 500);
            }

            $this->setFlash('error', 'Failed to update hero stat.');
        }

        $this->redirect('admin/hero');
    }

    public function deleteHeroStat($id) {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/hero');
            return;
        }

        $isAjax = $this->isAjaxRequest();
        $stat = $this->heroStatModel->find($id);
        if (!$stat) {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Hero stat not found.'
                ], 404);
                return;
            }

            $this->setFlash('error', 'Hero stat not found.');
            $this->redirect('admin/hero');
            return;
        }

        if ($this->heroStatModel->delete($stat['id'])) {
            if ($isAjax) {
                $this->json([
                    'success' => true,
                    'message' => 'Hero stat deleted successfully.'
                ]);
                return;
            }

            $this->setFlash('success', 'Hero stat deleted successfully.');
        } else {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to delete hero stat.'
                ], 500);
                return;
            }

            $this->setFlash('error', 'Failed to delete hero stat.');
        }

        $this->redirect('admin/hero');
    }

    public function reorderHeroStats() {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/hero');
            return;
        }

        $orderInput = trim($_POST['order'] ?? '');
        if ($orderInput === '') {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => 'No order changes were detected.'
                ], 422);
                return;
            }

            $this->setFlash('error', 'No order changes were detected.');
            $this->redirect('admin/hero');
            return;
        }

        $ids = array_values(array_filter(array_map(function ($value) {
            return (int)trim($value);
        }, explode(',', $orderInput)), function ($value) {
            return $value > 0;
        }));

        if (empty($ids)) {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid order data supplied.'
                ], 422);
                return;
            }

            $this->setFlash('error', 'Invalid order data supplied.');
            $this->redirect('admin/hero');
            return;
        }

        $hero = $this->ensureHeroSection();
        $heroId = (int)($hero['id'] ?? 1);
        $position = 1;

        foreach ($ids as $statId) {
            $stat = $this->heroStatModel->find($statId);
            if ($stat && (int)$stat['hero_id'] === $heroId) {
                $this->heroStatModel->updateSort($statId, $position++);
            }
        }

        if ($this->isAjaxRequest()) {
            $this->json([
                'success' => true,
                'message' => 'Hero stats order updated successfully.'
            ]);
            return;
        }

        $this->setFlash('success', 'Hero stats order updated successfully.');
        $this->redirect('admin/hero');
    }
    
    private function processHeroImageUpload(): ?string {
        if (empty($_FILES['hero_background_image_file']['name'])) {
            return null;
        }

        $fileError = $_FILES['hero_background_image_file']['error'];
        if ($fileError === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($fileError !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Hero image upload failed. Please try again.');
        }

        $currentUser = $this->userModel->getCurrentUser();
        $fileId = $this->mediaModel->uploadFile(
            $_FILES['hero_background_image_file'],
            $currentUser['id'] ?? null
        );

        $file = $this->mediaModel->find($fileId);
        if (!$file || empty($file['file_path'])) {
            throw new RuntimeException('Hero image upload failed. File path missing.');
        }

        return $file['file_path'];
    }

    private function ensureHeroSection(): array {
        $hero = $this->heroSectionModel->getActive();
        if ($hero) {
            return $hero;
        }

        $defaults = [
            'hero_intro_prefix' => "Hi, I'm",
            'hero_intro_name_first' => '',
            'hero_intro_name_rest' => '',
            'hero_intro_suffix' => '',
            'hero_title' => 'Creative UI/UX Designer',
            'hero_subtitle' => '',
            'hero_description' => '',
            'hero_primary_cta_text' => 'View My Work',
            'hero_primary_cta_url' => '/portfolio',
            'hero_secondary_cta_text' => "Let's Talk",
            'hero_secondary_cta_url' => '/contact',
            'hero_background_image_path' => '',
            'hero_background_image_alt' => 'Portrait',
            'scroll_indicator_text' => 'Scroll to explore'
        ];

        $this->heroSectionModel->updateActive($defaults);
        $hero = $this->heroSectionModel->getActive();

        return $hero ?: $defaults;
    }

    private function getDefaultHomePageCta(): array {
        return [
            'title' => 'Ready to Start Your Project?',
            'subtitle' => "Let's partner to build inclusive, outcome-driven experiences.",
            'primary_cta_text' => 'Get In Touch',
            'primary_cta_url' => '/contact',
            'secondary_cta_text' => 'View Services',
            'secondary_cta_url' => '/services'
        ];
    }

    private function sanitizeHomePageCtaInput(array $source, bool $useDefaults = false): array {
        $defaults = $this->getDefaultHomePageCta();

        $title = trim((string)($source['title'] ?? ($useDefaults ? $defaults['title'] : '')));
        if ($title === '' && $useDefaults) {
            $title = $defaults['title'];
        }
        $title = mb_substr($title, 0, 150);

        $subtitle = trim((string)($source['subtitle'] ?? ($useDefaults ? $defaults['subtitle'] : '')));
        $subtitle = mb_substr($subtitle, 0, 240);

        $primaryText = trim((string)($source['primary_cta_text'] ?? ($useDefaults ? $defaults['primary_cta_text'] : '')));
        if ($primaryText === '' && $useDefaults) {
            $primaryText = $defaults['primary_cta_text'];
        }
        $primaryText = mb_substr($primaryText, 0, 80);

        $primaryUrlRaw = (string)($source['primary_cta_url'] ?? ($useDefaults ? $defaults['primary_cta_url'] : ''));
        $primaryUrl = $this->sanitizeCtaUrl($primaryUrlRaw);
        if ($primaryUrl === '' && $useDefaults) {
            $primaryUrl = $this->sanitizeCtaUrl($defaults['primary_cta_url']);
        }

        $secondaryText = trim((string)($source['secondary_cta_text'] ?? ($useDefaults ? $defaults['secondary_cta_text'] : '')));
        $secondaryText = mb_substr($secondaryText, 0, 80);

        $secondaryUrlRaw = (string)($source['secondary_cta_url'] ?? ($useDefaults ? $defaults['secondary_cta_url'] : ''));
        $secondaryUrl = $this->sanitizeCtaUrl($secondaryUrlRaw);
        if ($secondaryText === '') {
            $secondaryUrl = '';
        }

        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'primary_cta_text' => $primaryText,
            'primary_cta_url' => $primaryUrl,
            'secondary_cta_text' => $secondaryText,
            'secondary_cta_url' => $secondaryUrl
        ];
    }

    private function sanitizeCtaUrl(string $value): string {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $value = preg_replace('/\s+/', '', $value);
        if ($value === '') {
            return '';
        }

        if ($this->urlLooksExternal($value)) {
            return mb_substr($value, 0, 255);
        }

        if ($value[0] !== '/') {
            $value = '/' . ltrim($value, '/');
        }

        return mb_substr($value, 0, 255);
    }

    private function urlLooksExternal(string $url): bool {
        return (bool)preg_match('/^(https?:\/\/|mailto:|tel:)/i', $url);
    }

    private function validateHomePageCtaInput(array $input): array {
        $errors = [];

        if ($input['title'] === '') {
            $errors[] = 'Title is required.';
        }

        if ($input['primary_cta_text'] === '') {
            $errors[] = 'Primary CTA label is required.';
        }

        if ($input['primary_cta_url'] === '') {
            $errors[] = 'Primary CTA URL is required.';
        } elseif ($this->urlLooksExternal($input['primary_cta_url']) && !filter_var($input['primary_cta_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Primary CTA URL must be a valid URL.';
        }

        if ($input['secondary_cta_text'] !== '' && $input['secondary_cta_url'] === '') {
            $errors[] = 'Secondary CTA URL is required when the label is provided.';
        }

        if ($input['secondary_cta_url'] !== '' && $input['secondary_cta_text'] === '') {
            $errors[] = 'Secondary CTA label is required when a URL is provided.';
        }

        if ($input['secondary_cta_url'] !== '' && $this->urlLooksExternal($input['secondary_cta_url']) && !filter_var($input['secondary_cta_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Secondary CTA URL must be a valid URL.';
        }

        return $errors;
    }

    private function getInternalRouteOptions(): array {
        return [
            ['value' => '', 'label' => 'Select internal route…'],
            ['value' => '/contact', 'label' => 'Contact'],
            ['value' => '/services', 'label' => 'Services'],
            ['value' => '/portfolio', 'label' => 'Portfolio'],
            ['value' => '/about', 'label' => 'About'],
            ['value' => '/#testimonials', 'label' => 'Homepage Testimonials']
        ];
    }

    private function buildHeroPreviewFallback(array $heroForm, array $heroStats): array {
        $hero = $heroForm;

        $hero['hero_background_image_url'] = $heroForm['hero_background_image_path']
            ? media_url($heroForm['hero_background_image_path'])
            : asset('images/placeholder.svg');

        $hero['hero_primary_cta'] = [
            'text' => $heroForm['hero_primary_cta_text'] ?: 'View My Work',
            'url' => navbar_build_nav_url($heroForm['hero_primary_cta_url'] ?: '/portfolio')
        ];

        $hero['hero_secondary_cta'] = [
            'text' => $heroForm['hero_secondary_cta_text'] ?: "Let's Talk",
            'url' => navbar_build_nav_url($heroForm['hero_secondary_cta_url'] ?: '/contact')
        ];

        $hero['hero_stats'] = array_values(array_map(function (array $stat) {
            return [
                'id' => $stat['id'] ?? null,
                'label' => $stat['label'] ?? '',
                'value' => $stat['value'] ?? '',
                'is_active' => (int)($stat['is_active'] ?? 0)
            ];
        }, array_filter($heroStats, function (array $stat) {
            return (int)($stat['is_active'] ?? 0) === 1;
        })));

        $hero['scroll_indicator_text'] = $heroForm['scroll_indicator_text'] ?? 'Scroll to explore';

        return $hero;
    }

    private function isAjaxRequest(): bool {
        $requestedWith = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        if ($requestedWith === 'xmlhttprequest') {
            return true;
        }

        $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
        return stripos($acceptHeader, 'application/json') !== false;
    }

    private function ensureHomePage(): array {
        $page = $this->pageModel->getByKey('home');
        if ($page) {
            return $page;
        }

        $defaultSections = [
            'hero' => [
                'title' => 'Designing outcomes, not just screens',
                'subtitle' => 'Partnering with product teams to ship accessible, data-informed experiences.'
            ],
            'featured_work' => [
                'section_title' => 'Featured Work',
                'section_subtitle' => 'Selected projects that highlight strategy, craft, and measurable outcomes.'
            ],
            'services_preview' => [
                'section_title' => 'Core Services',
                'section_subtitle' => 'Engagement models built to meet teams where they are.'
            ],
            'skills_tools' => [
                'section_title' => 'Skills & Tools',
                'section_subtitle' => 'Capabilities that keep research, design, and delivery in sync.'
            ],
            'testimonials' => [
                'section_title' => 'What Clients Say',
                'section_subtitle' => 'Leaders who trust the collaborative approach to product design.'
            ],
            'page_cta' => [
                'title' => 'Ready to Start Your Project?',
                'subtitle' => "Let's partner to build inclusive, outcome-driven experiences.",
                'primary_cta_text' => 'Get In Touch',
                'primary_cta_url' => '/contact',
                'secondary_cta_text' => 'View Services',
                'secondary_cta_url' => '/services'
            ]
        ];

        $payload = [
            'page_key' => 'home',
            'title' => 'Home - Portfolio',
            'meta_description' => '',
            'content' => '',
            'sections' => json_encode($defaultSections),
            'is_active' => 1
        ];

        $pageId = $this->pageModel->create($payload);
        $page = $this->pageModel->find($pageId);

        return $page ?: $payload;
    }

    private function ensureAboutPage(): array {
        $page = $this->pageModel->getByKey('about');
        if ($page) {
            return $page;
        }

        $defaultSections = [
            'about_greeting' => 'Hello, I\'m Yahya',
            'about_headline' => 'Designing with empathy, strategy, and measurable results',
            'about_bio' => 'I\'m a product-focused UI/UX designer who partners with ambitious teams to translate complex problems into inclusive experiences.',
            'about_philosophy' => 'Great design balances craft with measurable outcomes. My process blends research, rapid iteration, and storytelling to help teams ship with confidence.',
            'about_image_path' => '',
            'about_image_alt' => 'Portrait photograph',
            'about_highlights' => [
                ['text' => '8+ years leading product and UX initiatives'],
                ['text' => 'Shipped 40+ products across SaaS, healthcare, and fintech'],
                ['text' => 'Mentor and speaker on inclusive design practices']
            ],
            'timeline' => [
                'title' => 'Experience & Education',
                'subtitle' => 'A journey of experimentation, leadership, and continuous learning.'
            ]
        ];

        $defaultSections['about_title'] = $defaultSections['about_headline'];
        $defaultSections['about_subtitle'] = $defaultSections['about_greeting'];
        $defaultSections['about_content'] = $defaultSections['about_bio'];

        $payload = [
            'page_key' => 'about',
            'title' => 'About - Portfolio',
            'meta_description' => '',
            'content' => '',
            'sections' => json_encode($defaultSections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'is_active' => 1
        ];

        $pageId = $this->pageModel->create($payload);
        $page = $this->pageModel->find($pageId);

        return $page ?: $payload;
    }

    private function decodePageSections($raw): array {
        if (is_array($raw)) {
            return $raw;
        }

        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return json_last_error() === JSON_ERROR_NONE ? (array)$decoded : [];
    }

    private function normalizeAboutHighlights($raw): array {
        if (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $raw = $decoded;
            } else {
                $raw = array_map('trim', explode(',', $raw));
            }
        }

        if (!is_array($raw)) {
            return [];
        }

        $normalized = [];
        foreach ($raw as $item) {
            if (is_string($item)) {
                $text = trim($item);
            } elseif (is_array($item)) {
                $text = trim((string)($item['text'] ?? ''));
            } else {
                $text = '';
            }

            if ($text !== '') {
                $normalized[] = ['text' => $text];
            }
        }

        return $normalized;
    }

    private function normalizeTimelineMeta($raw): array {
        if (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $raw = $decoded;
            }
        }

        if (!is_array($raw)) {
            $raw = [];
        }

        return [
            'title' => trim((string)($raw['title'] ?? 'Experience & Education')),
            'subtitle' => trim((string)($raw['subtitle'] ?? ''))
        ];
    }

    private function buildAboutPreviewFallback(array $form, array $timelineItems): array {
        $imagePath = $form['about_image_path'] ?? '';
        $imageUrl = $imagePath ? media_url($imagePath) : asset('images/placeholder.svg');

        return [
            'greeting' => $form['about_greeting'] ?? '',
            'headline' => $form['about_headline'] ?? 'About Me',
            'bio' => $form['about_bio'] ?? '',
            'philosophy' => $form['about_philosophy'] ?? '',
            'image_url' => $imageUrl,
            'image_alt' => $form['about_image_alt'] ?? 'Portrait',
            'timeline_title' => $form['timeline_title'] ?? 'Experience & Education',
            'timeline_subtitle' => $form['timeline_subtitle'] ?? '',
            'timeline_items' => $timelineItems
        ];
    }

    private function processAboutImageUpload(): ?string {
        if (empty($_FILES['about_image_file']['name'])) {
            return null;
        }

        $fileError = $_FILES['about_image_file']['error'];
        if ($fileError === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($fileError !== UPLOAD_ERR_OK) {
            throw new RuntimeException('About image upload failed. Please try again.');
        }

        $currentUser = $this->userModel->getCurrentUser();
        $fileId = $this->mediaModel->uploadFile(
            $_FILES['about_image_file'],
            $currentUser['id'] ?? null
        );

        $file = $this->mediaModel->find($fileId);
        if (!$file || empty($file['file_path'])) {
            throw new RuntimeException('About image upload failed. File path missing.');
        }

        return $file['file_path'];
    }

    private function normalizeTimelineTags($raw): array {
        if (is_string($raw)) {
            $trimmed = trim($raw);
            if ($trimmed === '') {
                return [];
            }

            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $raw = $decoded;
            } else {
                $raw = explode(',', $trimmed);
            }
        }

        if (!is_array($raw)) {
            return [];
        }

        $tags = [];
        foreach ($raw as $value) {
            if (is_array($value)) {
                $value = $value['text'] ?? ($value['label'] ?? '');
            }

            $label = trim((string)$value);
            if ($label === '') {
                continue;
            }

            $tags[] = mb_substr($label, 0, 80);

            if (count($tags) >= 10) {
                break;
            }
        }

        return $tags;
    }

    private function transformTimelineItem(array $item): array {
        $item['id'] = (int)($item['id'] ?? 0);
        $item['sort_order'] = (int)($item['sort_order'] ?? 0);
        $item['is_education'] = (int)($item['is_education'] ?? 0);

        $status = strtolower(trim((string)($item['status'] ?? 'draft')));
        if (!array_key_exists($status, self::TIMELINE_STATUSES)) {
            $status = 'draft';
        }
        $item['status'] = $status;

        $item['tags'] = $this->normalizeTimelineTags($item['tags'] ?? []);

        return $item;
    }

    private function groupTimelineByType(array $items): array {
        $groups = [
            'experience' => [],
            'education' => []
        ];

        foreach ($items as $item) {
            $key = !empty($item['is_education']) ? 'education' : 'experience';
            $groups[$key][] = $item;
        }

        return $groups;
    }

    private function buildTimelineFormDefaults(array $overrides = []): array {
        $defaults = [
            'title' => '',
            'organization' => '',
            'date_range' => '',
            'description' => '',
            'tags' => '',
            'status' => 'published',
            'is_education' => 0,
            'sort_order' => $this->getNextTimelineSortOrder()
        ];

        return array_merge($defaults, $overrides);
    }

    private function getNextTimelineSortOrder(): int {
        return $this->timelineModel->getMaxSortOrder() + 1;
    }

    private function collectTimelineFormInput(): array {
        $rawTags = $_POST['tags'] ?? '';
        $tagsString = is_array($rawTags) ? implode(', ', $rawTags) : (string)$rawTags;
        $tags = $this->normalizeTimelineTags($rawTags);

        $status = strtolower(trim((string)($_POST['status'] ?? 'published')));
        if (!array_key_exists($status, self::TIMELINE_STATUSES)) {
            $status = 'draft';
        }

        $sortOrderRaw = trim((string)($_POST['sort_order'] ?? ''));
        $sortOrder = $sortOrderRaw === '' ? null : (int)$sortOrderRaw;
        if ($sortOrder !== null && $sortOrder < 0) {
            $sortOrder = 0;
        }

        return [
            'title' => trim((string)($_POST['title'] ?? '')),
            'organization' => trim((string)($_POST['organization'] ?? '')),
            'date_range' => trim((string)($_POST['date_range'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'is_education' => isset($_POST['is_education']) ? 1 : 0,
            'status' => $status,
            'sort_order' => $sortOrder,
            'tags' => $tags,
            'tags_input' => $tagsString
        ];
    }

    private function validateTimelineInput(array $input): array {
        $errors = [];

        if ($input['title'] === '') {
            $errors[] = 'Title is required.';
        } elseif (mb_strlen($input['title']) > 200) {
            $errors[] = 'Title must be 200 characters or fewer.';
        }

        if ($input['organization'] !== '' && mb_strlen($input['organization']) > 200) {
            $errors[] = 'Organization must be 200 characters or fewer.';
        }

        if ($input['date_range'] !== '' && mb_strlen($input['date_range']) > 120) {
            $errors[] = 'Date range must be 120 characters or fewer.';
        }

        if ($input['description'] !== '' && mb_strlen($input['description']) > 1000) {
            $errors[] = 'Description must be 1000 characters or fewer.';
        }

        if (count($input['tags']) > 10) {
            $errors[] = 'Please limit tags to 10 or fewer.';
        }

        return $errors;
    }

    private function handleTimelineFormFailure(array $input, array $errors, string $redirectPath): void {
        $this->setFlash('error', 'Please correct the highlighted issues.');
        if (!empty($errors)) {
            $this->setFlash('form_errors', $errors);
        }

        $this->setFlash('form_data', [
            'title' => $input['title'],
            'organization' => $input['organization'],
            'date_range' => $input['date_range'],
            'description' => $input['description'],
            'tags' => $input['tags_input'],
            'is_education' => $input['is_education'],
            'status' => $input['status'],
            'sort_order' => $input['sort_order'] ?? ''
        ]);

        $this->redirect($redirectPath);
    }

    private function persistTimelineItem(int $id = 0, array $input = []): bool {
        $payload = [
            'title' => $input['title'],
            'organization' => $input['organization'] !== '' ? $input['organization'] : null,
            'date_range' => $input['date_range'] !== '' ? $input['date_range'] : null,
            'description' => $input['description'] !== '' ? $input['description'] : null,
            'tags' => !empty($input['tags']) ? json_encode($input['tags'], JSON_UNESCAPED_UNICODE) : null,
            'is_education' => (int)$input['is_education'],
            'sort_order' => (int)($input['sort_order'] ?? 0),
            'status' => $input['status']
        ];

        if ($id > 0) {
            return $this->timelineModel->update($id, $payload);
        }

        return $this->timelineModel->create($payload) > 0;
    }
}
