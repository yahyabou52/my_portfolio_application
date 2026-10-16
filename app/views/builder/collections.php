<dialog class="wfe-dialog wfe-drawer" id="wfe-items" aria-label="Manage content">
<form method="dialog"><button>Close</button></form><h2>Manage content</h2>
<label>Collection<select id="wfe-collection"><?php foreach(CollectionStore::schemas() as $type=>$fields): ?><option value="<?= $type ?>"><?= ucfirst($type) ?></option><?php endforeach; ?></select></label>
<button type="button" id="wfe-new-item">Add item</button><div id="wfe-items-list"></div><p id="wfe-items-error" role="alert"></p>
</dialog>
<dialog class="wfe-dialog wfe-drawer" id="wfe-item" aria-labelledby="wfe-item-title">
<form method="dialog"><button>Close item</button></form><h2 id="wfe-item-title">Edit item</h2>
<p id="wfe-item-state"></p><p id="wfe-live-note" hidden>Live — saving this published item changes the public website immediately.</p>
<form id="wfe-item-form"><div id="wfe-item-fields"></div><p id="wfe-item-error" role="alert"></p>
<button type="submit" id="wfe-save-item">Save</button><button type="button" id="wfe-draft-item">Save as draft / Unpublish</button><button type="button" id="wfe-delete-item">Delete item</button></form>
</dialog>
<script type="application/json" id="wfe-collections-config"><?= json_encode(['api'=>url('editor-api/'),'csrf'=>csrf_token()],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?></script>
<script src="<?= asset('js/wfe-collections.js') ?>" defer></script>
