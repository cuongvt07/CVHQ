<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'DanhSachKhachHang_KV21042026-230744-868.xlsx';

if (!file_exists($filePath)) {
    echo "File not found: $filePath\n";
    exit;
}

try {
    $spreadsheet = IOFactory::load($filePath);
    $sheetNames = $spreadsheet->getSheetNames();
    echo "SHEETS DETECTED: " . implode(', ', $sheetNames) . "\n";
    
    foreach ($sheetNames as $name) {
        $sheet = $spreadsheet->getSheetByName($name);
        $highestRow = $sheet->getHighestRow();
        echo "Sheet '$name' has $highestRow rows.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
