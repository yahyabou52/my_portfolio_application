<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';

class Faq extends BaseModel
{
    protected $table = 'faqs';
    protected $fillable = [
        'question',
        'answer',
        'sort_order',
        'visible'
    ];

    public function allVisible(): array
    {
        $statement = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE visible = 1 ORDER BY sort_order ASC, id ASC"
        );
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function allForManager(): array
    {
        $statement = $this->db->prepare(
            "SELECT * FROM {$this->table} ORDER BY sort_order ASC, id ASC"
        );
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function reorder(array $orderedIds): array
    {
        $statement = $this->db->prepare(
            "UPDATE {$this->table} SET sort_order = :position, updated_at = NOW() WHERE {$this->primaryKey} = :id"
        );

        $this->db->beginTransaction();

        try {
            foreach ($orderedIds as $position => $id) {
                $statement->execute([
                    ':position' => $position,
                    ':id' => $id
                ]);
            }

            $this->db->commit();
        } catch (Throwable $throwable) {
            $this->db->rollBack();
            throw $throwable;
        }

        return $this->allForManager();
    }

    public function toggleVisibility(int $id, bool $visible): void
    {
        $statement = $this->db->prepare(
            "UPDATE {$this->table} SET visible = :visible, updated_at = NOW() WHERE {$this->primaryKey} = :id"
        );

        $statement->execute([
            ':visible' => $visible ? 1 : 0,
            ':id' => $id
        ]);
    }
}
