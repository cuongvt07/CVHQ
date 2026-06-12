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
    }

    public function save(): void
    {
        $this->validate([
            'shop_name'       => 'required|min:2|max:200',
            'shop_hn_address' => 'nullable|string|max:500',
            'shop_hn_phone'   => 'nullable|string|max:50',
            'shop_sg_address' => 'nullable|string|max:500',
            'shop_sg_phone'   => 'nullable|string|max:50',
        ]);

        SystemSetting::set('shop_name',       $this->shop_name);
        SystemSetting::set('shop_hn_address', $this->shop_hn_address);
        SystemSetting::set('shop_hn_phone',   $this->shop_hn_phone);
        SystemSetting::set('shop_sg_address', $this->shop_sg_address);
        SystemSetting::set('shop_sg_phone',   $this->shop_sg_phone);

        $this->dispatch('notify', message: 'Đã lưu cài đặt cửa hàng!', type: 'success');
    }

    public function render()
    {
        return view('livewire.system.system-settings')->layout('layouts.app');
    }
}
