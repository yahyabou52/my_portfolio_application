# Phase 5
Optional setup now produces the initial published profile with no sample items. The same profile stays editable inline or in Website settings. Account settings independently manages private credentials. Old dashboard GET paths redirect to the appropriate website-first surface, while old writes remain blocked. Empty collections have owner-only add-first-item prompts in the section panel. Messages can be read and deleted; contact inserts now match their existing table schema.

Run node scripts/test-settings-ui.mjs and node scripts/test-collections-ui.mjs. See WEBSITE_FIRST_COMPLETION.md for complete verification, design limitations and safe existing-customer rollout.
