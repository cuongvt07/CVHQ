<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithEvents;
use App\Traits\TracksImportProgress;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class CustomersImport implements OnEachRow, WithHeadingRow, WithChunkReading, ShouldQueue, WithEvents
{
    use TracksImportProgress;

    public function onRow(Row $row)
    {
        $rowData = $row->toArray();
        $name = $rowData['ten_khach_hang'] ?? $rowData['ten_khach'] ?? null;
        $code = $rowData['ma_khach_hang'] ?? $rowData['ma_khach'] ?? null;

        if (empty($name)) {
            return;
        }

        try {
            // Sanitizing currency/numeric values
            $currentDebt = isset($rowData['no_can_thu_hien_tai']) ? (int)str_replace(',', '', $rowData['no_can_thu_hien_tai']) : 0;
            $totalSpent = isset($rowData['tong_ban']) ? (int)str_replace(',', '', $rowData['tong_ban']) : 0;
            $totalSpentNet = isset($rowData['tong_ban_tru_tra_hang']) ? (int)str_replace(',', '', $rowData['tong_ban_tru_tra_hang']) : 0;

            $data = [
                'full_name'           => $name,
                'phone'               => $rowData['dien_thoai'] ?? null,
                'email'               => $rowData['email'] ?? null,
                'address'             => $rowData['dia_chi'] ?? null,
                'ward'                => $rowData['phuong_xa'] ?? null,
                'delivery_area'       => $rowData['khu_vuc_giao_hang'] ?? null,
                'customer_type'       => $rowData['loai_khach'] ?? 'Cá nhân',
                'company'             => $rowData['cong_ty'] ?? null,
                'tax_code'            => $rowData['ma_so_thue'] ?? null,
                'identity_number'     => $rowData['so_cmndcccd'] ?? null,
                'birthday'            => !empty($rowData['ngay_sinh']) ? (is_numeric($rowData['ngay_sinh']) ? Date::excelToDateTimeObject($rowData['ngay_sinh']) : $rowData['ngay_sinh']) : null,
                'gender'              => $rowData['gioi_tinh'] ?? null,
                'facebook'            => $rowData['facebook'] ?? null,
                'customer_group'      => $rowData['nhom_khach_hang'] ?? 'Khách lẻ',
                'note'                => $rowData['ghi_chu'] ?? null,
                'created_by'          => $rowData['nguoi_tao'] ?? null,
                'branch_created'      => $rowData['chi_nhanh_tao'] ?? null,
                'last_transaction_at' => !empty($rowData['ngay_giao_dich_cuoi']) ? (is_numeric($rowData['ngay_giao_dich_cuoi']) ? Date::excelToDateTimeObject($rowData['ngay_giao_dich_cuoi']) : $rowData['ngay_giao_dich_cuoi']) : null,
                'current_debt'        => $currentDebt,
                'total_spent'         => $totalSpent,
                'total_spent_net'     => $totalSpentNet,
                'status'              => (isset($rowData['trang_thai']) && ($rowData['trang_thai'] == '1' || strtolower($rowData['trang_thai']) == 'active')) ? 'Active' : 'Inactive',
            ];

            $customer = Customer::withTrashed()->firstOrNew(['customer_code' => $code]);
            
            if ($customer->exists && $customer->trashed()) {
                $customer->restore();
            }

            $customer->fill($data);
            $customer->save();

        } catch (\Exception $e) {
            $this->recordError("Dòng {$row->getIndex()}: " . $e->getMessage());
        }
    }

    public function tries(): int
    {
        return 3;
    }

    public function backoff(): int
    {
        return 5;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
