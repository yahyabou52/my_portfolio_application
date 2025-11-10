-- Add sample contact messages
INSERT INTO messages (name, email, subject, message, is_read, created_at)
VALUES
	('Sarah Johnson', 'sarah.johnson@email.com', 'Website Redesign Project', 'Hi! I represent a tech startup and we are looking for a talented UI/UX designer to redesign our company website. Could we schedule a call to discuss details and timeline?', 0, DATE_SUB(NOW(), INTERVAL 2 HOUR)),
	('Michael Chen', 'michael.chen@techcorp.com', 'Mobile App Design Consultation', 'We are developing a fintech mobile app and need help with the user interface design. What is your availability for a consultation?', 1, DATE_SUB(NOW(), INTERVAL 1 DAY)),
	('Emma Rodriguez', 'emma@startupventure.io', 'E-commerce Platform UI', 'Launching an e-commerce platform and need a conversion-optimized user experience. Can you share your approach and rates?', 0, DATE_SUB(NOW(), INTERVAL 3 HOUR)),
	('David Thompson', 'david.thompson@healthcare.org', 'Healthcare Dashboard Project', 'Our organization needs a new patient management dashboard. We saw your similar work and would love to discuss.', 1, DATE_SUB(NOW(), INTERVAL 2 DAY)),
	('Lisa Park', 'lisa.park@gmail.com', 'Portfolio Website Design', 'I am a photographer looking to create an elegant portfolio website. Could we discuss scope and pricing?', 0, DATE_SUB(NOW(), INTERVAL 5 HOUR)),
	('James Wilson', 'j.wilson@retailcompany.com', 'Retail Mobile App', 'We run a retail chain and want a customer-focused mobile app with loyalty features. Interested in collaborating?', 1, DATE_SUB(NOW(), INTERVAL 1 WEEK)),
	('Amanda Foster', 'amanda@nonprofitorg.org', 'Non-profit Website Redesign', 'We need a heartfelt, accessible site redesign for our non-profit. Can you help us shape the user journey?', 0, DATE_SUB(NOW(), INTERVAL 1 DAY)),
	('Robert Kim', 'robert.kim@gamesstudio.com', 'Gaming App Interface', 'Our studio is building a puzzle game and needs intuitive, playful UI. Do you have availability?', 1, DATE_SUB(NOW(), INTERVAL 3 DAY));