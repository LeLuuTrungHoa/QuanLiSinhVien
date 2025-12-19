<?php
require_once '../includes/session.php';
require_once '../config/db.php';

if ($_SESSION['role'] !== 'lecturer') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$phan_cong_id = (int)($_POST['phan_cong_id'] ?? 0);
if (!$phan_cong_id) {
    $_SESSION['error_message'] = "Thiếu thông tin phân công.";
    header("Location: index.php");
    exit();
}

// Kiểm tra quyền: giảng viên có được phân công này không?
$check = $pdo->prepare("SELECT id FROM phan_cong WHERE id = ? AND lecturer_id = ?");
$check->execute([$phan_cong_id, $_SESSION['user_id']]);
if (!$check->fetch()) {
    $_SESSION['error_message'] = "Không có quyền lưu điểm cho lớp này.";
    header("Location: index.php");
    exit();
}

$grades = $_POST['grades'] ?? [];

try {
    $pdo->beginTransaction();

    foreach ($grades as $student_id => $data) {
        if (!is_numeric($student_id)) continue;

        $qt = !empty($data['diem_qua_trinh']) ? (float)$data['diem_qua_trinh'] : null;
        $gk = !empty($data['diem_giua_ky']) ? (float)$data['diem_giua_ky'] : null;
        $ck = !empty($data['diem_cuoi_ky']) ? (float)$data['diem_cuoi_ky'] : null;

        // Kiểm tra xem đã có dòng điểm chưa
        $checkDiem = $pdo->prepare("SELECT id FROM diem WHERE student_id = ? AND phan_cong_id = ?");
        $checkDiem->execute([$student_id, $phan_cong_id]);
        $existing = $checkDiem->fetch();

        if ($existing) {
            // Cập nhật
            $update = $pdo->prepare("
                UPDATE diem 
                SET diem_qua_trinh = ?, diem_giua_ky = ?, diem_cuoi_ky = ?
                WHERE id = ?
            ");
            $update->execute([$qt, $gk, $ck, $existing['id']]);
        } else {
            // Thêm mới (chỉ khi có ít nhất 1 điểm)
            if ($qt !== null || $gk !== null || $ck !== null) {
                $insert = $pdo->prepare("
                    INSERT INTO diem (student_id, subject_id, phan_cong_id, diem_qua_trinh, diem_giua_ky, diem_cuoi_ky)
                    SELECT ?, pc.subject_id, ?, ?, ?, ?
                    FROM phan_cong pc
                    WHERE pc.id = ?
                ");
                $insert->execute([$student_id, $phan_cong_id, $qt, $gk, $ck, $phan_cong_id]);
            }
        }
    }

    $pdo->commit();
    $_SESSION['success_message'] = "Đã lưu điểm thành công!";
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Lỗi lưu điểm: " . $e->getMessage());
    $_SESSION['error_message'] = "Có lỗi khi lưu điểm. Vui lòng thử lại.";
}

header("Location: grades.php?phan_cong_id=" . $phan_cong_id);
exit();
?>