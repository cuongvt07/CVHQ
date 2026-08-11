# CVHQ POS — Desktop app (Electron wrapper)

App desktop nhẹ, mở thẳng CVHQ trên server (`https://cvhq.boongcake.com`) trong 1 cửa sổ
riêng như phần mềm cài đặt. **Dữ liệu vẫn nằm trên server** — mọi máy dùng chung, không cần
cài PHP/DB gì trên máy khách.

## Yêu cầu (máy để BUILD ra file cài)
- Node.js 18+ (https://nodejs.org)
- Windows (để build file `.exe` cho Windows)

## Chạy thử (dev)
```bash
cd desktop
npm install
npm start
```
Cửa sổ CVHQ sẽ mở. Đổi URL: sửa `APP_URL` trong `main.js`, hoặc chạy
`set CVHQ_URL=http://150.95.111.3:8000 && npm start`.

## Đóng gói app (khuyên dùng — chạy mọi máy)
```bash
cd desktop
npm install
npm run pack
```
Ra thư mục **`desktop/dist/CVHQ POS-win32-x64/`** chứa **`CVHQ POS.exe`** (chạy được ngay,
không cần cài). Cách phân phối: nén cả thư mục thành `.zip` gửi cho máy khác, giải nén rồi
double-click `CVHQ POS.exe` (có thể chuột phải → Ghim vào Taskbar / Tạo shortcut ra desktop).

## Tạo file cài đặt .exe (NSIS) — tuỳ chọn, cần Developer Mode
```bash
cd desktop
npm install
npm run dist
```
Ra `desktop/dist/CVHQ POS Setup 1.0.0.exe` (có icon desktop + Start Menu, gỡ cài trong Control Panel).

> ⚠️ Trên Windows, `npm run dist` cần quyền tạo symbolic link (để giải nén công cụ ký).
> Nếu gặp lỗi `Cannot create symbolic link ... A required privilege is not held`:
> bật **Developer Mode** (Settings → Privacy & security → For developers → Developer Mode: On)
> **hoặc** mở terminal bằng **Run as Administrator** rồi chạy lại. Cách `npm run pack` ở trên
> KHÔNG dính lỗi này.

## Icon (tuỳ chọn)
Đặt file `build/icon.ico` (256×256) để app + shortcut có logo riêng. Nếu chưa có, có thể build
không cần icon: xoá 2 dòng `"icon": "build/icon.ico"` trong `package.json` (dùng icon mặc định).

## Ghi chú
- Chỉ mở được 1 cửa sổ (mở lại sẽ focus cửa sổ đang chạy).
- Link khác domain (web WooCommerce, tải file...) mở bằng trình duyệt mặc định.
- Mất mạng: tự thử tải lại sau 3 giây.
- Muốn build cho macOS/Linux: đổi `--win` trong script `dist` và chạy trên đúng HĐH đó.
