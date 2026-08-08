<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header("Location: index.php");
    exit();
}

// Получаем информацию о товаре
$query = "SELECT p.* FROM products p WHERE p.id = $product_id";
$result = mysqli_query($conn, $query);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    header("Location: index.php");
    exit();
}

// Проверяем, есть ли вопросы к товару
$questions_query = "SELECT COUNT(*) as count FROM product_questions WHERE product_id = $product_id";
$questions_result = mysqli_query($conn, $questions_query);
$questions_count = mysqli_fetch_assoc($questions_result)['count'];

// Проверяем, есть ли отзывы
$reviews_query = "SELECT COUNT(*) as count FROM product_reviews WHERE product_id = $product_id";
$reviews_result = mysqli_query($conn, $reviews_query);
$reviews_count = mysqli_fetch_assoc($reviews_result)['count'];

// Проверяем, используется ли товар в заказах
$orders_query = "SELECT COUNT(*) as count FROM order_items WHERE product_id = $product_id";
$orders_result = mysqli_query($conn, $orders_query);
$orders_count = mysqli_fetch_assoc($orders_result)['count'];

// Обработка удаления
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm = isset($_POST['confirm']) ? $_POST['confirm'] : '';
    
    if ($confirm === 'yes') {
        // Начинаем транзакцию
        mysqli_begin_transaction($conn);
        
        try {
            // Удаляем связанные записи
            $queries = [
                "DELETE FROM product_attributes WHERE product_id = $product_id",
                "DELETE FROM product_images WHERE product_id = $product_id",
                "DELETE FROM product_questions WHERE product_id = $product_id",
                "DELETE FROM product_reviews WHERE product_id = $product_id",
                "DELETE FROM products WHERE id = $product_id"
            ];
            
            foreach ($queries as $query) {
                if (!mysqli_query($conn, $query)) {
                    throw new Exception(mysqli_error($conn));
                }
            }
            
            // Фиксируем транзакцию
            mysqli_commit($conn);
            
            // Логируем действие
            $admin_name = $_SESSION['user_name'] ?? $_SESSION['admin_name'] ?? 'Неизвестный';
            $log_message = date('Y-m-d H:i:s') . " - Администратор {$admin_name} удалил товар: {$product['name']} (ID: {$product['id']})\n";
            file_put_contents('../../admin_log.txt', $log_message, FILE_APPEND);
            
            $_SESSION['success'] = "Товар успешно удален";
            header("Location: index.php");
            exit();
            
        } catch (Exception $e) {
            // Откатываем транзакцию при ошибке
            mysqli_rollback($conn);
            $error = 'Ошибка при удалении товара: ' . $e->getMessage();
        }
    } else {
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удаление товара - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style/products/delete.css">
</head>
<body>
    <div class="delete-container">
        <div class="warning-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        
        <h1>Подтверждение удаления</h1>
        
        <div class="product-info">
            <?php if ($product['image_url']): ?>
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                     class="product-image-preview"
                     onerror="this.style.display='none'">
            <?php endif; ?>
            
            <div class="info-row">
                <span class="info-label">Название товара:</span>
                <span class="info-value"><?php echo htmlspecialchars($product['name']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Бренд:</span>
                <span class="info-value"><?php echo htmlspecialchars($product['brand']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Артикул:</span>
                <span class="info-value">ID <?php echo $product['id']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Цена:</span>
                <span class="info-value"><?php echo number_format($product['current_price'], 2); ?> ₽</span>
            </div>
            <div class="info-row">
                <span class="info-label">Статус:</span>
                <span class="info-value"><?php echo $product['in_stock'] ? 'В наличии' : 'Нет в наличии'; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Создан:</span>
                <span class="info-value"><?php echo date('d.m.Y', strtotime($product['created_at'])); ?></span>
            </div>
            <?php if ($questions_count > 0): ?>
                <div class="info-row">
                    <span class="info-label">Вопросов:</span>
                    <span class="info-value" style="color: var(--danger-color);">
                        <?php echo $questions_count; ?> (будут удалены)
                    </span>
                </div>
            <?php endif; ?>
            <?php if ($reviews_count > 0): ?>
                <div class="info-row">
                    <span class="info-label">Отзывов:</span>
                    <span class="info-value" style="color: var(--danger-color);">
                        <?php echo $reviews_count; ?> (будут удалены)
                    </span>
                </div>
            <?php endif; ?>
            <?php if ($orders_count > 0): ?>
                <div class="info-row">
                    <span class="info-label">В заказах:</span>
                    <span class="info-value" style="color: var(--danger-color);">
                        <?php echo $orders_count; ?> (останется в истории заказов)
                    </span>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($questions_count > 0): ?>
            <div class="danger-alert">
                <i class="fas fa-exclamation-circle"></i>
                <strong>Внимание!</strong> У этого товара есть вопросы от покупателей. При удалении все вопросы также будут удалены.
            </div>
        <?php endif; ?>
        
        <?php if ($reviews_count > 0): ?>
            <div class="danger-alert">
                <i class="fas fa-exclamation-circle"></i>
                <strong>Внимание!</strong> У этого товара есть отзывы. При удалении все отзывы также будут удалены.
            </div>
        <?php endif; ?>
        
        <?php if ($orders_count > 0): ?>
            <div class="danger-alert">
                <i class="fas fa-exclamation-circle"></i>
                <strong>Внимание!</strong> Этот товар содержится в заказах. Товар будет удален из каталога, но останется в истории заказов.
            </div>
        <?php endif; ?>
        
        <div class="danger-alert">
            <i class="fas fa-exclamation-circle"></i>
            <strong>Внимание!</strong> Это действие нельзя отменить. Все данные товара будут удалены безвозвратно.
            <br><br>
            <small>Будут удалены: товар, изображения, атрибуты, вопросы, отзывы.</small>
        </div>
        
        <form method="POST">
            <div class="button-group">
                <button type="submit" name="confirm" value="yes" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Удалить товар
                </button>
                <a href="index.php" class="btn btn-outline">Отмена</a>
            </div>
        </form>
    </div>
</body>
</html>