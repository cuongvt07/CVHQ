<?php

namespace App\Livewire\Report;

use App\Http\Controllers\ReportPrintController;
use App\Traits\HasPermissions;
use Livewire\Component;

class ProductReport extends Component
{
    use HasPermissions;

    public string $month = '';
    public string $branch = 'all';
    public string $mode = 'best';   // best | worst
    public int $limit = 15;

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
        if (!in_array($this->mode, ['best', 'worst'], true)) {
            $this->mode = 'best';
        }
        if (!in_array($this->limit, [10, 15, 20, 30, 50], true)) {
            $this->limit = 15;
        }

        $data = ReportPrintController::buildProducts($this->month, $this->branch, $this->mode, $this->limit);

        return view('livewire.report.product-report', $data + ['branches' => $branches])->layout('layouts.app');
    }
}
