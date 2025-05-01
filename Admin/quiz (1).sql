-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 29, 2025 lúc 12:53 PM
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


-- Tạo bảng quiz
CREATE TABLE `quiz` (
  `Id_cauhoi` INT(255) NOT NULL AUTO_INCREMENT,
  `id_baitest` VARCHAR(255) NOT NULL,
  `cauhoi` VARCHAR(255) NOT NULL,
  `hinhanh` VARCHAR(255) DEFAULT NULL,
  `cau_a` VARCHAR(255) NOT NULL,
  `giaithich_a` VARCHAR(250) NOT NULL,
  `cau_b` VARCHAR(255) NOT NULL,
  `giaithich_b` VARCHAR(255) NOT NULL,
  `cau_c` VARCHAR(255) NOT NULL,
  `giaithich_c` VARCHAR(255) NOT NULL,
  `cau_d` VARCHAR(255) NOT NULL,
  `giaithich_d` VARCHAR(255) NOT NULL,
  `dap_an` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`Id_cauhoi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Chèn 10 câu hỏi
INSERT INTO quiz (id_baitest, cauhoi, hinhanh, cau_a, giaithich_a, cau_b, giaithich_b, cau_c, giaithich_c, cau_d, giaithich_d, dap_an) VALUES
-- 4 câu hỏi giữa kỳ (GIUA_KY)
('GIUA_KY', 'Ngôn ngữ lập trình nào được sử dụng chủ yếu cho phát triển web phía server?', NULL, 'PHP', 'PHP là ngôn ngữ phía server phổ biến.', 'CSS', 'CSS dùng để định dạng giao diện.', 'HTML', 'HTML là ngôn ngữ đánh dấu.', 'JavaScript', 'JavaScript chủ yếu chạy trên trình duyệt.', 'A'),
('GIUA_KY', 'Câu lệnh nào dùng để lặp trong PHP?', NULL, 'for', 'Câu lệnh for dùng để lặp với số lần xác định.', 'if', 'if dùng để kiểm tra điều kiện.', 'echo', 'echo dùng để xuất dữ liệu.', 'switch', 'switch dùng để chọn nhiều trường hợp.', 'A'),
('GIUA_KY', 'Hàm nào dùng để kết nối cơ sở dữ liệu MySQL trong PHP?', NULL, 'mysqli_connect', 'Hàm này tạo kết nối đến MySQL.', 'mysql_connect', 'Hàm cũ, không khuyến khích dùng.', 'connect_db', 'Không phải hàm có sẵn.', 'db_open', 'Không tồn tại trong PHP.', 'A'),
('GIUA_KY', 'Thẻ HTML nào dùng để tạo tiêu đề cấp 1?', NULL, '<h1>', 'Thẻ <h1> tạo tiêu đề cấp 1.', '<title>', '<title> dùng trong <head>.', '<header>', '<header> là thẻ ngữ nghĩa.', '<p>', '<p> dùng cho đoạn văn.', 'A'),
-- 6 câu hỏi cuối kỳ (CUOI_KY)
('CUOI_KY', 'Phương thức nào dùng để gửi dữ liệu form an toàn hơn trong HTML?', NULL, 'POST', 'POST gửi dữ liệu qua body, an toàn hơn.', 'GET', 'GET gửi dữ liệu qua URL, dễ bị lộ.', 'PUT', 'PUT dùng trong API, không phải form HTML.', 'DELETE', 'DELETE dùng trong API, không phải form HTML.', 'A'),
('CUOI_KY', 'Trong PHP, biến toàn cục được khai báo bằng từ khóa nào?', NULL, 'global', 'Từ khóa global dùng để truy cập biến toàn cục.', 'static', 'static giữ giá trị giữa các lần gọi hàm.', 'const', 'const khai báo hằng số.', 'var', 'var không dùng để khai báo biến toàn cục.', 'A'),
('CUOI_KY', 'Hàm nào dùng để mã hóa mật khẩu trong PHP?', NULL, 'password_hash', 'Hàm này tạo chuỗi băm an toàn cho mật khẩu.', 'md5', 'md5 không an toàn cho mật khẩu.', 'sha1', 'sha1 không an toàn cho mật khẩu.', 'encrypt', 'Không phải hàm chuẩn trong PHP.', 'A'),
('CUOI_KY', 'Câu lệnh SQL nào dùng để cập nhật dữ liệu?', NULL, 'UPDATE', 'UPDATE dùng để sửa đổi dữ liệu.', 'INSERT', 'INSERT dùng để thêm dữ liệu.', 'DELETE', 'DELETE dùng để xóa dữ liệu.', 'SELECT', 'SELECT dùng để truy vấn dữ liệu.', 'A'),
('CUOI_KY', 'Trong PHP, hàm nào dùng để lấy số phần tử của mảng?', NULL, 'count', 'Hàm count trả về số phần tử của mảng.', 'sizeof', 'sizeof tương tự count, nhưng count phổ biến hơn.', 'length', 'length không tồn tại trong PHP.', 'array_length', 'array_length không phải hàm PHP.', 'A'),
('CUOI_KY', 'Thẻ HTML nào dùng để tạo liên kết?', NULL, '<a>', 'Thẻ <a> tạo liên kết với thuộc tính href.', '<link>', '<link> dùng để liên kết tài nguyên như CSS.', '<href>', '<href> không phải thẻ HTML.', '<url>', '<url> không phải thẻ HTML.', 'A');

--
-- Cấu trúc bảng cho bảng `quiz`
--

CREATE TABLE `quiz` (
  `Id_cauhoi` int(255) NOT NULL,
  `id_baitest` varchar(255) NOT NULL,
  `cauhoi` varchar(255) NOT NULL,
  `hinhanh` varchar(255) NOT NULL,
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

INSERT INTO `quiz` (`Id_cauhoi`, `id_baitest`, `cauhoi`, `hinhanh`, `cau_a`, `giaithich_a`, `cau_b`, `giaithich_b`, `cau_c`, `giaithich_c`, `cau_d`, `giaithich_d`, `dap_an`) VALUES
(1, 'Giữ ky', 'Cấu trúc điều khiển nào sau đây không có trong Python?', 'images/68109b322c143.jfif', 'For', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'while', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'do-while', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'if', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'C'),
(2, 'Giư ky', 'Kết quả của đoạn code JavaScript sau là gì?', '', 'null', 'Trong JavaScript, typeof null trả về \\\'object\\\' do một lỗi lịch sử trong thiết kế ngôn ngữ.', 'undefined', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'object', 'Trong JavaScript, typeof null trả về \\\'object\\\' do một lỗi lịch sử trong thiết kế ngôn ngữ.', 'string', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'C'),
(3, 'Giữ ky', 'có bao nhieu ngôn ngữ lâp trinh', '', '200', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', '3000', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', '400', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', '700', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'D'),
(4, 'Giữ ky', 'Phím sao chép trên thiết bị máy tính', '', 'ctrl+s', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'ctrl+c', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'ctrl+v', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'ctrl+d', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'B'),
(5, 'Giữ ky', 'GPT viết tắt của từ gì?', '', 'Generative Adversarial Transformer', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'General Parsing Transformer', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'Great Predictive Transformer\'', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'Generative Pre-trained Transformer', 'Python không có cấu trúc do-while. Nó sử dụng for, while và if để điều khiển luồng chương trình.', 'D');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`Id_cauhoi`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
