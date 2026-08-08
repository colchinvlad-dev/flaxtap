<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

$error = '';
$success = '';
$form_data = [
    'name' => '',
    'email' => '',
    'telephone' => '',
    'role' => 'user',
    'is_active' => 1
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем и очищаем данные
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $telephone = mysqli_real_escape_string($conn, trim($_POST['telephone'] ?? ''));
    $role = $_POST['role'] === 'admin' ? 'admin' : 'user';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Валидация
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Все обязательные поля должны быть заполнены';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Некорректный формат email';
    } elseif ($password !== $confirm_password) {
        $error = 'Пароли не совпадают';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен содержать минимум 6 символов';
    } else {
        // Проверяем, не существует ли уже пользователь с таким email
        $check_query = "SELECT id FROM users WHERE email = '$email'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = 'Пользователь с таким email уже существует';
        } else {
            // Хешируем пароль
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Вставляем нового пользователя
            $insert_query = "INSERT INTO users (
                name, 
                email, 
                password, 
                telephone, 
                role, 
                is_active, 
                created_at, 
                updated_at,
                last_password_change
            ) VALUES (
                '$name',
                '$email',
                '$hashed_password',
                '$telephone',
                '$role',
                $is_active,
                NOW(),
                NOW(),
                NOW()
            )";
            
            if (mysqli_query($conn, $insert_query)) {
                $new_user_id = mysqli_insert_id($conn);
                
                // Логируем действие
                $log_message = date('Y-m-d H:i:s') . " - Администратор {$_SESSION['user_name']} создал нового пользователя: $name (ID: $new_user_id, Email: $email)\n";
                file_put_contents('../admin_log.txt', $log_message, FILE_APPEND);
                
                $_SESSION['success'] = 'Пользователь успешно создан';
                header("Location: index.php");
                exit();
            } else {
                $error = 'Ошибка при создании пользователя: ' . mysqli_error($conn);
            }
        }
    }
    
    // Сохраняем введенные данные для повторного отображения
    $form_data = [
        'name' => htmlspecialchars($name ?? ''),
        'email' => htmlspecialchars($email ?? ''),
        'telephone' => htmlspecialchars($telephone ?? ''),
        'role' => $role,
        'is_active' => $is_active
    ];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить пользователя - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style/users/create.css">
    <script src="../assets/js/users/create.js"></script>
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Добавить нового пользователя</h1>
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Назад к списку
            </a>
        </div>
        
        <div class="create-container">
            <div class="form-card">
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="card-header">
                        <i class="fas fa-user-plus"></i>
                        <h2>Основная информация</h2>
                    </div>
                    
                    <div class="form-group">
                        <label for="name" class="required">Полное имя</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               class="form-control" 
                               placeholder="Иван Иванов" 
                               value="<?php echo htmlspecialchars($form_data['name']); ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="required">Email адрес</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-control" 
                               placeholder="user@example.com" 
                               value="<?php echo htmlspecialchars($form_data['email']); ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="telephone">Телефон</label>
                        <input type="tel" 
                               id="telephone" 
                               name="telephone" 
                               class="form-control" 
                               placeholder="+7 (999) 123-45-67" 
                               value="<?php echo htmlspecialchars($form_data['telephone']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="required">Роль</label>
                        <div class="radio-group">
                            <div class="radio-option">
                                <input type="radio" 
                                       id="role_user" 
                                       name="role" 
                                       value="user" 
                                       <?php echo $form_data['role'] === 'user' ? 'checked' : ''; ?>>
                                <label for="role_user">Пользователь</label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" 
                                       id="role_admin" 
                                       name="role" 
                                       value="admin" 
                                       <?php echo $form_data['role'] === 'admin' ? 'checked' : ''; ?>>
                                <label for="role_admin">Администратор</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1" 
                               <?php echo $form_data['is_active'] ? 'checked' : ''; ?>>
                        <label for="is_active">Активный аккаунт</label>
                    </div>
                    
                    <div class="card-header" style="margin-top: 30px;">
                        <i class="fas fa-lock"></i>
                        <h2>Безопасность</h2>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="required">Пароль</label>
                        <div class="password-field">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="form-control" 
                                   placeholder="Минимум 6 символов" 
                                   required>
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="strength-meter" id="passwordStrength"></div>
                        </div>
                        <div class="password-hint" id="passwordHint"></div>
                        <button type="button" class="btn btn-outline" onclick="generatePassword()" style="margin-top: 10px; padding: 8px 15px;">
                            <i class="fas fa-key"></i> Сгенерировать пароль
                        </button>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="required">Подтверждение пароля</label>
                        <div class="password-field">
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   class="form-control" 
                                   placeholder="Повторите пароль" 
                                   required>
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Создать пользователя
                        </button>
                        <a href="index.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Отмена
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>