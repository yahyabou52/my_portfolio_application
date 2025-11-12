<?php

require_once ROOT_PATH . '/app/models/SiteSetting.php';
require_once ROOT_PATH . '/app/models/HeroSection.php';
require_once ROOT_PATH . '/app/models/HeroStat.php';
require_once ROOT_PATH . '/app/models/Service.php';
require_once ROOT_PATH . '/app/models/ServiceFeature.php';
require_once ROOT_PATH . '/app/models/ServiceProcessStep.php';
require_once ROOT_PATH . '/app/models/PricingPlan.php';
require_once ROOT_PATH . '/app/models/Project.php';
require_once ROOT_PATH . '/app/models/ProjectImage.php';
require_once ROOT_PATH . '/app/models/Testimonial.php';
require_once ROOT_PATH . '/app/models/Skill.php';
require_once ROOT_PATH . '/app/models/TimelineItem.php';
require_once ROOT_PATH . '/app/models/Faq.php';
require_once ROOT_PATH . '/app/models/Page.php';
require_once ROOT_PATH . '/app/models/NavigationMenu.php';

class ContentRepository {
    private $siteSettingModel;
    private $heroSectionModel;
    private $heroStatModel;
    private $serviceModel;
    private $serviceFeatureModel;
    private $serviceProcessModel;
    private $pricingPlanModel;
    private $projectModel;
    private $projectImageModel;
    private $testimonialModel;
    private $skillAggregator;
    private $timelineModel;
    private $faqModel;
    private $pageModel;
    private $navigationModel;
    private $settings;

    public function __construct() {
        $this->siteSettingModel = new SiteSetting();
        $this->heroSectionModel = new HeroSection();
        $this->heroStatModel = new HeroStat();
        $this->serviceModel = new Service();
        $this->serviceFeatureModel = new ServiceFeature();
    $this->serviceProcessModel = new ServiceProcessStep();
    $this->pricingPlanModel = new PricingPlan();
        $this->projectModel = new Project();
        $this->projectImageModel = new ProjectImage();
        $this->testimonialModel = new Testimonial();
        $this->skillAggregator = new Skill();
        $this->timelineModel = new TimelineItem();
    $this->faqModel = new Faq();
        $this->pageModel = new Page();
        $this->navigationModel = new NavigationMenu();

        $this->settings = $this->siteSettingModel->getSettings() ?? [];
    }

    public function getGlobalData(): array {
        $siteSettings = $this->buildSiteSettings();
        $contactSettings = $this->buildContactSettings();
        $footerSettings = $this->buildFooterSettings();
        $socialLinks = $this->formatSocialLinks($this->siteSettingModel->getSocialLinks());
        $navigationMenu = $this->buildNavigationTree();
        $activeServices = $this->mapServices($this->serviceModel->getPublished());

        return [
            'siteSettings' => $siteSettings,
            'contactSettings' => $contactSettings,
            'footerSettings' => $footerSettings,
            'socialLinks' => $socialLinks,
            'navigationMenu' => $navigationMenu,
            'activeServices' => $activeServices,
        ];
    }

    private function buildSiteSettings(): array {
        $logo = $this->settings['logo_path'] ?? null;
        $favicon = $this->settings['favicon_path'] ?? null;

        return [
            'site_title' => $this->settings['site_title'] ?? 'Yahya Bouhafs',
            'site_tagline' => $this->settings['site_tagline'] ?? '',
            'site_description' => $this->settings['site_description'] ?? '',
            'nav_cta_text' => $this->settings['nav_cta_text'] ?? "Let's Talk",
            'nav_cta_url' => $this->settings['nav_cta_url'] ?? '/contact',
            'site_logo' => $logo ? media_url($logo) : '',
            'favicon' => $favicon ? media_url($favicon) : ''
        ];
    }

    private function buildContactSettings(): array {
        return [
            'contact_email' => $this->settings['contact_email'] ?? '',
            'contact_phone' => $this->settings['contact_phone'] ?? '',
            'contact_address' => $this->settings['contact_address'] ?? ''
        ];
    }

    private function buildFooterSettings(): array {
        return [
            'footer_text' => $this->settings['footer_text'] ?? '',
            'footer_links' => $this->siteSettingModel->getFooterLinks()
        ];
    }

    private function formatSocialLinks(array $links): array {
        $formatted = [];
        foreach ($links as $link) {
            if (empty($link['url'])) {
                continue;
            }
            $formatted[] = [
                'platform' => $link['platform'] ?? 'link',
                'url' => $link['url']
            ];
        }
        return $formatted;
    }

