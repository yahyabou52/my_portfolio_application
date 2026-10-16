# Editor workspace refresh
The owner interface is organized into Sections, Appearance, Content and Preview. A dedicated publish group distinguishes saving a draft from publishing the website. Undo, redo, discard and settings are secondary actions. The dock collapses, starts collapsed on mobile, and never changes public section geometry.

Sections and Appearance use separate views in the same customization drawer. Section cards expose visibility, ordering, layouts and removal. Content opens the existing shared collection drawer directly, with a link to all content and messages. Existing save/publish semantics remain unchanged.

Public templates, assets and design tokens are unchanged. Editor styles remain namespaced and owner-only. Modal dialogs support Escape and native focus containment; interactive controls have visible keyboard focus.

## Try it
Open an owner website and select Edit website. Use Sections to organize the page, Appearance to choose theme settings, Content to edit collections, and Preview to inspect devices. Click text or images directly on the page. Page edits remain drafts until Publish. On mobile, expand Website editor to reveal the controls.

## Verification
Run npm test with the local database environment configured. The suite includes scripts/test-editor-workspace.mjs for desktop/mobile navigation, drawer tabs, Escape dismissal and dock bounds. Screenshots are written to test-results/workspace. Existing suites cover draft conflicts, publish/discard, collections, authorization, onboarding, section dimensions and public screenshot parity.

## Live canvas updates
Section visibility, order, add/remove, variants and theme saves now refresh the same server-rendered draft inside the existing page. The editor dock and dialogs remain mounted. Discard and conflict recovery also update in place. Collection saves refresh the canvas automatically when invoked from the editor.

Refreshes reuse the authenticated edit route and current revision, rebind field and collection editing controls, preserve scroll position, and update the published/draft indicator. Failed preview requests expose Retry preview without repeating the write. No public renderer layout or collection publishing policy changes.

Regression tests preserve a window token across structure changes and Discard to prove no navigation occurred, and cover failed preview fetch recovery.

## Contextual editing and pages
Hover or focus a section to open its quick controls. Section settings highlights the matching section card and exposes that section's text, image and button fields. Collection sections open the same shared item drawer. On mobile, the compact controls retain settings and content access.

Pages provides titles, visibility and navigation to the supported Home, About, Portfolio, Services and Contact editors. For new sites, existing secondary routes can be configured as explicit draft pages. Existing migrated pages are preserved. Home cannot be hidden. All page configuration uses the existing owner/CSRF/revision-checked write endpoint and remains draft until Publish.

Editor surfaces inherit the public site's colors, fonts, border and background tokens, including dark mode. Public markup is unchanged.
