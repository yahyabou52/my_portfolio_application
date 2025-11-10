-- Portfolio Website Database Schema (v2)
-- This script rebuilds the content database using the new modular structure.

-- Create database
CREATE DATABASE IF NOT EXISTS portfolio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portfolio_db;

-- Reset existing tables so we start clean
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS project_images;
DROP TABLE IF EXISTS hero_stats;
DROP TABLE IF EXISTS hero_section;
DROP TABLE IF EXISTS pricing_package_features;
DROP TABLE IF EXISTS pricing_packages;
DROP TABLE IF EXISTS service_features;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS service_process_steps;
DROP TABLE IF EXISTS skills;
DROP TABLE IF EXISTS skill_categories;
DROP TABLE IF EXISTS testimonials;
DROP TABLE IF EXISTS timeline_items;
DROP TABLE IF EXISTS faq_items;
DROP TABLE IF EXISTS pages;
DROP TABLE IF EXISTS navigation_menu;
DROP TABLE IF EXISTS media_files;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS settings_site;
DROP TABLE IF EXISTS admin_users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE settings_site (
    id INT NOT NULL AUTO_INCREMENT,
    site_title VARCHAR(150) NOT NULL DEFAULT '',
    site_tagline VARCHAR(255) DEFAULT NULL,
    site_description TEXT,
    nav_cta_text VARCHAR(120) DEFAULT NULL,
    nav_cta_url VARCHAR(255) DEFAULT NULL,
    footer_text TEXT,
    logo_path VARCHAR(255) DEFAULT NULL,
    favicon_path VARCHAR(255) DEFAULT NULL,
    theme_default ENUM('light', 'dark') NOT NULL DEFAULT 'light',
    contact_email VARCHAR(150) DEFAULT NULL,
    contact_phone VARCHAR(80) DEFAULT NULL,
    contact_address TEXT,
    social_links JSON DEFAULT NULL,
    footer_links JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Hero headline content
CREATE TABLE hero_section (
    id INT NOT NULL AUTO_INCREMENT,
    hero_intro_prefix VARCHAR(120) DEFAULT NULL,
    hero_intro_name_first VARCHAR(120) DEFAULT NULL,
    hero_intro_name_rest VARCHAR(120) DEFAULT NULL,
    hero_intro_suffix VARCHAR(120) DEFAULT NULL,
    hero_title VARCHAR(200) DEFAULT NULL,
    hero_subtitle VARCHAR(255) DEFAULT NULL,
    hero_description TEXT,
    hero_primary_cta_text VARCHAR(150) DEFAULT NULL,
    hero_primary_cta_url VARCHAR(255) DEFAULT NULL,
    hero_secondary_cta_text VARCHAR(150) DEFAULT NULL,
    hero_secondary_cta_url VARCHAR(255) DEFAULT NULL,
    hero_background_image_path VARCHAR(255) DEFAULT NULL,
    hero_background_image_alt VARCHAR(150) DEFAULT NULL,
    scroll_indicator_text VARCHAR(150) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Hero statistic badges
CREATE TABLE hero_stats (
    id INT NOT NULL AUTO_INCREMENT,
    hero_id INT NOT NULL,
    label VARCHAR(120) NOT NULL,
    value VARCHAR(60) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_hero_id (hero_id),
    INDEX idx_sort_order (sort_order),
    CONSTRAINT fk_hero_stats_hero
        FOREIGN KEY (hero_id) REFERENCES hero_section(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Portfolio projects
CREATE TABLE projects (
    id INT NOT NULL AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    short_description VARCHAR(500) DEFAULT NULL,
    description TEXT,
    category VARCHAR(120) DEFAULT NULL,
    technologies JSON DEFAULT NULL,
    main_image_path VARCHAR(255) DEFAULT NULL,
    main_image_alt VARCHAR(150) DEFAULT NULL,
    client_name VARCHAR(150) DEFAULT NULL,
    client_visibility ENUM('yes', 'no') NOT NULL DEFAULT 'yes',
    project_url VARCHAR(255) DEFAULT NULL,
    featured TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    featured_sort_order INT NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_projects_slug (slug),
    INDEX idx_projects_category (category),
    INDEX idx_projects_featured (featured),
    INDEX idx_projects_featured_order (featured_sort_order),
    INDEX idx_projects_status (status),
    INDEX idx_projects_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Project gallery images
CREATE TABLE project_images (
    id INT NOT NULL AUTO_INCREMENT,
    project_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    caption VARCHAR(255) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_project_images_project (project_id),
    INDEX idx_project_images_sort (sort_order),
    CONSTRAINT fk_project_images_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Services catalogue
CREATE TABLE services (
    id INT NOT NULL AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    icon VARCHAR(120) DEFAULT NULL,
    description TEXT,
    price_label VARCHAR(120) DEFAULT NULL,
    price_amount DECIMAL(10,2) DEFAULT NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'published',
    homepage_featured TINYINT(1) NOT NULL DEFAULT 0,
    homepage_is_visible TINYINT(1) NOT NULL DEFAULT 1,
    homepage_sort_order INT NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_services_sort_order (sort_order),
    INDEX idx_services_homepage_featured (homepage_featured),
    INDEX idx_services_homepage_sort_order (homepage_sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Structured service process steps
CREATE TABLE service_process_steps (
    id INT NOT NULL AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    icon VARCHAR(120) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_process_steps_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Service feature bullets
CREATE TABLE service_features (
    id INT NOT NULL AUTO_INCREMENT,
    service_id INT NOT NULL,
    feature_text VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_service_features_service (service_id),
    INDEX idx_service_features_sort (sort_order),
    CONSTRAINT fk_service_features_service
        FOREIGN KEY (service_id) REFERENCES services(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pricing packages for services
CREATE TABLE pricing_packages (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    price_label VARCHAR(120) DEFAULT NULL,
    price_amount DECIMAL(10,2) DEFAULT NULL,
    price_period VARCHAR(60) DEFAULT NULL,
    description VARCHAR(255) DEFAULT NULL,
    badge_text VARCHAR(120) DEFAULT NULL,
    cta_text VARCHAR(120) DEFAULT NULL,
    cta_url VARCHAR(255) DEFAULT NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'published',
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_pricing_packages_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pricing package bullet points
CREATE TABLE pricing_package_features (
    id INT NOT NULL AUTO_INCREMENT,
    package_id INT NOT NULL,
    feature_text VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_package_features_package (package_id),
    INDEX idx_package_features_sort (sort_order),
    CONSTRAINT fk_package_features_package
        FOREIGN KEY (package_id) REFERENCES pricing_packages(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Skill categories (Design, Tools, etc.)
CREATE TABLE skill_categories (
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

-- Individual skills within a category
CREATE TABLE skills (
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

-- Testimonials / reviews
CREATE TABLE testimonials (
    id INT NOT NULL AUTO_INCREMENT,
    client_name VARCHAR(150) NOT NULL,
    client_position VARCHAR(150) DEFAULT NULL,
    client_company VARCHAR(150) DEFAULT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    rating TINYINT UNSIGNED DEFAULT 5,
    testimonial_text TEXT NOT NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_testimonials_featured (is_featured),
    INDEX idx_testimonials_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Career timeline (work/education)
CREATE TABLE timeline_items (
    id INT NOT NULL AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    organization VARCHAR(200) DEFAULT NULL,
    date_range VARCHAR(120) DEFAULT NULL,
    description TEXT,
    tags JSON DEFAULT NULL,
    is_education TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_timeline_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE faq_items (
    id INT NOT NULL AUTO_INCREMENT,
    page ENUM('services', 'contact', 'global') NOT NULL DEFAULT 'services',
    question VARCHAR(255) NOT NULL,
    answer TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_faq_page (page),
    INDEX idx_faq_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE pages (
    id INT NOT NULL AUTO_INCREMENT,
    page_key VARCHAR(100) NOT NULL,
    title VARCHAR(200) DEFAULT NULL,
    meta_description VARCHAR(300) DEFAULT NULL,
    content MEDIUMTEXT,
    sections JSON DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pages_page_key (page_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE navigation_menu (
    id INT NOT NULL AUTO_INCREMENT,
    title VARCHAR(150) NOT NULL,
    url VARCHAR(255) DEFAULT NULL,
    icon VARCHAR(120) DEFAULT NULL,
    parent_id INT DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    target VARCHAR(20) DEFAULT '_self',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_navigation_parent (parent_id),
    CONSTRAINT fk_navigation_parent
        FOREIGN KEY (parent_id) REFERENCES navigation_menu(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Media library for uploads
CREATE TABLE media_files (
    id INT NOT NULL AUTO_INCREMENT,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) DEFAULT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type ENUM('image', 'document', 'other') NOT NULL DEFAULT 'image',
    file_size INT DEFAULT NULL,
    mime_type VARCHAR(150) DEFAULT NULL,
    alt_text VARCHAR(255) DEFAULT NULL,
    title VARCHAR(255) DEFAULT NULL,
    uploaded_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_media_file_type (file_type),
    INDEX idx_media_uploaded_by (uploaded_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Contact form submissions
CREATE TABLE messages (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    responded_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_messages_created_at (created_at),
    INDEX idx_messages_is_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin accounts
CREATE TABLE admin_users (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) DEFAULT NULL,
    username VARCHAR(60) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'editor') NOT NULL DEFAULT 'editor',
    last_login_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_username (username),
    UNIQUE KEY uq_admin_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed base records
INSERT INTO settings_site (id, site_title, site_tagline, site_description, nav_cta_text, nav_cta_url, footer_text, theme_default)
VALUES (1, 'Yahya Bouhafs', 'UI/UX Designer & Product Strategist', 'Designing intuitive digital products that merge empathy, strategy, and polish.', 'Let''s Collaborate', '/contact', 'Copyright 2025 Yahya Bouhafs. All rights reserved.', 'light')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

INSERT INTO hero_section (
    id,
    hero_intro_prefix,
    hero_intro_name_first,
    hero_intro_name_rest,
    hero_intro_suffix,
    hero_title,
    hero_subtitle,
    hero_description,
    hero_primary_cta_text,
    hero_primary_cta_url,
    hero_secondary_cta_text,
    hero_secondary_cta_url,
    hero_background_image_path,
    hero_background_image_alt,
    scroll_indicator_text
) VALUES (
    1,
    'Hi, I''m',
    'Yahya',
    'Bouhafs',
    '- UI/UX Designer',
    'Designing products people trust',
    'UI/UX designer focused on shipping thoughtful experiences for SaaS and startups.',
    'From discovery to delivery, I help teams turn insight into beautiful, accessible interfaces.',
    'View Portfolio',
    '/portfolio',
    'Let''s Collaborate',
    '/contact',
    'assets/images/hero-portrait.jpg',
    'Portrait of Yahya Bouhafs',
    'Scroll to explore'
) ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

INSERT INTO admin_users (name, username, email, password, role)
VALUES ('Primary Admin', 'admin', 'admin@portfolio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;