<?php

use App\Livewire\Customer\CustomerIndex;
use App\Livewire\Dashboard\DashboardIndex;
use App\Livewire\HeroSection;
use App\Livewire\Invoice\InvoiceIndex;
use App\Livewire\Pos\PosTerminal;
use App\Livewire\Product\ProductIndex;
use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::get('/login', Login::class)->name('login')->middleware('guest');
Route::post('/logout', function() {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

// Admin Dashboard & Protected Modules
Route::middleware(['auth'])->group(function () {
    Route::get('/', DashboardIndex::class)->name('dashboard');
    Route::get('/pos', PosTerminal::class)->name('pos');
    Route::get('/products', ProductIndex::class)->name('products');
    Route::get('/products/restock', \App\Livewire\Product\RestockPlan::class)->name('products.restock');
    Route::get('/categories', \App\Livewire\Category\CategoryIndex::class)->name('categories');
    Route::get('/customers', CustomerIndex::class)->name('customers');
    Route::get('/users', \App\Livewire\User\UserIndex::class)->name('users');
    Route::get('/commissions', \App\Livewire\Product\ProductCommission::class)->name('commissions');
    Route::get('/reports/commissions', \App\Livewire\Report\CommissionReport::class)->name('reports.commissions');
    Route::get('/invoices', InvoiceIndex::class)->name('invoices');
    Route::get('/invoices/{invoice}', \App\Livewire\Invoice\InvoiceDetail::class)->name('invoices.detail');

    Route::get('/pos/print/{invoice}', function (App\Models\Invoice $invoice) {
        return view('pos.print-invoice', ['invoice' => $invoice]);
    })->name('pos.print');
});

// Marketing / Hero Landing
Route::get('/welcome', HeroSection::class)->name('welcome');
