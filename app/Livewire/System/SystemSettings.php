<?php

namespace App\Livewire\System;

use App\Models\SystemSetting;
use Livewire\Component;

class SystemSettings extends Component
{
    public string $shop_name = '';
    public string $shop_hn_address = '';
    public string $shop_hn_phone = '';
    public string $shop_sg_address = '';
    public string $shop_sg_phone = '';

    // Commission Settings
    public bool $auto_commission_enabled = false;
    public array $commission_ranges = [];

    public function mount(): void
    {
        if (!auth()->user() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        $this->shop_name      = SystemSetting::get('shop_name', 'Cửa hàng Cà vạt Hàn Quốc');
        $this->shop_hn_address = SystemSetting::get('shop_hn_address', '');
        $this->shop_hn_phone  = SystemSetting::get('shop_hn_phone', '');
        $this->shop_sg_address = SystemSetting::get('shop_sg_address', '');
        $this->shop_sg_phone  = SystemSetting::get('shop_sg_phone', '');
        
        $this->auto_commission_enabled = SystemSetting::get('auto_commission_enabled') === 'true';
        $this->commission_ranges = SystemSetting::get('commission_ranges', []);
    }

    public function save(): void
    {
        $this->validate([
            'shop_name'       => 'required|min:2|max:200',
            'shop_hn_address' => 'nullable|string|max:500',
            'shop_hn_phone'   => 'nullable|string|max:50',
            'shop_sg_address' => 'nullable|string|max:500',
            'shop_sg_phone'   => 'nullable|string|max:50',
            'auto_commission_enabled' => 'boolean',
            'commission_ranges' => 'array',
        ]);

        SystemSetting::set('shop_name',       $this->shop_name);
        SystemSetting::set('shop_hn_address', $this->shop_hn_address);
        SystemSetting::set('shop_hn_phone',   $this->shop_hn_phone);
        SystemSetting::set('shop_sg_address', $this->shop_sg_address);
        SystemSetting::set('shop_sg_phone',   $this->shop_sg_phone);
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

        $this->dispatch('notify', message: 'Đã lưu cài đặt cửa hàng!', type: 'success');
    }

    public function addCommissionRange()
    {
        $lastMax = collect($this->commission_ranges)->max(fn($range) => (int) ($range['max'] ?? 0));
        $this->commission_ranges[] = ['min' => (int) $lastMax, 'max' => 0, 'amount' => 0];
    }

    public function removeCommissionRange($index)
    {
        unset($this->commission_ranges[$index]);
        $this->commission_ranges = array_values($this->commission_ranges);
    }

    public function render()
    {
        return view('livewire.system.system-settings')->layout('layouts.app');
    }
}
