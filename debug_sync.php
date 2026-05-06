<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

DB::transaction(function () {
    $products = Product::all()->mapWithKeys(function ($item) {
        return [strtoupper(trim((string)$item->sku)) => $item];
    });
    
    echo "Total Products: " . $products->count() . "\n";
    
    $invoices = Invoice::with('items')->get();
    echo "Total Invoices: " . $invoices->count() . "\n";
    
    foreach ($invoices as $invoice) {
        echo "Processing Invoice: {$invoice->invoice_code} (Status: {$invoice->status})\n";
        foreach ($invoice->items as $item) {
            $sku = strtoupper(trim((string)$item->sku));
            $product = $products[$sku] ?? null;
            
            if ($product) {
                echo "  Match SKU [{$sku}]: Product Rate={$product->commission_amount}, Item Rate={$item->commission_amount}\n";
            } else {
                echo "  No Match for SKU [{$sku}]\n";
            }
        }
    }
});
