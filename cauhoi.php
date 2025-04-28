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
        "explanations" => [
            "gan" => "GANs được dùng chủ yếu để tạo hình ảnh hoặc dữ liệu tổng hợp, không phải để xử lý hoặc sinh ngôn ngữ như code.",
            "cnn" => "CNN là mạng nơ-ron chuyên xử lý dữ liệu hình ảnh, không phù hợp cho việc tạo code hoặc xử lý chuỗi.",
            "rnn" => "RNN xử lý dữ liệu tuần tự nhưng gặp hạn chế khi chuỗi quá dài, không mạnh mẽ bằng Transformer.",
            "transformer" => "Transformer mạnh trong xử lý ngôn ngữ nhờ cơ chế attention, nên được dùng để tạo code (ví dụ như Codex)."
        ]
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
        "explanations" => [
            "gan" => "GAN là Generative Adversarial Networks, không phải Transformer.",
            "cnn" => "CNN là Convolutional Neural Networks, General Parsing Transformer không tồn tại.",
            "rnn" => "RNN là Recurrent Neural Networks, Great Predictive Transformer là sai.",
            "gpt" => "GPT là viết tắt của Generative Pre-trained Transformer, đúng định nghĩa chuẩn."
        ]
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
        "explanations" => [
            "a" => "Biến trong hàm là biến cục bộ (local variable), không phải biến toàn cục.",
            "b" => "Biến không bị giới hạn bởi vòng lặp, vòng lặp chỉ ảnh hưởng đến luồng chương trình.",
            "c" => "Biến toàn cục được khai báo ngoài các hàm và có thể được truy cập ở bất kỳ đâu.",
            "d" => "Biến toàn cục tồn tại trong mọi ngôn ngữ, không chỉ riêng JavaScript."
        ]
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
        "explanations" => [
            "a" => "mysql_connect() là hàm cũ, đã bị loại bỏ từ PHP 7.",
            "b" => "mysqli_connect() là hàm hiện đại và phổ biến để kết nối MySQL.",
            "c" => "Không tồn tại hàm connect_mysql() trong PHP.",
            "d" => "pdo_connect() không phải hàm PHP chuẩn; PHP dùng PDO::__construct() để kết nối qua PDO."
        ]
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
        "explanations" => [
            "a" => "HighText Machine Language không tồn tại, đây là sai.",
            "b" => "HyperText and links Markup Language là không đúng, từ 'links' không có trong tên chính thức.",
            "c" => "HTML đúng là HyperText Markup Language - ngôn ngữ đánh dấu siêu văn bản.",
            "d" => "Hyper Tool Multi Language không phải tên chuẩn, hoàn toàn sai."
        ]
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
        "explanations" => [
            "a" => "Array là đối tượng (object) trong JavaScript, không phải kiểu nguyên thủy.",
            "b" => "Object bản thân là loại non-primitive (không nguyên thủy).",
            "c" => "Function cũng là object, không phải primitive.",
            "d" => "Boolean là kiểu dữ liệu nguyên thủy trong JavaScript."
        ]
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
        "explanations" => [
            "a" => "UPDATE dùng để cập nhật dữ liệu đã có, không phải lấy dữ liệu.",
            "b" => "DELETE dùng để xóa dữ liệu, không phải truy vấn.",
            "c" => "SELECT dùng để truy vấn và lấy dữ liệu từ cơ sở dữ liệu.",
            "d" => "INSERT dùng để thêm dữ liệu mới, không phải lấy dữ liệu."
        ]
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
        "explanations" => [
            "a" => "Kotlin được Google chọn là ngôn ngữ chính thức cho Android, thay thế Java trong nhiều trường hợp.",
            "b" => "Swift chủ yếu được dùng để phát triển ứng dụng iOS, không phải Android.",
            "c" => "PHP là ngôn ngữ server-side, không được dùng để lập trình app Android.",
            "d" => "Ruby không phải ngôn ngữ phổ biến trong lập trình ứng dụng di động."
        ]
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
        "explanations" => [
            "a" => "`git clone` dùng để sao chép toàn bộ nội dung repo về máy local.",
            "b" => "Không có lệnh `git copy` trong Git.",
            "c" => "`git init` chỉ khởi tạo repo Git mới chứ không sao chép repo.",
            "d" => "`git fork` là hành động trên GitHub (không phải lệnh Git CLI) để nhân bản repo vào tài khoản."
        ]
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
        "explanations" => [
            "a" => "Cú pháp thiếu dấu \$ và không hợp lệ trong PHP.",
            "b" => "PHP không sử dụng `new Array()`, đây là cú pháp JavaScript.",
            "c" => "Cú pháp chuẩn trong PHP là \$array = array(); hoặc từ PHP 5.4+, có thể dùng [] rút gọn.",
            "d" => "array() = \$arr; là cú pháp sai hoàn toàn trong PHP."
        ]
    ],
];
?>
