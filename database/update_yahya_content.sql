-- Content refresh for Yahya Bouhafs UI/UX portfolio
USE portfolio_db;

UPDATE settings_site
SET site_title = 'Yahya Bouhafs',
    site_tagline = 'UI/UX Designer & Product Strategist',
    site_description = 'Designing intuitive digital products that merge empathy, strategy, and polish.',
    nav_cta_text = 'Let''s Collaborate',
    nav_cta_url = '/contact',
    footer_text = 'Copyright 2025 Yahya Bouhafs. All rights reserved.',
    contact_email = 'hello@yahyabouhafs.com',
    contact_phone = '+213 555 010 210',
    contact_address = 'Algiers, Algeria\nCollaborating remotely worldwide',
    social_links = JSON_ARRAY(
        JSON_OBJECT('platform', 'linkedin', 'url', 'https://linkedin.com/in/yahyabouhafs'),
        JSON_OBJECT('platform', 'dribbble', 'url', 'https://dribbble.com/yahyabouhafs'),
        JSON_OBJECT('platform', 'behance', 'url', 'https://behance.net/yahyabouhafs'),
        JSON_OBJECT('platform', 'github', 'url', 'https://github.com/yahyabouhafs')
    ),
    footer_links = JSON_ARRAY(
        JSON_OBJECT('title', 'Services', 'url', '/services', 'target', '_self'),
        JSON_OBJECT('title', 'Portfolio', 'url', '/portfolio', 'target', '_self'),
        JSON_OBJECT('title', 'Contact', 'url', '/contact', 'target', '_self')
    ),
    updated_at = NOW()
WHERE id = 1;

UPDATE hero_section
SET hero_intro_prefix = 'Hi, I''m',
    hero_intro_name_first = 'Yahya',
    hero_intro_name_rest = 'Bouhafs',
    hero_intro_suffix = '- UI/UX Designer',
    hero_title = 'Product-Focused UI/UX Designer',
    hero_subtitle = 'Crafting delightful journeys for modern digital products',
    hero_description = 'I partner with product teams to translate insights into inclusive interfaces that drive measurable outcomes.',
    hero_primary_cta_text = 'View Portfolio',
    hero_primary_cta_url = '/portfolio',
    hero_secondary_cta_text = 'Let''s Collaborate',
    hero_secondary_cta_url = '/contact',
    hero_background_image_path = 'assets/images/hero-portrait.jpg',
    hero_background_image_alt = 'Portrait of Yahya Bouhafs',
    scroll_indicator_text = 'Scroll to explore',
    updated_at = NOW()
WHERE id = 1;

DELETE FROM hero_stats WHERE hero_id = 1;
INSERT INTO hero_stats (hero_id, label, value, sort_order)
VALUES
    (1, 'Products launched with research insights', '24 Projects', 1),
    (1, 'Usability tests facilitated', '120+ Sessions', 2),
    (1, 'Countries collaborated with', '9 Countries', 3)
ON DUPLICATE KEY UPDATE label = VALUES(label), value = VALUES(value), sort_order = VALUES(sort_order);

INSERT INTO pages (page_key, title, meta_description, content, sections, is_active)
VALUES
    ('home', 'Home - Yahya Bouhafs', 'UI/UX designer crafting measurable, human-centered digital products.', NULL,
        JSON_OBJECT(
            'hero', JSON_OBJECT(
                'title', 'Designing outcomes, not just screens',
                'subtitle', 'Partnering with product teams to ship accessible, data-informed experiences.'
            ),
            'featured_work', JSON_OBJECT(
                'section_title', 'Featured Work',
                'section_subtitle', 'Case studies that blend UX research, interface craft, and measurable product impact.'
            ),
            'services_preview', JSON_OBJECT(
                'section_title', 'Core Services',
                'section_subtitle', 'Engagement models built to meet teams where they are.'
            ),
            'skills_tools', JSON_OBJECT(
                'section_title', 'Skills & Tools',
                'section_subtitle', 'Capabilities that keep research, design, and delivery in sync.'
            ),
            'testimonials', JSON_OBJECT(
                'section_title', 'What Clients Say',
                'section_subtitle', 'Leaders who trust the collaborative approach to product design.'
            ),
            'page_cta', JSON_OBJECT(
                'title', 'Ready to Start Your Project?',
                'subtitle', 'Let''s partner to build inclusive, outcome-driven experiences.'
            )
        ),
        1
    ),
    ('about', 'About - Yahya Bouhafs', 'Discover how UI/UX designer Yahya Bouhafs turns complex briefs into simple, human-centered products.', 'Hi, I''m Yahya Bouhafs, a UI/UX designer focused on building honest, user-centered digital products that move teams forward. I blend research, product strategy, and interaction design to create experiences that feel effortless and deliver measurable business results.',
        JSON_OBJECT(
            'about_title', 'Designing clarity into complex products',
            'about_subtitle', 'I help SaaS, fintech, and healthcare teams launch experiences that feel effortless.',
            'about_image_path', 'assets/images/about-photo.jpg',
            'about_image_alt', 'Portrait of Yahya Bouhafs',
            'about_highlights', JSON_ARRAY(
                JSON_OBJECT('text', 'UI/UX designer with 6+ years crafting data-informed product experiences'),
                JSON_OBJECT('text', 'Led cross-functional discovery, prototyping, and delivery for 20+ teams'),
                JSON_OBJECT('text', 'Advocates for inclusive design and continuous product experimentation')
            ),
            'timeline', JSON_OBJECT(
                'title', 'Experience & Education',
                'subtitle', 'A journey through product strategy, interaction design, and collaborative leadership.'
            )
        ),
        1
    ),
    ('services', 'Services - Yahya Bouhafs', 'UI/UX strategy, design systems, and product design services.', '',
        JSON_OBJECT(
            'hero', JSON_OBJECT(
                'title', 'Strategic product design partnerships',
                'subtitle', 'Flexible engagements that combine research, design, and delivery.'
            ),
            'process', JSON_OBJECT(
                'title', 'My Design Process',
                'subtitle', 'A collaborative, transparent approach that keeps teams aligned.'
            ),
            'packages', JSON_OBJECT(
                'title', 'Service Packages',
                'subtitle', 'Options tailored to meet your team''s needs and budget.'
            ),
            'faq', JSON_OBJECT(
                'title', 'Frequently Asked Questions',
                'subtitle', 'Answers to common questions about collaboration and outcomes.'
            )
        ),
        1
    ),
    ('portfolio', 'Portfolio - Yahya Bouhafs', 'Case studies and product design work from Yahya Bouhafs.', '',
        JSON_OBJECT(
            'header', JSON_OBJECT(
                'title', 'Selected Work',
                'subtitle', 'Cross-functional product collaborations spanning strategy to execution.'
            ),
            'page_cta', JSON_OBJECT(
                'title', 'Have a project in mind?',
                'subtitle', 'Let''s build inclusive, data-informed experiences together.'
            )
        ),
        1
    ),
    ('contact', 'Contact - Yahya Bouhafs', 'Start a conversation about UI/UX design partnerships with Yahya Bouhafs.', '',
        JSON_OBJECT(
            'header', JSON_OBJECT(
                'title', 'Let''s collaborate',
                'subtitle', 'Share a few details about your product goals and timeline, and I''ll get back within 24 hours.'
            ),
            'page_cta', JSON_OBJECT(
                'title', 'Prefer email?',
                'subtitle', 'Reach out directly at hello@yahyabouhafs.com'
            )
        ),
        1
    )
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    meta_description = VALUES(meta_description),
    content = VALUES(content),
    sections = VALUES(sections),
    is_active = VALUES(is_active),
    updated_at = NOW();

