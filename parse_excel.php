<?php

$zip = new ZipArchive();
$excelPath = '20.7. 20h03.  PHƯƠNG ÁN SẮP XẾP (PA3).xlsx';

if ($zip->open($excelPath) === TRUE) {
    // Shared strings
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

    // Sheet1
    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    if ($sheetXml) {
        $xml = simplexml_load_string($sheetXml);
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
            }
        }
    }
    $zip->close();
} else {
    echo "Failed to open zip\n";
}
