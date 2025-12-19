<?php
$page_title = "Trang sinh viên";
require_once '../includes/session.php';
require_once '../config/db.php';

// 🔒 Bảo vệ: chỉ sinh viên mới truy cập được
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    $_SESSION['error_message'] = "Bạn không có quyền truy cập.";
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// === HÀM TÍNH GPA VÀ XẾP LOẠI ===
function calculateGPAAndRank($pdo, $student_id) {
    $stmt = $pdo->prepare("
        SELECT 
            d.diem_qua_trinh,
            d.diem_giua_ky,
            d.diem_cuoi_ky,
            mh.so_tin_chi
        FROM diem d
        JOIN phan_cong pc ON d.phan_cong_id = pc.id
        JOIN mon_hoc mh ON pc.subject_id = mh.id
        WHERE d.student_id = ? AND d.diem_cuoi_ky IS NOT NULL
    ");
    $stmt->execute([$student_id]);
    $records = $stmt->fetchAll();

    if (empty($records)) {
        return ['gpa' => 0.0, 'rank' => 'Chưa xếp loại'];
    }

    $total_points = 0;
    $total_credits = 0;

    foreach ($records as $r) {
        // Tính điểm tổng (hệ số: QT 20%, GK 30%, CK 50%)
        $score = 
            ($r['diem_qua_trinh'] ?? 0) * 0.2 +
            ($r['diem_giua_ky'] ?? 0) * 0.3 +
            ($r['diem_cuoi_ky'] ?? 0) * 0.5;

        // Quy đổi sang thang GPA 4.0
        if ($score >= 8.5) $gpa_point = 4.0;
        elseif ($score >= 7.0) $gpa_point = 3.0;
        elseif ($score >= 5.5) $gpa_point = 2.0;
        elseif ($score >= 4.0) $gpa_point = 1.0;
        else $gpa_point = 0.0;

        $credit = (int)($r['so_tin_chi'] ?? 0);
        $total_points += $gpa_point * $credit;
        $total_credits += $credit;
    }

    if ($total_credits == 0) {
        return ['gpa' => 0.0, 'rank' => 'Chưa xếp loại'];
    }

    $gpa = round($total_points / $total_credits, 2);

    // Xếp loại học lực
    if ($gpa >= 3.6) $rank = 'Xuất sắc';
    elseif ($gpa >= 3.2) $rank = 'Giỏi';
    elseif ($gpa >= 2.5) $rank = 'Khá';
    elseif ($gpa >= 2.0) $rank = 'Trung bình';
    elseif ($gpa >= 1.0) $rank = 'Yếu';
    else $rank = 'Kém';

    return ['gpa' => $gpa, 'rank' => $rank];
}

// === LẤY DỮ LIỆU ===
// Lịch học (từ dang_ky)
$schedules = [];
$stmt = $pdo->prepare("
    SELECT 
        mh.ten_mon AS subject_name,
        u.full_name AS lecturer_name,
        pc.hoc_ky,
        pc.nam_hoc
    FROM dang_ky dk
    JOIN phan_cong pc ON dk.phan_cong_id = pc.id
    JOIN mon_hoc mh ON pc.subject_id = mh.id
    JOIN users u ON pc.lecturer_id = u.id
    WHERE dk.student_id = ? AND dk.status = 'active'
    ORDER BY pc.nam_hoc DESC, pc.hoc_ky DESC, mh.ten_mon
");
$stmt->execute([$student_id]);
$schedules = $stmt->fetchAll();

// Bảng điểm
$grades = [];
$stmt = $pdo->prepare("
    SELECT 
        mh.ten_mon,
        d.diem_qua_trinh,
        d.diem_giua_ky,
        d.diem_cuoi_ky
    FROM diem d
    JOIN phan_cong pc ON d.phan_cong_id = pc.id
    JOIN mon_hoc mh ON pc.subject_id = mh.id
    WHERE d.student_id = ?
    ORDER BY mh.ten_mon
");
$stmt->execute([$student_id]);
$grades = $stmt->fetchAll();

// Tính GPA & xếp loại
$gpaInfo = calculateGPAAndRank($pdo, $student_id);
$gpa = $gpaInfo['gpa'];
$rank = $gpaInfo['rank'];
?>

<?php require_once '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mt-4">Chào mừng sinh viên, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h1>

            <!-- === GPA & XẾP LOẠI === -->
            <div class="row mt-4">
                <div class="col-md-6 mb-4">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Điểm trung bình (GPA)</h5>
                            <h2 class="text-primary"><?= number_format($gpa, 2) ?>/4.0</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Xếp loại học lực</h5>
                            <h2 class="<?php 
                                if ($gpa >= 2.5) echo 'text-success';
                                elseif ($gpa >= 2.0) echo 'text-warning';
                                else echo 'text-danger';
                            ?>">
                                <?= htmlspecialchars($rank) ?>
                            </h2>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($gpa > 0 && $gpa < 2.0): ?>
                <div class="alert alert-danger">
                    ⚠️ <strong>Cảnh báo học vụ:</strong> GPA của bạn dưới 2.0. Vui lòng liên hệ cố vấn học tập!
                </div>
            <?php endif; ?>

            <!-- === CHỨC NĂNG NHANH === -->
            <div class="row mt-4">
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">📝 Đăng ký môn học</h5>
                            <a href="register.php" class="btn btn-primary">Vào đăng ký</a>
                        </div>
                    </div>
                </div>
                
            </div>

            <!-- === LỊCH HỌC === -->
            <h2 class="mt-5">Lịch học học kỳ này</h2>
            <?php if (empty($schedules)): ?>
                <div class="alert alert-info">Bạn chưa đăng ký môn nào. <a href="register.php">Đăng ký ngay</a>.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-primary">
                            <tr>
                                <th>Môn học</th>
                                <th>Giảng viên</th>
                                <th>Học kỳ</th>
                                <th>Năm học</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schedules as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['subject_name']) ?></td>
                                <td><?= htmlspecialchars($s['lecturer_name']) ?></td>
                                <td><?= (int)$s['hoc_ky'] ?></td>
                                <td><?= htmlspecialchars($s['nam_hoc']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- === BẢNG ĐIỂM === -->
            <h2 class="mt-5">Bảng điểm cá nhân</h2>
            <?php if (empty($grades)): ?>
                <p class="text-muted">Chưa có điểm nào được nhập.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-primary">
                            <tr>
                                <th>Môn học</th>
                                <th>Quá trình</th>
                                <th>Giữa kỳ</th>
                                <th>Cuối kỳ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grades as $g): ?>
                            <tr>
                                <td><?= htmlspecialchars($g['ten_mon']) ?></td>
                                <td><?= $g['diem_qua_trinh'] !== null ? number_format((float)$g['diem_qua_trinh'], 1) : '–' ?></td>
                                <td><?= $g['diem_giua_ky'] !== null ? number_format((float)$g['diem_giua_ky'], 1) : '–' ?></td>
                                <td><?= $g['diem_cuoi_ky'] !== null ? number_format((float)$g['diem_cuoi_ky'], 1) : '–' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>