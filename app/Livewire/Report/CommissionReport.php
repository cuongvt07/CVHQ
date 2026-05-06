<?php

namespace App\Livewire\Report;

use App\Models\User;
use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasPermissions;

class CommissionReport extends Component
{
    use WithPagination, HasPermissions;

    protected function getModuleKey(): string
    {
        return 'reports';
    }

    public $view = 'summary'; // summary, employee_detail, invoice_detail
    public $selectedUserId = null;
    public $selectedInvoiceId = null;
    public $dateRange = 'this_month';

    public function selectEmployee($userId)
    {
        $this->selectedUserId = $userId;
        $this->view = 'employee_detail';
        $this->resetPage();
    }

    public function selectInvoice($invoiceId)
    {
        $this->selectedInvoiceId = $invoiceId;
        $this->view = 'invoice_detail';
    }

    public function backToSummary()
    {
        $this->view = 'summary';
        $this->selectedUserId = null;
        $this->selectedInvoiceId = null;
    }

    public function backToEmployee()
    {
        $this->view = 'employee_detail';
        $this->selectedInvoiceId = null;
    }

    public function render()
    {
        $data = [];

        if ($this->view === 'summary') {
            $data['employees'] = User::withCount(['invoices as total_invoices'])
                ->withSum('invoices as total_commission', 'total_commission')
                ->withSum('invoices as total_sales', 'final_amount')
                ->orderBy('total_commission', 'desc')
                ->paginate(15);
        } elseif ($this->view === 'employee_detail') {
            $data['employee'] = User::findOrFail($this->selectedUserId);
            $data['invoices'] = Invoice::where('user_id', $this->selectedUserId)
                ->latest()
                ->paginate(15);
        } elseif ($this->view === 'invoice_detail') {
            $data['invoice'] = Invoice::with(['items', 'customer', 'user'])->findOrFail($this->selectedInvoiceId);
        }

        return view('livewire.report.commission-report', $data)->layout('layouts.app');
    }
}