DELETE FROM project_images;
DELETE FROM projects;
ALTER TABLE project_images AUTO_INCREMENT = 1;
ALTER TABLE projects AUTO_INCREMENT = 1;

INSERT INTO projects (title, slug, short_description, description, category, technologies, main_image_path, main_image_alt, client_name, client_visibility, project_url, featured, status, featured_sort_order, sort_order)
VALUES
    (
        'Fintech Wallet Experience',
        'fintech-wallet-experience',
        'Redesign of a cross-border payment app with frictionless onboarding and trust-building microcopy.',
        'Led end-to-end UX for a fintech wallet serving 1M+ users, including research synthesis, design system foundations, and launch support for the new onboarding flow.',
        'Fintech',
        JSON_ARRAY('Figma', 'Design Tokens', 'React Native'),
        'assets/images/projects/banking-app.svg',
        'Fintech wallet product screens illustration',
        'PayLink Africa',
        'yes',
        'https://yahyabouhafs.com/work/fintech-wallet-experience',
        1,
        'published',
        1,
        1
    ),
    (
        'SaaS Insight Dashboard',
        'saas-insight-dashboard',
        'Turning churn data into actionable stories for customer success teams.',
        'Shaped the product vision, UX flows, and high-fidelity UI for a SaaS analytics platform, driving a 22% lift in feature adoption post-launch.',
        'SaaS',
        JSON_ARRAY('Figma', 'React', 'D3.js'),
        'assets/images/projects/saas-platform.svg',
        'SaaS analytics dashboard illustration',
        'Northwind Metrics',
        'yes',
        'https://yahyabouhafs.com/work/saas-insight-dashboard',
        1,
        'published',
        2,
        2
    ),
    (
        'Healthcare Appointment Platform',
        'healthcare-appointment-platform',
        'Improved scheduling clarity for patients and care providers with accessibility-first UI.',
        'Partnered with clinicians to co-create an accessible appointment platform, reducing booking drop-off by 37% within the first quarter.',
        'Healthcare',
        JSON_ARRAY('Figma', 'Design Ops', 'Vue.js'),
        'assets/images/projects/healthcare-dashboard.svg',
        'Accessible healthcare appointment interface illustration',
        'Clinique Horizon',
        'yes',
        'https://yahyabouhafs.com/work/healthcare-appointment-platform',
        0,
        'published',
        0,
        3
    );

INSERT INTO project_images (project_id, image_path, caption, sort_order)
VALUES
    (1, 'assets/images/projects/banking-app.svg', 'Highlights from the fintech wallet system', 1),
    (2, 'assets/images/projects/saas-platform.svg', 'Key dashboard modules and states', 1),
    (3, 'assets/images/projects/healthcare-dashboard.svg', 'Patient-first scheduling workflows', 1);

DELETE FROM testimonials;
ALTER TABLE testimonials AUTO_INCREMENT = 1;

INSERT INTO testimonials (client_name, client_position, client_company, image_path, rating, testimonial_text, is_featured, sort_order, status)
VALUES
    ('Lina Haddad', 'Product Director', 'PayLink Africa', 'assets/images/profile.svg', 5, 'Yahya uncovered the friction in our onboarding journey and rebuilt it with empathy. Activation climbed double digits within weeks of launch.', 1, 1, 'published'),
    ('Carlos Mendes', 'CTO', 'Northwind Metrics', 'assets/images/profile.svg', 5, 'From research to polished UI, Yahya kept the team aligned and shipping. Our dashboard finally tells stories our customers can act on.', 1, 2, 'published'),
    ('Dr. Amina Larbi', 'Chief Medical Officer', 'Clinique Horizon', 'assets/images/profile.svg', 5, 'Yahya works with genuine care for accessibility. Patients rave about the clarity of the new appointment flow and our staff saves hours each week.', 1, 3, 'published');
