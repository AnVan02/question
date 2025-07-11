-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th7 09, 2025 lúc 08:07 PM
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
-- Cơ sở dữ liệu: `student`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `account`
--

CREATE TABLE `account` (
  `account_id` int(11) NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `account_password` varchar(100) NOT NULL,
  `account_email` varchar(255) NOT NULL,
  `account_phone` varchar(20) NOT NULL,
  `account_type` int(11) NOT NULL,
  `account_status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Đang đổ dữ liệu cho bảng `account`
--

INSERT INTO `account` (`account_id`, `account_name`, `account_password`, `account_email`, `account_phone`, `account_type`, `account_status`) VALUES
(23, 'Admin', '123456', 'admin@gmail.com', '', 2, 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ket_qua`
--


CREATE TABLE `ket_qua` (
  `student_id` int(11) NOT NULL,
  `khoa_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `so_lan_thu` int(11) DEFAULT 1,
  `kq_cao_nhat` int(11) DEFAULT 0,
  `test_cao_nhat` text DEFAULT NULL COMMENT 'Lưu dạng JSON hoặc format thống nhất',
  `test_gan_nhat` text DEFAULT NULL COMMENT 'Lưu dạng JSON hoặc format thống nhất',
  PRIMARY KEY (`student_id`, `khoa_id`, `test_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Đang đổ dữ liệu cho bảng `ket_qua`
--

INSERT INTO `ket_qua` (`student_id`, `khoa_id`, `test_id`, `kq_cao_nhat`, `tt_bai_test`) VALUES
(1, 1, '19', 0, '0:B');

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
(3, 'YOLO'),
(4, 'Toán'),
(5, 'Văn'),
(6, 'Tiếng anh'),
(8, 'Sinh học'),
(10, 'Hoá học');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `kiem_tra`
--

CREATE TABLE `kiem_tra` (
  `Student_ID` int(11) NOT NULL,
  `Khoa_ID` int(11) NOT NULL,
  `Test_ID` varchar(255) NOT NULL,
  `Best_Score` int(11) DEFAULT 0,
  `Max_Score` int(11) DEFAULT 0,
  `Pass` varchar(10) DEFAULT '',
  `Trial` int(11) DEFAULT 0,
  `Max_trial` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `kiem_tra`
--

INSERT INTO `kiem_tra` (`Student_ID`, `Khoa_ID`, `Test_ID`, `Best_Score`, `Max_Score`, `Pass`, `Trial`, `Max_trial`) VALUES
(3, 1, '19', 0, 0, '80', 0, 3),
(3, 6, '21', 0, 0, '80', 0, 3),
(3, 5, '22', 0, 0, '100', 0, 2),
(3, 3, '16', 0, 0, '80', 0, 2),
(2, 10, '12', 0, 0, '80', 0, 2),
(2, 4, '23', 0, 0, '80', 0, 3),
(2, 3, '16', 0, 0, '80', 0, 2),
(4, 1, '19', 0, 0, '80', 0, 3),
(1, 1, '19', 0, 0, '80', 0, 3);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `login`
--

CREATE TABLE `login` (
  `Id` int(11) NOT NULL,
  `Student_ID` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `login`
--

INSERT INTO `login` (`Id`, `Student_ID`, `Password`) VALUES
(1, 'A', '1'),
(2, 'B', '2'),
(3, 'C', '3'),
(4, '4', '4');

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
  `hinhanh_a` varchar(255) DEFAULT NULL,
  `giaithich_a` varchar(250) NOT NULL,
  `cau_b` varchar(255) NOT NULL,
  `hinhanh_b` varchar(255) DEFAULT NULL,
  `giaithich_b` varchar(255) NOT NULL,
  `cau_c` varchar(255) NOT NULL,
  `hinhanh_c` varchar(255) DEFAULT NULL,
  `giaithich_c` varchar(255) NOT NULL,
  `cau_d` varchar(255) NOT NULL,
  `hinhanh_d` varchar(255) DEFAULT NULL,
  `giaithich_d` varchar(255) NOT NULL,
  `dap_an` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `quiz`
--

INSERT INTO `quiz` (`Id_cauhoi`, `id_baitest`, `ten_khoa`, `cauhoi`, `hinhanh`, `cau_a`, `hinhanh_a`, `giaithich_a`, `cau_b`, `hinhanh_b`, `giaithich_b`, `cau_c`, `hinhanh_c`, `giaithich_c`, `cau_d`, `hinhanh_d`, `giaithich_d`, `dap_an`) VALUES
(1, 'Bài kiem tra chương 1', 'Python cơ bản', 'qqqqqqqqqq', NULL, 'aaaaaaaa', NULL, 'aaaaaaaaaaaa', 'aaaaaaaaaaaaa', NULL, 'aaaaaaaaaaaaaaaaaaaaaaa', 'aaaaaaaaaaaaaaa', NULL, 'ssssssssssssss', 'ssssssssssssssss', 'images/d_686ea437a7402.png', 'wwwwwwwwwwwwwwww', '0'),
(2, 'Bài kiem tra chương 1', 'Python cơ bản', 'qqqqqqqqqqq', NULL, 'qqqqqqqqqqqqqq', NULL, 'qqqqqqqqqqqq', 'qqqqqqqqqqqq', NULL, 'qqqqqqqqqqqqqq', 'qqqqqqqqqqq', NULL, 'QQQQQQQQQQQQQQ', 'QQQQQQQQQQQQ', NULL, 'sssssssssssss', 'B');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sinhvien`
--

CREATE TABLE `sinhvien` (
  `student_id` text NOT NULL,
  `ten_hs` text NOT NULL,
  `pass` text NOT NULL,
  `khoa_hoc` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `students`
--

CREATE TABLE `students` (
  `IMEI` bigint(20) NOT NULL,
  `MB_ID` int(11) NOT NULL,
  `OS_ID` int(11) NOT NULL,
  `Student_ID` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Ten` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Khoahoc` varchar(1000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `students`
--

INSERT INTO `students` (`IMEI`, `MB_ID`, `OS_ID`, `Student_ID`, `Password`, `Ten`, `Email`, `Khoahoc`) VALUES
(1, 1, 1, '1', '1', 'A', 'an1@gmail.com', '1'),
(2, 2, 2, '2', '2', 'B', 'an2@gmail.com', '10,3,4'),
(3, 3, 3, '3', '3', 'C', 'An3@gmail.com', '1,6,5,3'),
(4, 4, 4, '4', '4', 'AN', 'tvdell789@gmail.com', '10,1');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `test`
--

CREATE TABLE `test` (
  `id_test` int(11) NOT NULL,
  `id_khoa` int(11) NOT NULL,
  `ten_test` varchar(255) NOT NULL,
  `lan_thu` int(11) DEFAULT 1,
  `Pass` varchar(255) NOT NULL,
  `so_cau_hien_thi` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `test`
--

INSERT INTO `test` (`id_test`, `id_khoa`, `ten_test`, `lan_thu`, `Pass`, `so_cau_hien_thi`) VALUES
(1, 8, 'Giữa kỳ', 2, '80', 0),
(16, 3, 'Giữa kỳ', 2, '80', 0),
(19, 1, 'Giữa kỳ', 3, '80', 0),
(22, 5, 'Giữa kỳ', 2, '100', 0),
(23, 4, 'Giữa kỳ', 3, '80', 0),
(29, 2, 'Giữa kỳ', 2, '100', 0),
(41, 8, 'Cuối kỳ', 1, '100', 0),
(42, 8, 'Cuối kỳ', 1, '100', 0);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `ket_qua`
--
ALTER TABLE `ket_qua`
  ADD PRIMARY KEY (`student_id`,`khoa_id`,`test_id`);

--
-- Chỉ mục cho bảng `khoa_hoc`
--
ALTER TABLE `khoa_hoc`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`Id`);

--
-- Chỉ mục cho bảng `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`Id_cauhoi`);

--
-- Chỉ mục cho bảng `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`IMEI`);

--
-- Chỉ mục cho bảng `test`
--
ALTER TABLE `test`
  ADD PRIMARY KEY (`id_test`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `khoa_hoc`
--
ALTER TABLE `khoa_hoc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `quiz`
--
ALTER TABLE `quiz`
  MODIFY `Id_cauhoi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT cho bảng `test`
--
ALTER TABLE `test`
  MODIFY `id_test` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
