# Coding rules — Wedding

Áp dụng cho mã nguồn trong `apps/`, cấu hình trong `docker/`, `docker-compose.yml` và script trong `scripts/`. Đọc tài liệu này trước khi thêm tính năng hoặc sửa lỗi.

Quy tắc theo ngôn ngữ:

- [CSS](css.md): lùi 4 space, một dòng trống giữa các khối selector.
- [JavaScript](js.md): lùi 2 space.
- [PHP và Blade](php.md): lùi 4 space, bao gồm nội dung bên trong Blade directive.

Mọi file văn bản phải kết thúc bằng ký tự xuống dòng LF (final newline/EOF newline), không thêm chữ `EOF` vào nội dung file. Quy tắc này áp dụng cả file mới và file được chỉnh sửa.

## 1. Công nghệ và phạm vi thay đổi

- Backend: Laravel 12.x, môi trường chạy PHP 8.3, MySQL 8.4.
- Giao diện: Blade, Vite và Tailwind CSS theo cấu hình hiện có trong `apps/package.json`.
- Kiểm thử: PHPUnit; định dạng PHP: Laravel Pint.
- Giữ thay đổi tập trung vào yêu cầu; không tự ý nâng major version, thêm framework hoặc refactor phần không liên quan.
- Kiểm tra dependency có sẵn trước khi thêm package. Khi thêm hoặc cập nhật dependency, cập nhật cả manifest và lock file tương ứng.
- Không sửa trực tiếp `vendor/`, `node_modules/` hoặc file sinh tự động.

## 2. Định dạng và đặt tên

- Tuân theo `.editorconfig` và `apps/.editorconfig`: UTF-8, LF, newline cuối file; dùng space, CSS/PHP/Blade 4 space, JavaScript/YAML 2 space.
- Dùng Laravel Pint để định dạng PHP. Không định dạng lại hàng loạt file ngoài phạm vi thay đổi.
- Tên class dùng `PascalCase`; tên phương thức và biến PHP dùng `camelCase`; hằng số dùng `UPPER_SNAKE_CASE`.
- Tên bảng, cột và khóa cấu hình dùng `snake_case`; bảng thường dùng danh từ số nhiều.
- Tên route dùng dấu chấm, ví dụ `guests.index`, `guests.store`.
- Dùng tên tiếng Anh mô tả rõ ý nghĩa trong mã nguồn. Nội dung hiển thị cho người dùng viết bằng tiếng Việt khi chưa có yêu cầu khác.
- Khai báo kiểu tham số và kiểu trả về khi phù hợp. Dùng PHPDoc cho kiểu collection, generic hoặc thông tin mà type hint chưa thể diễn đạt.
- Comment giải thích lý do hoặc ràng buộc nghiệp vụ; tránh diễn giải lại từng dòng code.

## 3. Tổ chức Laravel

- Khai báo web route trong `routes/web.php`; dùng named route khi tạo URL.
- Controller nhận request, gọi xử lý nghiệp vụ và trả response; tránh đặt toàn bộ nghiệp vụ trong controller hoặc route closure.
- Dùng Form Request cho validation phức tạp hoặc có thể tái sử dụng; validation ngắn, đơn giản có thể đặt trong controller.
- Dùng Policy hoặc Gate cho phân quyền; không chỉ ẩn nút trên giao diện.
- Model quản lý quan hệ, cast, scope và hành vi gắn trực tiếp với dữ liệu.
- Chỉ tách Action hoặc Service khi nghiệp vụ cần dùng lại hoặc đã đủ phức tạp; không tạo lớp trung gian rỗng chỉ để chuyển tiếp lời gọi.
- Ưu tiên dependency injection; tránh gọi `new` trực tiếp với dependency cần thay thế khi test.
- Đọc biến môi trường qua file `config/`, sau đó dùng `config()` trong mã ứng dụng.
- Xử lý lỗi bằng cơ chế exception của Laravel; không bắt mọi exception rồi trả thành công hoặc bỏ qua lỗi.

## 4. Cơ sở dữ liệu

