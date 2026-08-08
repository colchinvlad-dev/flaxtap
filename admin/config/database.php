<?php
session_start();

// Настройки подключения к базе данных
define('DB_HOST', 'mysql-8.4');
define('DB_NAME', 'db_flaxtap');
define('DB_USER', 'root'); 
define('DB_PASS', ''); 

// Создаем подключение
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Проверяем подключение
if (!$conn) {
    die("Ошибка подключения: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

// Функция для защиты от SQL-инъекций
function clean_input($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

// Проверка авторизации администратора
function checkAdminAuth() {
    if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] != 'admin') {
        header('Location: login.php');
        exit();
    }
}

// Получение данных администратора
function getAdminData() {
    if (isset($_SESSION['admin_id'])) {
        return [
            'id' => $_SESSION['admin_id'],
            'name' => $_SESSION['admin_name'],
            'email' => $_SESSION['admin_email'],
            'role' => $_SESSION['admin_role']
        ];
    }
    return null;
}

// Получение полных данных администратора
function getAdminFullData($admin_id) {
    global $conn;
    $query = "SELECT * FROM users WHERE id = $admin_id";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}
?>