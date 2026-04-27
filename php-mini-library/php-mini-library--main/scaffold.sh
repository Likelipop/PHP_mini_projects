#!/bin/bash

ROOT="php-mini-library"

echo "🚀 Đang khởi tạo dự án và thiết lập quyền truy cập..."

# 1. Tạo cấu trúc thư mục
mkdir -p $ROOT/public
mkdir -p $ROOT/src/Config
mkdir -p $ROOT/src/Data
mkdir -p $ROOT/src/Helpers
mkdir -p $ROOT/views

# 2. Tạo các file trống
touch $ROOT/public/index.php $ROOT/public/books.php
touch $ROOT/src/Config/config.php
touch $ROOT/src/Data/books.php
touch $ROOT/src/Helpers/functions.php
touch $ROOT/views/home.php
touch $ROOT/.gitignore $ROOT/composer.json $ROOT/README.md

# 3. CẤP QUYỀN TRUY CẬP (Fix lỗi của bạn)
# Cấp quyền 755 cho tất cả thư mục (User có toàn quyền, người khác chỉ xem)
find $ROOT -type d -exec chmod 755 {} +

# Cấp quyền 644 cho tất cả file (User có quyền đọc/ghi, người khác chỉ đọc)
find $ROOT -type f -exec chmod 644 {} +

# Đảm bảo bạn là chủ sở hữu (phòng trường hợp bạn lỡ chạy bằng sudo)
chown -R $USER:$USER $ROOT 2>/dev/null

echo "✅ Đã tạo xong và cấp quyền ghi cho Đạt!"
ls -la $ROOT