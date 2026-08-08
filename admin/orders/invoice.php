<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$order_id) {
    die('Заказ не найден');
}

$query = "SELECT o.*, 
          os.name as status_name,
          pm.name as payment_method,
          dm.name as delivery_method,
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
    die('Заказ не найден');
}

$items_query = "SELECT * FROM order_items WHERE order_id = $order_id";
$items_result = mysqli_query($conn, $items_query);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Счет №<?php echo htmlspecialchars($order['order_number']); ?></title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            border: 1px solid #ddd;
            background: white;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }
        
        .company-info h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        
        .company-details {
            margin-top: 10px;
            font-size: 11px;
            color: #666;
        }
        
        .invoice-info h2 {
            margin: 0;
            font-size: 20px;
            text-align: right;
        }
        
        .invoice-number {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-top: 5px;
        }
        
        .date {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        
        .two-columns {
            display: flex;
            justify-content: space-between;
        }
        
        .column {
            width: 48%;
        }
        
        .info-row {
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: bold;
            color: #666;
            min-width: 150px;
            display: inline-block;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        th {
            background: #f5f5f5;
            text-align: left;
            padding: 10px;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        
        td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        
        .text-right {
            text-align: right;
        }
        
        .totals {
            width: 300px;
            margin-left: auto;
            margin-top: 20px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #ddd;
        }
        
        .total-row:last-child {
            font-weight: bold;
            font-size: 16px;
            border-bottom: 2px solid #333;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
            text-align: center;
        }
        
        .signature {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-block {
            text-align: center;
            width: 45%;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin: 40px 0 10px;
        }
        
        @media print {
            body {
                padding: 0;
            }
            
            .invoice-container {
                border: none;
                padding: 0;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header">
            <div class="company-info">
                <h1>FlaxTap</h1>
                <div class="company-details">
                    ИНН: 1234567890<br>
                    ОГРН: 1234567890123<br>
                    Адрес: Санкт-Петербург, ул. Косметологов, 15<br>
                    Телефон: +7 (812) 123-45-67<br>
                    Email: info@flaxtap.ru
                </div>
            </div>
            
            <div class="invoice-info">
                <h2>Счет на оплату</h2>
                <div class="invoice-number">№ <?php echo htmlspecialchars($order['order_number']); ?></div>
                <div class="date">
                    от <?php echo date('d.m.Y', strtotime($order['created_at'])); ?>
                </div>
            </div>
        </div>
        
        <div class="section">
            <div class="two-columns">
                <div class="column">
                    <div class="section-title">Поставщик</div>
                    <div class="info-row">
                        <span class="info-label">Наименование:</span>
                        FlaxTap
                    </div>
                    <div class="info-row">
                        <span class="info-label">ИНН:</span>
                        1234567890
                    </div>
                    <div class="info-row">
                        <span class="info-label">Адрес:</span>
                        Санкт-Петербург, ул. Косметологов, 15
                    </div>
                </div>
                
                <div class="column">
                    <div class="section-title">Покупатель</div>
                    <div class="info-row">
                        <span class="info-label">ФИО:</span>
                        <?php echo htmlspecialchars($order['customer_name']); ?>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <?php echo htmlspecialchars($order['customer_email']); ?>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Телефон:</span>
                        <?php echo htmlspecialchars($order['customer_phone']); ?>
                    </div>
                    <?php if ($order['delivery_address']): ?>
                        <div class="info-row">
                            <span class="info-label">Адрес:</span>
                            <?php echo htmlspecialchars($order['delivery_address']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Товары и услуги</div>
            <table>
                <thead>
                    <tr>
                        <th width="50">№</th>
                        <th>Наименование</th>
                        <th width="100">Цена</th>
                        <th width="80">Кол-во</th>
                        <th width="120">Сумма</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; ?>
                    <?php while($item = mysqli_fetch_assoc($items_result)): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td class="text-right"><?php echo number_format($item['product_price'], 2, ',', ' '); ?> ₽</td>
                            <td class="text-right"><?php echo $item['quantity']; ?></td>
                            <td class="text-right"><?php echo number_format($item['subtotal'], 2, ',', ' '); ?> ₽</td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <div class="totals">
                <div class="total-row">
                    <span>Стоимость товаров:</span>
                    <span><?php echo number_format($order['subtotal'], 2, ',', ' '); ?> ₽</span>
                </div>
                <?php if ($order['delivery_price'] > 0): ?>
                    <div class="total-row">
                        <span>Доставка:</span>
                        <span><?php echo number_format($order['delivery_price'], 2, ',', ' '); ?> ₽</span>
                    </div>
                <?php endif; ?>
                <div class="total-row">
                    <span>Итого к оплате:</span>
                    <span><?php echo number_format($order['total_amount'], 2, ',', ' '); ?> ₽</span>
                </div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Условия оплаты</div>
            <div class="info-row">
                <span class="info-label">Способ оплаты:</span>
                <?php echo htmlspecialchars($order['payment_method'] ?? ''); ?>
            </div>
            <div class="info-row">
                <span class="info-label">Срок оплаты:</span>
                до <?php echo date('d.m.Y', strtotime('+3 days')); ?>
            </div>
            <div class="info-row">
                <span class="info-label">Статус заказа:</span>
                <?php echo htmlspecialchars($order['status_name']); ?>
            </div>
        </div>
        
        <div class="signature">
            <div class="signature-block">
                <div class="signature-line"></div>
                <div>Руководитель</div>
                <div>_________________ / М. Беляева</div>
            </div>
            
            <div class="signature-block">
                <div class="signature-line"></div>
                <div>Бухгалтер</div>
                <div>_________________ / А. Смирнова</div>
            </div>
        </div>
        
        <div class="footer">
            <p>Счет действителен в течение 3 дней с даты выставления</p>
            <p>FlaxTap © <?php echo date('Y'); ?>. Все права защищены.</p>
            <p class="no-print">
                <button onclick="window.print()">Распечатать</button>
                <button onclick="window.close()">Закрыть</button>
            </p>
        </div>
    </div>
    
    <script>
        // Автоматически открываем печать при загрузке
        window.onload = function() {
            if (window.location.search.indexOf('print=true') !== -1) {
                window.print();
            }
        };
    </script>
</body>
</html>