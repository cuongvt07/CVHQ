# CVHQ — Nhánh `stagging`: Chức năng & Bảng dữ liệu

> Tài liệu rà soát nhánh **stagging** (commit `25c2be9`) — dùng cho server staging `150.95.111.3:8000`.
> Mục đích: biết nhánh này có chức năng gì và **những bảng nào là cần thiết nhất**.

---

## 1. Chức năng đang có trên `stagging`

Theo menu (sidebar) và routes thực tế:

| Nhóm | Chức năng | Route |
|---|---|---|
| **Tổng quan** | Dashboard | `dashboard` |
| **Hàng hoá** | Danh mục · Sản phẩm · Báo cáo bán hàng | `categories`, `products`, `reports.sales` |
| **Tồn kho** | Kiểm kho · Dự toán nhập hàng · Chuyển hàng CN | `products.stock-checks`, `products.restock`, `products.transfers` |
| **Hoa hồng** | Bảng hoa hồng · Báo cáo hoa hồng · Cấu hình hoa hồng | `commissions`, `reports.commissions`, `commissions.settings` |
| **Giao dịch** | Trạm bán hàng POS · Hóa đơn · Danh sách trả hàng · Khách hàng | `pos`, `invoices`, `invoices.returns`, `customers` |
| **Hệ thống** | Nhân viên · Quản lý chi nhánh · Cài đặt cửa hàng · Lịch sử hệ thống | `users`, `branches`, `system.settings`, `system.logs` |
| *(ẩn)* | Đơn WP (WooCommerce) — **đã ẩn khỏi menu** | `wp.orders`, `wp.webhook` |

**Không có trên stagging** (chỉ có ở `master`): module **Chấm công / Tính lương**, **Báo cáo chi tiết**, **Đơn Mail** (bản nâng cấp của Đơn WP).

---

## 2. Bảng dữ liệu — phân theo mức độ cần thiết

Tổng cộng **24 bảng**.

### 2.1. ⭐ CỐT LÕI — thiếu là hệ thống không chạy

| Bảng | Vai trò |
|---|---|
| **`users`** | Tài khoản nhân viên: đăng nhập (`username`), vai trò (`role`), **phân quyền** (`permissions` JSON), chi nhánh làm việc (`work_branch`), ngừng hoạt động (`is_active`), nhận hoa hồng (`can_receive_commission`), tuỳ chỉnh giao diện (`ui_settings`). |
| **`products`** | Sản phẩm: `sku`, tên, **giá bán / giá gốc**, **tồn kho**, vị trí cất, hoa hồng (`commission_*`), danh mục, ảnh. Trung tâm của POS + kho. |
| **`categories`** | Danh mục sản phẩm. |
| **`customers`** | Khách hàng (tên, SĐT, địa chỉ, khu vực giao). |
| **`invoices`** | Hóa đơn: mã, chi nhánh, khách, NV bán, kênh bán, tiền hàng/giảm/phí/phải trả, **hoa hồng + chia hoa hồng**, trạng thái (`Completed`/`Returned`/`Cancelled`), phương thức thanh toán. |
| **`invoice_items`** | Dòng hàng của hóa đơn (SP, SL, đơn giá, hoa hồng, thành tiền). |
| **`system_settings`** | Cấu hình chung: **tên app + logo**, thông tin cửa hàng, **mức hoa hồng theo khoảng giá**. |

### 2.2. 🔧 NGHIỆP VỤ CHÍNH — mất là mất tính năng

