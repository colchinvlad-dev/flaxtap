<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "config/database.php";
checkAdminAuth();
$admin = getAdminData();

// Сообщения об успехе/ошибке
$message = '';
$error = '';

// Обработка смены пароля
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Валидация
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Все поля обязательны для заполнения';
    } elseif ($new_password != $confirm_password) {
        $error = 'Новые пароли не совпадают';
    } elseif (strlen($new_password) < 6) {
        $error = 'Новый пароль должен содержать минимум 6 символов';
    } else {
        // Получаем текущие данные администратора
        $admin_id = $admin['id'];
        $query = "SELECT password, last_password_change FROM users WHERE id = $admin_id AND role = 'admin'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $admin_data = mysqli_fetch_assoc($result);
            
            // Проверяем текущий пароль
            if (password_verify($current_password, $admin_data['password'])) {
                // Проверяем, когда последний раз меняли пароль (не чаще 1 раза в час)
                if ($admin_data['last_password_change']) {
                    $lastChange = strtotime($admin_data['last_password_change']);
                    $hoursSinceChange = (time() - $lastChange) / 3600;
                    
                    if ($hoursSinceChange < 1) {
                        $error = 'Вы можете менять пароль не чаще одного раза в час';
                    } else {
                        // Хешируем новый пароль
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        
                        // Обновляем пароль и время смены в БД
                        $update_query = "UPDATE users SET 
                            password = '$hashed_password', 
                            last_password_change = NOW(),
                            updated_at = NOW() 
                            WHERE id = $admin_id";
                        
                        if (mysqli_query($conn, $update_query)) {
                            $message = 'Пароль успешно изменен';
                            
                            // Запись в лог
                            $log_message = date('Y-m-d H:i:s') . " - Администратор {$admin['name']} (ID: {$admin['id']}) сменил пароль\n";
                            file_put_contents('admin_log.txt', $log_message, FILE_APPEND);
                            
                            // Обновляем данные администратора для отображения
                            $query = "SELECT * FROM users WHERE id = {$admin['id']}";
                            $result = mysqli_query($conn, $query);
                            $admin_full = mysqli_fetch_assoc($result);
                        } else {
                            $error = 'Ошибка при обновлении пароля: ' . mysqli_error($conn);
                        }
                    }
                } else {
                    // Первая смена пароля
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    $update_query = "UPDATE users SET 
                        password = '$hashed_password', 
                        last_password_change = NOW(),
                        updated_at = NOW() 
                        WHERE id = $admin_id";
                    
                    if (mysqli_query($conn, $update_query)) {
                        $message = 'Пароль успешно изменен';
                        
                        $log_message = date('Y-m-d H:i:s') . " - Администратор {$admin['name']} (ID: {$admin['id']}) сменил пароль впервые\n";
                        file_put_contents('admin_log.txt', $log_message, FILE_APPEND);
                        
                        $query = "SELECT * FROM users WHERE id = {$admin['id']}";
                        $result = mysqli_query($conn, $query);
                        $admin_full = mysqli_fetch_assoc($result);
                    } else {
                        $error = 'Ошибка при обновлении пароля: ' . mysqli_error($conn);
                    }
                }
            } else {
                $error = 'Текущий пароль указан неверно';
            }
        } else {
            $error = 'Администратор не найден';
        }
    }
}

// Получаем полную информацию об администраторе
$query = "SELECT * FROM users WHERE id = {$admin['id']}";
$result = mysqli_query($conn, $query);
$admin_full = mysqli_fetch_assoc($result);

// Проверяем и инициализируем отсутствующие поля для совместимости
if (!isset($admin_full['last_login_at'])) $admin_full['last_login_at'] = null;
if (!isset($admin_full['updated_at'])) $admin_full['updated_at'] = $admin_full['created_at'];
if (!isset($admin_full['login_count'])) $admin_full['login_count'] = 0;
if (!isset($admin_full['last_password_change'])) $admin_full['last_password_change'] = null;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль администратора - FlaxTap</title>
    <link rel="stylesheet" href="assets/style/style.css">
    <link rel="stylesheet" href="assets/style/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="assets/js/profile.js"></script>
