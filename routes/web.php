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
    Route::get('/products/stock-checks', \App\Livewire\Product\StockCheckIndex::class)->name('products.stock-checks');
    Route::get('/products/transfers', \App\Livewire\Product\StockTransferIndex::class)->name('products.transfers');
    Route::get('/products/transfers/print/{transfer}', function (App\Models\StockTransfer $transfer) {
        return view('pos.print-transfer', ['transfer' => $transfer]);
    })->name('products.transfer.print');
    Route::get('/categories', \App\Livewire\Category\CategoryIndex::class)->name('categories');
    Route::get('/customers', CustomerIndex::class)->name('customers');
    Route::get('/users', \App\Livewire\User\UserIndex::class)->name('users');
    Route::get('/commissions', \App\Livewire\Product\ProductCommission::class)->name('commissions');
    Route::get('/commissions/settings', \App\Livewire\System\CommissionSettings::class)->name('commissions.settings');
    Route::get('/reports/commissions', \App\Livewire\Report\CommissionReport::class)->name('reports.commissions');
    Route::get('/reports/sales', \App\Livewire\Report\SalesReport::class)->name('reports.sales');
    Route::get('/reports/sales/day/{date}', \App\Livewire\Report\SalesDayDetail::class)->name('reports.sales.day');
    Route::get('/invoices', InvoiceIndex::class)->name('invoices');
    Route::get('/wp-orders', \App\Livewire\Wp\WpOrderIndex::class)->name('wp.orders');
    Route::get('/invoices/returns', \App\Livewire\Invoice\ReturnIndex::class)->name('invoices.returns');
    Route::get('/invoices/{invoice}', \App\Livewire\Invoice\InvoiceDetail::class)->name('invoices.detail');
    Route::get('/system/logs', \App\Livewire\System\ActivityLogList::class)->name('system.logs');
    Route::get('/system/settings', \App\Livewire\System\SystemSettings::class)->name('system.settings');
    Route::get('/branches', \App\Livewire\System\BranchIndex::class)->name('branches');

    Route::get('/pos/print/{invoice}', function (App\Models\Invoice $invoice) {
        if (!auth()->user()->hasPermission('pos') && !auth()->user()->hasPermission('invoices')) {
            abort(403, 'Bạn không có quyền in hóa đơn này!');
        }
        return view('pos.print-invoice', ['invoice' => $invoice]);
    })->name('pos.print');
});

// Marketing / Hero Landing
Route::get('/welcome', HeroSection::class)->name('welcome');
