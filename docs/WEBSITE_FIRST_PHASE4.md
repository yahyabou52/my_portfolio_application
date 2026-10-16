# Phase 4 — shared collection management
/manage is a compact owner-only collections and messages surface. Projects, skills, experience, services and testimonials use one drawer shared with inline card clicks. New items start Draft; Publish item makes them live. Published items display a Live warning; Save as draft / Unpublish hides them. Item edits use row hashes to reject stale writes. Page drafts and collection records remain separate.

Every endpoint checks the tenant owner and CSRF. Image selection uses the existing MIME-validated upload pipeline and verifies ownership. Links accept http, https, mailto and tel only. Existing public templates gain conditional data attributes only in owner edit mode.

Run node scripts/test-collections-ui.mjs after data fixtures. It covers the shared drawer, draft/publish/live update/unpublish, all five collection types, stale edits, invalid CSRF and unsafe links.