</head>
<body>
    <?php include "inc/sidebar.php"; ?>
    
    <!-- Основное содержимое -->
    <main class="main-content">
    <?php include "inc/header.php"; ?>
        
        <!-- Сообщения -->
        <?php if ($message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Контент профиля -->
        <div class="profile-container">
            <!-- Карточка информации -->
            <div class="profile-card">
                <div class="card-header">
                    <i class="fas fa-id-card"></i>
                    <h2>Информация о профиле</h2>
                </div>
                
                <div class="info-group">
                    <div class="info-label">ID администратора</div>
                    <div class="info-value">#<?php echo $admin_full['id']; ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Имя</div>
                    <div class="info-value"><?php echo htmlspecialchars($admin_full['name']); ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($admin_full['email']); ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Телефон</div>
                    <div class="info-value"><?php echo $admin_full['telephone'] ? htmlspecialchars($admin_full['telephone']) : '<span style="color: #718096;">Не указан</span>'; ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Роль</div>
                    <div class="info-value">
                        <span style="background: #667eea; color: white; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                            <?php echo strtoupper($admin_full['role']); ?>
                        </span>
                    </div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Дата регистрации</div>
                    <div class="info-value"><?php echo date('d.m.Y H:i', strtotime($admin_full['created_at'])); ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Последний вход</div>
                    <div class="info-value">
                        <?php 
                        if ($admin_full['last_login_at']) {
                            echo date('d.m.Y H:i', strtotime($admin_full['last_login_at']));
                            echo ' <span class="last-login-time" data-time="' . $admin_full['last_login_at'] . '" style="font-size: 12px; color: #718096;">(';
                            
                            $lastLogin = strtotime($admin_full['last_login_at']);
                            $daysAgo = floor((time() - $lastLogin) / (60 * 60 * 24));
                            
                            if ($daysAgo == 0) {
                                $hoursAgo = floor((time() - $lastLogin) / 3600);
                                if ($hoursAgo == 0) {
                                    $minutesAgo = floor((time() - $lastLogin) / 60);
                                    echo $minutesAgo . ' мин. назад';
                                } else {
                                    echo $hoursAgo . ' час. назад';
                                }
                            } elseif ($daysAgo == 1) {
                                echo 'Вчера';
                            } else {
                                echo $daysAgo . ' дн. назад';
                            }
                            echo ')</span>';
                        } else {
                            echo 'Еще не входил';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Количество входов</div>
                    <div class="info-value"><?php echo $admin_full['login_count']; ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Последняя смена пароля</div>
                    <div class="info-value">
                        <?php 
                        if ($admin_full['last_password_change']) {
                            echo date('d.m.Y H:i', strtotime($admin_full['last_password_change']));
                            echo ' <span style="font-size: 12px; color: #718096;">(';
                            
                            $lastChange = strtotime($admin_full['last_password_change']);
                            $daysSinceChange = floor((time() - $lastChange) / (60 * 60 * 24));
                            
                            if ($daysSinceChange == 0) {
                                $hoursSinceChange = floor((time() - $lastChange) / 3600);
                                echo $hoursSinceChange . ' час. назад';
                            } else {
                                echo $daysSinceChange . ' дн. назад';
                            }
                            echo ')</span>';
                        } else {
                            echo 'Никогда';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Профиль обновлен</div>
                    <div class="info-value"><?php echo date('d.m.Y H:i', strtotime($admin_full['updated_at'])); ?></div>
                </div>
                
                <!-- Статистика -->
                <div class="admin-stats">
                    <div class="stat-item">
                        <div class="stat-number">#<?php echo $admin_full['id']; ?></div>
                        <div class="stat-label">ID</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $admin_full['login_count']; ?></div>
                        <div class="stat-label">Входов</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">
                            <?php
                            if ($admin_full['last_login_at']) {
                                $lastLogin = strtotime($admin_full['last_login_at']);
                                $hoursAgo = floor((time() - $lastLogin) / 3600);
                                
                                if ($hoursAgo < 1) {
                                    echo '<span class="good-stat">Сейчас</span>';
                                } elseif ($hoursAgo < 24) {
                                    echo $hoursAgo . ' час.';
                                } else {
                                    $daysAgo = floor($hoursAgo / 24);
                                    if ($daysAgo > 7) {
                                        echo '<span class="bad-stat">' . $daysAgo . ' дн.</span>';
                                    } else {
                                        echo $daysAgo . ' дн.';
                                    }
                                }
                            } else {
                                echo '<span class="bad-stat">Никогда</span>';
                            }
                            ?>
                        </div>
                        <div class="stat-label">Последний вход</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">
                            <?php
                            if ($admin_full['last_password_change']) {
                                $lastChange = strtotime($admin_full['last_password_change']);
                                $daysSinceChange = floor((time() - $lastChange) / (60 * 60 * 24));
                                
                                if ($daysSinceChange > 90) {
                                    echo '<span class="bad-stat">' . $daysSinceChange . '</span>';
                                } elseif ($daysSinceChange > 30) {
                                    echo '<span class="warning-stat">' . $daysSinceChange . '</span>';
                                } else {
                                    echo $daysSinceChange;
                                }
                            } else {
                                echo '<span class="bad-stat">∞</span>';
                            }
                            ?>
                        </div>
                        <div class="stat-label">Дней без смены пароля</div>
                    </div>
                </div>
            </div>
            
            <!-- Карточка смены пароля -->
            <div class="password-card">
                <div class="card-header">
                    <i class="fas fa-lock"></i>
                    <h2>Смена пароля</h2>
                </div>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="current_password">Текущий пароль</label>
                        <div style="position: relative;">
                            <input type="password" id="current_password" name="current_password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">Новый пароль</label>
                        <div style="position: relative;">
                            <input type="password" id="new_password" name="new_password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="strength-meter" id="passwordStrength"></div>
                        </div>
                        <div class="password-hint" id="passwordHint"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Подтвердите новый пароль</label>
                        <div style="position: relative;">
                            <input type="password" id="confirm_password" name="confirm_password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-hint" id="confirmHint"></div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="change_password" class="btn-submit">
                            <i class="fas fa-key"></i> Сменить пароль
                        </button>
                    </div>
                    
                    <div style="font-size: 12px; color: #718096; text-align: center; margin-top: 15px;">
                        <i class="fas fa-info-circle"></i> 
                        <?php 
                        if ($admin_full['last_password_change']) {
                            $lastChange = strtotime($admin_full['last_password_change']);
                            $hoursSinceChange = floor((time() - $lastChange) / 3600);
                            
                            if ($hoursSinceChange < 1) {
                                echo 'Вы можете менять пароль не чаще одного раза в час. Попробуйте через ' . (60 - floor((time() - $lastChange) / 60)) . ' минут.';
                            } else {
                                echo 'Рекомендуется использовать сложный пароль из 8+ символов с цифрами и буквами в разных регистрах.';
                            }
                        } else {
                            echo 'Рекомендуется использовать сложный пароль из 8+ символов с цифрами и буквами в разных регистрах.';
                        }
                        ?>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>