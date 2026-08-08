<?php
ob_start(); // Включаем буферизацию вывода
include "../config/database.php";
checkAdminAuth();

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$user_id) {
    header("Location: index.php");
    exit();
}

// Получаем данные пользователя
$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    header("Location: index.php");
    exit();
}

// Обработка формы редактирования
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $telephone = mysqli_real_escape_string($conn, $_POST['telephone']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Проверка email на уникальность (кроме текущего пользователя)
    $email_check_query = "SELECT id FROM users WHERE email = '$email' AND id != $user_id";
    $email_check_result = mysqli_query($conn, $email_check_query);
    
    if (mysqli_num_rows($email_check_result) > 0) {
        $error = 'Пользователь с таким email уже существует';
    } else {
        // Обновляем данные пользователя
        $update_query = "UPDATE users SET 
                        name = '$name',
                        email = '$email',
                        telephone = '$telephone',
                        role = '$role',
                        is_active = $is_active,
                        updated_at = NOW()
                        WHERE id = $user_id";
        
        if (mysqli_query($conn, $update_query)) {
            $success = 'Данные пользователя успешно обновлены';
            // Обновляем данные пользователя для отображения
            $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));
        } else {
            $error = 'Ошибка при обновлении данных: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование пользователя - <?php echo htmlspecialchars($user['name']); ?> - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/users/edit.css">
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Редактирование пользователя</h1>
            <div>
                <a href="view.php?id=<?php echo $user['id']; ?>" class="btn btn-outline">
                    <i class="fas fa-eye"></i> Просмотр
                </a>
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Назад к списку
                </a>
            </div>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="profile-container">
                <div class="profile-card">
                    <div class="card-header">
                        <i class="fas fa-user-edit"></i>
                        <h2>Редактирование данных</h2>
                    </div>
                    
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">ID пользователя</div>
                        <div class="info-value">#<?php echo $user['id']; ?></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Полное имя *</label>
                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Телефон</label>
                        <input type="tel" class="form-control" name="telephone" value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Роль *</label>
                        <select class="form-select" name="role" required>
                            <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>Пользователь</option>
                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Администратор</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" id="is_active" <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">Активный аккаунт</label>
                        </div>
                        <small style="display: block; margin-top: 5px; color: var(--gray-color); font-size: 12px;">
                            Если не отмечено, пользователь не сможет войти в систему
                        </small>
                    </div>
                </div>
                
                <div class="profile-card">
                    <div class="card-header">
                        <i class="fas fa-info-circle"></i>
                        <h2>Информация об аккаунте</h2>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Дата регистрации</div>
                        <div class="info-value"><?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Последнее обновление</div>
                        <div class="info-value"><?php echo date('d.m.Y H:i', strtotime($user['updated_at'])); ?></div>
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
                        <div class="info-label">Количество входов</div>
                        <div class="info-value"><?php echo $user['login_count']; ?></div>
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
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Сохранить изменения
                        </button>
                        <a href="view.php?id=<?php echo $user['id']; ?>" class="btn btn-outline">
                            <i class="fas fa-times"></i> Отмена
                        </a>
                        <a href="change_password.php?id=<?php echo $user['id']; ?>" class="btn btn-outline">
                            <i class="fas fa-key"></i> Сменить пароль
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </main>
</body>
</html>
