<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$order_id) {
    header("Location: index.php");
    exit();
}

// Получаем информацию о заказе
$query = "SELECT o.*, os.name as status_name 
          FROM orders o
          LEFT JOIN order_statuses os ON o.status_id = os.id
          WHERE o.id = $order_id";
$result = mysqli_query($conn, $query);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    header("Location: index.php");
    exit();
}

// Проверяем, можно ли удалять заказ (нельзя удалять оплаченные или доставленные)
$can_delete = true;
$message = '';
if ($order['status_id'] == 4 || $order['status_id'] == 6) {
    $can_delete = false;
    $message = 'Нельзя удалить оплаченный или доставленный заказ. Рекомендуется изменить статус на "Отменен".';
}

// Обработка удаления
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm = isset($_POST['confirm']) ? $_POST['confirm'] : '';
    
    if ($confirm === 'yes') {
        // Начинаем транзакцию
        mysqli_begin_transaction($conn);
        
        try {
            // Удаляем связанные записи
            $queries = [
                "DELETE FROM order_coupons WHERE order_id = $order_id",
                "DELETE FROM order_status_history WHERE order_id = $order_id",
                "DELETE FROM order_items WHERE order_id = $order_id",
                "DELETE FROM orders WHERE id = $order_id"
            ];
            
            foreach ($queries as $query) {
                if (!mysqli_query($conn, $query)) {
                    throw new Exception(mysqli_error($conn));
                }
            }
            
            // Фиксируем транзакцию
            mysqli_commit($conn);
            
            // Логируем действие
            $log_message = date('Y-m-d H:i:s') . " - Администратор {$_SESSION['user_name']} удалил заказ {$order['order_number']} (ID: {$order['id']})\n";
            file_put_contents('../admin_log.txt', $log_message, FILE_APPEND);
            
            $_SESSION['success'] = "Заказ успешно удален";
            header("Location: index.php");
            exit();
            
        } catch (Exception $e) {
            // Откатываем транзакцию при ошибке
            mysqli_rollback($conn);
            $error = 'Ошибка при удалении заказа: ' . $e->getMessage();
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
    <title>Удаление заказа - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style/orders/delete.css">
</head>
<body>
    <div class="delete-container">
        <div class="warning-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        
        <h1>Подтверждение удаления</h1>
        
        <div class="order-info">
            <div class="info-row">
                <span class="info-label">Номер заказа:</span>
                <span class="info-value"><?php echo htmlspecialchars($order['order_number']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Клиент:</span>
                <span class="info-value"><?php echo htmlspecialchars($order['customer_name']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value"><?php echo htmlspecialchars($order['customer_email']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Статус:</span>
                <span class="info-value"><?php echo htmlspecialchars($order['status_name']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Сумма:</span>
                <span class="info-value"><?php echo number_format($order['total_amount'], 0, ',', ' '); ?> ₽</span>
            </div>
            <div class="info-row">
                <span class="info-label">Дата создания:</span>
                <span class="info-value"><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></span>
            </div>
        </div>
        
        <?php if (!$can_delete): ?>
            <div class="danger-alert">
                <i class="fas fa-exclamation-circle"></i>
                <strong>Внимание!</strong> <?php echo $message; ?>
            </div>
            
            <div class="button-group">
                <a href="edit.php?id=<?php echo $order['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Изменить статус
                </a>
                <a href="index.php" class="btn btn-outline">Отмена</a>
            </div>
            
        <?php else: ?>
            <div class="danger-alert">
                <i class="fas fa-exclamation-circle"></i>
                <strong>Внимание!</strong> Это действие нельзя отменить. Все данные заказа будут удалены безвозвратно.
                <br><br>
                <small>Будут удалены: заказ, позиции заказа, история статусов, примененные купоны.</small>
            </div>
            
            <form method="POST">
                <div class="button-group">
                    <button type="submit" name="confirm" value="yes" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Удалить заказ
                    </button>
                    <a href="index.php" class="btn btn-outline">Отмена</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>