- Mọi thay đổi schema phải có migration. Không sửa schema thủ công qua phpMyAdmin rồi bỏ qua migration.
- Không sửa migration đã triển khai để thay đổi schema; tạo migration mới.
- Khai báo foreign key, index và unique constraint theo quan hệ và yêu cầu nghiệp vụ.
- Dùng transaction cho nhóm thao tác ghi cần thành công hoặc thất bại cùng nhau.
- Dùng Eloquent hoặc Query Builder với binding; không nối trực tiếp input vào SQL.
- Kiểm soát mass assignment bằng `$fillable` hoặc cơ chế tương đương. Chỉ truyền dữ liệu đã validate và được phép cập nhật.
- Tránh N+1 bằng eager loading khi đọc quan hệ trong vòng lặp; dùng phân trang cho danh sách có thể tăng lớn.
- Dùng factory và seeder cho dữ liệu kiểm thử hoặc dữ liệu mẫu; không đưa thông tin cá nhân thật vào repository.
- Với nghiệp vụ phụ thuộc hành vi riêng của MySQL, kiểm thử trên MySQL; không chỉ dựa vào SQLite.

## 5. Giao diện

- Blade chỉ xử lý trình bày; chuẩn bị dữ liệu trong controller hoặc lớp xử lý phù hợp.
- Mặc định dùng `{{ ... }}` để escape dữ liệu. Chỉ dùng `{!! ... !!}` với nội dung đã được làm sạch và có lý do rõ ràng.
- Form thay đổi dữ liệu phải có CSRF token; dùng đúng HTTP method.
- Dùng Blade component cho phần giao diện lặp lại có ý nghĩa.
- Ưu tiên utility Tailwind hiện có; hạn chế CSS và JavaScript inline khó tái sử dụng.
- Form cần label, thông báo validation và giữ lại input hợp lệ khi xảy ra lỗi.
- Kiểm tra hiển thị trên mobile và desktop; bảo đảm thao tác cơ bản bằng bàn phím.

## 6. Bảo mật và cấu hình

- Không commit `.env`, `APP_KEY`, mật khẩu, token hoặc credential thật. Chỉ ghi giá trị mẫu trong `.env.example`.
- Khi thêm cấu hình bắt buộc, cập nhật `.env.example` và hướng dẫn sử dụng.
- Không log mật khẩu, token, toàn bộ request có dữ liệu nhạy cảm hoặc thông tin cá nhân không cần thiết.
- Xác thực và phân quyền tại server cho mọi thao tác cần bảo vệ; validation không thay thế phân quyền.
- Upload phải kiểm tra loại file, kích thước và quyền truy cập; không tin tên file từ client.
- Không phục vụ thư mục gốc Laravel qua web; document root phải trỏ đến `public/`.
- Cấu hình Docker hiện tại dành cho phát triển local. Khi triển khai production, dùng secret riêng và tắt `APP_DEBUG`.

## 7. Docker và script

- Chạy lệnh Docker Compose từ thư mục gốc repository; mã Laravel nằm trong `apps/`.
- Giữ thao tác dọn dẹp trong phạm vi Compose project `wedding`. Không dùng `docker system prune` hoặc xóa toàn bộ container/image của máy.
- `scripts/setup.sh` là lệnh reset môi trường: xóa container, network, volume và image build của dự án trước khi dựng lại. Dữ liệu MySQL sẽ mất.
- Khi chỉ cần khởi động môi trường hiện có, dùng `docker compose up -d`; không dùng script reset thay cho lệnh khởi động thông thường.
- Script Bash dùng `set -Eeuo pipefail`, quote biến và dừng khi có lỗi.
- Không dùng quyền `777` để xử lý lỗi ghi file. Chỉ cấp quyền cần thiết cho `storage/` và `bootstrap/cache/`.
- Không thêm thao tác xóa dữ liệu hoặc mở rộng phạm vi dọn dẹp mà không nêu rõ trong thay đổi.

## 8. Kiểm tra trước khi bàn giao

Thực hiện các kiểm tra liên quan đến thay đổi. Các lệnh `exec` bên dưới yêu cầu service `app` đang chạy:

