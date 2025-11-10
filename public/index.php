<?php
// Front controller for the portfolio-web application

// Start output buffering to prevent headers already sent errors
ob_start();

// If using PHP built-in server, let it serve existing files directly
if (php_sapi_name() === 'cli-server') {
    $url  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $file = __DIR__ . $url;
    if (is_file($file)) {
        return false;
    }
}

// Load configuration (defines ROOT_PATH and helper functions)
require_once __DIR__ . '/../config/config.php';

// Autoload core classes (simple require for this small project)
require_once ROOT_PATH . '/app/core/Router.php';

// Instantiate router and define routes
$router = new Router();

$router->get('/', 'HomeController', 'index')
       ->get('/about', 'HomeController', 'about')
       ->get('/services', 'HomeController', 'services')
       ->get('/portfolio', 'PortfolioController', 'index')
       ->get('/portfolio/{slug}', 'PortfolioController', 'show')
       ->get('/contact', 'ContactController', 'index')
       ->post('/contact', 'ContactController', 'store')

       // Admin routes
       ->get('/admin', 'AdminController', 'redirectToAdmin')
       ->get('/admin/login', 'AdminController', 'login')
       ->post('/admin/login', 'AdminController', 'authenticate')
       ->get('/admin/dashboard', 'AdminController', 'dashboard')
       ->get('/admin/messages', 'AdminController', 'messages')
       ->get('/admin/messages/{id}', 'AdminController', 'messageDetail')
       ->post('/admin/messages/{id}/toggle-read', 'AdminController', 'toggleRead')
       ->post('/admin/messages/{id}/delete', 'AdminController', 'deleteMessage')
       ->get('/admin/logout', 'AdminController', 'logout')
       
       // Admin Management routes
       ->get('/admin/settings', 'AdminManagerController', 'settings')
       ->post('/admin/settings', 'AdminManagerController', 'updateSettings')
       ->get('/admin/hero', 'AdminManagerController', 'hero')
       ->post('/admin/hero', 'AdminManagerController', 'updateHero')
    ->post('/admin/hero/stats', 'AdminManagerController', 'storeHeroStat')
    ->post('/admin/hero/stats/reorder', 'AdminManagerController', 'reorderHeroStats')
    ->post('/admin/hero/stats/{id}/edit', 'AdminManagerController', 'updateHeroStat')
    ->post('/admin/hero/stats/{id}/delete', 'AdminManagerController', 'deleteHeroStat')
     ->get('/admin/home/page-cta', 'AdminManagerController', 'homePageCta')
     ->post('/admin/home/page-cta', 'AdminManagerController', 'updateHomePageCta')
       ->get('/admin/about', 'AdminManagerController', 'about')
       ->post('/admin/about', 'AdminManagerController', 'updateAbout')
         ->get('/admin/featured-projects', 'AdminManagerController', 'featuredProjects')
         ->post('/admin/featured-projects', 'AdminManagerController', 'updateFeaturedProjects')
    ->get('/admin/timeline', 'AdminManagerController', 'timeline')
    ->get('/admin/timeline/create', 'AdminManagerController', 'createTimelineItem')
    ->post('/admin/timeline/create', 'AdminManagerController', 'createTimelineItem')
    ->get('/admin/timeline/{id}/edit', 'AdminManagerController', 'editTimelineItem')
    ->post('/admin/timeline/{id}/edit', 'AdminManagerController', 'editTimelineItem')
    ->post('/admin/timeline/{id}/delete', 'AdminManagerController', 'deleteTimelineItem')
    ->post('/admin/timeline/reorder', 'AdminManagerController', 'reorderTimeline')
    ->get('/admin/services/preview', 'AdminManagerController', 'servicesPreview')
    ->post('/admin/services/preview', 'AdminManagerController', 'updateServicesPreview')
    ->get('/admin/services', 'AdminManagerController', 'services')
    ->post('/admin/services', 'AdminManagerController', 'services')
    ->get('/admin/services/features', 'AdminManagerController', 'serviceFeatures')
    ->post('/admin/services/features/list', 'AdminManagerController', 'fetchServiceFeatures')
    ->post('/admin/services/features', 'AdminManagerController', 'storeServiceFeature')
    ->post('/admin/services/features/reorder', 'AdminManagerController', 'reorderServiceFeatures')
    ->post('/admin/services/features/{id}/update', 'AdminManagerController', 'updateServiceFeature')
    ->post('/admin/services/features/{id}/delete', 'AdminManagerController', 'deleteServiceFeature')
    ->post('/admin/services/features/{id}/toggle', 'AdminManagerController', 'toggleServiceFeature')
       ->get('/admin/services/create', 'AdminManagerController', 'createService')
       ->post('/admin/services/create', 'AdminManagerController', 'createService')
       ->get('/admin/services/{id}/edit', 'AdminManagerController', 'editService')
       ->post('/admin/services/{id}/edit', 'AdminManagerController', 'editService')
       ->post('/admin/services/{id}/delete', 'AdminManagerController', 'deleteService')
       ->get('/admin/footer', 'AdminManagerController', 'footer')
       ->post('/admin/footer', 'AdminManagerController', 'updateFooter')
       
       // Navigation Management
       ->get('/admin/navigation', 'AdminManagerController', 'navigation')
       ->get('/admin/navigation/create', 'AdminManagerController', 'createMenuItem')
       ->post('/admin/navigation/create', 'AdminManagerController', 'createMenuItem')
       ->get('/admin/navigation/{id}/edit', 'AdminManagerController', 'editMenuItem')
       ->post('/admin/navigation/{id}/edit', 'AdminManagerController', 'editMenuItem')
       ->post('/admin/navigation/{id}/delete', 'AdminManagerController', 'deleteMenuItem')
       
       // Media Management
       ->get('/admin/media', 'AdminManagerController', 'media')
       ->post('/admin/media/upload', 'AdminManagerController', 'uploadMedia')
       ->post('/admin/media/{id}/update', 'AdminManagerController', 'updateMedia')
       ->post('/admin/media/{id}/delete', 'AdminManagerController', 'deleteMedia')
       
       // User Management
       ->get('/admin/users', 'AdminManagerController', 'users')
       ->get('/admin/users/create', 'AdminManagerController', 'createUser')
       ->post('/admin/users/create', 'AdminManagerController', 'createUser')
       ->get('/admin/users/{id}/edit', 'AdminManagerController', 'editUser')
       ->post('/admin/users/{id}/edit', 'AdminManagerController', 'editUser')
       ->post('/admin/users/{id}/delete', 'AdminManagerController', 'deleteUser')
       
       // Theme Management
       ->get('/admin/theme', 'AdminManagerController', 'theme')
       ->post('/admin/theme', 'AdminManagerController', 'updateTheme')
       
       // Pages Management
       ->get('/admin/pages', 'AdminManagerController', 'pages')
       ->get('/admin/pages/{key}/edit', 'AdminManagerController', 'editPage')
       ->post('/admin/pages/{key}/edit', 'AdminManagerController', 'editPage')
       
       // Projects Management
       ->get('/admin/projects', 'AdminManagerController', 'projects')
       ->get('/admin/projects/create', 'AdminManagerController', 'createProject')
       ->post('/admin/projects/create', 'AdminManagerController', 'createProject')
       ->get('/admin/projects/{id}/edit', 'AdminManagerController', 'editProject')
       ->post('/admin/projects/{id}/edit', 'AdminManagerController', 'editProject')
       ->post('/admin/projects/{id}/delete', 'AdminManagerController', 'deleteProject')
       
    // Testimonials Management
    ->get('/admin/testimonials', 'AdminManagerController', 'testimonials')
    ->post('/admin/testimonials', 'AdminManagerController', 'storeTestimonial')
    ->post('/admin/testimonials/order', 'AdminManagerController', 'reorderTestimonials')
    ->post('/admin/testimonials/{id}/edit', 'AdminManagerController', 'updateTestimonial')
    ->post('/admin/testimonials/{id}/delete', 'AdminManagerController', 'deleteTestimonial')
       
    // Skills Management
    ->get('/admin/skills', 'AdminManagerController', 'skills')
    ->post('/admin/skills', 'AdminManagerController', 'skills');

// Resolve the current request
try {
    $router->resolve();
} catch (Exception $e) {
    http_response_code(500);
    
    // Show actual error in development mode
    if (isset($_GET['debug']) && $_GET['debug'] === '1') {
        echo "<h1>500 Internal Server Error</h1>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
        echo "<pre><strong>Stack Trace:</strong>\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else if (defined('ROOT_PATH') && file_exists(ROOT_PATH . '/app/views/errors/500.php')) {
        include ROOT_PATH . '/app/views/errors/500.php';
    } else {
        echo "<h1>500 Internal Server Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Clean up output buffer
if (ob_get_level()) {
    ob_end_flush();
}
