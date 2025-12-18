<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/config/db.php';
// 1. Định nghĩa BASE_URL ngay từ đầu để sử dụng thống nhất
define('BASE_URL', '/QuanLiSinhVien/');

require_once dirname(__DIR__) . '/config/db.php';

// 2. Sử dụng đường dẫn tuyệt đối từ BASE_URL
if (!isset($_SESSION["user_id"])) {
    header("Location: " . BASE_URL . "login.php");
    exit;
}

// Get the current directory name (e.g., 'admin', 'lecturer', 'student')
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$role_dirs = ['admin', 'lecturer', 'student'];

// Role-based access control: Only apply strict checks for role-specific directories
if (in_array($current_dir, $role_dirs)) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] != $current_dir) {
        // If the user's role doesn't match the directory, deny access.
        // A special exception could be made for admin to access all, but for now, it's strict.
        die('<div style="text-align: center; margin-top: 50px;"><h1>Access Denied</h1><p>You do not have permission to view this page.</p><a href="../index.php">Go back to your dashboard</a></div>');
    }
}

// Fetch user details for the header
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$current_user = $stmt->fetch();
?>
