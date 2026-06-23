# StudyFlow Hub

StudyFlow Hub là một nền tảng quản lý lộ trình học tập và kho tài nguyên cá nhân (Knowledge & Resource Workspace). Dự án được xây dựng hoàn toàn bằng **PHP thuần (Vanilla PHP)** kết hợp với kiến trúc Repository/Service Pattern, tập trung mạnh vào các kỹ thuật lập trình web an toàn (Bảo vệ CSRF, Honeypot, PRG Pattern) và tối ưu truy vấn cơ sở dữ liệu.

## 🚀 Các tính năng chính

- **Quản lý Lộ trình (StudyFlows):** Tạo, xem, sửa, xóa các lộ trình học tập. Phân trang, tìm kiếm và sắp xếp an toàn bằng cơ chế Whitelist.
- **Quản lý Tài nguyên (Assets & Folders):** Tổ chức tài liệu học tập theo cấu trúc cây thư mục. Hỗ trợ ghi chú Markdown (Markdown Notes) và tải lên các file.
- **Quản lý Nhãn dán (Tags):** Gắn thẻ tài nguyên để phân loại nhanh, tìm kiếm đa chiều.
- **Bảo mật chuyên sâu:**
  - Ngăn chặn triệt để SQL Injection bằng **Prepared Statements (PDO)**.
  - Ngăn chặn CSRF qua **CSRF Token Middleware**.
  - Chống Spam Form bằng cơ chế **Honeypot**.
  - Loại bỏ lỗi Double-Submit bằng **PRG Pattern (Post-Redirect-Get)**.
- **Testing:** Tích hợp sẵn một Mini Test Framework bằng Python (Pytest + Selenium) để tự động hóa API Test và UI Test.

## 📁 Cấu trúc thư mục

```text
studyflow-hub/
├── src/                    # Chứa mã nguồn PHP lõi (Backend)
│   ├── Controllers/        # Nơi tiếp nhận Request và gọi Service
│   ├── Core/               # Các lớp nền tảng: Database, Router, Request, View
│   ├── Repositories/       # Data Access Layer (chứa 100% câu lệnh SQL)
│   └── Services/           # Business Logic Layer (Xử lý nghiệp vụ, Validation)
├── views/                  # Giao diện người dùng (Frontend)
│   ├── components/         # Các thành phần tái sử dụng (Navbar, Toast, v.v.)
│   ├── layouts/            # Khung HTML tổng thể (main.php)
│   └── pages/              # Giao diện của từng màn hình cụ thể
├── public/                 # Thư mục gốc public web (Entry point)
│   ├── index.php           # File nhận và điều hướng mọi Request
│   └── css/                # File style.css tùy chỉnh
├── tests/                  # Mini Test Framework tự động (Python)
├── docker-compose.yml      # Cấu hình môi trường (PostgreSQL, MinIO, Selenium)
├── init.sql                # Script khởi tạo lược đồ CSDL
├── seed.php                # Script nạp dữ liệu mẫu (Fake data)
```

## ⚙️ Hướng dẫn Cài đặt & Chạy dự án (Set up)

### Yêu cầu hệ thống
- **PHP 8.1+** (Được cài đặt sẵn trên máy Host)
- **Docker** và **Docker Compose** (Dành cho Database, Storage, Test)
- **Git**

### Bước 1: Clone dự án
Mở Terminal và tải mã nguồn về máy:
```bash
git clone <đường_dẫn_repo_của_bạn>
cd studyflow-hub
```

### Bước 2: Khởi động hệ sinh thái Docker
Dự án sử dụng PostgreSQL (Cơ sở dữ liệu) và MinIO (Lưu trữ file). Các service này được đóng gói trong Docker.
```bash
docker compose up -d
```
*(Lệnh này sẽ tải image và khởi động DB ở cổng `5433`, MinIO ở cổng `9002`)*.

### Bước 3: Nạp dữ liệu mẫu (Tùy chọn nhưng khuyên dùng)
Dự án có sẵn script tự động sinh ra hàng chục lộ trình và thư mục để bạn test tính năng phân trang (Pagination) và tìm kiếm.
```bash
php seed.php
```

### Bước 4: Chạy Local PHP Server
Sử dụng server tích hợp sẵn của PHP, trỏ thư mục root vào `public/`.
```bash
php -S localhost:8080 -t public/
```

### Bước 5: Trải nghiệm ứng dụng
Mở trình duyệt web và truy cập vào địa chỉ:
👉 **[http://localhost:8080/studyflows](http://localhost:8080/studyflows)**

---
