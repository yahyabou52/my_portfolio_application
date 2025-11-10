<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';

class FeaturedProject extends BaseModel {
    protected $table = 'projects';
    protected $primaryKey = 'id';
    protected $fillable = [
        'featured',
        'featured_sort_order',
        'updated_at'
    ];

    public function getAllProjects(): array {
        $sql = "SELECT * FROM {$this->table} ORDER BY title ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getFeaturedProjects(): array {
        $sql = "SELECT * FROM {$this->table} WHERE featured = 1 ORDER BY featured_sort_order ASC, sort_order ASC, id ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function updateFeaturedList(array $projectIds): bool {
        $this->db->beginTransaction();

        try {
            // Unfeature projects not in list
            if (empty($projectIds)) {
                $sql = "UPDATE {$this->table} SET featured = 0, featured_sort_order = 0 WHERE featured = 1";
                $this->db->exec($sql);
            } else {
                $placeholders = implode(',', array_fill(0, count($projectIds), '?'));
                $sql = "UPDATE {$this->table} SET featured = 0, featured_sort_order = 0 WHERE featured = 1 AND id NOT IN ({$placeholders})";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($projectIds);
            }

            // Update order for provided projects
            $order = 1;
            foreach ($projectIds as $projectId) {
                $stmt = $this->db->prepare("UPDATE {$this->table} SET featured = 1, featured_sort_order = :order WHERE id = :id");
                $stmt->bindValue(':order', $order++, PDO::PARAM_INT);
                $stmt->bindValue(':id', $projectId, PDO::PARAM_INT);
                $stmt->execute();
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
