# Môi trường phát triển

Khởi động từ thư mục gốc:

```bash
docker compose up -d
```

Service `frontend` dùng Node.js 22, cài dependency theo `package-lock.json`, build
CSS/JS lần đầu rồi chạy `vite build --watch`. Khi CSS, JS hoặc Blade thay đổi,
asset trong `apps/public/build` được build lại; tải lại trang để xem kết quả.
Service `app` chờ lần build đầu thành công trước khi khởi động.

Dependency frontend nằm trong volume riêng `frontend_node_modules`, không ghi đè
`apps/node_modules` trên máy host. Lifecycle script được bỏ qua trong container để
không cài Git hook của máy phát triển.

Xem trạng thái build:

```bash
docker compose logs -f frontend
```

Sau khi thay đổi `package.json` hoặc `package-lock.json`, chạy:

```bash
docker compose restart frontend
```

Lần khởi động đầu cần kết nối npm registry để cài dependency. Polling được bật để
theo dõi thay đổi file qua bind mount.
