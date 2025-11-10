<?php

require_once ROOT_PATH . '/app/core/BaseController.php';
require_once ROOT_PATH . '/app/models/User.php';
require_once ROOT_PATH . '/app/models/Message.php';
require_once ROOT_PATH . '/app/models/Setting.php';
require_once ROOT_PATH . '/app/models/Page.php';
require_once ROOT_PATH . '/app/models/Service.php';
require_once ROOT_PATH . '/app/models/ServiceFeature.php';
require_once ROOT_PATH . '/app/models/ServiceProcessStep.php';
require_once ROOT_PATH . '/app/models/PricingPlan.php';
require_once ROOT_PATH . '/app/models/Project.php';
require_once ROOT_PATH . '/app/models/NavigationMenu.php';
require_once ROOT_PATH . '/app/models/Media.php';
require_once ROOT_PATH . '/app/models/Testimonial.php';
require_once ROOT_PATH . '/app/models/Skill.php';
require_once ROOT_PATH . '/app/models/HeroSection.php';
require_once ROOT_PATH . '/app/models/HeroStat.php';
require_once ROOT_PATH . '/app/models/TimelineItem.php';
require_once ROOT_PATH . '/app/models/FeaturedProject.php';

class AdminManagerController extends BaseController {
    private const TIMELINE_STATUSES = [
        'published' => 'Published',
        'draft' => 'Draft'
    ];

