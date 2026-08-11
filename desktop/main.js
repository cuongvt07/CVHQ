const { app, BrowserWindow, shell } = require('electron');
const path = require('path');

// URL server CVHQ. Đổi tại đây, hoặc đặt biến môi trường CVHQ_URL khi chạy.
const APP_URL = process.env.CVHQ_URL || 'https://cvhq.boongcake.com';

let win;

function createWindow() {
    win = new BrowserWindow({
        width: 1280,
        height: 800,
        show: false,
        autoHideMenuBar: true, // ẩn thanh menu (nhấn Alt để hiện); F5/Ctrl+R vẫn reload được
        icon: path.join(__dirname, 'build', 'icon.ico'),
        webPreferences: {
            contextIsolation: true,
            nodeIntegration: false,
        },
    });

    win.maximize();
    win.show();
    win.loadURL(APP_URL);

    const sameHost = (url) => {
        try { return new URL(url).host === new URL(APP_URL).host; }
        catch (e) { return true; }
    };

    // Link khác domain (target=_blank, web WooCommerce, ...) -> mở bằng trình duyệt mặc định.
    win.webContents.setWindowOpenHandler(({ url }) => {
        if (!sameHost(url)) { shell.openExternal(url); return { action: 'deny' }; }
        return { action: 'allow' };
    });
    win.webContents.on('will-navigate', (e, url) => {
        if (!sameHost(url)) { e.preventDefault(); shell.openExternal(url); }
    });

    // Mất mạng/không tải được -> thử lại sau 3s.
    win.webContents.on('did-fail-load', (e, code, desc, validatedURL, isMainFrame) => {
        if (isMainFrame) setTimeout(() => win && win.loadURL(APP_URL), 3000);
    });
}

// Chỉ cho 1 cửa sổ (mở lại thì focus cửa sổ cũ).
if (!app.requestSingleInstanceLock()) {
    app.quit();
} else {
    app.on('second-instance', () => {
        if (win) { if (win.isMinimized()) win.restore(); win.focus(); }
    });

    app.whenReady().then(() => {
        createWindow();
        app.on('activate', () => {
            if (BrowserWindow.getAllWindows().length === 0) createWindow();
        });
    });

    app.on('window-all-closed', () => {
        if (process.platform !== 'darwin') app.quit();
    });
}
