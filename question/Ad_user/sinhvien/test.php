<?php if ($mode == 'edit' && empty($message) && !empty($student_data)): ?>
        <h3 >Chi tiết bài làm</h3>
        <?php foreach ($questions as $index => $question): ?>
            <div class="question-block">
                <p><strong>Câu <?php echo $index + 1; ?>:</strong> <?php echo htmlspecialchars($question['question']); ?></p>
                <?php if (!empty($question['image'])): ?>
                    <img src="<?php echo htmlspecialchars($question['image']); ?>" alt="Hình ảnh câu hỏi" style="max-width: 300px;">
                <?php endif; ?>
                
                <ul>
                    <?php foreach ($question['choices'] as $key => $value): ?>
                        <?php
                        $question_num = $index + 1;
                        $is_selected = isset($answers[$question_num]) && $key === $answers[$question_num];
                        $is_correct = $key === $question['correct'];
                        $class = '';

                        if ($is_selected) {
                            $class = $is_correct ? 'correct' : 'incorrect';
                        } elseif ($is_correct) {
                            $class = 'correct';
                        }

                        // Icon cho đáp án được chọn
                        $icon = '';
                        if ($is_selected && $is_correct) {
                            $icon = '<span class="icon-tick">✔</span>';
                        } elseif ($is_selected && !$is_correct) {
                            $icon = '<span class="icon-cross">✘</span>';
                        }
                        ?>
                        <li class="<?php echo $class; ?>">
                            <?php echo $key; ?>. <?php echo htmlspecialchars($value); ?>
                            <?php echo $icon; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>