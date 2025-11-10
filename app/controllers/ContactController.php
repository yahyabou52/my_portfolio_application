<?php

require_once ROOT_PATH . '/app/core/BaseController.php';
require_once ROOT_PATH . '/app/models/Message.php';

class ContactController extends BaseController {
    private $messageModel;
    
    public function __construct() {
        parent::__construct();
        $this->messageModel = new Message();
    }
    
    public function index() {
        $data = [
            'title' => 'Contact - Get In Touch',
            'meta_description' => 'Get in touch for UI/UX design projects, consultations, or collaborations. Let\'s discuss your design needs.',
            'page' => 'contact'
        ];
        
        $this->render('contact/index', 'main', $data);
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('contact');
            return;
        }
        
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'subject' => trim($_POST['subject'] ?? ''),
            'message' => trim($_POST['message'] ?? '')
        ];
        
        // Validate the data
        $errors = $this->messageModel->validate($data);
        
        if (!empty($errors)) {
            $this->setFlash('error', 'Please correct the errors below.');
            $this->setFlash('form_errors', $errors);
            $this->setFlash('form_data', $data);
            $this->redirect('contact');
            return;
        }
        
        try {
            // Save message to database
            $messageId = $this->messageModel->create($data);
            
            if ($messageId) {
                // Send email notification (optional)
                $this->sendEmailNotification($data);
                
                $this->setFlash('success', 'Thank you for your message! I\'ll get back to you soon.');
            } else {
                $this->setFlash('error', 'Sorry, there was an error sending your message. Please try again.');
            }
            
        } catch (Exception $e) {
            error_log('Contact form error: ' . $e->getMessage());
            $this->setFlash('error', 'Sorry, there was an error sending your message. Please try again.');
        }
        
        $this->redirect('contact');
    }
    
    private function sendEmailNotification($data) {
        // Basic email notification
        // In a real application, you might want to use a more robust email library
        
        $to = 'admin@portfolio.com'; // Change to your email
        $subject = 'New Contact Form Submission: ' . $data['subject'];
        
        $message = "New contact form submission:\n\n";
        $message .= "Name: " . $data['name'] . "\n";
        $message .= "Email: " . $data['email'] . "\n";
        $message .= "Subject: " . $data['subject'] . "\n\n";
        $message .= "Message:\n" . $data['message'] . "\n\n";
        $message .= "Submitted: " . date('Y-m-d H:i:s') . "\n";
        
        $headers = "From: noreply@portfolio.com\r\n";
        $headers .= "Reply-To: " . $data['email'] . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        // Send email (uncomment in production)
        // mail($to, $subject, $message, $headers);
        
        // For development, you can log the email instead
        error_log("Email would be sent to: $to\nSubject: $subject\nMessage: $message");
    }
}