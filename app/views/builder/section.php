<section id="<?= $e($section['id']) ?>" class="portfolio-section section-<?= $e($section['type']) ?> variant-<?= $e($section['variant']) ?><?= !$section['visible']?' section-hidden':'' ?>"<?= $editing?' data-section-id="'.$e($section['id']).'"':'' ?>>
<?php if($editing): ?><div class="section-meta"><?= $e(ucfirst($section['type'])) ?> · <?= $section['visible']?'Visible':'Hidden from visitors' ?></div><?php endif; ?>
<?php if($empty&&$editing): ?><div class="empty-section"><h2><?= $e($settings['heading']??ucfirst($section['type'])) ?></h2><p><?= $e(match($section['type']){'projects'=>'Add your first project','skills'=>'Add your first skill','experience'=>'Add your first experience','about'=>'Add your biography','contact'=>'Add your contact details',default=>'Add content'}) ?></p><span>Content controls will be available in the next phases.</span></div>
<?php else: require ROOT_PATH.'/app/views/builder/sections/'.$spec['component'].'.php'; endif; ?>
</section>
