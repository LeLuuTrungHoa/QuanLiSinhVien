<?php
require_once '../includes/session.php';
require_once '../config/db.php';

// Bảo vệ: chỉ sinh viên mới truy cập được
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Content-Type: text/plain; charset=utf-8");
    die("Bạn không có quyền truy cập.");
}

$student_id = $_SESSION['user_id'];

// === LOGIC TÍNH TOÁN ĐIỂM (Tương tự student/index.php) ===
$stmt = $pdo->prepare("SELECT lop_hoc_id FROM users WHERE id = ?");
$stmt->execute([$student_id]);
$student_class = $stmt->fetch();

$all_subjects_grades = [];
if ($student_class && $student_class['lop_hoc_id']) {
    $stmt = $pdo->prepare("
        SELECT
            pc.id AS phan_cong_id,
            mh.ten_mon,
            mh.so_tin_chi
        FROM phan_cong pc
        JOIN mon_hoc mh ON pc.subject_id = mh.id
        WHERE pc.lop_hoc_id = ?
        ORDER BY mh.ten_mon
    ");
    $stmt->execute([$student_class['lop_hoc_id']]);
    $assigned_subjects = $stmt->fetchAll();

    foreach ($assigned_subjects as $subject) {
        $stmt_grade = $pdo->prepare("
            SELECT diem_qua_trinh, diem_giua_ky, diem_cuoi_ky
            FROM diem
            WHERE student_id = ? AND phan_cong_id = ?
        ");
        $stmt_grade->execute([$student_id, $subject['phan_cong_id']]);
        $grade = $stmt_grade->fetch();

        $diem_qt = $grade['diem_qua_trinh'] ?? null;
        $diem_gk = $grade['diem_giua_ky'] ?? null;
        $diem_ck = $grade['diem_cuoi_ky'] ?? null;

        $score_10 = null;
        $gpa_point = null;

        if ($diem_qt !== null && $diem_gk !== null && $diem_ck !== null) {
            $score_10 = ($diem_qt * 0.2) + ($diem_gk * 0.3) + ($diem_ck * 0.5);

            if ($score_10 >= 8.5) $gpa_point = 4.0;
            elseif ($score_10 >= 8.0) $gpa_point = 3.5;
            elseif ($score_10 >= 7.0) $gpa_point = 3.0;
            elseif ($score_10 >= 6.5) $gpa_point = 2.5;
            elseif ($score_10 >= 5.5) $gpa_point = 2.0;
            elseif ($score_10 >= 5.0) $gpa_point = 1.5;
            elseif ($score_10 >= 4.0) $gpa_point = 1.0;
            else $gpa_point = 0.0;
        }

        $all_subjects_grades[] = [
            'ten_mon' => $subject['ten_mon'],
            'so_tin_chi' => $subject['so_tin_chi'],
            'diem_qua_trinh' => $diem_qt,
            'diem_giua_ky' => $diem_gk,
            'diem_cuoi_ky' => $diem_ck,
            'diem_he_10' => $score_10,
            'diem_he_4' => $gpa_point,
        ];
    }
}

// === XUẤT FILE CSV ===
$student_name_sanitized = preg_replace('/[^a-zA-Z0-9_ -]/', '', $_SESSION['full_name']);
$filename = "Bang_Diem_" . str_replace(' ', '_', $student_name_sanitized) . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Bổ sung BOM cho UTF-8 để Excel hiển thị đúng tiếng Việt
echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');

// Tiêu đề cột
fputcsv($output, [
    'STT',
    'Tên môn học',
    'Số tín chỉ',
    'Điểm quá trình',
    'Điểm giữa kỳ',
    'Điểm cuối kỳ',
    'Điểm hệ 10',
    'Điểm hệ 4'
]);

// Dữ liệu
$index = 1;
foreach ($all_subjects_grades as $grade) {
    fputcsv($output, [
        $index++,
        $grade['ten_mon'],
        $grade['so_tin_chi'],
        $grade['diem_qua_trinh'] !== null ? number_format((float)$grade['diem_qua_trinh'], 1) : '',
        $grade['diem_giua_ky'] !== null ? number_format((float)$grade['diem_giua_ky'], 1) : '',
        $grade['diem_cuoi_ky'] !== null ? number_format((float)$grade['diem_cuoi_ky'], 1) : '',
        $grade['diem_he_10'] !== null ? number_format((float)$grade['diem_he_10'], 2) : '',
        $grade['diem_he_4'] !== null ? number_format((float)$grade['diem_he_4'], 2) : '',
    ]);
}

fclose($output);
exit();
