<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithEvents;
use App\Traits\TracksImportProgress;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class CustomersImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, WithUpserts, ShouldQueue, WithEvents
{
    use TracksImportProgress;

    public function model(array $row)
    {
        $name = $row['ten_khach_hang'] ?? $row['ten_khach'] ?? null;
        $code = $row['ma_khach_hang'] ?? $row['ma_khach'] ?? null;

        if (empty($name)) {
            return null;
        }

        // Sanitizing currency/numeric values
        $currentDebt = isset($row['no_can_thu_hien_tai']) ? (float)str_replace(',', '', $row['no_can_thu_hien_tai']) : 0;
        $totalSpent = isset($row['tong_ban']) ? (float)str_replace(',', '', $row['tong_ban']) : 0;
        $totalSpentNet = isset($row['tong_ban_tru_tra_hang']) ? (float)str_replace(',', '', $row['tong_ban_tru_tra_hang']) : 0;

        return new Customer([
            'customer_code'       => $code,
            'full_name'           => $name,
            'phone'               => $row['dien_thoai'] ?? null,
            'email'               => $row['email'] ?? null,
            'address'             => $row['dia_chi'] ?? null,
            'ward'                => $row['phuong_xa'] ?? null,
            'delivery_area'       => $row['khu_vuc_giao_hang'] ?? null,
            'customer_type'       => $row['loai_khach'] ?? 'Cá nhân',
            'company'             => $row['cong_ty'] ?? null,
            'tax_code'            => $row['ma_so_thue'] ?? null,
            'identity_number'     => $row['so_cmndcccd'] ?? null,
            'birthday'            => !empty($row['ngay_sinh']) ? (is_numeric($row['ngay_sinh']) ? Date::excelToDateTimeObject($row['ngay_sinh']) : $row['ngay_sinh']) : null,
            'gender'              => $row['gioi_tinh'] ?? null,
            'facebook'            => $row['facebook'] ?? null,
            'customer_group'      => $row['nhom_khach_hang'] ?? 'Khách lẻ',
            'note'                => $row['ghi_chu'] ?? null,
            'created_by'          => $row['nguoi_tao'] ?? null,
            'branch_created'      => $row['chi_nhanh_tao'] ?? null,
            'last_transaction_at' => !empty($row['ngay_giao_dich_cuoi']) ? (is_numeric($row['ngay_giao_dich_cuoi']) ? Date::excelToDateTimeObject($row['ngay_giao_dich_cuoi']) : $row['ngay_giao_dich_cuoi']) : null,
            'current_debt'        => $currentDebt,
            'total_spent'         => $totalSpent,
            'total_spent_net'     => $totalSpentNet,
            'status'              => (isset($row['trang_thai']) && ($row['trang_thai'] == '1' || strtolower($row['trang_thai']) == 'active')) ? 1 : 0,
        ]);
    }

    public function uniqueBy()
    {
        return 'customer_code';
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
