<?php
$page_title = "Đổi mật khẩu";
require_once 'includes/session.php'; // Includes session_start(), db connection, and user validation

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Vui lòng điền đầy đủ các trường.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Mật khẩu mới và xác nhận mật khẩu không khớp.";
    } elseif (strlen($new_password) < 6) {
        $error = "Mật khẩu mới phải có ít nhất 6 ký tự.";
    } else {
        try {
            $user_id = $_SESSION['user_id'];

            // 1. Get current hashed password from DB
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->execute(['id' => $user_id]);
            $user = $stmt->fetch();

            // 2. Verify current password
            if ($user && password_verify($current_password, $user['password'])) {
                
                // 3. Hash the new password
                $new_password_hashed = password_hash($new_password, PASSWORD_BCRYPT);

                // 4. Update the password in the database
                $update_stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
                $update_stmt->execute(['password' => $new_password_hashed, 'id' => $user_id]);

                $message = "Đổi mật khẩu thành công!";
            } else {
                $error = "Mật khẩu hiện tại không đúng.";
            }
        } catch (PDOException $e) {
            $error = "Lỗi CSDL: " . $e->getMessage();
        }
    }
}

require_once 'includes/header.php'; // Display header after all logic
?>

<?php if ($message): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card">
            <div class="card-header">
                <h4><i class="bi bi-key-fill"></i> Thay đổi mật khẩu</h4>
            </div>
            <div class="card-body">
                <form action="change_password.php" method="POST">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Mật khẩu mới</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Xác nhận mật khẩu mới</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save-fill"></i> Lưu thay đổi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<?php
require_once 'includes/footer.php';
?>
