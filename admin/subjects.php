<?php
$page_title = "Quản lý Môn học";
require_once '../includes/session.php';
require_once '../includes/header.php';

// Handle Add/Edit/Delete Actions
$action = $_POST['action'] ?? '';
$message = '';
$error = '';

try {
    // ==== ADD NEW SUBJECT ====
    if ($action === 'add') {
        $ma_mon = $_POST['ma_mon'];
        $ten_mon = $_POST['ten_mon'];
        $so_tin_chi = $_POST['so_tin_chi'];
        $khoa_id = $_POST['khoa_id'];

        $sql = "INSERT INTO mon_hoc (ma_mon, ten_mon, so_tin_chi, khoa_id) VALUES (:ma_mon, :ten_mon, :so_tin_chi, :khoa_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'ma_mon' => $ma_mon,
            'ten_mon' => $ten_mon,
            'so_tin_chi' => $so_tin_chi,
            'khoa_id' => $khoa_id
        ]);
        $message = "Thêm môn học thành công!";
    }

    // ==== EDIT SUBJECT ====
    if ($action === 'edit') {
        $id = $_POST['id'];
        $ma_mon = $_POST['ma_mon'];
        $ten_mon = $_POST['ten_mon'];
        $so_tin_chi = $_POST['so_tin_chi'];
        $khoa_id = $_POST['khoa_id'];

        $sql = "UPDATE mon_hoc SET ma_mon = :ma_mon, ten_mon = :ten_mon, so_tin_chi = :so_tin_chi, khoa_id = :khoa_id WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'ma_mon' => $ma_mon,
            'ten_mon' => $ten_mon,
            'so_tin_chi' => $so_tin_chi,
            'khoa_id' => $khoa_id,
            'id' => $id
        ]);
        $message = "Cập nhật môn học thành công!";
    }

    // ==== DELETE SUBJECT ====
    if (isset($_GET['action']) && $_GET['action'] === 'delete') {
        $id = $_GET['id'];
        // Check if subject is in use
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM phan_cong WHERE subject_id = :id");
        $stmt_check->execute(['id' => $id]);
        if ($stmt_check->fetchColumn() > 0) {
            $error = "Không thể xóa môn học này vì đã được phân công giảng dạy.";
        } else {
            $sql = "DELETE FROM mon_hoc WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $message = "Xóa môn học thành công!";
            header("Location: subjects.php?message=" . urlencode($message));
            exit();
        }
    }
     if (isset($_GET['message'])) {
        $message = $_GET['message'];
    }

} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        $error = "Lỗi: Mã môn học đã tồn tại.";
    } else {
        $error = "Lỗi CSDL: " . $e->getMessage();
    }
}

// Fetch all subjects with faculty name
$subjects = $pdo->query("
    SELECT mh.id, mh.ma_mon, mh.ten_mon, mh.so_tin_chi, k.ten_khoa, mh.khoa_id
    FROM mon_hoc mh
    JOIN khoa k ON mh.khoa_id = k.id
    ORDER BY mh.ma_mon ASC
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
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                    <i class="bi bi-plus-circle"></i> Thêm Môn học
                </button>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <i class="bi bi-book"></i> Danh sách Môn học
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã Môn</th>
                                    <th>Tên Môn học</th>
                                    <th>Số tín chỉ</th>
                                    <th>Khoa</th>
                                    <th class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($subjects)): ?>
                                    <tr><td colspan="5" class="text-center text-muted">Chưa có môn học nào</td></tr>
                                <?php else: ?>
                                    <?php foreach ($subjects as $subject): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($subject['ma_mon']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($subject['ten_mon']); ?></td>
                                        <td><?php echo htmlspecialchars($subject['so_tin_chi']); ?></td>
                                        <td><?php echo htmlspecialchars($subject['ten_khoa']); ?></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-warning"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editSubjectModal"
                                                    data-entity-json='<?php echo htmlspecialchars(json_encode($subject)); ?>'>
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <a href="subjects.php?action=delete&id=<?php echo $subject['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc muốn xóa môn <?php echo $subject['ten_mon']; ?>?');">
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

            <div class="modal fade" id="addSubjectModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="subjects.php" method="POST">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">Thêm Môn học Mới</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="action" value="add">
                                <div class="mb-3">
                                    <label for="add_ma_mon" class="form-label">Mã Môn</label>
                                    <input type="text" class="form-control" id="add_ma_mon" name="ma_mon" required placeholder="VD: MH001">
                                </div>
                                <div class="mb-3">
                                    <label for="add_ten_mon" class="form-label">Tên Môn học</label>
                                    <input type="text" class="form-control" id="add_ten_mon" name="ten_mon" required placeholder="VD: Lập trình Web">
                                </div>
                                <div class="mb-3">
                                    <label for="add_so_tin_chi" class="form-label">Số tín chỉ</label>
                                    <input type="number" class="form-control" id="add_so_tin_chi" name="so_tin_chi" required min="1" value="3">
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
                                <button type="submit" class="btn btn-primary">Thêm mới</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="editSubjectModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="subjects.php" method="POST">
                            <div class="modal-header bg-warning text-dark">
                                <h5 class="modal-title">Chỉnh sửa Môn học</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" id="edit_id">
                                <div class="mb-3">
                                    <label for="edit_ma_mon" class="form-label">Mã Môn</label>
                                    <input type="text" class="form-control" id="edit_ma_mon" name="ma_mon" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_ten_mon" class="form-label">Tên Môn học</label>
                                    <input type="text" class="form-control" id="edit_ten_mon" name="ten_mon" required>
                                </div>
                                 <div class="mb-3">
                                    <label for="edit_so_tin_chi" class="form-label">Số tín chỉ</label>
                                    <input type="number" class="form-control" id="edit_so_tin_chi" name="so_tin_chi" required min="1">
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
    const editModal = document.getElementById('editSubjectModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', event => {
            // Button that triggered the modal
            const button = event.relatedTarget;
            // Extract info from data-entity-json attributes
            const data = JSON.parse(button.getAttribute('data-entity-json'));
            
            // Update the modal's content.
            const modalBodyInputId = editModal.querySelector('#edit_id');
            const modalBodyInputMa = editModal.querySelector('#edit_ma_mon');
            const modalBodyInputTen = editModal.querySelector('#edit_ten_mon');
            const modalBodyInputTinChi = editModal.querySelector('#edit_so_tin_chi');
            const modalBodySelectKhoa = editModal.querySelector('#edit_khoa_id');

            modalBodyInputId.value = data.id;
            modalBodyInputMa.value = data.ma_mon;
            modalBodyInputTen.value = data.ten_mon;
            modalBodyInputTinChi.value = data.so_tin_chi;
            modalBodySelectKhoa.value = data.khoa_id;
        });
    }
</script>