<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($password) || empty($confirm_password)) {
        $error = 'Все поля обязательны для заполнения';
    } elseif ($password !== $confirm_password) {
        $error = 'Пароли не совпадают';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен содержать минимум 6 символов';
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $update_query = "UPDATE users SET 
            password = '$hashed_password', 
            last_password_change = NOW(),
            updated_at = NOW()
            WHERE id = $user_id";
        
        if (mysqli_query($conn, $update_query)) {
            $success = 'Пароль успешно изменен';
            
            // Исправляем предупреждение: проверяем наличие ключа
            $admin_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : (isset($_SESSION['name']) ? $_SESSION['name'] : 'Администратор');
            
            // Логируем действие
            $log_message = date('Y-m-d H:i:s') . " - Администратор $admin_name сменил пароль пользователю {$user['name']} (ID: {$user['id']})\n";
            file_put_contents('../admin_log.txt', $log_message, FILE_APPEND);
        } else {
            $error = 'Ошибка при обновлении пароля: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Смена пароля - <?php echo htmlspecialchars($user['name']); ?> - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style/users/pwd.css">
    <script src="../assets/js/users/pwd.js"></script>
</head>
<body>
    <div class="password-container">
        <div class="user-header">
            <div class="user-avatar">
                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
            </div>
            <div class="user-name"><?php echo htmlspecialchars($user['name']); ?></div>
            <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="passwordForm">
            <div class="form-group">
                <label for="password">Новый пароль</label>
                <div class="password-field">
                    <i class="fas fa-key icon-left"></i>
                    <input type="password" id="password" name="password" required placeholder="Введите новый пароль">
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="password-strength">
                    <div class="strength-bar" id="strengthBar"></div>
                </div>
                <div class="strength-text" id="strengthText">Не оценен</div>
                <div class="password-hint">Минимум 6 символов. Рекомендуем использовать буквы, цифры и специальные символы</div>
            </div>
            
            <button type="button" class="btn-generate" onclick="generatePassword()">
                <i class="fas fa-key"></i> Сгенерировать надежный пароль
            </button>
            
            <div class="form-group">
                <label for="confirm_password">Подтверждение пароля</label>
                <div class="password-field">
                    <i class="fas fa-redo icon-left"></i>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Повторите пароль">
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="password-hint">Пароли должны совпадать</div>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Сменить пароль
            </button>
        </form>
        
        <div class="back-link">
            <a href="index.php">
                <i class="fas fa-arrow-left"></i> Вернуться к списку пользователей
            </a>
        </div>
    </div>
</body>
</html>