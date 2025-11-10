<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';

class ServiceProcessStep extends BaseModel {
    protected $table = 'design_process_steps';
    protected $fillable = [
        'service_id',
        'step_order',
        'icon_class',
        'title',
        'description',
        'display'
    ];

    public function getPublished(?int $serviceId = null, ?int $limit = null): array {
        $conditions = ['display' => 1];

        if ($serviceId !== null) {
            $conditions['service_id'] = $serviceId;
        }

        return $this->where($conditions, 'step_order ASC, id ASC', $limit);
    }

    public function getByService(?int $serviceId = null, bool $includeHidden = false): array {
        $sql = "SELECT * FROM {$this->table}";
        $conditions = [];
        $params = [];

        if ($serviceId === null) {
            $conditions[] = 'service_id IS NULL';
        } else {
            $conditions[] = 'service_id = :service_id';
            $params[':service_id'] = $serviceId;
        }

        if (!$includeHidden) {
            $conditions[] = 'display = 1';
        }

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY step_order ASC, id ASC';

        $statement = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $statement->bindValue($key, $value, PDO::PARAM_INT);
        }

        $statement->execute();

        return $statement->fetchAll() ?: [];
    }

    public function getNextOrder(?int $serviceId = null): int {
        $sql = "SELECT COALESCE(MAX(step_order), 0) + 1 AS next_order FROM {$this->table}";

        if ($serviceId === null) {
            $sql .= ' WHERE service_id IS NULL';
            $statement = $this->db->prepare($sql);
        } else {
            $sql .= ' WHERE service_id = :service_id';
            $statement = $this->db->prepare($sql);
            $statement->bindValue(':service_id', $serviceId, PDO::PARAM_INT);
        }

        $statement->execute();
        $result = $statement->fetch();

        return (int)($result['next_order'] ?? 1);
    }

    public function reorderForService(?int $serviceId, array $orderedIds): void {
        if (empty($orderedIds)) {
            return;
        }

        $this->db->beginTransaction();

        try {
            $position = 1;
            $query = "UPDATE {$this->table} SET step_order = :step_order WHERE id = :id";
            if ($serviceId === null) {
                $query .= ' AND service_id IS NULL';
            } else {
                $query .= ' AND service_id = :service_id';
            }

            $statement = $this->db->prepare($query);

            foreach ($orderedIds as $id) {
                $stepId = (int)$id;
                if ($stepId <= 0) {
                    continue;
                }

                $statement->bindValue(':step_order', $position++, PDO::PARAM_INT);
                $statement->bindValue(':id', $stepId, PDO::PARAM_INT);

                if ($serviceId !== null) {
                    $statement->bindValue(':service_id', $serviceId, PDO::PARAM_INT);
                }

                $statement->execute();
            }

            $this->db->commit();
        } catch (Exception $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function toggleDisplay(int $stepId, bool $display): bool {
        $sql = "UPDATE {$this->table} SET display = :display, updated_at = :updated_at WHERE id = :id";
        $statement = $this->db->prepare($sql);
        $statement->bindValue(':display', $display ? 1 : 0, PDO::PARAM_INT);
        $statement->bindValue(':updated_at', date('Y-m-d H:i:s'));
        $statement->bindValue(':id', $stepId, PDO::PARAM_INT);

        return $statement->execute();
    }
}
