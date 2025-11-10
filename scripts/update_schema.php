<?php

require_once __DIR__ . '/../config/config.php';

$connection = Database::getInstance()->getConnection();

function tableExists(PDO $connection, string $table): bool
{
    $statement = $connection->prepare(
        "SELECT 1
           FROM information_schema.TABLES
          WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = :table
          LIMIT 1"
    );

    $statement->execute([':table' => $table]);

    return (bool) $statement->fetch(PDO::FETCH_ASSOC);
}

function columnExists(PDO $connection, string $table, string $column): bool
{
    $tableEscaped = str_replace('`', '``', $table);
    $columnQuoted = $connection->quote($column);
    $sql = "SHOW COLUMNS FROM `{$tableEscaped}` LIKE {$columnQuoted}";
    $statement = $connection->query($sql);
    return $statement ? (bool) $statement->fetch(PDO::FETCH_ASSOC) : false;
}

function addColumn(PDO $connection, string $table, string $definition): void
{
    $tableEscaped = str_replace('`', '``', $table);
    $sql = "ALTER TABLE `{$tableEscaped}` ADD COLUMN {$definition}";
    $connection->exec($sql);
}

function indexExists(PDO $connection, string $table, string $indexName): bool
{
    $statement = $connection->prepare(
        "SELECT 1
           FROM information_schema.STATISTICS
          WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = :table
            AND INDEX_NAME = :index
          LIMIT 1"
    );

    $statement->execute([
        ':table' => $table,
        ':index' => $indexName,
    ]);

    return (bool) $statement->fetch(PDO::FETCH_ASSOC);
}

function foreignKeyExists(PDO $connection, string $table, string $constraintName): bool
{
    $statement = $connection->prepare(
        "SELECT CONSTRAINT_NAME
           FROM information_schema.TABLE_CONSTRAINTS
          WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = :table
            AND CONSTRAINT_NAME = :constraint
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'"
    );

    $statement->execute([
        ':table' => $table,
        ':constraint' => $constraintName,
    ]);

    return (bool) $statement->fetch(PDO::FETCH_ASSOC);
}

