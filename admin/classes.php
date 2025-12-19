<?php
$page_title = "Quản lý Lớp học";
require_once '../includes/session.php';
require_once '../includes/header.php';

// Handle Add/Edit/Delete Actions
$action = $_POST['action'] ?? '';
$message = '';
$error = '';

try {
    // ==== ADD NEW CLASS ====
    if ($action === 'add') {
        $ma_lop = $_POST['ma_lop'];
        $ten_lop = $_POST['ten_lop'];
        $khoa_id = $_POST['khoa_id'];

        $sql = "INSERT INTO lop_hoc (ma_lop, ten_lop, khoa_id) VALUES (:ma_lop, :ten_lop, :khoa_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['ma_lop' => $ma_lop, 'ten_lop' => $ten_lop, 'khoa_id' => $khoa_id]);
        $message = "Thêm lớp học thành công!";
    }

    // ==== EDIT CLASS ====
    if ($action === 'edit') {
        $id = $_POST['id'];
        $ma_lop = $_POST['ma_lop'];
        $ten_lop = $_POST['ten_lop'];
        $khoa_id = $_POST['khoa_id'];

        $sql = "UPDATE lop_hoc SET ma_lop = :ma_lop, ten_lop = :ten_lop, khoa_id = :khoa_id WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['ma_lop' => $ma_lop, 'ten_lop' => $ten_lop, 'khoa_id' => $khoa_id, 'id' => $id]);
        $message = "Cập nhật lớp học thành công!";
    }

    // ==== DELETE CLASS ====
     if (isset($_GET['action']) && $_GET['action'] === 'delete') {
        $id = $_GET['id'];
        // Check if class is in use by students
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE lop_hoc_id = :id");
        $stmt_check->execute(['id' => $id]);
        if ($stmt_check->fetchColumn() > 0) {
            $error = "Không thể xóa lớp này vì đang có sinh viên.";
        } else {
            $sql = "DELETE FROM lop_hoc WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $message = "Xóa lớp học thành công!";
            // Redirect để tránh lỗi refresh trang
            header("Location: classes.php?message=" . urlencode($message));
            exit();
        }
    }
     if (isset($_GET['message'])) {
        $message = $_GET['message'];
    }


} catch (PDOException $e) {
     if ($e->getCode() == '23000') {
        $error = "Lỗi: Mã lớp đã tồn tại. Vui lòng chọn mã khác.";
    } else {
        $error = "Lỗi CSDL: " . $e->getMessage();
    }
}

// Fetch all classes with faculty name
$classes = $pdo->query("
    SELECT lh.id, lh.ma_lop, lh.ten_lop, k.ten_khoa, lh.khoa_id
    FROM lop_hoc lh
    JOIN khoa k ON lh.khoa_id = k.id
    ORDER BY lh.ma_lop ASC
")->fetchAll();

// Fetch all faculties for dropdowns
$faculties = $pdo->query("SELECT id, ten_khoa FROM khoa ORDER BY ten_khoa ASC")->fetchAll();
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
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addClassModal">
                    <i class="bi bi-plus-circle"></i> Thêm Lớp học
                </button>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <i class="bi bi-list-ul"></i> Danh sách Lớp học
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã Lớp</th>
                                    <th>Tên Lớp</th>
                                    <th>Khoa</th>
                                    <th class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($classes as $class): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($class['ma_lop']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($class['ten_lop']); ?></td>
                                    <td><?php echo htmlspecialchars($class['ten_khoa']); ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-warning"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editClassModal"
                                                data-entity-json='<?php echo htmlspecialchars(json_encode($class)); ?>'>
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <a href="classes.php?action=delete&id=<?php echo $class['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc muốn xóa lớp <?php echo $class['ma_lop']; ?>?');">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="addClassModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="classes.php" method="POST">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">Thêm Lớp học Mới</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="action" value="add">
                                <div class="mb-3">
                                    <label for="add_ma_lop" class="form-label">Mã Lớp</label>
                                    <input type="text" class="form-control" id="add_ma_lop" name="ma_lop" required placeholder="Ví dụ: CNTT-K15">
                                </div>
                                <div class="mb-3">
                                    <label for="add_ten_lop" class="form-label">Tên Lớp</label>
                                    <input type="text" class="form-control" id="add_ten_lop" name="ten_lop" required placeholder="Ví dụ: Công nghệ thông tin K15">
                                </div>
                                <div class="mb-3">
                                    <label for="add_khoa_id" class="form-label">Khoa</label>
                                    <select class="form-select" id="add_khoa_id" name="khoa_id" required>
                                        <option value="">-- Chọn Khoa --</option>
                                        <?php foreach ($faculties as $faculty): ?>
                                        <option value="<?php echo $faculty['id']; ?>"><?php echo htmlspecialchars($faculty['ten_khoa']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                <button type="submit" class="btn btn-success">Thêm mới</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="editClassModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="classes.php" method="POST">
                            <div class="modal-header bg-warning text-dark">
                                <h5 class="modal-title">Chỉnh sửa Lớp học</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" id="edit_id">
                                <div class="mb-3">
                                    <label for="edit_ma_lop" class="form-label">Mã Lớp</label>
                                    <input type="text" class="form-control" id="edit_ma_lop" name="ma_lop" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_ten_lop" class="form-label">Tên Lớp</label>
                                    <input type="text" class="form-control" id="edit_ten_lop" name="ten_lop" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_khoa_id" class="form-label">Khoa</label>
                                    <select class="form-select" id="edit_khoa_id" name="khoa_id" required>
                                        <option value="">-- Chọn Khoa --</option>
                                        <?php foreach ($faculties as $faculty): ?>
                                        <option value="<?php echo $faculty['id']; ?>"><?php echo htmlspecialchars($faculty['ten_khoa']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                <button type="submit" class="btn btn-warning">Lưu thay đổi</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>

<script>
    const editModal = document.getElementById('editClassModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', event => {
            // Button that triggered the modal
            const button = event.relatedTarget;
            // Extract info from data-entity-json attributes
            const data = JSON.parse(button.getAttribute('data-entity-json'));
            
            // Update the modal's content.
            const modalBodyInputId = editModal.querySelector('#edit_id');
            const modalBodyInputMa = editModal.querySelector('#edit_ma_lop');
            const modalBodyInputTen = editModal.querySelector('#edit_ten_lop');
            const modalBodySelectKhoa = editModal.querySelector('#edit_khoa_id');

            modalBodyInputId.value = data.id;
            modalBodyInputMa.value = data.ma_lop;
            modalBodyInputTen.value = data.ten_lop;
            modalBodySelectKhoa.value = data.khoa_id;
        });
    }
</script>