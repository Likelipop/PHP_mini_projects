#!/bin/bash

# Cấu hình URL cơ sở
BASE_URL="http://localhost:8000"

# Màu sắc cho output
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' 

echo -e "${BLUE}=== BẮT ĐẦU KIỂM TRA HỆ THỐNG CONCERT HUB ===${NC}\n"

# Hàm hỗ trợ kiểm tra kết quả
# Tham số: $1: Tên test, $2: Status code kỳ vọng, $3: URL, $4: Method, $5: Header, $6: Data
check_api() {
    local label=$1
    local expected=$2
    local url=$3
    local method=$4
    local header=$5
    local data=$6

    echo -n "Đang test: $label... "

    # Thực hiện lệnh curl một cách cẩn thận với các tham số tách biệt
    if [ -z "$data" ]; then
        STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X "$method" "$url")
    else
        STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X "$method" "$url" -H "$header" -d "$data")
    fi
    
    if [ "$STATUS" == "$expected" ]; then
        echo -e "${GREEN}PASS (Status: $STATUS)${NC}"
    else
        echo -e "${RED}FAIL (Kỳ vọng: $expected nhưng nhận: $STATUS)${NC}"
    fi
}

# --- THỰC HIỆN CÁC TEST CASE ---

# 1. Test 200 OK: Lấy danh sách Concert
check_api "Lấy danh sách Concert (200 OK)" "200" "$BASE_URL/concerts" "GET"

# 2. Test 201 Created: Đăng ký vé thành công
# Chú ý: Key gửi đi phải khớp 100% với Controller của bạn (ví dụ: student_name)
DATA_OK='{"concert_id":1,"student_name":"Tran Tien Dat","email":"dat@hcmus.edu.vn","quantity":1}'
check_api "Đăng ký vé thành công (201 Created)" "201" "$BASE_URL/bookings" "POST" "Content-Type: application/json" "$DATA_OK"

# 3. Test 404 Not Found: Sai đường dẫn
check_api "Đường dẫn không tồn tại (404)" "404" "$BASE_URL/duong-dan-sai" "GET"

# 4. Test 405 Method Not Allowed: Sai Method tại route bookings
check_api "Sai Method (405)" "405" "$BASE_URL/bookings" "GET"

# 5. Test 415 Unsupported Media Type: Gửi sai Content-Type
check_api "Sai Content-Type (415)" "415" "$BASE_URL/bookings" "POST" "Content-Type: text/plain" "Name=Dat"

# 6. Test 422 Unprocessable Content: Lỗi nghiệp vụ (Hết vé)
# Gửi cho concert_id số 2 - nơi mà chúng ta đã để seats_available = 0 trong file concerts.php
DATA_FULL='{"concert_id":2,"student_name":"Dat","email":"dat@test.com","quantity":1}'
check_api "Lỗi nghiệp vụ - Hết vé (422)" "422" "$BASE_URL/bookings" "POST" "Content-Type: application/json" "$DATA_FULL"

echo -e "\n${BLUE}=== HOÀN TẤT KIỂM TRA ===${NC}"