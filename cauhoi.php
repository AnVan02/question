<?php
$questions = [
    [
        "question" => "Loại mạng nơ-ron nào thường được sử dụng trong mô hình tạo code tự động?",
        "choices" => [
            "gan" => "Mạng đối nghịch sinh (GANs)",
            "cnn" => "Mạng tích chập (CNN)",
            "rnn" => "Mạng hồi quy (RNN)",
            "transformer" => "Mạng biến đổi (Transformer)"
        ],
        "correct" => "transformer",
        "explanation" => "Các mô hình tạo code như Codex sử dụng kiến trúc Transformer do khả năng xử lý ngôn ngữ mạnh mẽ."
    ],
    [
        "question" => "GPT viết tắt của từ gì?",
        "choices" => [
            "gan" => "Generative Adversarial Transformer",
            "cnn" => "General Parsing Transformer",
            "rnn" => "Great Predictive Transformer",
            "gpt" => "Generative Pre-trained Transformer"
        ],
        "correct" => "gpt",
        "explanation" => "GPT là viết tắt của Generative Pre-trained Transformer – một mô hình học sâu dùng trong xử lý ngôn ngữ tự nhiên."
    ],
    [
        "question" => "Biến toàn cục (global variable) là gì?",
        "choices" => [
            "a" => "Biến được định nghĩa bên trong một hàm",
            "b" => "Biến chỉ tồn tại khi vòng lặp hoạt động",
            "c" => "Biến có thể truy cập ở mọi nơi trong chương trình",
            "d" => "Biến chỉ dùng trong file JavaScript"
        ],
        "correct" => "c",
        "explanation" => "Biến toàn cục là biến được khai báo ngoài các hàm và có thể truy cập từ bất kỳ đâu trong mã nguồn."
    ],
    [
        "question" => "Trong PHP, hàm nào dùng để kết nối đến MySQL?",
        "choices" => [
            "a" => "mysql_connect()",
            "b" => "mysqli_connect()",
            "c" => "connect_mysql()",
            "d" => "pdo_connect()"
        ],
        "correct" => "b",
        "explanation" => "Hàm `mysqli_connect()` là phương pháp được dùng phổ biến để kết nối đến MySQL trong PHP (phiên bản mới)."
    ],
    [
        "question" => "HTML viết tắt của cụm từ nào sau đây?",
        "choices" => [
            "a" => "HighText Machine Language",
            "b" => "HyperText and links Markup Language",
            "c" => "HyperText Markup Language",
            "d" => "Hyper Tool Multi Language"
        ],
        "correct" => "c",
        "explanation" => "HTML là viết tắt của HyperText Markup Language – ngôn ngữ đánh dấu để xây dựng trang web."
    ],
    [
        "question" => "Trong JavaScript, kiểu dữ liệu nào sau đây là kiểu nguyên thủy (primitive)?",
        "choices" => [
            "a" => "Array",
            "b" => "Object",
            "c" => "Function",
            "d" => "Boolean"
        ],
        "correct" => "d",
        "explanation" => "Kiểu dữ liệu nguyên thủy gồm: Number, String, Boolean, null, undefined, Symbol, BigInt."
    ],
    [
        "question" => "Câu lệnh nào trong SQL dùng để lấy dữ liệu?",
        "choices" => [
            "a" => "UPDATE",
            "b" => "DELETE",
            "c" => "SELECT",
            "d" => "INSERT"
        ],
        "correct" => "c",
        "explanation" => "`SELECT` là câu lệnh dùng để truy vấn và lấy dữ liệu từ bảng trong SQL.",
        "image" => "" // không có hinh ảnh
    ],
    [
        "question" => "Ngôn ngữ nào phổ biến nhất để xây dựng ứng dụng Android?",
        "choices" => [
            "a" => "Kotlin",
            "b" => "Swift",
            "c" => "PHP",
            "d" => "Ruby"
        ],
        "correct" => "a",
        "explanation" => "Kotlin hiện nay là ngôn ngữ chính thức được Google khuyến nghị để phát triển ứng dụng Android.",
        "image" => "image.png" // Đường dẫn ảnh, có thể bỏ trống
    ],
    [
        "question" => "Lệnh nào trong Git để sao chép một repo về máy?",
        "choices" => [
            "a" => "git clone",
            "b" => "git copy",
            "c" => "git init",
            "d" => "git fork"
        ],
        "correct" => "a",
        "explanation" => "`git clone` dùng để sao chép toàn bộ mã nguồn từ repository trên remote về máy local."
    ],
    [
        "question" => "Cú pháp khai báo mảng trong PHP là gì?",
        "choices" => [
            "a" => "array = []",
            "b" => "\$array = new Array();",
            "c" => "\$array = array();",
            "d" => "array() = \$arr;"
        ],
        "correct" => "c",
        "explanation" => "Trong PHP, cú pháp đúng để khai báo mảng là: \$array = array(); hoặc \$array = []; từ PHP 5.4+."
    ],
];
?>
