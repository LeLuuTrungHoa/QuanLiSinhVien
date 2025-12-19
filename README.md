# Hệ thống Quản lý Sinh viên (QuanLiSinhVien)

Đây là một dự án website Quản lý Sinh viên được xây dựng bằng PHP thuần và cơ sở dữ liệu MySQL. Hệ thống cho phép ba loại người dùng (Quản trị viên, Giảng viên, Sinh viên) tương tác với các chức năng phù hợp với vai trò của mình.

## Tính năng chính

### 1. Quản trị viên (Admin)
- Quản lý tài khoản người dùng (tạo, sửa, xóa tài khoản cho giảng viên và sinh viên).
- Quản lý danh sách Khoa.
- Quản lý danh sách Lớp học.
- Quản lý danh sách Môn học.
- Phân công giảng dạy.

### 2. Giảng viên (Lecturer)
- Xem danh sách các lớp học phần được phân công.
- Nhập và cập nhật điểm cho sinh viên trong lớp học phần của mình.
- Xuất danh sách sinh viên của lớp học phần ra file Excel.

### 3. Sinh viên (Student)
- Đăng ký môn học.
- Xem bảng điểm.
- Xuất bảng điểm ra file Excel.

## Công nghệ sử dụng
- **Backend:** PHP
- **Frontend:** HTML, CSS, JavaScript (cơ bản)
- **Database:** MySQL
- **Web Server:** Apache (thường đi kèm trong WAMP)

## Hướng dẫn cài đặt

1.  **Clone repository** về máy của bạn.
    ```sh
    git clone https://github.com/LeLuuTrungHoa/QuanLiSinhVien.git
    ```
2.  **Web Server & Database:**
    - Cài đặt WAMP Server.
    - Sao chép toàn bộ thư mục dự án vào thư mục `www` của WAMP (ví dụ: `c:\wamp64\www\QuanLiSinhVien`).

3.  **Import Database:**
    - Mở phpMyAdmin (hoặc công cụ quản lý MySQL khác).
    - Tạo một cơ sở dữ liệu mới (ví dụ: `student_management`).
    - Chọn cơ sở dữ liệu vừa tạo, vào tab `Import`, và chọn file `db/student_management.sql` để tải lên.

4.  **Cấu hình kết nối:**
    - Mở file `config/db.php`.
    - Chỉnh sửa các thông tin sau cho phù hợp với môi trường của bạn:
      ```php
      $servername = "localhost";
      $username = "root"; // Tên đăng nhập MySQL
      $password = "";     // Mật khẩu MySQL
      $dbname = "student_management"; // Tên database bạn đã tạo ở bước 3
      ```

5.  **Truy cập ứng dụng:**
    - Mở trình duyệt và truy cập vào địa chỉ: `http://localhost/QuanLiSinhVien`
    - Đăng nhập admin: Tài khoản `admin`, Mật khẩu `admin123`.
    - Đăng nhập sinh viên: Tài khoản: `SV001`, Mật khẩu `123456`.
    - Đăng nhập giảng viên: Tài khoản `GV001`, Mật khẩu `123456`.
     => Mật khẩu mặt định của sinh viên và giảng viên là `123456`.

## Cấu trúc thư mục

```
.
├── admin/            # Chức năng của Quản trị viên
├── assets/           # Chứa CSS, JS, và hình ảnh
├── config/           # Chứa file cấu hình (kết nối DB)
├── db/               # Chứa file .sql để import database
├── includes/         # Các thành phần tái sử dụng (header, footer, sidebar)
├── lecturer/         # Chức năng của Giảng viên
├── student/          # Chức năng của Sinh viên
├── index.php         # Trang chủ (chuyển hướng sau khi đăng nhập)
├── login.php         # Trang đăng nhập
├── logout.php        # Xử lý đăng xuất
└── ...
```