function ensurePricingPlans(PDO $connection): void
{
    $table = 'pricing_plans';

    if (!tableExists($connection, $table)) {
        $connection->exec(
            "CREATE TABLE `{$table}` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `title` VARCHAR(255) NOT NULL,
                `subtitle` VARCHAR(255) NULL,
                `price_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `price_period` VARCHAR(50) NULL,
                `badge_text` VARCHAR(120) NULL,
                `cta_label` VARCHAR(120) NULL,
                `cta_url` VARCHAR(255) NULL DEFAULT '/contact',
                `features` TEXT NULL,
                `highlight` TINYINT(1) NOT NULL DEFAULT 0,
                `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
                `visible` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_pricing_plans_sort` (`sort_order`),
                INDEX `idx_pricing_plans_visibility` (`visible`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        echo "Created {$table} table.\n";

        if (tableExists($connection, 'pricing_packages')) {
            $migrationQuery = $connection->query(
                "SELECT p.id, p.name, p.description, p.price_amount, p.price_period, p.is_featured, p.sort_order, p.status
                   FROM pricing_packages p
                  ORDER BY p.sort_order ASC, p.id ASC"
            );

            if ($migrationQuery) {
                $rows = $migrationQuery->fetchAll(PDO::FETCH_ASSOC);
                if ($rows) {
                    $insert = $connection->prepare(
                        "INSERT INTO `{$table}` (title, subtitle, price_amount, price_period, badge_text, cta_label, cta_url, features, highlight, sort_order, visible, created_at, updated_at)
                         VALUES (:title, :subtitle, :price_amount, :price_period, :badge_text, :cta_label, :cta_url, :features, :highlight, :sort_order, :visible, NOW(), NOW())"
                    );

                    foreach ($rows as $row) {
                        $featuresQuery = $connection->prepare(
                            "SELECT feature_text FROM pricing_package_features WHERE package_id = :package ORDER BY sort_order ASC, id ASC"
                        );
                        $featuresQuery->execute([':package' => $row['id']]);
                        $featuresList = $featuresQuery->fetchAll(PDO::FETCH_COLUMN) ?: [];
                        $featuresText = implode("\n", array_map('trim', $featuresList));

                        $insert->execute([
                            ':title' => trim((string)($row['name'] ?? '')),
                            ':subtitle' => trim((string)($row['description'] ?? '')) ?: null,
                            ':price_amount' => (float)($row['price_amount'] ?? 0),
                            ':price_period' => trim((string)($row['price_period'] ?? '')) ?: null,
                            ':badge_text' => trim((string)($row['badge_text'] ?? '')) ?: null,
                            ':cta_label' => trim((string)($row['cta_text'] ?? '')) ?: null,
                            ':cta_url' => trim((string)($row['cta_url'] ?? '')) ?: '/contact',
                            ':features' => $featuresText !== '' ? $featuresText : null,
                            ':highlight' => !empty($row['is_featured']) ? 1 : 0,
                            ':sort_order' => (int)($row['sort_order'] ?? 0),
                            ':visible' => (isset($row['status']) && strtolower((string)$row['status']) === 'draft') ? 0 : 1,
                        ]);
                    }

                    echo "Migrated existing pricing packages into {$table}.\n";
                }
            }
        }
    }
}

function ensureDesignProcessSteps(PDO $connection): void
{
    $table = 'design_process_steps';
    $legacyTable = 'service_process_steps';

    if (!tableExists($connection, $table)) {
        if (tableExists($connection, $legacyTable)) {
            $connection->exec("RENAME TABLE `{$legacyTable}` TO `{$table}`");
            echo "Renamed {$legacyTable} to {$table}.\n";
        } else {
            $connection->exec(
                "CREATE TABLE `{$table}` (
                    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `service_id` INT NULL,
                    `step_order` INT UNSIGNED NOT NULL DEFAULT 0,
                    `icon_class` VARCHAR(100) NULL,
                    `title` VARCHAR(255) NOT NULL,
                    `description` TEXT NULL,
                    `display` TINYINT(1) NOT NULL DEFAULT 1,
                    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    INDEX `idx_design_process_service` (`service_id`),
                    INDEX `idx_design_process_order` (`service_id`, `step_order`),
                    CONSTRAINT `fk_design_process_service`
                        FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );

            echo "Created {$table} table.\n";
            return;
        }
    }

    if (!columnExists($connection, $table, 'service_id')) {
        addColumn($connection, $table, "`service_id` INT NULL AFTER `id`");
        echo "Added {$table}.service_id column.\n";
    }
    $connection->exec("ALTER TABLE `{$table}` MODIFY COLUMN `service_id` INT NULL");

    if (columnExists($connection, $table, 'sort_order')) {
        $connection->exec("ALTER TABLE `{$table}` CHANGE COLUMN `sort_order` `step_order` INT UNSIGNED NOT NULL DEFAULT 0");
        echo "Renamed {$table}.sort_order to step_order.\n";
    } elseif (!columnExists($connection, $table, 'step_order')) {
        addColumn($connection, $table, "`step_order` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `service_id`");
        echo "Added {$table}.step_order column.\n";
    } else {
        $connection->exec("ALTER TABLE `{$table}` MODIFY COLUMN `step_order` INT UNSIGNED NOT NULL DEFAULT 0");
    }

    if (columnExists($connection, $table, 'icon') && !columnExists($connection, $table, 'icon_class')) {
        $connection->exec("ALTER TABLE `{$table}` CHANGE COLUMN `icon` `icon_class` VARCHAR(100) NULL");
        echo "Renamed {$table}.icon to icon_class.\n";
    } elseif (!columnExists($connection, $table, 'icon_class')) {
        addColumn($connection, $table, "`icon_class` VARCHAR(100) NULL AFTER `step_order`");
        echo "Added {$table}.icon_class column.\n";
    } else {
        $connection->exec("ALTER TABLE `{$table}` MODIFY COLUMN `icon_class` VARCHAR(100) NULL");
    }

    if (columnExists($connection, $table, 'title')) {
        $connection->exec("ALTER TABLE `{$table}` MODIFY COLUMN `title` VARCHAR(255) NOT NULL");
    }

    if (!columnExists($connection, $table, 'display')) {
        addColumn($connection, $table, "`display` TINYINT(1) NOT NULL DEFAULT 1 AFTER `description`");
        echo "Added {$table}.display column.\n";

        if (columnExists($connection, $table, 'status')) {
            $connection->exec(
                "UPDATE `{$table}`
                    SET `display` = CASE
                        WHEN LOWER(COALESCE(`status`, 'published')) IN ('draft', 'hidden', '0', 'false') THEN 0
                        ELSE 1
                    END"
            );
            $connection->exec("ALTER TABLE `{$table}` DROP COLUMN `status`");
            echo "Dropped legacy {$table}.status column.\n";
        }
    } else {
        $connection->exec("ALTER TABLE `{$table}` MODIFY COLUMN `display` TINYINT(1) NOT NULL DEFAULT 1");
    }

    if (!columnExists($connection, $table, 'created_at')) {
        addColumn($connection, $table, "`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
        echo "Added {$table}.created_at column.\n";
    }

    if (!columnExists($connection, $table, 'updated_at')) {
        addColumn($connection, $table, "`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        echo "Added {$table}.updated_at column.\n";
    }

    if (!indexExists($connection, $table, 'idx_design_process_service')) {
        $connection->exec("ALTER TABLE `{$table}` ADD INDEX `idx_design_process_service` (`service_id`)");
        echo "Added idx_design_process_service index.\n";
    }

    if (!indexExists($connection, $table, 'idx_design_process_order')) {
        $connection->exec("ALTER TABLE `{$table}` ADD INDEX `idx_design_process_order` (`service_id`, `step_order`)");
        echo "Added idx_design_process_order index.\n";
    }

    if (!foreignKeyExists($connection, $table, 'fk_design_process_service')) {
        $connection->exec(
            "ALTER TABLE `{$table}`
                ADD CONSTRAINT `fk_design_process_service`
                FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE SET NULL"
        );
        echo "Added fk_design_process_service foreign key.\n";
    }
}

try {
    ensurePricingPlans($connection);
    ensureDesignProcessSteps($connection);

    if (!columnExists($connection, 'service_features', 'icon_class')) {
        addColumn($connection, 'service_features', "`icon_class` VARCHAR(120) NULL AFTER `feature_text`");
        echo "Added service_features.icon_class column.\n";
    }

    if (!columnExists($connection, 'service_features', 'display')) {
        addColumn($connection, 'service_features', "`display` TINYINT(1) NOT NULL DEFAULT 1 AFTER `sort_order`");
        echo "Added service_features.display column.\n";
    }

    echo "Database schema updated successfully.\n";
} catch (Throwable $throwable) {
    fwrite(STDERR, 'Schema update failed: ' . $throwable->getMessage() . "\n");
    exit(1);
}
