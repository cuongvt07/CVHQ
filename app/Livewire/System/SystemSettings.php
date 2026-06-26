<?php

namespace App\Livewire\System;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class SystemSettings extends Component
{
    use WithFileUploads;

    // Thương hiệu hệ thống
    public string $app_name = '';
    public string $app_logo = '';        // đường dẫn logo đã lưu (storage path)
    public $logoUpload = null;           // file tạm khi người dùng chọn ảnh mới

    // Thông tin cửa hàng (hóa đơn)
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

        $this->app_name        = SystemSetting::get('app_name', 'CVHQ POS');
        $this->app_logo        = SystemSetting::get('app_logo', '');

        $this->shop_name       = SystemSetting::get('shop_name', 'Cửa hàng Cà vạt Hàn Quốc');
        $this->shop_hn_address = SystemSetting::get('shop_hn_address', '');
        $this->shop_hn_phone   = SystemSetting::get('shop_hn_phone', '');
        $this->shop_sg_address = SystemSetting::get('shop_sg_address', '');
        $this->shop_sg_phone   = SystemSetting::get('shop_sg_phone', '');
    }

    public function updatedLogoUpload(): void
    {
        $this->validate([
            'logoUpload' => 'image|max:2048', // tối đa 2MB
        ]);
    }

    public function removeLogo(): void
    {
        if ($this->app_logo && Storage::disk('public')->exists($this->app_logo)) {
            Storage::disk('public')->delete($this->app_logo);
        }
        $this->app_logo = '';
        $this->logoUpload = null;
        SystemSetting::set('app_logo', '');
        $this->dispatch('notify', message: 'Đã xóa logo hệ thống.', type: 'success');
    }

    public function save(): void
    {
        $this->validate([
            'app_name'        => 'required|min:2|max:120',
            'logoUpload'      => 'nullable|image|max:2048',
            'shop_name'       => 'required|min:2|max:200',
            'shop_hn_address' => 'nullable|string|max:500',
            'shop_hn_phone'   => 'nullable|string|max:50',
            'shop_sg_address' => 'nullable|string|max:500',
            'shop_sg_phone'   => 'nullable|string|max:50',
        ]);

        // Upload logo mới (nếu có) — xóa logo cũ
        if ($this->logoUpload) {
            if ($this->app_logo && Storage::disk('public')->exists($this->app_logo)) {
                Storage::disk('public')->delete($this->app_logo);
            }
            $this->app_logo = $this->logoUpload->store('system', 'public');
            $this->logoUpload = null;
        }

        SystemSetting::set('app_name',        $this->app_name);
        SystemSetting::set('app_logo',        $this->app_logo);

        SystemSetting::set('shop_name',       $this->shop_name);
        SystemSetting::set('shop_hn_address', $this->shop_hn_address);
        SystemSetting::set('shop_hn_phone',   $this->shop_hn_phone);
        SystemSetting::set('shop_sg_address', $this->shop_sg_address);
        SystemSetting::set('shop_sg_phone',   $this->shop_sg_phone);

        $this->dispatch('notify', message: 'Đã lưu cài đặt hệ thống!', type: 'success');
    }

    public function render()
    {
        return view('livewire.system.system-settings')->layout('layouts.app');
    }
}
