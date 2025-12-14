<?php
/*
 * Đây là file cấu hình kết nối cơ sở dữ liệu (CSDL) cho toàn bộ hệ thống.
 * It uses PDO (PHP Data Objects) to ensure a secure connection.
 */

// --- CẤU HÌNH CỦA BẠN --- //
// Thay đổi các giá trị này cho phù hợp với môi trường server của bạn.
define('DB_HOST', 'localhost'); // Thường là 'localhost' hoặc 127.0.0.1
define('DB_NAME', 'student_management'); // Tên CSDL bạn đã tạo
define('DB_USER', 'root');      // User của MySQL, mặc định của XAMPP/WAMP là 'root'
define('DB_PASS', '');          // Mật khẩu của MySQL, mặc định của XAMPP/WAMP là trống

// --- THIẾT LẬP KẾT NỐI PDO --- //
// DSN (Data Source Name) - Chuỗi kết nối
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

// Các tùy chọn cho PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Bật báo lỗi (throw exceptions)
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Đặt chế độ fetch mặc định là mảng kết hợp
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Tắt chế độ giả lập prepared statements để bảo mật hơn
];

// Biến $pdo sẽ được sử dụng trong toàn bộ ứng dụng
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Nếu kết nối thất bại, hiển thị thông báo lỗi và dừng chương trình
    // Trong môi trường production, bạn nên ghi lỗi ra file thay vì hiển thị trực tiếp
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}
?>
