# Phase 3 — section controls
Sections can be shown, hidden, removed, restored and reordered without wrappers. New sites allow one instance per type because original templates contain stable form/accordion IDs. Existing pages preserve their original section set and ordering; removing a section hides it without deleting original content.

Theme changes target the existing color tokens and existing Inter/Playfair fonts. Projects offer the existing homepage cards or portfolio gallery. Other sections retain their original layout: inventing alternatives would violate the design constraint. Device preview uses the same authorized draft renderer without editor controls.

Run node scripts/test-structure-ui.mjs after scripts/test-builder.php creates fixtures. Browser coverage exercises section hide/show/remove/add, theme draft isolation, exact 390px preview, and discard. node scripts/test-editor-ui.mjs verifies no edit-mode dimension changes.
