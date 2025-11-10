-- Insert default data for complete site management
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

-- Insert default pages (if not exists)
INSERT IGNORE INTO pages (page_key, title, slug, meta_description, content, is_active) VALUES 
('home', 'Home', 'home', 'Welcome to my portfolio - Creative Designer & Developer', '<h1>Welcome to My Portfolio</h1><p>This is the home page content that can be edited from the admin panel.</p>', 1),
('about', 'About Me', 'about', 'Learn more about my experience and skills', '<h1>About Me</h1><p>This is the about page content that can be fully customized.</p>', 1),
('services', 'Services', 'services', 'Discover the services I offer', '<h1>My Services</h1><p>This is the services page content.</p>', 1),
('portfolio', 'Portfolio', 'portfolio', 'View my latest projects and work', '<h1>My Portfolio</h1><p>This is the portfolio page content.</p>', 1),
('contact', 'Contact', 'contact', 'Get in touch with me', '<h1>Contact Me</h1><p>This is the contact page content.</p>', 1),
('privacy', 'Privacy Policy', 'privacy-policy', 'Privacy policy and data protection information', '<h1>Privacy Policy</h1><p>Your privacy policy content goes here.</p>', 1),
('terms', 'Terms of Service', 'terms-of-service', 'Terms and conditions of service', '<h1>Terms of Service</h1><p>Your terms of service content goes here.</p>', 1);

-- Insert default navigation menu (if not exists)
INSERT IGNORE INTO navigation_menus (title, url, sort_order, menu_location) VALUES 
('Home', '#home', 1, 'header'),
('About', '#about', 2, 'header'),
('Services', '#services', 3, 'header'),
('Portfolio', '#portfolio', 4, 'header'),
('Contact', '#contact', 5, 'header');

-- Insert sample services (if not exists)
INSERT IGNORE INTO services (title, slug, short_description, full_description, icon, is_featured, is_active, sort_order) VALUES 
('UI/UX Design', 'ui-ux-design', 'Beautiful and intuitive user interface design', 'Complete UI/UX design services including user research, wireframing, prototyping, and visual design. I create user-centered designs that are both beautiful and functional.', 'fas fa-paint-brush', 1, 1, 1),
('Web Development', 'web-development', 'Modern and responsive website development', 'Full-stack web development using the latest technologies. From simple landing pages to complex web applications, I build websites that perform excellently across all devices.', 'fas fa-code', 1, 1, 2),
('Mobile App Design', 'mobile-app-design', 'Native and cross-platform mobile app design', 'Mobile app design for iOS and Android platforms. I create engaging mobile experiences that users love, focusing on usability and performance.', 'fas fa-mobile-alt', 1, 1, 3),
('Branding & Identity', 'branding-identity', 'Complete brand identity and logo design', 'Brand identity design including logo design, color schemes, typography, and brand guidelines. I help businesses create a strong and memorable brand presence.', 'fas fa-palette', 0, 1, 4);

INSERT IGNORE INTO skill_categories (title, icon_class, sort_order, is_visible) VALUES
('Product & UX', 'bi-lightning-charge', 1, 1),
('Interface & Visual', 'bi-brush', 2, 1),
('Tools & Platforms', 'bi-cpu', 3, 1);

INSERT IGNORE INTO skills (category_id, name, proficiency_level, sort_order, is_visible) VALUES
((SELECT id FROM skill_categories WHERE title = 'Product & UX' LIMIT 1), 'User Research & Testing', 90, 1, 1),
((SELECT id FROM skill_categories WHERE title = 'Product & UX' LIMIT 1), 'Product Strategy', 85, 2, 1),
((SELECT id FROM skill_categories WHERE title = 'Product & UX' LIMIT 1), 'Information Architecture', 88, 3, 1),
((SELECT id FROM skill_categories WHERE title = 'Interface & Visual' LIMIT 1), 'UI Design Systems', 92, 1, 1),
((SELECT id FROM skill_categories WHERE title = 'Interface & Visual' LIMIT 1), 'Motion & Micro-interactions', 80, 2, 1),
((SELECT id FROM skill_categories WHERE title = 'Interface & Visual' LIMIT 1), 'Design Accessibility (WCAG)', 87, 3, 1),
((SELECT id FROM skill_categories WHERE title = 'Tools & Platforms' LIMIT 1), 'Figma', 95, 1, 1),
((SELECT id FROM skill_categories WHERE title = 'Tools & Platforms' LIMIT 1), 'Framer', 75, 2, 1),
((SELECT id FROM skill_categories WHERE title = 'Tools & Platforms' LIMIT 1), 'React + Storybook', 70, 3, 1);

-- Insert sample projects (if not exists)
INSERT IGNORE INTO projects (title, slug, short_description, full_description, image_url, project_url, technologies, category, is_featured, is_active, sort_order) VALUES 
('E-Commerce Platform', 'ecommerce-platform', 'Modern e-commerce solution with advanced features', 'Complete e-commerce platform built with React and Node.js. Features include product management, shopping cart, payment integration, and admin dashboard.', 'assets/images/projects/ecommerce.jpg', 'https://demo-ecommerce.com', '["React", "Node.js", "MongoDB", "Stripe"]', 'Web Development', 1, 1, 1),
('Mobile Banking App', 'mobile-banking-app', 'Secure banking application for iOS and Android', 'Mobile banking app with biometric authentication, real-time transactions, and comprehensive financial management tools.', 'assets/images/projects/banking-app.jpg', 'https://github.com/johndoe/banking-app', '["React Native", "Firebase", "Node.js"]', 'Mobile Development', 1, 1, 2),
('Healthcare Dashboard', 'healthcare-dashboard', 'Patient management system for healthcare providers', 'Comprehensive dashboard for healthcare professionals to manage patients, appointments, and medical records.', 'assets/images/projects/healthcare.jpg', 'https://healthcare-demo.com', '["Vue.js", "Laravel", "MySQL"]', 'Web Development', 0, 1, 3);

-- Insert sample testimonials (if not exists)
INSERT IGNORE INTO testimonials (client_name, client_position, client_company, testimonial_text, rating, is_featured, is_active, sort_order) VALUES 
('Sarah Johnson', 'CEO', 'TechStart Inc.', 'John delivered an exceptional website that exceeded our expectations. His attention to detail and creative approach helped us increase our conversion rate by 40%.', 5, 1, 1, 1),
('Michael Chen', 'Product Manager', 'InnovateCorp', 'Working with John was a pleasure. He understood our vision perfectly and created a mobile app that our users absolutely love. Highly recommended!', 5, 1, 1, 2),
('Emily Rodriguez', 'Marketing Director', 'GrowthAgency', 'John''s UI/UX design skills are outstanding. He transformed our complex platform into an intuitive and beautiful user experience.', 5, 0, 1, 3);