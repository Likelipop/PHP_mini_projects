# Student Learning Resource Hub — Technical Guide

## Mục tiêu của tài liệu
Tài liệu này giải thích cấu trúc, từng file code và cách hoạt động của project `php-mini-resource-hub`.
Nó dành cho người mới, trình bày rõ từng phần để bạn có thể đọc hiểu và vận hành được dự án.

---

## Yêu cầu
```
Vẫn thực hiện đầy đủ các bước như trong bài hướng dẫn:
· kiểm tra môi trường PHP, Composer, Git​
· mở project bằng VS Code​
· tạo project PHP có cấu trúc rõ ràng​
· tạo composer.json​
· cấu hình PSR-4 autoload​
· chạy composer dump-autoload​
· tạo thư mục public/ làm public web root​
· tạo public/index.php làm Front Controller​
· tạo Router để map URL và HTTP method đến controller/action​
· tạo Response helper hoặc support class để chuẩn hoá response​
· tạo controller theo nhóm chức năng​
· tạo dữ liệu mẫu bằng array PHP​
· tạo view HTML phù hợp với bài toán​
· tạo CSS cơ bản để giao diện dễ nhìn​
· khai báo route rõ ràng trong public/index.php​
· chuẩn hoá URL theo nguyên tắc: viết thường, ngắn gọn, dễ hiểu, nhất quán​
· chạy chương trình bằng:
php -S localhost:8000 -t public public/index.php
· test bằng browser, curl hoặc Postman​
· quản lý source code bằng Git và đưa project lên GitHub
Ứng dụng mới phải có
Ứng dụng mới phải có ít nhất:
· 1 trang chủ trả về HTML, ví dụ: GET /​
· 1 route kiểm tra hệ thống trả về JSON, ví dụ: GET /health​
· 1 trang danh sách dữ liệu, ví dụ: GET /courses, GET /supplies, GET /equipments​
· 1 trang form tạo mới dữ liệu, ví dụ: GET /courses/create​
· 1 route xử lý submit form bằng POST, ví dụ: POST /courses​
· 1 route login demo, ví dụ: GET /login​
· 1 route xử lý login demo bằng POST, ví dụ: POST /login​
· 1 route redirect, ví dụ: GET /go-home hoặc GET /logout​
· 1 trường hợp URL không tồn tại trả 404 Not Found​
· 1 trường hợp URL có tồn tại nhưng gọi sai method trả 405 Method Not Allowed
Tối thiểu ứng dụng phải xử lý được các tình huống sau
· 200 OK khi trả trang HTML thành công​
· 200 OK khi trả JSON health check thành công​
· 302 Found khi redirect thành công​
· 404 Not Found khi sai đường dẫn​
· 405 Method Not Allowed khi gọi sai method​
· Có header Content-Type: text/html; charset=UTF-8 khi trả HTML​
· Có header Content-Type: application/json; charset=UTF-8 khi trả JSON​
· Có header Location khi redirect​
· Có header Allow khi trả 405 nếu xác định được method hợp lệ
```

---

## Tổng quan ứng dụng
Project này là một **Student Learning Resource Hub**. Nó cho phép:
- Hiển thị trang chủ HTML.
- Kiểm tra hệ thống bằng route `/health` trả JSON.
- Hiển thị danh sách tài nguyên bằng route `/resources`.
- Hiển thị form tạo mới tài nguyên bằng route `/resources/create`.
- Xử lý `POST /resources` để tạo mới.
- Cho phép người dùng đăng ký, đăng nhập và đăng xuất.
- Lưu file upload vào MinIO.
- Lưu dữ liệu người dùng và tài nguyên vào PostgreSQL.
- Có 404 nếu đường dẫn không tồn tại và 405 nếu method sai.

---

## Cách chạy project
1. Mở terminal ở thư mục `php-mini-resource-hub`.
2. Chạy `./setup_and_run.sh` để cài Composer, dump autoload và khởi động Docker.
3. Mở trình duyệt vào `http://localhost:8000`.
4. Hoặc chạy trực tiếp nếu không dùng Docker: `php -S localhost:8000 -t public public/index.php`.

