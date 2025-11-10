-- Insert default data that matches existing table structures
USE portfolio_db;

-- Insert default site settings (if not exists)
INSERT IGNORE INTO settings (setting_group, setting_key, setting_value, setting_type, description) VALUES 
-- General Settings
('general', 'site_name', 'My Portfolio', 'text', 'Website name'),
('general', 'site_tagline', 'Creative Designer & Developer', 'text', 'Website tagline'),
('general', 'contact_email', 'hello@portfolio.com', 'text', 'Contact email address'),
('general', 'contact_phone', '+1 (555) 123-4567', 'text', 'Contact phone number'),
('general', 'address', 'New York, NY, USA', 'text', 'Business address'),

-- Hero Section
('hero', 'title', 'Hello, I''m John Doe', 'text', 'Hero section main title'),
('hero', 'subtitle', 'Creative Designer & Developer', 'text', 'Hero section subtitle'),
('hero', 'description', 'I create beautiful and functional digital experiences that help businesses grow and succeed in the digital world.', 'textarea', 'Hero section description'),
('hero', 'cta_text', 'View My Work', 'text', 'Call to action button text'),
('hero', 'cta_url', '#portfolio', 'text', 'Call to action button URL'),
('hero', 'background_image', 'assets/images/hero-bg.jpg', 'text', 'Hero background image'),

-- About Section
('about', 'title', 'About Me', 'text', 'About section title'),
('about', 'subtitle', 'Creative Designer with 5+ Years Experience', 'text', 'About section subtitle'),
('about', 'description', 'I am a passionate designer and developer with over 5 years of experience creating digital solutions. I specialize in UI/UX design, web development, and mobile app design.', 'textarea', 'About section description'),
('about', 'image', 'assets/images/about-me.jpg', 'text', 'About section image'),
('about', 'cv_url', 'assets/files/john-doe-cv.pdf', 'text', 'CV/Resume download URL'),

-- Footer Settings
('footer', 'copyright_text', '© 2025 John Doe. All rights reserved.', 'text', 'Footer copyright text'),
('footer', 'social_facebook', 'https://facebook.com/johndoe', 'text', 'Facebook URL'),
('footer', 'social_twitter', 'https://twitter.com/johndoe', 'text', 'Twitter URL'),
('footer', 'social_linkedin', 'https://linkedin.com/in/johndoe', 'text', 'LinkedIn URL'),
('footer', 'social_instagram', 'https://instagram.com/johndoe', 'text', 'Instagram URL'),
('footer', 'social_github', 'https://github.com/johndoe', 'text', 'GitHub URL'),

-- Theme Settings
('theme', 'primary_color', '#007bff', 'text', 'Primary theme color'),
('theme', 'secondary_color', '#6c757d', 'text', 'Secondary theme color'),
('theme', 'accent_color', '#28a745', 'text', 'Accent theme color'),
('theme', 'font_primary', 'Inter', 'text', 'Primary font family'),
('theme', 'font_secondary', 'Poppins', 'text', 'Secondary font family');

-- Insert default pages (matching existing table structure)
INSERT IGNORE INTO pages (page_key, title, meta_description, content, is_active) VALUES 
('home', 'Home Page', 'Welcome to my portfolio - Creative Designer & Developer', '<h1>Welcome to My Portfolio</h1><p>This is the home page content that can be edited from the admin panel.</p>', 1),
('about', 'About Me', 'Learn more about my experience and skills', '<h1>About Me</h1><p>This is the about page content that can be fully customized.</p>', 1),
('services', 'Services', 'Discover the services I offer', '<h1>My Services</h1><p>This is the services page content.</p>', 1),
('portfolio', 'Portfolio', 'View my latest projects and work', '<h1>My Portfolio</h1><p>This is the portfolio page content.</p>', 1),
('contact', 'Contact', 'Get in touch with me', '<h1>Contact Me</h1><p>This is the contact page content.</p>', 1),
('privacy', 'Privacy Policy', 'Privacy policy and data protection information', '<h1>Privacy Policy</h1><p>Your privacy policy content goes here.</p>', 1),
('terms', 'Terms of Service', 'Terms and conditions of service', '<h1>Terms of Service</h1><p>Your terms of service content goes here.</p>', 1);