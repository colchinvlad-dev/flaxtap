<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$unanswered_only = isset($_GET['unanswered']) ? $_GET['unanswered'] == '1' : false;

if (!$product_id) {
    die('<div class="no-data"><i class="fas fa-exclamation-triangle"></i><p>Не указан ID товара</p></div>');
}

// Получаем информацию о товаре
$product_query = "SELECT * FROM products WHERE id = $product_id";
$product_result = mysqli_query($conn, $product_query);
$product = mysqli_fetch_assoc($product_result);

if (!$product) {
    die('<div class="no-data"><i class="fas fa-exclamation-triangle"></i><p>Товар не найден</p></div>');
}

// Получаем вопросы
$where = "product_id = $product_id";
if ($unanswered_only) {
    $where .= " AND is_answered = 0";
}

$questions_query = "SELECT pq.*, 
                   u.name as user_name,
                   u2.name as answered_by_name
                   FROM product_questions pq
                   LEFT JOIN users u ON pq.user_id = u.id
                   LEFT JOIN users u2 ON pq.answered_by = u2.id
                   WHERE $where
                   ORDER BY is_answered ASC, created_at DESC
                   LIMIT 50";
$questions_result = mysqli_query($conn, $questions_query);
?>

<div style="margin-bottom: 20px;">
    <h3 style="margin-bottom: 10px;"><?php echo htmlspecialchars($product['name']); ?></h3>
    <p style="color: var(--gray-color); font-size: 14px; margin-bottom: 20px;">
        <?php if ($unanswered_only): ?>
            Вопросы без ответа: <?php echo mysqli_num_rows($questions_result); ?>
        <?php else: ?>
            Все вопросы: <?php echo mysqli_num_rows($questions_result); ?>
        <?php endif; ?>
    </p>
</div>

<?php if (mysqli_num_rows($questions_result) > 0): ?>
    <?php while ($question = mysqli_fetch_assoc($questions_result)): ?>
        <div class="question-item <?php echo $question['is_answered'] ? 'answered' : 'unanswered'; ?>" 
             style="margin-bottom: 15px; padding: 15px; border-radius: 8px; background: white; border: 1px solid #e2e8f0;">
            <div class="question-header" style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <div>
                    <div style="font-weight: 600; color: var(--dark-color);">
                        <?php echo htmlspecialchars($question['user_name'] ?: 'Аноним'); ?>
                    </div>
                </div>
                <div style="font-size: 12px; color: var(--gray-color);">
                    <?php echo date('d.m.Y H:i', strtotime($question['created_at'])); ?>
                </div>
            </div>
            
            <div class="question-content" style="margin-bottom: 10px;">
                <strong>Вопрос:</strong>
                <p><?php echo nl2br(htmlspecialchars($question['question'])); ?></p>
            </div>
            
            <?php if ($question['is_answered'] && $question['answer']): ?>
                <div class="answer-content" style="margin-top: 10px; padding: 10px; background: #e8f5e9; border-radius: 6px; border-left: 3px solid var(--success-color);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 14px; color: var(--success-color); font-weight: 600;">
                        <span>
                            <i class="fas fa-reply"></i> Ответ администратора
                            <?php if ($question['answered_by_name']): ?>
                                (<?php echo htmlspecialchars($question['answered_by_name']); ?>)
                            <?php endif; ?>
                        </span>
                        <span>
                            <?php echo date('d.m.Y H:i', strtotime($question['answered_at'])); ?>
                        </span>
                    </div>
                    <p><?php echo nl2br(htmlspecialchars($question['answer'])); ?></p>
                </div>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <div style="text-align: center; padding: 40px; color: var(--gray-color);">
        <i class="fas fa-question-circle" style="font-size: 48px; color: #e2e8f0; margin-bottom: 15px;"></i>
        <p>Вопросов по этому товару не найдено</p>
    </div>
<?php endif; ?>

<div style="margin-top: 20px; text-align: center;">
    <button onclick="closeModal()" class="btn btn-outline" style="padding: 10px 20px;">
        <i class="fas fa-times"></i> Закрыть
    </button>
    <?php if (!$unanswered_only && mysqli_num_rows($questions_result) > 0): ?>
        <a href="index.php?tab=questions&q_product=<?php echo $product_id; ?>" class="btn btn-primary" style="padding: 10px 20px; margin-left: 10px;">
            <i class="fas fa-external-link-alt"></i> Все вопросы в админке
        </a>
    <?php endif; ?>
</div>