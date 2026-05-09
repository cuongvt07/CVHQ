<?php

namespace App\Livewire\Invoice;

use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;

class ReturnIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $startDate = '';
    public $endDate = '';
    public $sellerFilter = '';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'sellerFilter' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function clearFilter($type)
    {
        if ($type === 'all') {
            $this->search = '';
            $this->startDate = '';
            $this->endDate = '';
            $this->sellerFilter = '';
        } else {
            $this->$type = '';
        }
        $this->resetPage();
    }

    public function getReturnedInvoices()
    {
        return Invoice::query()
            ->where('status', 'Returned')
            ->when($this->search, fn($q) => $q->where('invoice_code', 'like', "%{$this->search}%"))
            ->when($this->startDate, fn($q) => $q->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn($q) => $q->whereDate('created_at', '<=', $this->endDate))
            ->when($this->sellerFilter, fn($q) => $q->where('seller_name', 'like', "%{$this->sellerFilter}%"))
            ->with(['customer', 'items.product'])
            ->latest()
            ->paginate($this->perPage)
            ->onEachSide(1);
    }

    public function render()
    {
        return view('livewire.invoice.return-index', [
            'invoices' => $this->getReturnedInvoices(),
        ])->layout('layouts.app');
    }
}
