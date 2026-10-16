# Public design inventory and parity contract

The reference is the legacy public site at baseline commit bd7cc9a. No visual restyling is authorized.

| Surface | Template | Styles |
| --- | --- | --- |
| Shared page chrome | app/views/layouts/main.php, navbar.php, footer.php | style.css: navigation, buttons, footer, utilities, dark mode |
| Hero | app/views/home/index.php: .hero-section | style.css lines 307–500 |
| Featured projects | app/views/home/index.php: .featured-work-section | style.css cards, technology tags |
| Portfolio grid/detail | app/views/portfolio/index.php, show.php | style.css lines 1223–1464 |
| About | app/views/home/about.php: .about-hero | style.css lines 900–916 |
| Skills | home/index.php: .skills-section; home/about.php: .skills-detail-section | style.css lines 651–694, 993–1026 |
| Experience | home/about.php: .experience-section | style.css lines 917–992 |
| Contact | app/views/contact/index.php | style.css lines 1465–1593 |
| Services and supporting sections | app/views/home/services.php | style.css lines 1074–1222 |
| Call-to-action sections | Existing page templates | style.css lines 1594–1616 |

The public layout loads Bootstrap 5.3.2, Bootstrap Icons 1.11.1, AOS 2.3.1, and public/assets/css/style.css. Inter is the body font; Playfair Display is the display font, both loaded from Google Fonts.

Root tokens: primary #7B3FE4, primary-dark #5F2CC4, primary-light #9B6BE8; background #ffffff and #f8f9fa; text #2c3e50 and muted #6c757d; border #e9ecef. Spacing uses --section-padding: 5rem 0 and --container-padding: 1.5rem. Radius tokens are .375rem, .5rem and 1rem. Dark mode overrides variables under [data-bs-theme="dark"]. Main layout applies saved theme colors and custom CSS.

Public JavaScript: site-session.js, Bootstrap bundle, AOS, app.js, plus existing page-local scripts. Existing assets remain under public/assets/images and customer upload directories.

admin.css, admin-modern.css and studio.css belong to the old dashboard. The Phase 1 builder.css and builder section markup introduced a different design and must not remain the public renderer.

Parity tests must compare identical content and assets in the real legacy templates and the registry renderer at 1440, 768 and 390 CSS pixels. Screenshot differences must fail tests; no automatic baseline updates. Editor UI must remain a separate owner-only overlay layer with no public editor assets and no changes to section bounding boxes.

## Step 0 result

Real Chrome screenshots passed for all 18 section/viewport combinations (six sections × 1440/768/390px), with zero differing pixels at pixelmatch threshold 0.1. No visual differences were accepted. Reference images are committed under tests/visual-baselines; the suite compares both the renderer and future legacy output against these references. Machine-readable results: docs/design-parity-report.json.

Run the documented PHP environment with scripts/prepare-design-parity.php, then node scripts/test-design-parity.mjs. The fixture is an isolated copy; the original demo is not modified.
