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

// === LOGIC TÍNH TOÁN ĐIỂM VÀ GPA ===
// Ghi chú: File này thực hiện logic phức tạp để tính GPA, vui lòng đọc kỹ comment để hiểu rõ.

// 1. Lấy lớp của sinh viên để xác định chương trình học
$stmt = $pdo->prepare("SELECT lop_hoc_id FROM users WHERE id = ?");
$stmt->execute([$student_id]);
$student_class = $stmt->fetch();

$all_subjects_grades = [];
$incomplete_subjects_count = 0;
$total_points = 0;
$total_credits = 0;
$total_subjects_count = 0;

// Chỉ thực hiện tính toán nếu sinh viên đã được xếp lớp
if ($student_class && $student_class['lop_hoc_id']) {
    // 2. Lấy tất cả các môn học được phân công cho lớp của sinh viên.
    // Đây là nguồn chính để biết sinh viên phải học những môn nào.
    $stmt = $pdo->prepare("
        SELECT
            pc.id AS phan_cong_id,
            mh.ma_mon,
            mh.ten_mon,
            mh.so_tin_chi
        FROM phan_cong pc
        JOIN mon_hoc mh ON pc.subject_id = mh.id
        WHERE pc.lop_hoc_id = ?
        ORDER BY mh.ten_mon
    ");
    $stmt->execute([$student_class['lop_hoc_id']]);
    $assigned_subjects = $stmt->fetchAll();
    $total_subjects_count = count($assigned_subjects);

    // 3. Lặp qua từng môn học trong chương trình, tìm điểm và tính toán
    foreach ($assigned_subjects as $subject) {
        // Với mỗi môn học, truy vấn điểm của sinh viên
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

        // Chuẩn bị một mảng để chứa dữ liệu của môn học này, sẽ được dùng để hiển thị ở bảng điểm dưới
        $subject_data = [
            'ma_mon' => $subject['ma_mon'],
            'ten_mon' => $subject['ten_mon'],
            'so_tin_chi' => $subject['so_tin_chi'],
            'diem_qua_trinh' => $diem_qt,
            'diem_giua_ky' => $diem_gk,
            'diem_cuoi_ky' => $diem_ck,
            'diem_he_10' => null, // Điểm tổng kết theo thang 10
            'diem_he_4' => null,  // Điểm tổng kết theo thang 4 (GPA)
        ];

        // *** QUY TẮC QUAN TRỌNG: Kiểm tra môn học đã có ĐỦ 3 cột điểm chưa ***
        // Nếu một trong các điểm là null, môn này sẽ không được tính vào GPA.
        if ($diem_qt !== null && $diem_gk !== null && $diem_ck !== null) {
            // Môn học đủ điểm, tiến hành tính toán
            
            // a. Tính điểm hệ 10 (ví dụ: QT 20%, GK 30%, CK 50%)
            $score_10 = ($diem_qt * 0.2) + ($diem_gk * 0.3) + ($diem_ck * 0.5);
            $subject_data['diem_he_10'] = $score_10;

            // b. Quy đổi điểm từ thang 10 sang thang 4
            $gpa_point = 0.0;
            if ($score_10 >= 8.5) $gpa_point = 4.0;
            elseif ($score_10 >= 8.0) $gpa_point = 3.5; // B+
            elseif ($score_10 >= 7.0) $gpa_point = 3.0; // B
            elseif ($score_10 >= 6.5) $gpa_point = 2.5; // C+
            elseif ($score_10 >= 5.5) $gpa_point = 2.0; // C
            elseif ($score_10 >= 5.0) $gpa_point = 1.5; // D+
            elseif ($score_10 >= 4.0) $gpa_point = 1.0; // D
            else $gpa_point = 0.0; // F
            
            $subject_data['diem_he_4'] = $gpa_point;
            
            // c. Cộng dồn điểm và tín chỉ để tính GPA chung
            $credit = (int)$subject['so_tin_chi'];
            $total_points += $gpa_point * $credit; // Tổng điểm = (điểm hệ 4 * số tín chỉ)
            $total_credits += $credit;             // Tổng số tín chỉ
        } else {
            // Nếu chưa đủ điểm, đánh dấu là môn chưa hoàn thành
            $incomplete_subjects_count++;
        }
        
        // Thêm dữ liệu môn này vào mảng lớn để hiển thị
        $all_subjects_grades[] = $subject_data;
    }
}

// 4. Tính GPA cuối cùng và xếp loại
// GPA = Tổng điểm (đã nhân tín chỉ) / Tổng tín chỉ
$gpa = ($total_credits > 0) ? round($total_points / $total_credits, 2) : 0.0;

// Xếp loại học lực dựa trên GPA
$rank = 'Chưa xếp loại';
if ($gpa > 0) {
    if ($gpa >= 3.6) $rank = 'Xuất sắc';
    elseif ($gpa >= 3.2) $rank = 'Giỏi';
    elseif ($gpa >= 2.5) $rank = 'Khá';
    elseif ($gpa >= 2.0) $rank = 'Trung bình';
    else $rank = 'Yếu';
}

// 5. Lấy lịch học (các môn đã đăng ký) để hiển thị riêng
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


?>
<?php require_once '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mt-4">Chào mừng sinh viên, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h1>

            <!-- === KHU VỰC HIỂN THỊ GPA VÀ XẾP LOẠI === -->
            <div class="card mt-4 shadow-sm">
                <div class="card-header">
                    <h4>
                        <?php if ($incomplete_subjects_count > 0 && $total_subjects_count > 0): ?>
                            Kết quả học tập tạm thời
                        <?php else: ?>
                            Kết quả học tập chính thức
                        <?php endif; ?>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h5>
                                <?php if ($incomplete_subjects_count > 0 && $total_subjects_count > 0): ?>
                                    GPA tạm thời
                                <?php else: ?>
                                    GPA tích lũy
                                <?php endif; ?>
                            </h5>
                            <h2 class="text-primary"><?= number_format($gpa, 2) ?>/4.00</h2>
                        </div>
                        <div class="col-md-6">
                            <h5>
                                <?php if ($incomplete_subjects_count > 0 && $total_subjects_count > 0): ?>
                                    Xếp loại tạm thời
                                <?php else: ?>
                                    Xếp loại học lực
                                <?php endif; ?>
                            </h5>
                            <h2 class="<?php
                                if ($gpa >= 3.2) echo 'text-success';
                                elseif ($gpa >= 2.0) echo 'text-warning';
                                else echo 'text-danger';
                            ?>">
                                <?= htmlspecialchars($rank) ?>
                            </h2>
                        </div>
                    </div>
                    
                    <?php // Hiển thị thông báo nếu còn môn chưa đủ điểm ?>
                    <?php if ($incomplete_subjects_count > 0 && $total_subjects_count > 0): ?>
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="fas fa-info-circle"></i> 
                            Ghi chú: Còn <strong><?= $incomplete_subjects_count ?></strong>/<?= $total_subjects_count ?> môn chưa có điểm đầy đủ để tính vào GPA chính thức.
                            <br>
                            <small class="text-muted">GPA tạm thời chỉ được tính dựa trên các môn đã hoàn thành.</small>
                        </div>
                    <?php endif; ?>

                    <?php // Hiển thị cảnh báo học vụ nếu GPA thấp và đã có đủ điểm các môn ?>
                     <?php if ($gpa > 0 && $gpa < 2.0 && $incomplete_subjects_count == 0): ?>
                        <div class="alert alert-danger mt-3 mb-0">
                            ⚠️ <strong>Cảnh báo học vụ:</strong> GPA của bạn dưới 2.0. Vui lòng liên hệ cố vấn học tập!
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- === CHỨC NĂNG NHANH === -->
            <div class="row mt-4">
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">📝 Đăng ký môn học</h5>
                            <p class="card-text">Xem các môn học có thể đăng ký trong học kỳ mới.</p>
                            <a href="register.php" class="btn btn-primary mt-auto">Vào đăng ký</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- === LỊCH HỌC ĐÃ ĐĂNG KÝ === -->
            <h2 class="mt-5">Lịch học đã đăng ký</h2>
            <?php if (empty($schedules)): ?>
                <div class="alert alert-info">Bạn chưa đăng ký môn nào cho học kỳ này. <a href="register.php">Đăng ký ngay</a>.</div>
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
            
            <!-- === BẢNG ĐIỂM CHI TIẾT === -->
            <div class="d-flex justify-content-between align-items-center mt-5">
                <h2 class="mb-0">Bảng điểm chi tiết</h2>
                <a href="export_grades.php" target="_blank" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Xuất Excel
                </a>
            </div>
            <?php if (empty($all_subjects_grades)): ?>
                <div class="alert alert-info">Chưa có dữ liệu điểm cho chương trình học của bạn.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th class="text-center">Mã môn học</th>
                                <th>Tên môn học</th>
                                <th class="text-center">Số tín chỉ</th>
                                <th class="text-center">Quá trình</th>
                                <th class="text-center">Giữa kỳ</th>
                                <th class="text-center">Cuối kỳ</th>
                                <th class="text-center bg-secondary text-white">Điểm hệ 10</th>
                                <th class="text-center bg-secondary text-white">Điểm hệ 4</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_subjects_grades as $index => $g): ?>
                            <tr>
                                <td class="text-center"><?= htmlspecialchars($g['ma_mon']) ?></td>
                                <td><?= htmlspecialchars($g['ten_mon']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($g['so_tin_chi']) ?></td>
                                <td class="text-center"><?= $g['diem_qua_trinh'] !== null ? number_format((float)$g['diem_qua_trinh'], 1) : '–' ?></td>
                                <td class="text-center"><?= $g['diem_giua_ky'] !== null ? number_format((float)$g['diem_giua_ky'], 1) : '–' ?></td>
                                <td class="text-center"><?= $g['diem_cuoi_ky'] !== null ? number_format((float)$g['diem_cuoi_ky'], 1) : '–' ?></td>
                                <td class="text-center fw-bold <?= $g['diem_he_10'] === null ? 'text-muted' : '' ?>">
                                    <?= $g['diem_he_10'] !== null ? number_format((float)$g['diem_he_10'], 2) : 'N/A' ?>
                                </td>
                                <td class="text-center fw-bold <?= $g['diem_he_4'] === null ? 'text-muted' : '' ?>">
                                    <?= $g['diem_he_4'] !== null ? number_format((float)$g['diem_he_4'], 2) : 'N/A' ?>
                                </td>
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
