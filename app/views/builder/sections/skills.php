<h2><?= $e($settings['heading']??'Skills') ?></h2><ul class="skill-list"><?php foreach($items as $item): ?><li><?= $e($item['name']) ?></li><?php endforeach; ?></ul>
