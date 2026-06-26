# Giới thiệu Công nghệ & Môi trường phát triển

Tài liệu này giới thiệu tổng quan về ngôn ngữ lập trình, công nghệ, phần mềm hỗ trợ
lập trình, cách khởi tạo môi trường phát triển (VS Code + Laragon) và cơ sở dữ liệu
được sử dụng trong dự án.

---

## 1. Ngôn ngữ lập trình

| Ngôn ngữ | Vai trò trong dự án | Phiên bản |
|----------|---------------------|-----------|
| **PHP** | Ngôn ngữ chính, xử lý logic phía máy chủ (backend) | 8.2 trở lên |
| **HTML / Blade** | Xây dựng giao diện, template động phía server | — |
| **CSS** | Định dạng, trình bày giao diện | — |
| **JavaScript** | Tương tác phía trình duyệt (frontend) | ES Module |
| **SQL** | Truy vấn và thao tác cơ sở dữ liệu | — |

> **PHP** là ngôn ngữ kịch bản (scripting) phía máy chủ, phổ biến nhất cho lập trình
> web. Toàn bộ nghiệp vụ của hệ thống (xử lý dữ liệu, xác thực người dùng, kết nối
> cơ sở dữ liệu...) được viết bằng PHP.

---

## 2. Công nghệ & Framework

| Công nghệ | Mô tả | Phiên bản |
|-----------|-------|-----------|
| **Laravel** | Framework PHP mạnh nhất hiện nay, cung cấp sẵn cấu trúc MVC, định tuyến (routing), ORM (Eloquent), bảo mật, migration... giúp phát triển nhanh và an toàn | 12.x |
| **Livewire** | Thư viện giúp xây dựng giao diện động (reactive) bằng PHP mà không cần viết nhiều JavaScript | 4.x |
| **Tailwind CSS** | Framework CSS theo hướng "utility-first", tạo giao diện đẹp và responsive nhanh chóng | 4.x |
| **Vite** | Công cụ đóng gói (bundler) và biên dịch tài nguyên frontend (CSS, JS) với tốc độ cao | 7.x |
| **Eloquent ORM** | Bộ ánh xạ đối tượng – quan hệ (đi kèm Laravel), thao tác cơ sở dữ liệu bằng đối tượng PHP thay vì viết SQL thủ công | (đi kèm Laravel) |
| **Maatwebsite/Excel** | Thư viện xuất / nhập dữ liệu Excel | 3.1 |

### Mô hình kiến trúc — MVC

Dự án xây dựng theo mô hình **MVC (Model – View – Controller)** của Laravel:

- **Model**: Đại diện cho bảng dữ liệu, chứa logic nghiệp vụ (thư mục `app/Models`).
- **View**: Giao diện hiển thị, dùng template **Blade** + **Livewire** (`resources/views`).
- **Controller**: Tiếp nhận yêu cầu, điều phối xử lý (`app/Http/Controllers`).

---

## 3. Phần mềm hỗ trợ lập trình

| Phần mềm | Vai trò |
|----------|---------|
| **Visual Studio Code (VS Code)** | Trình soạn thảo mã nguồn (code editor) chính |
| **Laragon** | Môi trường phát triển web "tất cả trong một" (Apache/Nginx + PHP + MySQL) |
| **Composer** | Trình quản lý thư viện (package manager) cho PHP |
| **Node.js & npm** | Môi trường chạy và quản lý thư viện JavaScript (phục vụ Vite, Tailwind) |
| **Git** | Quản lý phiên bản mã nguồn |

### Tiện ích (Extensions) nên cài cho VS Code

- **PHP Intelephense** – gợi ý code, kiểm tra lỗi PHP.
- **Laravel Blade Snippets** / **Laravel Extension Pack** – hỗ trợ cú pháp Blade & Laravel.
- **Tailwind CSS IntelliSense** – gợi ý class Tailwind.
- **GitLens** – hỗ trợ Git trực quan.
- **EditorConfig for VS Code** – đồng bộ định dạng code theo file `.editorconfig`.

