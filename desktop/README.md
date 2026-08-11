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

## Đóng gói ra file cài đặt (.exe)
```bash
cd desktop
npm install
npm run dist
```
File cài nằm ở `desktop/dist/` (VD `CVHQ POS Setup 1.0.0.exe`). Gửi file này cho các máy,
cài xong sẽ có **icon ngoài desktop + Start Menu**, mở ra là app CVHQ toàn màn hình.

## Icon (tuỳ chọn)
Đặt file `build/icon.ico` (256×256) để app + shortcut có logo riêng. Nếu chưa có, có thể build
không cần icon: xoá 2 dòng `"icon": "build/icon.ico"` trong `package.json` (dùng icon mặc định).

## Ghi chú
- Chỉ mở được 1 cửa sổ (mở lại sẽ focus cửa sổ đang chạy).
- Link khác domain (web WooCommerce, tải file...) mở bằng trình duyệt mặc định.
- Mất mạng: tự thử tải lại sau 3 giây.
- Muốn build cho macOS/Linux: đổi `--win` trong script `dist` và chạy trên đúng HĐH đó.
