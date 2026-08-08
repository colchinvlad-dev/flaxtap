<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

$store_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$store_id) {
    header("Location: index.php");
    exit();
}

// Получаем информацию о магазине
$query = "SELECT s.* FROM stores s WHERE s.id = $store_id";
$result = mysqli_query($conn, $query);
$store = mysqli_fetch_assoc($result);

if (!$store) {
    header("Location: index.php");
    exit();
}

// Проверяем, есть ли отзывы на магазин
$reviews_query = "SELECT COUNT(*) as count FROM store_reviews WHERE store_id = $store_id";
$reviews_result = mysqli_query($conn, $reviews_query);
$reviews_count = mysqli_fetch_assoc($reviews_result)['count'];

// Проверяем, используется ли магазин в заказах
$orders_query = "SELECT COUNT(*) as count FROM orders WHERE delivery_method_id = 2 AND delivery_address LIKE '%" . mysqli_real_escape_string($conn, $store['name']) . "%'";
$orders_result = mysqli_query($conn, $orders_query);
$orders_count = mysqli_fetch_assoc($orders_result)['count'];

// Обработка удаления
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm = isset($_POST['confirm']) ? $_POST['confirm'] : '';
    
    if ($confirm === 'yes') {
        // Начинаем транзакцию
        mysqli_begin_transaction($conn);
        
        try {
            // Проверяем, не является ли магазин по умолчанию
            if ($store['is_default']) {
                // Находим другой активный магазин
                $alternative_query = "SELECT id FROM stores WHERE id != $store_id AND is_active = 1 ORDER BY sort_order LIMIT 1";
                $alternative_result = mysqli_query($conn, $alternative_query);
                
                if (mysqli_num_rows($alternative_result) > 0) {
                    $alternative = mysqli_fetch_assoc($alternative_result);
                    // Устанавливаем другой магазин по умолчанию
                    mysqli_query($conn, "UPDATE stores SET is_default = 1 WHERE id = " . $alternative['id']);
                }
            }
            
            // Удаляем связанные записи
            $queries = [
                "DELETE FROM store_images WHERE store_id = $store_id",
                "DELETE FROM store_reviews WHERE store_id = $store_id",
                "DELETE FROM store_staff WHERE store_id = $store_id",
                "DELETE FROM stores WHERE id = $store_id"
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
            $log_message = date('Y-m-d H:i:s') . " - Администратор {$admin_name} удалил магазин: {$store['name']} (ID: {$store['id']})\n";
            file_put_contents('../admin_log.txt', $log_message, FILE_APPEND);
            
            $_SESSION['success'] = "Магазин успешно удален";
            header("Location: index.php");
            exit();
            
        } catch (Exception $e) {
            // Откатываем транзакцию при ошибке
            mysqli_rollback($conn);
            $error = 'Ошибка при удалении магазина: ' . $e->getMessage();
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
    <title>Удаление магазина - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style/stores/delete.css">
</head>
<body>
    <div class="delete-container">
        <div class="warning-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        
        <h1>Подтверждение удаления</h1>
        
        <div class="store-info">
            <div class="info-row">
                <span class="info-label">Название магазина:</span>
                <span class="info-value"><?php echo htmlspecialchars($store['name']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Тип:</span>
                <span class="info-value">
                    <?php 
                    if ($store['type'] == 'shop') echo 'Магазин';
                    elseif ($store['type'] == 'warehouse_shop') echo 'Склад-магазин';
                    else echo 'Пункт выдачи';
                    ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Адрес:</span>
                <span class="info-value"><?php echo htmlspecialchars($store['city'] . ', ' . $store['address']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Статус:</span>
                <span class="info-value"><?php echo $store['is_active'] ? 'Активен' : 'Неактивен'; ?></span>
            </div>
            <?php if ($store['is_default']): ?>
                <div class="info-row">
                    <span class="info-label">Особый статус:</span>
                    <span class="info-value" style="color: var(--danger-color);">Магазин по умолчанию</span>
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
        </div>
        
        <?php if ($store['is_default']): ?>
            <div class="danger-alert">
                <i class="fas fa-exclamation-circle"></i>
                <strong>Внимание!</strong> Это магазин по умолчанию. При удалении будет автоматически выбран другой активный магазин.
            </div>
        <?php endif; ?>
        
        <?php if ($reviews_count > 0): ?>
            <div class="danger-alert">
                <i class="fas fa-exclamation-circle"></i>
                <strong>Внимание!</strong> У этого магазина есть отзывы. При удалении все отзывы также будут удалены безвозвратно.
            </div>
        <?php endif; ?>
        
        <div class="danger-alert">
            <i class="fas fa-exclamation-circle"></i>
            <strong>Внимание!</strong> Это действие нельзя отменить. Все данные магазина будут удалены безвозвратно.
            <br><br>
            <small>Будут удалены: магазин, изображения, отзывы, информация о персонале.</small>
        </div>
        
        <form method="POST">
            <div class="button-group">
                <button type="submit" name="confirm" value="yes" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Удалить магазин
                </button>
                <a href="index.php" class="btn btn-outline">Отмена</a>
            </div>
        </form>
    </div>
</body>
</html>