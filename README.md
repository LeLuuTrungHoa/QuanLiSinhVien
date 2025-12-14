# HỆ THỐNG WEBSITE QUẢN LÝ SINH VIÊN

> [cite_start]**Mô tả dự án:** Xây dựng một giải pháp phần mềm toàn diện nhằm số hóa quy trình quản lý đào tạo, thay thế phương pháp quản lý thủ công bằng hệ thống tự động hóa tập trung[cite: 5].

---

## 👥 Thành viên & Phân công (Team & Roles)

[cite_start]Dự án áp dụng chiến lược **Phân chia theo Module chức năng**, mỗi thành viên chịu trách nhiệm trọn vẹn (Full-stack) cho module của mình[cite: 20, 21].

| STT | Thành viên | Vai trò | Module phụ trách | Nhánh Git | Nhiệm vụ chi tiết |
|:---:|:---|:---|:---|:---|:---|
| **1** | **Nguyễn Quốc Huy** | Leader | Core System & Security | `feature/core-system` | **1. Cấu hình chung:** Kết nối CSDL (`db.php`), Hàm tiện ích (`functions.php`), Tổ chức thư mục.<br>**2. Xác thực:** Login, Logout, Check Login (Session), Đổi mật khẩu.<br>**3. Giao diện:** Layout chung (`header.php`, `footer.php`, `sidebar.php`). |
| **2** | **Lê Lưu Trung Hòa** | Developer | Admin Panel & Analytics | `feature/admin-stats` | **1. Quản lý dữ liệu:** CRUD Sinh viên, Giảng viên; Import Excel.<br>**2. Dashboard:** Trang thống kê Admin, Biểu đồ Chart.js.<br>**3. Danh mục:** CRUD Khoa, Lớp, Môn học. |
| **3** | **Nguyễn Lê Gia Bảo** | Developer | Academic Operations | `feature/academic-ops` | **1. Giảng viên:** Xem lịch dạy, Nhập điểm, Xuất bảng điểm PDF.<br>**2. Sinh viên:** Dashboard, Đăng ký môn học.<br>**3. Logic:** Tính GPA, Xếp loại học lực. |

[cite_start]*(Thông tin chi tiết tại bảng phân công [cite: 23])*

---

## 🛠 Công nghệ sử dụng (Tech Stack)

* [cite_start]**Backend:** PHP (Procedural/Thuần)[cite: 11].
* [cite_start]**Database:** MySQL[cite: 12].
* [cite_start]**Frontend:** HTML5, CSS3, JavaScript, Bootstrap (Responsive)[cite: 13].
* **Thư viện mở rộng:**
    * [cite_start]`PHPExcel/PhpSpreadsheet`: Xử lý Import/Export Excel[cite: 15].
    * [cite_start]`FPDF`: Xuất báo cáo PDF[cite: 16].
    * [cite_start]`Chart.js`: Vẽ biểu đồ thống kê[cite: 17].
* [cite_start]**Quản lý mã nguồn:** Git & GitHub[cite: 18].

---

## 📅 Lộ trình phát triển (Roadmap)

### Version 1.0: Core Foundation (Nền tảng)
* [cite_start][x] Thiết lập kết nối CSDL và sơ đồ ERD[cite: 26].
* [cite_start][x] Hoàn thành module Xác thực: Đăng nhập/Xuất, Phân quyền (Admin/Lecturer/Student)[cite: 27].
* [cite_start][x] Giao diện khung (Layout) ổn định trên Desktop[cite: 28].

### Version 2.0: Functional & Business Logic
* [cite_start][ ] **Admin:** Hoàn thiện CRUD Khoa, Lớp, Môn học, Users[cite: 30].
* [cite_start][ ] **Student:** Đăng ký môn học trực tuyến, Tra cứu lịch học & Bảng điểm [cite: 31-33].
* [cite_start][ ] **Lecturer:** Nhập điểm trực tiếp, Xem danh sách lớp[cite: 34].
* [cite_start][ ] **Logic:** Tự động tính điểm tổng kết & xếp loại[cite: 35].

### Version 3.0: Enterprise Features (Nâng cao)
* [cite_start][ ] **Data Integration:** Import/Export dữ liệu (Excel, PDF)[cite: 37].
* [cite_start][ ] **Analytics:** Dashboard thống kê với biểu đồ[cite: 38].
* [cite_start][ ] **Optimization:** Tối ưu SQL, UAT, Sửa lỗi giao diện[cite: 39].

---

## 🔄 Quy trình làm việc (Workflow)

### 1. Quản lý Source Code (Git Flow)
[cite_start]**Nguyên tắc "3 KHÔNG"** [cite: 47-50]:
1.  ❌ KHÔNG đặt tên nhánh theo tên thành viên.
2.  ❌ KHÔNG code trực tiếp trên nhánh `main`.
3.  ❌ KHÔNG phân nhánh theo version (v1, v2).

**Quy tắc đúng:**
* [cite_start]Phân nhánh theo **Module chức năng**: `feature/<tên-module>` (VD: `feature/core-auth`, `feature/admin-stats`)[cite: 52].
* [cite_start]**Quy trình Merge:** Feature -> Pull Request (PR) -> Code Review -> Merge to `main` [cite: 57-61].

[cite_start]**Quy định Commit Message** [cite: 64-68]:
* `[Feat]`: Tính năng mới.
* `[Fix]`: Sửa lỗi.
* `[Refactor]`: Tối ưu code.
* `[Doc]`: Tài liệu.

### 2. Quản lý Lỗi (Bug Tracking)
[cite_start]Sử dụng công cụ: **Redmine**[cite: 70].

**Quy trình xử lý:**
1.  [cite_start]**Report (New):** Tạo Issue trên GitHub/Redmine (Mô tả + Ảnh)[cite: 82].
2.  [cite_start]**Assign (In Progress):** Người phụ trách nhận lỗi và sửa[cite: 83].
3.  [cite_start]**Fix (Resolved):** Commit fix lỗi[cite: 84].
4.  **Verify (Closed):** Reporter kiểm tra lại. Nếu đạt -> Close; [cite_start]Nếu chưa -> Reopen [cite: 85-87].

---

## 🔗 Liên kết
* [cite_start]**Repository URL:** [https://github.com/LeLuuTrungHoa/QuanLiSinhVien.git](https://github.com/LeLuuTrungHoa/QuanLiSinhVien.git)[cite: 43].