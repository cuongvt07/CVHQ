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
        // Trang cấu hình hoa hồng tự động yêu cầu quyền chi tiết riêng.
        return 'commission.settings';
    }

    public bool $auto_commission_enabled = false;
    public array $commission_ranges = [];

    // Loại hoa hồng mặc định chung khi tạo sản phẩm mới.
    public string $commission_default_type = 'amount';   // 'amount' | 'percent'
    public float $commission_default_percent = 0;

    public function mount(): void
    {
        // get() trả về JSON đã decode (bool true, không phải chuỗi 'true') nên dùng filter_var.
        $this->auto_commission_enabled = filter_var(SystemSetting::get('auto_commission_enabled', false), FILTER_VALIDATE_BOOLEAN);
        $ranges = SystemSetting::get('commission_ranges', []);
        $this->commission_ranges = is_array($ranges) ? $ranges : [];

        $type = SystemSetting::get('commission_default_type', 'amount');
        $this->commission_default_type = in_array($type, ['amount', 'percent'], true) ? $type : 'amount';
        $this->commission_default_percent = (float) SystemSetting::get('commission_default_percent', 0);
    }

    public function saveDefaultType(): void
    {
        $this->validate([
            'commission_default_type' => 'required|in:amount,percent',
            'commission_default_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        SystemSetting::set('commission_default_type', $this->commission_default_type);
        SystemSetting::set('commission_default_percent', (string) ($this->commission_default_percent ?: 0));

        $this->dispatch('notify', message: 'Đã lưu loại hoa hồng mặc định!', type: 'success');
    }

    public function addCommissionRange(): void
    {
        // "Bước giá" của dòng cuối dùng để TỰ GỢI Ý mốc giá dòng mới (không tham gia tính HH).
        $last = collect($this->commission_ranges)->last();
        $newMin = (int) ($last['max'] ?? 0);
        $step = (int) ($last['step'] ?? 0);
        $newMax = $step > 0 ? $newMin + $step : 0;

        $this->commission_ranges[] = [
            'min' => $newMin,
            'max' => $newMax,
            'amount' => (int) ($last['amount'] ?? 0),
            'step' => $step,
        ];
    }

    public function removeCommissionRange($index): void
    {
        unset($this->commission_ranges[$index]);
        $this->commission_ranges = array_values($this->commission_ranges);
    }

    public function save(): void
    {
        $this->persistRanges();
        $this->dispatch('notify', message: 'Đã lưu cấu hình hoa hồng tự động!', type: 'success');
    }

    /**
     * Hiệu chỉnh hoa hồng HÀNG LOẠT: tính lại commission_amount cho TẤT CẢ sản phẩm
     * theo bảng khoảng giá hiện tại (ghi đè giá trị cũ).
     */
    public function applyToAllProducts(): void
    {
        $ranges = $this->persistRanges(); // lưu cấu hình mới trước, rồi áp dụng

        $updated = 0;
        \App\Models\Product::query()
            ->select('id', 'sale_price', 'commission_amount')
            ->chunkById(500, function ($products) use (&$updated, $ranges) {
                foreach ($products as $p) {
                    $new = $this->commissionForPrice((int) $p->sale_price, $ranges);
                    if ((int) $p->commission_amount !== $new) {
                        \App\Models\Product::where('id', $p->id)->update(['commission_amount' => $new]);
                        $updated++;
                    }
                }
            });

        $this->dispatch('notify', message: "Đã hiệu chỉnh hoa hồng cho {$updated} sản phẩm theo bảng cấu hình.", type: 'success');
    }

    /**
     * Chuẩn hóa + lưu bảng khoảng giá. Trả về mảng đã chuẩn hóa.
     */
    private function persistRanges(): array
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
                'step' => max(0, (int) ($range['step'] ?? 0)),
            ])
            ->filter(fn($range) => $range['amount'] > 0 || $range['min'] > 0 || $range['max'] > 0)
            ->sortBy('min')
            ->values()
            ->all();

        SystemSetting::set('auto_commission_enabled', $this->auto_commission_enabled ? 'true' : 'false');
        SystemSetting::set('commission_ranges', $ranges);
        $this->commission_ranges = $ranges;

        return $ranges;
    }

    /**
     * Tra mức hoa hồng theo giá (mốc trên BAO GỒM). Đồng bộ với ProductIndex::commissionForPrice().
     */
    private function commissionForPrice(int $price, array $ranges): int
    {
        foreach ($ranges as $range) {
            $min = (int) ($range['min'] ?? 0);
            $max = (int) ($range['max'] ?? 0);
            $amount = (int) ($range['amount'] ?? 0);
            if ($price >= $min && ($max <= 0 || $price <= $max)) {
                return $amount;
            }
        }
        return 0;
    }

    public function render()
    {
        return view('livewire.system.commission-settings')->layout('layouts.app');
    }
}
