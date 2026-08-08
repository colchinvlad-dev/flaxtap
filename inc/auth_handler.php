<?php
ob_start(); // Включаем буферизацию вывода
session_start();

// Определяем путь к корню сайта
$rootPath = dirname(__DIR__); // Это даст C:\OSPanel\domains\flaxtap\

// Подключаем конфигурацию базы данных
include $rootPath . '/config/database.php';

// Устанавливаем кодировку
header('Content-Type: text/html; charset=utf-8');

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

// Получаем действие и URL для редиректа
$action = $_POST['action'] ?? '';
$redirect_url = $_POST['redirect'] ?? '/profile/index.php';

// Очищаем URL от потенциально опасных символов
$redirect_url = filter_var($redirect_url, FILTER_SANITIZE_URL);

// Проверяем наличие действия
if (empty($action)) {
    $_SESSION['auth_error'] = 'Не указано действие';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
    exit;
}

// Обработка входа
if ($action === 'login') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Валидация
    if (empty($login) || empty($password)) {
        $_SESSION['auth_error'] = 'Заполните все поля';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        exit;
    }
    
    // Поиск пользователя
    $sql = "SELECT * FROM users WHERE (email = ? OR telephone = ?) AND is_active = 1";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("ss", $login, $login);
    
    if (!$stmt->execute()) {
        $_SESSION['auth_error'] = 'Ошибка базы данных';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        exit;
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['auth_error'] = 'Пользователь не найден';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        exit;
    }
    
    $user = $result->fetch_assoc();
    
    // Проверка пароля
    if (!password_verify($password, $user['password'])) {
        $_SESSION['auth_error'] = 'Неверный пароль';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        exit;
    }
    
    // Успешная авторизация
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    
    // Обновляем время последнего входа
    $update_sql = "UPDATE users SET last_login_at = NOW(), login_count = login_count + 1 WHERE id = ?";
    $update_stmt = $connection->prepare($update_sql);
    $update_stmt->bind_param("i", $user['id']);
    $update_stmt->execute();
    
    // Редирект
    header('Location: ' . $redirect_url);
    exit;

// Обработка регистрации
} elseif ($action === 'register') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Валидация
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Введите имя';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email';
    }
    
    if (empty($telephone)) {
        $errors[] = 'Введите телефон';
    }
    
    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Пароль должен быть не менее 6 символов';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Пароли не совпадают';
    }
    
    if (!isset($_POST['agreement'])) {
        $errors[] = 'Необходимо согласиться с правилами';
    }
    
    // Если есть ошибки валидации
    if (!empty($errors)) {
        $_SESSION['auth_error'] = implode('<br>', $errors);
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        exit;
    }
    
    // Проверка уникальности email
    $check_sql = "SELECT id FROM users WHERE email = ?";
    $check_stmt = $connection->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    
    if (!$check_stmt->execute()) {
        $_SESSION['auth_error'] = 'Ошибка базы данных при проверке email';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        exit;
    }
    
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $_SESSION['auth_error'] = 'Пользователь с таким email уже зарегистрирован';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        exit;
    }
    
    // Хешируем пароль
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Создаем пользователя
    $insert_sql = "INSERT INTO users (name, password, email, telephone, role, created_at) 
                  VALUES (?, ?, ?, ?, 'user', NOW())";
    $insert_stmt = $connection->prepare($insert_sql);
    $insert_stmt->bind_param("ssss", $name, $hashed_password, $email, $telephone);
    
    if (!$insert_stmt->execute()) {
        $_SESSION['auth_error'] = 'Ошибка при регистрации. Попробуйте позже.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        exit;
    }
    
    $user_id = $connection->insert_id;
    
    // Автоматически авторизуем пользователя
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role'] = 'user';
    
    // Редирект
    header('Location: ' . $redirect_url);
    exit;
}

// Если действие не распознано
$_SESSION['auth_error'] = 'Неизвестное действие';
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
exit;
?>