<?php

use App\Imports\InvoicesImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$file = 'd:/CVHQ/DanhSachChiTietHoaDon_KV21042026-230833-014.xlsx';

echo "Starting import test...\n";

try {
    // We want to run it synchronously for the test
    // Maatwebsite\Excel usually respects the queue config. 
    // We can force it by not using ShouldQueue in a temporary class or just mock it.
    // Actually, let's just run it and see.
    
    $import = new InvoicesImport();
    $import->setImportKey('test_import_' . time());
    
    Excel::import($import, $file);
    
    echo "Import command finished.\n";
    
    // Check results
    $count = Invoice::count();
    echo "Total invoices in DB: $count\n";
    
    $latest = Invoice::with('items')->latest()->first();
    if ($latest) {
        echo "Latest Invoice: {$latest->invoice_code}\n";
        echo "Items count: " . $latest->items->count() . "\n";
        echo "Total amount: {$latest->total_amount}\n";
        echo "Created at: {$latest->created_at}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
