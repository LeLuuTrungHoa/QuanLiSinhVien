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

<header class="navbar navbar-dark sticky-top bg-primary flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="<?php echo $base_url; ?>index.php">
        <i class="bi bi-mortarboard-fill"></i> StudentMS
    </a>
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="w-100"></div>

    <div class="navbar-nav">
        <div class="nav-item text-nowrap">
            <?php if (isset($_SESSION['user_id'])): ?>
            <ul class="navbar-nav flex-row">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle pe-3" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?php echo $base_url . 'assets/images/avatars/' . (isset($current_user['avatar']) ? htmlspecialchars($current_user['avatar']) : 'default.png'); ?>" alt="Avatar" class="avatar-sm rounded-circle me-1">
                        <?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Tài khoản'; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="<?php echo $base_url; ?>profile.php">
                            <i class="bi bi-person-circle"></i> Thông tin cá nhân
                        </a></li>
                        <li><a class="dropdown-item" href="<?php echo $base_url; ?>change_password.php">
                            <i class="bi bi-key-fill"></i> Đổi mật khẩu
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo $base_url; ?>logout.php">
                            <i class="bi bi-box-arrow-right"></i> Đăng xuất
                        </a></li>
                    </ul>
                </li>
            </ul>
            <?php else: ?>
                <a class="nav-link px-3" href="<?php echo $base_url; ?>login.php">Đăng nhập</a>
            <?php endif; ?>
        </div>
    </div>
</header>
