# Portfolio Studio

A PHP portfolio SaaS MVP. Customers create an account and website, then manage their public pages from a dedicated dashboard.

## Local application

- Platform: http://localhost:8765
- Create a website: http://localhost:8765/signup
- Existing demo: http://localhost:8765/s/demo
- Demo dashboard: http://localhost:8765/s/demo/admin/login
- Demo login: **admin / password**. New customers sign in using their chosen email and password.

The original portfolio data is preserved in the demo. New accounts start with example projects/services, their own identity, and no template testimonials, contact messages or timeline entries.

## Requirements and setup

PHP 8.1+ with PDO MySQL, mbstring and fileinfo; MySQL/MariaDB. No npm build is required. Node.js is used for integration checks.

| Environment variable | Default | Purpose |
| --- | --- | --- |
| DB_HOST | localhost | Database server |
| DB_PORT | 3306 | Database port |
| DB_USER | root | Database/provisioning account |
| DB_PASS | empty | Database password |
| PLATFORM_DB | portfolio_platform | Website registry |
| BASE_URL | legacy installation URL | Set to your public origin/base path |

For this workspace: DB_HOST=127.0.0.1, DB_PORT=3308, BASE_URL=http://localhost:8765. Isolated MariaDB data is in .local/db; its runtime binary is reused from the installed MariaDB package without modifying another project's database.

1. Run: php scripts/setup-platform.php
2. Run: php -S 127.0.0.1:8765 -t public public/index.php
3. Visit /signup.

Setup creates the registry, initializes the demo only if its database is empty, and applies migrations. It does not reset an existing demo. On this machine, run powershell -File .local/start.ps1 to restart the existing database and PHP server with the right extensions and environment. Logs/process IDs are in .local/.

**Do not import database/schema.sql into an existing database.** It is a fresh-site seed that drops tables. Customer provisioning strips its database selection and executes it only inside a newly allocated database. Complete/enhanced SQL files are historical schemas, not sequential migrations.

## Customer editing

Website editor is organized by Brand & contact, Home, About, Services, Portfolio, Project details, Contact, Navigation and Footer. It controls text, section visibility/order, identity, contact details, social/footer links and brand images. Links lead to specialist editors for hero/cover images, biography, projects, services, process, pricing, FAQs, testimonials, skills and timeline.

- Media uploads use customer-specific directories. Logo/favicon selection uses this library; hero/about/project editors also support images.
- Page settings control browser titles, descriptions and publication status. Visible copy is edited in Website editor.
- Theme changes apply to the public site. Visitor preferences are stored separately for each website.
- Saving publishes immediately. Draft revisions and a separate publishing workflow are not implemented.

## Code organization

- app/core/TenantContext.php resolves /s/{slug} before content models connect.
- app/core/SiteProvisioner.php allocates a database, seeds it, applies migrations and creates the owner.
- app/controllers/PlatformController.php handles signup and the website-address sign-in entry.
- app/controllers/WebsiteController.php handles unified editing with validation and transactions.
- app/core/WebsiteContent.php and config/website-fields.json define the editable field contract shared by templates and editor.
- app/controllers/admin/ contains 19 focused action modules. AdminManagerController composes them instead of containing 5,000 lines of actions.
- app/core/ContentRepository.php assembles content for public templates.
- app/models/ provides PDO persistence; legacy settings writes map shared identity fields into canonical settings_site data.
- app/views/ contains public templates, dashboard editors and platform onboarding.

See docs/SAAS_ARCHITECTURE.md for isolation and data flow.

## Verification

Run node scripts/test-saas.mjs against the local app. TEST_BASE_URL overrides the URL. Tests create two uniquely named QA customer websites, retained for inspection. Checks cover signup, login, editing, data/session isolation, CSRF, uploads, image URLs, section visibility/order, theme/footer publishing, 22 dashboard screens and inline JavaScript syntax.

PHP syntax checks and HTTP integration tests passed locally. Visual browser QA was unavailable in this session.

## Scope

This is a working local SaaS MVP, not a production launch. Billing, custom domains, email verification, password recovery, signup/login abuse protection, storage quotas and a platform operator console are not implemented. Contact messages are stored; email delivery remains disabled. The portfolio template layout is fixed; customers edit content and section order/visibility rather than designing arbitrary layouts.

Before public deployment, configure HTTPS, production error handling, backups and a dedicated database provisioning account. Database names and sessions are customer-isolated, but the runtime uses one server-side database credential. Per-database credentials and asynchronous provisioning are follow-up hardening work. Point the web server document root to public/. Apache rules are included; equivalent Nginx routing is required.