    private $userModel;
    private $messageModel;
    private $settingModel;
    private $pageModel;
    private $serviceModel;
    private $serviceFeatureModel;
    private $serviceProcessModel;
    private $pricingPlanModel;
    private $projectModel;
    private $navigationModel;
    private $mediaModel;
    private $testimonialModel;
    private $skillModel;
    private $heroSectionModel;
    private $heroStatModel;
    private $timelineModel;
    private $featuredProjectModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->messageModel = new Message();
        $this->settingModel = new Setting();
        $this->pageModel = new Page();
        $this->serviceModel = new Service();
        $this->serviceFeatureModel = new ServiceFeature();
        $this->serviceProcessModel = new ServiceProcessStep();
    $this->pricingPlanModel = new PricingPlan();
        $this->projectModel = new Project();
        $this->navigationModel = new NavigationMenu();
        $this->mediaModel = new Media();
        $this->testimonialModel = new Testimonial();
        $this->skillModel = new Skill();
        $this->heroSectionModel = new HeroSection();
        $this->heroStatModel = new HeroStat();
        $this->timelineModel = new TimelineItem();
        $this->featuredProjectModel = new FeaturedProject();
    }
    
    // SETTINGS MANAGEMENT
    public function settings() {
        $this->userModel->requireAuth();
        
        $settingGroups = $this->settingModel->getAllGroups();
        $settings = [];
        
        foreach ($settingGroups as $group) {
            $settings[$group] = $this->settingModel->getByGroup($group);
        }
        
        $data = [
            'title' => 'Site Settings - Admin',
            'page' => 'admin-settings',
            'setting_groups' => $settingGroups,
            'settings' => $settings
        ];
        
        $this->render('admin/settings', 'admin', $data);
    }
    
    public function updateSettings() {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/settings');
            return;
        }
        
        $settingsData = $_POST['settings'] ?? [];
        
        if ($this->settingModel->updateSettings($settingsData)) {
            $this->setFlash('success', 'Settings updated successfully!');
        } else {
            $this->setFlash('error', 'Failed to update settings.');
        }
        
        $this->redirect('admin/settings');
    }
    
    // HERO SECTION MANAGEMENT
    public function hero() {
        $this->userModel->requireAuth();

        $heroRecord = $this->ensureHeroSection();
        $heroFormDefaults = [
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

        $heroForm = array_merge($heroFormDefaults, $heroRecord);
        $heroId = (int)($heroRecord['id'] ?? 1);

        $heroStats = $this->heroStatModel->getByHero($heroId, false);

        $heroPreview = $this->contentRepository
            ? $this->contentRepository->getHeroData()
            : $this->buildHeroPreviewFallback($heroForm, $heroStats);

        $previewStats = array_map(function ($stat) {
            return [
                'id' => $stat['id'] ?? null,
                'label' => $stat['label'] ?? '',
                'value' => $stat['value'] ?? '',
                'is_active' => (int)($stat['is_active'] ?? 0)
            ];
        }, $heroStats);

        $data = [
            'title' => 'Hero Section - Admin',
            'page' => 'admin-hero',
            'hero_form' => $heroForm,
            'hero_preview' => $heroPreview,
            'hero_stats' => $heroStats,
            'hero_preview_stats' => $previewStats
        ];

        $this->render('admin/hero', 'admin', $data);
    }
    
    public function updateHero() {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/hero');
            return;
        }

        $heroRecord = $this->ensureHeroSection();
        $currentImagePath = $heroRecord['hero_background_image_path'] ?? '';

        $input = [
            'hero_intro_prefix' => trim($_POST['hero_intro_prefix'] ?? ''),
            'hero_intro_name_first' => trim($_POST['hero_intro_name_first'] ?? ''),
            'hero_intro_name_rest' => trim($_POST['hero_intro_name_rest'] ?? ''),
            'hero_intro_suffix' => trim($_POST['hero_intro_suffix'] ?? ''),
            'hero_title' => trim($_POST['hero_title'] ?? ''),
            'hero_subtitle' => trim($_POST['hero_subtitle'] ?? ''),
            'hero_description' => trim($_POST['hero_description'] ?? ''),
            'hero_primary_cta_text' => trim($_POST['hero_primary_cta_text'] ?? ''),
            'hero_primary_cta_url' => trim($_POST['hero_primary_cta_url'] ?? ''),
            'hero_secondary_cta_text' => trim($_POST['hero_secondary_cta_text'] ?? ''),
            'hero_secondary_cta_url' => trim($_POST['hero_secondary_cta_url'] ?? ''),
            'hero_background_image_path' => trim($_POST['hero_background_image_path'] ?? ''),
            'hero_background_image_alt' => trim($_POST['hero_background_image_alt'] ?? ''),
            'scroll_indicator_text' => trim($_POST['scroll_indicator_text'] ?? ''),
        ];

        try {
            $uploadedImagePath = $this->processHeroImageUpload();
        } catch (Exception $exception) {
            $this->setFlash('error', $exception->getMessage());
            $this->redirect('admin/hero');
            return;
        }
        if ($uploadedImagePath !== null) {
            $input['hero_background_image_path'] = $uploadedImagePath;
        } elseif ($input['hero_background_image_path'] === '' && $currentImagePath !== '') {
            $input['hero_background_image_path'] = $currentImagePath;
        }

        if ($this->heroSectionModel->updateActive($input)) {
            $this->setFlash('success', 'Hero section updated successfully!');
        } else {
            $this->setFlash('error', 'Failed to update hero section.');
        }

        $this->redirect('admin/hero');
    }

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
            : asset('images/hero-portrait.jpg');

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
        $imageUrl = $imagePath ? media_url($imagePath) : asset('images/about-photo.jpg');

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

    // ABOUT SECTION MANAGEMENT
    public function about() {
        $this->userModel->requireAuth();
        $page = $this->ensureAboutPage();
        $sections = $this->decodePageSections($page['sections'] ?? []);

        $timelineMeta = $this->normalizeTimelineMeta($sections['timeline'] ?? []);

        $aboutFormDefaults = [
            'about_greeting' => trim((string)($sections['about_greeting'] ?? $sections['about_subtitle'] ?? '')),
            'about_headline' => trim((string)($sections['about_headline'] ?? $sections['about_title'] ?? ($page['title'] ?? 'About Me'))),
            'about_bio' => trim((string)($sections['about_bio'] ?? $sections['about_content'] ?? ($page['content'] ?? ''))),
            'about_philosophy' => trim((string)($sections['about_philosophy'] ?? '')),
            'about_image_path' => trim((string)($sections['about_image_path'] ?? '')),
            'about_image_alt' => trim((string)($sections['about_image_alt'] ?? 'Portrait')),
            'timeline_title' => $timelineMeta['title'] ?? 'Experience & Education',
            'timeline_subtitle' => $timelineMeta['subtitle'] ?? ''
        ];

        $timelineItems = $this->contentRepository ? $this->contentRepository->getTimeline() : [];
        $aboutPreview = $this->buildAboutPreviewFallback($aboutFormDefaults, $timelineItems);

        $data = [
            'title' => 'About Section - Admin',
            'page' => 'admin-about',
            'about_form' => $aboutFormDefaults,
            'about_preview' => $aboutPreview,
            'timeline_items' => $timelineItems
        ];

        $this->render('admin/about', 'admin', $data);
    }
    
    public function updateAbout() {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/about');
            return;
        }

        $page = $this->ensureAboutPage();
        $sections = $this->decodePageSections($page['sections'] ?? []);

        $input = [
            'about_greeting' => trim($_POST['about_greeting'] ?? ''),
            'about_headline' => trim($_POST['about_headline'] ?? ''),
            'about_bio' => trim($_POST['about_bio'] ?? ''),
            'about_philosophy' => trim($_POST['about_philosophy'] ?? ''),
            'about_image_path' => trim($_POST['about_image_path'] ?? ''),
            'about_image_alt' => trim($_POST['about_image_alt'] ?? ''),
            'timeline_title' => trim($_POST['timeline_title'] ?? ''),
            'timeline_subtitle' => trim($_POST['timeline_subtitle'] ?? '')
        ];

        try {
            $uploadedImagePath = $this->processAboutImageUpload();
        } catch (Exception $exception) {
            $this->setFlash('error', $exception->getMessage());
            $this->redirect('admin/about');
            return;
        }

        if ($uploadedImagePath !== null) {
            $input['about_image_path'] = $uploadedImagePath;
        } elseif ($input['about_image_path'] === '' && !empty($sections['about_image_path'])) {
            $input['about_image_path'] = $sections['about_image_path'];
        }

        $sections['about_greeting'] = $input['about_greeting'];
        $sections['about_headline'] = $input['about_headline'];
        $sections['about_bio'] = $input['about_bio'];
        $sections['about_philosophy'] = $input['about_philosophy'];
        $sections['about_image_path'] = $input['about_image_path'];
        $sections['about_image_alt'] = $input['about_image_alt'];
        if (isset($sections['about_highlights'])) {
            unset($sections['about_highlights']);
        }

        // Maintain legacy keys for compatibility with existing views until fully migrated
        $sections['about_title'] = $input['about_headline'];
        $sections['about_subtitle'] = $input['about_greeting'];
        $sections['about_content'] = $input['about_bio'];

        $existingTimeline = $sections['timeline'] ?? [];
        if (!is_array($existingTimeline)) {
            $existingTimeline = [];
        }

        $existingTimeline['title'] = $input['timeline_title'];
        $existingTimeline['subtitle'] = $input['timeline_subtitle'];
        $sections['timeline'] = $existingTimeline;

        $updatePayload = [
            'content' => $input['about_bio'],
            'sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        ];

        $updated = $this->pageModel->update($page['id'], $updatePayload);

        if ($updated) {
            $this->setFlash('success', 'About section updated successfully!');
        } else {
            $this->setFlash('error', 'Failed to update about section.');
        }

        $this->redirect('admin/about');
    }

    public function featuredProjects() {
        $this->userModel->requireAuth();

        $allProjects = $this->featuredProjectModel->getAllProjects();
        $featuredProjects = $this->featuredProjectModel->getFeaturedProjects();

        $featuredIds = array_map(function ($project) {
            return (int)$project['id'];
        }, $featuredProjects);

        $serializedFeatured = array_map(function ($project) {
            return [
                'id' => (int)$project['id'],
                'title' => $project['title'] ?? '',
                'slug' => $project['slug'] ?? '',
                'short_description' => $project['short_description'] ?? '',
                'main_image_url' => !empty($project['main_image_path'])
                    ? media_url($project['main_image_path'])
                    : asset('images/projects/default.jpg'),
                'sort_order' => (int)($project['featured_sort_order'] ?? 0)
            ];
        }, $featuredProjects);

        $data = [
            'title' => 'Featured Work - Admin',
            'page' => 'admin-featured-projects',
            'projects' => $allProjects,
            'featured_projects' => $serializedFeatured,
            'featured_ids' => $featuredIds
        ];

        $this->render('admin/featured-projects', 'admin', $data);
    }

    public function updateFeaturedProjects() {
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

            $this->redirect('admin/featured-projects');
            return;
        }

        $featuredRaw = $_POST['featured'] ?? '';
        $projectIds = array_values(array_filter(array_map(function ($value) {
            return (int)trim($value);
        }, explode(',', (string)$featuredRaw)), function ($value) {
            return $value > 0;
        }));

        if (count($projectIds) < 3 || count($projectIds) > 6) {
            $message = 'Featured list must include between 3 and 6 projects.';

            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => $message
                ], 422);
                return;
            }

            $this->setFlash('error', $message);
            $this->redirect('admin/featured-projects');
            return;
        }

        try {
            $this->featuredProjectModel->updateFeaturedList($projectIds);
        } catch (Exception $exception) {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to update featured projects.'
                ], 500);
                return;
            }

            $this->setFlash('error', 'Failed to update featured projects.');
            $this->redirect('admin/featured-projects');
            return;
        }

        if ($isAjax) {
            $projects = $this->featuredProjectModel->getFeaturedProjects();
            $serialized = array_map(function ($project) {
                return [
                    'id' => (int)$project['id'],
                    'title' => $project['title'] ?? '',
                    'slug' => $project['slug'] ?? '',
                    'short_description' => $project['short_description'] ?? '',
                    'main_image_url' => !empty($project['main_image_path'])
                        ? media_url($project['main_image_path'])
                        : asset('images/projects/default.jpg'),
                    'sort_order' => (int)($project['featured_sort_order'] ?? 0)
                ];
            }, $projects);

            $this->json([
                'success' => true,
                'message' => 'Featured projects updated successfully.',
                'projects' => $serialized
            ]);
            return;
        }

        $this->setFlash('success', 'Featured projects updated successfully.');
        $this->redirect('admin/featured-projects');
    }

    public function servicesPreview() {
        $this->userModel->requireAuth();

        $allServices = $this->serviceModel->all('sort_order ASC, title ASC');
        $selectedServices = $this->serviceModel->getHomepageFeatured();

        $normalizedAll = array_map([$this, 'normalizeServiceForPreview'], $allServices);
        $normalizedSelected = array_map([$this, 'normalizeServiceForPreview'], $selectedServices);

        usort($normalizedSelected, function ($a, $b) {
            return ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0);
        });

        $selectedIds = array_map(function ($service) {
            return (int)$service['id'];
        }, $normalizedSelected);

        $available = array_values(array_filter($normalizedAll, function ($service) use ($selectedIds) {
            return !in_array((int)$service['id'], $selectedIds, true);
        }));

        $data = [
            'title' => 'Services Preview - Admin',
            'page' => 'admin-services-preview',
            'services' => $available,
            'selected_services' => $normalizedSelected,
            'all_services' => $normalizedAll,
            'services_min' => 3,
            'services_max' => 6
        ];

        $this->render('admin/services-preview', 'admin', $data);
    }

    public function updateServicesPreview() {
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

            $this->redirect('admin/services/preview');
            return;
        }

        $rawPayload = trim($_POST['services'] ?? '');
        if ($rawPayload === '') {
            $message = 'No services were provided.';
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => $message
                ], 422);
                return;
            }

            $this->setFlash('error', $message);
            $this->redirect('admin/services/preview');
            return;
        }

        $decoded = json_decode($rawPayload, true);
        if (!is_array($decoded)) {
            $message = 'Invalid services payload.';
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => $message
                ], 422);
                return;
            }

            $this->setFlash('error', $message);
            $this->redirect('admin/services/preview');
            return;
        }

        $services = [];
        foreach ($decoded as $item) {
            $id = (int)($item['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $visible = isset($item['visible']) ? (int)$item['visible'] : 1;
            $services[] = [
                'id' => $id,
                'visible' => $visible ? 1 : 0
            ];
        }

        if (count($services) < 3 || count($services) > 6) {
            $message = 'Please select between 3 and 6 services for the homepage.';

            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => $message
                ], 422);
                return;
            }

            $this->setFlash('error', $message);
            $this->redirect('admin/services/preview');
            return;
        }

        $visibleCount = 0;
        $existingIds = [];
        foreach ($services as $service) {
            $record = $this->serviceModel->find($service['id']);
            if (!$record) {
                $message = 'One or more selected services could not be found.';

                if ($isAjax) {
                    $this->json([
                        'success' => false,
                        'message' => $message
                    ], 404);
                    return;
                }

                $this->setFlash('error', $message);
                $this->redirect('admin/services/preview');
                return;
            }

            $existingIds[] = $service['id'];
            if ((int)$service['visible'] === 1) {
                $visibleCount += 1;
            }
        }

        if ($visibleCount < 3) {
            $message = 'At least 3 services must remain visible.';

            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => $message
                ], 422);
                return;
            }

            $this->setFlash('error', $message);
            $this->redirect('admin/services/preview');
            return;
        }

        try {
            $this->serviceModel->updateHomepageSelection($services);
        } catch (Exception $exception) {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to update homepage services.'
                ], 500);
                return;
            }

            $this->setFlash('error', 'Failed to update homepage services.');
            $this->redirect('admin/services/preview');
            return;
        }

        $freshServices = $this->serviceModel->getHomepageFeatured();
        $normalized = array_map([$this, 'normalizeServiceForPreview'], $freshServices);

        if ($isAjax) {
            $this->json([
                'success' => true,
                'message' => 'Homepage services updated successfully.',
                'services' => $normalized
            ]);
            return;
        }

        $this->setFlash('success', 'Homepage services updated successfully.');
        $this->redirect('admin/services/preview');
    }

    private function normalizeServiceForPreview($service) {
        if (!$service) {
            return [];
        }

        $description = strip_tags((string)($service['description'] ?? ''));
        $description = trim(preg_replace('/\s+/', ' ', $description));

        if ($description === '') {
            $summary = 'No description provided.';
        } else {
            $summary = str_limit($description, 140, '…');
        }

        $icon = trim((string)($service['icon'] ?? ''));

        return [
            'id' => (int)($service['id'] ?? 0),
            'title' => $service['title'] ?? 'Untitled Service',
            'summary' => $summary,
            'icon' => $icon,
            'status' => $service['status'] ?? 'draft',
            'visible' => (int)($service['homepage_is_visible'] ?? 1) === 1,
            'sort_order' => (int)($service['homepage_sort_order'] ?? 0),
            'featured' => (int)($service['homepage_featured'] ?? 0) === 1
        ];
    }

    // TIMELINE MANAGEMENT
    public function timeline() {
        $this->userModel->requireAuth();

        $records = $this->timelineModel->all('sort_order ASC, created_at DESC');
        $timelineItems = array_map(function ($record) {
            return $this->transformTimelineItem($record);
        }, $records);

        $orderValue = implode(',', array_map(function ($item) {
            return (string)$item['id'];
        }, $timelineItems));

        $publishedItems = array_values(array_filter($timelineItems, function ($item) {
            return $item['status'] === 'published';
        }));
        $draftItems = array_values(array_filter($timelineItems, function ($item) {
            return $item['status'] === 'draft';
        }));

        $previewSource = !empty($publishedItems) ? $publishedItems : $timelineItems;
        $previewItems = array_slice($previewSource, 0, 4);

        $groupedItems = $this->groupTimelineByType($timelineItems);
        $groupedPreview = $this->groupTimelineByType($previewSource);

        $itemsJson = json_encode($timelineItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($itemsJson)) {
            $itemsJson = '[]';
        }

        $data = [
            'title' => 'Timeline - Admin',
            'page' => 'admin-timeline',
            'timeline_items' => $timelineItems,
            'timeline_order_value' => $orderValue,
            'timeline_counts' => [
                'total' => count($timelineItems),
                'published' => count($publishedItems),
                'draft' => count($draftItems)
            ],
            'timeline_preview_items' => $previewItems,
            'timeline_statuses' => self::TIMELINE_STATUSES,
            'timeline_groups' => $groupedItems,
            'timeline_preview_groups' => $groupedPreview,
            'timeline_items_json' => $itemsJson,
            'timeline_group_counts' => [
                'experience' => count($groupedItems['experience'] ?? []),
                'education' => count($groupedItems['education'] ?? [])
            ],
            'timeline_reorder_url' => url('admin/timeline/reorder')
        ];

        $this->render('admin/timeline', 'admin', $data);
    }

    public function createTimelineItem() {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->collectTimelineFormInput();
            $errors = $this->validateTimelineInput($input);

            if (!empty($errors)) {
                $this->handleTimelineFormFailure($input, $errors, 'admin/timeline/create');
                return;
            }

            if ($input['sort_order'] === null) {
                $input['sort_order'] = $this->getNextTimelineSortOrder();
            }

            $created = $this->persistTimelineItem(0, $input);

            if ($created) {
                $this->setFlash('success', 'Timeline entry created successfully!');
            } else {
                $this->setFlash('error', 'Failed to create timeline entry.');
            }

            $this->redirect('admin/timeline');
            return;
        }

        $data = [
            'title' => 'Create Timeline Entry - Admin',
            'page' => 'admin-timeline',
            'timeline_defaults' => $this->buildTimelineFormDefaults(),
            'timeline_statuses' => self::TIMELINE_STATUSES
        ];

        $this->render('admin/timeline-form', 'admin', $data);
    }

    public function editTimelineItem($id) {
        $this->userModel->requireAuth();

        $record = $this->timelineModel->find($id);
        if (!$record) {
            $this->setFlash('error', 'Timeline entry not found.');
            $this->redirect('admin/timeline');
            return;
        }

        $timelineItem = $this->transformTimelineItem($record);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->collectTimelineFormInput();
            if ($input['sort_order'] === null) {
                $input['sort_order'] = $timelineItem['sort_order'];
            }

            $errors = $this->validateTimelineInput($input);
            if (!empty($errors)) {
                $this->handleTimelineFormFailure($input, $errors, 'admin/timeline/' . $timelineItem['id'] . '/edit');
                return;
            }

            $updated = $this->persistTimelineItem($timelineItem['id'], $input);

            if ($updated) {
                $this->setFlash('success', 'Timeline entry updated successfully!');
            } else {
                $this->setFlash('error', 'Failed to update timeline entry.');
            }

            $this->redirect('admin/timeline');
            return;
        }

        $formItem = $timelineItem;
        $formItem['tags'] = implode(', ', $timelineItem['tags']);

        $data = [
            'title' => 'Edit Timeline Entry - Admin',
            'page' => 'admin-timeline',
            'timeline_item' => $formItem,
            'timeline_defaults' => $this->buildTimelineFormDefaults([
                'sort_order' => $timelineItem['sort_order']
            ]),
            'timeline_statuses' => self::TIMELINE_STATUSES
        ];

        $this->render('admin/timeline-form', 'admin', $data);
    }

    public function deleteTimelineItem($id) {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/timeline');
            return;
        }

        $record = $this->timelineModel->find($id);
        if (!$record) {
            $this->setFlash('error', 'Timeline entry not found.');
            $this->redirect('admin/timeline');
            return;
        }

        if ($this->timelineModel->delete($record['id'])) {
            $this->setFlash('success', 'Timeline entry deleted successfully.');
        } else {
            $this->setFlash('error', 'Failed to delete timeline entry.');
        }

        $this->redirect('admin/timeline');
    }

    public function reorderTimeline() {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid request method.'
                ], 405);
                return;
            }

            $this->redirect('admin/timeline');
            return;
        }

        $orderInput = trim((string)($_POST['order'] ?? ''));
        if ($orderInput === '') {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => 'No order changes were detected.'
                ], 422);
                return;
            }

            $this->setFlash('error', 'No order changes were detected.');
            $this->redirect('admin/timeline');
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
            $this->redirect('admin/timeline');
            return;
        }

        $position = 1;
        foreach ($ids as $itemId) {
            $item = $this->timelineModel->find($itemId);
            if ($item) {
                $this->timelineModel->update($itemId, ['sort_order' => $position++]);
            }
        }

        if ($this->isAjaxRequest()) {
            $this->json([
                'success' => true,
                'message' => 'Timeline order updated successfully.',
                'order' => $ids
            ]);
            return;
        }

        $this->setFlash('success', 'Timeline order updated successfully.');
        $this->redirect('admin/timeline');
    }
    
    // SERVICES MANAGEMENT
    public function services() {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleServicesStructureSave();
            return;
        }

        $services = $this->serviceModel->getAllWithFeatures(true);
        $normalized = array_map([$this, 'formatServiceForManager'], $services);

        $data = [
            'title' => 'Services Management - Admin',
            'page' => 'admin-services',
            'services' => $normalized
        ];

        $this->render('admin/services', 'admin', $data);
    }

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
    
    // FOOTER MANAGEMENT
    public function footer() {
        $this->userModel->requireAuth();
        
        $footerSettings = $this->settingModel->getByGroup('footer');
        $footerData = [];
        
        foreach ($footerSettings as $setting) {
            $footerData[$setting['setting_key']] = $setting['setting_value'];
        }
        
        $data = [
            'title' => 'Footer Management - Admin',
            'page' => 'admin-footer',
            'footer_data' => $footerData
        ];
        
        $this->render('admin/footer', 'admin', $data);
    }
    
    public function updateFooter() {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/footer');
            return;
        }
        
        $footerData = $_POST;
        unset($footerData['csrf_token']);
        
        if ($this->settingModel->updateSettings($footerData)) {
            $this->setFlash('success', 'Footer updated successfully!');
        } else {
            $this->setFlash('error', 'Failed to update footer.');
        }
        
        $this->redirect('admin/footer');
    }
    
    // NAVIGATION MENU MANAGEMENT
    public function navigation() {
        $this->userModel->requireAuth();
        
        $menuTree = $this->navigationModel->getFullMenuTree();
        
        $data = [
            'title' => 'Navigation Menu - Admin',
            'page' => 'admin-navigation',
            'menu_tree' => $menuTree
        ];
        
        $this->render('admin/navigation', 'admin', $data);
    }
    
    public function createMenuItem() {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maxSort = $this->navigationModel->getMaxSortOrder($_POST['parent_id'] ?? null);
            
            $menuData = [
                'title' => $_POST['title'] ?? '',
                'url' => $_POST['url'] ?? '',
                'icon' => $_POST['icon'] ?? '',
                'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
                'sort_order' => $maxSort + 1,
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'target' => $_POST['target'] ?? '_self'
            ];
            
            if ($this->navigationModel->create($menuData)) {
                $this->setFlash('success', 'Menu item created successfully!');
            } else {
                $this->setFlash('error', 'Failed to create menu item.');
            }
            
            $this->redirect('admin/navigation');
            return;
        }
        
        $parentItems = $this->navigationModel->getMenuItems();
        
        $data = [
            'title' => 'Create Menu Item - Admin',
            'page' => 'admin-menu-create',
            'parent_items' => $parentItems
        ];
        
        $this->render('admin/menu-form', 'admin', $data);
    }
    
    public function editMenuItem($id) {
        $this->userModel->requireAuth();
        
        $menuItem = $this->navigationModel->find($id);
        if (!$menuItem) {
            $this->setFlash('error', 'Menu item not found.');
            $this->redirect('admin/navigation');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $menuData = [
                'title' => $_POST['title'] ?? '',
                'url' => $_POST['url'] ?? '',
                'icon' => $_POST['icon'] ?? '',
                'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'target' => $_POST['target'] ?? '_self'
            ];
            
            if ($this->navigationModel->update($id, $menuData)) {
                $this->setFlash('success', 'Menu item updated successfully!');
            } else {
                $this->setFlash('error', 'Failed to update menu item.');
            }
            
            $this->redirect('admin/navigation');
            return;
        }
        
        $parentItems = $this->navigationModel->getMenuItems();
        
        $data = [
            'title' => 'Edit Menu Item - Admin',
            'page' => 'admin-menu-edit',
            'menu_item' => $menuItem,
            'parent_items' => $parentItems
        ];
        
        $this->render('admin/menu-form', 'admin', $data);
    }
    
    public function deleteMenuItem($id) {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/navigation');
            return;
        }
        
        if ($this->navigationModel->delete($id)) {
            $this->setFlash('success', 'Menu item deleted successfully!');
        } else {
            $this->setFlash('error', 'Failed to delete menu item.');
        }
        
        $this->redirect('admin/navigation');
    }
    
    // MEDIA MANAGEMENT
    public function media() {
        $this->userModel->requireAuth();
        
        $images = $this->mediaModel->getImages();
        $documents = $this->mediaModel->getDocuments();
        
        $data = [
            'title' => 'Media Library - Admin',
            'page' => 'admin-media',
            'images' => $images,
            'documents' => $documents
        ];
        
        $this->render('admin/media', 'admin', $data);
    }
    
    public function uploadMedia() {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file'])) {
            $this->json(['success' => false, 'message' => 'No file uploaded'], 400);
            return;
        }
        
        try {
            $currentUser = $this->userModel->getCurrentUser();
            $fileId = $this->mediaModel->uploadFile($_FILES['file'], $currentUser['id']);
            
            $file = $this->mediaModel->find($fileId);
            $this->json([
                'success' => true,
                'message' => 'File uploaded successfully!',
                'file' => $file
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    public function updateMedia($id) {
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

            $this->redirect('admin/media');
            return;
        }

        $file = $this->mediaModel->find($id);
        if (!$file) {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'File not found.'
                ], 404);
                return;
            }

            $this->setFlash('error', 'File not found.');
            $this->redirect('admin/media');
            return;
        }

        $updateData = [
            'alt_text' => trim($_POST['alt_text'] ?? ''),
            'title' => trim($_POST['title'] ?? '')
        ];

        $updated = $this->mediaModel->updateFile($id, $updateData);

        if ($updated) {
            if ($isAjax) {
                $this->json([
                    'success' => true,
                    'message' => 'File updated successfully.',
                    'file' => $this->mediaModel->find($id)
                ]);
                return;
            }

            $this->setFlash('success', 'File updated successfully!');
        } else {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to update file.'
                ], 500);
                return;
            }

            $this->setFlash('error', 'Failed to update file.');
        }

        $this->redirect('admin/media');
    }

    public function deleteMedia($id) {
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

            $this->redirect('admin/media');
            return;
        }

        $file = $this->mediaModel->find($id);
        if (!$file) {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'File not found.'
                ], 404);
                return;
            }

            $this->setFlash('error', 'File not found.');
            $this->redirect('admin/media');
            return;
        }

        if ($this->mediaModel->deleteFile($id)) {
            if ($isAjax) {
                $this->json([
                    'success' => true,
                    'message' => 'File deleted successfully.'
                ]);
                return;
            }

            $this->setFlash('success', 'File deleted successfully!');
        } else {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to delete file.'
                ], 500);
                return;
            }

            $this->setFlash('error', 'Failed to delete file.');
        }

        $this->redirect('admin/media');
    }
    
    // USER MANAGEMENT
    public function users() {
        $this->userModel->requireAuth();
        
        $users = $this->userModel->all();
        
        $data = [
            'title' => 'Admin Users - Admin',
            'page' => 'admin-users',
            'users' => $users
        ];
        
        $this->render('admin/users', 'admin', $data);
    }
    
    public function createUser() {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userData = [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? ''
            ];
            
            $errors = $this->userModel->validateRegistration($userData);
            
            if (empty($errors)) {
                if ($this->userModel->createUser($userData)) {
                    $this->setFlash('success', 'User created successfully!');
                    $this->redirect('admin/users');
                    return;
                } else {
                    $this->setFlash('error', 'Failed to create user.');
                }
            } else {
                $this->setFlash('form_errors', $errors);
                $this->setFlash('form_data', $userData);
            }
        }
        
        $data = [
            'title' => 'Create User - Admin',
            'page' => 'admin-user-create'
        ];
        
        $this->render('admin/user-form', 'admin', $data);
    }
    
    public function editUser($id) {
        $this->userModel->requireAuth();
        
        $user = $this->userModel->find($id);
        if (!$user) {
            $this->setFlash('error', 'User not found.');
            $this->redirect('admin/users');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userData = [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? ''
            ];
            
            if (!empty($_POST['password'])) {
                $userData['password'] = $_POST['password'];
            }
            
            if ($this->userModel->update($id, $userData)) {
                $this->setFlash('success', 'User updated successfully!');
                $this->redirect('admin/users');
                return;
            } else {
                $this->setFlash('error', 'Failed to update user.');
            }
        }
        
        $data = [
            'title' => 'Edit User - Admin',
            'page' => 'admin-user-edit',
            'user' => $user
        ];
        
        $this->render('admin/user-form', 'admin', $data);
    }
    
    public function deleteUser($id) {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/users');
            return;
        }
        
        $currentUser = $this->userModel->getCurrentUser();
        if ($id == $currentUser['id']) {
            $this->setFlash('error', 'You cannot delete your own account.');
            $this->redirect('admin/users');
            return;
        }
        
        if ($this->userModel->delete($id)) {
            $this->setFlash('success', 'User deleted successfully!');
        } else {
            $this->setFlash('error', 'Failed to delete user.');
        }
        
        $this->redirect('admin/users');
    }
    
    // THEME MANAGEMENT
    public function theme() {
        $this->userModel->requireAuth();
        
        $themeSettings = $this->settingModel->getByGroup('theme');
        $themeData = [];
        
        foreach ($themeSettings as $setting) {
            $themeData[$setting['setting_key']] = $setting['setting_value'];
        }
        
        $data = [
            'title' => 'Theme Options - Admin',
            'page' => 'admin-theme',
            'theme_data' => $themeData
        ];
        
        $this->render('admin/theme', 'admin', $data);
    }
    
    public function updateTheme() {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/theme');
            return;
        }
        
        $themeData = $_POST;
        unset($themeData['csrf_token']);
        
        if ($this->settingModel->updateSettings($themeData)) {
            $this->setFlash('success', 'Theme updated successfully!');
        } else {
            $this->setFlash('error', 'Failed to update theme.');
        }
        
        $this->redirect('admin/theme');
    }
    
    // PAGES MANAGEMENT
    public function pages() {
        $this->userModel->requireAuth();
        
        $pages = $this->pageModel->all();
        
        $data = [
            'title' => 'Pages Management - Admin',
            'page' => 'admin-pages',
            'pages' => $pages
        ];
        
        $this->render('admin/pages', 'admin', $data);
    }
    
    public function editPage($key) {
        $this->userModel->requireAuth();
        
        $page = $this->pageModel->getByKey($key);
        if (!$page) {
            $this->setFlash('error', 'Page not found.');
            $this->redirect('admin/pages');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pageData = [
                'title' => $_POST['title'] ?? '',
                'meta_description' => $_POST['meta_description'] ?? '',
                'content' => $_POST['content'] ?? '',
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];
            
            if ($this->pageModel->updateByKey($key, $pageData)) {
                $this->setFlash('success', 'Page updated successfully!');
                $this->redirect('admin/pages');
                return;
            } else {
                $this->setFlash('error', 'Failed to update page.');
            }
        }
        
        $data = [
            'title' => 'Edit Page - Admin',
            'page' => 'admin-page-edit',
            'page_data' => $page
        ];
        
        $this->render('admin/page-form', 'admin', $data);
    }
    
    // PROJECTS MANAGEMENT
    public function projects() {
        $this->userModel->requireAuth();
        
        $projects = $this->projectModel->all('sort_order ASC, created_at DESC');
        
        $data = [
            'title' => 'Projects Management - Admin',
            'page' => 'admin-projects',
            'projects' => $projects
        ];
        
        $this->render('admin/projects', 'admin', $data);
    }
    
    public function createProject() {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formInput = $this->sanitizeProjectFormInput($_POST);
            [$formInput, $uploadErrors] = $this->handleProjectImageInput($formInput);
            $errors = array_merge($this->projectModel->validate($formInput), $uploadErrors);
            
            if (!empty($errors)) {
                $this->setFlash('form_errors', $errors);
                $this->setFlash('form_data', $formInput);
                $this->redirect('admin/projects/create');
                return;
            }
            
            $projectData = $this->mapProjectFormToData($formInput);
            $slugSource = $formInput['slug'] ?: $formInput['title'];
            $projectData['slug'] = $this->projectModel->generateSlug($slugSource);
            
            if ($this->projectModel->create($projectData)) {
                $this->setFlash('success', 'Project created successfully.');
                $this->redirect('admin/projects');
                return;
            }
            
            $this->setFlash('error', 'Failed to create project.');
            $this->setFlash('form_data', $formInput);
            $this->redirect('admin/projects/create');
            return;
        }
        
        $data = [
            'title' => 'Create Project - Admin',
            'page' => 'admin-project-create'
        ];
        
        $this->render('admin/project-form', 'admin', $data);
    }
    
    public function editProject($id) {
        $this->userModel->requireAuth();
        
        $project = $this->projectModel->find($id);
        if (!$project) {
            $this->setFlash('error', 'Project not found.');
            $this->redirect('admin/projects');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formInput = $this->sanitizeProjectFormInput($_POST, $project);
            [$formInput, $uploadErrors] = $this->handleProjectImageInput($formInput, $project);
            $errors = array_merge($this->projectModel->validate($formInput), $uploadErrors);
            
            if (!empty($errors)) {
                $this->setFlash('form_errors', $errors);
                $this->setFlash('form_data', $formInput);
                $this->redirect('admin/projects/' . $id . '/edit');
                return;
            }
            
            $projectData = $this->mapProjectFormToData($formInput, $project);
            $slugSource = $formInput['slug'] ?: $formInput['title'];
            $sanitizedSlug = $this->projectModel->generateSlug($slugSource, $project['id']);
            if ($sanitizedSlug !== $project['slug']) {
                $projectData['slug'] = $sanitizedSlug;
            }
            
            if ($this->projectModel->update($id, $projectData)) {
                $this->setFlash('success', 'Project updated successfully.');
                $this->redirect('admin/projects');
                return;
            }
            
            $this->setFlash('error', 'Failed to update project.');
            $this->setFlash('form_data', $formInput);
            $this->redirect('admin/projects/' . $id . '/edit');
            return;
        }
        
        $data = [
            'title' => 'Edit Project - Admin',
            'page' => 'admin-project-edit',
            'project' => $project
        ];
        
        $this->render('admin/project-form', 'admin', $data);
    }
    
    public function deleteProject($id) {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/projects');
            return;
        }
        
        if ($this->projectModel->delete($id)) {
            $this->setFlash('success', 'Project deleted successfully.');
        } else {
            $this->setFlash('error', 'Failed to delete project.');
        }
        
        $this->redirect('admin/projects');
    }
    
    // TESTIMONIALS MANAGEMENT
    public function testimonials() {
        $this->userModel->requireAuth();

        $records = $this->testimonialModel->getAllOrdered();
        $fallbackImage = $this->getTestimonialPlaceholderImage();

        $payload = array_map(function (array $testimonial) use ($fallbackImage) {
            return $this->formatTestimonialForManager($testimonial, $fallbackImage);
        }, $records);

        $data = [
            'title' => 'Testimonials Management - Admin',
            'page' => 'admin-testimonials',
            'testimonials' => $records,
            'testimonials_json' => htmlspecialchars(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8'),
            'testimonial_placeholder' => $fallbackImage,
            'testimonial_routes' => [
                'create' => url('admin/testimonials'),
                'update_base' => url('admin/testimonials'),
                'delete_base' => url('admin/testimonials'),
                'reorder' => url('admin/testimonials/order')
            ]
        ];

        $this->render('admin/testimonials', 'admin', $data);
    }

    public function storeTestimonial() {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/testimonials');
            return;
        }

        $input = $this->sanitizeTestimonialInput($_POST);
        $errors = $this->validateTestimonialInput($input);

        [$input, $uploadErrors] = $this->handleTestimonialImageUpload($input);
        $errors = array_merge($errors, $uploadErrors);

        if (!empty($errors)) {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => 'Please fix the highlighted errors.',
                    'errors' => $errors
                ], 422);
            }

            $this->setFlash('error', implode(' ', $errors));
            $this->redirect('admin/testimonials');
            return;
        }

        $input['sort_order'] = $this->testimonialModel->getNextSortOrder();

        try {
            $id = $this->testimonialModel->create($input);
        } catch (Exception $exception) {
            error_log('Failed to create testimonial: ' . $exception->getMessage());

            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to create testimonial. Please try again.'
                ], 500);
            }

            $this->setFlash('error', 'Failed to create testimonial.');
            $this->redirect('admin/testimonials');
            return;
        }

        $record = $this->testimonialModel->find($id);
        $formatted = $this->formatTestimonialForManager($record ?? $input + ['id' => $id], $this->getTestimonialPlaceholderImage());

        if ($this->isAjaxRequest()) {
            $this->json([
                'success' => true,
                'message' => 'Testimonial added successfully.',
                'testimonial' => $formatted
            ]);
            return;
        }

        $this->setFlash('success', 'Testimonial created successfully.');
        $this->redirect('admin/testimonials');
    }

    public function updateTestimonial($id) {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/testimonials');
            return;
        }

        $existing = $this->testimonialModel->find($id);
        if (!$existing) {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => 'Testimonial not found.'
                ], 404);
                return;
            }

            $this->setFlash('error', 'Testimonial not found.');
            $this->redirect('admin/testimonials');
            return;
        }

        $input = $this->sanitizeTestimonialInput($_POST, $existing);
        $errors = $this->validateTestimonialInput($input);

        [$input, $uploadErrors] = $this->handleTestimonialImageUpload($input, $existing);
        $errors = array_merge($errors, $uploadErrors);

        if (!empty($errors)) {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => 'Please fix the highlighted errors.',
                    'errors' => $errors
                ], 422);
                return;
            }

            $this->setFlash('error', implode(' ', $errors));
            $this->redirect('admin/testimonials');
            return;
        }

        // Preserve existing sort order unless explicitly provided
        if (!isset($input['sort_order'])) {
            $input['sort_order'] = (int)($existing['sort_order'] ?? 0);
        }

        try {
            $this->testimonialModel->update($id, $input);
        } catch (Exception $exception) {
            error_log('Failed to update testimonial: ' . $exception->getMessage());

            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to update testimonial. Please try again.'
                ], 500);
                return;
            }

            $this->setFlash('error', 'Failed to update testimonial.');
            $this->redirect('admin/testimonials');
            return;
        }

        $record = $this->testimonialModel->find($id);
        $formatted = $this->formatTestimonialForManager($record ?? $existing, $this->getTestimonialPlaceholderImage());

        if ($this->isAjaxRequest()) {
            $this->json([
                'success' => true,
                'message' => 'Testimonial updated successfully.',
                'testimonial' => $formatted
            ]);
            return;
        }

        $this->setFlash('success', 'Testimonial updated successfully.');
        $this->redirect('admin/testimonials');
    }
    
    public function deleteTestimonial($id) {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/testimonials');
            return;
        }
        
        $deleted = $this->testimonialModel->delete($id);

        if ($this->isAjaxRequest()) {
            if ($deleted) {
                $this->json([
                    'success' => true,
                    'message' => 'Testimonial removed.'
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to delete testimonial.'
                ], 500);
            }
            return;
        }

        if ($deleted) {
            $this->setFlash('success', 'Testimonial deleted successfully.');
        } else {
            $this->setFlash('error', 'Failed to delete testimonial.');
        }
        
        $this->redirect('admin/testimonials');
    }

    public function reorderTestimonials() {
        $this->userModel->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/testimonials');
            return;
        }

        $payload = $this->parseJsonInput();
        $items = isset($payload['items']) && is_array($payload['items']) ? $payload['items'] : [];

        $normalized = [];
        foreach ($items as $index => $item) {
            $id = (int)($item['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $normalized[] = [
                'id' => $id,
                'sort_order' => isset($item['sort_order']) ? (int)$item['sort_order'] : ($index + 1),
                'is_visible' => !empty($item['is_visible']) ? 1 : 0
            ];
        }

        if (empty($normalized)) {
            $this->json([
                'success' => false,
                'message' => 'No testimonial order changes detected.'
            ], 422);
            return;
        }

        try {
            $this->testimonialModel->reorder($normalized);
        } catch (Exception $exception) {
            error_log('Failed to reorder testimonials: ' . $exception->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Failed to save testimonial ordering. Please try again.'
            ], 500);
            return;
        }

        $records = $this->testimonialModel->getAllOrdered();
        $fallbackImage = $this->getTestimonialPlaceholderImage();
        $payload = array_map(function (array $testimonial) use ($fallbackImage) {
            return $this->formatTestimonialForManager($testimonial, $fallbackImage);
        }, $records);

        $this->json([
            'success' => true,
            'message' => 'Testimonials updated successfully.',
            'testimonials' => $payload
        ]);
    }
    
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
        $orderedIds = array_filter($orderedIds, static fn($value) => $value > 0);

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
        return asset('images/testimonials/default-avatar.jpg');
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