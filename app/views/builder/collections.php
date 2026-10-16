<dialog class="wfe-dialog wfe-drawer" id="wfe-items" aria-label="Manage content">
<div class="wfe-panel-heading"><div><span class="wfe-eyebrow">YOUR COLLECTIONS</span><h2>Manage content</h2></div><form method="dialog"><button>Close</button></form></div><p class="wfe-help">Add the work and experience that tell your story. Published items appear wherever this collection is used.</p>
<a class="wfe-manage-link" href="<?= url('manage') ?>">All content &amp; messages ↗</a>
<label>Collection<select id="wfe-collection"><?php foreach(CollectionStore::schemas() as $type=>$fields): ?><option value="<?= $type ?>"><?= ucfirst($type) ?></option><?php endforeach; ?></select></label>
<button type="button" id="wfe-new-item">Add item</button><div id="wfe-items-list"></div><p id="wfe-items-error" role="alert"></p>
</dialog>
<dialog class="wfe-dialog wfe-drawer" id="wfe-item" aria-labelledby="wfe-item-title">
<div class="wfe-panel-heading"><div><span class="wfe-eyebrow">CONTENT DETAILS</span><h2 id="wfe-item-title">Edit item</h2></div><form method="dialog"><button>Close item</button></form></div>
<p id="wfe-item-state"></p><p id="wfe-live-note" hidden>Live — saving this published item changes the public website immediately.</p>
<form id="wfe-item-form"><div id="wfe-item-fields"></div><p id="wfe-item-error" role="alert"></p>
<button type="submit" id="wfe-save-item">Save</button><button type="button" id="wfe-draft-item">Save as draft / Unpublish</button><button type="button" id="wfe-delete-item">Delete item</button></form>
</dialog>
<script type="application/json" id="wfe-collections-config"><?= json_encode(['api'=>url('editor-api/'),'csrf'=>csrf_token()],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?></script>
<script src="<?= asset('js/wfe-collections.js') ?>" defer></script>
