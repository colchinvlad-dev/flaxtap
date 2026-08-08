<?php
ob_start(); // Включаем буферизацию вывода
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /');
    exit;
}

include '../config/database.php';

function escape($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header('Location: /');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: /');
    exit;
}

// Получаем заказы пользователя
$orders_sql = "SELECT o.*, os.name as status_name, os.color as status_color 
               FROM orders o 
               LEFT JOIN order_statuses os ON o.status_id = os.id 
               WHERE o.user_id = ? 
               ORDER BY o.created_at DESC 
               LIMIT 10";
$orders_stmt = $connection->prepare($orders_sql);
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();

// Получаем избранные товары 
$favorites_result = null;
$total_favorites = 0;
$table_exists_sql = "SHOW TABLES LIKE 'user_favorites'";
$table_result = $connection->query($table_exists_sql);
if ($table_result->num_rows > 0) {
    $favorites_sql = "SELECT p.id, p.name, p.image_url, p.current_price, p.old_price 
                      FROM products p
                      INNER JOIN user_favorites uf ON p.id = uf.product_id
                      WHERE uf.user_id = ? AND p.in_stock = 1
                      ORDER BY uf.created_at DESC";
    $favorites_stmt = $connection->prepare($favorites_sql);
    $favorites_stmt->bind_param("i", $user_id);
    $favorites_stmt->execute();
    $favorites_result = $favorites_stmt->get_result();
    $total_favorites = $favorites_result->num_rows;
}

// Получаем отзывы пользователя
$reviews_result = null;
$total_reviews = 0;
$table_exists_sql = "SHOW TABLES LIKE 'product_reviews'";
$table_result = $connection->query($table_exists_sql);
if ($table_result->num_rows > 0) {
    $reviews_sql = "SELECT COUNT(*) as total_reviews FROM product_reviews WHERE user_id = ?";
    $reviews_stmt = $connection->prepare($reviews_sql);
    $reviews_stmt->bind_param("i", $user_id);
    $reviews_stmt->execute();
    $reviews_result = $reviews_stmt->get_result();
    $reviews_data = $reviews_result->fetch_assoc();
    $total_reviews = $reviews_data['total_reviews'];
}

// Получаем адреса доставки 
$addresses_result = null;
$table_exists_sql = "SHOW TABLES LIKE 'user_addresses'";
$table_result = $connection->query($table_exists_sql);
if ($table_result->num_rows > 0) {
    $addresses_sql = "SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC";
    $addresses_stmt = $connection->prepare($addresses_sql);
    $addresses_stmt->bind_param("i", $user_id);
    $addresses_stmt->execute();
    $addresses_result = $addresses_stmt->get_result();
}

// Получаем последние товары для "продолжить покупки"
$products_sql = "SELECT id, name, image_url, current_price, old_price 
                FROM products 
                WHERE in_stock = 1 
                AND is_featured = 1 
                ORDER BY created_at DESC 
                LIMIT 4";
$products_result = $connection->query($products_sql);

// Получаем все товары для заказа
$all_products_sql = "SELECT id, name, current_price, image_url, in_stock 
                     FROM products 
                     WHERE in_stock = 1 
                     ORDER BY name";
$all_products_result = $connection->query($all_products_sql);

// Получаем доставку и методы оплаты
$delivery_methods_sql = "SELECT * FROM delivery_methods WHERE is_active = 1 ORDER BY sort_order";
$delivery_methods_result = $connection->query($delivery_methods_sql);

$payment_methods_sql = "SELECT * FROM payment_methods WHERE is_active = 1 ORDER BY sort_order";
$payment_methods_result = $connection->query($payment_methods_sql);

