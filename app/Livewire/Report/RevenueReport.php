<?php

namespace App\Livewire\Report;

use App\Http\Controllers\ReportPrintController;
use App\Traits\HasPermissions;
use Livewire\Component;

class RevenueReport extends Component
{
    use HasPermissions;

    public string $fromMonth = '';
    public string $toMonth = '';
    public string $branch = 'all';

    protected function getModuleKey(): string
    {
        return 'reports';
    }

    public function mount(): void
    {
        $this->fromMonth = now()->startOfYear()->format('Y-m');
        $this->toMonth   = now()->format('Y-m');
    }

    public function render()
    {
        $branches = \App\Models\Branch::options();
        if ($this->branch !== 'all' && !array_key_exists($this->branch, $branches)) {
            $this->branch = 'all';
        }

        $data = ReportPrintController::buildRows($this->fromMonth, $this->toMonth, $this->branch);

        // Đồng bộ lại ô chọn nếu bị nhập ngược (buildRows đã hoán đổi).
        $this->fromMonth = $data['fromMonth'];
        $this->toMonth   = $data['toMonth'];

        return view('livewire.report.revenue-report', $data + ['branches' => $branches])->layout('layouts.app');
    }
}
