# QuanLiSinhVien
thực hành bài tập nhóm môn OSS 

# KẾ HOẠCH HỢP TÁC DỰ ÁN: HỆ THỐNG QUẢN LÝ SINH VIÊN

## [cite_start]I. TỔNG QUAN DỰ ÁN [cite: 2]
[cite_start]**Mục tiêu:** Xây dựng một trang web quản lý sinh viên hoàn chỉnh với 3 vai trò chính: [cite: 3]
* [cite_start]**Admin** (Quản trị viên) [cite: 4]
* [cite_start]**Lecturer** (Giảng viên) [cite: 5]
* [cite_start]**Student** (Sinh viên) [cite: 6]

### [cite_start]Công nghệ sử dụng (Tech Stack) [cite: 7]
* [cite_start]**Backend:** PHP [cite: 8]
* [cite_start]**Database:** MySQL [cite: 9]
* [cite_start]**Frontend:** HTML, CSS, JavaScript [cite: 10]
* [cite_start]**Version Control:** Git & GitHub [cite: 11]

---

## II. [cite_start]GIAI ĐOẠN 1: THIẾT LẬP MÔI TRƯỜNG & QUY TRÌNH [cite: 12]
[cite_start]**Mục tiêu:** Đảm bảo cả 3 thành viên có cùng môi trường làm việc và hiểu rõ quy trình hợp tác. [cite: 13]

### [cite_start]1. Thiết lập Repository trên GitHub [cite: 14]
* [cite_start]**Người thực hiện:** Trưởng nhóm (hoặc đại diện 1 người). [cite: 15]
* [cite_start][ ] Tạo **Private Repository** mới. [cite: 16]
* [cite_start][ ] Thêm 2 thành viên còn lại làm **Collaborators**. [cite: 17]
* [cite_start][ ] Tạo file `.gitignore` để loại bỏ các file rác/cấu hình local. [cite: 18]
* [cite_start][ ] Đẩy mã nguồn khởi tạo lên nhánh `main`. [cite: 19]

### [cite_start]2. Thiết lập Môi trường Local (Cá nhân) [cite: 20]
* [cite_start]**Người thực hiện:** Tất cả 3 thành viên. [cite: 21]

1.  [cite_start]Cài đặt **Git** trên máy tính. [cite: 22]
2.  Clone dự án về máy:
    ```bash
    git clone <URL-repository>
    ```
    [cite_start][cite: 23]
3.  [cite_start]Cài đặt server local (**XAMPP** hoặc **WAMP**). [cite: 24]
4.  [cite_start]**Import Database:** Import file `db/student-management.sql` vào MySQL local. [cite: 25]
5.  **Cấu hình kết nối:**
    * [cite_start]Tạo file `config/db.php` kết nối database local. [cite: 26]
    * **Lưu ý:** Không đẩy file cấu hình chứa mật khẩu cá nhân lên GitHub. [cite_start]Nên dùng file mẫu `config/db.php.example`. [cite: 27]

### [cite_start]3. Quy trình làm việc (Git Workflow) - Quan trọng [cite: 28]
[cite_start]Chúng ta sử dụng mô hình nhánh để tránh xung đột mã nguồn. [cite: 29]

**Các nhánh chính:**
* **Nhánh `main`:** Chỉ chứa code ổn định, đã kiểm thử. [cite_start]**TUYỆT ĐỐI KHÔNG CODE TRỰC TIẾP TRÊN NHÁNH NÀY.** [cite: 30]
* **Nhánh `develop`:** Nhánh phát triển chính. [cite_start]Nơi tập trung code mới trước khi đưa sang main. [cite: 31]
* [cite_start]**Nhánh `<tên-người-thực-hiện>`:** Nhánh riêng cho từng người làm các chức năng của mình (VD: `Huy`). [cite: 32]

[cite_start]**Quy trình code cơ bản của từng người:** [cite: 33]
1.  Về nhánh develop và cập nhật code mới nhất:
    ```bash
    git checkout develop
    git pull
    ```
    [cite_start][cite: 34]
2.  Tạo nhánh mới:
    ```bash
    git checkout -b <tên-người-thực-hiện>
    ```
    [cite_start][cite: 35]
