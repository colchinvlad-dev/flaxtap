<?php
ob_start(); // Включаем буферизацию вывода
// Запускаем сессию
session_start();

// Подключаем БД для обнуления токена
include "config/database.php";

// Если пользователь авторизован, удаляем remember_token из БД
if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    
    // Обнуляем remember_token в базе данных
    $query = "UPDATE users SET remember_token = NULL, token_expires_at = NULL WHERE id = $admin_id";
    mysqli_query($conn, $query);
}

// Удаляем cookies "Запомнить меня"
if (isset($_COOKIE['admin_token'])) {
    setcookie('admin_token', '', time() - 3600, '/');
    setcookie('admin_id', '', time() - 3600, '/');
}

// Уничтожаем все данные сессии
session_unset();
session_destroy();

// Удаляем куки сессии, если они есть
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Записываем в лог выход из системы
if (isset($admin_id)) {
    $log_message = date('Y-m-d H:i:s') . " - Администратор (ID: $admin_id) вышел из системы. IP: {$_SERVER['REMOTE_ADDR']}\n";
    file_put_contents('admin_log.txt', $log_message, FILE_APPEND);
}

// Перенаправляем на страницу входа
header('Location: login.php');
exit();
?>
