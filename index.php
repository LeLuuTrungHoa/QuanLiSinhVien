<?php
$page_title = "Admin Dashboard";
require_once '../includes/session.php';
require_once '../includes/header.php';

try {
    // ==========================================
    // 1. THỐNG KÊ TỔNG SỐ
    // ==========================================
    $stmt_students = $pdo->query("SELECT COUNT(id) FROM users WHERE role = 'student'");
    $total_students = $stmt_students->fetchColumn();

    $stmt_lecturers = $pdo->query("SELECT COUNT(id) FROM users WHERE role = 'lecturer'");
    $total_lecturers = $stmt_lecturers->fetchColumn();

    $stmt_faculties = $pdo->query("SELECT COUNT(id) FROM khoa");
    $total_faculties = $stmt_faculties->fetchColumn();

    $stmt_subjects = $pdo->query("SELECT COUNT(id) FROM mon_hoc");
    $total_subjects = $stmt_subjects->fetchColumn();

    // ==========================================
    // 2. DỮ LIỆU BIỂU ĐỒ: SINH VIÊN THEO KHOA
    // ==========================================
    // Lấy số lượng sinh viên cho mỗi khoa, đảm bảo tất cả các khoa đều xuất hiện
    $sql_sv_by_faculty = "
        SELECT
            k.ten_khoa,
            COUNT(u.id) AS so_luong
        FROM khoa k
        LEFT JOIN lop_hoc lh ON k.id = lh.khoa_id
        LEFT JOIN users u ON lh.id = u.lop_hoc_id AND u.role = 'student'
        GROUP BY k.id, k.ten_khoa
    ";
    $res_sv_by_faculty = $pdo->query($sql_sv_by_faculty)->fetchAll(PDO::FETCH_ASSOC);

    // Lấy số lượng sinh viên chưa được phân vào lớp/khoa nào
    $sql_sv_unassigned = "SELECT COUNT(id) FROM users WHERE role = 'student' AND lop_hoc_id IS NULL";
    $unassigned_count = $pdo->query($sql_sv_unassigned)->fetchColumn();

    $chart_data_sv = [];
    foreach($res_sv_by_faculty as $row) {
        $chart_data_sv[$row['ten_khoa']] = (int)$row['so_luong'];
    }

    // Nếu có sinh viên chưa phân lớp, thêm vào mảng dữ liệu
    if ($unassigned_count > 0) {
        $chart_data_sv['Chưa phân lớp'] = (int)$unassigned_count;
    }
    
    // Sắp xếp lại để hiển thị đẹp hơn (ví dụ: theo số lượng giảm dần)
    arsort($chart_data_sv);

    $labels_sv = array_keys($chart_data_sv);
    $data_sv = array_values($chart_data_sv);
    $colors_sv = [];

    // Tạo màu sắc
    foreach ($labels_sv as $label) {
        // Tô màu đỏ cho mục 'Chưa phân lớp' để làm nổi bật
        $colors_sv[] = ($label == 'Chưa phân lớp') ? 'rgba(255, 99, 132, 0.7)' : 'rgba(54, 162, 235, 0.7)';
    }

    // ==========================================
    // 3. DỮ LIỆU BIỂU ĐỒ: PHÂN BỐ GIẢNG VIÊN THEO KHOA (DỰA TRÊN PHÂN CÔNG)
    // ==========================================
    $sql_gv = "
        SELECT
            k.ten_khoa AS label_name,
            COUNT(DISTINCT pc.lecturer_id) AS so_luong
        FROM khoa k
        LEFT JOIN mon_hoc mh ON k.id = mh.khoa_id
        LEFT JOIN phan_cong pc ON mh.id = pc.subject_id AND pc.lecturer_id IS NOT NULL
        GROUP BY k.id, k.ten_khoa
        ORDER BY so_luong DESC
    ";
    $res_gv = $pdo->query($sql_gv)->fetchAll(PDO::FETCH_ASSOC);

    $labels_gv = [];
    $data_gv = [];
    $colors_gv = [];

    // Tạo màu ngẫu nhiên cho các khoa
    function generateRandomColor() {
        $r = rand(0, 200);
        $g = rand(100, 220);
        $b = rand(100, 255);
        return "rgba($r, $g, $b, 0.6)";
    }

    foreach ($res_gv as $row) {
        $labels_gv[] = $row['label_name'];
        $data_gv[] = (int)$row['so_luong']; // Đảm bảo là số nguyên
        $colors_gv[] = generateRandomColor();
    }

} catch (PDOException $e) {
    die("Lỗi kết nối hoặc truy vấn: " . $e->getMessage());
}
?>

