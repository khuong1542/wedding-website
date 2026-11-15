# Coding rule — CSS

- Dùng 4 space cho mỗi cấp thụt lề; không dùng tab.
- Giữa hai khối selector liên tiếp có đúng một dòng trống, kể cả bên trong `@media` hoặc các khối lồng nhau.
- Với nhóm selector dùng chung một khối khai báo, viết mỗi selector trên một dòng; không chèn dòng trống giữa các selector trong nhóm.
- File phải kết thúc bằng ký tự xuống dòng LF.

```css
.wedding-title {
    color: #333;
    font-weight: 600;
}

.wedding-card,
.wedding-panel {
    padding: 16px;
}

@media (max-width: 768px) {
    .wedding-title {
        font-size: 24px;
    }

    .wedding-card {
        padding: 12px;
    }
}
```
