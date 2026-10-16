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
require_once ROOT_PATH . '/app/models/Faq.php';
require_once ROOT_PATH . '/app/models/Project.php';
require_once ROOT_PATH . '/app/models/NavigationMenu.php';
require_once ROOT_PATH . '/app/models/Media.php';
require_once ROOT_PATH . '/app/models/Testimonial.php';
require_once ROOT_PATH . '/app/models/Skill.php';
require_once ROOT_PATH . '/app/models/HeroSection.php';
require_once ROOT_PATH . '/app/models/HeroStat.php';
require_once ROOT_PATH . '/app/models/TimelineItem.php';
require_once ROOT_PATH . '/app/models/FeaturedProject.php';

require_once ROOT_PATH . '/app/controllers/admin/SettingsActions.php';
require_once ROOT_PATH . '/app/controllers/admin/HeroSectionActions.php';
require_once ROOT_PATH . '/app/controllers/admin/HomeCtaActions.php';
require_once ROOT_PATH . '/app/controllers/admin/AboutSectionActions.php';
require_once ROOT_PATH . '/app/controllers/admin/TimelineActions.php';
require_once ROOT_PATH . '/app/controllers/admin/ServicesActions.php';
require_once ROOT_PATH . '/app/controllers/admin/ServiceFeatureActions.php';
require_once ROOT_PATH . '/app/controllers/admin/FooterActions.php';
require_once ROOT_PATH . '/app/controllers/admin/NavigationMenuActions.php';
require_once ROOT_PATH . '/app/controllers/admin/MediaActions.php';
require_once ROOT_PATH . '/app/controllers/admin/UserActions.php';
require_once ROOT_PATH . '/app/controllers/admin/ThemeActions.php';
require_once ROOT_PATH . '/app/controllers/admin/PagesActions.php';
require_once ROOT_PATH . '/app/controllers/admin/ProjectsActions.php';
require_once ROOT_PATH . '/app/controllers/admin/TestimonialsActions.php';
require_once ROOT_PATH . '/app/controllers/admin/SkillsActions.php';
require_once ROOT_PATH . '/app/controllers/admin/ServiceProcessStepActions.php';
require_once ROOT_PATH . '/app/controllers/admin/PricingPlanActions.php';
require_once ROOT_PATH . '/app/controllers/admin/FaqActions.php';

class AdminManagerController extends BaseController {
    use SettingsActions, HeroSectionActions, HomeCtaActions, AboutSectionActions, TimelineActions, ServicesActions, ServiceFeatureActions, FooterActions, NavigationMenuActions, MediaActions, UserActions, ThemeActions, PagesActions, ProjectsActions, TestimonialsActions, SkillsActions, ServiceProcessStepActions, PricingPlanActions, FaqActions;

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
    private $faqModel;
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
        $this->faqModel = new Faq();
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
    
}
