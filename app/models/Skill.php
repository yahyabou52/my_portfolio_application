<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';
require_once ROOT_PATH . '/app/models/SkillCategory.php';

class Skill extends BaseModel {
    protected $table = 'skills';
    protected $fillable = [
        'category_id',
        'name',
        'proficiency_level',
        'sort_order',
        'is_visible'
    ];

    private $categoryModel;

    public function __construct() {
        parent::__construct();

        $this->categoryModel = new SkillCategory();
    }

    public function getByCategory(int $categoryId, bool $includeHidden = false): array {
        if ($categoryId <= 0) {
            return [];
        }

        $conditions = ['category_id' => $categoryId];

        if (!$includeHidden) {
            $conditions['is_visible'] = 1;
        }

        return $this->where($conditions, 'sort_order ASC, name ASC');
    }

    public function getGroupedByCategory(bool $includeHidden = false): array {
        $categories = $this->categoryModel->getOrdered($includeHidden);
        $grouped = [];

        foreach ($categories as $category) {
            $title = $category['title'] ?? 'Skillset';

            $grouped[$title] = [
                'meta' => $category,
                'items' => $this->getByCategory((int)($category['id'] ?? 0), $includeHidden)
            ];
        }

        return $grouped;
    }

    public function getFlatList(bool $includeHidden = false): array {
        if ($includeHidden) {
            return $this->all('category_id ASC, sort_order ASC, name ASC');
        }

        return $this->where(['is_visible' => 1], 'category_id ASC, sort_order ASC, name ASC');
    }

    public function getCategoriesWithSkills(bool $includeHidden = false): array {
        $categories = $this->categoryModel->getOrdered($includeHidden);
        $structured = [];

        foreach ($categories as $category) {
            $categoryId = (int)($category['id'] ?? 0);
            $skills = $this->getByCategory($categoryId, $includeHidden);

            $structured[] = [
                'id' => $categoryId,
                'title' => $category['title'] ?? '',
                'icon_class' => $category['icon_class'] ?? '',
                'sort_order' => (int)($category['sort_order'] ?? 0),
                'is_visible' => (int)($category['is_visible'] ?? 0),
                'skills' => array_map(function ($skill) {
                    return [
                        'id' => (int)($skill['id'] ?? 0),
                        'category_id' => (int)($skill['category_id'] ?? 0),
                        'name' => $skill['name'] ?? '',
                        'proficiency_level' => (int)($skill['proficiency_level'] ?? 0),
                        'sort_order' => (int)($skill['sort_order'] ?? 0),
                        'is_visible' => (int)($skill['is_visible'] ?? 0)
                    ];
                }, $skills)
            ];
        }

        return $structured;
    }

    public function syncStructure(array $structure): bool {
        $this->db->beginTransaction();

        try {
            $keptCategoryIds = [];
            $categoryOrder = 1;

            foreach ($structure as $category) {
                $categoryId = (int)($category['id'] ?? 0);
                $categoryData = [
                    'title' => $category['title'],
                    'icon_class' => $category['icon_class'],
                    'sort_order' => $categoryOrder,
                    'is_visible' => !empty($category['is_visible']) ? 1 : 0
                ];

                if ($categoryId > 0) {
                    $this->categoryModel->update($categoryId, $categoryData);
                } else {
                    $categoryId = (int)$this->categoryModel->create($categoryData);
                }

                $keptCategoryIds[] = $categoryId;
                $categoryOrder += 1;

                $skillOrder = 1;
                $keptSkillIds = [];

                foreach ($category['skills'] as $skill) {
                    $skillId = (int)($skill['id'] ?? 0);
                    $skillData = [
                        'category_id' => $categoryId,
                        'name' => $skill['name'],
                        'proficiency_level' => $skill['proficiency_level'],
                        'sort_order' => $skillOrder,
                        'is_visible' => !empty($skill['is_visible']) ? 1 : 0
                    ];

                    if ($skillId > 0) {
                        $this->update($skillId, $skillData);
                    } else {
                        $skillId = (int)$this->create($skillData);
                    }

                    $keptSkillIds[] = $skillId;
                    $skillOrder += 1;
                }

                $this->removeMissingForCategory($categoryId, $keptSkillIds);
            }

            $this->categoryModel->deleteMissing($keptCategoryIds);

            $this->db->commit();
            return true;
        } catch (Exception $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function removeMissingForCategory(int $categoryId, array $keepIds): void {
        if ($categoryId <= 0) {
            return;
        }

        if (empty($keepIds)) {
            $statement = $this->db->prepare("DELETE FROM {$this->table} WHERE category_id = :category_id");
            $statement->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
            $statement->execute();
            return;
        }

        $placeholderNames = [];
        $parameters = [':category_id' => $categoryId];

        foreach ($keepIds as $index => $id) {
            $param = ':keep_' . $index;
            $placeholderNames[] = $param;
            $parameters[$param] = (int)$id;
        }

        $sql = "DELETE FROM {$this->table} WHERE category_id = :category_id AND id NOT IN (" . implode(',', $placeholderNames) . ")";
        $statement = $this->db->prepare($sql);

        foreach ($parameters as $key => $value) {
            $statement->bindValue($key, $value, PDO::PARAM_INT);
        }

        $statement->execute();
    }
}