-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 19, 2025 at 02:48 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `student_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `dang_ky`
--

DROP TABLE IF EXISTS `dang_ky`;
CREATE TABLE IF NOT EXISTS `dang_ky` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `phan_cong_id` int NOT NULL,
  `status` enum('active','dropped') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_phancong` (`student_id`,`phan_cong_id`),
  KEY `fk_dangky_student` (`student_id`),
  KEY `fk_dangky_phancong` (`phan_cong_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dang_ky`
--

INSERT INTO `dang_ky` (`id`, `student_id`, `phan_cong_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 5, 1, 'active', '2025-12-14 09:15:57', '2025-12-14 09:36:27'),
(4, 5, 2, 'dropped', '2025-12-14 09:17:53', '2025-12-14 09:36:24'),
(14, 4, 1, 'dropped', '2025-12-16 05:31:24', '2025-12-16 05:31:25'),
(16, 4, 2, 'dropped', '2025-12-16 06:00:38', '2025-12-16 06:00:46');

-- --------------------------------------------------------

--
-- Table structure for table `diem`
--

DROP TABLE IF EXISTS `diem`;
CREATE TABLE IF NOT EXISTS `diem` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `subject_id` int NOT NULL,
  `phan_cong_id` int NOT NULL,
  `diem_qua_trinh` float DEFAULT NULL,
  `diem_giua_ky` float DEFAULT NULL,
  `diem_cuoi_ky` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `subject_id` (`subject_id`),
  KEY `phan_cong_id` (`phan_cong_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `diem`
--

INSERT INTO `diem` (`id`, `student_id`, `subject_id`, `phan_cong_id`, `diem_qua_trinh`, `diem_giua_ky`, `diem_cuoi_ky`) VALUES
(1, 4, 1, 1, 8, 8, 8.5),
(2, 5, 1, 1, 3, 7.2, 5),
(3, 4, 2, 2, 9, 8, 9.5),
(4, 5, 2, 2, 5, 4, 3);

-- --------------------------------------------------------

--
-- Table structure for table `khoa`
--

DROP TABLE IF EXISTS `khoa`;
CREATE TABLE IF NOT EXISTS `khoa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ma_khoa` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_khoa` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ma_khoa` (`ma_khoa`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `khoa`
--

INSERT INTO `khoa` (`id`, `ma_khoa`, `ten_khoa`) VALUES
(1, 'CNTT', 'Công nghệ Thông tin'),
(2, 'KT', 'Kinh tế'),
(3, 'NN', 'Ngoại ngữ');

-- --------------------------------------------------------

--
-- Table structure for table `lop_hoc`
--

DROP TABLE IF EXISTS `lop_hoc`;
CREATE TABLE IF NOT EXISTS `lop_hoc` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ma_lop` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_lop` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `khoa_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ma_lop` (`ma_lop`),
  KEY `khoa_id` (`khoa_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lop_hoc`
--

INSERT INTO `lop_hoc` (`id`, `ma_lop`, `ten_lop`, `khoa_id`) VALUES
(1, 'CNTT.K15', 'Công nghệ Thông tin K15', 1),
(2, 'KT.K15', 'Kế toán K15', 2),
(3, 'NN.K16', 'Ngôn ngữ Anh K16', 3);

-- --------------------------------------------------------

--
-- Table structure for table `mon_hoc`
--

DROP TABLE IF EXISTS `mon_hoc`;
CREATE TABLE IF NOT EXISTS `mon_hoc` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ma_mon` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_mon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `so_tin_chi` int NOT NULL,
  `khoa_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ma_mon` (`ma_mon`),
  KEY `khoa_id` (`khoa_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mon_hoc`
--

INSERT INTO `mon_hoc` (`id`, `ma_mon`, `ten_mon`, `so_tin_chi`, `khoa_id`) VALUES
(1, 'CSDL', 'Cơ sở dữ liệu', 3, 1),
(2, 'LTHDT', 'Lập trình hướng đối tượng', 3, 1),
(3, 'KTVM', 'Kinh tế vĩ mô', 2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `phan_cong`
--

DROP TABLE IF EXISTS `phan_cong`;
CREATE TABLE IF NOT EXISTS `phan_cong` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lecturer_id` int NOT NULL,
  `subject_id` int NOT NULL,
  `lop_hoc_id` int NOT NULL,
  `hoc_ky` int NOT NULL,
  `nam_hoc` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `lecturer_id` (`lecturer_id`),
  KEY `subject_id` (`subject_id`),
  KEY `lop_hoc_id` (`lop_hoc_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `phan_cong`
--

INSERT INTO `phan_cong` (`id`, `lecturer_id`, `subject_id`, `lop_hoc_id`, `hoc_ky`, `nam_hoc`) VALUES
(1, 2, 1, 1, 1, '2023-2024'),
(2, 3, 2, 1, 1, '2023-2024');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','lecturer','student') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `gender` enum('Nam','Nữ','Khác') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'default.png',
  `lop_hoc_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `lop_hoc_id` (`lop_hoc_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `email`, `full_name`, `phone`, `address`, `gender`, `birthday`, `avatar`, `lop_hoc_id`) VALUES
(1, 'admin', '$2y$10$osliu2uKlYgGvPqxDwd5eeRjXniDxqFOfK3mVsuzZyOcCHANb8qnG', 'admin', 'admin@example.com', 'Quản trị viên', '0123456789', '123 Admin Street', 'Nam', '1990-01-01', 'default.png', NULL),
(2, 'GV001', '$2y$10$N0JDgahpGnxQUyHsScMLDOlmjoXpbo2fCcnXIXyuCGuQ6UP27oZCy', 'lecturer', 'GV001@lecturer.edu.vn', 'Nguyễn Văn A', '0987654321', '456 Teacher Avenue', 'Nam', '1985-05-10', 'default.png', NULL),
(3, 'GV002', '$2y$10$N0JDgahpGnxQUyHsScMLDOlmjoXpbo2fCcnXIXyuCGuQ6UP27oZCy', 'lecturer', 'GV002@lecturer.edu.vn', 'Trần Thị B', '0912345678', '789 Professor Road', 'Nữ', '1988-08-20', 'default.png', NULL),
(4, 'SV001', '$2y$10$N0JDgahpGnxQUyHsScMLDOlmjoXpbo2fCcnXIXyuCGuQ6UP27oZCy', 'student', 'SV001@student.edu.vn', 'Lê Văn C', '0369852147', '111 Student Lane', 'Nam', '2003-10-15', 'default.png', 1),
(5, 'SV002', '$2y$10$N0JDgahpGnxQUyHsScMLDOlmjoXpbo2fCcnXIXyuCGuQ6UP27oZCy', 'student', 'SV002@student.edu.vn', 'Phạm Thị D', '0321456987', '222 Student Boulevard', 'Nữ', '2003-11-25', 'default.png', 1),
(6, 'SV003', '$2y$10$N0JDgahpGnxQUyHsScMLDOlmjoXpbo2fCcnXIXyuCGuQ6UP27oZCy', 'student', 'SV003@student.edu.vn', 'Hoàng Văn E', '0958741236', '333 Student Court', 'Nam', '2003-12-30', 'default.png', 2),
(7, 'SV004', '$2y$10$PIIgwaRheYXJpj8EjxC7TuwDSBoKGkph1w3UQgExvwsCIrRdHlBU2', 'student', 'SV004@student.edu.vn', 'Nguyễn Văn F', NULL, NULL, NULL, NULL, 'default.png', 3);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dang_ky`
--
ALTER TABLE `dang_ky`
  ADD CONSTRAINT `fk_dangky_phancong` FOREIGN KEY (`phan_cong_id`) REFERENCES `phan_cong` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_dangky_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `diem`
--
ALTER TABLE `diem`
  ADD CONSTRAINT `diem_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `diem_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `mon_hoc` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `diem_ibfk_3` FOREIGN KEY (`phan_cong_id`) REFERENCES `phan_cong` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lop_hoc`
--
ALTER TABLE `lop_hoc`
  ADD CONSTRAINT `lop_hoc_ibfk_1` FOREIGN KEY (`khoa_id`) REFERENCES `khoa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mon_hoc`
--
ALTER TABLE `mon_hoc`
  ADD CONSTRAINT `mon_hoc_ibfk_1` FOREIGN KEY (`khoa_id`) REFERENCES `khoa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `phan_cong`
--
ALTER TABLE `phan_cong`
  ADD CONSTRAINT `phan_cong_ibfk_1` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `phan_cong_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `mon_hoc` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `phan_cong_ibfk_3` FOREIGN KEY (`lop_hoc_id`) REFERENCES `lop_hoc` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`lop_hoc_id`) REFERENCES `lop_hoc` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
