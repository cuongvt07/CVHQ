<?php

namespace App\Support;

/**
 * Trình bày nhật ký hoạt động: nhãn hành động cụ thể (tiếng Việt) + URL chi tiết.
 * Dùng chung cho trang Nhật ký hệ thống và chuông thông báo để hiển thị nhất quán.
 */
class LogPresenter
{
    /**
     * Nhãn hành động cụ thể theo loại đối tượng + dữ liệu thay đổi.
     * Tránh ghi chung chung "cập nhật": với hóa đơn phân biệt Thêm mới/Sửa/Trả hàng/Hủy.
     */
    public static function actionLabel(string $modelType, string $action, ?array $changes = null): string
    {
        $base = class_basename($modelType);

        if ($base === 'Invoice') {
            return match ($action) {
                'created' => 'Thêm mới',
                'deleted' => 'Xóa',
                'updated' => self::invoiceUpdateLabel($changes),
                default   => self::generic($action),
            };
        }

        if (in_array($base, ['Product', 'Category'], true)) {
            return match ($action) {
                'created' => 'Thêm mới',
                'deleted' => 'Xóa',
                'updated' => 'Sửa',
                default   => self::generic($action),
            };
        }

        if ($base === 'StockCheck') {
            return match ($action) {
                'created' => 'Tạo phiếu kiểm',
                'deleted' => 'Xóa phiếu kiểm',
                'updated' => 'Cập nhật phiếu kiểm',
                default   => self::generic($action),
            };
        }

        if ($base === 'StockTransfer') {
            return match ($action) {
                'created' => 'Tạo phiếu gửi',
                'deleted' => 'Xóa phiếu gửi',
                'updated' => 'Cập nhật phiếu gửi',
                default   => self::generic($action),
            };
        }

        return self::generic($action);
    }

    private static function invoiceUpdateLabel(?array $changes): string
    {
        $status = $changes['after']['status'] ?? null;
        return match ($status) {
            'Cancelled' => 'Hủy đơn',
            'Returned'  => 'Trả hàng',
            default     => 'Sửa',
        };
    }

    private static function generic(string $action): string
    {
        return match ($action) {
            'created'   => 'Thêm mới',
            'updated'   => 'Cập nhật',
            'deleted'   => 'Xóa',
            'cancelled' => 'Hủy',
            'restored'  => 'Khôi phục',
            default     => ucfirst($action),
        };
    }

    /**
     * Màu loại thông báo theo hành động (cho icon chuông).
     */
    public static function actionType(string $action): string
    {
        return match ($action) {
            'created'             => 'success',
            'deleted', 'cancelled' => 'error',
            default               => 'info',
        };
    }

    /**
     * Nhãn tiếng Việt cho các trường (dùng khi hiện nội dung sửa cũ → mới).
     */
    private static function fieldLabels(): array
    {
        return [
            // Sản phẩm
            'sku' => 'Mã SP', 'barcode' => 'Mã vạch', 'base_name' => 'Tên gốc', 'name' => 'Tên',
            'product_type' => 'Loại', 'category_id' => 'Danh mục', 'category_path' => 'Danh mục',
            'brand' => 'Thương hiệu', 'sale_price' => 'Giá bán', 'cost_price' => 'Giá gốc',
            'stock_quantity' => 'Tồn kho', 'reserved_quantity' => 'Tồn giữ', 'min_stock' => 'Tồn tối thiểu',
            'max_stock' => 'Tồn tối đa', 'unit' => 'ĐVT', 'commission_amount' => 'Hoa hồng',
            'commission_type' => 'Loại hoa hồng', 'commission_value' => 'Giá trị hoa hồng',
            'auto_commission_enabled' => 'Tự tính hoa hồng', 'location' => 'Vị trí', 'is_active' => 'Kích hoạt',
            'is_direct_sale' => 'Bán trực tiếp', 'images' => 'Ảnh', 'attributes' => 'Thuộc tính',
            'description' => 'Mô tả', 'weight' => 'Cân nặng', 'related_sku' => 'SKU liên kết',
            'note_template' => 'Ghi chú mẫu',
            // Danh mục
            'parent_id' => 'Danh mục cha', 'slug' => 'Slug',
        ];
    }

    /**
     * Tóm tắt nội dung sửa dạng "Trường: cũ → mới · ...". Rỗng nếu không có thay đổi.
     * Dùng cho nhật ký hệ thống + tab thông báo để dò lại được sau này.
     */
    public static function changeSummary(string $modelType, ?array $changes, int $max = 6): string
    {
        if (empty($changes['after']) || !is_array($changes['after'])) {
            return '';
        }

        $labels = self::fieldLabels();
        $money = ['sale_price', 'cost_price', 'commission_amount', 'commission_value', 'final_amount', 'total_amount', 'discount_amount', 'extra_fee'];
        $bool  = ['is_active', 'is_direct_sale', 'auto_commission_enabled'];
        $skip  = ['updated_at', 'created_at', 'deleted_at', 'id', 'remember_token', 'password', 'ui_settings', 'synced_at', 'seen'];

        $fmt = function ($field, $v) use ($money, $bool) {
            if (in_array($field, $bool, true)) {
                return $v ? 'Có' : 'Không';
            }
            if ($v === null || $v === '') {
                return '—';
            }
            if (is_array($v)) {
                return '(đã cập nhật)';
            }
            if (in_array($field, $money, true)) {
                return number_format((int) $v, 0, ',', '.') . 'đ';
            }
            if (is_numeric($v)) {
                $n = $v + 0;
                return $n == (int) $n ? number_format((int) $n, 0, ',', '.') : (string) $v;
            }
            $s = (string) $v;
            return mb_strlen($s) > 40 ? mb_substr($s, 0, 40) . '…' : $s;
        };

        $lines = [];
        foreach ($changes['after'] as $field => $newVal) {
            if (in_array($field, $skip, true)) {
                continue;
            }
            $oldVal = $changes['before'][$field] ?? null;
            if ($oldVal === $newVal) {
                continue;
            }
            $label = $labels[$field] ?? $field;
            $lines[] = $label . ': ' . $fmt($field, $oldVal) . ' → ' . $fmt($field, $newVal);
            if (count($lines) >= $max) {
                break;
            }
        }

        return implode(' · ', $lines);
    }

    /**
     * URL trỏ tới chi tiết đối tượng khi click dòng thông báo. Null nếu không có đích.
     */
    public static function detailUrl(string $modelType, $modelId): ?string
    {
        if (!$modelId) {
            return null;
        }

        return match (class_basename($modelType)) {
            'Invoice'       => route('invoices.detail', $modelId),
            'Product'       => route('products', ['open' => $modelId]),
            'Category'      => route('categories'),
            'StockCheck'    => route('products.stock-checks', ['open' => $modelId]),
            'StockTransfer' => route('products.transfers', ['open' => $modelId]),
            default         => null,
        };
    }
}
