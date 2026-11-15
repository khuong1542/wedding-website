#!/usr/bin/env bash
set -Eeuo pipefail

PROJECT_ROOT="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

command -v docker >/dev/null 2>&1 || { echo 'Cần cài Docker trước khi chạy.' >&2; exit 1; }
docker compose version >/dev/null
docker info >/dev/null

# Cố định project để thao tác dọn dẹp luôn nằm trong phạm vi wedding.
compose=(docker compose --project-name wedding --file "$PROJECT_ROOT/docker-compose.yml")
"${compose[@]}" config --quiet

echo 'Dọn container, network, volume (bao gồm dữ liệu MySQL) và image build của wedding...'
# --rmi local chỉ xóa image build của dự án; giữ image MySQL/phpMyAdmin có thể dùng chung.
# Không xóa mã nguồn trong apps/ và không chạy docker system prune.
"${compose[@]}" down --remove-orphans --volumes --rmi local

echo 'Build lại không dùng cache và khởi chạy...'
"${compose[@]}" build --pull --no-cache
"${compose[@]}" pull db phpmyadmin
echo 'Cài dependency và chuẩn bị Laravel...'
"${compose[@]}" run --rm --no-deps app sh -ec '
    composer install --no-interaction --prefer-dist
    if [ ! -f .env ]; then cp .env.example .env; fi
    if ! grep -qE "^APP_KEY=.+" .env; then php artisan key:generate --force; fi
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    chown -R www-data:www-data storage bootstrap/cache
    chmod -R ug+rwX storage bootstrap/cache
'
"${compose[@]}" up --detach --wait --wait-timeout 240
"${compose[@]}" exec -T --user www-data app php artisan migrate --force
"${compose[@]}" ps

echo 'Hoàn tất. Cổng mặc định: ứng dụng http://localhost:8080 | phpMyAdmin http://localhost:8081'
echo 'Đặt mã nguồn PHP trong apps/. Tài khoản DB mặc định: wedding / wedding_local.'
echo 'Có thể chỉnh cổng và thông tin DB bằng biến môi trường hoặc file .env tại thư mục dự án.'
