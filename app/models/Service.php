<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';

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

    /**
     * Backwards-compatible alias for legacy callers expecting getActive().
     * Uses the same query as getPublished() to return published services.
     */
    public function getActive($limit = null) {
        return $this->getPublished($limit);
    }

    public function getPublished($limit = null) {
        return $this->where(['status' => 'published'], 'sort_order ASC, title ASC', $limit);
    }

    public function updateSort($serviceId, $sortOrder) {
        return $this->update($serviceId, ['sort_order' => $sortOrder]);
    }

    public function getHomepageFeatured(): array {
        return $this->where(['homepage_featured' => 1], 'homepage_sort_order ASC, title ASC');
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
}