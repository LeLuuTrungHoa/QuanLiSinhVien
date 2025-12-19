<?php
$page_title = "Quản lý Người Dùng";
require_once '../includes/session.php';
require_once '../includes/header.php';

// 1. XÁC ĐỊNH VAI TRÒ
$role_filter = isset($_GET['role']) && in_array($_GET['role'], ['student', 'lecturer']) ? $_GET['role'] : 'student';
$page_title = ($role_filter == 'student') ? 'Quản lý Sinh viên' : 'Quản lý Giảng viên';
$search_term = $_GET['search'] ?? '';

// 2. XỬ LÝ LOGIC
$action = $_POST['action'] ?? '';
$message = '';
$error = '';

try {
    // ==== ADD NEW USER ====
    if ($action === 'add') {
        $full_name = $_POST['full_name'];
        $role = $_POST['role'];
        $lop_hoc_id = !empty($_POST['lop_hoc_id']) ? $_POST['lop_hoc_id'] : null;
        
        $prefix = ($role == 'student') ? 'SV' : 'GV';
        $email_domain = ($role == 'student') ? '@student.edu.vn' : '@lecturer.edu.vn';
        
        // Tạo mã tự động
        $stmt = $pdo->prepare("SELECT username FROM users WHERE username LIKE :prefix ORDER BY LENGTH(username) DESC, username DESC LIMIT 1");
        $stmt->execute(['prefix' => $prefix . '%']);
        $last_user = $stmt->fetch();
        
        if ($last_user) {
            $last_num = (int)preg_replace("/[^0-9]/", "", $last_user['username']);
            $new_num = $last_num + 1;
        } else {
            $new_num = 1;
        }
        
        $username = $prefix . str_pad($new_num, 3, '0', STR_PAD_LEFT); 
        $email = $username . $email_domain;
        $password = password_hash('123456', PASSWORD_BCRYPT);
        
        // [QUAN TRỌNG] Không cần lưu khoa_id nữa
        $sql = "INSERT INTO users (username, password, role, email, full_name, lop_hoc_id) VALUES (:username, :password, :role, :email, :full_name, :lop_hoc_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'username' => $username,
            'password' => $password,
            'role' => $role,
            'email' => $email,
            'full_name' => $full_name,
            'lop_hoc_id' => $lop_hoc_id
        ]);
        $message = "Thêm thành công! Mã: " . htmlspecialchars($username);
    }

    // ==== EDIT USER ====
    if ($action === 'edit') {
        $user_id = $_POST['id'];
        $full_name = $_POST['full_name'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $gender = $_POST['gender'];
        $birthday = $_POST['birthday'];
        $lop_hoc_id = !empty($_POST['lop_hoc_id']) ? $_POST['lop_hoc_id'] : null;

        // [QUAN TRỌNG] Không cần update khoa_id
        $sql = "UPDATE users SET full_name = :full_name, phone = :phone, address = :address, gender = :gender, birthday = :birthday, lop_hoc_id = :lop_hoc_id";
        $params = [
            'full_name' => $full_name, 'phone' => $phone, 'address' => $address, 
            'gender' => $gender, 'birthday' => $birthday, 'lop_hoc_id' => $lop_hoc_id, 
            'id' => $user_id
        ];

        // Upload Avatar
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $stmt_old = $pdo->prepare("SELECT avatar, username FROM users WHERE id = :id");
            $stmt_old->execute(['id' => $user_id]);
            $user_data = $stmt_old->fetch();
            $old_avatar = $user_data['avatar'];

            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $file_name = $_FILES['avatar']['name'];
            $file_tmp = $_FILES['avatar']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (in_array($file_ext, $allowed) && $_FILES['avatar']['size'] < 5000000) {
                $new_file_name = $user_data['username'] . '_' . time() . '.' . $file_ext;
                $upload_path = '../uploads/avatars/' . $new_file_name;
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $sql .= ", avatar = :avatar";
                    $params['avatar'] = $new_file_name;
                    if ($old_avatar && $old_avatar != 'default.png' && file_exists('../uploads/avatars/' . $old_avatar)) {
                        unlink('../uploads/avatars/' . $old_avatar);
                    }
                }
            } else { $error = 'Lỗi file ảnh.'; }
        }

        if (empty($error)) {
            $sql .= " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $message = "Cập nhật thành công!";
        }
    }

    // ==== DELETE & RESET PASS (GIỮ NGUYÊN) ====
    if ($action === 'delete') {
        $user_id = $_POST['user_id'];
        $stmt_del = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
        $stmt_del->execute([$user_id]);
        $del_data = $stmt_del->fetch();
        if ($del_data && $del_data['avatar'] != 'default.png' && file_exists('../uploads/avatars/' . $del_data['avatar'])) {
            unlink('../uploads/avatars/' . $del_data['avatar']);
        }
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $user_id]);
        $message = "Xóa thành công!";
    }

    if ($action === 'reset_password') {
        $user_id = $_POST['user_id'];
        $password = password_hash('123456', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->execute(['password' => $password, 'id' => $user_id]);
        $message = "Reset mật khẩu thành công!";
    }

} catch (PDOException $e) {
    $error = ($e->getCode() == 23000) ? "Lỗi: Mã người dùng đã tồn tại!" : "Lỗi hệ thống: " . $e->getMessage();
}

