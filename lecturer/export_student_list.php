<?php
require_once '../includes/session.php';
require_once '../config/db.php';

// 🔒 Chỉ giảng viên mới được truy cập
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'lecturer') {
    header("Content-Type: text/plain; charset=utf-8");
    die("Bạn không có quyền truy cập.");
}

// Kiểm tra có truyền phan_cong_id không
if (!isset($_GET['phan_cong_id']) || !is_numeric($_GET['phan_cong_id'])) {
    die("Phân công không hợp lệ.");
}

$phan_cong_id = (int)$_GET['phan_cong_id'];

// Kiểm tra phân công có thuộc giảng viên không
$check = $pdo->prepare("SELECT id FROM phan_cong WHERE id = ? AND lecturer_id = ?");
$check->execute([$phan_cong_id, $_SESSION['user_id']]);
if (!$check->fetch()) {
    die("Bạn không được phân công lớp này.");
}

// Lấy thông tin môn & lớp để đặt tên file
$info = $pdo->prepare("
    SELECT mh.ten_mon, lh.ten_lop
    FROM phan_cong pc
    JOIN mon_hoc mh ON pc.subject_id = mh.id
    JOIN lop_hoc lh ON pc.lop_hoc_id = lh.id
    WHERE pc.id = ?
");
$info->execute([$phan_cong_id]);
$course = $info->fetch();
$course_name = $course ? preg_replace('/[^a-zA-Z0-9_ -]/', '', $course['ten_mon']) : 'MonHoc';
$class_name = $course ? preg_replace('/[^a-zA-Z0-9_ -]/', '', $course['ten_lop']) : 'LopHoc';

// Lấy danh sách sinh viên và điểm
$stmt = $pdo->prepare("
    SELECT
        u.username AS mssv,
        u.full_name,
        d.diem_qua_trinh,
        d.diem_giua_ky,
        d.diem_cuoi_ky
    FROM users u
    JOIN lop_hoc lh ON u.lop_hoc_id = lh.id
    JOIN phan_cong pc ON pc.lop_hoc_id = lh.id
    LEFT JOIN diem d ON d.student_id = u.id AND d.phan_cong_id = pc.id
    WHERE pc.id = ? AND u.role = 'student'
    ORDER BY u.full_name
");
$stmt->execute([$phan_cong_id]);
$students = $stmt->fetchAll();


// === XUẤT FILE CSV ===
$filename = "DSSV_" . str_replace(' ', '_', $class_name) . "_" . str_replace(' ', '_', $course_name) . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Bổ sung BOM cho UTF-8 để Excel hiển thị đúng tiếng Việt
echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');

// Tiêu đề cột
fputcsv($output, [
    'STT',
    'MSSV',
    'Họ và tên',
    'Điểm quá trình',
    'Điểm giữa kỳ',
    'Điểm cuối kỳ'
]);

// Dữ liệu
$index = 1;
foreach ($students as $student) {
    fputcsv($output, [
        $index++,
        $student['mssv'],
        $student['full_name'],
        $student['diem_qua_trinh'] !== null ? number_format((float)$student['diem_qua_trinh'], 1) : '',
        $student['diem_giua_ky'] !== null ? number_format((float)$student['diem_giua_ky'], 1) : '',
        $student['diem_cuoi_ky'] !== null ? number_format((float)$student['diem_cuoi_ky'], 1) : '',
    ]);
}

fclose($output);
exit();
