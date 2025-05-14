-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 05, 2025 lúc 07:28 PM
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
-- Cấu trúc bảng cho `accounts`
--

CREATE TABLE `accounts`(
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar (255) NOT NULL,
  `bai_hoc` varchar (255) NOT NULL,
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--  Đang đỗ dữ liệu cho bảng `accounts` 
-- 

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
  `id_baitest` varchar(50) NOT NULL COMMENT 'Lưu Giữa kỳ hoặc Cuối kỳ',
  `ten_khoa` varchar(100) NOT NULL COMMENT 'Tên môn học, ví dụ: Lập trình',
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

INSERT INTO `quiz` (`Id_cauhoi`, `id_baitest`, `ten_khoa`, `cauhoi`, `hinhanh`, `cau_a`, `giaithich_a`, `cau_b`, `giaithich_b`, `cau_c`, `giaithich_c`, `cau_d`, `giaithich_d`, `dap_an`) VALUES
(1, 'Giữa kỳ', 'python cơ bản', 'Trong JavaScript, phương thức nào dùng để thêm phần tử vào cuối mảng?', NULL, 'push()', 'Phương thức push() thêm phần tử vào cuối mảng', 'pop()', 'pop() xóa phần tử cuối mảng', 'shift()', 'shift() xóa phần tử đầu mảng', 'unshift()', 'unshift() thêm phần tử vào đầu mảng', 'A'),
(2, 'Cuối kỳ', 'python nâng cao', 'Kết quả của đoạn code C sau là gì? ```int main() { int x = 3; printf(\"%d\", x++); return 0; }```', 'uploads/c_code.png', '3', 'x++ tăng x sau khi in, nên in 3', '4', 'Sai: x++ tăng sau khi in, không phải ++x', '0', 'Sai: code không trả về 0', 'Lỗi cú pháp', 'Code không có lỗi cú pháp', 'A'),
(3, 'Giữa kỳ', 'Yolo', 'Trong lập trình, \"đệ quy\" là gì?', NULL, 'Hàm gọi chính nó', 'Đệ quy là khi hàm tự gọi lại với tham số khác', 'Vòng lặp vô hạn', 'Sai: đệ quy không phải vòng lặp vô hạn', 'Hàm không trả về', 'Sai: đệ quy không liên quan đến việc không trả về', 'Gọi hàm khác', 'Sai: đệ quy là gọi chính hàm đó', 'A'),
(4, 'Cuối kỳ', 'Python cơ bản', 'Trong Java, từ khóa nào dùng để tạo đối tượng mới?', 'uploads/java_new.png', 'new', 'Từ khóa new cấp phát bộ nhớ cho đối tượng', 'create', 'Sai: Java không có từ khóa create', 'instance', 'Sai: instance không phải từ khóa', 'object', 'Sai: object không phải từ khóa tạo đối tượng', 'A'),
(5, 'Giữa kỳ', 'Yolo', 'Thuật toán tìm kiếm nào hiệu quả nhất cho mảng đã sắp xếp?', NULL, 'Binary Search', 'Binary Search có độ phức tạp O(log n) cho mảng đã sắp xếp', 'Linear Search', 'Linear Search có O(n), kém hiệu quả hơn', 'Bubble Search', 'Sai: không có thuật toán Bubble Search', 'Quick Search', 'Sai: Quick Search không tồn tại', 'A');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `test`
--

CREATE TABLE `test` (
  `id_test` int(11) NOT NULL AUTO_INCREMENT,
  `id_khoa` int(11) NOT NULL,
  `ten_test` varchar(255) NOT NULL,
  `lan_thu` int(11) DEFAULT 1,
  PRIMARY KEY (`id_test`)
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
  ADD PRIMARY KEY (`Id_cauhoi`);

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
-- AUTO_INCREMENT cho bảng `quiz`
--
ALTER TABLE `quiz`
  MODIFY `Id_cauhoi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `test`
--
ALTER TABLE `test`
  MODIFY `id_test` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `test`
--
ALTER TABLE `test`
  ADD CONSTRAINT `test_ibfk_1` FOREIGN KEY (`id_khoa`) REFERENCES `khoa_hoc` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;