// 3. TÌM KIẾM VÀ LẤY DANH SÁCH
// [QUAN TRỌNG] Join bắc cầu: Users -> Lop_hoc -> Khoa
$sql_users = "SELECT u.*, l.ten_lop, k.ten_khoa 
              FROM users u 
              LEFT JOIN lop_hoc l ON u.lop_hoc_id = l.id 
              LEFT JOIN khoa k ON l.khoa_id = k.id  -- Join qua bảng Lớp
              WHERE u.role = :role";
$params = ['role' => $role_filter];

if (!empty($search_term)) {
    $sql_users .= " AND (u.username LIKE :search_user OR u.full_name LIKE :search_name)";
    $params['search_user'] = '%' . $search_term . '%';
    $params['search_name'] = '%' . $search_term . '%';
}

$sql_users .= " ORDER BY LENGTH(u.username) DESC, u.username DESC"; 

$stmt = $pdo->prepare($sql_users);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Lấy danh sách lớp
$classes = $pdo->query("SELECT id, ten_lop FROM lop_hoc ORDER BY ten_lop")->fetchAll();
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $page_title; ?></h1>
                <div class="btn-group me-2">
                    <a href="?role=student" class="btn <?php echo $role_filter == 'student' ? 'btn-primary' : 'btn-outline-primary'; ?>">Sinh viên</a>
                    <a href="?role=lecturer" class="btn <?php echo $role_filter == 'lecturer' ? 'btn-primary' : 'btn-outline-primary'; ?>">Giảng viên</a>
                </div>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show"><?php echo $message; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <div class="row mb-3 g-2">
                <div class="col-md-6">
                    <form action="users.php" method="GET" class="d-flex">
                        <input type="hidden" name="role" value="<?php echo $role_filter; ?>">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Tìm theo Mã hoặc Tên..." value="<?php echo htmlspecialchars($search_term); ?>">
                            <button type="submit" class="btn btn-primary">Tìm</button>
                        </div>
                    </form>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="export_users.php?role=<?php echo $role_filter; ?>&search=<?php echo urlencode($search_term); ?>" class="btn btn-success me-1">
                        <i class="bi bi-file-earmark-excel-fill"></i> Xuất Excel
                    </a>
                    <?php if ($role_filter == 'student'): ?>
                        <a href="download_full_sample.php" class="btn btn-outline-success me-1"><i class="bi bi-download"></i> Tải Mẫu</a>
                        <a href="import_users.php" class="btn btn-success me-1"><i class="bi bi-file-earmark-spreadsheet"></i> Import</a>
                    <?php endif; ?>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-plus-circle"></i> Thêm Mới
                    </button>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">Ảnh</th>
                                    <th>Mã</th>
                                    <th>Họ tên</th>
                                    <th>Email</th>
                                    <th>Khoa</th> <?php if ($role_filter == 'student'): ?>
                                    <th>Lớp</th>
                                    <?php endif; ?>
                                    <th class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr><td colspan="7" class="text-center py-4 text-muted">Không có dữ liệu</td></tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td class="text-center">
                                            <img src="../uploads/avatars/<?php echo !empty($user['avatar']) ? $user['avatar'] : 'default.png'; ?>" class="rounded-circle border" width="35" height="35" style="object-fit: cover;">
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><small><?php echo htmlspecialchars($user['email']); ?></small></td>
                                        <td><?php echo htmlspecialchars($user['ten_khoa'] ?? '-'); ?></td>
                                        <?php if ($role_filter == 'student'): ?>
                                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($user['ten_lop'] ?? '-'); ?></span></td>
                                        <?php endif; ?>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editUserModal"
                                                    data-entity-json='<?php echo htmlspecialchars(json_encode($user)); ?>'>
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <form action="" method="POST" class="d-inline" onsubmit="return confirm('Reset mật khẩu?');">
                                                <input type="hidden" name="action" value="reset_password">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-info"><i class="bi bi-key"></i></button>
                                            </form>
                                            <form action="" method="POST" class="d-inline" onsubmit="return confirm('Xóa người dùng này?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="users.php?role=<?php echo $role_filter; ?>" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Thêm <?php echo $role_filter == 'student' ? 'Sinh viên' : 'Giảng viên'; ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="role" value="<?php echo $role_filter; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>
                    
                    <?php if ($role_filter == 'student'): ?>
                    <div class="mb-3">
                        <label class="form-label">Lớp học</label>
                        <select class="form-select" name="lop_hoc_id">
                            <option value="">-- Chọn lớp --</option>
                            <?php foreach ($classes as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['ten_lop']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-light border small">
                        Tự động tạo: Mã số, Email, Mật khẩu (123456).
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu lại</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="users.php?role=<?php echo $role_filter; ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Cập nhật thông tin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Mã (Read-only)</label>
                                    <input type="text" class="form-control bg-light" id="edit_username" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control bg-light" id="edit_email" readonly>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Họ tên</label>
                                    <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
                                </div>

                                <?php if ($role_filter == 'student'): ?>
                                <div class="col-md-6">
                                    <label class="form-label">Lớp</label>
                                    <select class="form-select" id="edit_lop_hoc_id" name="lop_hoc_id">
                                        <option value="">-- Chọn lớp --</option>
                                        <?php foreach ($classes as $c): ?>
                                        <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['ten_lop']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>

                                <div class="col-md-6">
                                    <label class="form-label">SĐT</label>
                                    <input type="tel" class="form-control" id="edit_phone" name="phone">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Giới tính</label>
                                    <select class="form-select" id="edit_gender" name="gender">
                                        <option value="">-- Chọn --</option>
                                        <option value="Nam">Nam</option>
                                        <option value="Nữ">Nữ</option>
                                        <option value="Khác">Khác</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Ngày sinh</label>
                                    <input type="date" class="form-control" id="edit_birthday" name="birthday">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Địa chỉ</label>
                                    <textarea class="form-control" id="edit_address" name="address" rows="1"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center border-start">
                            <label class="form-label fw-bold">Ảnh đại diện</label>
                            <div class="mb-2">
                                <img src="" id="edit_avatar_preview" class="img-thumbnail rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                            </div>
                            <input class="form-control form-control-sm" type="file" id="edit_avatar" name="avatar" onchange="previewImage(this)">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) { document.getElementById('edit_avatar_preview').src = e.target.result; }
            reader.readAsDataURL(input.files[0]);
        }
    }

    const editModal = document.getElementById('editUserModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const data = JSON.parse(button.getAttribute('data-entity-json'));

            editModal.querySelector('#edit_id').value = data.id;
            editModal.querySelector('#edit_username').value = data.username;
            editModal.querySelector('#edit_email').value = data.email;
            editModal.querySelector('#edit_full_name').value = data.full_name;
            editModal.querySelector('#edit_phone').value = data.phone || '';
            editModal.querySelector('#edit_gender').value = data.gender || '';
            editModal.querySelector('#edit_birthday').value = data.birthday || '';
            editModal.querySelector('#edit_address').value = data.address || '';
            
            const lopSelect = editModal.querySelector('#edit_lop_hoc_id');
            if(lopSelect) lopSelect.value = data.lop_hoc_id || '';

            const avatarImg = editModal.querySelector('#edit_avatar_preview');
            avatarImg.src = '../uploads/avatars/' + (data.avatar || 'default.png');
            editModal.querySelector('#edit_avatar').value = '';
        });
    }
</script>