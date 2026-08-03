<?php

namespace App\Livewire\Attendance;

use App\Models\Attendance;
use App\Models\SystemSetting;
use Livewire\Component;

/**
 * Nút check-in/check-out nổi (mọi nhân viên). KHÔNG theo ca:
 * nhân viên tự bấm check-in / check-out. Thời gian công 1 ngày TỐI ĐA 13 giờ.
 * Quên check-out (để mở qua ngày) -> coi như check-out = 0 (ngày đó tính 0 giờ).
 */
class CheckInButton extends Component
{
    /** Số phút công tối đa 1 phiên/ngày = 13 giờ. */
    public const MAX_MINUTES = 13 * 60;

    public ?int $openId = null;
    public ?string $checkInAtIso = null;
    public string $shiftName = '';       // giữ để view cũ không vỡ (luôn rỗng)
    public ?int $shiftMinutes = null;

    public function mount(): void
    {
        $this->refreshState();
    }

    private function refreshState(): void
    {
        try {
            $att = Attendance::where('user_id', auth()->id())
                ->whereNull('check_out_at')->latest('check_in_at')->first();

            // Phiên còn mở nhưng từ NGÀY TRƯỚC = quên check-out -> chốt 0 giờ.
            if ($att && $att->check_in_at->toDateString() < now()->toDateString()) {
                $att->update(['check_out_at' => $att->check_in_at, 'worked_minutes' => 0]);
                $att = null;
            }
        } catch (\Throwable $e) {
            $att = null;
        }

        $this->openId = $att?->id;
        $this->checkInAtIso = $att ? $att->check_in_at->toIso8601String() : null;
        $this->shiftName = '';
        $this->shiftMinutes = null;
    }

    /** Chặn nếu IP không thuộc mạng cửa hàng (khi bật khóa). */
    private function ipBlocked(): bool
    {
        if (SystemSetting::ipAllowedForAttendance(SystemSetting::clientIp())) {
            return false;
        }
        $this->dispatch('notify', message: 'Chỉ được check-in/out khi ở mạng cửa hàng.', type: 'error');
        return true;
    }

    /** Chặn nếu máy chưa đăng ký (khi bật khóa thiết bị). */
    private function deviceBlocked(?string $token): bool
    {
        if (SystemSetting::deviceAllowedForAttendance($token)) {
            return false;
        }
        $this->dispatch('notify', message: 'Máy này chưa được đăng ký chấm công.', type: 'error');
        return true;
    }

    public function checkIn(?string $deviceToken = null): void
    {
        if ($this->ipBlocked()) return;
        if ($this->deviceBlocked($deviceToken)) return;
        $this->refreshState(); // dọn phiên cũ quên check-out (nếu có)
        if ($this->openId) {
            return; // đã check-in hôm nay
        }

        $now = now();
        Attendance::create([
            'user_id'     => auth()->id(),
            'check_in_at' => $now,
            'work_date'   => $now->toDateString(),
        ]);

        $this->refreshState();
        $this->dispatch('ci-checked-in', iso: $this->checkInAtIso);
        $this->dispatch('notify', message: 'Đã check-in lúc ' . $now->format('H:i') . '.', type: 'success');
    }

    public function checkOut(?string $deviceToken = null): void
    {
        if ($this->ipBlocked()) return;
        if ($this->deviceBlocked($deviceToken)) return;
        $att = Attendance::where('user_id', auth()->id())
            ->whereNull('check_out_at')->latest('check_in_at')->first();
        if (!$att) {
            $this->refreshState();
            return;
        }

        $now = now();
        // Quên check-out (đã sang ngày khác) -> 0 giờ. Cùng ngày -> cap 13 giờ.
        if ($att->check_in_at->toDateString() < $now->toDateString()) {
            $worked = 0;
        } else {
            $worked = min((int) $att->check_in_at->diffInMinutes($now), self::MAX_MINUTES);
        }

        $att->update(['check_out_at' => $now, 'worked_minutes' => $worked]);

        $this->refreshState();
        $this->dispatch('ci-checked-out');
        $h = number_format($worked / 60, 2, ',', '.');
        $this->dispatch('notify', message: "Đã check-out. Thời gian công: {$h} giờ.", type: 'success');
    }

    public function render()
    {
        return view('livewire.attendance.check-in-button');
    }
}
