-- Site settings
UPDATE settings_site
	SET
	site_title = 'Yahya Bouhafs',
	logo_path = 'assets/images/logo.svg',
	favicon_path = 'assets/images/favicon.png',
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
	)
WHERE id = 1;

-- Hero statistics
INSERT INTO hero_stats (hero_id, label, value, sort_order)
VALUES
	(1, 'Products launched with research insights', '24 Projects', 1),
	(1, 'Usability tests facilitated', '120+ Sessions', 2),
	(1, 'Countries collaborated with', '9 Countries', 3)
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- Default scroll indicator text
UPDATE hero_section
SET scroll_indicator_text = 'Scroll to explore'
WHERE id = 1;

-- Page content sections (home, about, services, portfolio, contact)
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
	updated_at = CURRENT_TIMESTAMP;

-- Primary navigation items
INSERT INTO navigation_menu (id, title, url, icon, parent_id, sort_order, is_active, target)
VALUES
	(1, 'Home', '/', NULL, NULL, 1, 1, '_self'),
	(2, 'About', '/about', NULL, NULL, 2, 1, '_self'),
	(3, 'Services', '/services', NULL, NULL, 3, 1, '_self'),
	(4, 'Portfolio', '/portfolio', NULL, NULL, 4, 1, '_self'),
	(5, 'Contact', '/contact', NULL, NULL, 5, 1, '_self')
ON DUPLICATE KEY UPDATE
	title = VALUES(title),
	url = VALUES(url),
	sort_order = VALUES(sort_order),
	is_active = VALUES(is_active),
	target = VALUES(target),
	updated_at = CURRENT_TIMESTAMP;

-- Portfolio projects
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
	)
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- Project gallery images
INSERT INTO project_images (project_id, image_path, caption, sort_order)
VALUES
	(1, 'assets/images/projects/banking-app.svg', 'Highlights from the fintech wallet system', 1),
	(2, 'assets/images/projects/saas-platform.svg', 'Key dashboard modules and states', 1),
	(3, 'assets/images/projects/healthcare-dashboard.svg', 'Patient-first scheduling workflows', 1)
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- Services
INSERT INTO services (title, icon, description, price_label, price_amount, status, homepage_featured, homepage_is_visible, homepage_sort_order, sort_order)
VALUES
	('Product Strategy & Discovery', 'bi-compass', 'Aligning teams around clear goals through research, workshops, and measurable product roadmaps.', 'Starting from', 2500.00, 'published', 1, 1, 1, 1),
	('End-to-End Product Design', 'bi-palette', 'Designing intuitive, accessible experiences from whiteboard flows to developer-ready UI kits.', 'Typical engagement', 6500.00, 'published', 1, 1, 2, 2),
	('Design Systems & Ops', 'bi-diagram-3', 'Building scalable design systems, guidelines, and governance so teams ship faster with consistency.', 'From', 4200.00, 'published', 1, 1, 3, 3)
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- Service process steps
INSERT INTO service_process_steps (id, title, description, icon, sort_order, status)
VALUES
	(1, 'Discovery', 'Kick-off workshops, stakeholder interviews, and goal alignment to frame the problem clearly.', 'bi-lightbulb', 1, 'published'),
	(2, 'Research', 'User interviews, data review, and competitive analysis that uncover actionable insights.', 'bi-search', 2, 'published'),
	(3, 'Design & Prototype', 'Rapid iteration of flows, wireframes, and interactive prototypes tested with users.', 'bi-pencil-square', 3, 'published'),
	(4, 'Deliver & Support', 'High-fidelity UI, documentation, and design-to-dev support through launch.', 'bi-check-circle', 4, 'published')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- Service features
INSERT INTO service_features (service_id, feature_text, sort_order)
VALUES
	(1, 'Stakeholder workshops & alignment sessions', 1),
	(1, 'Customer journey mapping + opportunity framing', 2),
	(1, 'North-star metrics and experiment planning', 3),
	(2, 'Information architecture & user flows', 1),
	(2, 'Interactive prototypes tested with real users', 2),
	(2, 'Developer-ready design systems & documentation', 3),
	(3, 'Component libraries with usage guidelines', 1),
	(3, 'Design tokens + theming support', 2),
	(3, 'Design-to-dev handoff playbooks', 3)
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- Pricing packages
INSERT INTO pricing_packages (id, name, price_label, price_amount, price_period, description, badge_text, cta_text, cta_url, is_featured, status, sort_order)
VALUES
	(1, 'Starter', 'Investment', 1500.00, 'per project', 'Perfect for early-stage teams validating their first product.', NULL, 'Choose Starter', '/contact?package=starter', 0, 'published', 1),
	(2, 'Professional', 'Investment', 3500.00, 'per project', 'Ideal for scaling teams that need rigorous research and design.', 'Most Popular', 'Choose Professional', '/contact?package=professional', 1, 'published', 2),
	(3, 'Enterprise', 'Custom from', 7500.00, 'per engagement', 'Comprehensive partnership for complex products and design systems.', NULL, 'Discuss Enterprise', '/contact?package=enterprise', 0, 'published', 3)
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