| Bảng | Vai trò |
|---|---|
| **`stock_histories`** | **THẺ KHO** — ghi **mọi biến động tồn**: bán, trả hàng, nhập, kiểm kho, chuyển hàng, điều chỉnh tay (kèm **lý do**). ⇒ Bảng quan trọng nhất để **truy vết khi lệch kho**. |
| **`activity_logs`** | Nhật ký thao tác (thêm/sửa/xoá) — dùng cho **Lịch sử hệ thống** và **thông báo trên topbar**. Lưu `changes` (cũ → mới). |
| **`branches`** | Chi nhánh (HN / SG) — dùng cho POS, chuyển hàng, báo cáo. |
| **`stock_checks`** | Phiếu kiểm kho (mã, chi nhánh, người tạo, trạng thái, `balanced_at`). |
| **`stock_check_items`** | Dòng SP trong phiếu kiểm (tồn hệ thống / thực tế / lệch). |
| **`stock_check_logs`** | Nhật ký thao tác trong phiên kiểm kho. |
| **`stock_transfers`** | Phiếu chuyển hàng giữa 2 chi nhánh (mã, tuyến, trạng thái, người tạo/xác nhận). |
| **`stock_transfer_items`** | Dòng SP trong phiếu chuyển (số gửi, thực nhận, lý do điều chỉnh). |
| **`invoice_shippings`** | Thông tin giao hàng gắn với hóa đơn. |

### 2.3. ⚙️ HẠ TẦNG LARAVEL

| Bảng | Vai trò | Ghi chú |
|---|---|---|
| `sessions` | Phiên đăng nhập | **Cần** (không có là không login được) |
| `cache`, `cache_locks` | Cache | Cần nếu `CACHE_STORE=database` |
| `password_reset_tokens` | Quên mật khẩu | Ít dùng |
| `jobs`, `job_batches`, `failed_jobs` | Hàng đợi | **Gần như không dùng** — stagging **không chạy queue worker**, import chạy đồng bộ |

### 2.4. 💤 CÓ NHƯNG KHÔNG DÙNG

| Bảng | Ghi chú |
|---|---|
| `wp_orders` | Đơn WooCommerce. Code còn nhưng **tab đã ẩn khỏi menu** trên stagging → bảng tồn tại, không dùng. Có thể bỏ qua khi backup/khởi tạo. |

---

## 3. Tóm tắt: bảng cần thiết NHẤT

Nếu chỉ giữ tối thiểu để chạy được nghiệp vụ chính (bán hàng + kho):

```
users · products · categories · customers
invoices · invoice_items
stock_histories        ← thẻ kho, quan trọng nhất để truy vết
system_settings        ← tên app, logo, mức hoa hồng
sessions               ← để đăng nhập được
```

Thêm nếu dùng đủ tính năng:
```
branches · activity_logs · invoice_shippings
stock_checks · stock_check_items · stock_check_logs      (Kiểm kho)
stock_transfers · stock_transfer_items                    (Chuyển hàng CN)
```

---

## 4. Quan hệ chính (rút gọn)

```
users ──< invoices >── customers
             │
             └──< invoice_items >── products ──> categories
                                        │
                                        └──< stock_histories   (mọi biến động tồn)

products ──< stock_check_items >── stock_checks ──> users
products ──< stock_transfer_items >── stock_transfers ──> users
branches: mã chi nhánh (hn/sg) dùng trong invoices.branch, users.work_branch,
          stock_transfers.from_branch/to_branch
activity_logs: (model_type, model_id) trỏ tới Invoice / Product / StockCheck / StockTransfer
```

---

## 5. Ghi chú vận hành

- **Xóa mềm (SoftDeletes)**: `users`, `products`, `invoices`, `invoice_items`… → dữ liệu cũ vẫn tra được. Tên NV bán lưu ở cột text `invoices.seller_name` nên **xóa nhân viên không mất tên trên hóa đơn cũ**.
- **Sau khi build phải chạy**:
  ```bash
  php artisan migrate --force
  php artisan optimize:clear    # xoá route/config/view cache
  ```
- **DB**: MariaDB 11.4, database `anvwclyo_cvhq` (container `cvhq-db`). Dump bằng `mariadb-dump` (không phải `mysqldump`).
- Deploy nhanh: `./deploy.sh` (trong repo).

---

*Cập nhật theo nhánh `stagging` @ `25c2be9`.*
