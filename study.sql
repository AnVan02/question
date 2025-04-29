-- File: study.sql
-- Mô tả: Cơ sở dữ liệu SQL cho hệ thống quiz lập trình

-- Tạo bảng users (Lưu thông tin người dùng, nếu cần)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tạo bảng questions (Lưu danh sách câu hỏi)
CREATE TABLE questions (
    question_id INT AUTO_INCREMENT PRIMARY KEY,
    question_text TEXT NOT NULL,
    image_url VARCHAR(255),
    choice_a TEXT NOT NULL,
    choice_b TEXT NOT NULL,
    choice_c TEXT NOT NULL,
    choice_d TEXT NOT NULL,
    correct_answer CHAR(1) NOT NULL CHECK (correct_answer IN ('A', 'B', 'C', 'D'))
);

-- Tạo bảng quiz_sessions (Lưu thông tin phiên làm bài)
CREATE TABLE quiz_sessions (
    session_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    start_time DATETIME NOT NULL,
    score INT DEFAULT 0,
    highest_score INT DEFAULT 0,
    attempts INT DEFAULT 0,
    completed BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Tạo bảng selected_questions (Lưu danh sách câu hỏi được chọn cho mỗi phiên)
CREATE TABLE selected_questions (
    selection_id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT,
    question_id INT,
    question_order INT NOT NULL,
    FOREIGN KEY (session_id) REFERENCES quiz_sessions(session_id),
    FOREIGN KEY (question_id) REFERENCES questions(question_id)
);

-- Tạo bảng user_answers (Lưu đáp án của người dùng)
CREATE TABLE user_answers (
    answer_id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT,
    question_id INT,
    selected_answer CHAR(1) NOT NULL CHECK (selected_answer IN ('A', 'B', 'C', 'D')),
    is_correct BOOLEAN NOT NULL,
    FOREIGN KEY (session_id) REFERENCES quiz_sessions(session_id),
    FOREIGN KEY (question_id) REFERENCES questions(question_id)
);

-- Tạo chỉ số để tối ưu hóa truy vấn
CREATE INDEX idx_session_id ON quiz_sessions(session_id);
CREATE INDEX idx_selected_questions_session ON selected_questions(session_id, question_order);
CREATE INDEX idx_user_answers_session ON user_answers(session_id);

-- Ví dụ: Thêm người dùng
INSERT INTO users (username, email) VALUES ('test_user', 'test@example.com');

-- Ví dụ: Thêm câu hỏi mẫu
INSERT INTO questions (question_text, image_url, choice_a, choice_b, choice_c, choice_d, correct_answer)
VALUES 
    ('Câu hỏi 1: 1+1=?', NULL, '1', '2', '3', '4', 'B'),
    ('Câu hỏi 2: Thủ đô Việt Nam?', NULL, 'Hà Nội', 'TP.HCM', 'Đà Nẵng', 'Huế', 'A'),
    ('Câu hỏi 3: 2*3=?', NULL, '5', '6', '7', '8', 'B'),
    ('Câu hỏi 4: Mặt trời mọc ở hướng nào?', NULL, 'Đông', 'Tây', 'Nam', 'Bắc', 'A'),
    ('Câu hỏi 5: 10/2=?', NULL, '3', '4', '5', '6', 'C'),
    ('Câu hỏi 6: Ngôn ngữ lập trình nào phổ biến nhất?', NULL, 'Python', 'Java', 'C++', 'JavaScript', 'A');

-- Ví dụ: Tạo phiên quiz mới
INSERT INTO quiz_sessions (user_id, start_time, score, highest_score, attempts)
VALUES (1, NOW(), 0, 0, 0);

-- Ví dụ: Chọn 5 câu hỏi ngẫu nhiên cho phiên
INSERT INTO selected_questions (session_id, question_id, question_order)
SELECT 
    (SELECT MAX(session_id) FROM quiz_sessions),
    question_id,
    ROW_NUMBER() OVER (ORDER BY RAND()) AS question_order
FROM questions
ORDER BY RAND()
LIMIT 5;

-- Ví dụ: Lưu đáp án của người dùng
INSERT INTO user_answers (session_id, question_id, selected_answer, is_correct)
SELECT 
    1,
    1,
    'B',
    (SELECT CASE WHEN 'B' = correct_answer THEN TRUE ELSE FALSE END 
     FROM questions WHERE question_id = 1);

-- Ví dụ: Cập nhật điểm số
UPDATE quiz_sessions
SET score = score + 1
WHERE session_id = 1 AND EXISTS (
    SELECT 1 FROM user_answers 
    WHERE session_id = 1 AND is_correct = TRUE 
    AND question_id = 1
);

-- Ví dụ: Kiểm tra số lần làm bài
SELECT attempts FROM quiz_sessions WHERE session_id = 1;

-- Ví dụ: Reset phiên quiz
DELETE FROM selected_questions WHERE session_id = 1;
DELETE FROM user_answers WHERE session_id = 1;
INSERT INTO quiz_sessions (user_id, start_time, score, highest_score, attempts)
SELECT user_id, NOW(), 0, highest_score, attempts + 1
FROM quiz_sessions WHERE session_id = 1;

-- Ví dụ: Lấy câu hỏi hiện tại
SELECT q.question_text, q.image_url, q.choice_a, q.choice_b, q.choice_c, q.choice_d
FROM questions q
JOIN selected_questions sq ON q.question_id = sq.question_id
WHERE sq.session_id = 1 AND sq.question_order = 1;

-- Ví dụ: Đánh dấu phiên hoàn thành
UPDATE quiz_sessions
SET completed = TRUE, attempts = attempts + 1
WHERE session_id = 1;