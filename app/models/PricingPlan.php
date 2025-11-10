<?php

class PricingPlan extends BaseModel
{
    protected $table = 'pricing_plans';
    protected $fillable = [
        'title',
        'subtitle',
        'price_amount',
        'price_period',
        'badge_text',
        'cta_label',
        'cta_url',
        'features',
        'highlight',
        'sort_order',
        'visible',
    ];

    public function allVisible(): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE visible = 1 ORDER BY highlight DESC, sort_order ASC, id ASC"
        );
        $stmt->execute();

        return $this->mapFeatures($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function allForManager(): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} ORDER BY sort_order ASC, id ASC"
        );
        $stmt->execute();

        return $this->mapFeatures($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function updateHighlight(int $id): array
    {
        $this->db->beginTransaction();

        try {
            $clear = $this->db->prepare("UPDATE {$this->table} SET highlight = 0, updated_at = NOW() WHERE highlight = 1");
            $clear->execute();

            $mark = $this->db->prepare(
                "UPDATE {$this->table} SET highlight = 1, visible = 1, updated_at = NOW() WHERE {$this->primaryKey} = :id"
            );
            $mark->execute([':id' => $id]);

            $this->db->commit();
        } catch (Throwable $throwable) {
            $this->db->rollBack();
            throw $throwable;
        }

        return $this->allForManager();
    }

    public function clearHighlight(int $id): array
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET highlight = 0, updated_at = NOW() WHERE {$this->primaryKey} = :id"
        );

        $stmt->execute([':id' => $id]);

        return $this->allForManager();
    }

    public function reorder(array $order): array
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET sort_order = :position, updated_at = NOW() WHERE {$this->primaryKey} = :id"
        );

        $this->db->beginTransaction();

        try {
            foreach ($order as $position => $id) {
                $stmt->execute([
                    ':position' => $position,
                    ':id' => $id,
                ]);
            }

            $this->db->commit();
        } catch (Throwable $throwable) {
            $this->db->rollBack();
            throw $throwable;
        }

        return $this->allForManager();
    }

    public function toggleVisibility(int $id, bool $visible): array
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET visible = :visible, updated_at = NOW() WHERE {$this->primaryKey} = :id"
        );
        $stmt->execute([
            ':visible' => $visible ? 1 : 0,
            ':id' => $id,
        ]);

        return $this->allForManager();
    }

    public function findWithFeatures(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->mapFeatures([$row])[0];
    }

    private function mapFeatures(array $plans): array
    {
        foreach ($plans as &$plan) {
            $plan['features_list'] = $this->featuresToArray($plan['features'] ?? '');
        }

        return $plans;
    }

    private function featuresToArray(?string $features): array
    {
        if ($features === null || trim($features) === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $features);
        $lines = array_map(fn ($line) => trim((string) $line), $lines);
        $lines = array_filter($lines, fn ($line) => $line !== '');

        return array_values($lines);
    }
}
