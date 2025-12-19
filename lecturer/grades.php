<?php
$page_title = "Quản lý điểm";
require_once '../includes/session.php';
require_once '../config/db.php';

// 🔒 Chỉ giảng viên mới được truy cập
if ($_SESSION['role'] !== 'lecturer') {
    $_SESSION['error_message'] = "Bạn không có quyền truy cập.";
    header("Location: ../login.php");
    exit();
}

// Kiểm tra có truyền phan_cong_id không
if (!isset($_GET['phan_cong_id']) || !is_numeric($_GET['phan_cong_id'])) {
    $_SESSION['error_message'] = "Phân công không hợp lệ.";
    header("Location: index.php");
    exit();
}

$phan_cong_id = (int)$_GET['phan_cong_id'];

// Kiểm tra phân công có thuộc giảng viên không
$check = $pdo->prepare("SELECT id FROM phan_cong WHERE id = ? AND lecturer_id = ?");
$check->execute([$phan_cong_id, $_SESSION['user_id']]);
if (!$check->fetch()) {
    $_SESSION['error_message'] = "Bạn không được phân công lớp này.";
    header("Location: index.php");
    exit();
}

// Lấy thông tin phân công + danh sách sinh viên trong lớp + điểm hiện tại
$stmt = $pdo->prepare("
    SELECT 
        u.id AS student_id,
        u.full_name,
        d.id AS diem_id,
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

// Lấy thông tin môn & lớp để hiển thị tiêu đề
$info = $pdo->prepare("
    SELECT mh.ten_mon, lh.ten_lop
    FROM phan_cong pc
    JOIN mon_hoc mh ON pc.subject_id = mh.id
    JOIN lop_hoc lh ON pc.lop_hoc_id = lh.id
    WHERE pc.id = ?
");
$info->execute([$phan_cong_id]);
$course = $info->fetch();

require_once '../includes/header.php';
?>

<div class="container mt-4">
    <h2>Quản lý điểm: <?= htmlspecialchars($course['ten_mon'] ?? 'Môn học') ?> - <?= htmlspecialchars($course['ten_lop'] ?? 'Lớp') ?></h2>
    <a href="index.php" class="btn btn-secondary mb-3">← Quay lại</a>

    <?php if (empty($students)): ?>
        <div class="alert alert-warning">Không có sinh viên trong lớp này.</div>
    <?php else: ?>
        <form method="POST" action="save_grades.php">
            <input type="hidden" name="phan_cong_id" value="<?= $phan_cong_id ?>">

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Sinh viên</th>
                            <th>Điểm quá trình</th>
                            <th>Điểm giữa kỳ</th>
                            <th>Điểm cuối kỳ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $index => $s): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($s['full_name']) ?></td>
                            <td>
                                <input type="number" step="0.1" min="0" max="10" 
                                       name="grades[<?= $s['student_id'] ?>][diem_qua_trinh]"
                                       value="<?= $s['diem_qua_trinh'] !== null ? htmlspecialchars($s['diem_qua_trinh']) : '' ?>"
                                       class="form-control">
                            </td>
                            <td>
                                <input type="number" step="0.1" min="0" max="10" 
                                       name="grades[<?= $s['student_id'] ?>][diem_giua_ky]"
                                       value="<?= $s['diem_giua_ky'] !== null ? htmlspecialchars($s['diem_giua_ky']) : '' ?>"
                                       class="form-control">
                            </td>
                            <td>
                                <input type="number" step="0.1" min="0" max="10" 
                                       name="grades[<?= $s['student_id'] ?>][diem_cuoi_ky]"
                                       value="<?= $s['diem_cuoi_ky'] !== null ? htmlspecialchars($s['diem_cuoi_ky']) : '' ?>"
                                       class="form-control">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <button type="submit" class="btn btn-success">💾 Lưu điểm</button>
        </form>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>