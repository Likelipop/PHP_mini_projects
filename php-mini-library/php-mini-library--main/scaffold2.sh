#!/bin/bash

# Tên thư mục gốc
PROJECT_NAME="php-mini-workshop"

echo "🚀 Bắt đầu khởi tạo project: $PROJECT_NAME..."

# Tạo cấu trúc thư mục
mkdir -p $PROJECT_NAME/public
mkdir -p $PROJECT_NAME/src/Controllers
mkdir -p $PROJECT_NAME/src/Data
mkdir -p $PROJECT_NAME/src/Support
mkdir -p $PROJECT_NAME/views
mkdir -p $PROJECT_NAME/config
mkdir -p $PROJECT_NAME/storage/logs

# Di chuyển vào thư mục project
cd $PROJECT_NAME

# Tạo các file trong public/
touch public/index.php

# Tạo các file trong src/Controllers/
touch src/Controllers/HomeController.php
touch src/Controllers/EventController.php
touch src/Controllers/RegistrationController.php

# Tạo file trong src/Data/
touch src/Data/events.php

# Tạo các file trong src/Support/
touch src/Support/Env.php
touch src/Support/Response.php

# Tạo file trong views/
touch views/home.php

# Tạo file trong config/
touch config/app.php

# Tạo file .gitkeep để giữ thư mục trống trên git
touch storage/logs/.gitkeep

# Tạo các file cấu hình ở root
touch .env
touch .env.example
touch .gitignore
touch README.md

# Khởi tạo nội dung cơ bản cho composer.json
cat <<EOT >> composer.json
{
    "name": "ds/php-mini-workshop",
    "description": "Mini workshop project",
    "autoload": {
        "psr-4": {
            "App\\\\": "src/"
        }
    },
    "authors": [
        {
            "name": "Trần Tiến Đạt"
        }
    ],
    "require": {}
}
EOT

# Khởi tạo nội dung mẫu cho .gitignore
cat <<EOT >> .gitignore
/vendor/
.env
storage/logs/*.log
EOT

echo "✅ Đã tạo xong cấu trúc project!"
echo "📂 Gõ 'cd $PROJECT_NAME' để bắt đầu làm việc."