<?php

require_once ROOT_PATH . '/app/core/BaseController.php';
require_once ROOT_PATH . '/app/models/Project.php';
require_once ROOT_PATH . '/app/models/Testimonial.php';
require_once ROOT_PATH . '/app/models/Service.php';
require_once ROOT_PATH . '/app/models/Skill.php';

class HomeController extends BaseController {
    private $projectModel;
    private $testimonialModel;
    private $serviceModel;
    private $skillModel;
    
    public function __construct() {
        parent::__construct();
        $this->projectModel = new Project();
        $this->testimonialModel = new Testimonial();
        $this->serviceModel = new Service();
        $this->skillModel = new Skill();
    }
    
    public function index() {
        $defaultMeta = [
            'title' => 'Yahya Bouhafs Portfolio',
            'meta_description' => 'Creative UI/UX Designer specializing in user-centered design, mobile apps, and web interfaces. View my portfolio of successful design projects.',
        ];
        $pageMeta = $this->contentRepository
            ? $this->contentRepository->getPageMeta('home', $defaultMeta)
            : $defaultMeta;
        
        $projectLimit = 6;
        $featuredProjects = $this->contentRepository
            ? $this->contentRepository->getFeaturedProjects($projectLimit)
            : $this->projectModel->getFeatured($projectLimit);

        if (empty($featuredProjects) && !$this->contentRepository) {
            $featuredProjects = $this->projectModel->getPublished($projectLimit);
        }
        
        $heroData = $this->contentRepository ? $this->contentRepository->getHeroData() : [];
        $heroStats = $this->contentRepository ? $this->contentRepository->getHeroStats() : [];
        $services = $this->contentRepository ? $this->contentRepository->getHomepageServices() : $this->serviceModel->getActive();
        $skillsByCategory = $this->contentRepository ? $this->contentRepository->getSkillsByCategory() : $this->skillModel->getGroupedByCategory();
    $testimonials = $this->contentRepository ? $this->contentRepository->getFeaturedTestimonials(3) : $this->testimonialModel->getFeatured(3);
        
        $data = [
            'title' => $pageMeta['title'] ?? $defaultMeta['title'],
            'meta_description' => $pageMeta['meta_description'] ?? $defaultMeta['meta_description'],
            'featured_projects' => $featuredProjects,
            'hero' => $heroData,
            'hero_stats' => $heroStats,
            'services' => $services,
            'skills_by_category' => $skillsByCategory,
            'featured_testimonials' => $testimonials,
            'page_content' => $pageMeta['content'] ?? '',
            'page_sections' => $pageMeta['sections'] ?? [],
            'page' => 'home'
        ];
        
        $this->render('home/index', 'main', $data);
    }
    
    public function about() {
        $defaultMeta = [
            'title' => 'About - Yahya Bouhafs',
            'meta_description' => 'Learn about my background, experience, and approach to UI/UX design. Discover my skills, tools, and design philosophy.',
        ];
        $pageMeta = $this->contentRepository
            ? $this->contentRepository->getPageMeta('about', $defaultMeta)
            : $defaultMeta;
        
        $aboutData = $this->contentRepository ? $this->contentRepository->getAboutData() : [];
        $skillsByCategory = $this->contentRepository ? $this->contentRepository->getSkillsByCategory() : $this->skillModel->getGroupedByCategory();
        $testimonials = $this->contentRepository ? $this->contentRepository->getFeaturedTestimonials(6) : $this->testimonialModel->getFeatured(6);
        
        $data = [
            'title' => $pageMeta['title'] ?? $defaultMeta['title'],
            'meta_description' => $pageMeta['meta_description'] ?? $defaultMeta['meta_description'],
            'about' => $aboutData,
            'skills_by_category' => $skillsByCategory,
            'featured_testimonials' => $testimonials,
            'page_content' => $pageMeta['content'] ?? '',
            'page_sections' => $pageMeta['sections'] ?? [],
            'page' => 'about'
        ];
        
        $this->render('home/about', 'main', $data);
    }
    
    public function services() {
        $defaultMeta = [
            'title' => 'Services - Yahya Bouhafs',
            'meta_description' => 'Professional UI/UX design services including wireframing, prototyping, branding, and user research.',
        ];
        $pageMeta = $this->contentRepository
            ? $this->contentRepository->getPageMeta('services', $defaultMeta)
            : $defaultMeta;
        
    $services = $this->contentRepository ? $this->contentRepository->getActiveServices() : $this->serviceModel->getActive();
    $processSteps = $this->contentRepository ? $this->contentRepository->getServiceProcessSteps() : [];
    $pricingPackages = $this->contentRepository ? $this->contentRepository->getPricingPackages() : [];
    $faqs = $this->contentRepository ? $this->contentRepository->getFaq('services') : [];
    $testimonials = $this->contentRepository ? $this->contentRepository->getFeaturedTestimonials(3) : $this->testimonialModel->getFeatured(3);
        
        $data = [
            'title' => $pageMeta['title'] ?? $defaultMeta['title'],
            'meta_description' => $pageMeta['meta_description'] ?? $defaultMeta['meta_description'],
            'services' => $services,
            'process_steps' => $processSteps,
            'pricing_packages' => $pricingPackages,
            'faqs' => $faqs,
            'featured_testimonials' => $testimonials,
            'page_content' => $pageMeta['content'] ?? '',
            'page_sections' => $pageMeta['sections'] ?? [],
            'page' => 'services'
        ];
        
        $this->render('home/services', 'main', $data);
    }
    
    public function debug() {
        $debug_info = [
            'REQUEST_URI' => $_SERVER['REQUEST_URI'],
            'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'],
            'BASE_URL' => BASE_URL,
            'ROOT_PATH' => ROOT_PATH
        ];
        
        echo '<h1>Debug Info</h1>';
        echo '<pre>' . print_r($debug_info, true) . '</pre>';
        exit;
    }
}