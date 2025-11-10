-- Complete Portfolio Website Database Schema
-- ALL tables needed for complete site management

CREATE DATABASE IF NOT EXISTS portfolio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portfolio_db;

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor', 'moderator') DEFAULT 'admin',
    is_active TINYINT(1) DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Site settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT(11) NOT NULL AUTO_INCREMENT,
    setting_group VARCHAR(50) NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'textarea', 'number', 'boolean', 'json') DEFAULT 'text',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_group_key (setting_group, setting_key),
    INDEX idx_group (setting_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pages table for static content management
CREATE TABLE IF NOT EXISTS pages (
    id INT(11) NOT NULL AUTO_INCREMENT,
    page_key VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    meta_description TEXT,
    meta_keywords VARCHAR(500),
    content LONGTEXT,
    sections JSON,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_page_key (page_key),
    INDEX idx_slug (slug),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Services table
CREATE TABLE IF NOT EXISTS services (
    id INT(11) NOT NULL AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    short_description TEXT,
    full_description LONGTEXT,
    icon VARCHAR(100),
    image_url VARCHAR(500),
    price_from DECIMAL(10,2),
    features JSON,
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_slug (slug),
    INDEX idx_featured (is_featured),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Projects table for portfolio showcase
CREATE TABLE IF NOT EXISTS projects (
    id INT(11) NOT NULL AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    short_description TEXT,
    full_description LONGTEXT,
    image_url VARCHAR(500),
    gallery JSON,
    project_url VARCHAR(500),
    github_url VARCHAR(500),
    technologies JSON,
    category VARCHAR(100),
    client VARCHAR(200),
    client_visibility ENUM('yes', 'no') DEFAULT 'yes',
    project_date DATE,
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    views_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_slug (slug),
    INDEX idx_category (category),
    INDEX idx_featured (is_featured),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contact messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    read_at TIMESTAMP NULL,
    replied TINYINT(1) DEFAULT 0,
    replied_at TIMESTAMP NULL,
    priority ENUM('low', 'normal', 'high') DEFAULT 'normal',
    status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_status (status),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Navigation menu table
CREATE TABLE IF NOT EXISTS navigation_menus (
    id INT(11) NOT NULL AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    url VARCHAR(500),
    target ENUM('_self', '_blank') DEFAULT '_self',
    icon VARCHAR(100),
    parent_id INT(11) NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    menu_location ENUM('header', 'footer', 'sidebar') DEFAULT 'header',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (parent_id) REFERENCES navigation_menus(id) ON DELETE CASCADE,
    INDEX idx_parent (parent_id),
    INDEX idx_location (menu_location),
    INDEX idx_active (is_active),
    INDEX idx_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Media/files table
CREATE TABLE IF NOT EXISTS media (
    id INT(11) NOT NULL AUTO_INCREMENT,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_type ENUM('image', 'document', 'video', 'audio', 'other') NOT NULL,
    alt_text VARCHAR(255),
    caption TEXT,
    dimensions VARCHAR(20),
    uploaded_by INT(11),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (uploaded_by) REFERENCES admin_users(id) ON DELETE SET NULL,
    INDEX idx_type (file_type),
    INDEX idx_mime (mime_type),
    INDEX idx_uploaded_by (uploaded_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Testimonials table
CREATE TABLE IF NOT EXISTS testimonials (
    id INT(11) NOT NULL AUTO_INCREMENT,
    client_name VARCHAR(100) NOT NULL,
    client_position VARCHAR(100),
    client_company VARCHAR(100),
    client_image VARCHAR(500),
    testimonial_text TEXT NOT NULL,
    rating INT DEFAULT 5,
    project_id INT(11) NULL,
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    INDEX idx_featured (is_featured),
    INDEX idx_active (is_active),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Blog posts table (for future blog functionality)
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT(11) NOT NULL AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    excerpt TEXT,
    content LONGTEXT NOT NULL,
    featured_image VARCHAR(500),
    meta_description TEXT,
    meta_keywords VARCHAR(500),
    author_id INT(11) NOT NULL,
    category VARCHAR(100),
    tags JSON,
    is_published TINYINT(1) DEFAULT 0,
    is_featured TINYINT(1) DEFAULT 0,
    views_count INT DEFAULT 0,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (author_id) REFERENCES admin_users(id) ON DELETE CASCADE,
    INDEX idx_slug (slug),
    INDEX idx_published (is_published),
    INDEX idx_featured (is_featured),
    INDEX idx_category (category),
    INDEX idx_published_at (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Skill categories
CREATE TABLE IF NOT EXISTS skill_categories (
    id INT NOT NULL AUTO_INCREMENT,
    title VARCHAR(150) NOT NULL,
    icon_class VARCHAR(120) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_skill_categories_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Skills belonging to categories
CREATE TABLE IF NOT EXISTS skills (
    id INT NOT NULL AUTO_INCREMENT,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    proficiency_level TINYINT UNSIGNED NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_skills_category (category_id),
    INDEX idx_skills_sort (sort_order),
    CONSTRAINT fk_skills_category
        FOREIGN KEY (category_id) REFERENCES skill_categories(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user
INSERT INTO admin_users (username, email, password, role) VALUES 
('admin', 'admin@portfolio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE username = username;

-- Insert default site settings
INSERT INTO settings (setting_group, setting_key, setting_value, setting_type, description) VALUES 
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
('theme', 'font_secondary', 'Poppins', 'text', 'Secondary font family')

ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Insert default pages
INSERT INTO pages (page_key, title, slug, meta_description, content, is_active) VALUES 
('home', 'Home', 'home', 'Welcome to my portfolio - Creative Designer & Developer', '<h1>Welcome to My Portfolio</h1><p>This is the home page content.</p>', 1),
('about', 'About Me', 'about', 'Learn more about my experience and skills', '<h1>About Me</h1><p>This is the about page content.</p>', 1),
('services', 'Services', 'services', 'Discover the services I offer', '<h1>My Services</h1><p>This is the services page content.</p>', 1),
('portfolio', 'Portfolio', 'portfolio', 'View my latest projects and work', '<h1>My Portfolio</h1><p>This is the portfolio page content.</p>', 1),
('contact', 'Contact', 'contact', 'Get in touch with me', '<h1>Contact Me</h1><p>This is the contact page content.</p>', 1),
('privacy', 'Privacy Policy', 'privacy-policy', 'Privacy policy and data protection information', '<h1>Privacy Policy</h1><p>This is the privacy policy content.</p>', 1),
('terms', 'Terms of Service', 'terms-of-service', 'Terms and conditions of service', '<h1>Terms of Service</h1><p>This is the terms of service content.</p>', 1)

ON DUPLICATE KEY UPDATE title = VALUES(title);

-- Insert default navigation menu
INSERT INTO navigation_menus (title, url, sort_order, menu_location) VALUES 
('Home', '#home', 1, 'header'),
('About', '#about', 2, 'header'),
('Services', '#services', 3, 'header'),
('Portfolio', '#portfolio', 4, 'header'),
('Contact', '#contact', 5, 'header')

ON DUPLICATE KEY UPDATE title = VALUES(title);

-- Insert sample services
INSERT INTO services (title, slug, short_description, full_description, icon, is_featured, is_active, sort_order) VALUES 
('UI/UX Design', 'ui-ux-design', 'Beautiful and intuitive user interface design', 'Complete UI/UX design services including user research, wireframing, prototyping, and visual design. I create user-centered designs that are both beautiful and functional.', 'fas fa-paint-brush', 1, 1, 1),
('Web Development', 'web-development', 'Modern and responsive website development', 'Full-stack web development using the latest technologies. From simple landing pages to complex web applications, I build websites that perform excellently across all devices.', 'fas fa-code', 1, 1, 2),
('Mobile App Design', 'mobile-app-design', 'Native and cross-platform mobile app design', 'Mobile app design for iOS and Android platforms. I create engaging mobile experiences that users love, focusing on usability and performance.', 'fas fa-mobile-alt', 1, 1, 3),
('Branding & Identity', 'branding-identity', 'Complete brand identity and logo design', 'Brand identity design including logo design, color schemes, typography, and brand guidelines. I help businesses create a strong and memorable brand presence.', 'fas fa-palette', 0, 1, 4)

ON DUPLICATE KEY UPDATE title = VALUES(title);

INSERT INTO skill_categories (title, icon_class, sort_order, is_visible) VALUES
('Product & UX', 'bi-lightning-charge', 1, 1),
('Interface & Visual', 'bi-brush', 2, 1),
('Tools & Platforms', 'bi-cpu', 3, 1)
ON DUPLICATE KEY UPDATE title = VALUES(title);

INSERT INTO skills (category_id, name, proficiency_level, sort_order, is_visible) VALUES
((SELECT id FROM skill_categories WHERE title = 'Product & UX' LIMIT 1), 'User Research & Testing', 90, 1, 1),
((SELECT id FROM skill_categories WHERE title = 'Product & UX' LIMIT 1), 'Product Strategy', 85, 2, 1),
((SELECT id FROM skill_categories WHERE title = 'Product & UX' LIMIT 1), 'Information Architecture', 88, 3, 1),
((SELECT id FROM skill_categories WHERE title = 'Interface & Visual' LIMIT 1), 'UI Design Systems', 92, 1, 1),
((SELECT id FROM skill_categories WHERE title = 'Interface & Visual' LIMIT 1), 'Motion & Micro-interactions', 80, 2, 1),
((SELECT id FROM skill_categories WHERE title = 'Interface & Visual' LIMIT 1), 'Design Accessibility (WCAG)', 87, 3, 1),
((SELECT id FROM skill_categories WHERE title = 'Tools & Platforms' LIMIT 1), 'Figma', 95, 1, 1),
((SELECT id FROM skill_categories WHERE title = 'Tools & Platforms' LIMIT 1), 'Framer', 75, 2, 1),
((SELECT id FROM skill_categories WHERE title = 'Tools & Platforms' LIMIT 1), 'React + Storybook', 70, 3, 1)
ON DUPLICATE KEY UPDATE name = VALUES(name);