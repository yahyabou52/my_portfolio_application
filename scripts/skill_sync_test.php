<?php
if(!preg_match('/^portfolio_builder_test_[a-f0-9]+$/',(string)getenv('DB_NAME'))){fwrite(STDERR,"Set DB_NAME to an isolated builder test database.\n");exit(1);}
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
    $rows=$skill->getFlatList(true);if(count($rows)!==2||$rows[0]['name']!=='User Research & Testing')throw new RuntimeException('Unexpected synchronized skill records.');
    echo "PASS: legacy skill synchronization on an isolated fixture\n";
} catch (Throwable $exception) {
    fwrite(STDERR,'Sync failed: ' . $exception->getMessage() . "\n");exit(1);
}
