<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$order_id) {
    header("Location: index.php");
    exit();
}

// Получаем данные заказа
$query = "SELECT * FROM orders WHERE id = $order_id";
$result = mysqli_query($conn, $query);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    header("Location: index.php");
    exit();
}

// Получаем статусы, методы оплаты и доставки
$statuses_query = "SELECT * FROM order_statuses ORDER BY sort_order";
$statuses_result = mysqli_query($conn, $statuses_query);

$payments_query = "SELECT * FROM payment_methods WHERE is_active = 1 ORDER BY sort_order";
$payments_result = mysqli_query($conn, $payments_query);

$deliveries_query = "SELECT * FROM delivery_methods WHERE is_active = 1 ORDER BY sort_order";
$deliveries_result = mysqli_query($conn, $deliveries_query);

// Обработка формы
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    $tracking_number = mysqli_real_escape_string($conn, trim($_POST['tracking_number']));
    $estimated_delivery = $_POST['estimated_delivery'] ?: NULL;
    $manager_notes = mysqli_real_escape_string($conn, trim($_POST['manager_notes']));
    $is_paid = isset($_POST['is_paid']) ? 1 : 0;
    $paid_amount = (float)$_POST['paid_amount'];
    $payment_date = $_POST['payment_date'] ?: NULL;
    
    // Проверка обязательных полей
    if (empty($customer_name) || empty($customer_email) || empty($customer_phone)) {
        $error = 'Заполните обязательные поля: ФИО, Email и Телефон';
    } elseif (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Некорректный формат email';
    } else {
        // Обновление заказа
        $update_query = "UPDATE orders SET 
                        status_id = $status_id,
                        payment_method_id = $payment_method_id,
                        delivery_method_id = $delivery_method_id,
                        customer_name = '$customer_name',
                        customer_email = '$customer_email',
                        customer_phone = '$customer_phone',
                        delivery_address = '$delivery_address',
                        delivery_city = '$delivery_city',
                        delivery_postcode = '$delivery_postcode',
                        delivery_notes = '$delivery_notes',
                        tracking_number = '$tracking_number',
                        estimated_delivery = " . ($estimated_delivery ? "'$estimated_delivery'" : "NULL") . ",
                        manager_notes = '$manager_notes',
                        is_paid = $is_paid,
                        paid_amount = $paid_amount,
                        payment_date = " . ($payment_date ? "'$payment_date'" : "NULL") . ",
                        updated_at = NOW()
                        WHERE id = $order_id";
        
        if (mysqli_query($conn, $update_query)) {
            // Если статус изменился, добавляем запись в историю
            if ($order['status_id'] != $status_id) {
                $admin_id = $_SESSION['admin_id'] ?? 1;
                $history_query = "INSERT INTO order_status_history 
                                (order_id, old_status_id, new_status_id, changed_by, change_reason) 
                                VALUES ($order_id, {$order['status_id']}, $status_id, $admin_id, 'Изменено администратором')";
                mysqli_query($conn, $history_query);
            }
            
            $success = 'Заказ успешно обновлен';
            // Логируем действие
            $admin_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 
                        (isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 
                        (isset($_SESSION['username']) ? $_SESSION['username'] : 'Неизвестный'));
            $log_message = date('Y-m-d H:i:s') . " - Администратор {$admin_name} отредактировал заказ {$order['order_number']} (ID: {$order['id']})\n";
            file_put_contents('../admin_log.txt', $log_message, FILE_APPEND);
            
            // Обновляем данные заказа
            $order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM orders WHERE id = $order_id"));
        } else {
            $error = 'Ошибка при обновлении заказа: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование заказа <?php echo htmlspecialchars($order['order_number']); ?> - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style/orders/edit.css">
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Редактирование заказа</h1>
            <div>
                <a href="view.php?id=<?php echo $order['id']; ?>" class="btn btn-outline">
                    <i class="fas fa-eye"></i> Просмотр
                </a>
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Назад
                </a>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>
        
        <div class="edit-container">
            <!-- Форма редактирования -->
            <div>
                <form method="POST">
                    <div class="form-section">
                        <div class="section-header">
                            <i class="fas fa-user"></i>
                            <h2>Информация о клиенте</h2>
                        </div>
                        
                        <div class="row">
                            <div class="form-group">
                                <label for="customer_name" class="form-label required">ФИО</label>
                                <input type="text" id="customer_name" name="customer_name" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($order['customer_name']); ?>" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="customer_phone" class="form-label required">Телефон</label>
                                <input type="tel" id="customer_phone" name="customer_phone" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($order['customer_phone']); ?>" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_email" class="form-label required">Email</label>
                            <input type="email" id="customer_email" name="customer_email" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($order['customer_email']); ?>" 
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-section" style="margin-top: 30px;">
                        <div class="section-header">
                            <i class="fas fa-truck"></i>
                            <h2>Доставка</h2>
                        </div>
                        
                        <div class="row">
                            <div class="form-group">
                                <label for="delivery_method_id" class="form-label">Способ доставки</label>
                                <select id="delivery_method_id" name="delivery_method_id" class="form-control">
                                    <option value="">Не выбран</option>
                                    <?php mysqli_data_seek($deliveries_result, 0); ?>
                                    <?php while($delivery = mysqli_fetch_assoc($deliveries_result)): ?>
                                        <option value="<?php echo $delivery['id']; ?>" 
                                            <?php echo $order['delivery_method_id'] == $delivery['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($delivery['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="delivery_city" class="form-label">Город</label>
                                <input type="text" id="delivery_city" name="delivery_city" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($order['delivery_city'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="delivery_address" class="form-label">Адрес доставки</label>
                            <textarea id="delivery_address" name="delivery_address" 
                                      class="form-control" 
                                      rows="3"><?php echo htmlspecialchars($order['delivery_address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="form-group">
                                <label for="delivery_postcode" class="form-label">Почтовый индекс</label>
                                <input type="text" id="delivery_postcode" name="delivery_postcode" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($order['delivery_postcode'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="tracking_number" class="form-label">Трек-номер</label>
                                <input type="text" id="tracking_number" name="tracking_number" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($order['tracking_number'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="form-group">
                                <label for="estimated_delivery" class="form-label">Ориентировочная доставка</label>
                                <input type="date" id="estimated_delivery" name="estimated_delivery" 
                                       class="form-control" 
                                       value="<?php echo $order['estimated_delivery'] ? date('Y-m-d', strtotime($order['estimated_delivery'])) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="delivery_notes" class="form-label">Примечания к доставке</label>
                                <input type="text" id="delivery_notes" name="delivery_notes" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($order['delivery_notes'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section" style="margin-top: 30px;">
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
                                        <option value="<?php echo $status['id']; ?>" 
                                            <?php echo $order['status_id'] == $status['id'] ? 'selected' : ''; ?>>
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
                                        <option value="<?php echo $payment['id']; ?>" 
                                            <?php echo $order['payment_method_id'] == $payment['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($payment['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="is_paid" name="is_paid" value="1" 
                                   <?php echo $order['is_paid'] ? 'checked' : ''; ?>>
                            <label for="is_paid">Заказ оплачен</label>
                        </div>
                        
                        <div class="payment-status">
                            <div class="form-group" style="flex: 1;">
                                <label for="paid_amount" class="form-label">Сумма оплаты</label>
                                <input type="number" id="paid_amount" name="paid_amount" 
                                       class="form-control" 
                                       value="<?php echo $order['paid_amount']; ?>" 
                                       step="0.01" min="0">
                            </div>
                            
                            <div class="form-group" style="flex: 1;">
                                <label for="payment_date" class="form-label">Дата оплаты</label>
                                <input type="datetime-local" id="payment_date" name="payment_date" 
                                       class="form-control" 
                                       value="<?php echo $order['payment_date'] ? date('Y-m-d\TH:i', strtotime($order['payment_date'])) : ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section" style="margin-top: 30px;">
                        <div class="section-header">
                            <i class="fas fa-sticky-note"></i>
                            <h2>Примечания</h2>
                        </div>
                        
                        <div class="form-group">
                            <label for="manager_notes" class="form-label">Заметки менеджера</label>
                            <textarea id="manager_notes" name="manager_notes" 
                                      class="form-control" 
                                      rows="5"><?php echo htmlspecialchars($order['manager_notes'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 15px; margin-top: 30px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Сохранить изменения
                        </button>
                        <a href="view.php?id=<?php echo $order['id']; ?>" class="btn btn-outline">
                            <i class="fas fa-times"></i> Отмена
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Информация о заказе -->
            <div>
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-info-circle"></i>
                        <h2>Информация о заказе</h2>
                    </div>
                    
                    <div class="info-box">
                        <div class="info-item">
                            <span class="info-label">Номер заказа:</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['order_number']); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Дата создания:</span>
                            <span class="info-value"><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Последнее обновление:</span>
                            <span class="info-value"><?php echo date('d.m.Y H:i', strtotime($order['updated_at'])); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Источник:</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['source']); ?></span>
                        </div>
                    </div>
                    
                    <div class="section-header" style="margin-top: 30px;">
                        <i class="fas fa-money-bill-wave"></i>
                        <h2>Финансы</h2>
                    </div>
                    
                    <div class="info-box">
                        <div class="info-item">
                            <span class="info-label">Стоимость товаров:</span>
                            <span class="info-value"><?php echo number_format($order['subtotal'], 0, ',', ' '); ?> ₽</span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Скидка:</span>
                            <span class="info-value"><?php echo number_format($order['discount_amount'], 0, ',', ' '); ?> ₽</span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Доставка:</span>
                            <span class="info-value"><?php echo number_format($order['delivery_price'], 0, ',', ' '); ?> ₽</span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Итого:</span>
                            <span class="info-value" style="color: var(--primary-color);">
                                <?php echo number_format($order['total_amount'], 0, ',', ' '); ?> ₽
                            </span>
                        </div>
                    </div>
                    
                    <div class="section-header" style="margin-top: 30px;">
                        <i class="fas fa-history"></i>
                        <h2>Быстрые действия</h2>
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 20px;">
                        <button type="button" class="btn btn-outline" onclick="sendNotification()">
                            <i class="fas fa-bell"></i> Отправить уведомление
                        </button>
                        <button type="button" class="btn btn-outline" onclick="createInvoice()">
                            <i class="fas fa-file-invoice"></i> Создать счет
                        </button>
                        <button type="button" class="btn btn-outline" onclick="duplicateOrder()">
                            <i class="fas fa-copy"></i> Дублировать заказ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        function sendNotification() {
            const orderId = <?php echo $order_id; ?>;
            fetch('api/send_notification.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ order_id: orderId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Уведомление отправлено');
                } else {
                    alert('Ошибка: ' + data.error);
                }
            });
        }
        
        function createInvoice() {
            window.open('invoice.php?id=<?php echo $order_id; ?>', '_blank');
        }
        
        function duplicateOrder() {
            if (confirm('Создать копию этого заказа?')) {
                window.location.href = 'duplicate.php?id=<?php echo $order_id; ?>';
            }
        }
        
        // Автоматическое обновление цены доставки при выборе способа
        document.getElementById('delivery_method_id').addEventListener('change', function() {
            const methodId = this.value;
            if (methodId) {
                fetch('api/get_delivery_price.php?id=' + methodId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.price) {
                            // Можно обновить поле с ценой доставки, если оно есть
                            console.log('Цена доставки:', data.price);
                        }
                    });
            }
        });
        
        // При изменении статуса оплаты показать/скрыть поля оплаты
        document.getElementById('is_paid').addEventListener('change', function() {
            const paidAmount = document.getElementById('paid_amount');
            const paymentDate = document.getElementById('payment_date');
            
            if (this.checked) {
                paidAmount.value = paidAmount.value || <?php echo $order['total_amount']; ?>;
                paymentDate.value = paymentDate.value || '<?php echo date('Y-m-d\TH:i'); ?>';
            }
        });
    </script>
</body>
</html>