<?php

use App\Livewire\Customer\CustomerIndex;
use App\Livewire\Dashboard\DashboardIndex;
use App\Livewire\HeroSection;
use App\Livewire\Invoice\InvoiceIndex;
use App\Livewire\Pos\PosTerminal;
use App\Livewire\Product\ProductIndex;
use Illuminate\Support\Facades\Route;

// Admin Dashboard
Route::get('/', DashboardIndex::class)->name('dashboard');

// Management Modules
Route::get('/pos', PosTerminal::class)->name('pos');
Route::get('/products', ProductIndex::class)->name('products');
Route::get('/categories', \App\Livewire\Category\CategoryIndex::class)->name('categories');
Route::get('/customers', CustomerIndex::class)->name('customers');
Route::get('/invoices', InvoiceIndex::class)->name('invoices');
Route::get('/invoices/{invoice}', \App\Livewire\Invoice\InvoiceDetail::class)->name('invoices.detail');

Route::get('/pos/print/{invoice}', function (App\Models\Invoice $invoice) {
    return view('pos.print-invoice', ['invoice' => $invoice]);
})->name('pos.print');

// Marketing / Hero Landing
Route::get('/welcome', HeroSection::class)->name('welcome');
