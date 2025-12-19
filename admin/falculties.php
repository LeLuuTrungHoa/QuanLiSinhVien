<?php
$page_title = "Quản lý Khoa";
require_once '../includes/session.php';
require_once '../includes/header.php';

// Handle Add/Edit/Delete Actions
$action = $_POST['action'] ?? '';
$message = '';
$error = '';

try {
    // ==== ADD NEW FACULTY ====
    if ($action === 'add') {
        $ma_khoa = $_POST['ma_khoa'];
        $ten_khoa = $_POST['ten_khoa'];

        $sql = "INSERT INTO khoa (ma_khoa, ten_khoa) VALUES (:ma_khoa, :ten_khoa)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['ma_khoa' => $ma_khoa, 'ten_khoa' => $ten_khoa]);
        $message = "Thêm khoa thành công!";
    }

    // ==== EDIT FACULTY ====
    if ($action === 'edit') {
        $id = $_POST['id'];
        $ma_khoa = $_POST['ma_khoa'];
        $ten_khoa = $_POST['ten_khoa'];

        $sql = "UPDATE khoa SET ma_khoa = :ma_khoa, ten_khoa = :ten_khoa WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['ma_khoa' => $ma_khoa, 'ten_khoa' => $ten_khoa, 'id' => $id]);
        $message = "Cập nhật khoa thành công!";
    }

    // ==== DELETE FACULTY ====
    if (isset($_GET['action']) && $_GET['action'] === 'delete') {
        $id = $_GET['id'];
        // Check if faculty is in use
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM lop_hoc WHERE khoa_id = :id");
        $stmt_check->execute(['id' => $id]);
        if ($stmt_check->fetchColumn() > 0) {
            $error = "Không thể xóa khoa này vì đang có lớp học liên kết.";
        } else {
            $sql = "DELETE FROM khoa WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $message = "Xóa khoa thành công!";
             // Redirect to avoid re-deleting on refresh
            header("Location: falculties.php?message=" . urlencode($message));
            exit();
        }
    }
     if (isset($_GET['message'])) {
        $message = $_GET['message'];
    }


} catch (PDOException $e) {
    if ($e->getCode() == '23000') { // Integrity constraint violation
        $error = "Lỗi: Mã khoa đã tồn tại. Vui lòng chọn mã khác.";
    } else {
        $error = "Lỗi CSDL: " . $e->getMessage();
    }
}

// Fetch all faculties
$faculties = $pdo->query("SELECT * FROM khoa ORDER BY ma_khoa ASC")->fetchAll();
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
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFacultyModal">
                    <i class="bi bi-plus-circle"></i> Thêm Khoa
                </button>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <i class="bi bi-building"></i> Danh sách Khoa
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã Khoa</th>
                                    <th>Tên Khoa</th>
                                    <th class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($faculties)): ?>
                                    <tr><td colspan="3" class="text-center text-muted">Chưa có dữ liệu khoa</td></tr>
                                <?php else: ?>
                                    <?php foreach ($faculties as $faculty): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($faculty['ma_khoa']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($faculty['ten_khoa']); ?></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-warning"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editFacultyModal"
                                                    data-entity-json='<?php echo htmlspecialchars(json_encode($faculty)); ?>'>
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <a href="falculties.php?action=delete&id=<?php echo $faculty['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc muốn xóa khoa <?php echo $faculty['ten_khoa']; ?>? Thao tác này không thể hoàn tác.');">
                                                <i class="bi bi-trash-fill"></i>
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

            <div class="modal fade" id="addFacultyModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="falculties.php" method="POST">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">Thêm Khoa Mới</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="action" value="add">
                                <div class="mb-3">
                                    <label for="add_ma_khoa" class="form-label">Mã Khoa</label>
                                    <input type="text" class="form-control" id="add_ma_khoa" name="ma_khoa" required placeholder="VD: CNTT">
                                </div>
                                <div class="mb-3">
                                    <label for="add_ten_khoa" class="form-label">Tên Khoa</label>
                                    <input type="text" class="form-control" id="add_ten_khoa" name="ten_khoa" required placeholder="VD: Công nghệ thông tin">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                <button type="submit" class="btn btn-primary">Thêm mới</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="editFacultyModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="falculties.php" method="POST">
                            <div class="modal-header bg-warning text-dark">
                                <h5 class="modal-title">Chỉnh sửa Khoa</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" id="edit_id">
                                <div class="mb-3">
                                    <label for="edit_ma_khoa" class="form-label">Mã Khoa</label>
                                    <input type="text" class="form-control" id="edit_ma_khoa" name="ma_khoa" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_ten_khoa" class="form-label">Tên Khoa</label>
                                    <input type="text" class="form-control" id="edit_ten_khoa" name="ten_khoa" required>
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
    const editModal = document.getElementById('editFacultyModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', event => {
            // Button that triggered the modal
            const button = event.relatedTarget;
            // Extract info from data-entity-json attributes
            const data = JSON.parse(button.getAttribute('data-entity-json'));
            
            // Update the modal's content.
            const modalBodyInputId = editModal.querySelector('#edit_id');
            const modalBodyInputMa = editModal.querySelector('#edit_ma_khoa');
            const modalBodyInputTen = editModal.querySelector('#edit_ten_khoa');

            modalBodyInputId.value = data.id;
            modalBodyInputMa.value = data.ma_khoa;
            modalBodyInputTen.value = data.ten_khoa;
        });
    }
</script>