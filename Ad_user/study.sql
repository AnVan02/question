-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 04, 2025 lúc 08:08 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `study`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khoa_hoc`
--

CREATE TABLE `khoa_hoc` (
  `id` int(11) NOT NULL,
  `khoa_hoc` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `khoa_hoc`
--

INSERT INTO `khoa_hoc` (`id`, `khoa_hoc`) VALUES
(1, 'Python cơ bản'),
(2, 'Python nâng cao'),
(3, 'YOLO');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `quiz`
--

CREATE TABLE `quiz` (
  `Id_cauhoi` int(11) NOT NULL,
  `ten_test` int(11) NOT NULL,
  `khoa_hoc` int(11) NOT NULL,
  `cauhoi` varchar(255) NOT NULL,
  `hinhanh` varchar(255) DEFAULT NULL,
  `cau_a` varchar(255) NOT NULL,
  `giaithich_a` varchar(250) NOT NULL,
  `cau_b` varchar(255) NOT NULL,
  `giaithich_b` varchar(255) NOT NULL,
  `cau_c` varchar(255) NOT NULL,
  `giaithich_c` varchar(255) NOT NULL,
  `cau_d` varchar(255) NOT NULL,
  `giaithich_d` varchar(255) NOT NULL,
  `dap_an` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `quiz`
--

INSERT INTO `quiz` (`Id_cauhoi`, `ten_test`, ``, `cauhoi`, `hinhanh`, `cau_a`, `giaithich_a`, `cau_b`, `giaithich_b`, `cau_c`, `giaithich_c`, `cau_d`, `giaithich_d`, `dap_an`) VALUES
(1, 'giữa kỳ', ' Python cơ bản', 'Python là ngôn ngữ lập trình gì?', 'cuoiky.jpg', 'Python là ngôn ngữ cấp thấp', 'Sai, Python là ngôn ngữ cấp cao', 'Python là ngôn ngữ lập trình tổng quát', 'Đúng', 'Python là ngôn ngữ sử dụng cho hệ điều hành', 'Sai, không đặc trưng cho hệ điều hành', 'Python là ngôn ngữ lập trình cho game', 'Sai, không đặc biệt cho game', 'B'),
(2, 'cuoi ky', 'YOLO', 'Trong Python, lệnh nào dùng để khai báo một hàm?', 'hinh_2.jpg', 'def', 'Đúng, def dùng để khai báo hàm', 'function', 'Sai, không phải cú pháp của Python', 'func', 'Sai, không phải cú pháp của Python', 'declare', 'Sai, không phải cú pháp của Python', 'A'),
(3, 'Giữa kỳ','python nâng cao ', 'YOLO là gì trong học máy?', 'yolo_image.jpg', 'You Only Learn Once', 'Sai, YOLO là You Only Look Once', 'You Open Learning Once', 'Sai, không phải', 'You Only Live Once', 'Sai, không phải', 'You Observe Learning Once', 'Sai, không phải', 'B');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `test`
--

CREATE TABLE `test` (
  `id_test` int(11) NOT NULL,
  `id_khoa` int(11) NOT NULL,
  `ten_test` varchar(255) NOT NULL,
  `lan_thu` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `test`
--

INSERT INTO `test` (`id_test`, `id_khoa`, `ten_test`, `lan_thu`) VALUES
(1, 1, 'Giữa kỳ', 1),
(2, 2, 'Giữa kỳ', 1),
(3, 3, 'Cuối kỳ', 5),
(4, 1, 'cuôi kỳ', 5);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `khoa_hoc`
--
ALTER TABLE `khoa_hoc`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`Id_cauhoi`),
  ADD KEY `id_khoa` (`id_khoa`),
  ADD KEY `id_baitest` (`id_baitest`);

--
-- Chỉ mục cho bảng `test`
--
ALTER TABLE `test`
  ADD PRIMARY KEY (`id_test`),
  ADD KEY `id_khoa` (`id_khoa`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `khoa_hoc`
--
ALTER TABLE `khoa_hoc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `test`
--
ALTER TABLE `test`
  MODIFY `id_test` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `quiz`
--
ALTER TABLE `quiz`
  ADD CONSTRAINT `quiz_ibfk_1` FOREIGN KEY (`id_khoa`) REFERENCES `khoa_hoc` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_ibfk_2` FOREIGN KEY (`id_baitest`) REFERENCES `test` (`id_test`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `test`
--
ALTER TABLE `test`
  ADD CONSTRAINT `test_ibfk_1` FOREIGN KEY (`id_khoa`) REFERENCES `khoa_hoc` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
