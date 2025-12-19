<?php
$page_title = "Quản lý Người Dùng";
require_once '../includes/session.php';
require_once '../includes/header.php';

// 1. XÁC ĐỊNH VAI TRÒ (SINH VIÊN HAY GIẢNG VIÊN)
$role_filter = isset($_GET['role']) && in_array($_GET['role'], ['student', 'lecturer']) ? $_GET['role'] : 'student';
$page_title = ($role_filter == 'student') ? 'Quản lý Sinh viên' : 'Quản lý Giảng viên';

// 2. XỬ LÝ LOGIC (THÊM / SỬA / XÓA / RESET PASS)
$action = $_POST['action'] ?? '';
$message = '';
$error = '';

try {
    // ==== ADD NEW USER ====
        if ($action === 'add') {
            $full_name = $_POST['full_name'];
            $role = $_POST['role'];
            $lop_hoc_id = !empty($_POST['lop_hoc_id']) ? $_POST['lop_hoc_id'] : null;
            
            // Tự động tạo Mã và Email
            $prefix = ($role == 'student') ? 'SV' : 'GV';
            $email_domain = ($role == 'student') ? '@student.edu.vn' : '@lecturer.edu.vn';
            
            // --- SỬA LỖI TẠI ĐÂY ---
            // Thêm ORDER BY LENGTH(username) DESC để lấy đúng mã số lớn nhất (SV10000 thay vì SV9999)
            $stmt = $pdo->prepare("SELECT username FROM users WHERE username LIKE :prefix ORDER BY LENGTH(username) DESC, username DESC LIMIT 1");
            $stmt->execute(['prefix' => $prefix . '%']);
            $last_user = $stmt->fetch();
            
            if ($last_user) {
                // Lấy số đuôi và tăng lên 1
                $last_num = (int)preg_replace("/[^0-9]/", "", $last_user['username']);
                $new_num = $last_num + 1;
            } else {
                $new_num = 1;
            }
            
            // Tạo mã mới. str_pad sẽ tự động mở rộng nếu số vượt quá 4 chữ số (ví dụ 10001 vẫn giữ nguyên)
            $username = $prefix . str_pad($new_num, 3, '0', STR_PAD_LEFT); 
            $email = $username . $email_domain;
            
            // Mật khẩu mặc định
            $password = password_hash('123456', PASSWORD_BCRYPT);
            
            $sql = "INSERT INTO users (username, password, role, email, full_name, lop_hoc_id) VALUES (:username, :password, :role, :email, :full_name, :lop_hoc_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'username' => $username,
                'password' => $password,
                'role' => $role,
                'email' => $email,
                'full_name' => $full_name,
                'lop_hoc_id' => $lop_hoc_id,
            ]);
            $message = "Thêm người dùng " . htmlspecialchars($username) . " thành công!";
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

        $sql = "UPDATE users SET full_name = :full_name, phone = :phone, address = :address, gender = :gender, birthday = :birthday, lop_hoc_id = :lop_hoc_id";
        $params = [
            'full_name' => $full_name,
            'phone' => $phone,
            'address' => $address,
            'gender' => $gender,
            'birthday' => $birthday,
            'lop_hoc_id' => $lop_hoc_id,
            'id' => $user_id
        ];

        // Xử lý Upload Avatar
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            // Lấy tên file cũ để xóa
            $stmt_old = $pdo->prepare("SELECT avatar, username FROM users WHERE id = :id");
            $stmt_old->execute(['id' => $user_id]);
            $user_data = $stmt_old->fetch();
            $old_avatar = $user_data['avatar'];

            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $file_name = $_FILES['avatar']['name'];
            $file_tmp = $_FILES['avatar']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (in_array($file_ext, $allowed)) {
                if ($_FILES['avatar']['size'] < 5000000) { // < 5MB
                    $new_file_name = $user_data['username'] . '_' . time() . '.' . $file_ext;
                    $upload_path = '../uploads/avatars/' . $new_file_name;
                    
                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        $sql .= ", avatar = :avatar";
                        $params['avatar'] = $new_file_name;

                        // Xóa ảnh cũ nếu không phải default
                        if ($old_avatar && $old_avatar != 'default.png' && file_exists('../uploads/avatars/' . $old_avatar)) {
                            unlink('../uploads/avatars/' . $old_avatar);
                        }
                    } else {
                        $error = 'Lỗi: Không thể lưu file ảnh.';
                    }
                } else {
                    $error = 'Lỗi: Ảnh quá lớn (Max 5MB).';
                }
            } else {
                $error = 'Lỗi: Định dạng ảnh không hợp lệ.';
            }
        }

        if (empty($error)) {
            $sql .= " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $message = "Cập nhật thông tin thành công!";
        }
    }

    // ==== DELETE USER ====
    if ($action === 'delete') {
        $user_id = $_POST['user_id'];
        // Xóa avatar cũ trước
        $stmt_del = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
        $stmt_del->execute([$user_id]);
        $del_data = $stmt_del->fetch();
        if ($del_data && $del_data['avatar'] != 'default.png' && file_exists('../uploads/avatars/' . $del_data['avatar'])) {
            unlink('../uploads/avatars/' . $del_data['avatar']);
        }

        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $user_id]);
        $message = "Xóa người dùng thành công!";
    }

    // ==== RESET PASSWORD ====
    if ($action === 'reset_password') {
        $user_id = $_POST['user_id'];
        $password = password_hash('123456', PASSWORD_BCRYPT);
        $sql = "UPDATE users SET password = :password WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['password' => $password, 'id' => $user_id]);
        $message = "Reset mật khẩu về '123456' thành công!";
    }

} catch (PDOException $e) {
    $error = "Lỗi CSDL: " . $e->getMessage();
}

