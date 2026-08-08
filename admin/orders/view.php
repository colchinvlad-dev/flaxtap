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
$query = "SELECT o.*, 
          os.name as status_name, 
          os.color as status_color,
          os.description as status_description,
          pm.name as payment_method,
          pm.description as payment_description,
          dm.name as delivery_method,
          dm.price as delivery_price,
          dm.delivery_days,
          u.name as user_name,
          u.email as user_email
          FROM orders o
          LEFT JOIN order_statuses os ON o.status_id = os.id
          LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
          LEFT JOIN delivery_methods dm ON o.delivery_method_id = dm.id
          LEFT JOIN users u ON o.user_id = u.id
          WHERE o.id = $order_id";

$result = mysqli_query($conn, $query);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    header("Location: index.php");
    exit();
}

// Получаем товары заказа
$items_query = "SELECT oi.*, p.name as product_name, p.image_url as product_image
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = $order_id
                ORDER BY oi.id";
$items_result = mysqli_query($conn, $items_query);

// Получаем историю статусов
$history_query = "SELECT h.*, os.name as status_name, u.name as changed_by_name
                  FROM order_status_history h
                  LEFT JOIN order_statuses os ON h.new_status_id = os.id
                  LEFT JOIN users u ON h.changed_by = u.id
                  WHERE h.order_id = $order_id
                  ORDER BY h.created_at DESC";
$history_result = mysqli_query($conn, $history_query);

// Получаем купоны
$coupons_query = "SELECT oc.*, c.code, c.discount_type, c.discount_value
                  FROM order_coupons oc
                  LEFT JOIN coupons c ON oc.coupon_id = c.id
                  WHERE oc.order_id = $order_id";
