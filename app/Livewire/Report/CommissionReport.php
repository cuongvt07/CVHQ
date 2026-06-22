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
    public $customStart = null; // dùng khi dateRange = 'custom' (Y-m-d)
    public $customEnd = null;

    public function updatedDateRange(): void
    {
        // Mặc định ngày cho khoảng tùy chỉnh = đầu/cuối tháng hiện tại
        if ($this->dateRange === 'custom') {
            $this->customStart = $this->customStart ?: now()->startOfMonth()->toDateString();
            $this->customEnd = $this->customEnd ?: now()->toDateString();
        }
        $this->resetPage();
    }

    public function updatedCustomStart(): void { $this->resetPage(); }
    public function updatedCustomEnd(): void { $this->resetPage(); }

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
            $employees = User::withCount(['invoices as total_invoices' => function($query) use ($range) {
                    $query->where('status', '!=', 'Cancelled')
                          ->whereBetween('created_at', [$range['start'], $range['end']]);
                }])
                ->withSum(['invoices as gross_commission' => function($query) use ($range) {
                    $query->where('status', '!=', 'Cancelled')
                          ->whereBetween('created_at', [$range['start'], $range['end']]);
                }], 'total_commission')
                ->withSum(['invoices as shared_out' => function($query) use ($range) {
                    $query->where('status', '!=', 'Cancelled')
                          ->whereBetween('created_at', [$range['start'], $range['end']]);
                }], 'shared_commission_amount')
                ->withSum(['invoices as total_sales' => function($query) use ($range) {
                    $query->where('status', '!=', 'Cancelled')
                          ->whereBetween('created_at', [$range['start'], $range['end']]);
                }], 'final_amount')
                ->get();

            // Received shared commission from others
            $receivedMap = Invoice::where('status', '!=', 'Cancelled')
                ->whereBetween('created_at', [$range['start'], $range['end']])
                ->whereNotNull('shared_to_user_id')
                ->whereNotNull('shared_commission_amount')
                ->groupBy('shared_to_user_id')
                ->selectRaw('shared_to_user_id, SUM(shared_commission_amount) as received_commission')
                ->pluck('received_commission', 'shared_to_user_id');

            foreach ($employees as $employee) {
                $employee->received_commission = (int) ($receivedMap[$employee->id] ?? 0);
                $employee->net_commission = (int) ($employee->gross_commission ?? 0)
                    - (int) ($employee->shared_out ?? 0)
                    + $employee->received_commission;
            }

            $data['employees'] = $employees->sortByDesc('net_commission')->values();

        } elseif ($this->view === 'employee_detail') {
            $data['employee'] = User::findOrFail($this->selectedUserId);
            $data['invoices'] = Invoice::with(['customer', 'sharedTo'])
                ->where('user_id', $this->selectedUserId)
                ->where('status', '!=', 'Cancelled')
                ->whereBetween('created_at', [$range['start'], $range['end']])
                ->latest()
                ->paginate(15);
            // Invoices where this user received shared commission
            $data['receivedInvoices'] = Invoice::with(['user', 'customer'])
                ->where('shared_to_user_id', $this->selectedUserId)
                ->where('status', '!=', 'Cancelled')
                ->whereBetween('created_at', [$range['start'], $range['end']])
                ->latest()
                ->get();
        } elseif ($this->view === 'invoice_detail') {
            $data['invoice'] = Invoice::with(['items', 'customer', 'user', 'sharedTo'])->findOrFail($this->selectedInvoiceId);
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
            case 'custom':
                $start = $this->customStart
                    ? Carbon::parse($this->customStart)->startOfDay()
                    : now()->startOfMonth();
                $end = $this->customEnd
                    ? Carbon::parse($this->customEnd)->endOfDay()
                    : now()->endOfDay();
                // Nếu nhập ngược (đầu > cuối) thì hoán đổi cho an toàn
                if ($start->gt($end)) {
                    [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
                }
                break;
        }

        return ['start' => $start, 'end' => $end];
    }
}
