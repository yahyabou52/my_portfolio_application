<link rel="stylesheet" href="<?= asset('css/wfe-editor.css') ?>">
<div class="wfe-toolbar" role="toolbar" aria-label="Website editor">
<button type="button" id="wfe-toggle" aria-expanded="true" aria-controls="wfe-actions">Editor</button>
<div id="wfe-actions"><span id="wfe-status" role="status" aria-live="polite">Saved draft</span><span id="wfe-publication"></span><button type="button" id="wfe-undo" disabled>Undo</button><button type="button" id="wfe-redo" disabled>Redo</button><button type="button" id="wfe-retry" hidden>Retry save</button><button type="button" id="wfe-reload" hidden>Reload latest draft</button><button type="button" id="wfe-discard">Discard changes</button><button type="button" id="wfe-publish">Publish</button><a href="<?= url() ?>">Exit editor</a></div></div>
<dialog class="wfe-dialog" id="wfe-field-dialog" aria-labelledby="wfe-dialog-title"><form method="dialog" id="wfe-field-form"><h2 id="wfe-dialog-title">Edit button</h2><label>Label<input id="wfe-label" maxlength="5000"></label><label>Destination<input id="wfe-url" placeholder="https://, mailto: or tel:"></label><p id="wfe-dialog-error" role="alert"></p><button type="button" id="wfe-apply-button">Apply</button><button value="cancel">Cancel</button></form></dialog>
<input type="file" id="wfe-image-file" accept="image/jpeg,image/png,image/webp,image/gif" hidden>
<script type="application/json" id="wfe-config"><?= json_encode($boot,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_THROW_ON_ERROR) ?></script>
<script src="<?= asset('js/wfe-editor.js') ?>" defer></script>
