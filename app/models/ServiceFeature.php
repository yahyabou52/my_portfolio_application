<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';

class ServiceFeature extends BaseModel {
    private static $schemaEnsured = false;

    public function __construct() {
        parent::__construct();
        $this->ensureSchema();
    }

    protected $table = 'service_features';
    protected $fillable = [
        'service_id',
        'feature_text',
        'icon_class',
        'sort_order',
        'display'
    ];

    public function getByService(int $serviceId, bool $includeHidden = false): array {
        $sql = "SELECT * FROM {$this->table} WHERE service_id = :service_id";

        if (!$includeHidden) {
            $sql .= " AND display = 1";
        }

        $sql .= " ORDER BY sort_order ASC, id ASC";

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':service_id', $serviceId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll() ?: [];
    }

    private function ensureSchema(): void {
        if (self::$schemaEnsured) {
            return;
        }

        try {
            $columns = $this->db->query("SHOW COLUMNS FROM {$this->table}")->fetchAll(PDO::FETCH_COLUMN);
            $columns = array_map('strtolower', $columns);

            if (!in_array('icon_class', $columns, true)) {
                $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN icon_class VARCHAR(120) NULL AFTER feature_text");
            }

            if (!in_array('display', $columns, true)) {
                $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN display TINYINT(1) NOT NULL DEFAULT 1 AFTER sort_order");
            }
        } catch (Exception $exception) {
            error_log('ServiceFeature schema ensure failed: ' . $exception->getMessage());
        }

        self::$schemaEnsured = true;
    }

    public function deleteByService($serviceId) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE service_id = :service_id");
        $stmt->bindValue(':service_id', $serviceId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getNextSortOrder(int $serviceId): int {
        $sql = "SELECT COALESCE(MAX(sort_order), 0) + 1 AS next_order FROM {$this->table} WHERE service_id = :service_id";
        $statement = $this->db->prepare($sql);
        $statement->bindValue(':service_id', $serviceId, PDO::PARAM_INT);
        $statement->execute();

        $result = $statement->fetch();
        return (int)($result['next_order'] ?? 1);
    }

    public function reorderForService(int $serviceId, array $orderedIds): void {
        if (empty($orderedIds)) {
            return;
        }

        $this->db->beginTransaction();

        try {
            $position = 1;
            $query = "UPDATE {$this->table} SET sort_order = :sort_order WHERE id = :id AND service_id = :service_id";
            $statement = $this->db->prepare($query);

            foreach ($orderedIds as $id) {
                $featureId = (int)$id;
                if ($featureId <= 0) {
                    continue;
                }

                $statement->bindValue(':sort_order', $position++, PDO::PARAM_INT);
                $statement->bindValue(':id', $featureId, PDO::PARAM_INT);
                $statement->bindValue(':service_id', $serviceId, PDO::PARAM_INT);
                $statement->execute();
            }

            $this->db->commit();
        } catch (Exception $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function toggleDisplay(int $featureId, bool $display): bool {
        $sql = "UPDATE {$this->table} SET display = :display, updated_at = :updated_at WHERE id = :id";
        $statement = $this->db->prepare($sql);
        $statement->bindValue(':display', $display ? 1 : 0, PDO::PARAM_INT);
        $statement->bindValue(':updated_at', date('Y-m-d H:i:s'));
        $statement->bindValue(':id', $featureId, PDO::PARAM_INT);

        return $statement->execute();
    }
}
