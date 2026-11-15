# Kiến trúc xử lý dữ liệu

Các chức năng xử lý dữ liệu phải dùng luồng:

```text
Controller → Form Request → Service → Repository → Model
```

- **Controller:** nhận request, chuyển dữ liệu đã validate sang Service, trả view,
  JSON, file response hoặc redirect. Không truy vấn Model, không validate inline,
  không mở transaction hoặc thực hiện nghiệp vụ trong controller.
- **Form Request:** validation và authorization cho dữ liệu đầu vào, gồm lọc danh
  sách, upload, đổi trạng thái, sắp xếp và ID tài nguyên. Controller chỉ truyền
  dữ liệu đã validate vào Service. Trang không nhận dữ liệu đầu vào không cần
  tạo Form Request rỗng.
- **Service:** xử lý nghiệp vụ, điều phối Repository, transaction và vòng đời file.
  Nhận array đã validate hoặc tham số có kiểu; không phụ thuộc Request HTTP.
  Không chạy Eloquent query hoặc gọi save/update/delete trực tiếp trên Model.
- **Repository:** truy vấn, khóa bản ghi, tạo Model, ghi và xóa dữ liệu qua Model.
  Không xử lý request, session, view hoặc redirect.
- **Model:** cấu hình bảng, fillable, cast, quan hệ và hành vi gắn với thực thể.

Dùng constructor injection với Service và Repository cụ thể. Các lớp hiện có nằm
trong `app/Services/Admin` và `app/Repositories/Admin`. Chỉ thêm interface khi có
nhu cầu thay thế implementation; không cần BaseRepository cho các module hiện tại.

Validation về kiểu/định dạng đặt trong Form Request. Các điều kiện phụ thuộc dữ
liệu đang khóa, như danh sách ID khi đổi thứ tự, phải được kiểm tra trong transaction
của Service. Repository thực hiện các truy vấn và cập nhật tương ứng.

Khi lưu ảnh, Service dọn file vừa tạo nếu transaction lỗi và chỉ xóa file cũ sau
khi transaction thành công. Các thay đổi schema vẫn phải đi qua migration.
