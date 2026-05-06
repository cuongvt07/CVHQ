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

    public function syncCommissions()
    {
        if (!auth()->user()->hasPermission('invoice.edit')) {
            $this->dispatch('notify', message: 'Bạn không có quyền đồng bộ dữ liệu!', type: 'error');
            return;
        }

        \DB::transaction(function () {
            // Get all non-cancelled invoices
            $invoices = Invoice::where('status', '!=', 'Cancelled')->with(['items.product', 'user'])->get();
            $count = 0;

            foreach ($invoices as $invoice) {
                $seller = $invoice->user;
                $canReceiveCommission = $seller ? $seller->can_receive_commission : true;
                $totalCommission = 0;

                foreach ($invoice->items as $item) {
                    // Use product's current commission rate if available, else keep current
                    $currentRate = $item->product ? $item->product->commission_amount : $item->commission_amount;
                    $newRate = $canReceiveCommission ? $currentRate : 0;

                    if ($item->commission_amount != $newRate) {
                        $item->update(['commission_amount' => $newRate]);
                    }

                    $totalCommission += ($newRate * $item->quantity);
                }

                if ($invoice->total_commission != $totalCommission) {
                    $invoice->update(['total_commission' => $totalCommission]);
                    $count++;
                }
            }

            $this->dispatch('notify', message: "Đã rà soát và cập nhật lại hoa hồng cho {$count} hóa đơn!", type: 'success');
        });
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
