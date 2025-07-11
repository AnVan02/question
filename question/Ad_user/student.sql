-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th7 10, 2025 lúc 06:12 AM
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

DELIMITER $$
--
-- Thủ tục
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `InsertKetQuaWithIdCauhoi` (IN `p_student_id` INT, IN `p_khoa_id` INT, IN `p_test_id` VARCHAR(255), IN `p_kq_cao_nhat` INT, IN `p_dap_an_list` VARCHAR(1000))   BEGIN
    DECLARE v_id_cauhoi INT;
    DECLARE v_new_tt_bai_test VARCHAR(1000) DEFAULT '';
    DECLARE v_dap_an VARCHAR(255);
    DECLARE v_counter INT DEFAULT 1;
    DECLARE done INT DEFAULT FALSE;
    DECLARE cur_quiz CURSOR FOR 
        SELECT Id_cauhoi 
        FROM quiz 
        WHERE id_baitest = p_test_id 
        ORDER BY Id_cauhoi;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    -- Tách danh sách đáp án
    SET @dap_an_1 = TRIM(SUBSTRING_INDEX(p_dap_an_list, ',', 1));
    SET @dap_an_2 = IF(LOCATE(',', p_dap_an_list) > 0, TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(p_dap_an_list, ',', 2), ',', -1)), '');
    SET @dap_an_3 = IF(LOCATE(',', SUBSTRING_INDEX(p_dap_an_list, ',', -1)) > 0, TRIM(SUBSTRING_INDEX(p_dap_an_list, ',', -1)), '');

    -- Xây dựng tt_bai_test
    OPEN cur_quiz;
    read_quiz: LOOP
        FETCH cur_quiz INTO v_id_cauhoi;
        IF done THEN
            LEAVE read_quiz;
        END IF;
        IF v_counter = 1 THEN
            SET v_new_tt_bai_test = CONCAT('id', v_id_cauhoi, ': ', @dap_an_1);
        ELSEIF v_counter = 2 AND @dap_an_2 != '' THEN
            SET v_new_tt_bai_test = CONCAT(v_new_tt_bai_test, ', id', v_id_cauhoi, ': ', @dap_an_2);
        ELSEIF v_counter = 3 AND @dap_an_3 != '' THEN
            SET v_new_tt_bai_test = CONCAT(v_new_tt_bai_test, ', id', v_id_cauhoi, ': ', @dap_an_3);
        END IF;
        SET v_counter = v_counter + 1;
    END LOOP;
    CLOSE cur_quiz;

    -- Thêm bản ghi vào ket_qua
    INSERT INTO `ket_qua` (`student_id`, `khoa_id`, `test_id`, `kq_cao_nhat`, `tt_bai_test`)
    VALUES (p_student_id, p_khoa_id, p_test_id, p_kq_cao_nhat, v_new_tt_bai_test);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `account`
--

CREATE TABLE `account` (
  `account_id` int(11) NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `account_password` varchar(100) NOT NULL,
  `account_email` varchar(255) NOT NULL,
  `account_type` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Đang đổ dữ liệu cho bảng `account`
--

INSERT INTO `account` (`account_id`, `account_name`, `account_password`, `account_email`, `account_type`) VALUES
(1, 'Admin', '123456', 'admin@gmail.com', 2),
(2, 'Ad', '$2y$10$6niXOEGeDuvAbW8KC1x9EOUj1JPCtGxUCZGvhs2hDbAwj/6ZJkdce', 'admin2@gmail.com', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ket_qua`
--

CREATE TABLE `ket_qua` (
  `student_id` int(11) NOT NULL,
  `khoa_id` int(11) NOT NULL,
  `test_id` varchar(255) NOT NULL,
  `so_lan_thu` varchar(255) NOT NULL,
  `kq_cao_nhat` int(255) NOT NULL,
  `test_cao_nhat` varchar(1000) NOT NULL,
  `test_gan_nhat` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--_________
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
(10, 'Python cơ bản'),
(28, 'Yolo'),
(29, 'C ++'),
(30, 'C'),
(31, 'Toán');

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
(2, 28, '59', 0, 0, '80', 0, 3),
(1, 10, '71', 0, 0, '80', 0, 3);

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
(1, 'Bài kiểm tra chương 1', 'Python cơ bản', 'qqqqqqqqqqq', NULL, 'qqqqqqqqqqqqqq', NULL, 'qqqqqqqqqqqq', 'qqqqqqqqqqqq', NULL, 'qqqqqqqqqqqqqq', 'qqqqqqqqqqq', NULL, 'QQQQQQQQQQQQQQ', 'QQQQQQQQQQQQ', NULL, 'sssssssssssss', 'B'),
(2, 'Bài kiểm tra chương 1', 'Python cơ bản', 'qqqqqqqqqq', NULL, 'aaaaaaaa', NULL, 'aaaaaaaaaaaa', 'aaaaaaaaaaaaa', NULL, 'aaaaaaaaaaaaaaaaaaaaaaa', 'aaaaaaaaaaaaaaa', NULL, 'ssssssssssssss', 'ssssssssssssssss', 'images/d_686ea437a7402.png', 'wwwwwwwwwwwwwwww', '0'),
(3, 'Bài kiểm tra chương 1', 'Python cơ bản', 'qqqqqqqqqq', NULL, 'aaaaaaaa', NULL, 'aaaaaaaaaaaa', 'aaaaaaaaaaaaa', NULL, 'aaaaaaaaaaaaaaaaaaaaaaa', 'aaaaaaaaaaaaaaa', NULL, 'ssssssssssssss', 'ssssssssssssssss', 'images/d_686ea437a7402.png', 'wwwwwwwwwwwwwwww', '0'),
(4, 'Bài kiểm tra chương 1', 'Python cơ bản', 'qqqqqqqqqqq', NULL, 'qqqqqqqqqqqqqq', NULL, 'qqqqqqqqqqqq', 'qqqqqqqqqqqq', NULL, 'qqqqqqqqqqqqqq', 'qqqqqqqqqqq', NULL, 'QQQQQQQQQQQQQQ', 'QQQQQQQQQQQQ', NULL, 'sssssssssssss', 'B');

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
(1, 1, 1, '1', '1', 'A', 'an1@gmail.com', '10'),
(2, 2, 2, '2', '2', 'B', 'an2@gmail.com', '28'),
(3, 3, 3, '3', '3', 'C', 'An3@gmail.com', ''),
(4, 4, 4, '4', '4', 'abc', 'aa@gmail.com', '');

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
(52, 26, 'Cuối kỳ', 3, '100', 0),
(53, 26, 'Giữa kỳ', 3, '80', 0),
(59, 28, 'Giữa kỳ', 3, '80', 0),
(71, 10, 'Bài kiểm tra chương 1', 3, '80', 4),
(72, 31, 'Giữa kỳ', 1, '80', 0);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`account_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT cho bảng `test`
--
ALTER TABLE `test`
  MODIFY `id_test` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
