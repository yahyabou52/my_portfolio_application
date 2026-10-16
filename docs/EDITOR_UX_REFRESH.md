# Editor workspace refresh
The owner interface is organized into Sections, Appearance, Content and Preview. A dedicated publish group distinguishes saving a draft from publishing the website. Undo, redo, discard and settings are secondary actions. The dock collapses, starts collapsed on mobile, and never changes public section geometry.

Sections and Appearance use separate views in the same customization drawer. Section cards expose visibility, ordering, layouts and removal. Content opens the existing shared collection drawer directly, with a link to all content and messages. Existing save/publish semantics remain unchanged.

Public templates, assets and design tokens are unchanged. Editor styles remain namespaced and owner-only. Modal dialogs support Escape and native focus containment; interactive controls have visible keyboard focus.

## Try it
Open an owner website and select Edit website. Use Sections to organize the page, Appearance to choose theme settings, Content to edit collections, and Preview to inspect devices. Click text or images directly on the page. Page edits remain drafts until Publish. On mobile, expand Website editor to reveal the controls.

## Verification
Run npm test with the local database environment configured. The suite includes scripts/test-editor-workspace.mjs for desktop/mobile navigation, drawer tabs, Escape dismissal and dock bounds. Screenshots are written to test-results/workspace. Existing suites cover draft conflicts, publish/discard, collections, authorization, onboarding, section dimensions and public screenshot parity.
