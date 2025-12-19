<?php
$page_title = "Phân công Giảng dạy";
require_once '../includes/session.php';
require_once '../includes/header.php';

$message = '';
$error = '';

try {
    // ==== HANDLE ADD ASSIGNMENT ====
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
        $lecturer_id = $_POST['lecturer_id'];
        $subject_id = $_POST['subject_id'];
        $lop_hoc_id = $_POST['lop_hoc_id'];
        $hoc_ky = $_POST['hoc_ky'];
        $nam_hoc = $_POST['nam_hoc'];

        // Check for duplicates
        $stmt_check = $pdo->prepare("SELECT id FROM phan_cong WHERE lecturer_id = ? AND subject_id = ? AND lop_hoc_id = ? AND hoc_ky = ? AND nam_hoc = ?");
        $stmt_check->execute([$lecturer_id, $subject_id, $lop_hoc_id, $hoc_ky, $nam_hoc]);
        if ($stmt_check->fetch()) {
            $error = "Lỗi: Phân công này đã tồn tại.";
        } else {
            $sql = "INSERT INTO phan_cong (lecturer_id, subject_id, lop_hoc_id, hoc_ky, nam_hoc) VALUES (?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$lecturer_id, $subject_id, $lop_hoc_id, $hoc_ky, $nam_hoc]);
            $message = "Thêm phân công thành công!";
        }
    }

    // ==== HANDLE DELETE ASSIGNMENT ====
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = $_GET['id'];
        
        // Check if grades have been entered for this assignment
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM diem WHERE phan_cong_id = ?");
        $stmt_check->execute([$id]);
        if ($stmt_check->fetchColumn() > 0) {
            $error = "Không thể xóa phân công này vì đã có điểm được nhập.";
        } else {
            $sql = "DELETE FROM phan_cong WHERE id = ?";
            $pdo->prepare($sql)->execute([$id]);
            $message = "Xóa phân công thành công!";
        }
        // Redirect to clean the URL
        $redirect_url = "assignments.php";
        if ($message) $redirect_url .= "?message=" . urlencode($message);
        if ($error) $redirect_url .= "?error=" . urlencode($error);
        header("Location: " . $redirect_url);
        exit();
    }
     if (isset($_GET['message'])) $message = $_GET['message'];
    if (isset($_GET['error'])) $error = $_GET['error'];


    // Fetch data for dropdowns
    $lecturers = $pdo->query("SELECT id, full_name, username FROM users WHERE role = 'lecturer' ORDER BY full_name")->fetchAll();
    $subjects = $pdo->query("SELECT id, ten_mon, ma_mon FROM mon_hoc ORDER BY ten_mon")->fetchAll();
    $classes = $pdo->query("SELECT id, ten_lop FROM lop_hoc ORDER BY ten_lop")->fetchAll();

    // Fetch existing assignments
    $assignments = $pdo->query("
        SELECT pc.id, u.full_name AS lecturer_name, mh.ten_mon, lh.ten_lop, pc.hoc_ky, pc.nam_hoc
        FROM phan_cong pc
        JOIN users u ON pc.lecturer_id = u.id
        JOIN mon_hoc mh ON pc.subject_id = mh.id
        JOIN lop_hoc lh ON pc.lop_hoc_id = lh.id
        ORDER BY pc.nam_hoc DESC, pc.hoc_ky DESC, u.full_name ASC
    ")->fetchAll();

} catch (PDOException $e) {
    $error = "Lỗi CSDL: " . $e->getMessage();
}
?>

<div class="container-fluid">
    <div class="row">
        
        <?php include_once '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $page_title; ?></h1>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                   <i class="bi bi-person-plus-fill"></i> Thêm Phân công mới
                </div>
                <div class="card-body">
                    <form action="assignments.php" method="POST" class="row g-3">
                        <input type="hidden" name="action" value="add">
                        <div class="col-md-6 col-lg-3">
                            <label for="lecturer_id" class="form-label">Giảng viên</label>
                            <select id="lecturer_id" name="lecturer_id" class="form-select" required>
                                <option value="">-- Chọn Giảng viên --</option>
                                <?php foreach ($lecturers as $lecturer): ?>
                                    <option value="<?php echo $lecturer['id']; ?>"><?php echo htmlspecialchars($lecturer['full_name'] . ' (' . $lecturer['username'] . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="subject_id" class="form-label">Môn học</label>
                            <select id="subject_id" name="subject_id" class="form-select" required>
                                <option value="">-- Chọn Môn học --</option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['ten_mon'] . ' (' . $subject['ma_mon'] . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="lop_hoc_id" class="form-label">Lớp học</label>
                            <select id="lop_hoc_id" name="lop_hoc_id" class="form-select" required>
                                <option value="">-- Chọn Lớp --</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['ten_lop']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 col-lg-1">
                            <label for="hoc_ky" class="form-label">Học kỳ</label>
                            <input type="number" id="hoc_ky" name="hoc_ky" class="form-control" required min="1" max="3" value="1">
                        </div>
                        <div class="col-md-3 col-lg-2">
                             <label for="nam_hoc" class="form-label">Năm học</label>
                            <select id="nam_hoc" name="nam_hoc" class="form-select" required>
                                <?php 
                                $current_year = date('Y');
                                for ($i = $current_year - 2; $i <= $current_year + 1; $i++) {
                                    $year_range = $i . '-' . ($i + 1);
                                    echo "<option value='{$year_range}' " . ($i == $current_year ? 'selected' : '') . ">{$year_range}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-12 d-flex align-items-end mt-3">
                            <button type="submit" class="btn btn-primary w-100 w-md-auto">
                                <i class="bi bi-plus-circle"></i> Thêm phân công
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                   <i class="bi bi-list-task"></i> Danh sách Phân công
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Giảng viên</th>
                                    <th>Môn học</th>
                                    <th>Lớp</th>
                                    <th>Học kỳ</th>
                                    <th>Năm học</th>
                                    <th class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($assignments)): ?>
                                    <tr><td colspan="6" class="text-center text-muted">Chưa có dữ liệu phân công</td></tr>
                                <?php else: ?>
                                    <?php foreach ($assignments as $assignment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($assignment['lecturer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($assignment['ten_mon']); ?></td>
                                        <td><?php echo htmlspecialchars($assignment['ten_lop']); ?></td>
                                        <td><?php echo htmlspecialchars($assignment['hoc_ky']); ?></td>
                                        <td><?php echo htmlspecialchars($assignment['nam_hoc']); ?></td>
                                        <td class="text-center">
                                             <a href="assignments.php?action=delete&id=<?php echo $assignment['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc muốn xóa phân công này?');">
                                                <i class="bi bi-trash-fill"></i> Xóa
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main> </div> </div> <?php
require_once '../includes/footer.php';
?>