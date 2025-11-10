# Project Summary Documentation

## Project Purpose
- **Overview:** Responsive portfolio website tailored for Yahya Bouhafs, a product-focused UI/UX designer.
- **Goal:** Showcase case studies, services, and testimonials while providing an admin workspace to manage content without touching code.
- **Audience:** Potential clients, hiring managers, and collaborators evaluating Yahya’s design capabilities.

## Tech Stack
- **Frontend:** HTML5, Bootstrap (light/dark theme support), bespoke UI components, AOS-powered motion, and modular SCSS-derived CSS.
- **Backend:** PHP 8+ using a lightweight MVC architecture (controllers, models, views, reusable layouts, helper functions).
- **Database:** MySQL / MariaDB with UTF8MB4 encoding; content seeded via SQL migrations and update scripts.
- **Admin Dashboard Design System:** Custom Bootstrap-based layout with reusable cards, collapsible panels, form controls, drag-and-drop ordering, live previews, and toast feedback components.

## Features Completed So Far
- **Hero Section Editor:** Manage intro copy, scroll indicator text, statistics, and background imagery (including uploads). Supports previewing updates and reordering stats through drag-and-drop.
- **Featured Work Selector:** Choose published projects, control featured ordering, and preview changes before publishing.
- **Services Preview System:** Toggle visibility, adjust ordering, and persist homepage service selections with status badges.
- **Skills & Tools Preview Logic:** Group skills by category, display proficiency percentages, and surface categories conditionally on the homepage preview.
- **Testimonials Preview Editor:** Edit testimonial entries, manage slider ordering, preview transitions, and revert via cancel actions.

## Database Structure
| Table | Columns (Key columns bolded) | Relationships |
|-------|------------------------------|----------------|
| `settings_site` | **id**, site_title, site_tagline, site_description, nav_cta_text, nav_cta_url, footer_text, logo_path, favicon_path, theme_default, contact_email, contact_phone, contact_address, social_links (JSON), footer_links (JSON), created_at, updated_at | Root configuration. No FKs. |
| `hero_section` | **id**, hero_intro_prefix, hero_intro_name_first, hero_intro_name_rest, hero_intro_suffix, hero_title, hero_subtitle, hero_description, hero_primary_cta_text, hero_primary_cta_url, hero_secondary_cta_text, hero_secondary_cta_url, hero_background_image_path, hero_background_image_alt, scroll_indicator_text, created_at, updated_at | One-to-many with `hero_stats`. |
| `hero_stats` | **id**, hero_id (FK), label, value, sort_order, is_active, created_at, updated_at | FK `hero_id` → `hero_section.id`; ordered badges displayed in hero editor. |
| `projects` | **id**, title, slug (unique), short_description, description, category, technologies (JSON), main_image_path, main_image_alt, client_name, client_visibility, project_url, featured (bool), status, featured_sort_order, sort_order, created_at, updated_at | One-to-many with `project_images`; referenced by featured work selector. |
| `project_images` | **id**, project_id (FK), image_path, caption, sort_order, created_at, updated_at | FK `project_id` → `projects.id`; acts as gallery/pivot for ordering. |
| `services` | **id**, title, icon, description, price_label, price_amount, status, homepage_featured, homepage_is_visible, homepage_sort_order, sort_order, created_at, updated_at | One-to-many with `service_features`; referenced by services preview. |
| `service_process_steps` | **id**, title, description, icon, sort_order, status, created_at, updated_at | Standalone ordered list for process timeline. |
| `service_features` | **id**, service_id (FK), feature_text, sort_order, created_at, updated_at | FK `service_id` → `services.id`; bullet points per service. |
| `pricing_packages` | **id**, name, price_label, price_amount, price_period, description, badge_text, cta_text, cta_url, is_featured, status, sort_order, created_at, updated_at | One-to-many with `pricing_package_features`. |
| `pricing_package_features` | **id**, package_id (FK), feature_text, sort_order, created_at, updated_at | FK `package_id` → `pricing_packages.id`; pivot-like ordering for pricing bullets. |
| `skill_categories` | **id**, title, icon_class, sort_order, is_visible, created_at, updated_at | Parent categories for grouped skills. |
| `skills` | **id**, category_id (FK), name, proficiency_level, sort_order, is_visible, created_at, updated_at | FK `category_id` → `skill_categories.id`; provides percentage metrics. |
| `testimonials` | **id**, client_name, client_position, client_company, image_path, rating, testimonial_text, is_featured, sort_order, status, created_at, updated_at | Ordered testimonials powering homepage slider/editor. |
| `timeline_items` | **id**, title, organization, date_range, description, tags (JSON), is_education, sort_order, status, created_at, updated_at | Supplies About page journey timeline. |
| `faq_items` | **id**, page (enum), question, answer, sort_order, is_active, created_at, updated_at | Content for FAQ blocks by page key. |
| `pages` | **id**, page_key (unique), title, meta_description, content (HTML), sections (JSON), is_active, created_at, updated_at | Stores structured page metadata and modular section data for ContentRepository. |
| `navigation_menu` | **id**, title, url, icon, parent_id (FK self), sort_order, is_active, target, created_at, updated_at | Supports multi-level navigation tree. |
| `media_files` | **id**, filename, original_name, file_path, file_type, file_size, mime_type, alt_text, title, uploaded_by (FK), created_at, updated_at | FK `uploaded_by` → `admin_users.id`; central media library. |
| `messages` | **id**, name, email, subject, message, is_read, responded_at, created_at | Contact submissions. |
| `admin_users` | **id**, name, username (unique), email (unique), password (BCrypt hash), role, last_login_at, created_at, updated_at | Auth and permissions for dashboard. |