<div class="container-fluid">
    <div class="row">

        <?php include_once '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="text-muted">
                        Xin chào, <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong>!
                    </div>
                </div>
            </div>

            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-4">
                <div class="col">
                    <div class="card h-100 text-white bg-primary shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title text-white-50">Sinh viên</h5>
                                    <h2 class="card-text fw-bold"><?php echo $total_students; ?></h2>
                                </div>
                                <i class="bi bi-people-fill" style="font-size: 3rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                        <div class="card-footer bg-primary border-0 text-center">
                            <a href="users.php?role=student" class="text-white text-decoration-none small">Xem chi tiết <i class="bi bi-arrow-right-circle"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 text-white bg-success shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title text-white-50">Giảng viên</h5>
                                    <h2 class="card-text fw-bold"><?php echo $total_lecturers; ?></h2>
                                </div>
                                <i class="bi bi-person-workspace" style="font-size: 3rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                         <div class="card-footer bg-success border-0 text-center">
                            <a href="users.php?role=lecturer" class="text-white text-decoration-none small">Xem chi tiết <i class="bi bi-arrow-right-circle"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 text-white bg-warning shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title text-white-50">Khoa</h5>
                                    <h2 class="card-text fw-bold"><?php echo $total_faculties; ?></h2>
                                </div>
                                <i class="bi bi-building-fill" style="font-size: 3rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                         <div class="card-footer bg-warning border-0 text-center">
                            <a href="falculties.php" class="text-white text-decoration-none small">Xem chi tiết <i class="bi bi-arrow-right-circle"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 text-white bg-danger shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title text-white-50">Môn học</h5>
                                    <h2 class="card-text fw-bold"><?php echo $total_subjects; ?></h2>
                                </div>
                                <i class="bi bi-book-fill" style="font-size: 3rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                         <div class="card-footer bg-danger border-0 text-center">
                            <a href="subjects.php" class="text-white text-decoration-none small">Xem chi tiết <i class="bi bi-arrow-right-circle"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-lg-10 mx-auto">
                    <div class="card shadow">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
                            <h6 class="m-0 font-weight-bold text-primary" id="chartTitle">
                                <i class="bi bi-pie-chart-fill"></i> Phân bố Sinh viên theo Khoa
                            </h6>
                            
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-primary btn-sm active" id="btnShowSV" onclick="switchChart('student')">
                                    <i class="bi bi-people-fill"></i> Sinh viên
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm" id="btnShowGV" onclick="switchChart('lecturer')">
                                    <i class="bi bi-person-workspace"></i> Giảng viên
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div style="height: 400px; position: relative;">
                                <canvas id="mainChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // 1. Chuẩn bị dữ liệu từ PHP
    const dataStore = {
        student: {
            labels: <?php echo json_encode($labels_sv); ?>,
            data: <?php echo json_encode($data_sv); ?>,
            colors: <?php echo json_encode($colors_sv); ?>,
            title: 'Phân bố Sinh viên theo Khoa',
            typeColor: 'primary'
        },
        lecturer: {
            labels: <?php echo json_encode($labels_gv); ?>,
            data: <?php echo json_encode($data_gv); ?>,
            colors: <?php echo json_encode($colors_gv); ?>,
            title: 'Phân bố Giảng viên theo Khoa',
            typeColor: 'success'
        }
    };

    // 2. Khởi tạo biểu đồ
    const ctx = document.getElementById('mainChart').getContext('2d');
    let myChart = new Chart(ctx, {
        type: 'bar', // Dạng cột
        data: {
            labels: dataStore.student.labels,
            datasets: [{
                label: 'Số lượng',
                data: dataStore.student.data,
                backgroundColor: dataStore.student.colors,
                borderWidth: 1,
                borderRadius: 5 // Bo góc cột cho đẹp
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }, // Ẩn chú thích vì màu sắc đã thể hiện rõ hoặc tiêu đề đã nói
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ' Số lượng: ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    ticks: { stepSize: 1 },
                    grid: { color: '#f0f0f0' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // 3. Hàm chuyển đổi biểu đồ
    function switchChart(type) {
        const selectedData = dataStore[type];
        const titleEl = document.getElementById('chartTitle');
        const btnSV = document.getElementById('btnShowSV');
        const btnGV = document.getElementById('btnShowGV');

        // Cập nhật dữ liệu
        myChart.data.labels = selectedData.labels;
        myChart.data.datasets[0].data = selectedData.data;
        myChart.data.datasets[0].backgroundColor = selectedData.colors;
        myChart.update();

        // Cập nhật giao diện tiêu đề
        titleEl.innerHTML = `<i class="bi bi-pie-chart-fill"></i> ${selectedData.title}`;
        titleEl.className = `m-0 font-weight-bold text-${selectedData.typeColor}`;

        // Cập nhật trạng thái nút
        if (type === 'student') {
            btnSV.className = 'btn btn-primary btn-sm active';
            btnGV.className = 'btn btn-outline-success btn-sm';
        } else {
            btnSV.className = 'btn btn-outline-primary btn-sm';
            btnGV.className = 'btn btn-success btn-sm active';
        }
    }
</script>