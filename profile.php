<?php
$page_title = "Thông tin cá nhân";
require_once 'includes/session.php'; // Includes session_start(), db connection, and user validation

$message = '';
$error = '';

$user_id = $_SESSION['user_id'];

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_profile') {
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $avatar_path = $current_user['avatar']; // Keep old avatar by default

    // Handle File Upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $file_name = $_FILES['avatar']['name'];
        $file_tmp = $_FILES['avatar']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed)) {
            if ($_FILES['avatar']['size'] < 5000000) { // 5MB
                // Create a unique filename
                $new_file_name = $_SESSION['username'] . '_' . uniqid() . '.' . $file_ext;
                $upload_path = 'assets/images/avatars/' . $new_file_name;
                
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    // Delete old avatar if it's not the default one
                    if ($avatar_path != 'default.png' && file_exists('assets/images/avatars/' . $avatar_path)) {
                        unlink('assets/images/avatars/' . $avatar_path);
                    }
                    $avatar_path = $new_file_name;
                    $_SESSION['avatar'] = $avatar_path; // Update session
                } else {
                    $error = 'Không thể tải lên ảnh đại diện.';
                }
            } else {
                $error = 'Kích thước file quá lớn (tối đa 5MB).';
            }
        } else {
            $error = 'Định dạng file không được phép (chỉ jpg, jpeg, png, gif).';
        }
    }
    
    if (empty($error)) {
        try {
            // Only update fields that are meant to be editable
            $sql = "UPDATE users SET phone = ?, address = ?, avatar = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$phone, $address, $avatar_path, $user_id]);
            
            $message = "Cập nhật thông tin cá nhân thành công!";
            
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute(['id' => $user_id]);
            $current_user = $stmt->fetch();

        } catch (PDOException $e) {
            $error = "Lỗi CSDL: " . $e->getMessage();
        }
    }
}


// Fetch full user details for display
try {
    $sql = "
        SELECT u.*, l.ten_lop, k.ten_khoa 
        FROM users u 
        LEFT JOIN lop_hoc l ON u.lop_hoc_id = l.id
        LEFT JOIN khoa k ON l.khoa_id = k.id
        WHERE u.id = :id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $user_id]);
    $user_info = $stmt->fetch();
} catch(PDOException $e) {
    die("Không thể lấy thông tin người dùng: " . $e->getMessage());
}

require_once 'includes/header.php'; // Display header after all logic
?>

<?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div class="card">
    <div class="card-header">
        <h4><i class="bi bi-person-badge-fill"></i> Hồ sơ của bạn</h4>
    </div>
    <div class="card-body">
        <form action="profile.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_profile">
            <div class="row">
                <div class="col-md-4 text-center">
                    <img id="avatar-preview" src="<?php echo $base_url . 'assets/images/avatars/' . (isset($current_user['avatar']) ? htmlspecialchars($current_user['avatar']) : 'default.png'); ?>" 
                        alt="Avatar" 
                        class="rounded-circle me-1"
                        style="width: 100px; height: 100px; object-fit: cover; min-width: 40px;">
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <div class="mb-3">
                        <label for="avatar" class="form-label">Đổi ảnh đại diện</label>
                        <input class="form-control form-control-sm" type="file" id="avatar" name="avatar" onchange="previewAvatar(event)">
                    </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mã / Username</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user_info['username']); ?>" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($user_info['email']); ?>" readonly>
                        </div>
                         <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">Họ và tên</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user_info['full_name']); ?>" required readonly>
                        </div>
                         <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user_info['phone']); ?>">
                        </div>
                         <div class="col-md-6 mb-3">
                            <label for="birthday" class="form-label">Ngày sinh</label>
                            <input type="date" class="form-control" id="birthday" name="birthday" value="<?php echo htmlspecialchars($user_info['birthday']); ?>" readonly>
                        </div>
                         <div class="col-md-6 mb-3">
                            <label class="form-label">Giới tính</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="gender_nam" value="Nam" <?php echo ($user_info['gender'] == 'Nam') ? 'checked' : ''; ?> disabled>
                                    <label class="form-check-label" for="gender_nam">Nam</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="gender_nu" value="Nữ" <?php echo ($user_info['gender'] == 'Nữ') ? 'checked' : ''; ?> disabled>
                                    <label class="form-check-label" for="gender_nu">Nữ</label>
                                </div>
                            </div>
                        </div>
                        <?php if ($user_info['role'] == 'student'): ?>
                         <div class="col-md-6 mb-3">
                            <label class="form-label">Lớp</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user_info['ten_lop'] ?? 'Chưa xếp lớp'); ?>" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Khoa</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user_info['ten_khoa'] ?? 'N/A'); ?>" readonly>
                        </div>
                        <?php endif; ?>
                         <div class="col-12 mb-3">
                            <label for="address" class="form-label">Địa chỉ</label>
                            <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($user_info['address']); ?></textarea>
                        </div>
                    </div>
                     <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i> Cập nhật thông tin</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function previewAvatar(event) {
    const reader = new FileReader();
    reader.onload = function(){
        const output = document.getElementById('avatar-preview');
        output.src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}

document.addEventListener('DOMContentLoaded', function() {
    // The preview logic is now inline in the onchange attribute
    // You can keep this block if you have other JS initializations
});
</script>

<?php
require_once 'includes/footer.php';
?>