*Foreign Key Summary:* `hero_stats`, `project_images`, `service_features`, `pricing_package_features`, `skills`, `navigation_menu` (self-referential), and `media_files` enforce one-to-many relationships. Ordering columns (`sort_order`, `featured_sort_order`) function as pivot-style controls for front-end ordering.

## Admin UI Interaction System
- **Live Preview Syncing:** Admin panels use AJAX fetches to update a mirrored preview pane; changes reflect immediately using batched DOM updates to prevent flicker.
- **Save / Cancel Behavior:** Each editor tracks the initial state; cancel actions revert the preview using cached JSON payloads while save commits via POST and then refreshes preview data.
- **Feedback & Toasts:** Success/error notifications leverage a WeakMap of timer handles to avoid lingering toasts; repeated saves reset the display timer to ensure consistent visibility duration.
- **Drag-and-Drop Ordering:** Sortable lists (hero stats, projects, testimonials, services) use HTML5 drag events with data attributes for weights; order persistence posts the new sequence to corresponding controller endpoints.

## File / Folder Structure
- **MVC Layout:**
  - `app/controllers/` – Handles routing logic (`HomeController`, `PortfolioController`, `AdminController`, etc.).
  - `app/models/` – ORM-like classes (`Project.php`, `Service.php`, `HeroSection.php`, etc.) responsible for DB reads/writes.
  - `app/views/` – Blade-like PHP templates separated into `layouts`, `home`, `portfolio`, `contact`, `admin`, etc.
  - `app/core/` – Framework kernel (`Router.php`, `BaseController.php`, `ContentRepository.php`) plus shared helpers.
- **Admin UI Views:** Located under `app/views/admin/` (e.g., `dashboard.php`, `login.php`, `messages.php`, component partials).
- **Shared Components:** Layouts (`layouts/main.php`, `layouts/navbar.php`, `layouts/footer.php`, `layouts/admin.php`) encapsulate header/footer/navigation and include asset loaders.
- **Assets & Uploads:** Static assets live in `public/assets/` (CSS, JS, images). Media uploads are stored under `public/assets/images/` with DB references managed via `media_files`.

## Next Steps Roadmap
1. **Finalize Skills & Tools Preview Corrections** – align category toggles with new grouped data, ensure progress bars sync with edited percentages, and validate fallback messaging.
2. **Complete Testimonials Preview Polishing** – finalize slider animation controls, add inline validation, and extend image upload handling in the admin flow.
3. **About Page Content Editor** – build admin interface for About page sections (hero, highlights, timeline), connect to `pages` JSON structure, and surface real-time preview updates.

---
This document captures the current state of the Yahya Bouhafs portfolio project and should provide all necessary context for continuing development on any machine.
