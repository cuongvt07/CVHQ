<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithEvents;
use App\Traits\TracksImportProgress;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class InvoicesImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading, ShouldQueue, WithEvents
{
    use TracksImportProgress;
    public function collection(Collection $rows)
    {
        // Group by Invoice Code
        $groupedInvoices = $rows->groupBy('ma_hoa_don');

        foreach ($groupedInvoices as $invoiceCode => $items) {
            if (empty($invoiceCode)) continue;

            $firstItem = $items->first();
            
            // Find or create customer
            $customer = null;
            if (!empty($firstItem['ma_khach_hang'])) {
                $customer = Customer::where('customer_code', $firstItem['ma_khach_hang'])->first();
            }

            // Create Invoice
            $invoice = Invoice::updateOrCreate(
                ['invoice_code' => $invoiceCode],
                [
                    'branch'          => $firstItem['chi_nhanh'] ?? null,
                    'customer_id'     => $customer ? $customer->id : null,
                    'seller_name'     => $firstItem['nguoi_ban'] ?? null,
                    'sales_channel'   => $firstItem['kenh_ban'] ?? null,
                    'total_amount'    => $this->cleanNumeric($firstItem['tong_tien_hang'] ?? 0),
                    'discount_amount' => $this->cleanNumeric($firstItem['giam_gia_hoa_don'] ?? 0),
                    'extra_fee'       => $this->cleanNumeric($firstItem['thu_khac'] ?? 0),
                    'final_amount'    => $this->cleanNumeric($firstItem['khach_can_tra'] ?? 0),
                    'paid_amount'     => $this->cleanNumeric($firstItem['khach_da_tra'] ?? 0),
                    'cash_amount'     => $this->cleanNumeric($firstItem['tien_mat'] ?? 0),
                    'card_amount'     => $this->cleanNumeric($firstItem['the'] ?? 0),
                    'wallet_amount'   => $this->cleanNumeric($firstItem['vi'] ?? 0),
                    'transfer_amount' => $this->cleanNumeric($firstItem['chuyen_khoan'] ?? 0),
                    'status'          => $firstItem['trang_thai'] ?? 'Completed',
                    'delivery_status' => $firstItem['trang_thai_giao_hang'] ?? null,
                    'created_at'      => $this->parseDate($firstItem['thoi_gian_tao'] ?? null),
                ]
            );

            // Clear existing items to avoid duplicates if re-importing
            $invoice->items()->delete();

            // Create Items
            foreach ($items as $itemRow) {
                if (empty($itemRow['ma_hang'])) continue;

                $product = Product::where('sku', $itemRow['ma_hang'])->first();

                InvoiceItem::create([
                    'invoice_id'       => $invoice->id,
                    'product_id'       => $product ? $product->id : null,
                    'sku'              => $itemRow['ma_hang'],
                    'product_name'     => $itemRow['ten_hang'] ?? ($product ? $product->name : null),
                    'quantity'         => $this->cleanNumeric($itemRow['so_luong'] ?? 1),
                    'unit_price'       => $this->cleanNumeric($itemRow['don_gia'] ?? 0),
                    'discount_percent' => $this->cleanNumeric($itemRow['giam_gia'] ?? 0), // "Giảm giá %"
                    'discount_amount'  => $this->cleanNumeric($itemRow['giam_gia_2'] ?? 0), // "Giảm giá" (amount)
                    'final_price'      => $this->cleanNumeric($itemRow['thanh_tien'] ?? 0),
                ]);
            }
        }
    }

    private function cleanNumeric($value)
    {
        if (is_numeric($value)) return $value;
        if (empty($value)) return 0;
        
        // Remove thousands separators (commas or dots depending on format)
        // If it's something like 60,000.00 -> remove comma
        // If it's something like 60.000,00 -> remove dot and change comma to dot
        
        $value = str_replace(',', '', $value); // Remove comma assuming it's thousands
        return (float) $value;
    }

    private function parseDate($value)
    {
        if (empty($value)) return now();
        
        if (is_numeric($value)) {
            return Date::excelToDateTimeObject($value);
        }

        try {
            // Handle common formats like "21/04/2026 20:49:31"
            return \Carbon\Carbon::createFromFormat('d/m/Y H:i:s', $value);
        } catch (\Exception $e) {
            try {
                return \Carbon\Carbon::parse($value);
            } catch (\Exception $e2) {
                return now();
            }
        }
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
