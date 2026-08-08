<?php
// Конфигурация базы данных
define('DB_HOST', 'mysql-8.4');     // Хост базы данных
define('DB_USER', 'root');          // Имя пользователя
define('DB_PASSWORD', '');          // Пароль пользователя
define('DB_NAME', 'db_flaxtap');    // Имя базы данных

// Создаем подключение
$connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Проверяем подключение
if (!$connection) {
    die("Ошибка подключения: " . mysqli_connect_error());
}

mysqli_set_charset($connection, "utf8");
?>