---

## 4. Khởi tạo môi trường phát triển

### Bước 1 — Cài đặt phần mềm

1. Tải và cài **Laragon** (bản Full): https://laragon.org
   → Laragon đã tích hợp sẵn **PHP, MySQL, Apache/Nginx và Composer**.
2. Tải và cài **VS Code**: https://code.visualstudio.com
3. Tải và cài **Node.js** (bản LTS): https://nodejs.org

### Bước 2 — Đưa mã nguồn vào Laragon

- Đặt thư mục dự án vào: `C:\laragon\www\` (ví dụ `C:\laragon\www\CVHQ`).
- Mở **Laragon → Start All** để khởi động Apache/Nginx và MySQL.

### Bước 3 — Cài đặt thư viện & cấu hình

Mở terminal trong VS Code (`Ctrl + ` `) tại thư mục dự án và chạy lần lượt:

```bash
# 1. Cài thư viện PHP
composer install

# 2. Tạo file cấu hình môi trường
copy .env.example .env

# 3. Tạo khóa ứng dụng
php artisan key:generate

# 4. Cài thư viện frontend
npm install
```

### Bước 4 — Tạo & nạp dữ liệu cơ sở dữ liệu

```bash
# Tạo các bảng trong CSDL
php artisan migrate

# (Tùy chọn) Nạp dữ liệu mẫu
php artisan db:seed
```

### Bước 5 — Chạy dự án

```bash
# Biên dịch & theo dõi tài nguyên frontend (chạy ở 1 cửa sổ terminal)
npm run dev

# Khởi động máy chủ (nếu không dùng virtual host của Laragon)
php artisan serve
```

> Truy cập ứng dụng tại địa chỉ Laragon cung cấp (ví dụ `http://cvhq.test`) hoặc
> `http://127.0.0.1:8000` nếu dùng `php artisan serve`.

---

## 5. Cơ sở dữ liệu

| Thành phần | Mô tả |
|------------|-------|
| **Hệ quản trị CSDL** | **MySQL** (đi kèm Laragon) — có thể dùng **SQLite** cho môi trường thử nghiệm |
| **Công cụ quản lý** | **HeidiSQL** / **phpMyAdmin** (tích hợp trong Laragon) |
| **Truy cập từ code** | Thông qua **Eloquent ORM** và **Query Builder** của Laravel |
| **Quản lý cấu trúc bảng** | **Migration** (`database/migrations`) — tạo/sửa bảng bằng code, dễ đồng bộ giữa các máy |

### Cấu hình kết nối trong file `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ten_co_so_du_lieu
DB_USERNAME=root
DB_PASSWORD=
```

> Mặc định tài khoản MySQL của Laragon là **username: `root`**, **password: để trống**.
> Hãy tạo cơ sở dữ liệu mới trong HeidiSQL/phpMyAdmin rồi điền tên vào `DB_DATABASE`.

---

## 6. Tổng kết kiến trúc công nghệ

```
┌─────────────────────────────────────────────────────────┐
│                 NGƯỜI DÙNG (Trình duyệt)                  │
└───────────────────────────┬─────────────────────────────┘
                            │ HTTP
┌───────────────────────────▼─────────────────────────────┐
│   Frontend: Blade + Livewire + Tailwind CSS (qua Vite)   │
├──────────────────────────────────────────────────────────┤
│        Backend: PHP 8.2 + Laravel 12 (mô hình MVC)       │
├──────────────────────────────────────────────────────────┤
│              Cơ sở dữ liệu: MySQL (Eloquent ORM)         │
└──────────────────────────────────────────────────────────┘
      Môi trường: Laragon  |  Soạn thảo: VS Code  |  Git
```

---

*Tài liệu được biên soạn cho dự án CVHQ.*
