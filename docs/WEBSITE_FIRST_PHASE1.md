> Historical Phase 1 report. For the completed implementation, current rollout coverage and browser results, see [WEBSITE_FIRST_COMPLETION.md](WEBSITE_FIRST_COMPLETION.md).

# Website-first builder: Phase 1

Baseline: branch `baseline/dashboard-saas`, commit `2b028c1`.
Implementation branch: `feature/website-first-phase-1`.

## What is available

- Six registered sections: Hero, About, Projects, Skills, Experience, Contact.
- One PHP renderer and the same section templates for public and owner-only draft views.
- New websites start in website_first mode with an initial published document, no sample collections and an empty private contact address (login email is not implicitly public).
- /s/{slug} remains canonical. Reserved platform names are rejected for new signup.
- Existing websites remain legacy. Existing pages and project-detail routes are retained.
- Owner-only /edit renders the draft. It is a Phase 1 preview, not yet an interactive editor.
- Legacy migration candidates can be inspected at /s/{slug}/admin/builder-preview after migration.
- Empty collection sections disappear publicly and show explanatory prompts in the owner view.
- Page-level draft save, publish and discard services are transactional and tested; public write APIs and inline editing controls arrive in Phase 2.

## Authority and publication

| Website mode | Authoritative profile/page data | Collection authority | Allowed writes |
| --- | --- | --- | --- |
| legacy, before migration | Existing settings_site, hero_section, pages, site_settings | Existing tenant collection tables | Existing dashboard |
| legacy, migration candidate | Same legacy tables; builder document is only a candidate | Same tenant collection tables | Existing dashboard; refresh candidate after content changes |
| website_first | Single profile object inside builder_state.draft_json; visitors read its immutable published version | Same collection rows and IDs; sections contain collection references | New builder services only; old content POST endpoints are blocked |

There are no runtime profile dual writes. Imported legacy tables are retained as historical data after cutover. Archived published profile values are version history, not independently editable copies.

New tables in each customer database:
- builder_migrations: applied schema version and backup path.
- builder_state: tenant ID, owner ID, editor_mode, revision, draft JSON, published version pointer, source hash, verification hash and last-save time.
- builder_versions: immutable published document JSON, author and timestamp.

Skills gain a Draft/Published status derived from existing visibility during migration. The remaining collections already have status columns. New website insert defaults are Draft. Existing collection records and IDs are retained. The unified item drawer and live-save semantics are Phase 4 work; website-first legacy collection POSTs are deliberately blocked until the replacement exists.

The profile is shared by every section and includes name, profession, intro, biography, photo, public contact details and social links. Page documents store section type, registered variant, stable ID, order, visibility, settings and a linked collection name. Raw HTML is not accepted as renderable content: text is escaped. Links allow http, https, mailto, tel, safe internal paths and anchors; scripts, protocol-relative links and control characters are rejected.

## Migration commands

Use the same PHP and database environment as the application. For the local Windows environment:

```powershell
$env:DB_HOST='127.0.0.1'
$env:DB_PORT='3308'
$env:BASE_URL='http://localhost:8765'
$phpExe=(Get-Command php).Source
$ext=Join-Path (Split-Path $phpExe) 'ext'
& $phpExe -n -d "extension_dir=$ext" -d extension=pdo_mysql -d extension=mbstring scripts/migrate-builder.php --site=demo --dry-run
```

Replace the final arguments as needed:
- `--site=demo`: export first, then create a legacy migration candidate.
- `--all`: process registered active sites independently; continue after individual failures and return nonzero if any failed.
- `--dry-run`: report current schema and plan without database or filesystem writes.
- `--verify`: validate document shape, published pointer, ownership and source fingerprint.
- `--refresh`: back up and re-import a legacy candidate after legacy edits. Keeps previous imported versions; forbidden for website_first sites.
- `--activate --verified-hash=HASH`: requires a matching reviewed document, resolved owner, unchanged source and no compatibility blockers.

**Phase 1 intentionally blocks activation of imported legacy sites with unmapped content.** Navigation/footer, custom labels and additional legacy section types still need migration coverage. The six-section import is a review candidate; it must not silently replace a complete existing website. New sites do not have those compatibility blockers.

Exports are written outside public/ to .local/builder-backups, with SHA-256 sidecars. Each export contains schema and all table data, including private account records, so keep this directory private and excluded from Git. A failed export aborts before schema changes. Migration DDL is resumable; state/version/migration-marker insertion is transactional. Each tenant has an advisory migration lock. Logs are JSONL at .local/builder-migrations.jsonl.

Exports are restored into a NEW empty database, not over the original tenant. They contain no USE or DROP statements. Tests restore an actual demo export and compare source fingerprints. Media files remain in their existing locations; this migration never moves or removes them. Include uploaded files in normal operational backups.

For an existing-site maintenance window, pause legacy writes while refreshing, verifying and eventually activating. Source hashes detect intervening content changes; do not treat structural verification as a substitute for visual review.

## Verification

```powershell
& $phpExe -n -d "extension_dir=$ext" -d extension=pdo_mysql -d extension=mbstring scripts/test-builder.php
node scripts/test-builder-http.mjs
node scripts/test-saas.mjs
```

The PHP suite creates and retains isolated copies of demo-shaped data and sparse/interrupted fixtures; it does not mutate the original demo. It checks export restoration, idempotency, authorization, public/draft separation, stale revisions, rate limiting, atomic publish failure, discard, URL validation, escaped output and empty new sites.

The HTTP suite uses two isolated test accounts, checks owner vs visitor rendering, forged cross-site cookies, CSRF, blocked legacy writes, signup and 23 existing demo dashboard routes plus public URLs. The original test-saas.mjs CRUD suite runs against two additional restored legacy fixtures, retaining upload, spoofed-image and settings regression coverage. Test credentials stay in ignored .local/builder-test-sites.json and .local/legacy-test-sites.json.

Inline editing, onboarding answers beyond the current signup name, theme controls, the item drawer, undo/redo and account-settings UI are intentionally later phases. No browser was connected for visual QA; responsive CSS and HTTP rendering are implemented but visual/mobile interaction remains to be verified.
