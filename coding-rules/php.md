# Coding rule — PHP và Blade

- Dùng 4 space cho mỗi cấp thụt lề; không dùng tab.
- Nội dung bên trong Blade directive phải lùi thêm 4 space so với directive mở.
- Directive đóng và các nhánh như `@else`, `@elseif`, `@empty` căn cùng cấp với directive mở tương ứng.
- Mỗi directive lồng nhau tăng thêm 4 space. Áp dụng cho các khối như `@if`, `@foreach`, `@auth`, `@section` và `@php`.
- Dùng Laravel Pint cho mã PHP; kiểm tra riêng định dạng Blade khi chỉnh sửa template.
- File phải kết thúc bằng ký tự xuống dòng LF.

```php
<?php

namespace App\Services;

class GuestService
{
    public function normalizeName(string $name): string
    {
        return trim($name);
    }
}
```

```blade
@if ($guests->isNotEmpty())
    @foreach ($guests as $guest)
        <p>{{ $guest->name }}</p>
    @endforeach
@else
    <p>Chưa có khách mời.</p>
@endif
```
