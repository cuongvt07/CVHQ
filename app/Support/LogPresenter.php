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
