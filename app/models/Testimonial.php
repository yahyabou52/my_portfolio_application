<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';

class Testimonial extends BaseModel {
    protected $table = 'testimonials';
    protected $fillable = [
        'client_name',
        'client_position',
        'client_company',
        'image_path',
        'rating',
        'testimonial_text',
        'status',
        'is_featured',
        'sort_order'
    ];

    public function getAllOrdered(): array {
        return $this->all('sort_order ASC, id ASC');
    }

    public function getVisible($limit = null): array {
        return $this->where(['status' => 'published'], 'sort_order ASC, id ASC', $limit);
    }

    public function getFeatured($limit = null) {
        return $this->getVisible($limit);
    }

    public function getAverageRating(): float {
        $stmt = $this->db->prepare("SELECT AVG(rating) as avg_rating FROM {$this->table} WHERE status = 'published'");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? round((float)$result['avg_rating'], 1) : 0.0;
    }

    public function getNextSortOrder(): int {
        $stmt = $this->db->query("SELECT MAX(sort_order) AS max_sort FROM {$this->table}");
        $max = $stmt->fetchColumn();
        return $max !== false ? (int)$max + 1 : 1;
    }

    public function reorder(array $items): bool {
        if (empty($items)) {
            return true;
        }

        $this->db->beginTransaction();

        try {
            $sql = "UPDATE {$this->table} SET sort_order = :sort_order, status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $this->db->prepare($sql);

            foreach ($items as $item) {
                if (!isset($item['id'])) {
                    continue;
                }

                $stmt->bindValue(':id', (int)$item['id'], PDO::PARAM_INT);
                $stmt->bindValue(':sort_order', (int)($item['sort_order'] ?? 0), PDO::PARAM_INT);
                $stmt->bindValue(':status', !empty($item['is_visible']) ? 'published' : 'draft');
                $stmt->execute();
            }

            $this->db->commit();
            return true;
        } catch (Exception $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function getStats(): array {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'published' THEN 1 END) as visible,
                AVG(rating) as avg_rating
            FROM {$this->table}
        ");
        $stmt->execute();
        return $stmt->fetch() ?: ['total' => 0, 'visible' => 0, 'avg_rating' => 0];
    }
}