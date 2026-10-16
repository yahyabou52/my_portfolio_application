<?php $escape=fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8'); ?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Manage content</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="<?= asset('css/wfe-editor.css') ?>"></head><body>
<main class="container py-5"><nav class="d-flex gap-3 mb-4"><a href="<?= url() ?>">Website</a><a href="<?= url('edit') ?>">Edit website</a><a href="<?= url('account') ?>">Account settings</a><a href="<?= url('website-settings') ?>">Website settings</a></nav>
<h1>Manage content</h1><p>Publish items here to show them wherever their collection is used. Page layout and text have their own Publish button in the editor.</p>
<div class="d-flex gap-2 flex-wrap mb-4"><?php foreach(CollectionStore::schemas() as $type=>$fields): ?><button class="btn btn-outline-primary" data-manage-collection="<?= $type ?>"><?= ucfirst($type) ?></button><?php endforeach; ?></div>
<h2>Messages</h2><?php if(!$messages): ?><p>No messages yet. New enquiries appear here.</p><?php endif; ?>
<?php foreach($messages as $message): ?><details class="border rounded p-3 mb-2"><summary><?= $escape($message['subject']) ?> — <?= $escape($message['name']) ?></summary><p><?= $escape($message['email']) ?></p><p><?= nl2br($escape($message['message'])) ?></p><small><?= $escape($message['created_at']) ?></small></details><?php endforeach; ?>
</main><?php require __DIR__.'/collections.php'; ?></body></html>
