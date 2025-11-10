<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';
require_once ROOT_PATH . '/app/models/ServiceFeature.php';

class Service extends BaseModel {
    protected $table = 'services';
    protected $fillable = [
        'title',
        'icon',
        'description',
        'price_label',
        'price_amount',
        'status',
        'homepage_featured',
        'homepage_is_visible',
        'homepage_sort_order',
        'sort_order'
    ];

    private $featureModel;

    /**
     * Backwards-compatible alias for legacy callers expecting getActive().
     * Uses the same query as getPublished() to return published services.
     */
    public function getActive($limit = null) {
        return $this->getPublished($limit);
    }

    public function getPublished($limit = null) {
        $services = $this->where(['status' => 'published'], 'sort_order ASC, title ASC', $limit);
        return $this->attachFeatures($services);
    }

    public function updateSort($serviceId, $sortOrder) {
        return $this->update($serviceId, ['sort_order' => $sortOrder]);
    }

    public function getHomepageFeatured(): array {
        $services = $this->where(['homepage_featured' => 1], 'homepage_sort_order ASC, title ASC');
        return $this->attachFeatures($services);
    }

    public function updateHomepageSelection(array $items): bool {
        $this->db->beginTransaction();

        try {
            $ids = array_column($items, 'id');

            if (empty($ids)) {
                $sql = "UPDATE {$this->table} SET homepage_featured = 0, homepage_sort_order = 0, homepage_is_visible = 1 WHERE homepage_featured = 1";
                $this->db->exec($sql);
                $this->db->commit();
                return true;
            }

            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $resetSql = "UPDATE {$this->table} SET homepage_featured = 0, homepage_sort_order = 0, homepage_is_visible = 1 WHERE homepage_featured = 1 AND id NOT IN ({$placeholders})";
            $resetStmt = $this->db->prepare($resetSql);
            $resetStmt->execute($ids);

            $order = 1;
            $updateSql = "UPDATE {$this->table} SET homepage_featured = 1, homepage_sort_order = :sort_order, homepage_is_visible = :visible WHERE id = :id";
            $updateStmt = $this->db->prepare($updateSql);

            foreach ($items as $item) {
                $id = (int)($item['id'] ?? 0);
                if (!$id) {
                    continue;
                }

                $visible = isset($item['visible']) ? (int)$item['visible'] : 1;

                $updateStmt->bindValue(':sort_order', $order, PDO::PARAM_INT);
                $updateStmt->bindValue(':visible', $visible ? 1 : 0, PDO::PARAM_INT);
                $updateStmt->bindValue(':id', $id, PDO::PARAM_INT);
                $updateStmt->execute();

                $order += 1;
            }

            $this->db->commit();
            return true;
        } catch (Exception $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function __construct() {
        parent::__construct();

        $this->featureModel = new ServiceFeature();
    }

    public function getAllWithFeatures(bool $includeDrafts = true): array {
        $conditions = [];
        if (!$includeDrafts) {
            $conditions['status'] = 'published';
        }

        $services = empty($conditions)
            ? $this->all('sort_order ASC, title ASC')
            : $this->where($conditions, 'sort_order ASC, title ASC');

        return $this->attachFeatures($services, true, true);
    }

    public function syncServices(array $services): bool {
        $this->db->beginTransaction();

        try {
            $order = 1;
            $keptIds = [];

            foreach ($services as $service) {
                $id = (int)($service['id'] ?? 0);

                $payload = [
                    'title' => $service['title'],
                    'icon' => $service['icon'] !== '' ? $service['icon'] : null,
                    'description' => $service['description'],
                    'price_label' => $service['price_label'] !== '' ? $service['price_label'] : null,
                    'price_amount' => $service['price_amount'] !== null ? (float)$service['price_amount'] : null,
                    'status' => !empty($service['is_visible']) ? 'published' : 'draft',
                    'sort_order' => $order
                ];

                if ($id > 0) {
                    $this->update($id, $payload);
                } else {
                    $id = (int)$this->create($payload);
                }

                $keptIds[] = $id;
                $order += 1;

                $features = isset($service['features']) && is_array($service['features'])
                    ? $service['features']
                    : [];

                $this->syncFeatures($id, $features);
            }

            $this->deleteMissing($keptIds);

            $this->db->commit();
            return true;
        } catch (Exception $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    private function deleteMissing(array $keepIds): void {
        if (empty($keepIds)) {
            $sql = "DELETE FROM {$this->table}";
            $this->db->exec($sql);
            return;
        }

        $placeholders = implode(',', array_fill(0, count($keepIds), '?'));
        $sql = "DELETE FROM {$this->table} WHERE id NOT IN ({$placeholders})";
        $statement = $this->db->prepare($sql);
        foreach ($keepIds as $index => $id) {
            $statement->bindValue($index + 1, (int)$id, PDO::PARAM_INT);
        }
        $statement->execute();
    }

    private function syncFeatures(int $serviceId, array $features): void {
        if (empty($features)) {
            return;
        }

        $shouldSync = false;
        foreach ($features as $feature) {
            if (is_array($feature) && (isset($feature['icon_class']) || isset($feature['display']) || isset($feature['id']))) {
                $shouldSync = true;
                break;
            }
        }

        if (!$shouldSync) {
            return;
        }

        $this->featureModel->deleteByService($serviceId);

        $order = 1;
        foreach ($features as $featureData) {
            if (is_array($featureData)) {
                $text = trim((string)($featureData['feature_text'] ?? $featureData['text'] ?? ''));
                $icon = trim((string)($featureData['icon_class'] ?? ''));
                $displayRaw = $featureData['display'] ?? 1;
            } else {
                $text = trim((string)$featureData);
                $icon = '';
                $displayRaw = 1;
            }

            if ($text === '') {
                continue;
            }

            $this->featureModel->create([
                'service_id' => $serviceId,
                'feature_text' => mb_substr($text, 0, 255),
                'icon_class' => $icon !== '' ? mb_substr($icon, 0, 100) : null,
                'sort_order' => $order,
                'display' => in_array((string)$displayRaw, ['0', 'false', 'no'], true) ? 0 : (int)!empty($displayRaw)
            ]);

            $order += 1;
        }
    }

    private function attachFeatures(array $services, bool $includeVisibilityFlag = false, bool $includeHiddenFeatures = false): array {
        if (empty($services)) {
            return [];
        }

        return array_map(function ($service) use ($includeVisibilityFlag, $includeHiddenFeatures) {
            $serviceId = (int)($service['id'] ?? 0);
            $rawFeatures = $serviceId > 0
                ? $this->featureModel->getByService($serviceId, $includeHiddenFeatures)
                : [];

            $mappedFeatures = array_map(function ($feature) {
                return [
                    'id' => (int)($feature['id'] ?? 0),
                    'feature_text' => (string)($feature['feature_text'] ?? ''),
                    'icon_class' => $feature['icon_class'] ?? null,
                    'sort_order' => (int)($feature['sort_order'] ?? 0),
                    'display' => isset($feature['display']) ? (int)$feature['display'] : 1
                ];
            }, $rawFeatures);

            if (!$includeHiddenFeatures) {
                $mappedFeatures = array_values(array_filter($mappedFeatures, function ($feature) {
                    return (int)$feature['display'] === 1;
                }));
            }

            $service['features'] = $mappedFeatures;

            if ($includeVisibilityFlag) {
                $service['is_visible'] = ($service['status'] ?? 'draft') === 'published' ? 1 : 0;
            }

            return $service;
        }, $services);
    }
}