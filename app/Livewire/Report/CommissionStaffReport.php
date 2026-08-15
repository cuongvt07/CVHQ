<?php

namespace App\Livewire\Report;

use App\Http\Controllers\ReportPrintController;
use App\Traits\HasPermissions;
use Livewire\Component;

class CommissionStaffReport extends Component
{
    use HasPermissions;

    public string $month = '';
    public string $branch = 'all';

    protected function getModuleKey(): string
    {
        return 'reports';
    }

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function render()
    {
        $branches = \App\Models\Branch::options();
        if ($this->branch !== 'all' && !array_key_exists($this->branch, $branches)) {
            $this->branch = 'all';
        }

        $data = ReportPrintController::buildCommission($this->month, $this->branch);

        return view('livewire.report.commission-staff-report', $data + ['branches' => $branches])->layout('layouts.app');
    }
}