// Обработка POST-запросов
$success_message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'update_profile':
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telephone = trim($_POST['telephone'] ?? '');
            
            $errors = [];
            
            if (empty($name)) $errors[] = 'Имя обязательно для заполнения';
            if (empty($email)) $errors[] = 'Email обязателен для заполнения';
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Некорректный формат email';
            if (empty($telephone)) $errors[] = 'Телефон обязателен для заполнения';
            
            if (empty($errors)) {
                // Проверяем email
                $check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
                $check_stmt = $connection->prepare($check_sql);
                $check_stmt->bind_param("si", $email, $user_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    $errors[] = 'Этот email уже используется другим пользователем';
                } else {
                    $update_sql = "UPDATE users SET name = ?, email = ?, telephone = ?, updated_at = NOW() WHERE id = ?";
                    $update_stmt = $connection->prepare($update_sql);
                    $update_stmt->bind_param("sssi", $name, $email, $telephone, $user_id);
                    
                    if ($update_stmt->execute()) {
                        $success_message = 'Данные профиля успешно обновлены';
                        $_SESSION['user_name'] = $name;
                        $user['name'] = $name;
                        $user['email'] = $email;
                        $user['telephone'] = $telephone;
                    } else {
                        $errors[] = 'Ошибка при обновлении данных';
                    }
                }
            }
            break;
            
        case 'change_password':
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            $errors = [];
            
            if (empty($current_password)) $errors[] = 'Текущий пароль обязателен';
            if (empty($new_password)) $errors[] = 'Новый пароль обязателен';
            elseif (strlen($new_password) < 6) $errors[] = 'Новый пароль должен содержать минимум 6 символов';
            if ($new_password !== $confirm_password) $errors[] = 'Пароли не совпадают';
            
            if (empty($errors)) {
                if (password_verify($current_password, $user['password'])) {
                    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_sql = "UPDATE users SET password = ?, last_password_change = NOW(), updated_at = NOW() WHERE id = ?";
                    $update_stmt = $connection->prepare($update_sql);
                    $update_stmt->bind_param("si", $new_password_hash, $user_id);
                    
                    if ($update_stmt->execute()) {
                        $success_message = 'Пароль успешно изменен';
                    } else {
                        $errors[] = 'Ошибка при изменении пароля';
                    }
                } else {
                    $errors[] = 'Неверный текущий пароль';
                }
            }
            break;
            
        case 'add_address':
            // Создаем таблицу если ее нет
            $create_table_sql = "CREATE TABLE IF NOT EXISTS user_addresses (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                title VARCHAR(100) NOT NULL,
                address TEXT NOT NULL,
                city VARCHAR(100) NOT NULL,
                postcode VARCHAR(20),
                phone VARCHAR(20) NOT NULL,
                notes TEXT,
                is_default BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )";
            $connection->query($create_table_sql);
            
            $title = trim($_POST['address_title'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $postcode = trim($_POST['postcode'] ?? '');
            $phone = trim($_POST['address_phone'] ?? '');
            $notes = trim($_POST['notes'] ?? '');
            $is_default = isset($_POST['is_default']) ? 1 : 0;
            
            $errors = [];
            
            if (empty($title)) $errors[] = 'Название адреса обязательно';
            if (empty($address)) $errors[] = 'Адрес обязателен';
            if (empty($city)) $errors[] = 'Город обязателен';
            if (empty($phone)) $errors[] = 'Телефон обязателен';
            
            if (empty($errors)) {
                if ($is_default) {
                    $reset_sql = "UPDATE user_addresses SET is_default = 0 WHERE user_id = ?";
                    $reset_stmt = $connection->prepare($reset_sql);
                    $reset_stmt->bind_param("i", $user_id);
                    $reset_stmt->execute();
                }
                
                $insert_sql = "INSERT INTO user_addresses (user_id, title, address, city, postcode, phone, notes, is_default) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $insert_stmt = $connection->prepare($insert_sql);
                $insert_stmt->bind_param("issssssi", $user_id, $title, $address, $city, $postcode, $phone, $notes, $is_default);
                
                if ($insert_stmt->execute()) {
                    $success_message = 'Адрес успешно добавлен';
                    // Обновляем список адресов
                    $addresses_stmt = $connection->prepare($addresses_sql);
                    $addresses_stmt->bind_param("i", $user_id);
                    $addresses_stmt->execute();
                    $addresses_result = $addresses_stmt->get_result();
                } else {
                    $errors[] = 'Ошибка при добавлении адреса';
                }
            }
            break;
            
        case 'create_order':
            // Создание заказа
            $items = $_POST['items'] ?? [];
            $delivery_method_id = $_POST['delivery_method_id'] ?? '';
            $payment_method_id = $_POST['payment_method_id'] ?? '';
            $delivery_notes = $_POST['delivery_notes'] ?? '';
            $customer_notes = $_POST['customer_notes'] ?? '';
            
            $errors = [];
            
            if (empty($items)) $errors[] = 'Добавьте товары в заказ';
            if (empty($delivery_method_id)) $errors[] = 'Выберите способ доставки';
            if (empty($payment_method_id)) $errors[] = 'Выберите способ оплаты';
            
            if (empty($errors)) {
                $connection->begin_transaction();
                
                try {
                    // Рассчитываем суммы
                    $subtotal = 0;
                    $order_items = [];
                    
                    foreach ($items as $item) {
                        $product_id = $item['product_id'];
                        $quantity = (int)$item['quantity'];
                        
                        $product_sql = "SELECT id, name, current_price FROM products WHERE id = ?";
                        $product_stmt = $connection->prepare($product_sql);
                        $product_stmt->bind_param("i", $product_id);
                        $product_stmt->execute();
                        $product_result = $product_stmt->get_result();
                        $product = $product_result->fetch_assoc();
                        
                        if (!$product) {
                            throw new Exception("Товар не найден");
                        }
                        
                        $item_total = $product['current_price'] * $quantity;
                        $subtotal += $item_total;
                        
                        $order_items[] = [
                            'product' => $product,
                            'quantity' => $quantity,
                            'total' => $item_total
                        ];
                    }
                    
                    // Стоимость доставки
                    $delivery_sql = "SELECT price, free_threshold FROM delivery_methods WHERE id = ?";
                    $delivery_stmt = $connection->prepare($delivery_sql);
                    $delivery_stmt->bind_param("i", $delivery_method_id);
                    $delivery_stmt->execute();
                    $delivery_result = $delivery_stmt->get_result();
                    $delivery = $delivery_result->fetch_assoc();
                    
                    $delivery_price = $delivery['price'];
                    if ($delivery['free_threshold'] && $subtotal >= $delivery['free_threshold']) {
                        $delivery_price = 0;
                    }
                    
                    $total_amount = $subtotal + $delivery_price;
                    
                    // Создаем заказ
                    $order_sql = "INSERT INTO orders (
                        user_id, status_id, payment_method_id, delivery_method_id,
                        customer_name, customer_email, customer_phone,
                        subtotal, delivery_price, total_amount,
                        customer_notes
                    ) VALUES (?, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $order_stmt = $connection->prepare($order_sql);
                    $order_stmt->bind_param(
                        "iiisssddds",
                        $user_id,
                        $payment_method_id,
                        $delivery_method_id,
                        $user['name'],
                        $user['email'],
                        $user['telephone'],
                        $subtotal,
                        $delivery_price,
                        $total_amount,
                        $customer_notes
                    );
                    
                    $order_stmt->execute();
                    $order_id = $connection->insert_id;
                    
                    // Добавляем товары
                    foreach ($order_items as $item) {
                        $item_sql = "INSERT INTO order_items (
                            order_id, product_id, product_name, product_price, quantity, subtotal
                        ) VALUES (?, ?, ?, ?, ?, ?)";
                        
                        $item_stmt = $connection->prepare($item_sql);
                        $item_stmt->bind_param(
                            "iisddd",
                            $order_id,
                            $item['product']['id'],
                            $item['product']['name'],
                            $item['product']['current_price'],
                            $item['quantity'],
                            $item['total']
                        );
                        
                        $item_stmt->execute();
                    }
                    
                    // История статусов
                    $history_sql = "INSERT INTO order_status_history (order_id, new_status_id, change_reason) 
                                    VALUES (?, 1, 'Заказ создан пользователем')";
                    $history_stmt = $connection->prepare($history_sql);
                    $history_stmt->bind_param("i", $order_id);
                    $history_stmt->execute();
                    
                    $connection->commit();
                    $success_message = 'Заказ успешно создан! Номер заказа: ORD-' . $order_id;
                    
                } catch (Exception $e) {
                    $connection->rollback();
                    $errors[] = 'Ошибка при создании заказа: ' . $e->getMessage();
                }
            }
            break;
    }
}

