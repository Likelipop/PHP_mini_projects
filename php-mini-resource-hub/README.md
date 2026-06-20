# Student Learning Resource Hub - PHP Mini Project

Dự án **Student Learning Resource Hub** là một ứng dụng web nhỏ gọn (mini project) được xây dựng bằng ngôn ngữ PHP thuần (Vanilla PHP) kết hợp với cơ sở dữ liệu PostgreSQL và dịch vụ lưu trữ đối tượng S3-compatible (MinIO). Ứng dụng này đóng vai trò là một cổng thông tin chia sẻ tài liệu học tập, cho phép sinh viên đăng ký tài khoản, đăng nhập, đăng tải tài liệu học tập dưới định dạng Markdown, tải lên tệp đính kèm và cùng nhau xây dựng cộng đồng chia sẻ tri thức.

---

## 1. Cấu trúc cây thư mục dự án

Dưới đây là sơ đồ cấu trúc các tệp tin và thư mục trong dự án sau khi lược bỏ thư mục cài đặt thư viện `vendor`:

```text
.
├── composer.json           # Cấu hình các thư viện phụ thuộc (AWS SDK, Parsedown...)
├── composer.lock           # Khóa phiên bản chi tiết của các thư viện phụ thuộc
├── docker-compose.yml      # Cấu hình các container dịch vụ (Web, PostgreSQL, MinIO)
├── Dockerfile              # Dockerfile cấu hình môi trường PHP và các extension
├── document.md             # Tài liệu mô tả và đặc tả dự án
├── init.sql                # File SQL khởi tạo cấu trúc bảng cơ sở dữ liệu ban đầu
├── kiem thu.md             # Kịch bản kiểm thử bảo mật & Validation (Phần 2)
├── kiemthu_2.md            # Kịch bản kiểm thử bảo mật Session & UI/UX (Phần 3 & 4)
├── public                  # Thư mục gốc công khai của web server
│   ├── assets
│   │   └── style.css       # File định kiểu giao diện CSS
│   └── index.php           # File điều phối trung tâm (Front Controller)
├── setup_and_run.sh        # Script tự động hóa cài đặt và khởi chạy dự án
├── src                     # Mã nguồn logic xử lý của ứng dụng
│   ├── Controllers         # Các bộ điều hướng điều phối luồng xử lý (MVC)
│   │   ├── AuthController.php
│   │   ├── HealthController.php
│   │   ├── HomeController.php
│   │   └── ResourceController.php
│   ├── Core                # Lớp xử lý lõi của hệ thống (Router)
│   │   └── Router.php
│   └── Support             # Các lớp hỗ trợ (Database, Storage, Response, Helpers)
│       ├── Database.php
│       ├── helpers.php
│       ├── Response.php
│       └── Storage.php
├── traloi.md               # Tài liệu trả lời các câu hỏi lý thuyết bảo mật Lab 04
└── views                   # Giao diện hiển thị HTML của ứng dụng (Templates)
    ├── auth
    │   ├── login.php
    │   └── signup.php
    ├── home.php
    └── resources
        ├── create.php
        └── index.php
```

---

## 2. Các tính năng chính và Cải tiến nâng cấp (Lab 03 lên Lab 04)

Dự án đã được nâng cấp toàn diện từ phiên bản cơ bản (Lab 03) lên phiên bản bảo mật nâng cao (Lab 04) với các cơ chế phòng thủ chuyên sâu:

