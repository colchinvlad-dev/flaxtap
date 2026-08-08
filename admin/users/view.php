<?php
ob_start(); // Включаем буферизацию вывода
include "../config/database.php";
checkAdminAuth();

// Получаем данные администратора из сессии или базы
if (isset($_SESSION['admin_id'])) {
    $admin_id = (int)$_SESSION['admin_id'];
    $admin_query = "SELECT name, email FROM users WHERE id = $admin_id";
    $admin_result = mysqli_query($conn, $admin_query);
    $admin = mysqli_fetch_assoc($admin_result);
} else {
    // Если нет в сессии, берем из базы первого администратора
    $admin_query = "SELECT name, email FROM users WHERE role = 'admin' LIMIT 1";
    $admin_result = mysqli_query($conn, $admin_query);
    $admin = mysqli_fetch_assoc($admin_result);
    if ($admin) {
        // Получаем ID администратора
        $admin_id_query = "SELECT id FROM users WHERE role = 'admin' LIMIT 1";
        $admin_id_result = mysqli_query($conn, $admin_id_query);
        $admin_id_row = mysqli_fetch_assoc($admin_id_result);
        $_SESSION['admin_id'] = $admin_id_row['id'] ?? 1;
    }
}

// Если все еще нет админа, устанавливаем значения по умолчанию
if (!$admin) {
    $admin = [
        'name' => 'Администратор',
        'email' => 'admin@example.com'
    ];
}

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$user_id) {
    header("Location: index.php");
    exit();
}

$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    header("Location: index.php");
    exit();
}

// Получаем статистику пользователя
$order_query = "SELECT COUNT(*) as order_count FROM orders WHERE user_id = $user_id";
$order_result = mysqli_query($conn, $order_query);
$order_stats = mysqli_fetch_assoc($order_result);

$review_query = "SELECT COUNT(*) as review_count FROM product_reviews WHERE user_id = $user_id";
$review_result = mysqli_query($conn, $review_query);
$review_stats = mysqli_fetch_assoc($review_result);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр пользователя - <?php echo htmlspecialchars($user['name']); ?> - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/users/view.css">
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Просмотр пользователя</h1>
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Назад к списку
            </a>
        </div>
        
        <div class="profile-container">
            <div class="profile-card">
                <div class="card-header">
                    <i class="fas fa-user-circle"></i>
                    <h2>Основная информация</h2>
                </div>
                
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
                
                <div class="info-group">
                    <div class="info-label">ID пользователя</div>
                    <div class="info-value">#<?php echo $user['id']; ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Полное имя</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['name']); ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Телефон</div>
                    <div class="info-value"><?php echo $user['telephone'] ? htmlspecialchars($user['telephone']) : 'Не указан'; ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Роль</div>
                    <div class="info-value">
                        <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-danger' : 'badge-info'; ?>">
                            <?php echo $user['role'] === 'admin' ? 'Администратор' : 'Пользователь'; ?>
                        </span>
                    </div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Статус аккаунта</div>
                    <div class="info-value">
                        <span class="badge <?php echo $user['is_active'] ? 'badge-success' : 'badge-danger'; ?>">
                            <?php echo $user['is_active'] ? 'Активен' : 'Заблокирован'; ?>
                        </span>
                    </div>
                </div>
                
                <div class="user-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $order_stats['order_count']; ?></div>
                        <div class="stat-label">Заказов</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $review_stats['review_count']; ?></div>
                        <div class="stat-label">Отзывов</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $user['login_count']; ?></div>
                        <div class="stat-label">Входов</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">
                            <?php
                            if ($user['last_login_at']) {
                                $lastLogin = strtotime($user['last_login_at']);
                                $daysAgo = floor((time() - $lastLogin) / (60 * 60 * 24));
                                echo $daysAgo . ' дн.';
                            } else {
                                echo 'Никогда';
                            }
                            ?>
                        </div>
                        <div class="stat-label">Последний вход</div>
                    </div>
                </div>
            </div>
            
            <div class="profile-card">
                <div class="card-header">
                    <i class="fas fa-history"></i>
                    <h2>Детали аккаунта</h2>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Дата регистрации</div>
                    <div class="info-value"><?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Последнее обновление</div>
                    <div class="info-value"><?php echo date('d.m.Y H:i', strtotime($user['updated_at'] ?? '')); ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Последний вход</div>
                    <div class="info-value">
                        <?php 
                        if ($user['last_login_at']) {
                            echo date('d.m.Y H:i', strtotime($user['last_login_at']));
                        } else {
                            echo 'Еще не входил';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Последняя смена пароля</div>
                    <div class="info-value">
                        <?php 
                        if ($user['last_password_change']) {
                            echo date('d.m.Y H:i', strtotime($user['last_password_change']));
                        } else {
                            echo 'Никогда';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Редактировать
                    </a>
                    <a href="change_password.php?id=<?php echo $user['id']; ?>" class="btn btn-outline">
                        <i class="fas fa-key"></i> Сменить пароль
                    </a>
                    <a href="delete.php?id=<?php echo $user['id']; ?>" 
                       class="btn btn-danger"
                       onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?');">
                        <i class="fas fa-trash"></i> Удалить
                    </a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>