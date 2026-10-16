# Website-first builder — final implementation and verification

The public design is still the existing PHP templates, Bootstrap classes, CSS variables, Inter/Playfair fonts and assets. There is no replacement public stylesheet or preview renderer. Editing attributes and wfe CSS/JS are emitted only for the authenticated owner in edit mode.

## Use locally

Open http://localhost:8765/signup to create a new website. Optional setup supports profession, introduction, public contact email, a validated photo and preferred sections. Collections start empty. The site is published immediately.

Sign in, open your public /s/{slug} URL, and click **Edit website**:
- Click text, an image, or a CTA to edit.
- Page changes autosave to draft. Publish makes them live. Discard restores the last published page. Undo/Redo cover field edits.
- Sections & theme manages visibility, order, removal, addition and existing layout variants; Device preview renders the same draft at desktop/tablet/mobile widths.
- Manage content opens the shared item drawer. New items are Draft; Publish item makes them live. Saving a Published item is immediate and explicitly labelled Live.
- Website settings edits the shared public profile. Account settings edits private login email/password and requires the current password.
- Website URL remains /s/{slug}. A deliberate address change is immediate and warns that the old URL stops working.

One section instance per type is allowed because existing templates contain stable form and accordion IDs. Projects can use either existing card layout. Other sections retain their existing layout; new invented variants would violate the design constraint. No free positioning was introduced.

## Data authority and rollout

| Site state | Authoritative page/profile content | Collections |
| --- | --- | --- |
| Legacy, including unactivated migration candidates | Existing legacy tables | Existing collection tables |
| Website-first public | Published builder_versions snapshot | Published rows in existing tenant collection tables |
| Website-first editor | builder_state.draft_json | The SAME collection rows and drawer |
| Account | admin_users; registry owner_email identifies the owner | Never published as public contact |

No profile dual writes occur during editing. New sites capture their presentation at provisioning. Existing hero/cover imagery and separate legacy text are retained rather than silently overwritten by a unified profile value when those values differ.

The migration runner still exports each tenant first, tracks schema version, supports dry run, logs per tenant, and resumes safely. Standard Home/About/Services/Portfolio/Contact templates are mapped, including original navigation, footer and additional sections. Existing project-detail URLs remain supported. Sparse or custom unsupported content blocks activation. Previous Phase 1 candidates need --refresh to capture the complete mapping, then per-site visual review and --activate --verified-hash=HASH. No real customer/demo was activated by this work.

Only isolated test copies were activated. Tests verify source preservation, restored exports, idempotency, stale revisions, current-owner authorization, atomic publish failure, discard, blocked legacy writes and exact review hashes.

## Run tests

Requires the running local PHP server at localhost:8765, MariaDB, Node dependencies (npm ci), and installed Chrome.

PowerShell:
```powershell
$env:DB_HOST='127.0.0.1'
$env:DB_PORT='3308'
$env:BASE_URL='http://localhost:8765'
npm test
```

scripts/test-all.ps1 stops at the first failure. It creates isolated fixtures, runs the PHP data tests, legacy skill synchronization, HTTP tenant isolation, legacy SaaS/UX suite, editor UI, section UI, collection/message UI, setup/account UI, and the visual suite.

Visual suite: 6 sections × 3 widths (1440, 768, 390), comparing BOTH the committed golden screenshots and current legacy page against the new renderer. Baselines are never automatically updated. Final report: docs/design-parity-report.json.

Editor checks include exact section bounding-box equality at all three widths, anonymous HTML without editor assets/controls/drafts, transient autosave retry, plain-text paste, stale conflicts, publish/discard UI, undo/redo, image upload, and link validation. Other browser tests cover all collection lifecycle states, item conflicts, messages, section ordering, gallery variant, theme isolation, exact device preview width, setup photo, private account settings, login and URL changes.

Screenshots are under test-results/editor/: public-viewport-1440.png, edit-viewport-1440.png, public-viewport-390.png, edit-viewport-390.png. Full-page captures are also retained. Section comparison images and pixel differences are under test-results/design-parity/.

## Known limits kept under the design constraint

- The existing demo footer logo renders at 960px and creates a 972px document width on a 390px viewport in BOTH legacy and new-renderer views. The existing mobile hero statistics also overlap. These inherited design defects were not restyled. Full mobile visual readiness therefore remains unresolved for that legacy content.
- The existing navigation script reports an invalid-URL error for the theme-toggle button. A fix changed the Contact character-counter color, so it was reverted to keep the exact baseline.
- Validation was in real headless Chrome. Safari/Firefox and physical touch devices were not tested.
- No production deployment or customer activation was performed. Custom domains, advanced styling, AI writing, billing implementation and testimonials inbox remain outside this implementation.
