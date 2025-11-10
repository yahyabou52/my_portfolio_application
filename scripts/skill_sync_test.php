<?php
require __DIR__ . '/../config/config.php';
require ROOT_PATH . '/app/core/BaseModel.php';
require ROOT_PATH . '/app/models/SkillCategory.php';
require ROOT_PATH . '/app/models/Skill.php';

$skill = new Skill();

$data = [
    [
        'id' => 1,
        'title' => 'Product & UX',
        'icon_class' => 'bi-lightning-charge',
        'is_visible' => 1,
        'skills' => [
            [
                'id' => 1,
                'name' => 'User Research & Testing',
                'proficiency_level' => 90,
                'is_visible' => 1
            ],
            [
                'id' => 2,
                'name' => 'Product Strategy',
                'proficiency_level' => 85,
                'is_visible' => 1
            ]
        ]
    ]
];

try {
    $skill->syncStructure($data);
    echo "Sync completed successfully\n";
} catch (Throwable $exception) {
    echo 'Sync failed: ' . $exception->getMessage() . "\n";
}
