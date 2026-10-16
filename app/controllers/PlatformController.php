<?php
require_once ROOT_PATH . '/app/core/SiteProvisioner.php';
final class PlatformController {
    public function handle(string $path): void {
        session_start_safe();
        $_SESSION['platform_csrf'] ??= bin2hex(random_bytes(32));
        $error = '';
        if (!in_array($path, ['/', '/signup', '/login'], true)) { http_response_code(404); echo 'Page not found'; return; }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!hash_equals($_SESSION['platform_csrf'], (string)($_POST['csrf_token'] ?? ''))) { http_response_code(403); exit('Please reload the form.'); }
            $slug = strtolower(trim((string)($_POST['slug'] ?? '')));
            if (!TenantContext::validSlug($slug)) { $error = 'Use 3–40 lowercase letters, numbers, or hyphens for your website address.'; }
            elseif ($path === '/login') { header('Location: ' . PLATFORM_URL . '/s/' . $slug . '/admin/login'); exit; }
            elseif ($path === '/signup') {
                $name = trim((string)($_POST['name'] ?? ''));
                $email = trim((string)($_POST['email'] ?? ''));
                $password = (string)($_POST['password'] ?? '');
                if ($name === '' || mb_strlen($name) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190 || strlen($password) < 12 || strlen($password) > 72) {
                    $error = 'Enter your name, a valid email, and a password with 12–72 characters.';
                } else {
                    try {
                        $answers=['profession'=>trim((string)($_POST['profession']??'')),'intro'=>trim((string)($_POST['intro']??'')),'contact_email'=>trim((string)($_POST['contact_email']??''))];if(isset($_POST['setup_sections']))$answers['sections']=(array)($_POST['sections']??[]);
                        require_once ROOT_PATH.'/app/core/builder/SiteDocument.php';SiteDocument::validate(SiteDocument::fresh($answers+['name'=>$name]));
                        $site = SiteProvisioner::create($name, $slug, $email, $password,$answers,$_FILES['photo']??null);
                        header('Location: ' . PLATFORM_URL . '/s/' . $site['slug'] . '/admin/login?created=1'); exit;
                    } catch (InvalidArgumentException $e) { $error=$e->getMessage(); } catch (PDOException $e) {
                        error_log($e->getMessage());
                        $error = $e->getCode() === '23000' ? 'That website address is already taken. Choose another.' : 'Your website could not be created. Please try again.';
                    } catch (Throwable $e) { error_log($e->getMessage()); $error = 'Your website could not be created. Please try again.'; }
                }
            }
        }
        require ROOT_PATH . '/app/views/platform/index.php';
    }
}
