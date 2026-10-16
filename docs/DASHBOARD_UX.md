# Dashboard workflow

The sidebar has eight destinations: Overview, Website pages, Brand & contact, Projects, Services, Media library, Messages and Appearance. Account administration is in the header menu.

Website pages owns page text, section visibility and order, publication, search metadata, navigation and footer settings. Collections are linked from the relevant page with a return link to the editor.

Brand & contact owns shared identity, images, contact details and social profiles. Appearance owns colors, default light/dark mode, the visitor theme switch and optional CSS. Saving one area preserves settings owned by other areas.

The preview displays saved content. Authenticated owners can preview unpublished pages; anonymous requests return 404. Unpublished pages are removed from shared navigation. Empty menus and footer link lists no longer recreate placeholder links.

Validation failures retain submitted edits for the next render. Section order uses up/down controls. The editor warns before leaving with unsaved changes. Legacy footer and page-metadata GET routes redirect to the unified editor; POST routes remain compatible.

## Verification

Run `node scripts/test-saas.mjs` against the local server. The suite creates isolated QA customers and tests public rendering, account isolation, uploads, all editor groups, draft recovery, settings independence, publication and preview authorization. It exercises 22 dashboard routes and parses inline JavaScript. QA accounts remain for inspection.

PHP syntax checks, JavaScript syntax checks and git diff whitespace checks passed. Visual desktop/mobile verification remains unverified because no connected browser was available.
