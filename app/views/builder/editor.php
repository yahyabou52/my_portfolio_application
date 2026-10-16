<link rel="stylesheet" href="<?= asset('css/wfe-editor.css') ?>">
<div class="wfe-toolbar" role="toolbar" aria-label="Website editor">
<button type="button" id="wfe-toggle" aria-expanded="true" aria-controls="wfe-actions">Editor</button>
<div id="wfe-actions"><span id="wfe-status" role="status" aria-live="polite">Saved draft</span><span id="wfe-publication"></span><button type="button" id="wfe-sections">Sections & theme</button><button type="button" id="wfe-preview">Device preview</button><a href="<?= url('manage') ?>">Manage content</a><button type="button" id="wfe-undo" disabled>Undo</button><button type="button" id="wfe-redo" disabled>Redo</button><button type="button" id="wfe-retry" hidden>Retry save</button><button type="button" id="wfe-reload" hidden>Reload latest draft</button><button type="button" id="wfe-discard">Discard changes</button><button type="button" id="wfe-publish">Publish</button><a href="<?= url() ?>">Exit editor</a></div></div>
<dialog class="wfe-dialog" id="wfe-field-dialog" aria-labelledby="wfe-dialog-title"><form method="dialog" id="wfe-field-form"><h2 id="wfe-dialog-title">Edit button</h2><label>Label<input id="wfe-label" maxlength="5000"></label><label>Destination<input id="wfe-url" placeholder="https://, mailto: or tel:"></label><p id="wfe-dialog-error" role="alert"></p><button type="button" id="wfe-apply-button">Apply</button><button value="cancel">Cancel</button></form></dialog>
<input type="file" id="wfe-image-file" accept="image/jpeg,image/png,image/webp,image/gif" hidden>
<script type="application/json" id="wfe-config"><?= json_encode($boot,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_THROW_ON_ERROR) ?></script>
<script src="<?= asset('js/wfe-editor.js') ?>" defer></script>

<dialog class="wfe-dialog wfe-panel" id="wfe-structure" aria-label="Sections and website settings">
<form method="dialog"><button aria-label="Close settings">Close</button></form>
<h2>Sections</h2><p>Original layouts stay responsive. Empty collections appear after publishing an item in Manage content.</p>
<div id="wfe-section-list"></div><label>Add a section<select id="wfe-section-type"></select></label><button type="button" id="wfe-add-section">Add section</button>
<h2>Theme</h2><label>Primary color<input type="color" id="wfe-color" value="<?= htmlspecialchars($boot['theme']['primary_color']??'#7B3FE4',ENT_QUOTES) ?>"></label>
<label>Background<input type="color" id="wfe-background" value="<?= htmlspecialchars($boot['theme']['background_color']??'#ffffff',ENT_QUOTES) ?>"></label>
<label>Font<select id="wfe-font"><?php foreach(['original'=>'Original design','Inter'=>'Inter','Playfair Display'=>'Playfair Display'] as $value=>$label): ?><option value="<?= $value ?>" <?= ($boot['theme']['builder_font']??'original')===$value?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></label>
<button type="button" id="wfe-save-theme">Save theme to draft</button><p id="wfe-structure-error" role="alert"></p>
</dialog>
<dialog class="wfe-dialog wfe-preview" id="wfe-preview-dialog" aria-label="Device preview">
<form method="dialog"><button>Close preview</button><label>Device<select id="wfe-device"><option value="1440">Desktop</option><option value="768">Tablet</option><option value="390">Mobile</option></select></label></form>
<iframe id="wfe-preview-frame" title="Draft website device preview"></iframe>
</dialog>
<script src="<?= asset('js/wfe-structure.js') ?>" defer></script>
