<?php
$page_title = "Trang giảng viên";
require_once '../includes/session.php';
require_once '../config/db.php';

if ($_SESSION['role'] !== 'lecturer') {
    $_SESSION['error_message'] = "Bạn không có quyền truy cập.";
    header("Location: ../login.php");
    exit();
}

// Lấy danh sách phân công
$stmt = $pdo->prepare("
    SELECT 
        pc.id,
        mh.ten_mon,
        lh.ten_lop,
        pc.hoc_ky,
        pc.nam_hoc
    FROM phan_cong pc
    JOIN mon_hoc mh ON pc.subject_id = mh.id
    JOIN lop_hoc lh ON pc.lop_hoc_id = lh.id
    WHERE pc.lecturer_id = ?
    ORDER BY pc.nam_hoc DESC, pc.hoc_ky DESC
");
$stmt->execute([$_SESSION['user_id']]);
$phan_congs = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mt-4">Chào mừng giảng viên, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h1>
            <h3 class="mt-4">Các môn học được phân công</h3>

            <?php if (!empty($phan_congs)): ?>
                <div class="row mt-4">
                    <?php foreach ($phan_congs as $pc): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($pc['ten_mon']) ?></h5>
                                <p class="card-text">
                                    <strong>Lớp:</strong> <?= htmlspecialchars($pc['ten_lop']) ?><br>
                                    <strong>Học kỳ:</strong> <?= $pc['hoc_ky'] ?>/<?= $pc['nam_hoc'] ?>
                                </p>
                                <!-- ✅ TRUYỀN phan_cong_id vào URL -->
                                <a href="grades.php?phan_cong_id=<?= (int)$pc['id'] ?>" class="btn btn-primary">Quản lí điểm</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Các chức năng chung -->
            
        </div>
    </div>
</div>
<?php
$schedules = [];

// Truy vấn lịch dạy nếu là giảng viên
if ($_SESSION['role'] === 'lecturer') {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                mh.ten_mon AS subject_name,
                lh.ten_lop AS class_name,
                pc.hoc_ky AS semester,
                pc.nam_hoc AS year,
                '7h-9h' AS time,       -- Giả sử thời gian cố định
                'P101' AS room         -- Giả sử phòng cố định
            FROM phan_cong pc
            JOIN mon_hoc mh ON pc.subject_id = mh.id
            JOIN lop_hoc lh ON pc.lop_hoc_id = lh.id
            WHERE pc.lecturer_id = ?
            ORDER BY pc.nam_hoc DESC, pc.hoc_ky DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $schedules = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Ghi log hoặc hiển thị lỗi cho dev
        error_log("Lỗi truy vấn lịch dạy: " . $e->getMessage());
        $schedules = []; // Đặt mảng rỗng nếu có lỗi
    }
}
?>

<!-- Hiển thị lịch dạy -->
<h2 class="mt-5">Lịch dạy tuần này</h2>
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-primary">
            <tr>
                <th>Môn học</th>
                <th>Lớp</th>
                <th>Thời gian</th>
                <th>Phòng</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($schedules)): ?>
                <?php foreach ($schedules as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['subject_name']) ?></td>
                        <td><?= htmlspecialchars($row['class_name']) ?></td>
                        <td><?= htmlspecialchars($row['time'] ?? 'Chưa có') ?></td>
                        <td><?= htmlspecialchars($row['room'] ?? 'Chưa có') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center">Chưa có lịch dạy.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once '../includes/footer.php'; ?>