    private function buildNavigationTree($parentId = null): array {
        try {
            $items = $this->navigationModel->getActiveMenuItems($parentId);
        } catch (\PDOException $exception) {
            if ($exception->getCode() === '42S02') {
                return [];
            }

            throw $exception;
        }

        $tree = [];
        foreach ($items as $item) {
            $item['children'] = $this->buildNavigationTree($item['id']);
            $tree[] = $item;
        }
        return $tree;
    }

    public function getPageMeta(string $pageKey, array $fallback = []): array {
        try {
            $page = $this->pageModel->getByKey($pageKey);
        } catch (\PDOException $exception) {
            // Gracefully handle missing table while migrations are catching up
            if ($exception->getCode() === '42S02') {
                return $fallback;
            }

            throw $exception;
        }

        if (!$page) {
            return $fallback;
        }

        $sections = [];
        if (!empty($page['sections'])) {
            $decoded = json_decode($page['sections'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $sections = $decoded;
            }
        }

        return array_merge($fallback, [
            'title' => $page['title'] ?? ($fallback['title'] ?? null),
            'meta_description' => $page['meta_description'] ?? ($fallback['meta_description'] ?? null),
            'content' => $page['content'] ?? '',
            'sections' => $sections,
        ]);
    }

    public function getHeroData(): array {
        $hero = $this->heroSectionModel->getActive() ?? [];

        $defaults = [
            'hero_intro_prefix' => "Hi, I'm",
            'hero_intro_name_first' => 'Yahya',
            'hero_intro_name_rest' => 'Bouhafs',
            'hero_intro_suffix' => '- UI/UX Designer',
            'hero_title' => 'Product-Focused UI/UX Designer',
            'hero_subtitle' => 'Crafting delightful journeys for modern digital products',
            'hero_description' => 'I partner with product teams to translate insights into inclusive interfaces that drive measurable outcomes.',
            'hero_primary_cta_text' => 'View Portfolio',
            'hero_primary_cta_url' => '/portfolio',
            'hero_secondary_cta_text' => "Let's Collaborate",
            'hero_secondary_cta_url' => '/contact',
            'hero_background_image_path' => '',
            'hero_background_image_alt' => 'Portrait of Yahya Bouhafs',
            'scroll_indicator_text' => 'Scroll to explore'
        ];

        $hero = array_merge($defaults, $hero);
        $hero['hero_background_image_url'] = media_url($hero['hero_background_image_path']);

        $hero['hero_primary_cta'] = [
            'text' => $hero['hero_primary_cta_text'],
            'url' => navbar_build_nav_url($hero['hero_primary_cta_url'])
        ];

        $hero['hero_secondary_cta'] = [
            'text' => $hero['hero_secondary_cta_text'],
            'url' => navbar_build_nav_url($hero['hero_secondary_cta_url'] ?: '/contact')
        ];

        // Backwards compatibility for legacy view fields
        $hero['hero_cta_text'] = $hero['hero_primary_cta_text'];
        $hero['hero_cta_url'] = $hero['hero_primary_cta_url'];

        $hero['scroll_indicator_text'] = $hero['scroll_indicator_text'] ?: 'Scroll to explore';

        return $hero;
    }

    public function getHeroStats(int $limit = 3): array {
        $stats = $this->heroStatModel->getActive($limit);
        return array_map(function ($stat) {
            return [
                'label' => $stat['label'],
                'value' => $stat['value']
            ];
        }, $stats);
    }

    public function getAboutData(): array {
        $page = $this->getPageMeta('about', []);
        $sections = $page['sections'] ?? [];

        $aboutImagePath = $sections['about_image_path'] ?? null;
        $aboutImageAlt = trim((string)($sections['about_image_alt'] ?? 'Portrait'));
        $highlights = $sections['about_highlights'] ?? [];

        if (is_string($highlights)) {
            $decoded = json_decode($highlights, true);
            $highlights = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }

        if (!is_array($highlights)) {
            $highlights = [];
        }

        $normalizedHighlights = [];
        foreach ($highlights as $item) {
            if (is_string($item)) {
                $text = trim($item);
            } elseif (is_array($item)) {
                $text = trim((string)($item['text'] ?? ''));
            } else {
                $text = '';
            }

            if ($text !== '') {
                $normalizedHighlights[] = ['text' => $text];
            }
        }

        $aboutImageUrl = $aboutImagePath ? media_url($aboutImagePath) : asset('images/about-photo.jpg');

        $greeting = trim((string)($sections['about_greeting'] ?? $sections['about_subtitle'] ?? ''));
        $headline = trim((string)($sections['about_headline'] ?? $sections['about_title'] ?? ($page['title'] ?? 'About Me')));
        $bio = trim((string)($sections['about_bio'] ?? $sections['about_content'] ?? ($page['content'] ?? '')));
        $philosophy = trim((string)($sections['about_philosophy'] ?? ''));
        $timelineMeta = is_array($sections['timeline'] ?? null) ? $sections['timeline'] : [];

        return [
            'greeting' => $greeting,
            'headline' => $headline,
            'bio' => $bio,
            'philosophy' => $philosophy,
            'image_url' => $aboutImageUrl,
            'image_alt' => $aboutImageAlt !== '' ? $aboutImageAlt : 'Portrait',
            'timeline_meta' => [
                'title' => trim((string)($timelineMeta['title'] ?? 'Experience & Education')),
                'subtitle' => trim((string)($timelineMeta['subtitle'] ?? ''))
            ],
            'timeline' => $this->timelineModel->getPublished(),

            // Legacy keys for backwards compatibility
            'about_title' => $headline,
            'about_subtitle' => $greeting,
            'about_content' => $bio,
            'about_image_url' => $aboutImageUrl,
            'about_image_alt' => $aboutImageAlt !== '' ? $aboutImageAlt : 'Portrait',
            'about_highlights' => $normalizedHighlights
        ];
    }

    public function getSkillsByCategory(): array {
        return $this->skillAggregator->getGroupedByCategory();
    }

    public function getActiveServices(int $limit = null): array {
        $services = $this->mapServices($this->serviceModel->getPublished());
        if ($limit !== null) {
            return array_slice($services, 0, $limit);
        }
        return $services;
    }

    public function getHomepageServices(int $limit = 4): array {
        $selected = $this->serviceModel->getHomepageFeatured();

        if (!empty($selected)) {
            $mapped = $this->mapServices($selected);
            $visible = array_filter($mapped, function ($service) {
                return (int)($service['homepage_is_visible'] ?? 1) === 1;
            });

            if ($limit !== null && count($visible) > $limit) {
                $visible = array_slice($visible, 0, $limit);
            }

            return array_values($visible);
        }

        return $this->getActiveServices($limit);
    }

    public function getServiceProcessSteps(?int $serviceId = null): array {
        $steps = $this->serviceProcessModel->getPublished($serviceId);

        return array_map(function (array $step) {
            $iconClass = trim((string)($step['icon_class'] ?? ''));
            if ($iconClass === '') {
                $iconClass = 'bi-check-circle';
            }

            return [
                'id' => (int)($step['id'] ?? 0),
                'service_id' => isset($step['service_id']) ? (int)$step['service_id'] : null,
                'step_order' => (int)($step['step_order'] ?? 0),
                'icon_class' => $iconClass,
                'title' => (string)($step['title'] ?? ''),
                'description' => (string)($step['description'] ?? ''),
                'display' => (int)($step['display'] ?? 1)
            ];
        }, $steps);
    }

    public function getPricingPlans(): array {
        return $this->pricingPlanModel->allVisible();
    }

    private function mapServices(array $services): array {
        $mapped = [];
        foreach ($services as $service) {
            $features = $this->serviceFeatureModel->getByService($service['id']);
            $service['features'] = $features;
            $mapped[] = $service;
        }
        return $mapped;
    }

    public function getFeaturedProjects(?int $limit = 6): array {
        $projects = $this->projectModel->getFeatured(null);

        if (empty($projects)) {
            $projects = $this->projectModel->getPublished($limit ?? null);
        }

        if ($limit !== null && count($projects) > $limit) {
            $projects = array_slice($projects, 0, $limit);
        }

        return array_map(function ($project) {
            $project['main_image_url'] = $project['main_image_path'] ? media_url($project['main_image_path']) : asset('images/projects/default.jpg');
            $project['technologies_list'] = $this->projectModel->getTechnologies($project['id']);
            return $project;
        }, $projects);
    }

    public function getProjectGallery(int $projectId): array {
        $images = $this->projectImageModel->getByProject($projectId);
        return array_map(function ($image) {
            $image['image_url'] = media_url($image['image_path']);
            return $image;
        }, $images);
    }

    public function getFeaturedTestimonials(int $limit = 3): array {
        $testimonials = $this->testimonialModel->getVisible($limit);

        if (empty($testimonials)) {
            $fallback = $this->testimonialModel->getAllOrdered();
            $testimonials = $limit > 0 ? array_slice($fallback, 0, $limit) : $fallback;
        }

        return array_map(function ($testimonial) {
            $testimonial['image_url'] = $testimonial['image_path'] ? media_url($testimonial['image_path']) : asset('images/testimonials/default-avatar.jpg');
            return $testimonial;
        }, $testimonials);
    }

    public function getTimeline(): array {
        return $this->timelineModel->getPublished();
    }

    public function getFaq(string $page): array {
        if (strtolower($page) !== 'services') {
            return [];
        }

        try {
            return $this->faqModel->allVisible();
        } catch (\PDOException $exception) {
            if ($exception->getCode() === '42S02') {
                return [];
            }

            throw $exception;
        }
    }
}
