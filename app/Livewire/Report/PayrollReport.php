<?php

namespace App\Livewire\Report;

use App\Models\Attendance;
use App\Models\User;
use App\Traits\HasPermissions;
use Livewire\Component;

class PayrollReport extends Component
{
    use HasPermissions;

    /** Tối đa 13 giờ / ngày. */
    public const MAX_MINUTES = 13 * 60;

    public string $month = '';             // Y-m
    public ?int $expandedUserId = null;    // NV đang mở chi tiết
    public array $editHours = [];          // [attendance_id => số giờ] để sửa

    protected function getModuleKey(): string
    {
        return 'reports';
    }

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    private function bounds(): array
    {
        try {
            $from = \Illuminate\Support\Carbon::createFromFormat('Y-m', $this->month)->startOfMonth();
        } catch (\Throwable $e) {
            $from = now()->startOfMonth();
            $this->month = $from->format('Y-m');
        }
        return [$from, $from->copy()->endOfMonth()];
    }

    /** Mở/đóng chi tiết 1 nhân viên; nạp sẵn số giờ để sửa. */
    public function toggleDetail(int $userId): void
    {
        if ($this->expandedUserId === $userId) {
            $this->expandedUserId = null;
            return;
        }
        $this->expandedUserId = $userId;
        [$from, $to] = $this->bounds();
        $this->editHours = [];
        Attendance::where('user_id', $userId)
            ->whereBetween('work_date', [$from->toDateString(), $to->toDateString()])
            ->get(['id', 'worked_minutes'])
            ->each(fn ($a) => $this->editHours[$a->id] = round(((int) $a->worked_minutes) / 60, 2));
    }

    /** Sửa số giờ công của 1 phiên (cap 13 giờ). */
    public function saveHours(int $attendanceId): void
    {
        $att = Attendance::find($attendanceId);
        if (!$att) {
            return;
        }
        $h = (float) ($this->editHours[$attendanceId] ?? 0);
        $mins = max(0, min((int) round($h * 60), self::MAX_MINUTES));
        $data = ['worked_minutes' => $mins];
        // Nếu phiên chưa có check-out (quên) mà sửa giờ -> đánh dấu đã chốt.
        if ($att->check_out_at === null) {
            $data['check_out_at'] = $att->check_in_at->copy()->addMinutes($mins);
        }
        $att->update($data);
        $this->dispatch('notify', message: 'Đã cập nhật giờ công ngày ' . $att->work_date->format('d/m') . '.', type: 'success');
    }

    public function render()
    {
        [$from, $to] = $this->bounds();

        // Lấy toàn bộ chấm công trong tháng (kể cả phiên chưa check-out = 0 giờ).
        $atts = Attendance::with('user:id,name,hourly_rate,deleted_at')
            ->whereBetween('work_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('check_in_at')
            ->get();

        $byUser = $atts->groupBy('user_id');

        $rows = $byUser->map(function ($list, $uid) {
            $u = $list->first()->user;
            if (!$u) {
                return null;
            }
            // Cap 13h/ngày rồi cộng các ngày.
            $minutes = $list->groupBy(fn ($a) => optional($a->work_date)->toDateString())
                ->sum(fn ($g) => min($g->sum(fn ($a) => (int) $a->worked_minutes), self::MAX_MINUTES));
            $hours = round($minutes / 60, 2);
            $rate  = (int) $u->hourly_rate;
            return [
                'user_id'  => (int) $uid,
                'name'     => $u->name . ($u->deleted_at ? ' (đã nghỉ)' : ''),
                'sessions' => $list->count(),
                'hours'    => $hours,
                'rate'     => $rate,
                'salary'   => (int) round($hours * $rate),
            ];
        })->filter()->sortBy('name')->values();

        // Chi tiết theo ngày cho NV đang mở.
        $detail = collect();
        if ($this->expandedUserId && $byUser->has($this->expandedUserId)) {
            $rate = (int) optional($byUser[$this->expandedUserId]->first()->user)->hourly_rate;
            $detail = $byUser[$this->expandedUserId]->sortByDesc('check_in_at')->map(function ($a) use ($rate) {
                $mins = (int) $a->worked_minutes;
                return [
                    'id'     => $a->id,
                    'date'   => optional($a->work_date)->format('d/m/Y'),
                    'in'     => optional($a->check_in_at)->format('H:i'),
                    'out'    => $a->check_out_at ? $a->check_out_at->format('H:i') : null,
                    'forgot' => $a->check_out_at === null,
                    'salary' => (int) round(($mins / 60) * $rate),
                ];
            })->values();
        }

        return view('livewire.report.payroll-report', [
            'rows'        => $rows,
            'detail'      => $detail,
            'totalHours'  => round($rows->sum('hours'), 2),
            'totalSalary' => (int) $rows->sum('salary'),
        ])->layout('layouts.app');
    }
}
