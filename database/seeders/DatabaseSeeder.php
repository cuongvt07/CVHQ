<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceShipping;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Sample Products
        Product::create([
            'sku' => 'QT-001',
            'barcode' => '888777666001',
            'name' => 'Meta Quest 3S',
            'product_type' => 'Hardware',
            'category_path' => 'VR Headsets',
            'brand' => 'Meta',
            'cost_price' => 200.00,
            'sale_price' => 299.99,
            'stock_quantity' => 124,
            'images' => ['https://images.unsplash.com/photo-1622979135225-d2ba269cf1ac?q=80&w=400&auto=format&fit=crop'],
            'is_active' => true,
        ]);

        Product::create([
            'sku' => 'RB-002',
            'barcode' => '888777666002',
            'name' => 'Ray-Ban Meta Wayfarer',
            'product_type' => 'Hardware',
            'category_path' => 'Smart Glasses',
            'brand' => 'Ray-Ban',
            'cost_price' => 180.00,
            'sale_price' => 329.00,
            'stock_quantity' => 45,
            'images' => ['https://images.unsplash.com/photo-1572635196237-14b3f281503f?q=80&w=400&auto=format&fit=crop'],
            'is_active' => true,
        ]);

        Product::create([
            'sku' => 'AC-003',
            'barcode' => '888777666003',
            'name' => 'Elite Strap with Battery',
            'product_type' => 'Accessory',
            'category_path' => 'Accessories',
            'brand' => 'Meta',
            'cost_price' => 60.00,
            'sale_price' => 129.99,
            'stock_quantity' => 8,
            'images' => ['https://images.unsplash.com/photo-1593508512255-86ab42a8e620?q=80&w=400&auto=format&fit=crop'],
            'is_active' => true,
        ]);

        Product::create([
            'sku' => 'QT-004',
            'barcode' => '888777666004',
            'name' => 'Meta Quest Pro',
            'product_type' => 'Hardware',
            'category_path' => 'VR Headsets',
            'brand' => 'Meta',
            'cost_price' => 700.00,
            'sale_price' => 999.99,
            'stock_quantity' => 12,
            'images' => ['https://images.unsplash.com/photo-1617806118233-18e1db207fa6?q=80&w=400&auto=format&fit=crop'],
            'is_active' => false,
        ]);

        // Sample Customers
        $customer1 = Customer::create([
            'customer_code' => 'KH001',
            'full_name' => 'John Wick',
            'phone' => '0901234567',
            'customer_group' => 'VIP',
            'current_debt' => 0.0,
            'total_spent' => 15000.0,
            'status' => 'Active'
        ]);

        $customer2 = Customer::create([
            'customer_code' => 'KH002',
            'full_name' => 'Tony Stark',
            'phone' => '0987654321',
            'customer_group' => 'Wholesale',
            'current_debt' => 2500.0,
            'total_spent' => 45000.0,
            'status' => 'Active'
        ]);

        // Sample Invoices
        $invoice1 = Invoice::create([
            'invoice_code' => 'HD0001',
            'customer_id' => $customer1->id,
            'seller_name' => 'Admin',
            'sales_channel' => 'POS',
            'total_amount' => 628.99,
            'final_amount' => 628.99,
            'paid_amount' => 628.99,
            'cash_amount' => 628.99,
            'status' => 'Completed'
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice1->id,
            'product_name' => 'Meta Quest 3S',
            'sku' => 'QT-001',
            'quantity' => 1,
            'unit_price' => 299.99,
            'final_price' => 299.99
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice1->id,
            'product_name' => 'Ray-Ban Meta Wayfarer',
            'sku' => 'RB-002',
            'quantity' => 1,
            'unit_price' => 329.00,
            'final_price' => 329.00
        ]);
    }
}
