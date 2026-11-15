# Coding rule — JavaScript

- Dùng 2 space cho mỗi cấp thụt lề; không dùng tab.
- Mỗi khối lồng nhau tăng thêm 2 space so với khối bao ngoài.
- Áp dụng cho file `.js`, `.mjs`, `.cjs` và nội dung JavaScript trong thẻ `<script>` của Blade.
- File phải kết thúc bằng ký tự xuống dòng LF.

```js
document.addEventListener('DOMContentLoaded', () => {
  const button = document.querySelector('[data-rsvp]');

  if (button) {
    button.addEventListener('click', () => {
      button.disabled = true;
    });
  }
});
```
