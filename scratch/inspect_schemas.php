<?php

try {
    $pdo = new PDO('mysql:host=127.0.0.1;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $dbs = ['food_map', 'stay_in_dong_anh', 'wellness_care', 'smart_education_map', 'dong_anh_market', 'dong_anh_culture_hub'];

    foreach ($dbs as $db) {
        echo "=== DB: $db ===\n";
        $stmt = $pdo->query("SHOW COLUMNS FROM `$db`.`eateries`");
        $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "eateries columns: " . implode(', ', $cols) . "\n\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
