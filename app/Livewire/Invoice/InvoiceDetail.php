<?php

namespace App\Livewire\Invoice;

use App\Models\Invoice;
use Livewire\Component;
use App\Traits\HasPermissions;

class InvoiceDetail extends Component
{
    use HasPermissions;

    protected function getModuleKey(): string
    {
        return 'invoices';
    }

    public Invoice $invoice;

    public function mount(Invoice $invoice)
    {
        $this->invoice = $invoice->load(['customer', 'items', 'sharedTo']);
    }

    public function render()
    {
        return view('livewire.invoice.invoice-detail', [
            // Đơn Mail (WooCommerce) gốc đã lập ra hóa đơn này (nếu có) — liên kết 2 chiều.
            'wpOrder' => \App\Models\WpOrder::where('local_invoice_id', $this->invoice->id)->first(),
        ])->layout('layouts.app');
    }
}
