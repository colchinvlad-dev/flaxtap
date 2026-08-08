<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "config/database.php";
checkAdminAuth();
$admin = getAdminData();

// Получаем статистику из базы данных
$stats = [];

// Количество пользователей
$query = "SELECT COUNT(*) as count FROM users";
$result = mysqli_query($conn, $query);
$stats['users'] = mysqli_fetch_assoc($result)['count'];

// Количество заказов
$query = "SELECT COUNT(*) as count FROM orders";
$result = mysqli_query($conn, $query);
$stats['orders'] = mysqli_fetch_assoc($result)['count'];

// Количество вакансий
$query = "SELECT COUNT(*) as count FROM vacancies";
$result = mysqli_query($conn, $query);
$stats['vacancies'] = mysqli_fetch_assoc($result)['count'];

// Количество новостей
$query = "SELECT COUNT(*) as count FROM news";
$result = mysqli_query($conn, $query);
$stats['news'] = mysqli_fetch_assoc($result)['count'];

// Количество товаров
$query = "SELECT COUNT(*) as count FROM products";
$result = mysqli_query($conn, $query);
$stats['products'] = mysqli_fetch_assoc($result)['count'];

// Количество магазинов
$query = "SELECT COUNT(*) as count FROM stores";
$result = mysqli_query($conn, $query);
$stats['stores'] = mysqli_fetch_assoc($result)['count'];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - FlaxTap</title>
    <link rel="stylesheet" href="assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style/index.css">
</head>
<body>
    <?php include "inc/sidebar.php"; ?>
    
    <!-- Основное содержимое -->
    <main class="main-content">
            <?php include "inc/header.php"; ?>
        
        <!-- Статистика -->
        <div class="stats-grid">
            <div class="stat-card users">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Пользователи</div>
                        <div class="stat-value"><?php echo $stats['users']; ?></div>
                        <div class="stat-change">+5 за месяц</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card orders">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Заказы</div>
                        <div class="stat-value"><?php echo $stats['orders']; ?></div>
                        <div class="stat-change">+12 за месяц</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card vacancies">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Вакансии</div>
                        <div class="stat-value"><?php echo $stats['vacancies']; ?></div>
                        <div class="stat-change">0 активных</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card news">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Новости</div>
                        <div class="stat-value"><?php echo $stats['news']; ?></div>
                        <div class="stat-change">+6 за месяц</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card products">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Товары</div>
                        <div class="stat-value"><?php echo $stats['products']; ?></div>
                        <div class="stat-change">+3 за месяц</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card stores">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Магазины</div>
                        <div class="stat-value"><?php echo $stats['stores']; ?></div>
                        <div class="stat-change">2 города</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-store"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Недавняя активность -->
        <div class="recent-activity">
            <h2 class="section-title">Недавняя активность</h2>
            <ul class="activity-list">
                <li class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-user-plus text-primary"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Новый пользователь зарегистрирован</div>
                        <div class="activity-time">5 минут назад</div>
                    </div>
                </li>
                <li class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-shopping-cart text-success"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Новый заказ #ORD-2026-00001</div>
                        <div class="activity-time">30 минут назад</div>
                    </div>
                </li>
                <li class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-newspaper text-purple"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Новая новость опубликована</div>
                        <div class="activity-time">2 часа назад</div>
                    </div>
                </li>
                <li class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-box text-danger"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Новый товар добавлен в каталог</div>
                        <div class="activity-time">5 часов назад</div>
                    </div>
                </li>
            </ul>
        </div>
    </main>
</body>
</html>