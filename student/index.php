<?php
include_once dirname(__DIR__) . '/includes/session.php';
$page_title = 'Trang Sinh Viên';
include_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once dirname(__DIR__) . '/includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $page_title; ?></h1>
            </div>

            <h2>Chào mừng Sinh viên</h2>
            <p>Đây là trang dành cho sinh viên. Bạn có thể xem bảng điểm và thông tin cá nhân tại đây.</p>
            
        </main>
    </div>
</div>

<?php include_once dirname(__DIR__) . '/includes/footer.php'; ?>