> Lưu ý: project hiện dùng Docker để dễ cấu hình PostgreSQL và MinIO.

---

## Thư mục chính và trách nhiệm

### Root files

- `composer.json`
  - Khai báo dependencies của dự án và cấu hình PSR-4 autoload.
  - Autoload cho các namespace `Core\`, `Support\`, `Controllers\`.

- `composer.lock`
  - Khóa phiên bản dependency để đảm bảo môi trường giống nhau.

- `Dockerfile`
  - Xây dựng image PHP 8.4 CLI.
  - Cài `pdo_pgsql` để kết nối PostgreSQL.
  - Copy Composer binary và chạy PHP built-in server.

- `docker-compose.yml`
  - Cấu hình hệ thống gồm 3 service: `app`, `db`, `minio`.
  - `app` chạy ứng dụng PHP và kết nối đến `db`, `minio`.
  - `db` dùng PostgreSQL 15, import schema bằng `init.sql`.
  - `minio` dùng MinIO để lưu file upload.

- `init.sql`
  - Khai báo bảng `users` và `resources`.
  - `users` chứa thông tin đăng ký: `name`, `email`, `password`.
  - `resources` chứa: `user_id`, `title`, `markdown_recommendation`, `minio_object_key`.

- `setup_and_run.sh`
  - Script tạo `composer.json` nếu chưa có.
  - Chạy `composer install` và `composer dump-autoload` trong Docker.
  - Khởi động Docker Compose.

---

## public/

- `public/index.php`
  - Là Front Controller của ứng dụng.
  - Nhập autoload Composer.
  - Khởi tạo `Router` và đăng ký tất cả route.
  - Lấy `$_SERVER['REQUEST_METHOD']` và `$_SERVER['REQUEST_URI']` để dispatch.
  - Đây là điểm vào duy nhất cho tất cả request.

- `public/assets/style.css`
  - CSS toàn bộ giao diện.
  - Định nghĩa màu sắc, button, form, layout responsive.
  - Giúp giao diện dễ nhìn và sạch sẽ.

---

## src/Core/

- `src/Core/Router.php`
  - Quản lý routing của ứng dụng.
  - Có hai method: `get()` và `post()` để đăng ký route.
  - `dispatch($method, $path)` kiểm tra:
    - nếu route không tồn tại => `Response::notFound()` (404)
    - nếu path đúng nhưng method sai => `Response::methodNotAllowed()` (405 + Allow header)
    - nếu route tồn tại và method đúng => tạo controller và gọi action.

---

## src/Support/

- `src/Support/Response.php`
  - Helper chuẩn hoá response.
  - Các method:
    - `view($view, $data, $status)` để render view PHP.
    - `json($status, $data)` trả JSON.
    - `redirect($url, $status)` trả redirect 302 kèm header Location.
    - `text($status, $message)` trả text/plain.
    - `notFound()` trả 404.
    - `methodNotAllowed($allowedMethods)` trả 405 và header `Allow`.
  - Như vậy, ứng dụng có header đúng cho HTML, JSON, redirect, 404, 405.

- `src/Support/Database.php`
  - Kết nối PostgreSQL bằng PDO.
  - Đọc cấu hình từ biến môi trường `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`.
  - Thiết lập chế độ exception và fetch kiểu associative.

- `src/Support/Storage.php`
  - Quản lý kết nối MinIO bằng AWS SDK S3.
  - `getClient()` xây dựng client nội bộ dùng endpoint `http://minio:9000`.
  - `upload($key, $filePath)` lưu file vào bucket `resources`.
  - `getDownloadUrl($key)` tạo URL download được ký chữ ký cho browser.

---

## src/Controllers/

Các controller chia chức năng theo nhóm:

