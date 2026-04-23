<?php

namespace App\Livewire\Invoice;

use App\Models\Invoice;
use Livewire\Component;

class InvoiceDetail extends Component
{
    public Invoice $invoice;

    public function mount(Invoice $invoice)
    {
        $this->invoice = $invoice->load(['customer', 'items']);
    }

    public function render()
    {
        return view('livewire.invoice.invoice-detail')
            ->layout('layouts.app');
    }
}