INSERT INTO pricing_package_features (id, package_id, feature_text, sort_order)
VALUES
	(1, 1, 'Up to 5 key screens designed', 1),
	(2, 1, 'Lightweight style exploration', 2),
	(3, 1, 'Two revision rounds', 3),
	(4, 2, 'Full UX audit and research synthesis', 1),
	(5, 2, 'Interactive prototype & usability testing', 2),
	(6, 2, 'Component library & annotations', 3),
	(7, 2, 'Four revision rounds', 4),
	(8, 3, 'Multi-platform product coverage', 1),
	(9, 3, 'Dedicated design ops support', 2),
	(10, 3, 'Advanced analytics & experimentation plan', 3),
	(11, 3, 'Ongoing design advisory retainer', 4)
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- Skill categories and items
INSERT INTO skill_categories (id, title, icon_class, sort_order, is_visible)
VALUES
	(1, 'Product & UX', 'bi-lightning-charge', 1, 1),
	(2, 'Interface & Visual', 'bi-brush', 2, 1),
	(3, 'Tools & Platforms', 'bi-cpu', 3, 1)
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

INSERT INTO skills (category_id, name, proficiency_level, sort_order, is_visible)
VALUES
	(1, 'User Research & Testing', 90, 1, 1),
	(1, 'Product Strategy', 85, 2, 1),
	(1, 'Information Architecture', 88, 3, 1),
	(2, 'UI Design Systems', 92, 1, 1),
	(2, 'Motion & Micro-interactions', 80, 2, 1),
	(2, 'Design Accessibility (WCAG)', 87, 3, 1),
	(3, 'Figma', 95, 1, 1),
	(3, 'Framer', 75, 2, 1),
	(3, 'React + Storybook', 70, 3, 1)
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- Testimonials
INSERT INTO testimonials (client_name, client_position, client_company, image_path, rating, testimonial_text, is_featured, sort_order, status)
VALUES
	('Lina Haddad', 'Product Director', 'PayLink Africa', 'assets/images/profile.svg', 5, 'Yahya uncovered the friction in our onboarding journey and rebuilt it with empathy. Activation climbed double digits within weeks of launch.', 1, 1, 'published'),
	('Carlos Mendes', 'CTO', 'Northwind Metrics', 'assets/images/profile.svg', 5, 'From research to polished UI, Yahya kept the team aligned and shipping. Our dashboard finally tells stories our customers can act on.', 1, 2, 'published'),
	('Dr. Amina Larbi', 'Chief Medical Officer', 'Clinique Horizon', 'assets/images/profile.svg', 5, 'Yahya works with genuine care for accessibility. Patients rave about the clarity of the new appointment flow and our staff saves hours each week.', 1, 3, 'published')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- Timeline items
INSERT INTO timeline_items (title, organization, date_range, description, tags, is_education, sort_order, status)
VALUES
	('Lead Product Designer', 'Brightwave Labs', '2022 - Present', 'Owning discovery through delivery for subscription products serving 200k+ monthly active users.', JSON_ARRAY('SaaS', 'Design Systems', 'Experimentation'), 0, 1, 'published'),
	('Senior UX Designer', 'MedTech Systems', '2019 - 2022', 'Led cross-functional teams delivering HIPAA-compliant healthcare tools with measurable patient impact.', JSON_ARRAY('Healthcare', 'Enterprise'), 0, 2, 'published'),
	('BFA Interaction Design', 'California College of the Arts', '2013 - 2017', 'Explored the relationship between storytelling, ethics, and emerging technology.', JSON_ARRAY('Human-Centered Design', 'Ethics'), 1, 3, 'published')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- Frequently asked questions
INSERT INTO faq_items (page, question, answer, sort_order, is_active)
VALUES
	('services', 'What does a typical engagement look like?', 'Most projects start with a two-week discovery sprint to clarify goals, followed by design and iteration cycles tailored to your team.', 1, 1),
	('services', 'Do you collaborate with in-house developers?', 'Absolutely. I deliver detailed documentation, components, and stay close to engineering until launch.', 2, 1),
	('contact', 'How soon can we get started?', 'My calendar usually opens up within 2-3 weeks. Reach out with your timeline and we will make a plan.', 1, 1)
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;