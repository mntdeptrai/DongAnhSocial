<?php

try {
    $pdo = new PDO('mysql:host=127.0.0.1;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $dbs = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
    echo "=== DATABASES FOUND ===\n";
    foreach ($dbs as $db) {
        echo "- $db\n";
    }

    echo "\n=== TABLES PER DATABASE ===\n";
    $targetDbs = ['food_map', 'stay_in_dong_anh', 'wellness_care', 'smart_education_map', 'dong_anh_market', 'dong_anh', 'dong_anh_culture_hub'];
    foreach ($targetDbs as $dbName) {
        if (in_array($dbName, $dbs)) {
            echo "\n--- DB: $dbName ---\n";
            $tables = $pdo->query("SHOW TABLES FROM `$dbName`")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($tables as $t) {
                $count = $pdo->query("SELECT COUNT(*) FROM `$dbName`.`$t`")->fetchColumn();
                echo "  * $t ($count rows)\n";
            }
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
