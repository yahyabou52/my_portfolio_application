-- Enhanced Portfolio Website Database Schema
-- Complete management system for all website content

USE portfolio_db;

-- Website settings table
CREATE TABLE IF NOT EXISTS site_settings (
    id INT(11) NOT NULL AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('text', 'textarea', 'url', 'email', 'number', 'boolean', 'json') DEFAULT 'text',
    setting_group VARCHAR(50) DEFAULT 'general',
    is_editable TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_setting (setting_key),
    INDEX idx_group (setting_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pages content table
CREATE TABLE IF NOT EXISTS pages (
    id INT(11) NOT NULL AUTO_INCREMENT,
    page_key VARCHAR(50) NOT NULL UNIQUE,
    title VARCHAR(200) NOT NULL,
    meta_description VARCHAR(300),
    content TEXT,
    sections JSON,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_page (page_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Navigation menu table
CREATE TABLE IF NOT EXISTS navigation_menu (
    id INT(11) NOT NULL AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    url VARCHAR(255) NOT NULL,
    icon VARCHAR(50),
    parent_id INT(11) DEFAULT NULL,
    sort_order INT(11) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    target VARCHAR(20) DEFAULT '_self',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (parent_id) REFERENCES navigation_menu(id) ON DELETE CASCADE,
    INDEX idx_parent (parent_id),
    INDEX idx_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Media files table
CREATE TABLE IF NOT EXISTS media_files (
    id INT(11) NOT NULL AUTO_INCREMENT,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    file_size INT(11) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    alt_text VARCHAR(255),
    title VARCHAR(255),
    uploaded_by INT(11),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (uploaded_by) REFERENCES admin_users(id) ON DELETE SET NULL,
    INDEX idx_type (file_type),
    INDEX idx_uploaded_by (uploaded_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Services table
CREATE TABLE IF NOT EXISTS services (
    id INT(11) NOT NULL AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    icon VARCHAR(100),
    features JSON,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT(11) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_active (is_active),
    INDEX idx_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Testimonials table
CREATE TABLE IF NOT EXISTS testimonials (
    id INT(11) NOT NULL AUTO_INCREMENT,
    client_name VARCHAR(100) NOT NULL,
    client_position VARCHAR(100),
    client_company VARCHAR(100),
    client_image VARCHAR(255),
    testimonial TEXT NOT NULL,
    rating INT(1) DEFAULT 5,
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT(11) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_featured (is_featured),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default site settings
INSERT INTO site_settings (setting_key, setting_value, setting_type, setting_group) VALUES
-- General Settings
('site_title', 'Yahya Bouhafs', 'text', 'general'),
('site_tagline', 'Creative UI/UX Designer', 'text', 'general'),
('site_description', 'Creative UI/UX Designer specializing in user-centered design, mobile apps, and web interfaces.', 'textarea', 'general'),
('site_keywords', 'UI, UX, Designer, Portfolio, Web Design, Mobile Apps', 'text', 'general'),
('site_logo', '', 'text', 'general'),
('favicon', '', 'text', 'general'),

-- Contact Information
('contact_email', 'hello@yourportfolio.com', 'email', 'contact'),
('contact_phone', '+1 (555) 123-4567', 'text', 'contact'),
('contact_address', '123 Design Street, Creative City, CC 12345', 'textarea', 'contact'),
('contact_hours', 'Mon-Fri: 9AM-6PM', 'text', 'contact'),

-- Social Media
('social_facebook', '', 'url', 'social'),
('social_twitter', '', 'url', 'social'),
('social_linkedin', 'https://linkedin.com/in/yourprofile', 'url', 'social'),
('social_instagram', '', 'url', 'social'),
('social_dribbble', 'https://dribbble.com/yourprofile', 'url', 'social'),
('social_behance', 'https://behance.net/yourprofile', 'url', 'social'),
('social_github', '', 'url', 'social'),

-- Footer Settings
('footer_text', '© 2024 Yahya Bouhafs. All rights reserved.', 'text', 'footer'),
('footer_links', '[]', 'json', 'footer'),
('footer_show_social', '1', 'boolean', 'footer'),
('footer_additional_text', 'Built with passion and creativity.', 'textarea', 'footer'),

-- Hero Section
('hero_title', 'Creative UI/UX Designer', 'text', 'hero'),
('hero_subtitle', 'Transforming Ideas into Beautiful Digital Experiences', 'text', 'hero'),
('hero_description', 'I create user-centered designs that combine aesthetics with functionality, helping businesses connect with their audience through exceptional digital experiences.', 'textarea', 'hero'),
('hero_cta_text', 'View My Work', 'text', 'hero'),
('hero_cta_url', '/portfolio', 'text', 'hero'),
('hero_background_image', '', 'text', 'hero'),

-- About Section
('about_title', 'About Me', 'text', 'about'),
('about_subtitle', 'Passionate about creating meaningful user experiences', 'text', 'about'),
('about_content', 'I am a dedicated UI/UX designer with over 5 years of experience in creating digital solutions that matter. My approach combines user research, creative design, and technical expertise to deliver products that users love and businesses value.', 'textarea', 'about'),
('about_image', '', 'text', 'about'),
('about_skills', '["UI Design", "UX Research", "Prototyping", "User Testing", "Figma", "Adobe XD", "Sketch", "HTML/CSS"]', 'json', 'about'),

-- Theme Settings
('primary_color', '#7B3FE4', 'text', 'theme'),
('secondary_color', '#6c757d', 'text', 'theme'),
('accent_color', '#28a745', 'text', 'theme'),
('dark_mode_enabled', '1', 'boolean', 'theme'),
('custom_css', '', 'textarea', 'theme');

-- Insert default navigation menu
INSERT INTO navigation_menu (title, url, icon, sort_order, is_active) VALUES
('Home', '/', 'bi-house', 1, 1),
('About', '/about', 'bi-person', 2, 1),
('Services', '/services', 'bi-gear', 3, 1),
('Portfolio', '/portfolio', 'bi-briefcase', 4, 1),
('Contact', '/contact', 'bi-envelope', 5, 1);

-- Insert default services
INSERT INTO services (title, description, icon, features, sort_order) VALUES
('UI/UX Design', 'Creating intuitive and engaging user interfaces that provide exceptional user experiences across all devices and platforms.', 'bi-palette', '["User Research", "Wireframing", "Prototyping", "Visual Design", "Usability Testing"]', 1),
('Web Design', 'Designing responsive and modern websites that look great on all devices while focusing on performance and user experience.', 'bi-globe', '["Responsive Design", "Performance Optimization", "SEO-Friendly", "Cross-browser Compatibility"]', 2),
('Mobile App Design', 'Crafting beautiful and functional mobile app interfaces for iOS and Android platforms with focus on user engagement.', 'bi-phone', '["iOS Design Guidelines", "Material Design", "Touch Interactions", "Mobile Optimization"]', 3),
('Brand Identity', 'Developing comprehensive brand identities including logos, color schemes, and design systems that reflect your brand values.', 'bi-award', '["Logo Design", "Brand Guidelines", "Color Palette", "Typography", "Design System"]', 4);

-- Insert default pages content
INSERT INTO pages (page_key, title, meta_description, content, sections) VALUES
('home', 'Home - UI/UX Designer Portfolio', 'Creative UI/UX Designer specializing in user-centered design, mobile apps, and web interfaces.', '', '{}'),
('about', 'About - UI/UX Designer', 'Learn about my background, experience, and approach to UI/UX design.', '', '{}'),
('services', 'Services - UI/UX Design', 'Professional UI/UX design services including wireframing, prototyping, and branding.', '', '{}'),
('portfolio', 'Portfolio - UI/UX Design Projects', 'Browse my portfolio of UI/UX design projects including mobile apps and websites.', '', '{}'),
('contact', 'Contact - Get In Touch', 'Get in touch for UI/UX design projects, consultations, or collaborations.', '', '{}');