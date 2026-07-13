<?php

namespace App\Livewire\Attendance;

use App\Models\Attendance;
use App\Models\WorkShift;
use Livewire\Component;

/**
 * Nút check-in/check-out nổi ở góc phải dưới (mọi nhân viên đã đăng nhập).
 * Xanh "Check In" -> đỏ "Check Out" kèm đồng hồ đếm thời gian đang làm.
 */
class CheckInButton extends Component
{
    public ?int $openId = null;
    public ?string $checkInAtIso = null; // cho đồng hồ JS đếm lên
    public string $shiftName = '';
    public ?int $shiftMinutes = null;

    public function mount(): void
    {
        $this->refreshState();
    }

    private function refreshState(): void
    {
        $att = Attendance::where('user_id', auth()->id())
            ->whereNull('check_out_at')->latest('check_in_at')->first();

        if ($att) {
            $this->openId = $att->id;
            $this->checkInAtIso = $att->check_in_at->toIso8601String();
            $this->shiftName = $att->shift_name ?? '';
            $this->shiftMinutes = $att->shift_minutes;
        } else {
            $this->openId = null;
            $this->checkInAtIso = null;
            $this->shiftName = '';
            $this->shiftMinutes = null;
        }
    }

    public function checkIn(): void
    {
        // Không cho mở 2 phiên cùng lúc.
        if (Attendance::where('user_id', auth()->id())->whereNull('check_out_at')->exists()) {
            $this->refreshState();
            return;
        }

        $now = now();
        $shift = WorkShift::detectForCheckIn($now); // ca theo giờ check-in gần nhất

        Attendance::create([
            'user_id'       => auth()->id(),
            'work_shift_id' => $shift?->id,
            'shift_name'    => $shift?->name,
            'shift_minutes' => $shift?->duration_minutes,
            'check_in_at'   => $now,
            'work_date'     => $now->toDateString(),
        ]);

        $this->refreshState();
        $this->dispatch('notify', message: 'Đã check-in' . ($shift ? ' — ' . $shift->name : '') . '.', type: 'success');
    }

    public function checkOut(): void
    {
        $att = Attendance::where('user_id', auth()->id())
            ->whereNull('check_out_at')->latest('check_in_at')->first();
        if (!$att) {
            $this->refreshState();
            return;
        }

        $now = now();
        $actual = max(0, $att->check_in_at->diffInMinutes($now));
        // Chỉ tính đủ/thiếu, KHÔNG thừa: cap theo thời lượng ca.
        $worked = $att->shift_minutes ? min($actual, (int) $att->shift_minutes) : $actual;

        $att->update(['check_out_at' => $now, 'worked_minutes' => $worked]);

        $this->refreshState();
        $h = number_format($worked / 60, 2, ',', '.');
        $this->dispatch('notify', message: "Đã check-out. Thời gian công: {$h} giờ.", type: 'success');
    }

    public function render()
    {
        return view('livewire.attendance.check-in-button');
    }
}
