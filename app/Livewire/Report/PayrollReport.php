<?php

namespace App\Livewire\Report;

use App\Models\Attendance;
use App\Models\User;
use App\Traits\HasPermissions;
use Livewire\Component;

class PayrollReport extends Component
{
    use HasPermissions;

    public string $month = ''; // định dạng Y-m (tính lương theo tháng)

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
        try {
            $from = \Illuminate\Support\Carbon::createFromFormat('Y-m', $this->month)->startOfMonth();
        } catch (\Throwable $e) {
            $from = now()->startOfMonth();
            $this->month = $from->format('Y-m');
        }
        $to = $from->copy()->endOfMonth();

        // Tổng phút công (chỉ phiên đã check-out) theo nhân viên trong tháng.
        $agg = Attendance::query()
            ->whereBetween('work_date', [$from->toDateString(), $to->toDateString()])
            ->whereNotNull('check_out_at')
            ->selectRaw('user_id, SUM(worked_minutes) AS minutes, COUNT(*) AS sessions')
            ->groupBy('user_id')->get()->keyBy('user_id');

        $rows = User::withTrashed()->orderBy('name')->get(['id', 'name', 'hourly_rate', 'deleted_at'])
            ->map(function ($u) use ($agg) {
                $minutes  = (int) ($agg[$u->id]->minutes ?? 0);
                $sessions = (int) ($agg[$u->id]->sessions ?? 0);
                $hours    = round($minutes / 60, 2);
                $rate     = (float) $u->hourly_rate;
                return [
                    'name'     => $u->name . ($u->deleted_at ? ' (đã nghỉ)' : ''),
                    'sessions' => $sessions,
                    'hours'    => $hours,
                    'rate'     => (int) $rate,
                    'salary'   => (int) round($hours * $rate),
                ];
            })
            ->filter(fn ($r) => $r['sessions'] > 0)
            ->values();

        return view('livewire.report.payroll-report', [
            'rows'        => $rows,
            'totalHours'  => round($rows->sum('hours'), 2),
            'totalSalary' => (int) $rows->sum('salary'),
        ])->layout('layouts.app');
    }
}