| Tính năng / Cơ chế | Phiên bản cũ (Lab 03) | Phiên bản nâng cấp (Lab 04) |
| :--- | :--- | :--- |
| **Server-side Validation** | Chỉ kiểm tra rỗng cơ bản hoặc phụ thuộc client-side HTML. | Kiểm tra đa tầng ở server-side (kiểm tra rỗng $\rightarrow$ sai định dạng email $\rightarrow$ độ dài chuỗi $\rightarrow$ kiểm tra trùng lặp email trong Database). |
| **Giữ lại dữ liệu cũ (Sticky Forms)** | Bị xóa sạch dữ liệu form khi gửi lỗi, bắt người dùng nhập lại từ đầu. | Tự động điền lại dữ liệu cũ đã nhập (trừ mật khẩu) qua cơ chế session flash để tối ưu hóa trải nghiệm UI/UX. |
| **Chống Spam & Auto-bot** | Không có lớp bảo vệ nào, bot dễ dàng gửi hàng ngàn yêu cầu. | **Honeypot Trap (Spatial Trap):** Tạo trường ẩn `website` bằng CSS để lừa bot điền thông tin và chặn lại.<br>**Rate Limiting (Temporal Trap):** Giới hạn khoảng cách giữa 2 lần gửi form thành công phải từ 5 giây trở lên. |
| **Bảo mật Session Cookie** | Sử dụng session mặc định của PHP, dễ bị tấn công đánh cắp phiên. | Cấu hình bảo mật nâng cao cho cookie session: Bật `HttpOnly` (chống XSS lấy cookie), `SameSite=Lax` (chống CSRF), và tự động bật `Secure` nếu kết nối qua giao thức HTTPS. |
| **Chống Session Fixation** | Giữ nguyên Session ID trước và sau khi đăng nhập. | Thực thi `session_regenerate_id(true)` ngay khi xác thực tài khoản thành công để cấp Session ID mới hoàn toàn. |
| **Bảo mật Đăng xuất (Logout)** | Dùng phương thức GET đơn giản, dễ bị đăng xuất ngoài ý muốn hoặc tấn công CSRF. | Bắt buộc phải đăng xuất qua phương thức `POST /logout`; đồng thời thực hiện **Logout sạch** (xóa dữ liệu RAM, yêu cầu client xóa cookie cũ qua Max-Age=0, hủy file session trên đĩa cứng server). |
| ** Idle Timeout & Session Hijacking** | Phiên đăng nhập tồn tại mãi mãi cho tới khi tắt trình duyệt. | **Idle Timeout:** Tự động hết hạn phiên và đẩy về trang đăng nhập sau 15 phút không hoạt động.<br>**Context Check:** Kiểm tra tính nhất quán của thiết bị (`User-Agent`) để ngăn chặn việc lấy trộm Session ID. |
| **Chống mã độc XSS** | Render trực tiếp dữ liệu thô từ database. | Toàn bộ dữ liệu hiển thị do người dùng nhập vào đều được mã hóa bằng hàm an toàn `h()` (`htmlspecialchars` với tùy chọn `ENT_QUOTES`). |
| **Xử lý lỗi HTTP nâng cao** | Trả về giao diện lỗi chung chung. | Phân biệt chính xác giữa lỗi **404 Not Found** (đường dẫn không tồn tại) và **405 Method Not Allowed** (truy cập sai phương thức HTTP, ví dụ: gửi `GET /logout` sẽ nhận lỗi 405 kèm header `Allow: POST`). |

---

## 3. Hướng dẫn Cài đặt & Khởi chạy dự án

Dự án hỗ trợ chạy hoàn toàn bằng Docker, giúp bạn không cần cài đặt PHP, Composer hay PostgreSQL cục bộ trên máy tính của mình.

### Bước 1: Chuẩn bị hệ thống
Đảm bảo máy tính của bạn đã cài đặt sẵn:
1. **Docker Engine / Docker Desktop**
2. **Docker Compose**

### Bước 2: Clone Repo và truy cập thư mục dự án
Từ thiết bị đầu cuối (Terminal/Command Prompt), clone repository tổng về và di chuyển vào thư mục của dự án này:
```bash
# Di chuyển vào thư mục chứa 3 dự án PHP mini
cd PHP_mini_projects

# Di chuyển tiếp vào thư mục dự án Resource Hub
cd php-mini-resource-hub
```

### Bước 3: Cài đặt thư viện phụ thuộc và khởi động hệ thống
Bạn có thể tự cấu hình thủ công hoặc chạy script tự động hóa được chuẩn bị sẵn:

* **Cách 1: Sử dụng Shell Script tự động (Khuyên dùng trên Linux/macOS):**
  Cấp quyền thực thi và khởi chạy script:
  ```bash
  chmod +x setup_and_run.sh
  ./setup_and_run.sh
  ```
  *Script này sẽ tự động tạo cấu hình Composer, tải về thư viện phụ thuộc (AWS SDK, Parsedown) bằng một container tạm thời, sau đó build và khởi chạy hệ thống bằng Docker Compose.*

* **Cách 2: Thực hiện thủ công từng lệnh:**
  1. Cài đặt các thư viện PHP thông qua Docker Composer:
     ```bash
     docker run --rm -v $(pwd):/app -w /app composer:latest install
     docker run --rm -v $(pwd):/app -w /app composer:latest dump-autoload
     ```
  2. Khởi chạy Docker Compose để build các service:
     ```bash
     docker compose up -d --build
     ```

### Bước 4: Truy cập ứng dụng
Sau khi hệ thống khởi động thành công:
* **Giao diện Website:** Mở trình duyệt web và truy cập địa chỉ: [http://localhost:8000](http://localhost:8000)
* **Trang quản trị lưu trữ MinIO (S3 Console):** Truy cập địa chỉ [http://localhost:9001](http://localhost:9001) (Tài khoản: `minioadmin` / Mật khẩu: `minioadmin`) để quản lý các file tài liệu đã tải lên.

---

## 4. Lưu ý về Biến môi trường (.env)

Dự án này **không yêu cầu** bạn phải tự tạo hay cấu hình tệp tin `.env` thủ công. 

Tất cả các biến môi trường kết nối (bao gồm thông tin đăng nhập PostgreSQL và cấu hình kết nối MinIO S3) đã được thiết lập sẵn trong tệp tin `docker-compose.yml` tại phần `environment` của các service tương ứng (`app`, `db`, `minio`). Khi chạy lệnh khởi động Docker, các cấu hình này sẽ tự động được nạp trực tiếp vào môi trường chạy của PHP thông qua hàm `getenv()`.