- `src/Controllers/HomeController.php`
  - Phục vụ trang chủ `/`.
  - Chỉ cần gọi `Response::view('home', ['title' => 'Student Learning Resource Hub'])`.
  - Khởi tạo session để kiểm tra xem người dùng đã login chưa.

- `src/Controllers/HealthController.php`
  - Phục vụ `/health` trả JSON.
  - Kiểm tra kết nối database và MinIO.
  - Trả về JSON gồm `status`, `database`, `minio`.
  - Dùng `Response::json()` nên tiêu đề `Content-Type: application/json` được tự động đặt.

- `src/Controllers/ResourceController.php`
  - Quản lý trang danh sách tài nguyên và tạo mới.
  - `index()` nhận dữ liệu từ DB, chuyển markdown sang HTML bằng Parsedown, tạo download URL.
  - `create()` hiển thị form tạo tài nguyên. Nếu chưa login thì redirect `/login`.
  - `store()` xử lý `POST /resources`, kiểm tra tiêu đề, upload file nếu có, insert dữ liệu vào bảng `resources`.
  - Nếu gọi POST khi chưa login, trả 401 Unauthorized.
  - `getDownloadUrl()` đảm bảo download link hoạt động.

- `src/Controllers/AuthController.php`
  - Quản lý đăng nhập, đăng ký, đăng xuất.
  - `login()` hiển thị form login.
  - `signup()` hiển thị form đăng ký.
  - `handleLogin()` xử lý `POST /login`.
  - `handleSignup()` xử lý `POST /signup`.
  - `logout()` hủy session và redirect về `/`.
  - Tất cả validate cơ bản: email hợp lệ, password tồn tại, confirm password, trùng email.

---

## views/

Toàn bộ view được viết bằng PHP template đơn giản.

- `views/home.php`
  - Trang chủ HTML.
  - Hiển thị điều hướng, hero section, thông báo login/signup/logout.
  - Nếu user đã login, hiển thị `Hi, <name>` và nút Logout.
  - Nếu chưa login, hiển thị nút Login/Sign Up.

- `views/auth/login.php`
  - Form đăng nhập email/password.
  - Điểm vào route `GET /login`.
  - Gửi `POST /login` để xử lý.

- `views/auth/signup.php`
  - Form tạo tài khoản mới.
  - Điểm vào route `GET /signup`.
  - Gửi `POST /signup` để xử lý.

- `views/resources/index.php`
  - Trang danh sách tài nguyên `/resources`.
  - Hiển thị title, author, ngày tạo, nội dung markdown đã render.
  - Nếu file có upload, hiển thị nút download.
  - Nếu chưa có tài nguyên, hiển thị empty state.

- `views/resources/create.php`
  - Form tạo tài nguyên mới `/resources/create`.
  - Input title, markdown recommendation, file upload.
  - Gửi `POST /resources`.
  - Chỉ mở cho user đã đăng nhập.

---

## Route và hành vi HTTP chi tiết

### Đã hỗ trợ trong `public/index.php`
- `GET /` → `HomeController@index`
- `GET /health` → `HealthController@index`
- `GET /resources` → `ResourceController@index`
- `GET /resources/create` → `ResourceController@create`
- `POST /resources` → `ResourceController@store`
- `GET /login` → `AuthController@login`
- `POST /login` → `AuthController@handleLogin`
- `GET /signup` → `AuthController@signup`
- `POST /signup` → `AuthController@handleSignup`
- `GET /logout` → `AuthController@logout`

### 404 Not Found
- Nếu đường dẫn không tồn tại trong `routes`, `Router::dispatch()` gọi `Response::notFound()`.
- Ví dụ: `GET /unknown` trả 404.

### 405 Method Not Allowed
- Nếu path tồn tại nhưng method không phù hợp, `Response::methodNotAllowed()` trả 405 và header `Allow`.
- Ví dụ: `POST /login` thì đúng, nhưng `GET /login` cũng đúng; nếu gọi `DELETE /login` thì 405.

