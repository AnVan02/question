-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 01, 2025 lúc 07:22 PM
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
-- Cấu trúc bảng cho bảng `product` 
--
CREATE TABLE `product`(
  `id_baitest` int (200) NOT NULL ,
  `ten_baitest` varchar(255) NOT NULL, 
  `loai_baitest` varchar (255) NOT NULL,
  `cau_hoi` varchar(255) NOT NULL,
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



--
-- Đổ dữ liệu cho bảng `product` 
--







--
-- Cấu trúc bảng cho bảng `quiz`
--

CREATE TABLE `quiz` (
  `Id_cauhoi` int(255) NOT NULL,
  `id_baitest` varchar(255) NOT NULL,
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

INSERT INTO `quiz` (`Id_cauhoi`, `id_baitest`, `cauhoi`, `hinhanh`, `cau_a`, `giaithich_a`, `cau_b`, `giaithich_b`, `cau_c`, `giaithich_c`, `cau_d`, `giaithich_d`, `dap_an`) VALUES
(1, 'TEST1', 'What is PHP?', '', 'A server-side scripting language', 'PHP runs on the server.', 'A styling language', 'CSS is for styling.', 'A database', 'MySQL is a database.', 'A browser', 'Browsers display web pages.', 'A'),
(2, 'TEST1', 'What does HTML stand for?', '', 'Hyper Text Markup Language', 'Correct definition.', 'High Text Machine Language', 'Incorrect.', 'Hyper Tabular Markup Language', 'Incorrect.', 'None of these', 'Incorrect.', 'A'),
(3, 'TEST1', 'Which is a CSS framework?', 'images/bootstrap.png', 'Bootstrap', 'A popular CSS framework.', 'Laravel', 'A PHP framework.', 'Django', 'A Python framework.', 'Flask', 'A Python framework.', 'A'),
(4, 'TEST1', 'What is MySQL?', '', 'A programming language', 'Incorrect.', 'A database management system', 'Manages databases.', 'A web server', 'Incorrect.', 'A text editor', 'Incorrect.', 'B'),
(5, 'TEST1', 'What is the purpose of JavaScript?', '', 'Styling web pages', 'CSS is for styling.', 'Adding interactivity to web pages', 'Correct.', 'Managing databases', 'Incorrect.', 'Serving web pages', 'Incorrect.', 'B'),
(6, '1', 'hello là gì', '', 'for', 'dfsfjđss', 'hello', 'dsjfodjds', 'do-while', 'dsjdjdjs', 'if', 'dsjjdhsodo', 'B'),
(8, 'Giưa ky', '1+2 = mấy', NULL, '2', 'dfsfjđss', '4', 'dsjfodjds', '3', 'dsjdjdjs', '5', 'dsjjdhsodo', 'C'),
(9, 'GIUA_KY', 'Ngôn ngữ lập trình nào được sử dụng chủ yếu cho phát triển web phía server?', NULL, 'PHP', 'PHP là ngôn ngữ phía server phổ biến.', 'CSS', 'CSS dùng để định dạng giao diện.', 'HTML', 'HTML là ngôn ngữ đánh dấu.', 'JavaScript', 'JavaScript chủ yếu chạy trên trình duyệt.', 'A'),
(10, 'GIUA_KY', 'Câu lệnh nào dùng để lặp trong PHP?', NULL, 'for', 'Câu lệnh for dùng để lặp với số lần xác định.', 'if', 'if dùng để kiểm tra điều kiện.', 'echo', 'echo dùng để xuất dữ liệu.', 'switch', 'switch dùng để chọn nhiều trường hợp.', 'A'),
(11, 'GIUA_KY', 'Hàm nào dùng để kết nối cơ sở dữ liệu MySQL trong PHP?', NULL, 'mysqli_connect', 'Hàm này tạo kết nối đến MySQL.', 'mysql_connect', 'Hàm cũ, không khuyến khích dùng.', 'connect_db', 'Không phải hàm có sẵn.', 'db_open', 'Không tồn tại trong PHP.', 'A'),
(12, 'CUOI_KY', 'Phương thức nào dùng để gửi dữ liệu form an toàn hơn trong HTML?', NULL, 'POST', 'POST gửi dữ liệu qua body, an toàn hơn.', 'GET', 'GET gửi dữ liệu qua URL, dễ bị lộ.', 'PUT', 'PUT dùng trong API, không phải form HTML.', 'DELETE', 'DELETE dùng trong API, không phải form HTML.', 'A'),
(13, 'CUOI_KY', 'Trong PHP, biến toàn cục được khai báo bằng từ khóa nào?', NULL, 'global', 'Từ khóa global dùng để truy cập biến toàn cục.', 'static', 'static giữ giá trị giữa các lần gọi hàm.', 'const', 'const khai báo hằng số.', 'var', 'var không dùng để khai báo biến toàn cục.', 'A'),
(14, 'CUOI_KY', 'Hàm nào dùng để mã hóa mật khẩu trong PHP?', NULL, 'password_hash', 'Hàm này tạo chuỗi băm an toàn cho mật khẩu.', 'md5', 'md5 không an toàn cho mật khẩu.', 'sha1', 'sha1 không an toàn cho mật khẩu.', 'encrypt', 'Không phải hàm chuẩn trong PHP.', 'A'),
(15, 'CUOI_KY', 'Câu lệnh SQL nào dùng để cập nhật dữ liệu?', NULL, 'UPDATE', 'UPDATE dùng để sửa đổi dữ liệu.', 'INSERT', 'INSERT dùng để thêm dữ liệu.', 'DELETE', 'DELETE dùng để xóa dữ liệu.', 'SELECT', 'SELECT dùng để truy vấn dữ liệu.', 'A'),
(16, 'CUOI_KY', 'Trong PHP, hàm nào dùng để lấy số phần tử của mảng?', NULL, 'count', 'Hàm count trả về số phần tử của mảng.', 'sizeof', 'sizeof tương tự count, nhưng count phổ biến hơn.', 'length', 'length không tồn tại trong PHP.', 'array_length', 'array_length không phải hàm PHP.', 'A'),
(17, 'CUOI_KY', 'Thẻ HTML nào dùng để tạo liên kết?', NULL, '<a>', 'Thẻ <a> tạo liên kết với thuộc tính href.', '<link>', '<link> dùng để liên kết tài nguyên như CSS.', '<href>', '<href> không phải thẻ HTML.', '<url>', '<url> không phải thẻ HTML.', 'A');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`Id_cauhoi`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `quiz`
--
ALTER TABLE `quiz`
  MODIFY `Id_cauhoi` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
