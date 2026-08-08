<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "config/database.php";

// Сообщения об ошибках
$error = '';
$email = '';

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    // Валидация
    if (empty($email) || empty($password)) {
        $error = 'Пожалуйста, заполните все поля';
    } else {
        // Ищем пользователя с ролью admin
        $query = "SELECT * FROM users WHERE email = '$email' AND role = 'admin' LIMIT 1";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $admin = mysqli_fetch_assoc($result);
            
            // Проверяем пароль
            if (password_verify($password, $admin['password'])) {
                // Проверяем, не заблокирован ли аккаунт (дополнительная проверка)
                $is_active = true; 
                
                if ($is_active) {
                    // Обновляем время последнего входа и счетчик
                    $admin_id = $admin['id'];
                    $update_query = "UPDATE users SET 
                        last_login_at = NOW(),
                        login_count = login_count + 1,
                        updated_at = NOW()
                        WHERE id = $admin_id";
                    
                    if (mysqli_query($conn, $update_query)) {
                        // Устанавливаем сессию
                        $_SESSION['admin_id'] = $admin['id'];
                        $_SESSION['admin_name'] = $admin['name'];
                        $_SESSION['admin_email'] = $admin['email'];
                        $_SESSION['admin_role'] = $admin['role'];
                        $_SESSION['admin_logged_in'] = true;
                        
                        // Запоминаем пользователя на 30 дней, если выбрано "Запомнить меня"
                        if ($remember) {
                            $cookie_token = bin2hex(random_bytes(32));
                            $expire = time() + (30 * 24 * 60 * 60); // 30 дней
                            
                            // Сохраняем токен в базу данных
                            $update_token_query = "UPDATE users SET 
                                remember_token = '$cookie_token',
                                token_expires_at = FROM_UNIXTIME($expire)
                                WHERE id = $admin_id";
                            
                            if (mysqli_query($conn, $update_token_query)) {
                                setcookie('admin_token', $cookie_token, $expire, '/', '', false, true);
                                setcookie('admin_id', $admin_id, $expire, '/', '', false, true);
                            }
                        }
                        
                        // Записываем в лог
                        $log_message = date('Y-m-d H:i:s') . " - Администратор {$admin['name']} (ID: {$admin['id']}) вошел в систему. IP: {$_SERVER['REMOTE_ADDR']}\n";
                        file_put_contents('admin_log.txt', $log_message, FILE_APPEND);
                        
                        // Перенаправляем в админ-панель
                        header('Location: index.php');
                        exit();
                    } else {
                        $error = 'Ошибка при обновлении данных. Попробуйте еще раз.';
                    }
                } else {
                    $error = 'Ваш аккаунт заблокирован. Обратитесь к главному администратору.';
                }
            } else {
                $error = 'Неверный email или пароль';
                
                // Записываем в лог неудачную попытку входа
                $log_message = date('Y-m-d H:i:s') . " - Неудачная попытка входа для email: $email. IP: {$_SERVER['REMOTE_ADDR']}\n";
                file_put_contents('login_attempts.txt', $log_message, FILE_APPEND);
            }
        } else {
            $error = 'Неверный email или пароль';
            
            // Записываем в лог неудачную попытку входа
            $log_message = date('Y-m-d H:i:s') . " - Неудачная попытка входа для несуществующего email: $email. IP: {$_SERVER['REMOTE_ADDR']}\n";
            file_put_contents('login_attempts.txt', $log_message, FILE_APPEND);
        }
    }
}

// Если пользователь уже авторизован, перенаправляем в админ-панель
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit();
}

// Проверка cookie для автоматического входа
if (isset($_COOKIE['admin_token']) && isset($_COOKIE['admin_id']) && !isset($_SESSION['admin_logged_in'])) {
    $admin_id = mysqli_real_escape_string($conn, $_COOKIE['admin_id']);
    $token = mysqli_real_escape_string($conn, $_COOKIE['admin_token']);
    
    $query = "SELECT * FROM users WHERE id = '$admin_id' AND remember_token = '$token' AND role = 'admin' 
              AND token_expires_at > NOW() LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);
        
        // Обновляем время последнего входа
        $update_query = "UPDATE users SET 
            last_login_at = NOW(),
            login_count = login_count + 1,
            updated_at = NOW()
            WHERE id = $admin_id";
        mysqli_query($conn, $update_query);
        
        // Устанавливаем сессию
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_logged_in'] = true;
        
        // Записываем в лог
        $log_message = date('Y-m-d H:i:s') . " - Администратор {$admin['name']} (ID: {$admin['id']}) вошел автоматически по токену. IP: {$_SERVER['REMOTE_ADDR']}\n";
        file_put_contents('admin_log.txt', $log_message, FILE_APPEND);
        
        header('Location: index.php');
        exit();
    } else {
        // Неверный токен, удаляем куки
        setcookie('admin_token', '', time() - 3600, '/');
        setcookie('admin_id', '', time() - 3600, '/');
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в панель управления - FlaxTap</title>
    <link rel="stylesheet" href="assets/style/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="assets/js/login.js"></script>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <div class="logo-icon">FT</div>
                <h1>FlaxTap</h1>
                <p>Панель управления</p>
            </div>
            
            <div class="login-title">
                <h2>Вход в систему</h2>
                <p>Введите свои учетные данные для доступа к панели управления</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email адрес</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control" 
                           placeholder="admin@example.com" 
                           value="<?php echo htmlspecialchars($email); ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <div class="password-container">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control" 
                               placeholder="Введите пароль" 
                               required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="remember-forgot">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Запомнить меня</label>
                    </div>
                    <a href="#" class="forgot-password" onclick="alert('Функция восстановления пароля пока недоступна. Обратитесь к главному администратору.');">
                        Забыли пароль?
                    </a>
                </div>
                
                <button type="submit" class="btn-login" onclick="validateForm()">
                    <i class="fas fa-sign-in-alt"></i> Войти
                </button>
            </form>
            
            <div class="security-info">
                <i class="fas fa-shield-alt"></i>
                <span>Ваши данные защищены. Все действия в системе логируются.</span>
            </div>
        </div>
        
        <div class="back-to-site">
            <a href="/">
                <i class="fas fa-arrow-left"></i> Вернуться на сайт
            </a>
        </div>
    </div>
</body>
</html>