// 3. TÌM KIẾM VÀ LẤY DANH SÁCH
$search_term = $_GET['search'] ?? '';
$sql_users = "SELECT u.*, l.ten_lop FROM users u LEFT JOIN lop_hoc l ON u.lop_hoc_id = l.id WHERE u.role = :role";
$params = ['role' => $role_filter];

if (!empty($search_term)) {
    // search thành 2 biến riêng biệt :search_user và :search_name
    $sql_users .= " AND (u.username LIKE :search_user OR u.full_name LIKE :search_name)";
    
    // Gán giá trị cho cả 2 biến này (giá trị giống nhau)
    $params['search_user'] = '%' . $search_term . '%';
    $params['search_name'] = '%' . $search_term . '%';
    }


$sql_users .= " ORDER BY u.username DESC"; 

$stmt = $pdo->prepare($sql_users);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Lấy danh sách lớp cho Dropdown
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
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="row mb-3 g-2">
                <div class="col-md-6">
                    <form action="users.php" method="GET" class="d-flex">
                        <input type="hidden" name="role" value="<?php echo $role_filter; ?>">
                        <input type="text" name="search" class="form-control me-2" placeholder="Tìm theo Mã hoặc Tên..." value="<?php echo htmlspecialchars($search_term); ?>">
                        <button type="submit" class="btn btn-outline-secondary">Tìm</button>
                    </form>
                </div>
                <div class="col-md-6 text-md-end">
                    <?php if ($role_filter == 'student'): ?>
                        <a href="download_full_sample.php" class="btn btn-outline-success me-1"><i class="bi bi-download"></i> Tải Mẫu</a>
                        <a href="import_users.php" class="btn btn-success me-1"><i class="bi bi-file-earmark-spreadsheet"></i> Import Excel</a>
                    <?php endif; ?>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-plus-circle"></i> Thêm <?php echo $role_filter == 'student' ? 'Sinh viên' : 'Giảng viên'; ?>
                    </button>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" width="50">Avt</th> 
                                    <th>Mã</th>
                                    <th>Họ và tên</th>
                                    <th>Email</th>
                                    <th>SĐT</th>
                                    <?php if ($role_filter == 'student'): ?>
                                    <th>Lớp</th>
                                    <?php endif; ?>
                                    <th class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr><td colspan="7" class="text-center py-4 text-muted">Không tìm thấy dữ liệu</td></tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td class="text-center">
                                            <?php 
                                            $avatarPath = '../uploads/avatars/' . ($user['avatar'] ?? 'default.png');
                                            if (!file_exists($avatarPath)) $avatarPath = '../uploads/avatars/default.png';
                                            ?>
                                            <img src="<?php echo $avatarPath; ?>" class="rounded-circle" width="32" height="32" style="object-fit: cover;">
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['phone'] ?? ''); ?></td>
                                        <?php if ($role_filter == 'student'): ?>
                                        <td><?php echo htmlspecialchars($user['ten_lop'] ?? 'Chưa có'); ?></td>
                                        <?php endif; ?>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-warning" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editUserModal"
                                                    data-entity-json='<?php echo htmlspecialchars(json_encode($user)); ?>'>
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            
                                            <form action="" method="POST" class="d-inline" onsubmit="return confirm('Reset mật khẩu về `123456`?');">
                                                <input type="hidden" name="action" value="reset_password">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-info" title="Reset Mật khẩu"><i class="bi bi-key-fill"></i></button>
                                            </form>

                                            <form action="" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash-fill"></i></button>
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

            <div class="modal fade" id="addUserModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="users.php?role=<?php echo $role_filter; ?>" method="POST">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">Thêm <?php echo $role_filter == 'student' ? 'Sinh viên' : 'Giảng viên'; ?> Mới</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="role" value="<?php echo $role_filter; ?>">
                                <div class="mb-3">
                                    <label for="add_full_name" class="form-label">Họ và tên</label>
                                    <input type="text" class="form-control" id="add_full_name" name="full_name" required>
                                </div>
                                <?php if ($role_filter == 'student'): ?>
                                <div class="mb-3">
                                    <label for="add_lop_hoc_id" class="form-label">Lớp</label>
                                    <select class="form-select" id="add_lop_hoc_id" name="lop_hoc_id">
                                        <option value="">-- Chọn lớp --</option>
                                        <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['ten_lop']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                                <div class="alert alert-light border">
                                    <small><i class="bi bi-info-circle"></i> Mã người dùng, Email sẽ được tạo tự động.<br>
                                    <i class="bi bi-key"></i> Mật khẩu mặc định là <strong>123456</strong>.</small>
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

            <div class="modal fade" id="editUserModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form action="users.php?role=<?php echo $role_filter; ?>" method="POST" enctype="multipart/form-data">
                            <div class="modal-header bg-warning text-dark">
                                <h5 class="modal-title">Chỉnh sửa thông tin</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" id="edit_id">
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Mã (Không sửa)</label>
                                                <input type="text" class="form-control bg-light" id="edit_username" readonly>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control bg-light" id="edit_email" readonly>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label for="edit_full_name" class="form-label">Họ và tên</label>
                                                <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_phone" class="form-label">Số điện thoại</label>
                                                <input type="tel" class="form-control" id="edit_phone" name="phone">
                                            </div>
                                            <?php if ($role_filter == 'student'): ?>
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_lop_hoc_id" class="form-label">Lớp</label>
                                                <select class="form-select" id="edit_lop_hoc_id" name="lop_hoc_id">
                                                    <option value="">-- Chọn lớp --</option>
                                                    <?php foreach ($classes as $class): ?>
                                                    <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['ten_lop']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <?php endif; ?>
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_gender" class="form-label">Giới tính</label>
                                                <select class="form-select" id="edit_gender" name="gender">
                                                    <option value="">-- Chọn --</option>
                                                    <option value="Nam">Nam</option>
                                                    <option value="Nữ">Nữ</option>
                                                    <option value="Khác">Khác</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_birthday" class="form-label">Ngày sinh</label>
                                                <input type="date" class="form-control" id="edit_birthday" name="birthday">
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label for="edit_address" class="form-label">Địa chỉ</label>
                                                <textarea class="form-control" id="edit_address" name="address" rows="2"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4 text-center border-start">
                                        <label class="form-label fw-bold">Ảnh đại diện</label>
                                        <div class="mb-3">
                                            <img src="../uploads/avatars/default.png" id="edit_avatar_preview" class="img-thumbnail rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                                        </div>
                                        <input class="form-control form-control-sm" type="file" id="edit_avatar" name="avatar" onchange="previewImage(this)">
                                        <small class="text-muted d-block mt-2">Cho phép: .jpg, .png, .jpeg (Max 5MB)</small>
                                    </div>
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

<?php require_once '../includes/footer.php'; ?>

<script>
    // Hàm preview ảnh khi chọn file
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('edit_avatar_preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Xử lý đổ dữ liệu vào Modal Edit
    const editModal = document.getElementById('editUserModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const data = JSON.parse(button.getAttribute('data-entity-json'));

            // Đổ dữ liệu text/select
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

            // Xử lý hiển thị ảnh cũ
            const avatarImg = editModal.querySelector('#edit_avatar_preview');
            if (data.avatar) {
                avatarImg.src = '../uploads/avatars/' + data.avatar;
            } else {
                avatarImg.src = '../uploads/avatars/default.png';
            }
            
            // Reset input file (tránh trường hợp người dùng chọn file rồi đóng modal mở lại vẫn còn file)
            editModal.querySelector('#edit_avatar').value = '';
        });
    }
</script>