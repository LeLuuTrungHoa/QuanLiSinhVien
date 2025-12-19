<?php
// admin/export_users.php
require_once '../includes/session.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Bạn không có quyền truy cập file này.");
}

// Lấy tham số lọc từ URL
$role_filter = $_GET['role'] ?? 'student'; // Mặc định xuất Sinh viên
$class_filter_id = $_GET['class_id'] ?? ''; // Lọc theo lớp (nếu có)
$search_term = $_GET['search'] ?? '';       // Lọc theo từ khóa tìm kiếm (nếu có)

// ==========================================
// 1. TRUY VẤN DỮ LIỆU TỪ DATABASE
// ==========================================
try {
    // Xây dựng câu SQL (BỎ JOIN BẢNG KHOA)
    $sql = "SELECT u.*, l.ten_lop 
            FROM users u 
            LEFT JOIN lop_hoc l ON u.lop_hoc_id = l.id
            WHERE u.role = :role";
    
    $params = [':role' => $role_filter];

    // Lọc theo từ khóa tìm kiếm (Mã hoặc Tên)
    if (!empty($search_term)) {
        $sql .= " AND (u.username LIKE :search OR u.full_name LIKE :search)";
        $params[':search'] = '%' . $search_term . '%';
    }

    // Nếu lọc theo lớp (chỉ áp dụng cho sinh viên nếu cần)
    if ($role_filter == 'student' && !empty($class_filter_id)) {
        $sql .= " AND u.lop_hoc_id = :class_id";
        $params[':class_id'] = $class_filter_id;
    }

    $sql .= " ORDER BY u.username ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Lỗi truy vấn dữ liệu: " . $e->getMessage());
}

// ==========================================
// 2. KHỞI TẠO EXCEL & HEADER
// ==========================================
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Danh_sach_' . ucfirst($role_filter));

// Đặt tiêu đề cột (BỎ CỘT KHOA)
// Nếu là Giảng viên, ta cũng có thể ẩn luôn cột Lớp nếu muốn, 
// nhưng ở đây tôi giữ lại tiêu đề "Lớp" chung để format bảng đồng nhất.
$headers = [
    'STT', 
    'Mã (Username)', 
    'Họ và tên', 
    'Email', 
    'SĐT', 
    'Giới tính', 
    'Ngày sinh', 
    'Địa chỉ', 
    'Lớp' // Cột cuối luôn là Lớp (Sinh viên có, Giảng viên trống)
];

$sheet->fromArray($headers, NULL, 'A1');

// Style Header (Xanh dương, chữ trắng, in đậm)
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0d6efd']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];
$sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
$sheet->getRowDimension('1')->setRowHeight(25);

// ==========================================
// 3. ĐỔ DỮ LIỆU VÀO CELL
// ==========================================
$rowNum = 2;
foreach ($users as $index => $user) {
    // Xử lý ngày sinh
    $dob = !empty($user['birthday']) ? date('d/m/Y', strtotime($user['birthday'])) : '';
    
    // Xử lý cột Lớp
    // Nếu có tên lớp thì hiện, không thì để trống
    $className = $user['ten_lop'] ?? ''; 

    $sheet->setCellValue('A' . $rowNum, $index + 1);
    $sheet->setCellValue('B' . $rowNum, $user['username']);
    $sheet->setCellValue('C' . $rowNum, $user['full_name']);
    $sheet->setCellValue('D' . $rowNum, $user['email']);
    // Ép kiểu chuỗi cho SĐT
    $sheet->setCellValueExplicit('E' . $rowNum, $user['phone'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('F' . $rowNum, $user['gender']);
    $sheet->setCellValue('G' . $rowNum, $dob);
    $sheet->setCellValue('H' . $rowNum, $user['address']);
    $sheet->setCellValue('I' . $rowNum, $className); // Chỉ hiển thị tên lớp

    $rowNum++;
}

// ==========================================
// 4. FORMAT GIAO DIỆN (AUTO SIZE & BORDER)
// ==========================================

// Auto-size các cột
foreach (range('A', 'I') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Kẻ khung toàn bộ bảng dữ liệu
$dataRange = 'A1:I' . ($rowNum - 1);
$sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Căn giữa các cột ngắn
$sheet->getStyle('A2:A' . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // STT
$sheet->getStyle('B2:B' . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Mã
$sheet->getStyle('E2:G' . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // SĐT, Gender, DOB
$sheet->getStyle('I2:I' . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Lớp

// ==========================================
// 5. XUẤT FILE RA BROWSER
// ==========================================
$role_vn = ($role_filter == 'student') ? 'SinhVien' : 'GiangVien';
$filename = 'DanhSach_' . $role_vn . '_' . date('Y-m-d_H-i') . '.xlsx';

// Xóa buffer để tránh file bị lỗi
ob_end_clean(); 

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>