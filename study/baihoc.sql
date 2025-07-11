-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost:3306
-- Thời gian đã tạo: Th5 03, 2025 lúc 11:20 AM
-- Phiên bản máy phục vụ: 10.6.21-MariaDB-cll-lve-log
-- Phiên bản PHP: 8.3.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `nvpbgqcv_rosacomputer09`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `Camera`
--

CREATE TABLE `Camera` (
  `id` int(11) NOT NULL,
  `imei` varchar(50) DEFAULT NULL,
  `guid` varchar(100) DEFAULT NULL,
  `mbid` varchar(100) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `new_password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `Camera`
--

INSERT INTO `Camera` (`id`, `imei`, `guid`, `mbid`, `username`, `password`, `new_password`) VALUES
(1, '1014392102', '812cd865-1353-4e95-95a9-8b30baf52278', '/6T47BG3/CNWSC0018Q19GQ/', 'admin', 'admin', 'a');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `Lectures`
--

CREATE TABLE `Lectures` (
  `ID` int(11) NOT NULL,
  `Type` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Lecture` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `UpdateDate` text NOT NULL,
  `Description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Path` text DEFAULT NULL,
  `Link` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Đang đổ dữ liệu cho bảng `Lectures`
--

INSERT INTO `Lectures` (`ID`, `Type`, `Lecture`, `UpdateDate`, `Description`, `Path`, `Link`) VALUES
(51, 'Python cơ bản', '1.0', '2024-11-28 08:59:01', '<h1 style=\"color: rgb(13, 12, 134);\">Python cơ bản</h1>\r\n\r\n<h2>Chương trình học:</h2>\r\n<ul>\r\n  <li><strong>Chương 1:</strong> Giới thiệu chung về Python</li>\r\n  <li><strong>Chương 2:</strong> Cấu trúc điều kiện, vòng lặp và hàm trong Python</li>\r\n  <li><strong>Chương 3:</strong> Cấu trúc dữ liệu trong Python</li>\r\n  <li><strong>Chương 4:</strong> Module và Package</li>\r\n  <li><strong>Chương 5:</strong> Pandas - Phân tích dữ liệu</li>\r\n  <li><strong>Chương 6:</strong> Matplotlib - Vẽ đồ thị dữ liệu</li>\r\n</ul>\r\n\r\n<p><strong>Hãy hoàn thành thật tốt bài tập trong các chương để củng cố kiến thức và nâng cao kỹ năng lập trình Python của bạn!</strong></p>', NULL, 'baihoc.rosacomputer.vn'),
(53, 'YOLO', '1.0', '2025-02-08 09:09:44', '<h1 style=\"color: rgb(13,12,134);\">YOLO11 <span style=\"color: rgb(13,12,134);\"></span></h1>\r\n\r\n<h2>Chương trình học:</h2>\r\n<ul>\r\n  <li><strong>Chương 1:</strong> Giới thiệu về YOLO và Thị giác máy tính</li>\r\n  <li><strong>Chương 2:</strong> Hướng dẫn cơ bản YOLO và ứng dụng</li>\r\n  <li><strong>Chương 3:</strong> Chuẩn bị dữ liệu cho mô hình YOLO</li>\r\n  <li><strong>Chương 4:</strong> Huấn luyện mô hình YOLO với dữ liệu tùy chỉnh</li>\r\n  <li><strong>Chương 5:</strong> Đánh giá và cải thiện hiệu suất mô hình thông qua các thông số tiêu chuẩn</li>\r\n  <li><strong>Chương 6:</strong> Xây dựng ứng dụng thực tế với YOLO</li>\r\n</ul>\r\n\r\n<p><strong>Hãy thực hành thật kỹ các ví dụ và bài tập trong mỗi chương để nâng cao kỹ năng vận dụng YOLO của bạn!</strong></p>', NULL, 'ai.rosacomputer.vn');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `Token_neww`
--

CREATE TABLE `Token_neww` (
  `ID` int(11) NOT NULL,
  `IMEI` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `GUID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MBID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TypeSW` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `CreateDate` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `AccessDate` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Đang đổ dữ liệu cho bảng `Token_neww`
--

INSERT INTO `Token_neww` (`ID`, `IMEI`, `GUID`, `MBID`, `TypeSW`, `CreateDate`, `AccessDate`) VALUES
(3, '3', NULL, NULL, 'Python cơ bản', NULL, NULL),
(9, '1014392100', NULL, NULL, 'Python cơ bản,YOLO', NULL, NULL),
(10, '1014392111', '55b3da0b-b11d-4263-9377-bfc5b7b1859c', '221011433001011', 'Python cơ bản,YOLO', '2025-02-08 10:47:17', '2025-02-08 15:25:32'),
(12, '2', 'e349a55c-45a3-42ba-88e4-20683890e50d', 'Error: [WinError 2] The system cannot find the file specified', 'Python cơ bản,YOLO', '2025-02-08 11:05:32', '2025-02-08 11:25:26'),
(13, '10143921041', NULL, NULL, 'Python cơ bản,YOLO', NULL, NULL),
(14, '1014392105', NULL, NULL, 'Python cơ bản,YOLO', NULL, NULL),
(15, '15', '812cd865-1353-4e95-95a9-8b30baf52278', '/6T47BG3/CNWSC0018Q19GQ/', 'Python cơ bản,YOLO', '2025-02-10 08:58:28', NULL),
(16, '1014392188', NULL, NULL, 'Python cơ bản,YOLO', NULL, NULL),
(19, '19', '045c0333-9682-4fa3-a464-b75927330f11', '230926374300040', 'Python cơ bản,YOLO', '2025-02-10 10:07:03', '2025-02-18 09:06:59'),
(20, '1014398787', '2045d448-dbd5-4d10-83d8-71ae7d832d76', '241248364500135', 'Python cơ bản,YOLO', '2025-02-10 10:10:26', '2025-03-28 10:26:47'),
(21, '1014392156', '1b19081a-1240-4992-97a9-eb3fd1c89aec', '221112174102055', 'Python cơ bản,YOLO', '2025-02-13 16:40:02', '2025-02-17 08:23:49');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `TypeLectures`
--

CREATE TABLE `TypeLectures` (
  `id` int(11) NOT NULL,
  `type_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Đang đổ dữ liệu cho bảng `TypeLectures`
--

INSERT INTO `TypeLectures` (`id`, `type_name`) VALUES
(12, 'Python cơ bản'),
(13, 'Python nâng cao'),
(14, 'YOLO');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `Camera`
--
ALTER TABLE `Camera`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guid` (`guid`),
  ADD KEY `mbid` (`mbid`);

--
-- Chỉ mục cho bảng `Lectures`
--
ALTER TABLE `Lectures`
  ADD PRIMARY KEY (`ID`);

--
-- Chỉ mục cho bảng `Token_neww`
--
ALTER TABLE `Token_neww`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `GUID` (`GUID`,`MBID`);

--
-- Chỉ mục cho bảng `TypeLectures`
--
ALTER TABLE `TypeLectures`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `Camera`
--
ALTER TABLE `Camera`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `Lectures`
--
ALTER TABLE `Lectures`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT cho bảng `Token_neww`
--
ALTER TABLE `Token_neww`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT cho bảng `TypeLectures`
--
ALTER TABLE `TypeLectures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
