<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();
$admin = getAdminData(); // Получаем данные админа

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$user_id) {
    header("Location: index.php");
    exit();
}

// Проверяем, не пытаемся ли удалить себя
if ($user_id == $admin['id']) {
    $_SESSION['error'] = "Вы не можете удалить свой собственный аккаунт";
    header("Location: index.php");
    exit();
}

// Получаем информацию о пользователе
$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    header("Location: index.php");
    exit();
}

// Проверяем, есть ли у пользователя заказы
$order_query = "SELECT COUNT(*) as order_count FROM orders WHERE user_id = $user_id";
$order_result = mysqli_query($conn, $order_query);
$order_count = mysqli_fetch_assoc($order_result)['order_count'];

// Обработка удаления
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm = isset($_POST['confirm']) ? $_POST['confirm'] : '';
    
    if ($confirm === 'yes') {
        // Если пользователь имеет заказы, возможно, стоит только деактивировать аккаунт
        if ($order_count > 0) {
            // Деактивируем аккаунт вместо удаления
            $deactivate_query = "UPDATE users SET is_active = 0, updated_at = NOW() WHERE id = $user_id";
            if (mysqli_query($conn, $deactivate_query)) {
                // Логируем действие
                $log_message = date('Y-m-d H:i:s') . " - Администратор {$_SESSION['user_name']} деактивировал пользователя {$user['name']} (ID: {$user['id']}) из-за наличия заказов\n";
                file_put_contents('../admin_log.txt', $log_message, FILE_APPEND);
                
                $_SESSION['success'] = "Пользователь деактивирован (имеет $order_count заказов)";
                header("Location: index.php");
                exit();
            }
        } else {
            // Удаляем пользователя
            $delete_query = "DELETE FROM users WHERE id = $user_id";
            if (mysqli_query($conn, $delete_query)) {
                // Логируем действие
                $log_message = date('Y-m-d H:i:s') . " - Администратор {$_SESSION['user_name']} удалил пользователя {$user['name']} (ID: {$user['id']})\n";
                file_put_contents('../admin_log.txt', $log_message, FILE_APPEND);
                
                $_SESSION['success'] = "Пользователь успешно удален";
                header("Location: index.php");
                exit();
            }
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
    <title>Удаление пользователя - FlaxTap</title>
   <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <link rel="stylesheet" href="../assets/style/users/delete.css">
</head>
<body>
    <div class="delete-container">
        <div class="warning-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        
        <h1>Подтверждение удаления</h1>
        
        <div class="user-info">
            <div class="info-row">
                <span class="info-label">ID:</span>
                <span class="info-value">#<?php echo $user['id']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Имя:</span>
                <span class="info-value"><?php echo htmlspecialchars($user['name']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Роль:</span>
                <span class="info-value"><?php echo $user['role'] === 'admin' ? 'Администратор' : 'Пользователь'; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Заказов:</span>
                <span class="info-value"><?php echo $order_count; ?></span>
            </div>
        </div>
        
        <?php if ($order_count > 0): ?>
            <div class="danger-alert">
                <i class="fas fa-exclamation-circle"></i>
                <strong>Внимание!</strong> У этого пользователя есть <?php echo $order_count; ?> заказ(ов). 
                Вместо удаления аккаунт будет деактивирован.
            </div>
        <?php else: ?>
            <div class="danger-alert">
                <i class="fas fa-exclamation-circle"></i>
                <strong>Внимание!</strong> Это действие нельзя отменить. Все данные пользователя будут удалены безвозвратно.
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="button-group">
                <button type="submit" name="confirm" value="yes" class="btn btn-danger">
                    <i class="fas fa-trash"></i> <?php echo $order_count > 0 ? 'Деактивировать' : 'Удалить'; ?>
                </button>
                <a href="index.php" class="btn btn-outline">Отмена</a>
            </div>
        </form>
    </div>
</body>
</html>