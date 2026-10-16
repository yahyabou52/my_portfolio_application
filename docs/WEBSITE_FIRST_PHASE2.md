# Website-first Phase 2

Step 0 passed before editor implementation: 18 real-Chrome section comparisons at 1440, 768 and 390 pixels, with zero different pixels. Existing public templates, markup, assets, fonts and CSS remain the rendering source. See DESIGN_PARITY.md.

The owner-only editor adds data attributes to existing elements and loads wfe-editor.css / wfe-editor.js only in authenticated edit mode. Toolbar and dialogs are fixed overlays; outlines do not affect layout. Visitors receive neither editor assets, editor controls nor serialized drafts. The owner-only Edit website link uses existing Bootstrap utility classes.

Draft APIs at /s/{slug}/editor-api/{state,save,publish,discard,upload} enforce current owner role, site identity and CSRF for mutations. Autosave sends allowlisted field patches, not arbitrary documents. Revision conflicts return HTTP 409; repeated immediate saves return 429. The client debounces, serializes writes, retries transient failures and retains failed changes.

Text is plain text, escaped by existing templates. Paste/drop handlers discard HTML formatting. Chrome's plaintext-only contenteditable mode forces pre-wrap even on text that previously used normal whitespace, so contenteditable=true plus plain-text handling preserves the existing design. Rich HTML is not introduced.

Buttons use a keyboard-accessible dialog. Modified links accept only http, https, mailto and tel. Existing internal links remain unchanged until edited; their dialog displays an absolute URL. Images use the existing MIME/size-checked upload pipeline and tenant-owned media paths.

Undo/redo applies to field edits. Publish first waits for pending autosaves, then atomically switches the public version. Discard reloads the last published document. Conflicts stop automatic saving and expose an explicit reload action.

Verification:
- scripts/test-builder.php: data model, migrations, authorization, atomic publish/discard, conflicts.
- scripts/test-builder-http.mjs: public privacy, owner permissions and legacy-write protection.
- scripts/test-saas.mjs: legacy dashboard editing and upload regression checks.
- scripts/test-editor-ui.mjs: real-browser field edits, undo/redo, publish/discard, conflict UI, links, uploads, visitor isolation and section bounding-box comparisons.
- scripts/test-design-parity.mjs: committed visual references and legacy-to-renderer comparisons.

Use scripts/prepare-design-parity.php --editor in the documented PHP environment to create an isolated interactive fixture. Its credentials/URL are in ignored .local/editor-fixture.json. Screenshot artifacts and reports are under test-results/editor and test-results/design-parity.

Final parity verification: all 18 comparisons against both the committed legacy baseline and current legacy renderer passed with zero differing pixels. An unrelated navigation bug fix was reverted because it changed the existing contact counter color. No visual differences were accepted.
