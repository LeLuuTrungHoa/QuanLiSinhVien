<?php
// 1. Khởi tạo và Logic PHP (Lấy số liệu)
session_start(); // Đảm bảo session được start nếu chưa có trong included files
require_once '../includes/session.php';
$page_title = "Admin Dashboard";
require_once '../includes/header.php';

// Kết nối database và lấy số liệu
try {
    // Tổng Sinh viên
    $stmt_students = $pdo->query("SELECT COUNT(id) AS total FROM users WHERE role = 'student'");
    $total_students = $stmt_students->fetchColumn();

    // Tổng Giảng viên
    $stmt_lecturers = $pdo->query("SELECT COUNT(id) AS total FROM users WHERE role = 'lecturer'");
    $total_lecturers = $stmt_lecturers->fetchColumn();

    // Tổng Khoa
    $stmt_faculties = $pdo->query("SELECT COUNT(id) AS total FROM khoa");
    $total_faculties = $stmt_faculties->fetchColumn();

    // Tổng Môn học
    $stmt_subjects = $pdo->query("SELECT COUNT(id) AS total FROM mon_hoc");
    $total_subjects = $stmt_subjects->fetchColumn();
} catch (PDOException $e) {
    // Xử lý lỗi nhẹ nhàng hơn die() để không vỡ giao diện
    $error_message = "Could not fetch statistics: " . $e->getMessage();
}
?>

<div class="container-fluid">
    <div class="row">
        
        <?php include_once '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $page_title; ?></h1>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                <div class="col">
                    <div class="card h-100 text-white bg-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Tổng Sinh viên</h5>
                                    <h1 class="card-text"><?php echo $total_students ?? 0; ?></h1>
                                </div>
                                <i class="bi bi-people-fill" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                        <div class="card-footer text-center">
                            <a href="users.php?role=student" class="text-white text-decoration-none">Xem chi tiết <i class="bi bi-arrow-right-circle-fill"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card h-100 text-white bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Tổng Giảng viên</h5>
                                    <h1 class="card-text"><?php echo $total_lecturers ?? 0; ?></h1>
                                </div>
                                <i class="bi bi-person-workspace" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                        <div class="card-footer text-center">
                            <a href="users.php?role=lecturer" class="text-white text-decoration-none">Xem chi tiết <i class="bi bi-arrow-right-circle-fill"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card h-100 text-white bg-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Tổng Khoa</h5>
                                    <h1 class="card-text"><?php echo $total_faculties ?? 0; ?></h1>
                                </div>
                                <i class="bi bi-building-fill" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                        <div class="card-footer text-center">
                            <a href="falculties.php" class="text-white text-decoration-none">Xem chi tiết <i class="bi bi-arrow-right-circle-fill"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card h-100 text-white bg-danger">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Tổng Môn học</h5>
                                    <h1 class="card-text"><?php echo $total_subjects ?? 0; ?></h1>
                                </div>
                                <i class="bi bi-book-fill" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                        <div class="card-footer text-center">
                            <a href="subjects.php" class="text-white text-decoration-none">Xem chi tiết <i class="bi bi-arrow-right-circle-fill"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <h4>Chào mừng, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?>!</h4>
                <p>Sử dụng menu điều hướng bên trái để bắt đầu quản lý hệ thống.</p>
            </div>

        </main> </div> </div> <?php
require_once '../includes/footer.php';
?>