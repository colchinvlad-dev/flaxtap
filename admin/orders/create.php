<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

$error = '';
$success = '';

// Получаем статусы, методы оплаты и доставки
$statuses_query = "SELECT * FROM order_statuses ORDER BY sort_order";
$statuses_result = mysqli_query($conn, $statuses_query);

$payments_query = "SELECT * FROM payment_methods WHERE is_active = 1 ORDER BY sort_order";
$payments_result = mysqli_query($conn, $payments_query);

$deliveries_query = "SELECT * FROM delivery_methods WHERE is_active = 1 ORDER BY sort_order";
$deliveries_result = mysqli_query($conn, $deliveries_query);

// Получаем товары для выбора
$products_query = "SELECT id, name, current_price, image_url FROM products WHERE in_stock = 1 ORDER BY name";
$products_result = mysqli_query($conn, $products_query);

// Получаем пользователей
$users_query = "SELECT id, name, email FROM users WHERE is_active = 1 ORDER BY name";
$users_result = mysqli_query($conn, $users_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Основные данные
    $user_id = (int)$_POST['user_id'];
    $status_id = (int)$_POST['status_id'];
    $payment_method_id = (int)$_POST['payment_method_id'];
    $delivery_method_id = (int)$_POST['delivery_method_id'];
    $customer_name = mysqli_real_escape_string($conn, trim($_POST['customer_name']));
    $customer_email = mysqli_real_escape_string($conn, trim($_POST['customer_email']));
    $customer_phone = mysqli_real_escape_string($conn, trim($_POST['customer_phone']));
    $delivery_address = mysqli_real_escape_string($conn, trim($_POST['delivery_address']));
    $delivery_city = mysqli_real_escape_string($conn, trim($_POST['delivery_city']));
    $delivery_postcode = mysqli_real_escape_string($conn, trim($_POST['delivery_postcode']));
    $delivery_notes = mysqli_real_escape_string($conn, trim($_POST['delivery_notes']));
    $customer_notes = mysqli_real_escape_string($conn, trim($_POST['customer_notes']));
    
    // Товары
    $product_ids = $_POST['product_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $prices = $_POST['price'] ?? [];
    
    // Проверка обязательных полей
    if (empty($customer_name) || empty($customer_email) || empty($customer_phone)) {
        $error = 'Заполните обязательные поля: ФИО, Email и Телефон';
    } elseif (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Некорректный формат email';
    } elseif (empty($product_ids)) {
        $error = 'Добавьте хотя бы один товар в заказ';
    } else {
        // Рассчитываем стоимость
        $subtotal = 0;
        $items = [];
        
        for ($i = 0; $i < count($product_ids); $i++) {
            $product_id = (int)$product_ids[$i];
            $quantity = (int)$quantities[$i];
            $price = (float)$prices[$i];
            
            if ($product_id > 0 && $quantity > 0) {
                $item_total = $price * $quantity;
                $subtotal += $item_total;
                
                $items[] = [
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $item_total
                ];
            }
        }
        
        // Получаем цену доставки
        $delivery_price = 0;
        if ($delivery_method_id) {
            $delivery_query = "SELECT price FROM delivery_methods WHERE id = $delivery_method_id";
            $delivery_res = mysqli_query($conn, $delivery_query);
            $delivery = mysqli_fetch_assoc($delivery_res);
            $delivery_price = $delivery['price'] ?? 0;
        }
        
        $total_amount = $subtotal + $delivery_price;
        
        // Начинаем транзакцию
        mysqli_begin_transaction($conn);
        
        try {
            // Вставляем заказ
            $insert_query = "INSERT INTO orders (
                user_id, status_id, payment_method_id, delivery_method_id,
                customer_name, customer_email, customer_phone,
                delivery_address, delivery_city, delivery_postcode, delivery_notes,
                subtotal, delivery_price, total_amount,
                customer_notes, created_at, updated_at
            ) VALUES (
                " . ($user_id ? $user_id : "NULL") . ",
                $status_id,
                " . ($payment_method_id ? $payment_method_id : "NULL") . ",
                " . ($delivery_method_id ? $delivery_method_id : "NULL") . ",
                '$customer_name',
                '$customer_email',
                '$customer_phone',
                '$delivery_address',
                '$delivery_city',
                '$delivery_postcode',
                '$delivery_notes',
                $subtotal,
                $delivery_price,
                $total_amount,
                '$customer_notes',
                NOW(),
                NOW()
            )";
            
            if (!mysqli_query($conn, $insert_query)) {
                throw new Exception('Ошибка при создании заказа: ' . mysqli_error($conn));
            }
            
            $new_order_id = mysqli_insert_id($conn);
            
            // Вставляем товары
            foreach ($items as $item) {
                // Получаем информацию о товаре
                $product_query = "SELECT name, image_url FROM products WHERE id = {$item['product_id']}";
                $product_res = mysqli_query($conn, $product_query);
                $product = mysqli_fetch_assoc($product_res);
                
                $insert_item = "INSERT INTO order_items (
                    order_id, product_id, product_name, product_price,
                    quantity, subtotal, product_image
                ) VALUES (
                    $new_order_id,
                    {$item['product_id']},
                    '{$product['name']}',
                    {$item['price']},
                    {$item['quantity']},
                    {$item['total']},
                    '{$product['image_url']}'
                )";
                
                if (!mysqli_query($conn, $insert_item)) {
                    throw new Exception('Ошибка при добавлении товара: ' . mysqli_error($conn));
                }
            }
            
            // Добавляем запись в историю статусов
            $admin_id = $_SESSION['admin_id'] ?? 1;
            $history_query = "INSERT INTO order_status_history 
                            (order_id, new_status_id, changed_by, change_reason) 
                            VALUES ($new_order_id, $status_id, $admin_id, 'Заказ создан администратором')";
            mysqli_query($conn, $history_query);
            
            // Фиксируем транзакцию
            mysqli_commit($conn);
            
            // Логируем действие
            $log_message = date('Y-m-d H:i:s') . " - Администратор {$_SESSION['user_name']} создал новый заказ (ID: $new_order_id) для $customer_name\n";
            file_put_contents('../admin_log.txt', $log_message, FILE_APPEND);
            
            $success = 'Заказ успешно создан! Номер заказа будет присвоен автоматически.';
            
            // Редирект на просмотр заказа
            $_SESSION['success'] = $success;
            header("Location: view.php?id=$new_order_id");
            exit();
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание заказа - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style/orders/create.css">
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Создание нового заказа</h1>
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Назад к списку
            </a>
        </div>
        
        <div class="create-container">
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="orderForm">
                <!-- Клиент -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-user"></i>
                        <h2>Информация о клиенте</h2>
                    </div>
                    
                    <div class="form-group user-selector">
                        <label class="form-label">Выберите пользователя (необязательно)</label>
                        <input type="text" id="userSearch" class="user-search" placeholder="Поиск по имени или email">
                        <input type="hidden" id="user_id" name="user_id" value="">
                        <div class="user-results" id="userResults"></div>
                        <small style="display: block; margin-top: 5px; color: var(--gray-color);">
                            Оставьте пустым для заказа без регистрации
                        </small>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="customer_name" class="form-label required">ФИО</label>
                            <input type="text" id="customer_name" name="customer_name" 
                                   class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_phone" class="form-label required">Телефон</label>
                            <input type="tel" id="customer_phone" name="customer_phone" 
                                   class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_email" class="form-label required">Email</label>
                        <input type="email" id="customer_email" name="customer_email" 
                               class="form-control" required>
                    </div>
                </div>
                
                <!-- Доставка -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-truck"></i>
                        <h2>Доставка</h2>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="delivery_method_id" class="form-label">Способ доставки</label>
                            <select id="delivery_method_id" name="delivery_method_id" class="form-control">
                                <option value="">Не выбран</option>
                                <?php while($delivery = mysqli_fetch_assoc($deliveries_result)): ?>
                                    <option value="<?php echo $delivery['id']; ?>" data-price="<?php echo $delivery['price']; ?>">
                                        <?php echo htmlspecialchars($delivery['name']); ?> 
                                        (<?php echo number_format($delivery['price'], 0, ',', ' '); ?> ₽)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="delivery_city" class="form-label">Город</label>
                            <input type="text" id="delivery_city" name="delivery_city" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="delivery_address" class="form-label">Адрес доставки</label>
                        <textarea id="delivery_address" name="delivery_address" 
                                  class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="delivery_postcode" class="form-label">Почтовый индекс</label>
                            <input type="text" id="delivery_postcode" name="delivery_postcode" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="delivery_notes" class="form-label">Примечания к доставке</label>
                            <input type="text" id="delivery_notes" name="delivery_notes" class="form-control">
                        </div>
                    </div>
                </div>
                
                <!-- Товары -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-shopping-cart"></i>
                        <h2>Товары</h2>
                    </div>
                    
                    <table class="items-table" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th>Товар</th>
                                <th style="width: 120px;">Цена</th>
                                <th style="width: 100px;">Кол-во</th>
                                <th style="width: 120px;">Сумма</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <!-- Товары будут добавляться динамически -->
                        </tbody>
                    </table>
                    
                    <button type="button" class="btn-add-item" onclick="addItem()">
                        <i class="fas fa-plus"></i> Добавить товар
                    </button>
                    
                    <!-- Сводка -->
                    <div class="summary-box">
                        <div class="summary-item">
                            <span>Стоимость товаров:</span>
                            <span id="subtotal">0</span> ₽
                        </div>
                        <div class="summary-item">
                            <span>Доставка:</span>
                            <span id="deliveryCost">0</span> ₽
                        </div>
                        <div class="summary-item summary-total">
                            <span>Итого к оплате:</span>
                            <span id="totalAmount">0</span> ₽
                        </div>
                    </div>
                </div>
                
                <!-- Настройки -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-cog"></i>
                        <h2>Настройки заказа</h2>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="status_id" class="form-label required">Статус заказа</label>
                            <select id="status_id" name="status_id" class="form-control" required>
                                <?php mysqli_data_seek($statuses_result, 0); ?>
                                <?php while($status = mysqli_fetch_assoc($statuses_result)): ?>
                                    <option value="<?php echo $status['id']; ?>">
                                        <?php echo htmlspecialchars($status['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_method_id" class="form-label">Способ оплаты</label>
                            <select id="payment_method_id" name="payment_method_id" class="form-control">
                                <option value="">Не выбран</option>
                                <?php mysqli_data_seek($payments_result, 0); ?>
                                <?php while($payment = mysqli_fetch_assoc($payments_result)): ?>
                                    <option value="<?php echo $payment['id']; ?>">
                                        <?php echo htmlspecialchars($payment['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_notes" class="form-label">Комментарий клиента</label>
                        <textarea id="customer_notes" name="customer_notes" 
                                  class="form-control" rows="3"></textarea>
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Создать заказ
                    </button>
                    <button type="reset" class="btn btn-outline">
                        <i class="fas fa-redo"></i> Очистить форму
                    </button>
                </div>
            </form>
        </div>
    </main>
    
    <script>
        // Массив товаров для выбора
        const products = [
            <?php 
            mysqli_data_seek($products_result, 0);
            while($product = mysqli_fetch_assoc($products_result)): 
            ?>
            {
                id: <?php echo $product['id']; ?>,
                name: '<?php echo addslashes($product['name']); ?>',
                price: <?php echo $product['current_price']; ?>,
                image: '<?php echo $product['image_url']; ?>'
            },
            <?php endwhile; ?>
        ];
        
        // Массив пользователей
        const users = [
            <?php 
            mysqli_data_seek($users_result, 0);
            while($user = mysqli_fetch_assoc($users_result)): 
            ?>
            {
                id: <?php echo $user['id']; ?>,
                name: '<?php echo addslashes($user['name']); ?>',
                email: '<?php echo addslashes($user['email']); ?>'
            },
            <?php endwhile; ?>
        ];
        
        let itemCounter = 0;
        
        function addItem(product = null) {
            itemCounter++;
            const tbody = document.getElementById('itemsBody');
            
            const row = document.createElement('tr');
            row.id = `item-${itemCounter}`;
            
            // Создаем опции для выбора товара
            let options = '<option value="">Выберите товар</option>';
            products.forEach(product => {
                options += `<option value="${product.id}" data-price="${product.price}">${product.name} (${product.price} ₽)</option>`;
            });
            
            row.innerHTML = `
                <td>${itemCounter}</td>
                <td>
                    <select name="product_id[]" class="form-control item-product" onchange="updatePrice(${itemCounter})" required>
                        ${options}
                    </select>
                </td>
                <td>
                    <input type="number" name="price[]" class="form-control item-price" 
                           step="0.01" min="0" value="${product ? product.price : ''}" required>
                </td>
                <td>
                    <input type="number" name="quantity[]" class="form-control item-quantity" 
                           value="1" min="1" onchange="updateTotal(${itemCounter})" required>
                </td>
                <td class="item-total" id="itemTotal-${itemCounter}">0 ₽</td>
                <td>
                    <button type="button" class="btn-remove-item" onclick="removeItem(${itemCounter})">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            `;
            
            tbody.appendChild(row);
            
            // Если передан товар, выбираем его
            if (product) {
                row.querySelector('.item-product').value = product.id;
            }
            
            updatePrice(itemCounter);
            calculateTotals();
        }
        
        function removeItem(id) {
            const row = document.getElementById(`item-${id}`);
            row.remove();
            calculateTotals();
        }
        
        function updatePrice(id) {
            const row = document.getElementById(`item-${id}`);
            const select = row.querySelector('.item-product');
            const priceInput = row.querySelector('.item-price');
            const selectedOption = select.options[select.selectedIndex];
            
            if (selectedOption && selectedOption.dataset.price) {
                priceInput.value = selectedOption.dataset.price;
                updateTotal(id);
            }
        }
        
        function updateTotal(id) {
            const row = document.getElementById(`item-${id}`);
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const quantity = parseInt(row.querySelector('.item-quantity').value) || 0;
            const total = price * quantity;
            
            document.getElementById(`itemTotal-${id}`).textContent = total.toFixed(2) + ' ₽';
            calculateTotals();
        }
        
        function calculateTotals() {
            let subtotal = 0;
            
            // Суммируем все товары
            for (let i = 1; i <= itemCounter; i++) {
                const totalElement = document.getElementById(`itemTotal-${i}`);
                if (totalElement) {
                    const total = parseFloat(totalElement.textContent) || 0;
                    subtotal += total;
                }
            }
            
            // Получаем стоимость доставки
            const deliverySelect = document.getElementById('delivery_method_id');
            const deliveryOption = deliverySelect.options[deliverySelect.selectedIndex];
            const deliveryPrice = deliveryOption ? parseFloat(deliveryOption.dataset.price) || 0 : 0;
            
            // Обновляем отображение
            document.getElementById('subtotal').textContent = subtotal.toFixed(2);
            document.getElementById('deliveryCost').textContent = deliveryPrice.toFixed(2);
            document.getElementById('totalAmount').textContent = (subtotal + deliveryPrice).toFixed(2);
        }
        
        // Поиск пользователей
        document.getElementById('userSearch').addEventListener('input', function(e) {
            const search = e.target.value.toLowerCase();
            const results = document.getElementById('userResults');
            
            if (search.length < 2) {
                results.style.display = 'none';
                return;
            }
            
            const filtered = users.filter(user => 
                user.name.toLowerCase().includes(search) || 
                user.email.toLowerCase().includes(search)
            );
            
            results.innerHTML = filtered.map(user => `
                <div class="user-option" data-id="${user.id}" 
                     onclick="selectUser(${user.id}, '${user.name}', '${user.email}')">
                    <strong>${user.name}</strong><br>
                    <small>${user.email}</small>
                </div>
            `).join('');
            
            results.style.display = filtered.length ? 'block' : 'none';
        });
        
        function selectUser(id, name, email) {
            document.getElementById('user_id').value = id;
            document.getElementById('customer_name').value = name;
            document.getElementById('customer_email').value = email;
            document.getElementById('userResults').style.display = 'none';
            document.getElementById('userSearch').value = name + ' (' + email + ')';
        }
        
        // Обновление стоимости при изменении способа доставки
        document.getElementById('delivery_method_id').addEventListener('change', calculateTotals);
        
        // Добавляем первый товар при загрузке
        document.addEventListener('DOMContentLoaded', function() {
            addItem();
        });
        
        // Валидация формы
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            const items = document.querySelectorAll('.item-product');
            let hasItems = false;
            
            items.forEach(item => {
                if (item.value) hasItems = true;
            });
            
            if (!hasItems) {
                e.preventDefault();
                alert('Добавьте хотя бы один товар в заказ');
            }
        });
    </script>
</body>
</html>