<?php

namespace App\Livewire\Attendance;

use App\Models\Attendance;
use App\Models\SystemSetting;
use Livewire\Component;

/**
 * Nút check-in/check-out nổi (mọi nhân viên). KHÔNG theo ca:
 * nhân viên tự bấm check-in / check-out. Thời gian công MỘT PHIÊN TỐI ĐA 13 giờ
 * (tính theo thời lượng thực tế kể từ lúc check-in, KHÔNG so sánh theo ngày lịch —
 * tránh check-in cuối ngày rồi bị tự động chốt/đổi trạng thái ngay khi qua 0h).
 * Quên check-out quá 13 giờ -> tự chốt phiên ở đúng mốc 13h.
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

    /**
     * Đồng bộ định kỳ (wire:poll) — phòng trường hợp 1 tab/thiết bị bị "quên" trạng thái
     * (VD nhân viên check-in trên điện thoại, tab máy tính công ty vẫn mở từ trước và
     * chưa reload, nên vẫn hiện xanh -> dễ bấm Check In lần 2, tạo phiên trùng/chồng
     * chéo). Chỉ bắn event khi trạng thái THỰC SỰ đổi so với lần đọc trước, tránh
     * làm phiền UI (nhấp nháy) mỗi lần poll không cần thiết.
     */
    public function poll(): void
    {
        $wasOpen = $this->openId;
        $this->refreshState();

        if ($wasOpen === $this->openId) {
            return; // không đổi -> không cần bắn lại event
        }

        if ($this->openId) {
            $this->dispatch('ci-checked-in', iso: $this->checkInAtIso);
        } else {
            $this->dispatch('ci-checked-out');
        }
    }

    private function refreshState(): void
    {
        try {
            $att = Attendance::where('user_id', auth()->id())
                ->whereNull('check_out_at')->latest('check_in_at')->first();

            // Chỉ coi là "quên check-out" khi đã QUÁ MAX_MINUTES (13h) kể từ lúc check-in —
            // KHÔNG so sánh theo ngày lịch (check-in 23h, request đến sau 0h vẫn chưa quá vài
            // tiếng thì KHÔNG được tự đóng phiên, nếu không nút sẽ tự nhảy xanh giữa ca làm).
            if ($att && $att->check_in_at->diffInMinutes(now()) > self::MAX_MINUTES) {
                $att->update(['check_out_at' => $att->check_in_at->copy()->addMinutes(self::MAX_MINUTES), 'worked_minutes' => self::MAX_MINUTES]);
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
        $userId = auth()->id();

        // Khoá theo user trong transaction: chặn race-condition khi bấm 2 lần/2 tab
        // gần như đồng thời (đã gặp thực tế: tạo 2 phiên mở cùng lúc, giờ công chồng
        // chéo lẫn nhau — VD 18:15 và 18:22 cùng tối). lockForUpdate() giữ khoá tới
        // hết transaction nên request thứ 2 phải đợi request đầu insert xong rồi mới
        // đọc lại, thấy phiên đã mở và tự dừng.
        $created = \DB::transaction(function () use ($userId, $now) {
            $stillOpen = Attendance::where('user_id', $userId)
                ->whereNull('check_out_at')
                ->lockForUpdate()
                ->exists();
            if ($stillOpen) {
                return false;
            }
            Attendance::create([
                'user_id'     => $userId,
                'check_in_at' => $now,
                'work_date'   => $now->toDateString(),
            ]);
            return true;
        });

        $this->refreshState();

        if (!$created) {
            return; // request khác đã check-in trước trong lúc chờ khoá
        }

        // Tự bấm (không phải đồng bộ từ poll) -> bắn event riêng để frontend RELOAD TRANG,
        // đảm bảo trạng thái luôn đúng 100% từ server thay vì tin vào Alpine cache.
        $this->dispatch('ci-self-checked-in');
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
        // Cap tối đa 13h/phiên theo thời lượng THỰC TẾ (không so sánh ngày lịch,
        // tránh check-in cuối ngày rồi checkout sau 0h bị tính nhầm = 0 giờ).
        $worked = min((int) $att->check_in_at->diffInMinutes($now), self::MAX_MINUTES);

        $att->update(['check_out_at' => $now, 'worked_minutes' => $worked]);

        $this->refreshState();
        // Tự bấm -> reload trang (xem ghi chú ở checkIn()).
        $this->dispatch('ci-self-checked-out');
        $h = number_format($worked / 60, 2, ',', '.');
        $this->dispatch('notify', message: "Đã check-out. Thời gian công: {$h} giờ.", type: 'success');
    }

    public function render()
    {
        return view('livewire.attendance.check-in-button');
    }
}
