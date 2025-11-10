<?php

require_once ROOT_PATH . '/app/core/BaseController.php';
require_once ROOT_PATH . '/app/models/Project.php';

class PortfolioController extends BaseController {
    private $projectModel;
    
    public function __construct() {
        parent::__construct();
        $this->projectModel = new Project();
    }
    
    public function index() {
        $category = $_GET['category'] ?? '';
        $search = $_GET['search'] ?? '';
        
        if ($search) {
            $projects = $this->projectModel->search($search);
        } elseif ($category) {
            $projects = $this->projectModel->getByCategory($category);
        } else {
            $projects = $this->projectModel->getPublished();
        }

        if (!empty($projects)) {
            foreach ($projects as &$project) {
                $project['technologies_list'] = $this->projectModel->getTechnologies($project['id']);
            }
            unset($project);
        }
        
        $categories = $this->projectModel->getCategories();
        
        $data = [
            'title' => 'Portfolio - UI/UX Design Projects',
            'meta_description' => 'Browse my portfolio of UI/UX design projects including mobile apps, websites, and branding work.',
            'projects' => $projects,
            'categories' => $categories,
            'current_category' => $category,
            'search_query' => $search,
            'page' => 'portfolio'
        ];
        
        $this->render('portfolio/index', 'main', $data);
    }
    
    public function show($slug) {
        $project = $this->projectModel->findBySlug($slug);
        
        if (!$project || $project['status'] !== 'published') {
            http_response_code(404);
            $this->render('errors/404', 'main', [
                'title' => 'Project Not Found',
                'page' => 'error'
            ]);
            return;
        }
        
        // Get next and previous projects
        $nextProject = $this->projectModel->getNext($project['id']);
        $previousProject = $this->projectModel->getPrevious($project['id']);
        
    $technologies = $this->projectModel->getTechnologies($project['id']);
    $gallery = $this->contentRepository ? $this->contentRepository->getProjectGallery($project['id']) : [];
        
        $data = [
            'title' => $project['title'] . ' - Portfolio',
            'meta_description' => $project['short_description'],
            'project' => $project,
            'technologies' => $technologies,
            'gallery' => $gallery,
            'next_project' => $nextProject,
            'previous_project' => $previousProject,
            'page' => 'project-detail'
        ];
        
        $this->render('portfolio/show', 'main', $data);
    }
    
    public function category($category) {
        $projects = $this->projectModel->getByCategory($category);
        $categories = $this->projectModel->getCategories();
        
        $data = [
            'title' => ucfirst($category) . ' Projects - Portfolio',
            'meta_description' => 'View my ' . $category . ' projects and design work.',
            'projects' => $projects,
            'categories' => $categories,
            'current_category' => $category,
            'page' => 'portfolio'
        ];
        
        $this->render('portfolio/index', 'main', $data);
    }
}