### Redirect
- `AuthController::logout()` dùng `Response::redirect('/?logout=success')`.
- Nếu người đã login truy cập `/login` hoặc `/signup`, controller redirect về `/`.
- Nếu chưa login truy cập `/resources/create`, redirect tới `/login`.

### HTML trả về
- `Response::view()` dùng `require` để render file view.
- Trang HTML tự động trả tiêu đề `Content-Type: text/html` nhờ trình duyệt mặc định, và HTML đúng UTF-8 do meta charset.
- Hầu hết trang view có `<meta charset="UTF-8">`.

### JSON trả về
- `Response::json()` đặt header `Content-Type: application/json`.
- Route `/health` trả JSON status và độ khả dụng của DB/MinIO.

### File upload / Download
- `ResourceController::store()` nhận `$_FILES['file']`.
- `Storage::upload()` gửi file lên MinIO qua S3 client.
- `Storage::getDownloadUrl()` trả URL presigned để trình duyệt download.

---

## Cách tổ chức code cho người mới

### PSR-4 autoload
- `composer.json` khai báo `autoload.psr-4`.
- Thư mục `src/Core` map tới namespace `Core\\`.
- Thư mục `src/Support` map tới namespace `Support\\`.
- Thư mục `src/Controllers` map tới namespace `Controllers\\`.
- Composer sẽ tự load các class theo namespace.

### Front Controller
- `public/index.php` là điểm vào để tập trung xử lý request.
- Không dùng nhiều file PHP entry point, chỉ có một file public.

### Router
- `Router` là lớp nhỏ nhưng rất quan trọng.
- Nó định nghĩa route bằng method và path.
- Nó trả 404 khi không tìm thấy route, trả 405 khi method sai.

### Response helper
- `Response` chuẩn hoá cách gửi HTML, JSON, redirect, lỗi.
- Giúp controller đọc hiểu nhanh và giảm lặp mã.

### Controller
- Mỗi controller tập trung vào một nhóm chức năng:
  - Home, Health, Auth, Resource.
- Controller gọi `Response` để xuất kết quả.
- Controller có thể truy cập database và storage khi cần.

---

## Các thành phần môi trường và cài đặt

### Database
- `docker-compose.yml` tạo service `db` với PostgreSQL.
- `init.sql` tạo bảng `users` và `resources`.
- `Database::getConnection()` dùng biến môi trường để kết nối.

### Storage
- `minio` là hệ thống lưu file object storage.
- `Storage::getClient()` dùng endpoint nội bộ `http://minio:9000`.
- Bucket `resources` được tạo tự động khi cần.

### Docker
- `app` chạy PHP built-in server.
- `db` chạy PostgreSQL.
- `minio` chạy MinIO server và console.
- `docker-compose.yml` nối các service bằng mạng nội bộ.

---

## Khớc phục lỗi phổ biến

- Nếu route trả 404, kiểm tra lại đường dẫn có ghi đúng trong `public/index.php`.
- Nếu trả 405, kiểm tra đúng method `GET` hoặc `POST`.
- Nếu không kết nối được database, kiểm tra biến môi trường trong `docker-compose.yml` và trạng thái container `resource_hub_db`.
- Nếu upload file không lưu được, kiểm tra MinIO đang chạy và `MINIO_ENDPOINT`.

---

## Kết luận
Project này đã bao gồm đầy đủ các yêu cầu kỹ thuật cơ bản của một PHP mini project:
- Front Controller và `public/index.php`.
- Router xử lý route và HTTP method.
- Response helper chuẩn hoá JSON, HTML, redirect, lỗi 404/405.
- Controller theo nhóm chức năng.
- Tạo view HTML và CSS đẹp.
- Đăng ký, đăng nhập, đăng xuất, tạo tài nguyên, xem danh sách.
- Docker để chạy app, database, MinIO.

Nếu bạn là người mới, hãy bắt đầu bằng cách mở `public/index.php` và đọc tiếp từng file controller, sau đó nhìn vào các view để hiểu flow của request và response.
