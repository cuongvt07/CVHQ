<?php

namespace App\Livewire\Report;

use App\Models\User;
use App\Models\Invoice;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasPermissions;
use App\Traits\WithColumnVisibility;
use App\Traits\WithUserPreferences;

class CommissionReport extends Component
{
    use WithPagination, HasPermissions, WithColumnVisibility, WithUserPreferences;

    protected function getModuleKey(): string
    {
        return 'reports';
    }

    public $view = 'summary'; // summary, employee_detail, invoice_detail
    public $selectedUserId = null;
    public $selectedInvoiceId = null;
    public $dateRange = 'this_month';

    protected function getDefaultVisibleColumns(): array
    {
        return ['employee', 'orders', 'sales', 'commission', 'actions'];
    }

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
        $range = $this->getDateRange();

        if ($this->view === 'summary') {
            $data['employees'] = User::withCount(['invoices as total_invoices' => function($query) use ($range) {
                    $query->where('status', '!=', 'Cancelled')
                          ->whereBetween('created_at', [$range['start'], $range['end']]);
                }])
                ->withSum(['invoices as total_commission' => function($query) use ($range) {
                    $query->where('status', '!=', 'Cancelled')
                          ->whereBetween('created_at', [$range['start'], $range['end']]);
                }], 'total_commission')
                ->withSum(['invoices as total_sales' => function($query) use ($range) {
                    $query->where('status', '!=', 'Cancelled')
                          ->whereBetween('created_at', [$range['start'], $range['end']]);
                }], 'final_amount')
                ->orderBy('total_commission', 'desc')
                ->paginate(15);
        } elseif ($this->view === 'employee_detail') {
            $data['employee'] = User::findOrFail($this->selectedUserId);
            $data['invoices'] = Invoice::where('user_id', $this->selectedUserId)
                ->where('status', '!=', 'Cancelled')
                ->whereBetween('created_at', [$range['start'], $range['end']])
                ->latest()
                ->paginate(15);
        } elseif ($this->view === 'invoice_detail') {
            $data['invoice'] = Invoice::with(['items', 'customer', 'user'])->findOrFail($this->selectedInvoiceId);
        }

        return view('livewire.report.commission-report', $data)->layout('layouts.app');
    }

    private function getDateRange()
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        switch ($this->dateRange) {
            case 'today':
                $start = now()->startOfDay();
                $end = now()->endOfDay();
                break;
            case 'this_week':
                $start = now()->startOfWeek();
                $end = now()->endOfWeek();
                break;
            case 'this_month':
                $start = now()->startOfMonth();
                $end = now()->endOfMonth();
                break;
            case 'last_month':
                $start = now()->subMonth()->startOfMonth();
                $end = now()->subMonth()->endOfMonth();
                break;
        }

        return ['start' => $start, 'end' => $end];
    }
}
