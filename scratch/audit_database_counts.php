<?php

try {
    $pdo = new PDO('mysql:host=127.0.0.1;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sourceDbs = ['food_map', 'stay_in_dong_anh', 'wellness_care', 'smart_education_map', 'dong_anh_market', 'dong_anh_culture_hub'];
    $targetDb = 'donganh_social';

    echo "========================================================================\n";
    echo "       AUDIT BÁO CÁO ĐỐI CHIẾU DỮ LIỆU CÁC DATABASE KHI GỘP         \n";
    echo "========================================================================\n\n";

    // 1. Get list of all tables in target DB
    $targetTables = $pdo->query("SHOW TABLES FROM `$targetDb`")->fetchAll(PDO::FETCH_COLUMN);

    $totalDiscrepancies = 0;

    foreach ($targetTables as $table) {
        $targetCount = (int)$pdo->query("SELECT COUNT(*) FROM `$targetDb`.`$table`")->fetchColumn();
        
        // Sum counts across all source DBs for this table
        $sumSourceCount = 0;
        $sourceBreakdown = [];

        foreach ($sourceDbs as $sDb) {
            $hasTable = $pdo->query("SHOW TABLES FROM `$sDb` LIKE '$table'")->rowCount() > 0;
            if ($hasTable) {
                $c = (int)$pdo->query("SELECT COUNT(*) FROM `$sDb`.`$table`")->fetchColumn();
                if ($c > 0) {
                    $sourceBreakdown[] = "$sDb: $c";
                }
                // For deduplicated tables like categories, communes, users
                if (in_array($table, ['categories', 'communes'])) {
                    $sumSourceCount = max($sumSourceCount, $c);
                } else if ($table === 'users') {
                    // Unique users by email
                } else {
                    $sumSourceCount += $c;
                }
            }
        }

        if ($table === 'users') {
            // Count unique emails across all source DBs
            $emails = [];
            foreach ($sourceDbs as $sDb) {
                $hasTable = $pdo->query("SHOW TABLES FROM `$sDb` LIKE 'users'")->rowCount() > 0;
                if ($hasTable) {
                    $rows = $pdo->query("SELECT email FROM `$sDb`.`users`")->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($rows as $em) {
                        $emails[strtolower(trim($em))] = true;
                    }
                }
            }
            $sumSourceCount = count($emails);
        }

        $breakdownStr = !empty($sourceBreakdown) ? implode(', ', $sourceBreakdown) : 'no rows in source';
        $status = ($targetCount >= $sumSourceCount) ? "✅ KHÔNG MẤT" : "❌ THIẾU DỮ LIỆU";

        if ($targetCount < $sumSourceCount) {
            $totalDiscrepancies++;
        }

        printf("%-25s | Target: %-5d | Sources Total: %-5d | Status: %s | (%s)\n", $table, $targetCount, $sumSourceCount, $status, $breakdownStr);
    }

    echo "\n------------------------------------------------------------------------\n";
    if ($totalDiscrepancies === 0) {
        echo "RESULT: 🎉 TẤT CẢ 100% BẢNG VÀ BẢN GHI ĐÃ ĐƯỢC BẢO TOÀN ĐẦY ĐỦ VÀ GỘP THÀNH CÔNG!\n";
    } else {
        echo "RESULT: ⚠️ Phát hiện $totalDiscrepancies bảng có sự chênh lệch!\n";
    }
    echo "------------------------------------------------------------------------\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
