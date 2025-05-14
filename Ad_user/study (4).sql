-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 14, 2025 lúc 08:20 PM
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
(3, 'YOLO'),
(4, 'Toán'),
(5, 'Văn'),
(6, 'Tiếng anh'),
(10, 'Hoá học');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `kiem_tra`
--

CREATE TABLE `kiem_tra` (
  `Studen_ID` int(11) NOT NULL,
  `Khoa_ID` int(11) NOT NULL,
  `Test_ID` int(11) NOT NULL,
  `Best_Scone` varchar(255) NOT NULL,
  `Max_Scone` varchar(255) NOT NULL,
  `Pass` varchar(255) NOT NULL,
  `Tral` varchar(255) NOT NULL,
  `Max_tral` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `kiem_tra`
--

INSERT INTO `kiem_tra` (`Studen_ID`, `Khoa_ID`, `Test_ID`, `Best_Scone`, `Max_Scone`, `Pass`, `Tral`, `Max_tral`) VALUES
(2, 6, 1, '0', '0', '0', '0', '0');

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
(1, 'Giữa kỳ', 'Python nâng cao', 'Trong JavaScript, phương thức nào dùng để thêm phần tử vào cuối mảng?', NULL, 'push()', 'Phương thức push() thêm phần tử vào cuối mảng', 'pop()', 'pop() xóa phần tử cuối mảng', 'shift()', 'shift() xóa phần tử đầu mảng', 'unshift()', 'unshift() thêm phần tử vào đầu mảng', 'A'),
(2, 'Cuối kỳ', 'Python nâng cao', 'Kết quả của đoạn code C sau là gì? ```int main() { int x = 3; printf(\"%d\", x++); return 0; }```', 'uploads/c_code.png', '3', 'x++ tăng x sau khi in, nên in 3', '4', 'Sai: x++ tăng sau khi in, không phải ++x', '0', 'Sai: code không trả về 0', 'Lỗi cú pháp', 'Code không có lỗi cú pháp', 'A'),
(3, 'Giữa kỳ', 'Yolo', 'Trong lập trình, \"đệ quy\" là gì?', NULL, 'Hàm gọi chính nó', 'Đệ quy là khi hàm tự gọi lại với tham số khác', 'Vòng lặp vô hạn', 'Sai: đệ quy không phải vòng lặp vô hạn', 'Hàm không trả về', 'Sai: đệ quy không liên quan đến việc không trả về', 'Gọi hàm khác', 'Sai: đệ quy là gọi chính hàm đó', 'A'),
(4, 'Cuối kỳ', 'Python cơ bản', 'Trong Java, từ khóa nào dùng để tạo đối tượng mới?', 'uploads/java_new.png', 'new', 'Từ khóa new cấp phát bộ nhớ cho đối tượng', 'create', 'Sai: Java không có từ khóa create', 'instance', 'Sai: instance không phải từ khóa', 'object', 'Sai: object không phải từ khóa tạo đối tượng', 'A'),
(5, 'Giữa kỳ', 'Yolo', 'Thuật toán tìm kiếm nào hiệu quả nhất cho mảng đã sắp xếp?', NULL, 'Binary Search', 'Binary Search có độ phức tạp O(log n) cho mảng đã sắp xếp', 'Linear Search', 'Linear Search có O(n), kém hiệu quả hơn', 'Bubble Search', 'Sai: không có thuật toán Bubble Search', 'Quick Search', 'Sai: Quick Search không tồn tại', 'A'),
(6, 'Giữa kỳ', 'Toán', 'Kết quả của phép tính 2^3 + 5 * 2 là bao nhiêu?', NULL, '18', '2^3 = 8, 5 * 2 = 10, tổng là 8 + 10 = 18', '13', 'Sai: ưu tiên lũy thừa trước, rồi nhân, rồi cộng', '10', 'Sai: nhầm lẫn thứ tự ưu tiên toán tử', '16', 'Sai: tính sai lũy thừa', 'A'),
(7, 'Giữa kỳ', 'Văn', 'Tác giả của tác phẩm \"Truyện Kiều\" là ai?', NULL, 'Nguyễn Du', 'Nguyễn Du là tác giả Truyện Kiều', 'Nguyễn Trãi', 'Sai: Nguyễn Trãi là tác giả Quốc âm thi tập', 'Hồ Xuân Hương', 'Sai: Hồ Xuân Hương nổi tiếng với thơ Nôm', 'Tố Hữu', 'Sai: Tố Hữu là nhà thơ cách mạng', 'A'),
(9, 'Cuối kỳ', 'Văn', 'Thể loại chính của \"Chí Phèo\" của Nam Cao là gì?', NULL, 'Truyện ngắn', 'Chí Phèo là truyện ngắn hiện thực phê phán', 'Tiểu thuyết', 'Sai: Chí Phèo không phải tiểu thuyết', 'Thơ', 'Sai: Chí Phèo không phải thơ', 'Kịch', 'Sai: Chí Phèo không phải kịch', 'A'),
(10, 'Cuối kỳ', 'Toán', 'Diện tích hình tròn có bán kính r = 3 là bao nhiêu? (π ≈ 3.14)', NULL, '28.26', 'Diện tích = π * r^2 = 3.14 * 3^2 = 28.26', '18.84', 'Sai: nhầm lẫn chu vi (2πr) với diện tích', '9', 'Sai: tính sai bình phương bán kính', '12.56', 'Sai: nhầm lẫn công thức', 'A'),
(13, 'Cuối kỳ', 'Tiếng anh', 'What is the plural form of the word \"child\"?', NULL, 'children', 'The plural form of \"child\" is \"children\".', 'childs', 'The word \"childs\" is incorrect.', 'childes', 'The word \"childes\" is not a correct English word.', 'childrened', 'The word \"childrened\" is incorrect.', 'A'),
(14, 'Giữa kỳ', 'Tiếng anh', 'Which of the following is the correct form of the verb in the sentence: \"She ______ to the park every morning.\"?', NULL, 'goes', 'The correct form is \"goes\", as it agrees with the singular subject \"she\".', 'going', 'The word \"going\" is the present participle and does not fit in this sentence.', 'gone', 'The word \"gone\" is the past participle and does not fit in this sentence.', 'went', 'The word \"went\" is the past tense and does not fit in this sentence.', 'A'),
(15, 'Giữa kỳ', 'Hoá học', 'H20 có nghĩa là gì', NULL, 'Nước', 'H2O được cấu tạo từ nước', 'khí', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'oxi', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'nito', 'JavaScript chủ yếu chạy trên trình duyệt.', 'A'),
(17, 'Cuối kỳ', 'Hoá học', 'Công thức đường Glucose là gì', NULL, 'C6H12O12', 'đường glu là một loại monosaccharide', 'C6H12O6', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'C2H10O5', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'C12H23O8', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'A'),
(18, 'Giữa kỳ', 'Văn', 'Đề thi THPT 2020 là bài nào', 'images/681dd199800ea.png', 'Tây tiến', 'PHP là ngôn ngữ phía server phổ biến.', 'Đất nước', 'CSS dùng để định dạng giao diện', 'Sóng', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'Ai đã đặt ten cho dòng sống', 'JavaScript chủ yếu chạy trên trình duyệt', 'B'),
(19, 'Giữa kỳ', 'Python nâng cao', 'qqqqqqqqqqqqqq', NULL, 'PHP', 'PHP là ngôn ngữ phía server phổ biến.', 'CSS', 'CSS dùng để định dạng giao diện.', 'HTML', 'HTML là ngôn ngữ đánh dấu.', 'JavaScript', 'JavaScript chủ yếu chạy trên trình duyệt.', 'A'),
(20, 'Giữa kỳ', 'Python nâng cao', 'qqqqq', NULL, 'PHP', 'PHP là ngôn ngữ phía server phổ biến.', 'CSS', 'CSS dùng để định dạng giao diện', 'do-while', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'JavaScript', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'B'),
(21, 'Giữa kỳ', 'Python nâng cao', '22222', NULL, 'PHP', 'Trong JavaScript, typeof null trả về \\\'object\\\' do một lỗi lịch sử trong thiết kế ngôn ngữ.', 'CSS', 'CSS dùng để định dạng giao diện', 'HTML', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'd', 'JavaScript chủ yếu chạy trên trình duyệt', 'C'),
(22, 'Giữa kỳ', 'Python nâng cao', '2222222222', NULL, 'For', 'PHP là ngôn ngữ phía server phổ biến.', 'CSS', 'fdksghidfsi', 'HTML', 'echo dùng để xuất dữ liệu.', 'JavaScript', 'là 1 loại ngôn ngữ tieng anh', 'D'),
(23, 'Cuối kỳ', 'Python cơ bản', 'qqaa', NULL, 'PHP', 'PHP là ngôn ngữ phía server phổ biến.', 'CSS', 'CSS dùng để định dạng giao diện.', 'dhkhkdfhk', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'Xin chào', 'JavaScript chủ yếu chạy trên trình duyệt', 'B');

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
  `khoahoc` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `students`
--

INSERT INTO `students` (`IMEI`, `MB_ID`, `OS_ID`, `Student_ID`, `Password`, `Ten`, `Email`, `khoahoc`) VALUES
(2, 2, 2, '2', '2', 'AN', 'admin1@gmail.com', '6');

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
(10, 1, 'Giưa ky', 1),
(11, 6, 'Giưa ky', 1),
(12, 4, 'cuôi ky', 5),
(16, 3, 'Giưa ky', 2),
(18, 2, 'Cuối kỳ', 2),
(19, 1, 'Cuối kỳ', 1),
(20, 4, 'Giữa kỳ', 1),
(21, 6, 'Cuối kỳ', 1),
(22, 5, 'Giữa ky', 1),
(23, 5, 'Cuối kỳ', 1),
(24, 10, 'Cuối kỳ', 5),
(29, 2, 'Giữa kỳ', 2);

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
  ADD PRIMARY KEY (`id_test`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `khoa_hoc`
--
ALTER TABLE `khoa_hoc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `quiz`
--
ALTER TABLE `quiz`
  MODIFY `Id_cauhoi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT cho bảng `test`
--
ALTER TABLE `test`
  MODIFY `id_test` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