$coupons_result = mysqli_query($conn, $coupons_query);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Заказ <?php echo htmlspecialchars($order['order_number']); ?> - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style/orders/view.css">
    <style>
        .status-badge-large {
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
            background-color: <?php echo $order['status_color']; ?>20;
            color: <?php echo $order['status_color']; ?>;
        }
    </style>
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Просмотр заказа</h1>
            <div>
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Назад к списку
                </a>
            </div>
        </div>
        
        <div class="order-container">
            <!-- Левая колонка -->
            <div>
                <!-- Основная информация -->
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-number"><?php echo htmlspecialchars($order['order_number']); ?></div>
                            <div class="order-date">
                                Создан: <?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?>
                            </div>
                        </div>
                        <div class="status-badge-large">
                            <?php echo htmlspecialchars($order['status_name']); ?>
                        </div>
                    </div>
                    
                    <div class="card-header">
                        <i class="fas fa-user"></i>
                        <h2>Информация о клиенте</h2>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">ФИО</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['customer_email']); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Телефон</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['customer_phone']); ?></div>
                    </div>
                    
                    <?php if ($order['user_id']): ?>
                        <div class="info-group">
                            <div class="info-label">Аккаунт</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($order['user_name']); ?> (<?php echo htmlspecialchars($order['user_email']); ?>)
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-header" style="margin-top: 30px;">
                        <i class="fas fa-truck"></i>
                        <h2>Доставка</h2>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Способ доставки</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['delivery_method']); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Адрес доставки</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($order['delivery_address'] ?? ''); ?><br>
                            <?php echo htmlspecialchars($order['delivery_city'] ?? ''); ?>, <?php echo htmlspecialchars($order['delivery_postcode'] ?? ''); ?>
                        </div>
                    </div>
                    
                    <?php if ($order['delivery_notes']): ?>
                        <div class="info-group">
                            <div class="info-label">Примечания к доставке</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['delivery_notes']); ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($order['tracking_number']): ?>
                        <div class="info-group">
                            <div class="info-label">Трек-номер</div>
                            <div class="info-value">
                                <strong><?php echo htmlspecialchars($order['tracking_number']); ?></strong>
                                <?php if ($order['estimated_delivery']): ?>
                                    <br><small>Ориентировочная доставка: <?php echo date('d.m.Y', strtotime($order['estimated_delivery'])); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- История статусов -->
                <div class="order-card" style="margin-top: 30px;">
                    <div class="card-header">
                        <i class="fas fa-history"></i>
                        <h2>История статусов</h2>
                    </div>
                    
                    <?php if (mysqli_num_rows($history_result) > 0): ?>
                        <?php while($history = mysqli_fetch_assoc($history_result)): ?>
                            <div class="history-item <?php echo strtolower($history['status_name']); ?>">
                                <div class="history-date">
                                    <?php echo date('d.m.Y H:i', strtotime($history['created_at'])); ?>
                                </div>
                                <div class="history-status">
                                    <?php echo htmlspecialchars($history['status_name']); ?>
                                </div>
                                <?php if ($history['change_reason']): ?>
                                    <div class="history-reason">
                                        <?php echo htmlspecialchars($history['change_reason']); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($history['changed_by_name']): ?>
                                    <div class="history-by">
                                        Изменено: <?php echo htmlspecialchars($history['changed_by_name']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="color: var(--gray-color); text-align: center; padding: 20px;">
                            История изменений статусов отсутствует
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Правая колонка -->
            <div>
                <!-- Товары -->
                <div class="order-card">
                    <div class="card-header">
                        <i class="fas fa-shopping-cart"></i>
                        <h2>Состав заказа</h2>
                    </div>
                    
                    <?php if (mysqli_num_rows($items_result) > 0): ?>
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th style="width: 60px;"></th>
                                    <th>Товар</th>
                                    <th style="width: 100px;">Цена</th>
                                    <th style="width: 80px;">Кол-во</th>
                                    <th style="width: 100px;">Сумма</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($item = mysqli_fetch_assoc($items_result)): ?>
                                    <tr>
                                        <td>
                                            <?php if ($item['product_image']): ?>
                                                <img src="<?php echo htmlspecialchars($item['product_image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                                     class="item-image">
                                            <?php else: ?>
                                                <div style="width: 60px; height: 60px; background: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-box" style="color: #ccc;"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                            <?php if ($item['product_sku']): ?>
                                                <small style="color: var(--gray-color);">Артикул: <?php echo htmlspecialchars($item['product_sku']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="item-price"><?php echo number_format($item['product_price'], 0, ',', ' '); ?> ₽</td>
                                        <td><?php echo $item['quantity']; ?> шт.</td>
                                        <td class="item-price"><?php echo number_format($item['subtotal'], 0, ',', ' '); ?> ₽</td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        
                        <!-- Итоги -->
                        <table class="totals-table">
                            <tr>
                                <td>Стоимость товаров:</td>
                                <td style="text-align: right;"><?php echo number_format($order['subtotal'], 0, ',', ' '); ?> ₽</td>
                            </tr>
                            
                            <?php if ($order['discount_amount'] > 0): ?>
                                <tr>
                                    <td>Скидка:</td>
                                    <td style="text-align: right; color: var(--danger-color);">
                                        -<?php echo number_format($order['discount_amount'], 0, ',', ' '); ?> ₽
                                    </td>
                                </tr>
                            <?php endif; ?>
                            
                            <?php if ($order['delivery_price'] > 0): ?>
                                <tr>
                                    <td>Доставка:</td>
                                    <td style="text-align: right;"><?php echo number_format($order['delivery_price'], 0, ',', ' '); ?> ₽</td>
                                </tr>
                            <?php endif; ?>
                            
                            <tr>
                                <td>Итого к оплате:</td>
                                <td style="text-align: right;"><?php echo number_format($order['total_amount'], 0, ',', ' '); ?> ₽</td>
                            </tr>
                            
                            <?php if ($order['paid_amount'] > 0): ?>
                                <tr>
                                    <td>Оплачено:</td>
                                    <td style="text-align: right; color: var(--success-color);">
                                        <?php echo number_format($order['paid_amount'], 0, ',', ' '); ?> ₽
                                    </td>
                                </tr>
                                <?php if ($order['is_paid']): ?>
                                    <tr>
                                        <td>Дата оплаты:</td>
                                        <td style="text-align: right;">
                                            <?php echo date('d.m.Y H:i', strtotime($order['payment_date'])); ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endif; ?>
                        </table>
                    <?php else: ?>
                        <p style="color: var(--gray-color); text-align: center; padding: 20px;">
                            Товары в заказе отсутствуют
                        </p>
                    <?php endif; ?>
                </div>
                
                <!-- Оплата -->
                <div class="order-card" style="margin-top: 30px;">
                    <div class="card-header">
                        <i class="fas fa-credit-card"></i>
                        <h2>Оплата</h2>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Способ оплаты</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['payment_method'] ?? ''); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Статус оплаты</div>
                        <div class="info-value">
                            <?php if ($order['is_paid']): ?>
                                <span style="color: var(--success-color);">
                                    <i class="fas fa-check-circle"></i> Оплачен
                                </span>
                            <?php else: ?>
                                <span style="color: var(--danger-color);">
                                    <i class="fas fa-times-circle"></i> Не оплачен
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($order['payment_id']): ?>
                        <div class="info-group">
                            <div class="info-label">ID платежа</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['payment_id']); ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Купоны -->
                    <?php if (mysqli_num_rows($coupons_result) > 0): ?>
                        <div class="card-header" style="margin-top: 20px;">
                            <i class="fas fa-tag"></i>
                            <h2>Примененные купоны</h2>
                        </div>
                        
                        <?php while($coupon = mysqli_fetch_assoc($coupons_result)): ?>
                            <div class="info-group">
                                <div class="info-label">Купон</div>
                                <div class="info-value">
                                    <?php echo htmlspecialchars($coupon['code']); ?> 
                                    (<?php echo $coupon['discount_type'] === 'percent' ? $coupon['discount_value'] . '%' : $coupon['discount_value'] . ' ₽'; ?>)
                                    <br>
                                    <small>Скидка: <?php echo number_format($coupon['discount_amount'], 0, ',', ' '); ?> ₽</small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Примечания -->
                <div class="order-card" style="margin-top: 30px;">
                    <div class="card-header">
                        <i class="fas fa-sticky-note"></i>
                        <h2>Примечания</h2>
                    </div>
                    
                    <?php if ($order['customer_notes']): ?>
                        <div class="info-group">
                            <div class="info-label">Комментарий клиента</div>
                            <div class="info-value"><?php echo nl2br(htmlspecialchars($order['customer_notes'])); ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($order['manager_notes']): ?>
                        <div class="info-group">
                            <div class="info-label">Заметки менеджера</div>
                            <div class="info-value"><?php echo nl2br(htmlspecialchars($order['manager_notes'])); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="edit.php?id=<?php echo $order['id']; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Редактировать заказ
            </a>
            <a href="delete.php?id=<?php echo $order['id']; ?>" 
               class="btn btn-danger"
               onclick="return confirm('Вы уверены, что хотите удалить этот заказ?');">
                <i class="fas fa-trash"></i> Удалить заказ
            </a>
            <button onclick="window.print()" class="btn btn-print">
                <i class="fas fa-print"></i> Распечатать
            </button>
            <a href="invoice.php?id=<?php echo $order['id']; ?>" target="_blank" class="btn btn-invoice">
                <i class="fas fa-file-invoice"></i> Счет
            </a>
        </div>
    </main>
    
    <script src="../assets/js/orders/view.js"></script>
</body>
</html>