<?php
$page_title = "Import Users";
require_once '../includes/session.php';
require_once '../includes/header.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date; 

$message = "";
$error_list = [];

// [KHÔNG THỂ THIẾU 1]: Lấy danh sách Lớp để map từ Tên -> ID
// Người dùng nhập "CNTT-K60", ta cần lưu vào DB là ID (ví dụ: 5)
$class_map = [];
try {
    // Lấy cặp key-value: 'Tên Lớp' => 'ID'
    $stmt_class = $pdo->query("SELECT ten_lop, id FROM lop_hoc");
    $class_map = $stmt_class->fetchAll(PDO::FETCH_KEY_PAIR);
    // Kết quả mảng dạng: ['CNTT-K60' => 5, 'KTE-K12' => 8, ...]
} catch (Exception $e) {
    die("Lỗi tải danh sách lớp: " . $e->getMessage());
}

if (isset($_POST['import_btn'])) {
    if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] == 0) {
        $file_tmp = $_FILES['excel_file']['tmp_name'];
        $role_target = $_POST['role_target']; // Chọn role muốn import (student/lecturer)

        try {
            $spreadsheet = IOFactory::load($file_tmp);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // SQL Insert đầy đủ các trường
            $sql = "INSERT INTO users (full_name, email, username, password, role, phone, gender, birthday, address, lop_hoc_id, avatar) 
                    VALUES (:name, :email, :username, :pass, :role, :phone, :gender, :birthday, :address, :class_id, 'default.png')";
            
            $stmt = $pdo->prepare($sql);
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email OR username = :username");
            $default_pass = password_hash('123456', PASSWORD_DEFAULT);

            // [KHÔNG THỂ THIẾU 2]: Transaction
            // Đảm bảo toàn vẹn dữ liệu: 1 người lỗi -> hủy tất cả để sửa lại từ đầu
            $pdo->beginTransaction();
            
            $count_success = 0;

            // Bắt đầu lặp từ dòng 1 (bỏ header dòng 0)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                // Gán biến theo cột Excel
                $name     = trim($row[0] ?? '');
                $email    = trim($row[1] ?? '');
                $username = trim($row[2] ?? '');
                $phone    = trim($row[3] ?? '');
                $gender   = trim($row[4] ?? 'Khác');
                $dob_raw  = $row[5] ?? null; // Ngày sinh dạng Raw
                $address  = trim($row[6] ?? '');
                $class_name = trim($row[7] ?? '');

                // Validation cơ bản
                if (empty($name) || empty($email) || empty($username)) continue;

                // [KHÔNG THỂ THIẾU 3]: Xử lý Ngày sinh (Excel Date -> PHP Date -> MySQL Date)
                $birthday = null;
                if (!empty($dob_raw)) {
                    if (Date::isDateTime($sheet->getCell("F" . ($i + 1)))) {
                        // Nếu Excel nhận diện đúng là ngày tháng (dạng số)
                        $birthday = Date::excelToDateTimeObject($dob_raw)->format('Y-m-d');
                    } else {
                        // Nếu Excel lưu dạng text "15/08/2002"
                        $date_parts = explode('/', $dob_raw);
                        if (count($date_parts) == 3) {
                            $birthday = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
                        }
                    }
                }

                // [KHÔNG THỂ THIẾU 4]: Map Tên lớp sang ID lớp
                $class_id = null;
                if (!empty($class_name) && isset($class_map[$class_name])) {
                    $class_id = $class_map[$class_name];
                } else if (!empty($class_name)) {
                    $error_list[] = "Dòng " . ($i + 1) . ": Lớp '$class_name' không tồn tại trong hệ thống.";
                    // Tùy chọn: continue; nếu bắt buộc phải có lớp đúng
                }

                // Kiểm tra trùng
                $checkStmt->execute([':email' => $email, ':username' => $username]);
                if ($checkStmt->fetchColumn() > 0) {
                    $error_list[] = "Dòng " . ($i + 1) . ": User $username hoặc Email đã tồn tại.";
                    continue;
                }

                // Thực thi Insert
                $stmt->execute([
                    ':name'     => $name,
                    ':email'    => $email,
                    ':username' => $username,
                    ':pass'     => $default_pass,
                    ':role'     => $role_target,
                    ':phone'    => $phone,
                    ':gender'   => $gender,
                    ':birthday' => $birthday,
                    ':address'  => $address,
                    ':class_id' => $class_id
                ]);
                $count_success++;
            }

            $pdo->commit();
            $message = "Import thành công $count_success người dùng!";

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $message = "Lỗi nghiêm trọng: " . $e->getMessage();
        }
    }
}
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Import User</h5>
        </div>
        <div class="card-body">
            
            <?php if ($message): ?>
                <div class="alert alert-info"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error_list)): ?>
                <div class="alert alert-warning">
                    <strong>Cảnh báo:</strong>
                    <ul>
                    <?php foreach ($error_list as $err): ?>
                        <li><?php echo $err; ?></li>
                    <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>1. Tải file mẫu</h6>
                    <p class="text-muted small">File mẫu đã có sẵn các cột: Ngày sinh, Địa chỉ, Lớp...</p>
                    <a href="download_full_sample.php" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-download"></i> Tải file mẫu .xlsx
                    </a>
                </div>
                <div class="col-md-6">
                    <h6>2. Lưu ý quan trọng</h6>
                    <ul class="small text-muted">
                        <li><strong>Tên Lớp:</strong> Phải nhập chính xác tên lớp đã có trong hệ thống (Ví dụ: <code>CNTT-K60</code>).</li>
                        <li><strong>Ngày sinh:</strong> Nhập định dạng dd/mm/yyyy.</li>
                        <li>Nếu Tên Lớp sai, người dùng sẽ được thêm vào nhưng <strong>không có lớp</strong>.</li>
                    </ul>
                </div>
            </div>

            <form method="POST" enctype="multipart/form-data" class="border p-4 rounded bg-light">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Vai trò (Role):</label>
                        <select name="role_target" class="form-select">
                            <option value="student">Sinh viên</option>
                            <option value="lecturer">Giảng viên</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Chọn file Excel:</label>
                        <input type="file" name="excel_file" class="form-control" required accept=".xlsx">
                    </div>
                    <div class="col-12 text-end mt-3">
                         <a href="users.php" class="btn btn-secondary me-2">Hủy bỏ</a>
                        <button type="submit" name="import_btn" class="btn btn-primary px-4">
                            <i class="bi bi-cloud-arrow-up-fill"></i> Tiến hành Import
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>