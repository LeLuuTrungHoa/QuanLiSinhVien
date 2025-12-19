<?php
$page_title = "Đăng ký môn học";
require_once '../includes/session.php';
require_once '../config/db.php';

if ($_SESSION['role'] !== 'student') {
    $_SESSION['error_message'] = "Chỉ sinh viên mới có thể đăng ký môn học.";
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
require_once '../includes/header.php';
// Xử lý form: đăng ký hoặc hủy
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phan_cong_id = (int)($_POST['phan_cong_id'] ?? 0);
    
    if (!$phan_cong_id) {
        $_SESSION['error_message'] = "Phân công không hợp lệ.";
        header("Location: register.php");
        exit();
    }

    if (isset($_POST['register'])) {
        // Kiểm tra sĩ số (giới hạn 30)
        $checkCount = $pdo->prepare("
            SELECT COUNT(*) FROM dang_ky 
            WHERE phan_cong_id = ? AND status = 'active'
        ");
        $checkCount->execute([$phan_cong_id]);
        if ($checkCount->fetchColumn() >= 30) {
            $_SESSION['error_message'] = "Lớp đã đầy (tối đa 30 sinh viên).";
        } else {
            // Thêm vào dang_ky
            $insert = $pdo->prepare("
                INSERT IGNORE INTO dang_ky (student_id, phan_cong_id) 
                VALUES (?, ?)
            ");
            $insert->execute([$student_id, $phan_cong_id]);
            $_SESSION['success_message'] = "Đăng ký thành công!";
        }
    } elseif (isset($_POST['drop'])) {
        // Hủy đăng ký
        $update = $pdo->prepare("
            UPDATE dang_ky 
            SET status = 'dropped', updated_at = NOW() 
            WHERE student_id = ? AND phan_cong_id = ? AND status = 'active'
        ");
        $update->execute([$student_id, $phan_cong_id]);
        $_SESSION['success_message'] = "Đã hủy đăng ký.";
    }

    header("Location: register.php");
    exit();
}

// Lấy danh sách phân công (môn học mở)
$courses = $pdo->prepare("
    SELECT 
        pc.id AS phan_cong_id,
        mh.ten_mon,
        lh.ten_lop,
        u.full_name AS lecturer,
        pc.hoc_ky,
        pc.nam_hoc
    FROM phan_cong pc
    JOIN mon_hoc mh ON pc.subject_id = mh.id
    JOIN lop_hoc lh ON pc.lop_hoc_id = lh.id
    JOIN users u ON pc.lecturer_id = u.id
    ORDER BY pc.nam_hoc DESC, pc.hoc_ky DESC, mh.ten_mon
");
$courses->execute();
$courses = $courses->fetchAll();

// Lấy ID các môn đã đăng ký
$registered = $pdo->prepare("
    SELECT phan_cong_id FROM dang_ky 
    WHERE student_id = ? AND status = 'active'
");
$registered->execute([$student_id]);
$registered_ids = array_column($registered->fetchAll(), 'phan_cong_id');
?>



<div class="container mt-4">
    <h2>📝 Đăng ký môn học</h2>
    <p class="text-muted">Chọn môn học bạn muốn đăng ký. Mỗi lớp tối đa <strong>30 sinh viên</strong>.</p>

    <?php if (!empty($courses)): ?>
        <div class="row">
            <?php foreach ($courses as $c): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($c['ten_mon']) ?></h5>
                        <p class="card-text">
                            <small class="text-muted">
                                Lớp: <?= htmlspecialchars($c['ten_lop']) ?><br>
                                Giảng viên: <?= htmlspecialchars($c['lecturer']) ?><br>
                                Học kỳ: <?= (int)$c['hoc_ky'] ?>/<?= htmlspecialchars($c['nam_hoc']) ?>
                            </small>
                        </p>

                        <?php if (in_array($c['phan_cong_id'], $registered_ids)): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="phan_cong_id" value="<?= $c['phan_cong_id'] ?>">
                                <button type="submit" name="drop" class="btn btn-outline-danger btn-sm">
                                    Hủy đăng ký
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="phan_cong_id" value="<?= $c['phan_cong_id'] ?>">
                                <button type="submit" name="register" class="btn btn-primary btn-sm">
                                    Đăng ký
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Hiện chưa có môn học nào được mở để đăng ký.</div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>