<?php

require __DIR__ . '/../vendor/autoload.php';

use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\HeadingRowImport;

// Need to bootstrap Laravel to use the Excel facade or just use the reader directly
// For simplicity, let's just use PhpSpreadsheet if possible, but Maatwebsite is easier if bootstrapped.
// Actually, I can use a simpler approach with PHP.

$file = 'd:/CVHQ/DanhSachChiTietHoaDon_KV21042026-230833-014.xlsx';

if (!file_exists($file)) {
    echo "File not found: $file\n";
    exit(1);
}

// Since I can't easily bootstrap Laravel here, I'll use PhpOffice\PhpSpreadsheet directly
// which is a dependency of maatwebsite/excel.

use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $spreadsheet = IOFactory::load($file);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();
    
    if (count($rows) > 0) {
        echo "Headings found:\n";
        print_r($rows[0]);
        
        echo "\nData rows (codes):\n";
        $codes = [];
        for ($i = 1; $i < count($rows); $i++) {
            $codes[] = $rows[$i][1]; // Index 1 is Mã hóa đơn
        }
        print_r(array_unique($codes));
    } else {
        echo "No rows found.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