// Определяем активную вкладку из GET или localStorage
$active_tab = $_GET['tab'] ?? 'profile';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="../assets/style/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include "../inc/header.php"; ?>

    <div class="profile-container">
        <!-- Боковая панель -->
        <div class="profile-sidebar">
            <div class="user-card">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-info">
                    <h3 class="user-name"><?php echo escape($user['name']); ?></h3>
                    <p class="user-email"><?php echo escape($user['email']); ?></p>
                    <p class="user-phone"><?php echo escape($user['telephone']); ?></p>
                </div>
            </div>
            
            <nav class="profile-nav">
                <a href="#profile" class="nav-item <?php echo $active_tab === 'profile' ? 'active' : ''; ?>" data-tab="profile">
                    <i class="fas fa-user"></i>
                    <span>Мой профиль</span>
                </a>
                <a href="#orders" class="nav-item <?php echo $active_tab === 'orders' ? 'active' : ''; ?>" data-tab="orders">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Мои заказы</span>
                    <?php if ($orders_result->num_rows > 0): ?>
                        <span class="badge"><?php echo $orders_result->num_rows; ?></span>
                    <?php endif; ?>
                </a>
                <a href="#favorites" class="nav-item <?php echo $active_tab === 'favorites' ? 'active' : ''; ?>" data-tab="favorites">
                    <i class="far fa-heart"></i>
                    <span>Избранное</span>
                    <?php if ($total_favorites > 0): ?>
                        <span class="badge"><?php echo $total_favorites; ?></span>
                    <?php endif; ?>
                </a>
                <a href="#addresses" class="nav-item <?php echo $active_tab === 'addresses' ? 'active' : ''; ?>" data-tab="addresses">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Адреса доставки</span>
                </a>
                <a href="#reviews" class="nav-item <?php echo $active_tab === 'reviews' ? 'active' : ''; ?>" data-tab="reviews">
                    <i class="far fa-star"></i>
                    <span>Мои отзывы</span>
                    <?php if ($total_reviews > 0): ?>
                        <span class="badge"><?php echo $total_reviews; ?></span>
                    <?php endif; ?>
                </a>
                <a href="#settings" class="nav-item <?php echo $active_tab === 'settings' ? 'active' : ''; ?>" data-tab="settings">
                    <i class="fas fa-cog"></i>
                    <span>Настройки</span>
                </a>
                <a href="?action=logout" class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Выйти</span>
                </a>
            </nav>
            
            <div class="profile-stats">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $orders_result->num_rows; ?></div>
                    <div class="stat-label">заказов</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_reviews; ?></div>
                    <div class="stat-label">отзывов</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_favorites; ?></div>
                    <div class="stat-label">избранных</div>
                </div>
            </div>
        </div>

        <!-- Основной контент -->
        <div class="profile-content">
            <!-- Сообщения -->
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Вкладка профиля -->
            <div class="tab-content <?php echo $active_tab === 'profile' ? 'active' : ''; ?>" id="profileTab">
                <div class="tab-header">
                    <h2 class="tab-title">Мой профиль</h2>
                    <button class="btn btn-primary" id="editProfileBtn">
                        <i class="fas fa-edit"></i> Редактировать
                    </button>
                </div>
                
                <!-- Форма редактирования (скрыта по умолчанию) -->
                <div class="form-section" id="editProfileForm" style="display: none;">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-group">
                            <label for="name">Имя *</label>
                            <input type="text" id="name" name="name" value="<?php echo escape($user['name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" value="<?php echo escape($user['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="telephone">Телефон *</label>
                            <input type="tel" id="telephone" name="telephone" value="<?php echo escape($user['telephone']); ?>" required>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                            <button type="button" class="btn btn-secondary" id="cancelEditProfile">Отмена</button>
                        </div>
                    </form>
                </div>
                
                <!-- Информация профиля -->
                <div id="profileInfo">
                    <div class="form-section">
                        <div class="info-header">
                            <i class="fas fa-user-circle"></i>
                            <h3>Личная информация</h3>
                        </div>
                        <div class="info-body">
                            <div class="info-row">
                                <span class="label">Имя:</span>
                                <span class="value"><?php echo escape($user['name']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="label">Email:</span>
                                <span class="value"><?php echo escape($user['email']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="label">Телефон:</span>
                                <span class="value"><?php echo escape($user['telephone']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="label">Дата регистрации:</span>
                                <span class="value"><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="label">Последний вход:</span>
                                <span class="value">
                                    <?php echo $user['last_login_at'] ? date('d.m.Y H:i', strtotime($user['last_login_at'])) : 'Никогда'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <div class="info-header">
                            <i class="fas fa-shield-alt"></i>
                            <h3>Безопасность</h3>
                        </div>
                        <div class="info-body">
                            <div class="info-row">
                                <span class="label">Смена пароля:</span>
                                <button class="btn btn-secondary btn-small" id="changePasswordBtn">
                                    Изменить пароль
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Форма смены пароля -->
                    <div class="form-section" id="changePasswordForm" style="display: none;">
                        <form method="POST">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="form-group">
                                <label for="current_password">Текущий пароль *</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">Новый пароль *</label>
                                <input type="password" id="new_password" name="new_password" required minlength="6">
                                <small>Минимум 6 символов</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Подтвердите пароль *</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Изменить пароль</button>
                                <button type="button" class="btn btn-secondary" id="cancelChangePassword">Отмена</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Вкладка заказов -->
            <div class="tab-content <?php echo $active_tab === 'orders' ? 'active' : ''; ?>" id="ordersTab">
                <div class="tab-header">
                    <h2 class="tab-title">Мои заказы</h2>
                    <button class="btn btn-primary" id="newOrderBtn">
                        <i class="fas fa-plus"></i> Новый заказ
                    </button>
                </div>
                
                <!-- Форма нового заказа -->
                <div class="form-section" id="newOrderForm" style="display: none;">
                    <form method="POST" id="createOrderForm">
                        <input type="hidden" name="action" value="create_order">
                        
                        <h3><i class="fas fa-shopping-cart"></i> Товары в заказе</h3>
                        <div id="orderItemsContainer">
                            <div class="order-item form-row">
                                <div class="form-group">
                                    <label>Товар</label>
                                    <select name="items[0][product_id]" class="product-select" required>
                                        <option value="">Выберите товар</option>
                                        <?php while ($product = $all_products_result->fetch_assoc()): ?>
                                            <option value="<?php echo $product['id']; ?>" data-price="<?php echo $product['current_price']; ?>">
                                                <?php echo escape($product['name']); ?> - <?php echo $product['current_price']; ?> ₽
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Количество</label>
                                    <input type="number" name="items[0][quantity]" value="1" min="1" class="quantity-input" required>
                                </div>
                                <div class="form-group">
                                    <label>Цена</label>
                                    <input type="text" class="price-input" value="0 ₽" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Сумма</label>
                                    <input type="text" class="subtotal-input" value="0 ₽" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-secondary btn-small" id="addOrderItem">
                            <i class="fas fa-plus"></i> Добавить товар
                        </button>
                        
                        <h3><i class="fas fa-truck"></i> Доставка</h3>
                        <div class="form-group">
                            <label>Способ доставки *</label>
                            <select name="delivery_method_id" id="deliveryMethod" required>
                                <option value="">Выберите способ доставки</option>
                                <?php while ($delivery = $delivery_methods_result->fetch_assoc()): ?>
                                    <option value="<?php echo $delivery['id']; ?>" 
                                            data-price="<?php echo $delivery['price']; ?>"
                                            data-free="<?php echo $delivery['free_threshold']; ?>">
                                        <?php echo escape($delivery['name']); ?> - <?php echo $delivery['price']; ?> ₽
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Комментарий к доставке</label>
                            <textarea name="delivery_notes" rows="2"></textarea>
                        </div>
                        
                        <h3><i class="fas fa-credit-card"></i> Оплата</h3>
                        <div class="form-group">
                            <label>Способ оплаты *</label>
                            <select name="payment_method_id" required>
                                <option value="">Выберите способ оплаты</option>
                                <?php 
                                $payment_methods_result->data_seek(0);
                                while ($payment = $payment_methods_result->fetch_assoc()): ?>
                                    <option value="<?php echo $payment['id']; ?>">
                                        <?php echo escape($payment['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Комментарий к заказу</label>
                            <textarea name="customer_notes" rows="3"></textarea>
                        </div>
                        
                        <div class="order-summary">
                            <h4>Итого</h4>
                            <div class="summary-row">
                                <span>Товары:</span>
                                <span id="itemsTotal">0 ₽</span>
                            </div>
                            <div class="summary-row">
                                <span>Доставка:</span>
                                <span id="deliveryCost">0 ₽</span>
                            </div>
                            <div class="summary-row total">
                                <span>К оплате:</span>
                                <span id="orderTotal">0 ₽</span>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Создать заказ</button>
                            <button type="button" class="btn btn-secondary" id="cancelNewOrder">Отмена</button>
                        </div>
                    </form>
                </div>
                
                <!-- Список заказов -->
                <?php if ($orders_result->num_rows > 0): ?>
                    <div class="orders-list">
                        <?php while ($order = $orders_result->fetch_assoc()): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <span class="order-number"><?php echo escape($order['order_number']); ?></span>
                                    <span class="order-date"><?php echo date('d.m.Y', strtotime($order['created_at'])); ?></span>
                                </div>
                                <div class="order-status">
                                    <span class="status-badge" style="background-color: <?php echo escape($order['status_color']); ?>">
                                        <?php echo escape($order['status_name']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="order-summary">
                                <div class="summary-item">
                                    <span class="label">Товары:</span>
                                    <span class="value"><?php echo number_format($order['subtotal'], 0, '.', ' '); ?> ₽</span>
                                </div>
                                <div class="summary-item">
                                    <span class="label">Доставка:</span>
                                    <span class="value"><?php echo number_format($order['delivery_price'], 0, '.', ' '); ?> ₽</span>
                                </div>
                                <div class="summary-item">
                                    <span class="label">Итого:</span>
                                    <span class="value total"><?php echo number_format($order['total_amount'], 0, '.', ' '); ?> ₽</span>
                                </div>
                            </div>
                            
                            <div class="order-actions">
                                <button class="btn btn-secondary" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                    <i class="fas fa-eye"></i> Подробнее
                                </button>
                                <?php if ($order['status_id'] == 1): ?>
                                    <button class="btn btn-primary" onclick="payOrder(<?php echo $order['id']; ?>)">
                                        <i class="fas fa-credit-card"></i> Оплатить
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <h3>У вас пока нет заказов</h3>
                        <p>Сделайте свой первый заказ и он появится здесь</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Вкладка избранного -->
            <div class="tab-content <?php echo $active_tab === 'favorites' ? 'active' : ''; ?>" id="favoritesTab">
                <div class="tab-header">
                    <h2 class="tab-title">Избранные товары</h2>
                    <a href="/catalog.html" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Добавить товары
                    </a>
                </div>
                
                <?php if ($favorites_result && $favorites_result->num_rows > 0): ?>
                    <div class="favorites-grid">
                        <?php while ($product = $favorites_result->fetch_assoc()): ?>
                        <div class="product-card-mini">
                            <div class="product-image">
                                <img src="<?php echo escape($product['image_url']); ?>" alt="<?php echo escape($product['name']); ?>">
                            </div>
                            <div class="product-info">
                                <h4 class="product-title"><?php echo escape($product['name']); ?></h4>
                                <div class="product-price">
                                    <span class="current-price"><?php echo number_format($product['current_price'], 0, '.', ' '); ?> ₽</span>
                                    <?php if (!empty($product['old_price']) && $product['old_price'] > $product['current_price']): ?>
                                        <span class="old-price"><?php echo number_format($product['old_price'], 0, '.', ' '); ?> ₽</span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-actions">
                                    <button class="btn btn-primary btn-small" onclick="addToCart(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-cart-plus"></i> В корзину
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="far fa-heart"></i>
                        </div>
                        <h3>Список избранного пуст</h3>
                        <p>Добавляйте товары в избранное, чтобы не потерять</p>
                        <a href="/catalog.html" class="btn btn-primary">Перейти в каталог</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Вкладка адресов -->
            <div class="tab-content <?php echo $active_tab === 'addresses' ? 'active' : ''; ?>" id="addressesTab">
                <div class="tab-header">
                    <h2 class="tab-title">Адреса доставки</h2>
                    <button class="btn btn-primary" id="addAddressBtn">
                        <i class="fas fa-plus"></i> Добавить адрес
                    </button>
                </div>
                
                <!-- Форма добавления адреса -->
                <div class="form-section" id="addAddressForm" style="display: none;">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_address">
                        
                        <div class="form-group">
                            <label for="address_title">Название адреса *</label>
                            <input type="text" id="address_title" name="address_title" placeholder="Дом, Работа..." required>
                        </div>
                        
                        <div class="form-group">
                            <label for="city">Город *</label>
                            <input type="text" id="city" name="city" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Адрес *</label>
                            <textarea id="address" name="address" rows="3" required></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="postcode">Индекс</label>
                                <input type="text" id="postcode" name="postcode">
                            </div>
                            <div class="form-group">
                                <label for="address_phone">Телефон *</label>
                                <input type="tel" id="address_phone" name="address_phone" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Комментарий для курьера</label>
                            <textarea id="notes" name="notes" rows="2"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="is_default" value="1">
                                <span>Сделать адресом по умолчанию</span>
                            </label>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Добавить адрес</button>
                            <button type="button" class="btn btn-secondary" id="cancelAddAddress">Отмена</button>
                        </div>
                    </form>
                </div>
                
                <?php if ($addresses_result && $addresses_result->num_rows > 0): ?>
                    <div class="addresses-list">
                        <?php while ($address = $addresses_result->fetch_assoc()): ?>
                        <div class="address-card">
                            <div class="address-header">
                                <h4><?php echo escape($address['title']); ?></h4>
                                <div class="address-actions">
                                    <button class="btn-icon" onclick="editAddress(<?php echo $address['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon" onclick="deleteAddress(<?php echo $address['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="address-body">
                                <p><i class="fas fa-map-marker-alt"></i> <?php echo escape($address['city']); ?>, <?php echo escape($address['address']); ?></p>
                                <?php if ($address['postcode']): ?>
                                    <p><i class="fas fa-mail-bulk"></i> <?php echo escape($address['postcode']); ?></p>
                                <?php endif; ?>
                                <p><i class="fas fa-phone"></i> <?php echo escape($address['phone']); ?></p>
                                <?php if ($address['notes']): ?>
                                    <p><i class="fas fa-sticky-note"></i> <?php echo escape($address['notes']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="address-footer">
                                <?php if ($address['is_default']): ?>
                                    <span class="tag default">Адрес по умолчанию</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3>У вас пока нет сохраненных адресов</h3>
                        <p>Добавьте адрес для быстрого оформления заказов</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Вкладка отзывов -->
            <div class="tab-content <?php echo $active_tab === 'reviews' ? 'active' : ''; ?>" id="reviewsTab">
                <div class="tab-header">
                    <h2 class="tab-title">Мои отзывы</h2>
                </div>
                
                <?php 
                if ($total_reviews > 0): 
                    // Получаем отзывы с информацией о товарах
                    $user_reviews_sql = "SELECT pr.*, p.name as product_name, p.image_url as product_image 
                                         FROM product_reviews pr
                                         JOIN products p ON pr.product_id = p.id
                                         WHERE pr.user_id = ?
                                         ORDER BY pr.created_at DESC";
                    $user_reviews_stmt = $connection->prepare($user_reviews_sql);
                    $user_reviews_stmt->bind_param("i", $user_id);
                    $user_reviews_stmt->execute();
                    $user_reviews_result = $user_reviews_stmt->get_result();
                ?>
                    <div class="orders-list">
                        <?php while ($review = $user_reviews_result->fetch_assoc()): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <h4><?php echo escape($review['product_name']); ?></h4>
                                    <div class="review-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <span class="order-date"><?php echo date('d.m.Y', strtotime($review['created_at'])); ?></span>
                            </div>
                            
                            <?php if (!empty($review['title'])): ?>
                                <h5><?php echo escape($review['title']); ?></h5>
                            <?php endif; ?>
                            
                            <?php if (!empty($review['comment'])): ?>
                                <p><?php echo nl2br(escape($review['comment'])); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="far fa-star"></i>
                        </div>
                        <h3>У вас пока нет отзывов</h3>
                        <p>Оставьте отзыв о товаре после покупки</p>
                        <a href="/catalog.html" class="btn btn-primary">Перейти к покупкам</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Вкладка настроек -->
            <div class="tab-content <?php echo $active_tab === 'settings' ? 'active' : ''; ?>" id="settingsTab">
                <div class="tab-header">
                    <h2 class="tab-title">Настройки аккаунта</h2>
                </div>
                
                <div class="form-section">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_settings">
                        
                        <h3><i class="fas fa-bell"></i> Уведомления</h3>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="email_notifications" value="1">
                                <span>Email-уведомления о заказах</span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="promo_notifications" value="1">
                                <span>Уведомления о скидках и акциях</span>
                            </label>
                        </div>
                        
                        <h3><i class="fas fa-globe"></i> Язык и регион</h3>
                        <div class="form-group">
                            <label for="language">Язык:</label>
                            <select id="language" name="language">
                                <option value="ru">Русский</option>
                                <option value="en">English</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="currency">Валюта:</label>
                            <select id="currency" name="currency">
                                <option value="RUB">Рубли (₽)</option>
                                <option value="USD">Доллары ($)</option>
                                <option value="EUR">Евро (€)</option>
                            </select>
                        </div>
                        
                        <h3><i class="fas fa-database"></i> Конфиденциальность</h3>
                        <div class="form-group">
                            <button type="button" class="btn btn-secondary" id="exportDataBtn">
                                <i class="fas fa-download"></i> Экспорт данных
                            </button>
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-danger" id="deleteAccountBtn">
                                <i class="fas fa-trash"></i> Удалить аккаунт
                            </button>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                            <button type="button" class="btn btn-secondary" id="resetSettings">Сбросить</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Рекомендации -->
            <div class="recommendations-section">
                <h3 class="section-title">Продолжить покупки</h3>
                <?php if ($products_result->num_rows > 0): ?>
                    <div class="recommendations-grid">
                        <?php while ($product = $products_result->fetch_assoc()): ?>
                        <div class="product-card-mini">
                            <div class="product-image">
                                <img src="<?php echo escape($product['image_url']); ?>" alt="<?php echo escape($product['name']); ?>">
                            </div>
                            <div class="product-info">
                                <h4 class="product-title"><?php echo escape($product['name']); ?></h4>
                                <div class="product-price">
                                    <span class="current-price"><?php echo number_format($product['current_price'], 0, '.', ' '); ?> ₽</span>
                                    <?php if (!empty($product['old_price']) && $product['old_price'] > $product['current_price']): ?>
                                        <span class="old-price"><?php echo number_format($product['old_price'], 0, '.', ' '); ?> ₽</span>
                                    <?php endif; ?>
                                </div>
                                <button class="btn btn-primary btn-small" onclick="addToCart(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-cart-plus"></i> В корзину
                                </button>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include "../inc/modal.php"; ?>
    <?php include "../inc/footer.php"; ?>

    <script src="../assets/js/profile.js"></script>
</body>
</html>