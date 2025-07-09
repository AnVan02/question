-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th6 19, 2025 lúc 08:58 AM
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
-- Cấu trúc bảng cho bảng 'login'
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

INSERT INTO `account` (`account_id`, `account_name`, `account_password`, `account_email`,`account_type`) VALUES
(1, 'Admin', '123456', 'admin@gmail.com',2);



--
-- Cấu trúc bảng cho bảng `ket_qua`
--

CREATE TABLE `ket_qua` (
  `student_id` int(11) NOT NULL,
  `khoa_id` int(11) NOT NULL,
  `test_id` varchar(255) NOT NULL,
  `kq_cao_nhat` int(255) NOT NULL,
  `tt_bai_test` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `ket_qua`
--

INSERT INTO `ket_qua` (`student_id`, `khoa_id`, `test_id`, `kq_cao_nhat`, `tt_bai_test`) VALUES
(1, 1, '19', 4, 'Câu 1: B, Câu 2: C'),
(2, 10, '12', 4, 'Câu 1: B, Câu 2: C, Câu 3: D');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khoa_hoc`
--


CREATE TABLE `khoa_hoc` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `khoa_hoc` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
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
(1, 10, '12', 0, 0, '80', 0, 2),
(1, 2, '29', 0, 0, '100', 0, 2),
(1, 4, '23', 0, 0, '80', 0, 3);

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
  `Id_cauhoi` int(11) NOT NULL AUTO_INCREMENT,
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
  `dap_an` varchar(255) NOT NULL,
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- lấy dữ liên thêm cau hỏi tăng id_câuhoi

ALTER TABLE quiz
MODIFY COLUMN Id_cauhoi INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE quiz MODIFY Id_cauhoi INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE quiz ADD PRIMARY KEY (Id_cauhoi);


-----
--
-- Đang đổ dữ liệu cho bảng `quiz`
--

INSERT INTO `quiz` (`Id_cauhoi`, `id_baitest`, `ten_khoa`, `cauhoi`, `hinhanh`, `cau_a`, `giaithich_a`, `cau_b`, `giaithich_b`, `cau_c`, `giaithich_c`, `cau_d`, `giaithich_d`, `dap_an`) VALUES
(1, 'Giữa kỳ', 'Python cơ bản', 'Câu lệnh in ra \"Hello World\" trong Python là?', NULL, 'print(\"Hello World\")', 'Đúng', 'echo \"Hello World\"', 'Sai, dùng trong PHP', 'console.log(\"Hello World\")', 'Sai, JS dùng', 'System.out.println(\"Hello World\")', 'Sai, Java dùng', 'A'),
(2, 'Giữa kỳ', 'Python cơ bản', 'Kiểu dữ liệu nào lưu trữ chuỗi?', NULL, 'int', 'Số nguyên', 'str', 'Đúng, là kiểu chuỗi', 'bool', 'Boolean', 'float', 'Số thực', 'B'),
(3, 'Giữa kỳ', 'Python cơ bản', 'Vòng lặp nào lặp qua dãy số?', NULL, 'for', 'Đúng, dùng for range', 'if', 'Không phải vòng lặp', 'try', 'Khối xử lý lỗi', 'print', 'In ra', 'A'),
(4, 'Giữa kỳ', 'Python cơ bản', 'Cách khai báo danh sách?', NULL, '[1,2,3]', 'Đúng, là list', '(1,2,3)', 'Tuple', '{1,2,3}', 'Set', '<1,2,3>', 'Không hợp lệ', 'A'),
(5, 'Cuối kỳ', 'Python cơ bản', 'Toán tử so sánh bằng là?', NULL, '=', 'Gán giá trị', '==', 'Đúng', '!=', 'So sánh khác', '===', 'Không dùng trong Python', 'B'),
(6, 'Cuối kỳ', 'Python cơ bản', 'Hàm tính độ dài list?', NULL, 'length()', 'Sai', 'len()', 'Đúng', 'count()', 'Sai', 'size()', 'Sai', 'B'),
(7, 'Giữa kỳ', 'Python nâng cao', 'Lambda dùng để làm gì?', NULL, 'Định nghĩa hàm nhanh', 'Đúng', 'Tạo biến', 'Sai', 'Tạo class', 'Sai', 'Tạo loop', 'Sai', 'A'),
(8, 'Giữa kỳ', 'Python nâng cao', 'Decorator được dùng để?', NULL, 'Trang trí UI', 'Sai', 'Chạy chương trình', 'Sai', 'Thêm chức năng cho hàm', 'Đúng', 'Gỡ lỗi', 'Sai', 'C'),
(9, 'Giữa kỳ', 'Python nâng cao', 'Gói chuẩn để xử lý JSON?', NULL, 'os', 'Sai', 'sys', 'Sai', 'json', 'Đúng', 'math', 'Sai', 'C'),
(10, 'Cuối kỳ', 'Python nâng cao', 'Generator là gì?', NULL, 'Hàm trả iterator', 'Đúng', 'List', 'Sai', 'Loop', 'Sai', 'Dict', 'Sai', 'A'),
(11, 'Cuối kỳ', 'Python nâng cao', 'Từ khóa yield dùng trong?', NULL, 'Class', 'Sai', 'Loop', 'Sai', 'Generator', 'Đúng', 'Import', 'Sai', 'C'),
(12, 'Cuối kỳ', 'Python nâng cao', 'Module nào làm việc với file hệ thống?', NULL, 'os', 'Đúng', 'json', 'Không đúng', 'sys', 'Sai', 'math', 'Sai', 'A'),
(13, 'Giữa kỳ', 'YOLO', 'YOLO là viết tắt của?', NULL, 'You Only Learn Once', 'Sai', 'You Only Look Once', 'Đúng', 'Your Only Logic Option', 'Sai', 'None of the above', 'Sai', 'B'),
(14, 'Giữa kỳ', 'YOLO', 'YOLO dùng để?', NULL, 'Dịch ngôn ngữ', 'Sai', 'Xử lý ảnh', 'Đúng', 'Phân tích âm thanh', 'Sai', 'Tạo ảnh', 'Sai', 'B'),
(15, 'Giữa kỳ', 'YOLO', 'YOLO thuộc nhóm?', NULL, 'Phân loại ảnh', 'Sai', 'Phát hiện vật thể', 'Đúng', 'Tăng cường học', 'Sai', 'LSTM', 'Sai', 'B'),
(16, 'Cuối kỳ', 'YOLO', 'YOLO dựa vào?', NULL, 'CNN', 'Đúng', 'RNN', 'Sai', 'GAN', 'Sai', 'Transformer', 'Sai', 'A'),
(17, 'Cuối kỳ', 'YOLO', 'YOLOv4 khác gì YOLOv3?', NULL, 'Nhanh hơn', 'Đúng', 'Chậm hơn', 'Sai', 'Không khác gì', 'Sai', 'Cũ hơn', 'Sai', 'A'),
(18, 'Cuối kỳ', 'YOLO', 'Đầu ra của YOLO là?', NULL, 'Văn bản', 'Sai', 'Ảnh', 'Sai', 'Hộp giới hạn & nhãn', 'Đúng', 'Âm thanh', 'Sai', 'C'),
(19, 'Cuối kỳ', 'Toán', 'Giá trị của π là?', NULL, '3.14', 'Gần đúng', '2.71', 'Sai', '1.61', 'Sai', '1.41', 'Sai', 'A'),
(20, 'Giữa kỳ', 'Toán', 'Đạo hàm của x^2 là?', NULL, 'x', 'Sai', '2x', 'Đúng', 'x^2', 'Sai', '1', 'Sai', 'B'),
(21, 'Giữa kỳ', 'Toán', 'Hàm số y = mx + b là dạng?', NULL, 'Bậc hai', 'Sai', 'Tuyến tính', 'Đúng', 'Hằng số', 'Sai', 'Lôgarit', 'Sai', 'B'),
(22, 'Giữa kỳ', 'Toán', 'sin(90°) bằng?', NULL, '0', 'Sai', '1', 'Đúng', '0.5', 'Sai', '√2/2', 'Sai', 'B'),
(23, 'Giữa kỳ', 'Toán', 'Căn bậc hai của 49?', NULL, '5', 'Sai', '6', 'Sai', '7', 'Đúng', '8', 'Sai', 'C'),
(24, 'Giữa kỳ', 'Toán', 'log(100) cơ số 10?', NULL, '1', 'Sai', '2', 'Đúng', '10', 'Sai', '0', 'Sai', 'B'),
(25, 'Giữa kỳ', 'Văn', 'Tác giả Truyện Kiều?', NULL, 'Nguyễn Du', 'Đúng', 'Nguyễn Trãi', 'Sai', 'Hồ Xuân Hương', 'Sai', 'Tố Hữu', 'Sai', 'A'),
(26, 'Giữa kỳ', 'Văn', 'Phong cách thơ Xuân Quỳnh?', NULL, 'Lãng mạn', 'Đúng', 'Hiện thực', 'Sai', 'Trào phúng', 'Sai', 'Chính luận', 'Sai', 'A'),
(27, 'Giữa kỳ', 'Văn', 'Truyện ngắn \"Lão Hạc\" của?', NULL, 'Nam Cao', 'Đúng', 'Kim Lân', 'Sai', 'Ngô Tất Tố', 'Sai', 'Nguyễn Huy Tưởng', 'Sai', 'A'),
(28, 'Giữa kỳ', 'Văn', '\"Bình Ngô đại cáo\" do ai viết?', NULL, 'Nguyễn Du', 'Sai', 'Nguyễn Trãi', 'Đúng', 'Lê Lợi', 'Sai', 'Trần Quốc Tuấn', 'Sai', 'B'),
(29, 'Cuối kỳ', 'Văn', 'Thể thơ lục bát là?', NULL, '6-8 chữ', 'Đúng', '5 chữ', 'Sai', '7 chữ', 'Sai', '4 chữ', 'Sai', 'A'),
(30, 'Cuối kỳ', 'Văn', 'Tác phẩm \"Tắt đèn\" của?', NULL, 'Ngô Tất Tố', 'Đúng', 'Nam Cao', 'Sai', 'Tô Hoài', 'Sai', 'Vũ Trọng Phụng', 'Sai', 'A'),
(31, 'Cuối kỳ', 'Tiếng anh', 'Từ \"beautiful\" là loại từ gì?', NULL, 'Động từ', 'Sai', 'Tính từ', 'Đúng', 'Danh từ', 'Sai', 'Trạng từ', 'Sai', 'B'),
(32, 'Giữa kỳ', 'Tiếng anh', 'Quá khứ của \"go\"?', NULL, 'goed', 'Sai', 'gone', 'Sai', 'went', 'Đúng', 'goes', 'Sai', 'C'),
(33, 'Giữa kỳ', 'Tiếng anh', 'Số nhiều của \"child\"?', NULL, 'childs', 'Sai', 'children', 'Đúng', 'childes', 'Sai', 'childer', 'Sai', 'B'),
(34, 'Cuối kỳ', 'Tiếng anh', 'Which word is a noun?', NULL, 'run', 'Sai', 'quick', 'Sai', 'happiness', 'Đúng', 'sad', 'Sai', 'C'),
(35, 'Cuối kỳ', 'Tiếng anh', 'Tense of \"had eaten\"?', NULL, 'Present', 'Sai', 'Past Simple', 'Sai', 'Past Perfect', 'Đúng', 'Future', 'Sai', 'C'),
(36, 'Cuối kỳ', 'Tiếng anh', 'Antonym of \"happy\"?', NULL, 'joyful', 'Sai', 'sad', 'Đúng', 'glad', 'Sai', 'cheerful', 'Sai', 'B'),
(37, 'Giữa kỳ', 'Sinh hoc', 'Đơn vị cấu tạo cơ thể?', NULL, 'Tế bào', 'Đúng', 'Cơ quan', 'Sai', 'Hệ cơ quan', 'Sai', 'Tổ chức', 'Sai', 'A'),
(38, 'Giữa kỳ', 'Sinh hoc', 'DNA mang thông tin?', NULL, 'Di truyền', 'Đúng', 'Thức ăn', 'Sai', 'Hô hấp', 'Sai', 'Thị giác', 'Sai', 'A'),
(39, 'Giữa kỳ', 'Sinh hoc', 'Thực vật quang hợp nhờ?', NULL, 'Ti thể', 'Sai', 'Lục lạp', 'Đúng', 'Nhân', 'Sai', 'Không bào', 'Sai', 'B'),
(40, 'Cuối kỳ', 'Sinh hoc', 'Hệ tuần hoàn gồm?', NULL, 'Tim và mạch máu', 'Đúng', 'Não và tủy', 'Sai', 'Gan và thận', 'Sai', 'Xương và cơ', 'Sai', 'A'),
(41, 'Cuối kỳ', 'Sinh hoc', 'Máu vận chuyển gì?', NULL, 'Oxy và chất dinh dưỡng', 'Đúng', 'Điện', 'Sai', 'Khí CO2', 'Chỉ 1 phần', 'Sóng', 'Sai', 'A'),
(42, 'Cuối kỳ', 'Sinh hoc', 'Bộ gen người có?', NULL, '23 cặp NST', 'Đúng', '46 NST đơn lẻ', 'Cũng đúng', '24 cặp NST', 'Sai', '22 NST', 'Sai', 'A'),
(43, 'Giữa kỳ', 'Hoá học', 'H2O là công thức của?', NULL, 'Oxy', 'Sai', 'Nước', 'Đúng', 'Hydro', 'Sai', 'Không khí', 'Sai', 'B'),
(44, 'Giữa kỳ', 'Hoá học', 'pH < 7 là?', NULL, 'Trung tính', 'Sai', 'Axit', 'Đúng', 'Bazơ', 'Sai', 'Muối', 'Sai', 'B'),
(45, 'Giữa kỳ', 'Hoá học', 'NaCl là?', NULL, 'Axit', 'Sai', 'Muối', 'Đúng', 'Bazơ', 'Sai', 'Kim loại', 'Sai', 'B'),
(46, 'Cuối kỳ', 'Hoá học', 'Khi đốt Mg trong O2 tạo?', NULL, 'CO2', 'Sai', 'MgO', 'Đúng', 'H2O', 'Sai', 'NaCl', 'Sai', 'B'),
(47, 'Cuối kỳ', 'Hoá học', 'Nguyên tử có?', NULL, 'Proton, neutron, electron', 'Đúng', 'Chỉ electron', 'Sai', 'Chỉ proton', 'Sai', 'Chỉ neutron', 'Sai', 'A'),
(48, 'Cuối kỳ', 'Hoá học', 'Số hiệu nguyên tử H là?', NULL, '1', 'Đúng', '2', 'Sai', '0', 'Sai', '8', 'Sai', 'A');

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
(1, 1, 1, '1', '1', 'A', 'an1@gmail.com', '1,4'),
(2, 2, 2, '2', '2', 'B', 'an2@gmail.com', '10,3'),
(3, 3, 3, '3', '3', 'C', 'An3@gmail.com', '1,6,5,3');

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
(2, 8, 'Giữa kỳ', 2, '80', 0),
(12, 10, 'Giữa kỳ', 2, '80', 3),
(16, 3, 'Giữa kỳ', 2, '80', 0),
(19, 1, 'Cuối kỳ', 3, '80', 0),
(21, 6, 'Cuối kỳ', 3, '80', 0),
(22, 5, 'Giữa kỳ', 2, '100', 0),
(23, 4, 'Giữa kỳ', 3, '80', 0),
(29, 2, 'Giữa kỳ', 2, '100', 0);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `ket_qua`
--
ALTER TABLE `ket_qua`
  ADD PRIMARY KEY (`student_id`);

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
-- AUTO_INCREMENT cho bảng `ket_qua`
--
ALTER TABLE `ket_qua`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `test`
--
ALTER TABLE `test`
  MODIFY `id_test` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
