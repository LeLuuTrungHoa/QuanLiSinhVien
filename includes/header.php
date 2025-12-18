<?php
// It's recommended to have a single session_start() at the beginning of your application's entry point (e.g., index.php)
// and include a session management file like this.
// include_once 'includes/session.php';

$base_url = '/QuanLiSinhVien/';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Hệ thống Quản lý Sinh viên'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/style.css">
</head>
<body>

<header class="navbar navbar-dark sticky-top bg-primary p-0 shadow-sm py-2">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="<?php echo $base_url; ?>index.php">
        <i class="bi bi-mortarboard-fill"></i> StudentMS
    </a>

    <ul class="navbar-nav flex-row ms-auto me-3">
        <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle text-white d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?php echo $base_url . 'assets/images/avatars/' . (isset($current_user['avatar']) ? htmlspecialchars($current_user['avatar']) : 'default.png'); ?>" 
                         alt="Avatar" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                    <span class="d-none d-sm-inline"><?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Tài khoản'; ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" aria-labelledby="navbarDropdown" style="position: absolute;">
                    <li><a class="dropdown-item py-2" href="<?php echo $base_url; ?>profile.php"><i class="bi bi-person-circle me-2"></i>Thông tin cá nhân</a></li>
                    <li><a class="dropdown-item py-2" href="<?php echo $base_url; ?>change_password.php"><i class="bi bi-key-fill me-2"></i>Đổi mật khẩu</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item py-2 text-danger" href="<?php echo $base_url; ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a></li>
                </ul>
            </li>
        <?php else: ?>
            <li class="nav-item"><a class="nav-link px-3 text-white" href="<?php echo $base_url; ?>login.php">Đăng nhập</a></li>
        <?php endif; ?>
    </ul>

    <button class="navbar-toggler d-md-none collapsed border-0" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false">
        <span class="navbar-toggler-icon"></span>
    </button>
</header>
