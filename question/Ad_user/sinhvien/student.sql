-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th7 07, 2025 lúc 06:31 PM
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
  `test_id` varchar(255) NOT NULL,
  `kq_cao_nhat` int(11) NOT NULL,
  `tt_bai_test` text DEFAULT NULL COMMENT 'Lưu dạng JSON hoặc format thống nhất'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `ket_qua`
--

INSERT INTO `ket_qua` (`student_id`, `khoa_id`, `test_id`, `kq_cao_nhat`, `tt_bai_test`) VALUES
(1, 1, '19', 1, '5:B;6:C'),
(1, 10, '12', 0, '43:B;45:B'),
(4, 1, '19', 1, '1:A;4:C');

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
(4, 1, '19', 0, 0, '80', 0, 3);

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
(1, 'Giữa kỳ', 'Python cơ bản', 'Câu lệnh in ra \"Hello World\" trong Python là?', NULL, 'print(\"Hello World\")', NULL, 'Đúng', 'echo \"Hello World\"', NULL, 'Sai, dùng trong PHP', 'console.log(\"Hello World\")', NULL, 'Sai, JS dùng', 'System.out.println(\"Hello World\")', NULL, 'Sai, Java dùng', 'A'),
(4, 'Giữa kỳ', 'Python cơ bản', 'Cách khai báo danh sách?', NULL, '[1,2,3]', NULL, 'Đúng, là list', '(1,2,3)', NULL, 'Tuple', '{1,2,3}', NULL, 'Set', '<1,2,3>', NULL, 'Không hợp lệ', 'A'),
(5, 'Cuối kỳ', 'Python cơ bản', 'Toán tử so sánh bằng là?', NULL, '=', NULL, 'Gán giá trị', '==', NULL, 'Đúng', '!=', NULL, 'So sánh khác', '===', NULL, 'Không dùng trong Python', 'B'),
(6, 'Cuối kỳ', 'Python cơ bản', 'Hàm tính độ dài list?', NULL, 'length()', NULL, 'Sai', 'len()', NULL, 'Đúng', 'count()', NULL, 'Sai', 'size()', NULL, 'Sai', 'B'),
(7, 'Giữa kỳ', 'Python nâng cao', 'Lambda dùng để làm gì?', NULL, 'Định nghĩa hàm nhanh', NULL, 'Đúng', 'Tạo biến', NULL, 'Sai', 'Tạo class', NULL, 'Sai', 'Tạo loop', NULL, 'Sai', 'A'),
(8, 'Giữa kỳ', 'Python nâng cao', 'Decorator được dùng để?', NULL, 'Trang trí UI', NULL, 'Sai', 'Chạy chương trình', NULL, 'Sai', 'Thêm chức năng cho hàm', NULL, 'Đúng', 'Gỡ lỗi', NULL, 'Sai', 'C'),
(9, 'Giữa kỳ', 'Python nâng cao', 'Gói chuẩn để xử lý JSON?', NULL, 'os', NULL, 'Sai', 'sys', NULL, 'Sai', 'json', NULL, 'Đúng', 'math', NULL, 'Sai', 'C'),
(10, 'Cuối kỳ', 'Python nâng cao', 'Generator là gì?', NULL, 'Hàm trả iterator', NULL, 'Đúng', 'List', NULL, 'Sai', 'Loop', NULL, 'Sai', 'Dict', NULL, 'Sai', 'A'),
(11, 'Cuối kỳ', 'Python nâng cao', 'Từ khóa yield dùng trong?', NULL, 'Class', NULL, 'Sai', 'Loop', NULL, 'Sai', 'Generator', NULL, 'Đúng', 'Import', NULL, 'Sai', 'C'),
(12, 'Cuối kỳ', 'Python nâng cao', 'Module nào làm việc với file hệ thống?', NULL, 'os', NULL, 'Đúng', 'json', NULL, 'Không đúng', 'sys', NULL, 'Sai', 'math', NULL, 'Sai', 'A'),
(13, 'Giữa kỳ', 'YOLO', 'YOLO là viết tắt của?', NULL, 'You Only Learn Once', NULL, 'Sai', 'You Only Look Once', NULL, 'Đúng', 'Your Only Logic Option', NULL, 'Sai', 'None of the above', NULL, 'Sai', 'B'),
(14, 'Giữa kỳ', 'YOLO', 'YOLO dùng để?', NULL, 'Dịch ngôn ngữ', NULL, 'Sai', 'Xử lý ảnh', NULL, 'Đúng', 'Phân tích âm thanh', NULL, 'Sai', 'Tạo ảnh', NULL, 'Sai', 'B'),
(15, 'Giữa kỳ', 'YOLO', 'YOLO thuộc nhóm?', NULL, 'Phân loại ảnh', NULL, 'Sai', 'Phát hiện vật thể', NULL, 'Đúng', 'Tăng cường học', NULL, 'Sai', 'LSTM', NULL, 'Sai', 'B'),
(16, 'Cuối kỳ', 'YOLO', 'YOLO dựa vào?', NULL, 'CNN', NULL, 'Đúng', 'RNN', NULL, 'Sai', 'GAN', NULL, 'Sai', 'Transformer', NULL, 'Sai', 'A'),
(17, 'Cuối kỳ', 'YOLO', 'YOLOv4 khác gì YOLOv3?', NULL, 'Nhanh hơn', NULL, 'Đúng', 'Chậm hơn', NULL, 'Sai', 'Không khác gì', NULL, 'Sai', 'Cũ hơn', NULL, 'Sai', 'A'),
(18, 'Cuối kỳ', 'YOLO', 'Đầu ra của YOLO là?', NULL, 'Văn bản', NULL, 'Sai', 'Ảnh', NULL, 'Sai', 'Hộp giới hạn & nhãn', NULL, 'Đúng', 'Âm thanh', NULL, 'Sai', 'C'),
(19, 'Cuối kỳ', 'Toán', 'Giá trị của π là?', NULL, '3.14', NULL, 'Gần đúng', '2.71', NULL, 'Sai', '1.61', NULL, 'Sai', '1.41', NULL, 'Sai', 'A'),
(20, 'Giữa kỳ', 'Toán', 'Đạo hàm của x^2 là?', NULL, 'x', NULL, 'Sai', '2x', NULL, 'Đúng', 'x^2', NULL, 'Sai', '1', NULL, 'Sai', 'B'),
(21, 'Giữa kỳ', 'Toán', 'Hàm số y = mx + b là dạng?', NULL, 'Bậc hai', NULL, 'Sai', 'Tuyến tính', NULL, 'Đúng', 'Hằng số', NULL, 'Sai', 'Lôgarit', NULL, 'Sai', 'B'),
(22, 'Giữa kỳ', 'Toán', 'sin(90°) bằng?', NULL, '0', NULL, 'Sai', '1', NULL, 'Đúng', '0.5', NULL, 'Sai', '√2/2', NULL, 'Sai', 'B'),
(23, 'Giữa kỳ', 'Toán', 'Căn bậc hai của 49?', NULL, '5', NULL, 'Sai', '6', NULL, 'Sai', '7', NULL, 'Đúng', '8', NULL, 'Sai', 'C'),
(24, 'Giữa kỳ', 'Toán', 'log(100) cơ số 10?', NULL, '1', NULL, 'Sai', '2', NULL, 'Đúng', '10', NULL, 'Sai', '0', NULL, 'Sai', 'B'),
(25, 'Giữa kỳ', 'Văn', 'Tác giả Truyện Kiều?', NULL, 'Nguyễn Du', NULL, 'Đúng', 'Nguyễn Trãi', NULL, 'Sai', 'Hồ Xuân Hương', NULL, 'Sai', 'Tố Hữu', NULL, 'Sai', 'A'),
(26, 'Giữa kỳ', 'Văn', 'Phong cách thơ Xuân Quỳnh?', NULL, 'Lãng mạn', NULL, 'Đúng', 'Hiện thực', NULL, 'Sai', 'Trào phúng', NULL, 'Sai', 'Chính luận', NULL, 'Sai', 'A'),
(27, 'Giữa kỳ', 'Văn', 'Truyện ngắn \"Lão Hạc\" của?', NULL, 'Nam Cao', NULL, 'Đúng', 'Kim Lân', NULL, 'Sai', 'Ngô Tất Tố', NULL, 'Sai', 'Nguyễn Huy Tưởng', NULL, 'Sai', 'A'),
(28, 'Giữa kỳ', 'Văn', '\"Bình Ngô đại cáo\" do ai viết?', NULL, 'Nguyễn Du', NULL, 'Sai', 'Nguyễn Trãi', NULL, 'Đúng', 'Lê Lợi', NULL, 'Sai', 'Trần Quốc Tuấn', NULL, 'Sai', 'B'),
(29, 'Cuối kỳ', 'Văn', 'Thể thơ lục bát là?', NULL, '6-8 chữ', NULL, 'Đúng', '5 chữ', NULL, 'Sai', '7 chữ', NULL, 'Sai', '4 chữ', NULL, 'Sai', 'A'),
(30, 'Cuối kỳ', 'Văn', 'Tác phẩm \"Tắt đèn\" của?', NULL, 'Ngô Tất Tố', NULL, 'Đúng', 'Nam Cao', NULL, 'Sai', 'Tô Hoài', NULL, 'Sai', 'Vũ Trọng Phụng', NULL, 'Sai', 'A'),
(31, 'Cuối kỳ', 'Tiếng anh', 'Từ \"beautiful\" là loại từ gì?', NULL, 'Động từ', NULL, 'Sai', 'Tính từ', NULL, 'Đúng', 'Danh từ', NULL, 'Sai', 'Trạng từ', NULL, 'Sai', 'B'),
(32, 'Giữa kỳ', 'Tiếng anh', 'Quá khứ của \"go\"?', NULL, 'goed', NULL, 'Sai', 'gone', NULL, 'Sai', 'went', NULL, 'Đúng', 'goes', NULL, 'Sai', 'C'),
(33, 'Giữa kỳ', 'Tiếng anh', 'Số nhiều của \"child\"?', NULL, 'childs', NULL, 'Sai', 'children', NULL, 'Đúng', 'childes', NULL, 'Sai', 'childer', NULL, 'Sai', 'B'),
(34, 'Cuối kỳ', 'Tiếng anh', 'Which word is a noun?', NULL, 'run', NULL, 'Sai', 'quick', NULL, 'Sai', 'happiness', NULL, 'Đúng', 'sad', NULL, 'Sai', 'C'),
(35, 'Cuối kỳ', 'Tiếng anh', 'Tense of \"had eaten\"?', NULL, 'Present', NULL, 'Sai', 'Past Simple', NULL, 'Sai', 'Past Perfect', NULL, 'Đúng', 'Future', NULL, 'Sai', 'C'),
(36, 'Cuối kỳ', 'Tiếng anh', 'Antonym of \"happy\"?', NULL, 'joyful', NULL, 'Sai', 'sad', NULL, 'Đúng', 'glad', NULL, 'Sai', 'cheerful', NULL, 'Sai', 'B'),
(37, 'Giữa kỳ', 'Sinh hoc', 'Đơn vị cấu tạo cơ thể?', NULL, 'Tế bào', NULL, 'Đúng', 'Cơ quan', NULL, 'Sai', 'Hệ cơ quan', NULL, 'Sai', 'Tổ chức', NULL, 'Sai', 'A'),
(38, 'Giữa kỳ', 'Sinh hoc', 'DNA mang thông tin?', NULL, 'Di truyền', NULL, 'Đúng', 'Thức ăn', NULL, 'Sai', 'Hô hấp', NULL, 'Sai', 'Thị giác', NULL, 'Sai', 'A'),
(39, 'Giữa kỳ', 'Sinh hoc', 'Thực vật quang hợp nhờ?', NULL, 'Ti thể', NULL, 'Sai', 'Lục lạp', NULL, 'Đúng', 'Nhân', NULL, 'Sai', 'Không bào', NULL, 'Sai', 'B'),
(40, 'Cuối kỳ', 'Sinh hoc', 'Hệ tuần hoàn gồm?', NULL, 'Tim và mạch máu', NULL, 'Đúng', 'Não và tủy', NULL, 'Sai', 'Gan và thận', NULL, 'Sai', 'Xương và cơ', NULL, 'Sai', 'A'),
(41, 'Cuối kỳ', 'Sinh hoc', 'Máu vận chuyển gì?', NULL, 'Oxy và chất dinh dưỡng', NULL, 'Đúng', 'Điện', NULL, 'Sai', 'Khí CO2', NULL, 'Chỉ 1 phần', 'Sóng', NULL, 'Sai', 'A'),
(42, 'Cuối kỳ', 'Sinh hoc', 'Bộ gen người có?', NULL, '23 cặp NST', NULL, 'Đúng', '46 NST đơn lẻ', NULL, 'Cũng đúng', '24 cặp NST', NULL, 'Sai', '22 NST', NULL, 'Sai', 'A'),
(43, 'Giữa kỳ', 'Hoá học', 'H2O là công thức của?', NULL, 'Oxy', NULL, 'Sai', 'Nước', NULL, 'Đúng', 'Hydro', NULL, 'Sai', 'Không khí', NULL, 'Sai', 'B'),
(45, 'Giữa kỳ', 'Hoá học', 'NaCl là?', NULL, 'Axit', NULL, 'Sai', 'Muối', NULL, 'Đúng', 'Bazơ', NULL, 'Sai', 'Kim loại', NULL, 'Sai', 'B'),
(46, 'Cuối kỳ', 'Hoá học', 'Khi đốt Mg trong O2 tạo?', NULL, 'CO2', NULL, 'Sai', 'MgO', NULL, 'Đúng', 'H2O', NULL, 'Sai', 'NaCl', NULL, 'Sai', 'B'),
(47, 'Cuối kỳ', 'Hoá học', 'Nguyên tử có?', NULL, 'Proton, neutron, electron', NULL, 'Đúng', 'Chỉ electron', NULL, 'Sai', 'Chỉ proton', NULL, 'Sai', 'Chỉ neutron', NULL, 'Sai', 'A'),
(48, 'Cuối kỳ', 'Hoá học', 'Số hiệu nguyên tử H là?', NULL, '1', NULL, 'Đúng', '2', NULL, 'Sai', '0', NULL, 'Sai', '8', NULL, 'Sai', 'A'),
(49, 'Giữa kỳ', 'Python cơ bản', 'ssssssssss', NULL, 'wwwwwww', 'images/a_686befe1d6f33.png', 'saaaaaaaaaaa', 'saaaaaaa', 'images/b_686befe1d74fd.png', 'saaaaaaa', 'saaaaaa', 'images/c_686befe1d7ae5.png', 'ssssssssss', 'saaaaa', 'images/d_686befe1d8047.png', 'saaaaaa', 'A');

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
(21, 6, 'Cuối kỳ', 3, '80', 0),
(22, 5, 'Giữa kỳ', 2, '100', 0),
(23, 4, 'Giữa kỳ', 3, '80', 0),
(29, 2, 'Giữa kỳ', 2, '100', 0),
(39, 8, 'Cuối kỳ', 1, '100', 0),
(40, 8, 'Cuối kỳ', 1, '100', 0),
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
-- AUTO_INCREMENT cho bảng `test`
--
ALTER TABLE `test`
  MODIFY `id_test` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