```bash
# Kiểm tra định dạng PHP; thêm đường dẫn tương đối trong apps/ để giới hạn phạm vi.
docker compose exec -T app vendor/bin/pint --test

# Chạy test Laravel.
docker compose exec -T app php artisan test

# Khi sửa cấu hình Docker hoặc Bash.
docker compose config --quiet
bash -n scripts/setup.sh

# Khi sửa frontend; chạy trên máy có Node.js và đã cài dependency.
cd apps
npm run build
```

- Thêm hoặc cập nhật test cho lỗi đã sửa, validation, phân quyền và nghiệp vụ mới có hành vi đáng kiểm chứng.
- Dùng Feature test cho HTTP, database và tích hợp Laravel; Unit test cho logic độc lập.
- Không viết test chỉ để lặp lại implementation hoặc cho thay đổi tài liệu, định dạng đơn giản.
- Test phải độc lập, không dùng database thật hoặc gọi dịch vụ ngoài không kiểm soát. Dùng fake/mock khi phù hợp.
- Khi có migration mới, kiểm tra áp dụng trên database phát triển hoặc kiểm thử trước khi bàn giao.
- Báo rõ thay đổi, lệnh đã kiểm tra và giới hạn chưa kiểm tra; không khẳng định test đạt nếu chưa chạy.

## 9. Commit và review

- Mỗi commit tập trung vào một mục đích. Gợi ý tiền tố: `feat:`, `fix:`, `refactor:`, `test:`, `docs:`, `chore:`.
- Review cả file mới, file cấu hình và lock file; loại bỏ debug code, `dd()`, `dump()` và log tạm.
- Mô tả thay đổi dữ liệu, biến môi trường mới và bước vận hành cần thực hiện nếu có.
- Không đưa file cache, build artifact hoặc credential vào commit.

## 10. Tự động format khi commit bằng Lefthook

Yêu cầu Node.js >= 22.22.1 và dependency phát triển. Cài từ thư mục gốc:

```bash
npm --prefix apps ci
```

Script `prepare` tự cài hook `pre-commit` qua Lefthook. Nếu dependency đã được cài,
hoặc trước đó cài khi chưa có Git repository, chạy:

```bash
npm --prefix apps run hooks:install
```

Khi chạy `git commit`, Lefthook gọi `lint-staged` để xử lý các file đã stage:

- PHP: Laravel Pint với preset Laravel, lùi 4 space.
- Blade: blade-formatter, lùi 4 space bên trong directive.
- JavaScript: Prettier, lùi 2 space.
- CSS: Prettier và Stylelint, lùi 4 space, mỗi selector trong nhóm trên một dòng và một dòng trống giữa các khối.
- YAML: Prettier, lùi 2 space.
- File văn bản UTF-8: chuẩn hóa LF và newline cuối file; bỏ qua file nhị phân, symlink, dependency và thư mục sinh tự động.

Các thay đổi format được tự đưa vào commit. `lint-staged` giữ lại phần chỉnh sửa
chưa stage trong file chỉ stage một phần, và mặc định tạo stash dự phòng khi repository
đã có commit. Nếu formatter báo lỗi cú pháp hoặc công cụ không chạy được, commit bị chặn;
sửa lỗi rồi commit lại. Không cần tự `git add` phần formatter đã sửa.

Có thể chạy thủ công trên các file đã stage:

```bash
npm --prefix apps run format:staged
```

PHP cần `apps/vendor/` đã được cài bằng Composer. Hook ưu tiên PHP local có các
extension `mbstring`, `dom`, `xml`; nếu thiếu, dùng `docker compose run --rm --no-deps`
với service `app` (cần Docker và image của dự án). Lần chạy Docker đầu có thể mất thời gian.
Hook phải được cài trên từng máy sau khi clone; cài với `--ignore-scripts` cần chạy
`hooks:install` thủ công.

Các quy tắc về nghiệp vụ, kiến trúc, phân quyền, bảo mật và đặt tên vẫn cần review
và test phù hợp; formatter không kiểm chứng được toàn bộ nội dung coding rule.
