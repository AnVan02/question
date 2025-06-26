-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 21, 2025 lúc 10:40 AM
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
(8, 'Sinh hoc'),
(10, 'Hoá học');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `kiem_tra`
--

CREATE TABLE `kiem_tra` (
  `Student_ID` int(11) NOT NULL,
  `Khoa_ID` int(11) NOT NULL,
  `Test_ID` text NOT NULL,
  `Best_Scone` varchar(255) NOT NULL,
  `Max_Scone` varchar(255) NOT NULL,
  `Pass` varchar(255) NOT NULL,
  `Tral` varchar(255) NOT NULL,
  `Max_tral` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `kiem_tra`
--

INSERT INTO `kiem_tra` (`Student_ID`, `Khoa_ID`, `Test_ID`, `Best_Scone`, `Max_Scone`, `Pass`, `Tral`, `Max_tral`) VALUES
(1, 6, '11', '0', '0', '100', '0', '1'),
(1, 4, '37', '0', '0', '100', '0', '10'),
(2, 4, '37', '0', '0', '50', '0', '10');

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
(1, '1', '1'),
(2, '2', '2'),
(3, '3', '3'),
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
(3, 'Cuối kỳ', 'Yolo', 'Trong lập trình, \"đệ quy\" là gì?', NULL, 'Hàm gọi chính nó', 'Đệ quy là khi hàm tự gọi lại với tham số khác', 'Vòng lặp vô hạn', 'Sai: đệ quy không phải vòng lặp vô hạn', 'Hàm không trả về', 'Sai: đệ quy không liên quan đến việc không trả về', 'Gọi hàm khác', 'Sai: đệ quy là gọi chính hàm đó', 'A'),
(4, 'Cuối kỳ', 'Python cơ bản', 'Trong Java, từ khóa nào dùng để tạo đối tượng mới?', 'uploads/java_new.png', 'new', 'Từ khóa new cấp phát bộ nhớ cho đối tượng', 'create', 'Sai: Java không có từ khóa create', 'instance', 'Sai: instance không phải từ khóa', 'object', 'Sai: object không phải từ khóa tạo đối tượng', 'A'),
(5, 'Giữa kỳ', 'Yolo', 'Thuật toán tìm kiếm nào hiệu quả nhất cho mảng đã sắp xếp?', NULL, 'Binary Search', 'Binary Search có độ phức tạp O(log n) cho mảng đã sắp xếp', 'Linear Search', 'Linear Search có O(n), kém hiệu quả hơn', 'Bubble Search', 'Sai: không có thuật toán Bubble Search', 'Quick Search', 'Sai: Quick Search không tồn tại', 'A'),
(6, 'Giữa kỳ', 'Toán', 'Kết quả của phép tính 2^3 + 5 * 2 là bao nhiêu?', NULL, '18', '2^3 = 8, 5 * 2 = 10, tổng là 8 + 10 = 18', '13', 'Sai: ưu tiên lũy thừa trước, rồi nhân, rồi cộng', '10', 'Sai: nhầm lẫn thứ tự ưu tiên toán tử', '16', 'Sai: tính sai lũy thừa', 'A'),
(7, 'Giữa kỳ', 'Văn', 'Tác giả của tác phẩm \"Truyện Kiều\" là ai?', NULL, 'Nguyễn Du', 'Nguyễn Du là tác giả Truyện Kiều', 'Nguyễn Trãi', 'Sai: Nguyễn Trãi là tác giả Quốc âm thi tập', 'Hồ Xuân Hương', 'Sai: Hồ Xuân Hương nổi tiếng với thơ Nôm', 'Tố Hữu', 'Sai: Tố Hữu là nhà thơ cách mạng', 'A'),
(9, 'Cuối kỳ', 'Văn', 'Thể loại chính của \"Chí Phèo\" của Nam Cao là gì?', NULL, 'Truyện ngắn', 'Chí Phèo là truyện ngắn hiện thực phê phán', 'Tiểu thuyết', 'Sai: Chí Phèo không phải tiểu thuyết', 'Thơ', 'Sai: Chí Phèo không phải thơ', 'Kịch', 'Sai: Chí Phèo không phải kịch', 'A'),
(13, 'Cuối kỳ', 'Tiếng anh', 'What is the plural form of the word \"child\"?', NULL, 'children', 'The plural form of \"child\" is \"children\".', 'childs', 'The word \"childs\" is incorrect.', 'childes', 'The word \"childes\" is not a correct English word.', 'childrened', 'The word \"childrened\" is incorrect.', 'A'),
(14, 'Giữa kỳ', 'Tiếng anh', 'Which of the following is the correct form of the verb in the sentence: \"She ______ to the park every morning.\"?', NULL, 'goes', 'The correct form is \"goes\", as it agrees with the singular subject \"she\".', 'going', 'The word \"going\" is the present participle and does not fit in this sentence.', 'gone', 'The word \"gone\" is the past participle and does not fit in this sentence.', 'went', 'The word \"went\" is the past tense and does not fit in this sentence.', 'A'),
(15, 'Giữa kỳ', 'Hoá học', 'H20 có nghĩa là gì', NULL, 'Nước', 'H2O được cấu tạo từ nước', 'khí', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'oxi', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'nito', 'JavaScript chủ yếu chạy trên trình duyệt.', 'A'),
(17, 'Cuối kỳ', 'Hoá học', 'Công thức đường Glucose là gì', NULL, 'C6H12O12', 'đường glu là một loại monosaccharide', 'C6H12O6', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'C2H10O5', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'C12H23O8', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'A'),
(18, 'Giữa kỳ', 'Văn', 'Đề thi THPT 2020 là bài nào', 'images/681dd199800ea.png', 'Tây tiến', 'PHP là ngôn ngữ phía server phổ biến.', 'Đất nước', 'CSS dùng để định dạng giao diện', 'Sóng', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'Ai đã đặt ten cho dòng sống', 'JavaScript chủ yếu chạy trên trình duyệt', 'B'),
(19, 'Giữa kỳ', 'Python nâng cao', 'qqqqqqqqqqqqqq', NULL, 'PHP', 'PHP là ngôn ngữ phía server phổ biến.', 'CSS', 'CSS dùng để định dạng giao diện.', 'HTML', 'HTML là ngôn ngữ đánh dấu.', 'JavaScript', 'JavaScript chủ yếu chạy trên trình duyệt.', 'A'),
(20, 'Giữa kỳ', 'Python nâng cao', 'qqqqq', NULL, 'PHP', 'PHP là ngôn ngữ phía server phổ biến.', 'CSS', 'CSS dùng để định dạng giao diện', 'do-while', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'JavaScript', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'B'),
(21, 'Giữa kỳ', 'Python nâng cao', '22222', NULL, 'PHP', 'Trong JavaScript, typeof null trả về \\\'object\\\' do một lỗi lịch sử trong thiết kế ngôn ngữ.', 'CSS', 'CSS dùng để định dạng giao diện', 'HTML', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'd', 'JavaScript chủ yếu chạy trên trình duyệt', 'C'),
(22, 'Giữa kỳ', 'Python nâng cao', '2222222222', NULL, 'For', 'PHP là ngôn ngữ phía server phổ biến.', 'CSS', 'fdksghidfsi', 'HTML', 'echo dùng để xuất dữ liệu.', 'JavaScript', 'là 1 loại ngôn ngữ tieng anh', 'D'),
(23, 'Cuối kỳ', 'Python cơ bản', 'qqaa', NULL, 'PHP', 'PHP là ngôn ngữ phía server phổ biến.', 'CSS', 'CSS dùng để định dạng giao diện.', 'dhkhkdfhk', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'Xin chào', 'JavaScript chủ yếu chạy trên trình duyệt', 'B'),
(24, 'Cuối kỳ', 'Toán', 'Diện tích hình tròn có bán kính r = 3 là bao nhiêu? (π ≈ 3.14)', NULL, '28.26', 'Diện tích = π * r^2 = 3.14 * 3^2 = 28.26', '18.84', 'Sai: nhầm lẫn chu vi (2πr) với diện tích', '9', 'Sai: tính sai bình phương bán kính', '12.56', 'Sai: nhầm lẫn công thức', 'B'),
(25, 'Cuối kỳ', 'Toán', 'Diện tích hình tròn có bán kính r = 3 là bao nhiêu? (π ≈ 3.14)', NULL, '28.26', 'Diện tích = π * r^2 = 3.14 * 3^2 = 28.26', '18.84', 'Sai: nhầm lẫn chu vi (2πr) với diện tích', '9', 'Sai: tính sai bình phương bán kính', '12.56', 'Sai: nhầm lẫn công thức', 'A'),
(26, 'Cuối kỳ', 'Toán', 'Diện tích hình tròn có bán kính r = 3 là bao nhiêu? (π ≈ 3.14)', NULL, '28.26', 'Diện tích = π * r^2 = 3.14 * 3^2 = 28.26', '18.84', 'Sai: nhầm lẫn chu vi (2πr) với diện tích', '9', 'Sai: tính sai bình phương bán kính', '12.56', 'Sai: nhầm lẫn công thức', 'A'),
(27, 'Cuối kỳ', 'Toán', 'Diện tích hình tròn có bán kính r = 3 là bao nhiêu? (π ≈ 3.14)', NULL, '28.26', 'Diện tích = π * r^2 = 3.14 * 3^2 = 28.26', '18.84', 'Sai: nhầm lẫn chu vi (2πr) với diện tích', '9', 'Sai: tính sai bình phương bán kính', '12.56', 'Sai: nhầm lẫn công thức', 'A'),
(28, 'Cuối kỳ', 'Toán', 'Diện tích hình tròn có bán kính r = 3 là bao nhiêu? (π ≈ 3.14)', NULL, '28.26', 'Diện tích = π * r^2 = 3.14 * 3^2 = 28.26', '18.84', 'Sai: nhầm lẫn chu vi (2πr) với diện tích', '9', 'Sai: tính sai bình phương bán kính', '12.56', 'Sai: nhầm lẫn công thức', 'A');

-- --------------------------------------------------------

INSERT INTO `quiz` (`Id_cauhoi`, `id_baitest`, `ten_khoa`, `cauhoi`, `hinhanh`, `cau_a`, `giaithich_a`, `cau_b`, `giaithich_b`, `cau_c`, `giaithich_c`, `cau_d`, `giaithich_d`, `dap_an`) VALUES
-- Python cơ bản
(1, 'giua_ky', 'Python cơ bản', 'Câu lệnh in ra "Hello World" trong Python là?', NULL, 'print("Hello World")', 'Đúng', 'echo "Hello World"', 'Sai, dùng trong PHP', 'console.log("Hello World")', 'Sai, JS dùng', 'System.out.println("Hello World")', 'Sai, Java dùng', 'A'),
(2, 'giua_ky', 'Python cơ bản', 'Kiểu dữ liệu nào lưu trữ chuỗi?', NULL, 'int', 'Số nguyên', 'str', 'Đúng, là kiểu chuỗi', 'bool', 'Boolean', 'float', 'Số thực', 'B'),
(3, 'giua_ky', 'Python cơ bản', 'Vòng lặp nào lặp qua dãy số?', NULL, 'for', 'Đúng, dùng for range', 'if', 'Không phải vòng lặp', 'try', 'Khối xử lý lỗi', 'print', 'In ra', 'A'),
(4, 'cuoi_ky', 'Python cơ bản', 'Cách khai báo danh sách?', NULL, '[1,2,3]', 'Đúng, là list', '(1,2,3)', 'Tuple', '{1,2,3}', 'Set', '<1,2,3>', 'Không hợp lệ', 'A'),
(5, 'cuoi_ky', 'Python cơ bản', 'Toán tử so sánh bằng là?', NULL, '=', 'Gán giá trị', '==', 'Đúng', '!=', 'So sánh khác', '===', 'Không dùng trong Python', 'B'),
(6, 'cuoi_ky', 'Python cơ bản', 'Hàm tính độ dài list?', NULL, 'length()', 'Sai', 'len()', 'Đúng', 'count()', 'Sai', 'size()', 'Sai', 'B'),

-- Python nâng cao
(7, 'giua_ky', 'Python nâng cao', 'Lambda dùng để làm gì?', NULL, 'Định nghĩa hàm nhanh', 'Đúng', 'Tạo biến', 'Sai', 'Tạo class', 'Sai', 'Tạo loop', 'Sai', 'A'),
(8, 'giua_ky', 'Python nâng cao', 'Decorator được dùng để?', NULL, 'Trang trí UI', 'Sai', 'Chạy chương trình', 'Sai', 'Thêm chức năng cho hàm', 'Đúng', 'Gỡ lỗi', 'Sai', 'C'),
(9, 'giua_ky', 'Python nâng cao', 'Gói chuẩn để xử lý JSON?', NULL, 'os', 'Sai', 'sys', 'Sai', 'json', 'Đúng', 'math', 'Sai', 'C'),
(10, 'cuoi_ky', 'Python nâng cao', 'Generator là gì?', NULL, 'Hàm trả iterator', 'Đúng', 'List', 'Sai', 'Loop', 'Sai', 'Dict', 'Sai', 'A'),
(11, 'cuoi_ky', 'Python nâng cao', 'Từ khóa yield dùng trong?', NULL, 'Class', 'Sai', 'Loop', 'Sai', 'Generator', 'Đúng', 'Import', 'Sai', 'C'),
(12, 'cuoi_ky', 'Python nâng cao', 'Module nào làm việc với file hệ thống?', NULL, 'os', 'Đúng', 'json', 'Không đúng', 'sys', 'Sai', 'math', 'Sai', 'A'),

-- YOLO
(13, 'giua_ky', 'YOLO', 'YOLO là viết tắt của?', NULL, 'You Only Learn Once', 'Sai', 'You Only Look Once', 'Đúng', 'Your Only Logic Option', 'Sai', 'None of the above', 'Sai', 'B'),
(14, 'giua_ky', 'YOLO', 'YOLO dùng để?', NULL, 'Dịch ngôn ngữ', 'Sai', 'Xử lý ảnh', 'Đúng', 'Phân tích âm thanh', 'Sai', 'Tạo ảnh', 'Sai', 'B'),
(15, 'giua_ky', 'YOLO', 'YOLO thuộc nhóm?', NULL, 'Phân loại ảnh', 'Sai', 'Phát hiện vật thể', 'Đúng', 'Tăng cường học', 'Sai', 'LSTM', 'Sai', 'B'),
(16, 'cuoi_ky', 'YOLO', 'YOLO dựa vào?', NULL, 'CNN', 'Đúng', 'RNN', 'Sai', 'GAN', 'Sai', 'Transformer', 'Sai', 'A'),
(17, 'cuoi_ky', 'YOLO', 'YOLOv4 khác gì YOLOv3?', NULL, 'Nhanh hơn', 'Đúng', 'Chậm hơn', 'Sai', 'Không khác gì', 'Sai', 'Cũ hơn', 'Sai', 'A'),
(18, 'cuoi_ky', 'YOLO', 'Đầu ra của YOLO là?', NULL, 'Văn bản', 'Sai', 'Ảnh', 'Sai', 'Hộp giới hạn & nhãn', 'Đúng', 'Âm thanh', 'Sai', 'C'),

-- Toán
(19, 'giua_ky', 'Toán', 'Giá trị của π là?', NULL, '3.14', 'Gần đúng', '2.71', 'Sai', '1.61', 'Sai', '1.41', 'Sai', 'A'),
(20, 'giua_ky', 'Toán', 'Đạo hàm của x^2 là?', NULL, 'x', 'Sai', '2x', 'Đúng', 'x^2', 'Sai', '1', 'Sai', 'B'),
(21, 'giua_ky', 'Toán', 'Hàm số y = mx + b là dạng?', NULL, 'Bậc hai', 'Sai', 'Tuyến tính', 'Đúng', 'Hằng số', 'Sai', 'Lôgarit', 'Sai', 'B'),
(22, 'cuoi_ky', 'Toán', 'sin(90°) bằng?', NULL, '0', 'Sai', '1', 'Đúng', '0.5', 'Sai', '√2/2', 'Sai', 'B'),
(23, 'cuoi_ky', 'Toán', 'Căn bậc hai của 49?', NULL, '5', 'Sai', '6', 'Sai', '7', 'Đúng', '8', 'Sai', 'C'),
(24, 'cuoi_ky', 'Toán', 'log(100) cơ số 10?', NULL, '1', 'Sai', '2', 'Đúng', '10', 'Sai', '0', 'Sai', 'B'),

-- Văn
(25, 'giua_ky', 'Văn', 'Tác giả Truyện Kiều?', NULL, 'Nguyễn Du', 'Đúng', 'Nguyễn Trãi', 'Sai', 'Hồ Xuân Hương', 'Sai', 'Tố Hữu', 'Sai', 'A'),
(26, 'giua_ky', 'Văn', 'Phong cách thơ Xuân Quỳnh?', NULL, 'Lãng mạn', 'Đúng', 'Hiện thực', 'Sai', 'Trào phúng', 'Sai', 'Chính luận', 'Sai', 'A'),
(27, 'giua_ky', 'Văn', 'Truyện ngắn "Lão Hạc" của?', NULL, 'Nam Cao', 'Đúng', 'Kim Lân', 'Sai', 'Ngô Tất Tố', 'Sai', 'Nguyễn Huy Tưởng', 'Sai', 'A'),
(28, 'cuoi_ky', 'Văn', '"Bình Ngô đại cáo" do ai viết?', NULL, 'Nguyễn Du', 'Sai', 'Nguyễn Trãi', 'Đúng', 'Lê Lợi', 'Sai', 'Trần Quốc Tuấn', 'Sai', 'B'),
(29, 'cuoi_ky', 'Văn', 'Thể thơ lục bát là?', NULL, '6-8 chữ', 'Đúng', '5 chữ', 'Sai', '7 chữ', 'Sai', '4 chữ', 'Sai', 'A'),
(30, 'cuoi_ky', 'Văn', 'Tác phẩm "Tắt đèn" của?', NULL, 'Ngô Tất Tố', 'Đúng', 'Nam Cao', 'Sai', 'Tô Hoài', 'Sai', 'Vũ Trọng Phụng', 'Sai', 'A'),

-- Tiếng anh
(31, 'giua_ky', 'Tiếng anh', 'Từ "beautiful" là loại từ gì?', NULL, 'Động từ', 'Sai', 'Tính từ', 'Đúng', 'Danh từ', 'Sai', 'Trạng từ', 'Sai', 'B'),
(32, 'giua_ky', 'Tiếng anh', 'Quá khứ của "go"?', NULL, 'goed', 'Sai', 'gone', 'Sai', 'went', 'Đúng', 'goes', 'Sai', 'C'),
(33, 'giua_ky', 'Tiếng anh', 'Số nhiều của "child"?', NULL, 'childs', 'Sai', 'children', 'Đúng', 'childes', 'Sai', 'childer', 'Sai', 'B'),
(34, 'cuoi_ky', 'Tiếng anh', 'Which word is a noun?', NULL, 'run', 'Sai', 'quick', 'Sai', 'happiness', 'Đúng', 'sad', 'Sai', 'C'),
(35, 'cuoi_ky', 'Tiếng anh', 'Tense of "had eaten"?', NULL, 'Present', 'Sai', 'Past Simple', 'Sai', 'Past Perfect', 'Đúng', 'Future', 'Sai', 'C'),
(36, 'cuoi_ky', 'Tiếng anh', 'Antonym of "happy"?', NULL, 'joyful', 'Sai', 'sad', 'Đúng', 'glad', 'Sai', 'cheerful', 'Sai', 'B'),

-- Sinh học
(37, 'giua_ky', 'Sinh hoc', 'Đơn vị cấu tạo cơ thể?', NULL, 'Tế bào', 'Đúng', 'Cơ quan', 'Sai', 'Hệ cơ quan', 'Sai', 'Tổ chức', 'Sai', 'A'),
(38, 'giua_ky', 'Sinh hoc', 'DNA mang thông tin?', NULL, 'Di truyền', 'Đúng', 'Thức ăn', 'Sai', 'Hô hấp', 'Sai', 'Thị giác', 'Sai', 'A'),
(39, 'giua_ky', 'Sinh hoc', 'Thực vật quang hợp nhờ?', NULL, 'Ti thể', 'Sai', 'Lục lạp', 'Đúng', 'Nhân', 'Sai', 'Không bào', 'Sai', 'B'),
(40, 'cuoi_ky', 'Sinh hoc', 'Hệ tuần hoàn gồm?', NULL, 'Tim và mạch máu', 'Đúng', 'Não và tủy', 'Sai', 'Gan và thận', 'Sai', 'Xương và cơ', 'Sai', 'A'),
(41, 'cuoi_ky', 'Sinh hoc', 'Máu vận chuyển gì?', NULL, 'Oxy và chất dinh dưỡng', 'Đúng', 'Điện', 'Sai', 'Khí CO2', 'Chỉ 1 phần', 'Sóng', 'Sai', 'A'),
(42, 'cuoi_ky', 'Sinh hoc', 'Bộ gen người có?', NULL, '23 cặp NST', 'Đúng', '46 NST đơn lẻ', 'Cũng đúng', '24 cặp NST', 'Sai', '22 NST', 'Sai', 'A'),

-- Hóa học
(43, 'giua_ky', 'Hoá học', 'H2O là công thức của?', NULL, 'Oxy', 'Sai', 'Nước', 'Đúng', 'Hydro', 'Sai', 'Không khí', 'Sai', 'B'),
(44, 'giua_ky', 'Hoá học', 'pH < 7 là?', NULL, 'Trung tính', 'Sai', 'Axit', 'Đúng', 'Bazơ', 'Sai', 'Muối', 'Sai', 'B'),
(45, 'giua_ky', 'Hoá học', 'NaCl là?', NULL, 'Axit', 'Sai', 'Muối', 'Đúng', 'Bazơ', 'Sai', 'Kim loại', 'Sai', 'B'),
(46, 'cuoi_ky', 'Hoá học', 'Khi đốt Mg trong O2 tạo?', NULL, 'CO2', 'Sai', 'MgO', 'Đúng', 'H2O', 'Sai', 'NaCl', 'Sai', 'B'),
(47, 'cuoi_ky', 'Hoá học', 'Nguyên tử có?', NULL, 'Proton, neutron, electron', 'Đúng', 'Chỉ electron', 'Sai', 'Chỉ proton', 'Sai', 'Chỉ neutron', 'Sai', 'A'),
(48, 'cuoi_ky', 'Hoá học', 'Số hiệu nguyên tử H là?', NULL, '1', 'Đúng', '2', 'Sai', '0', 'Sai', '8', 'Sai', 'A');


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
  `Khoahoc` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `students`
--

INSERT INTO `students` (`IMEI`, `MB_ID`, `OS_ID`, `Student_ID`, `Password`, `Ten`, `Email`, `Khoahoc`) VALUES
(1, 1, 1, '1', '1', 'An', 'an1@gmail.com', '6,4'),
(2, 2, 2, '2', '2', 'Hoài An', 'an2@gmail.com', '4');

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
(11, 6, 'Giưa ky', 1, '100', 0),
(16, 3, 'Giưa ky', 2, '80', 0),
(18, 2, 'Cuối kỳ', 2, '80', 0),
(19, 1, 'Cuối kỳ', 1, '80', 0),
(21, 6, 'Cuối kỳ', 1, '80', 0),
(22, 5, 'Giữa ky', 1, '100', 0),
(23, 5, 'Cuối kỳ', 1, '80', 0),
(24, 10, 'Cuối kỳ', 5, '100', 0),
(29, 2, 'Giữa kỳ', 2, '100', 0),
(31, 3, 'Cuối kỳ', 1, '100', 0),
(36, 10, 'Giữa kỳ', 2, '5', 0),
(37, 4, 'Cuối kỳ', 10, '50', 5);

--
-- Chỉ mục cho các bảng đã đổ
--

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
  ADD PRIMARY KEY (`Student_ID`),
  ADD UNIQUE KEY `IMEI` (`IMEI`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `MB_ID` (`MB_ID`),
  ADD KEY `OS_ID` (`OS_ID`);

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
-- AUTO_INCREMENT cho bảng `login`
--
ALTER TABLE `login`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `quiz`
--
ALTER TABLE `quiz`
  MODIFY `Id_cauhoi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT cho bảng `test`
--
ALTER TABLE `test`
  MODIFY `id_test` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