3.  [cite_start]Code và Commit thường xuyên. [cite: 36]
4.  Đẩy lên GitHub:
    ```bash
    git push origin <tên-người-thực-hiện>
    ```
    [cite_start][cite: 37]
5.  [cite_start]Tạo **Pull Request (PR)** từ `<tên-người-thực-hiện>` vào `develop`. [cite: 38]
6.  [cite_start]**Code Review:** Thành viên khác review code, comment góp ý. [cite: 39]
7.  [cite_start]Merge vào `develop` sau khi được duyệt và xóa nhánh feature. [cite: 40]

---

## III. [cite_start]GIAI ĐOẠN 2: PHÂN CHIA CÔNG VIỆC [cite: 41]

### [cite_start]👤 Nguyễn Quốc Huy: Lõi (Core) & Xác thực [cite: 42]
[cite_start]*Trách nhiệm: Nền tảng và các chức năng chung.* [cite: 43]
* [cite_start][ ] **Đăng nhập/Đăng xuất:** `login.php`, `logout.php`, quản lý `session.php`, kết nối database `db.php`. [cite: 44]
* [cite_start][ ] **Trang cá nhân:** `profile.php` (chung cho mọi user). [cite: 45]
* [cite_start][ ] **Đổi mật khẩu:** `change_password.php`. [cite: 46]
* [cite_start][ ] **Layout chung:** `header.php`, `footer.php`, `functions.php`. [cite: 47]

### [cite_start]👤 Lê Lưu Trung Hòa: Module Quản trị viên (Admin) [cite: 48]
[cite_start]*Trách nhiệm: Các chức năng quản lý hệ thống.* [cite: 49]
* [cite_start][ ] **Dashboard Admin:** `admin/index.php`. [cite: 50]
* [cite_start][ ] **Quản lý Người dùng:** `admin/users.php` (Thêm, sửa, xóa, xem, upload avatar). [cite: 51]
* [cite_start][ ] **Reset mật khẩu:** Cho Giảng viên/Sinh viên. [cite: 52]
* [cite_start][ ] **Quản lý danh mục:** Khoa, Lớp, Môn học, Phân công giảng dạy. [cite: 53]

### [cite_start]👤 Thành viên 3: Module Giảng viên & Sinh viên [cite: 54]
[cite_start]*Trách nhiệm: Các chức năng người dùng cuối.* [cite: 55]
* [cite_start][ ] **Dashboard Giảng viên:** `lecturer/index.php`. [cite: 56]
* [cite_start][ ] **Quản lý Điểm:** `lecturer/grades.php` (Nhập/sửa điểm). [cite: 57]
* [cite_start][ ] **Trang Sinh viên:** `student/index.php` (Xem điểm, lịch học, thông tin cá nhân). [cite: 58]
* [cite_start][ ] **Kiểm tra luồng:** Đảm bảo Sinh viên/Giảng viên dùng được chức năng đổi mật khẩu và xem profile. [cite: 59]

---

## IV. [cite_start]GIAI ĐOẠN 3: TÍCH HỢP & HOÀN THIỆN [cite: 60]
1.  [cite_start]**Kiểm thử chéo (Cross-check):** Các thành viên test chức năng của nhau. [cite: 61]
2.  [cite_start]**Fix Bugs:** Sửa lỗi phát sinh khi ghép nối các module. [cite: 62]
3.  [cite_start]**UI/UX:** Tinh chỉnh giao diện `style.css`, `script.js` cho đồng bộ. [cite: 63]
4.  [cite_start]**Tài liệu:** Viết hướng dẫn sử dụng (nếu cần). [cite: 64]

---

## [cite_start]V. CÔNG CỤ GIAO TIẾP & QUẢN LÝ [cite: 65]
* [cite_start]💬 **Giao tiếp:** Nhóm chat Zalo để trao đổi nhanh. [cite: 66]
* [cite_start]📅 **Họp:** Online ngắn 1-2 lần/tuần để cập nhật tiến độ. [cite: 67]
* [cite_start]📋 **Quản lý task:** Sử dụng tab **Issues** và **Projects** trên GitHub. [cite: 68]