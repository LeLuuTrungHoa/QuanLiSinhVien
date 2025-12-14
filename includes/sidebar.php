<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$base_url = '/QuanLiSinhVien/';
?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="<?php echo $base_url; ?>index.php">
                    <i class="bi bi-house-door"></i>
                    Trang chủ
                </a>
            </li>
            <?php if (isset($_SESSION['role'])): ?>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>Quản trị</span>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>admin/index.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>admin/users.php">
                                <i class="bi bi-people"></i> Quản lý Users
                            </a>
                        </li>
                         <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>admin/falculties.php">
                                <i class="bi bi-building"></i> Quản lý Khoa
                            </a>
                        </li>
                         <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>admin/classes.php">
                                <i class="bi bi-person-video3"></i> Quản lý Lớp
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>admin/subjects.php">
                                <i class="bi bi-book"></i> Quản lý Môn học
                            </a>
                        </li>
                         <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>admin/assignments.php">
                                <i class="bi bi-journal-bookmark"></i> Phân công
                            </a>
                        </li>
                    </ul>
                <?php elseif ($_SESSION['role'] == 'lecturer'): ?>
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>Giảng viên</span>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>lecturer/index.php">
                                 <i class="bi bi-easel"></i> Lớp được phân công
                            </a>
                        </li>
                    </ul>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>lecturer/index.php">
                                 <i class="bi bi-easel"></i> Nhập điểm
                            </a>
                        </li>
                    </ul>
                <?php elseif ($_SESSION['role'] == 'student'): ?>
                     <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>Sinh viên</span>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>student/index.php">
                                <i class="bi bi-table"></i> Bảng điểm
                            </a>
                        </li>
                    </ul>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>student/index.php">
                                <i class="bi bi-table"></i> Xem lịch học
                            </a>
                        </li>
                    </ul>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>student/index.php">
                                <i class="bi bi-table"></i> Đăng ký môn học
                            </a>
                        </li>
                    </ul>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
    </div>
</nav>