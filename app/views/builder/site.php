<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= $e($page['title']) ?></title>
<?php if($editing): ?><meta name="robots" content="noindex,nofollow"><?php endif; ?>
<link rel="stylesheet" href="<?= asset('css/builder.css') ?>">
<style>:root{--brand:<?= $e($theme['color']) ?>;--paper:<?= $e($theme['background']) ?>;--body-font:<?= ['system'=>'system-ui, sans-serif','serif'=>'Georgia, serif','mono'=>'ui-monospace, monospace'][$theme['font']] ?>}</style></head>
<body>
<?php if($editing): ?><aside class="builder-bar" aria-label="Website editor"><strong>Draft preview · Phase 1</strong><span>Editing controls arrive in Phase 2.</span><a href="<?= url() ?>">View published website</a></aside><?php endif; ?>
<header class="site-header"><a class="wordmark" href="<?= url() ?>"><?= $e($profile['name']) ?></a><nav aria-label="Website navigation"><?php foreach($sections as $s): if(!$s['visible'])continue; $type=$s['type']; if($type==='hero')continue;if(isset(SectionRegistry::get($type)['collection'])&&!$this->collections($type))continue;if($type==='about'&&$profile['bio']==='')continue;if($type==='contact'&&$profile['email']===''&&$profile['phone']===''&&$profile['location']===''&&!$profile['social'])continue; ?><a href="#<?= $e($s['id']) ?>"><?= $e($s['settings']['heading']??ucfirst($type)) ?></a><?php endforeach; ?></nav></header>
<main id="main"><?= implode("\n",$rendered) ?></main>
<footer class="site-footer"><span><?= $e($profile['name']) ?></span><span>© <?= date('Y') ?></span></footer>
<?php if($owner&&!$editing): ?><a class="owner-edit" href="<?= url('edit') ?>">Edit website</a><?php endif; ?>
</body></html>
