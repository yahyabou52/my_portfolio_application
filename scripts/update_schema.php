<?php

require_once __DIR__ . '/../config/config.php';

$connection = Database::getInstance()->getConnection();

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

try {
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
