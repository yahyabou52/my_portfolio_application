<link rel="stylesheet" href="<?= asset('css/wfe-editor.css') ?>">
<div class="wfe-toolbar" role="toolbar" aria-label="Website editor">
<div class="wfe-dock-heading"><button type="button" id="wfe-toggle" aria-expanded="true" aria-controls="wfe-actions"><span class="wfe-brand-mark" aria-hidden="true">✦</span> Website editor <span class="wfe-chevron" aria-hidden="true">⌃</span></button><span class="wfe-edit-hint">Click anything on your page to edit</span></div>
<div id="wfe-actions">
<div class="wfe-tools"><button type="button" id="wfe-sections"><span aria-hidden="true">▤</span> Sections</button><button type="button" id="wfe-appearance"><span aria-hidden="true">◐</span> Appearance</button><button type="button" id="wfe-content"><span aria-hidden="true">＋</span> Content</button><button type="button" id="wfe-pages">Pages</button><button type="button" id="wfe-preview">Preview</button></div>
<div class="wfe-publish-group"><div class="wfe-save-state"><span id="wfe-status" role="status" aria-live="polite">Saved draft</span><span id="wfe-publication"></span></div><button type="button" id="wfe-publish">Publish</button></div>
<div class="wfe-secondary"><div><button type="button" id="wfe-undo" disabled title="Undo (Ctrl+Z)">Undo</button><button type="button" id="wfe-redo" disabled title="Redo (Ctrl+Shift+Z)">Redo</button><button type="button" id="wfe-retry" hidden>Retry save</button><button type="button" id="wfe-reload" hidden>Reload latest draft</button></div><div><button type="button" id="wfe-discard">Discard changes</button><a href="<?= url('website-settings') ?>">Settings</a><a href="<?= url() ?>">Exit editor ↗</a></div></div>
</div></div>
<dialog class="wfe-dialog" id="wfe-field-dialog" aria-labelledby="wfe-dialog-title"><form method="dialog" id="wfe-field-form"><h2 id="wfe-dialog-title">Edit button</h2><label>Label<input id="wfe-label" maxlength="5000"></label><label>Destination<input id="wfe-url" placeholder="https://, mailto: or tel:"></label><p id="wfe-dialog-error" role="alert"></p><button type="button" id="wfe-apply-button">Apply</button><button value="cancel">Cancel</button></form></dialog>
<input type="file" id="wfe-image-file" accept="image/jpeg,image/png,image/webp,image/gif" hidden>
<script type="application/json" id="wfe-config"><?= json_encode($boot,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_THROW_ON_ERROR) ?></script>
<script src="<?= asset('js/wfe-editor.js') ?>" defer></script>

<dialog class="wfe-dialog wfe-panel" id="wfe-structure" aria-label="Sections and website settings">
<div class="wfe-panel-heading"><div><span class="wfe-eyebrow">YOUR WEBSITE</span><h2>Make it yours</h2></div><form method="dialog"><button aria-label="Close settings">Close</button></form></div><nav class="wfe-tabs" aria-label="Website customization"><button type="button" data-wfe-tab="sections" aria-pressed="true">Sections</button><button type="button" data-wfe-tab="pages" aria-pressed="false">Pages</button><button type="button" data-wfe-tab="appearance" aria-pressed="false">Appearance</button></nav><div data-wfe-pane="pages" hidden><h3>Pages &amp; navigation</h3><p class="wfe-help">Choose a page to edit its sections. Page titles and visibility stay in draft until Publish.</p><label>Page<select id="wfe-page-select"></select></label><label>Page title<input id="wfe-page-title" maxlength="160"></label><label><input type="checkbox" id="wfe-page-visible"> Visible to visitors</label><button type="button" id="wfe-save-page">Save page settings</button><button type="button" id="wfe-open-page">Edit this page</button><p id="wfe-page-note" class="wfe-help"></p></div><div data-wfe-pane="sections">
<h3>Your page, in order</h3><p class="wfe-help">Choose what visitors see. Move sections up or down, or hide them until you’re ready.</p>
<p id="wfe-section-context" class="wfe-help" hidden></p><div id="wfe-section-list"></div><label>Add a section<select id="wfe-section-type"></select></label><button type="button" id="wfe-add-section">Add section</button>
</div><div data-wfe-pane="appearance" hidden><h3>Set the mood</h3><p class="wfe-help">Personalize your colors and type. Changes stay private until you publish.</p><label>Primary color<input type="color" id="wfe-color" value="<?= htmlspecialchars($boot['theme']['primary_color']??'#7B3FE4',ENT_QUOTES) ?>"></label>
<label>Background<input type="color" id="wfe-background" value="<?= htmlspecialchars($boot['theme']['background_color']??'#ffffff',ENT_QUOTES) ?>"></label>
<label>Font<select id="wfe-font"><?php foreach(['original'=>'Original design','Inter'=>'Inter','Playfair Display'=>'Playfair Display'] as $value=>$label): ?><option value="<?= $value ?>" <?= ($boot['theme']['builder_font']??'original')===$value?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></label>
<button type="button" id="wfe-save-theme">Save theme to draft</button></div><p id="wfe-structure-error" role="alert"></p>
</dialog>
<dialog class="wfe-dialog wfe-preview" id="wfe-preview-dialog" aria-label="Device preview">
<form method="dialog"><button>Close preview</button><label>Device<select id="wfe-device"><option value="1440">Desktop</option><option value="768">Tablet</option><option value="390">Mobile</option></select></label></form>
<iframe id="wfe-preview-frame" title="Draft website device preview"></iframe>
</dialog>
<script src="<?= asset('js/wfe-structure.js') ?>" defer></script>
<script src="<?= asset('js/wfe-context.js') ?>" defer></script>
<script src="<?= asset('js/wfe-move.js') ?>" defer></script>

<?php require_once ROOT_PATH.'/app/core/builder/CollectionStore.php';require __DIR__.'/collections.php'; ?>
