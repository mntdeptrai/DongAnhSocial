<?php

$zip = new ZipArchive();
$excelPath = 'Copy of 28. Danh sach ôcp xã Đông Anh.xlsx';

if ($zip->open($excelPath) === TRUE) {
    $sharedStrings = [];
    $ssXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($ssXml) {
        $xml = simplexml_load_string($ssXml);
        foreach ($xml->si as $si) {
            if (isset($si->t)) {
                $sharedStrings[] = (string)$si->t;
            } else {
                $text = '';
                foreach ($si->r as $r) {
                    $text .= (string)$r->t;
                }
                $sharedStrings[] = $text;
            }
        }
    }

    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    if ($sheetXml) {
        $xml = simplexml_load_string($sheetXml);
        $count = 0;
        foreach ($xml->sheetData->row as $row) {
            $rNum = (int)$row['r'];
            $cells = [];
            foreach ($row->c as $c) {
                $cellRef = (string)$c['r'];
                $val = (string)$c->v;
                if ((string)$c['t'] === 's' && isset($sharedStrings[(int)$val])) {
                    $val = $sharedStrings[(int)$val];
                }
                if (trim($val) !== '') {
                    $cells[] = "$cellRef: $val";
                }
            }
            if (!empty($cells)) {
                echo "Row $rNum | " . implode(' | ', $cells) . PHP_EOL;
                if (++$count > 40) break;
            }
        }
    }
    $zip->close();
} else {
    echo "Failed to open $excelPath\n";
}
