<?php

namespace App\Livewire\System;

use App\Models\SystemSetting;
use App\Models\WorkShift;
use Livewire\Component;

class WorkShiftSettings extends Component
{
    public ?int $editId = null;
    public string $name = '';
    public string $start_time = '';
    public string $end_time = '';

    // Cấu hình đi muộn / phạt lương
    public string $lateStart = '08:30';
    public array $penalties = []; // [['minutes' => 10, 'amount' => 25000], ...]

    // Khóa check-in theo IP mạng cửa hàng
    public bool $ipLock = false;
    public array $allowedIps = [];

    public function mount(): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }
        $this->lateStart = SystemSetting::lateStartTime();
        $this->penalties = SystemSetting::latePenaltyTiers();
        if (empty($this->penalties)) {
            $this->penalties = [['minutes' => 10, 'amount' => 25000], ['minutes' => 20, 'amount' => 50000]];
        }
        $this->ipLock = SystemSetting::attendanceIpLock();
        $this->allowedIps = SystemSetting::attendanceAllowedIps();
        // Mặc định hiện sẵn 2 ô IP (VD 2 cửa hàng) để nhập ngay.
        while (count($this->allowedIps) < 2) {
            $this->allowedIps[] = '';
        }
    }

    public function addIp(): void
    {
        $this->allowedIps[] = '';
    }

    public function addCurrentIp(): void
    {
        $ip = SystemSetting::clientIp();
        if ($ip && !in_array($ip, $this->allowedIps, true)) {
            $this->allowedIps[] = $ip;
        }
    }

    public function removeIp(int $i): void
    {
        unset($this->allowedIps[$i]);
        $this->allowedIps = array_values($this->allowedIps);
    }

    public function saveIpConfig(): void
    {
        $ips = array_values(array_filter(
            array_map(fn ($s) => trim((string) $s), $this->allowedIps),
            fn ($s) => $s !== ''
        ));
        SystemSetting::set('attendance_ip_lock', $this->ipLock ? 1 : 0, 'Khóa check-in theo IP');
        SystemSetting::set('attendance_allowed_ips', $ips, 'Danh sách IP được phép check-in');
        $this->allowedIps = $ips;
        $this->dispatch('notify', message: 'Đã lưu cấu hình khóa IP check-in.', type: 'success');
    }

    public function addPenalty(): void
    {
        $this->penalties[] = ['minutes' => 0, 'amount' => 0];
    }

    public function removePenalty(int $i): void
    {
        unset($this->penalties[$i]);
        $this->penalties = array_values($this->penalties);
    }

    public function saveLateConfig(): void
    {
        $this->validate([
            'lateStart'          => 'required|date_format:H:i',
            'penalties.*.minutes'=> 'nullable|integer|min:0',
            'penalties.*.amount' => 'nullable|integer|min:0',
        ]);

        $tiers = [];
        foreach ($this->penalties as $p) {
            $m = (int) ($p['minutes'] ?? 0);
            $a = (int) ($p['amount'] ?? 0);
            if ($m > 0 && $a > 0) $tiers[] = ['minutes' => $m, 'amount' => $a];
        }
        usort($tiers, fn ($x, $y) => $x['minutes'] <=> $y['minutes']);

        SystemSetting::set('attendance_start_time', $this->lateStart, 'Giờ vào chuẩn (đi muộn)');
        SystemSetting::set('attendance_penalties', $tiers, 'Bậc phạt đi muộn');

        $this->penalties = $tiers;
        $this->dispatch('notify', message: 'Đã lưu cấu hình đi muộn & phạt lương.', type: 'success');
    }

    protected function rules(): array
    {
        return [
            'name'       => 'required|string|max:100',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i',
        ];
    }

    public function edit(int $id): void
    {
        $s = WorkShift::find($id);
        if (!$s) return;
        $this->editId = $s->id;
        $this->name = $s->name;
        $this->start_time = substr((string) $s->start_time, 0, 5);
        $this->end_time = substr((string) $s->end_time, 0, 5);
    }

    public function resetForm(): void
    {
        $this->reset(['editId', 'name', 'start_time', 'end_time']);
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $data = $this->validate();
        WorkShift::updateOrCreate(
            ['id' => $this->editId],
            [
                'name'       => $data['name'],
                'start_time' => $data['start_time'],
                'end_time'   => $data['end_time'],
                'sort_order' => $this->editId ? WorkShift::find($this->editId)?->sort_order ?? 0 : (WorkShift::max('sort_order') + 1),
            ]
        );
        $this->resetForm();
        $this->dispatch('notify', message: 'Đã lưu ca làm việc.', type: 'success');
    }

    public function toggleActive(int $id): void
    {
        $s = WorkShift::find($id);
        if ($s) {
            $s->update(['is_active' => !$s->is_active]);
        }
    }

    public function delete(int $id): void
    {
        WorkShift::where('id', $id)->delete();
        if ($this->editId === $id) {
            $this->resetForm();
        }
        $this->dispatch('notify', message: 'Đã xóa ca làm việc.', type: 'success');
    }

    public function render()
    {
        return view('livewire.system.work-shift-settings', [
            'shifts' => WorkShift::orderBy('sort_order')->orderBy('start_time')->get(),
        ])->layout('layouts.app');
    }
}
