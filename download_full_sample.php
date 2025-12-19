<?php
// admin/download_full_sample.php
require '../vendor/autoload.php';
session_start();

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Bạn không có quyền truy cập file này.");
}

// Kết nối database (Sử dụng config của bạn)
require_once '../config/db.php'; 

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

// ==========================================
// 1. CHUẨN BỊ DỮ LIỆU TỪ DATABASE
// ==========================================
try {
    // Lấy tất cả tên lớp để làm Dropdown list
    $stmt = $pdo->query("SELECT ten_lop FROM lop_hoc ORDER BY ten_lop ASC");
    $all_classes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($all_classes)) {
        $all_classes = ['Chưa có lớp']; // Fallback nếu DB trống
    }
} catch (Exception $e) {
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Mau_Import');

// ==========================================
// 2. TẠO HEADER VÀ STYLE
// ==========================================
$headers = ['Họ và tên', 'Email', 'Mã (Username)', 'SĐT', 'Giới tính', 'Ngày sinh', 'Địa chỉ', 'Tên Lớp (Chính xác)'];
$sheet->fromArray($headers, NULL, 'A1');

// Style cho Header (Nền xanh, chữ trắng, in đậm, căn giữa)
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0d6efd']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
];
$sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
$sheet->getRowDimension('1')->setRowHeight(30);

// ==========================================
// 3. XỬ LÝ DROPDOWN LIST (DATA VALIDATION)
// ==========================================

// --- A. Tạo Sheet ẩn chứa dữ liệu Lớp học (Tránh lỗi giới hạn ký tự của Excel) ---
$sheet2 = $spreadsheet->createSheet();
$sheet2->setTitle('HiddenData');
// Đổ dữ liệu lớp vào cột A của sheet ẩn
foreach ($all_classes as $index => $className) {
    $sheet2->setCellValue('A' . ($index + 1), $className);
}
// Ẩn sheet này đi để người dùng không xóa nhầm
$sheet2->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

// --- B. Tạo Dropdown cho cột H (Tên lớp) ---
$validationClass = $sheet->getCell('H2')->getDataValidation();
$validationClass->setType(DataValidation::TYPE_LIST);
$validationClass->setErrorStyle(DataValidation::STYLE_STOP);
$validationClass->setAllowBlank(false);
$validationClass->setShowInputMessage(true);
$validationClass->setShowErrorMessage(true);
$validationClass->setShowDropDown(true);
$validationClass->setErrorTitle('Lỗi nhập liệu');
$validationClass->setError('Vui lòng chọn tên lớp từ danh sách!');
// Tham chiếu đến vùng dữ liệu ở Sheet ẩn
$totalClasses = count($all_classes);
$validationClass->setFormula1("'HiddenData'!\$A\$1:\$A\$$totalClasses");

// Áp dụng Dropdown này cho 1000 dòng đầu tiên của cột H
$sheet->setDataValidation('H2:H1000', $validationClass);

// --- C. Tạo Dropdown cho cột E (Giới tính) ---
$validationGender = $sheet->getCell('E2')->getDataValidation();
$validationGender->setType(DataValidation::TYPE_LIST);
$validationGender->setErrorStyle(DataValidation::STYLE_STOP);
$validationGender->setAllowBlank(false);
$validationGender->setShowInputMessage(true);
$validationGender->setShowDropDown(true);
$validationGender->setFormula1('"Nam,Nữ"'); // Giá trị cứng

// Áp dụng Dropdown này cho 1000 dòng đầu tiên của cột E
$sheet->setDataValidation('E2:E1000', $validationGender);

// ==========================================
// 4. DỮ LIỆU MẪU & FORMAT CỘT
// ==========================================
$sheet->setCellValue('A2', 'Nguyễn Văn Mẫu');
$sheet->setCellValue('B2', 'mau.nv@student.edu.vn');
$sheet->setCellValue('C2', 'SV9999');
$sheet->setCellValue('D2', '0988888888');
$sheet->setCellValue('E2', 'Nam');
$sheet->setCellValue('F2', '15/08/2002'); // Nên nhập dạng Text để tránh Excel tự đổi format
$sheet->setCellValue('G2', 'Hà Nội');
$sheet->setCellValue('H2', $all_classes[0] ?? ''); // Lấy lớp đầu tiên làm mẫu

// Format cột Ngày sinh (F) thành dạng Text để người dùng nhập dd/mm/yyyy không bị lỗi
$sheet->getStyle('F2:F1000')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

// Tự động độ rộng cột
$columnWidths = ['A'=>25, 'B'=>30, 'C'=>15, 'D'=>15, 'E'=>10, 'F'=>15, 'G'=>20, 'H'=>25];
foreach ($columnWidths as $col => $width) {
    $sheet->getColumnDimension($col)->setWidth($width);
}

// Thêm chú thích (Comment) hướng dẫn nhập ngày
$sheet->getComment('F1')->getText()->createTextRun('Nhập định dạng: ngày/tháng/năm (dd/mm/yyyy)');

// ==========================================
// 5. XUẤT FILE
// ==========================================
$filename = 'mau_import_' . date('Y-m-d') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>