<?php

namespace App\Livewire\System;

use App\Models\SystemSetting;
use Livewire\Component;
use App\Traits\HasPermissions;

class CommissionSettings extends Component
{
    use HasPermissions;

    protected function getModuleKey(): string
    {
        return 'commissions';
    }

    public bool $auto_commission_enabled = false;
    public array $commission_ranges = [];

    public function mount(): void
    {
        // get() trả về JSON đã decode (bool true, không phải chuỗi 'true') nên dùng filter_var.
        $this->auto_commission_enabled = filter_var(SystemSetting::get('auto_commission_enabled', false), FILTER_VALIDATE_BOOLEAN);
        $ranges = SystemSetting::get('commission_ranges', []);
        $this->commission_ranges = is_array($ranges) ? $ranges : [];
    }

    public function addCommissionRange(): void
    {
        $lastMax = collect($this->commission_ranges)->max(fn($range) => (int) ($range['max'] ?? 0));
        $this->commission_ranges[] = ['min' => (int) $lastMax, 'max' => 0, 'amount' => 0];
    }

    public function removeCommissionRange($index): void
    {
        unset($this->commission_ranges[$index]);
        $this->commission_ranges = array_values($this->commission_ranges);
    }

    public function save(): void
    {
        $this->validate([
            'auto_commission_enabled' => 'boolean',
            'commission_ranges' => 'array',
        ]);

        $ranges = collect($this->commission_ranges)
            ->map(fn($range) => [
                'min' => max(0, (int) ($range['min'] ?? 0)),
                'max' => max(0, (int) ($range['max'] ?? 0)),
                'amount' => max(0, (int) ($range['amount'] ?? 0)),
            ])
            ->filter(fn($range) => $range['amount'] > 0 || $range['min'] > 0 || $range['max'] > 0)
            ->sortBy('min')
            ->values()
            ->all();

        SystemSetting::set('auto_commission_enabled', $this->auto_commission_enabled ? 'true' : 'false');
        SystemSetting::set('commission_ranges', $ranges);
        $this->commission_ranges = $ranges;

        $this->dispatch('notify', message: 'Đã lưu cấu hình hoa hồng tự động!', type: 'success');
    }

    public function render()
    {
        return view('livewire.system.commission-settings')->layout('layouts.app');
    }
}
