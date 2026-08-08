<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

// Параметры пагинации
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Параметры поиска и фильтрации
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$payment_filter = isset($_GET['payment']) ? $_GET['payment'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Построение запроса
$where = ["1=1"];
if ($search) {
    $where[] = "(o.order_number LIKE '%$search%' OR 
                 o.customer_name LIKE '%$search%' OR 
                 o.customer_email LIKE '%$search%' OR 
                 o.customer_phone LIKE '%$search%')";
}
if ($status_filter && $status_filter !== 'all') {
    $where[] = "o.status_id = " . (int)$status_filter;
}
if ($payment_filter && $payment_filter !== 'all') {
    $where[] = "o.payment_method_id = " . (int)$payment_filter;
}
if ($date_from) {
    $where[] = "DATE(o.created_at) >= '$date_from'";
}
if ($date_to) {
    $where[] = "DATE(o.created_at) <= '$date_to'";
}
$where_clause = implode(' AND ', $where);

// Получение заказов
$query = "SELECT o.*, 
          os.name as status_name, 
          os.color as status_color,
          pm.name as payment_method,
          dm.name as delivery_method
          FROM orders o
          LEFT JOIN order_statuses os ON o.status_id = os.id
          LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
          LEFT JOIN delivery_methods dm ON o.delivery_method_id = dm.id
          WHERE $where_clause
          ORDER BY o.created_at DESC 
          LIMIT $offset, $per_page";

$result = mysqli_query($conn, $query);

// Общее количество заказов для пагинации
$count_query = "SELECT COUNT(*) as total FROM orders o WHERE $where_clause";
$count_result = mysqli_query($conn, $count_query);
$total_orders = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_orders / $per_page);

// Получение статусов и методов оплаты для фильтров
$statuses_query = "SELECT * FROM order_statuses ORDER BY sort_order";
$statuses_result = mysqli_query($conn, $statuses_query);

$payments_query = "SELECT * FROM payment_methods WHERE is_active = 1 ORDER BY sort_order";
$payments_result = mysqli_query($conn, $payments_query);

// Статистика
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status_id = 1 THEN 1 ELSE 0 END) as new,
    SUM(CASE WHEN status_id = 3 THEN 1 ELSE 0 END) as confirmed,
    SUM(CASE WHEN status_id = 4 THEN 1 ELSE 0 END) as paid,
    SUM(CASE WHEN status_id = 6 THEN 1 ELSE 0 END) as delivered,
    SUM(CASE WHEN status_id = 7 THEN 1 ELSE 0 END) as cancelled,
    SUM(total_amount) as total_revenue
    FROM orders";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление заказами - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style/orders/index.css">
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Управление заказами</h1>
            <div class="quick-actions">
                <a href="create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Новый заказ
                </a>
                <button class="export-btn" onclick="exportOrders()">
                    <i class="fas fa-file-export"></i> Экспорт
                </button>
            </div>
        </div>
        
        <!-- Статистика -->
        <div class="stats-grid">
            <div class="stat-card total-orders">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Всего заказов</div>
            </div>
            <div class="stat-card new-orders">
                <div class="stat-number"><?php echo $stats['new']; ?></div>
                <div class="stat-label">Новых</div>
            </div>
            <div class="stat-card confirmed-orders">
                <div class="stat-number"><?php echo $stats['confirmed']; ?></div>
                <div class="stat-label">Подтверждено</div>
            </div>
            <div class="stat-card paid-orders">
                <div class="stat-number"><?php echo $stats['paid']; ?></div>
                <div class="stat-label">Оплачено</div>
            </div>
            <div class="stat-card delivered-orders">
                <div class="stat-number"><?php echo $stats['delivered']; ?></div>
                <div class="stat-label">Доставлено</div>
            </div>
            <div class="stat-card cancelled-orders">
                <div class="stat-number"><?php echo $stats['cancelled']; ?></div>
                <div class="stat-label">Отменено</div>
            </div>
            <div class="stat-card revenue">
                <div class="stat-number"><?php echo number_format($stats['total_revenue'] ?? 0, 0, ',', ' '); ?> ₽</div>
                <div class="stat-label">Общая выручка</div>
            </div>
        </div>
        
        <!-- Фильтры -->
        <form method="GET" class="filter-form">
            <div class="form-group">
                <label for="search">Поиск</label>
                <input type="text" id="search" name="search" class="form-control" 
                       value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Номер заказа, имя, email или телефон">
            </div>
            
            <div class="form-group">
                <label for="status">Статус</label>
                <select id="status" name="status" class="form-control">
                    <option value="all">Все статусы</option>
                    <?php while($status = mysqli_fetch_assoc($statuses_result)): ?>
                        <option value="<?php echo $status['id']; ?>" 
                            <?php echo $status_filter == $status['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($status['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="payment">Способ оплаты</label>
                <select id="payment" name="payment" class="form-control">
                    <option value="all">Все способы</option>
                    <?php mysqli_data_seek($payments_result, 0); ?>
                    <?php while($payment = mysqli_fetch_assoc($payments_result)): ?>
                        <option value="<?php echo $payment['id']; ?>" 
                            <?php echo $payment_filter == $payment['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($payment['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="date_from">Дата от</label>
                <div class="date-inputs">
                    <input type="date" id="date_from" name="date_from" class="form-control" 
                           value="<?php echo htmlspecialchars($date_from); ?>">
                    <input type="date" id="date_to" name="date_to" class="form-control" 
                           value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>&nbsp;</label>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Применить
                    </button>
                    <?php if ($search || $status_filter || $payment_filter || $date_from || $date_to): ?>
                        <a href="?" class="btn btn-outline">
                            <i class="fas fa-times"></i> Сбросить
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
        
        <!-- Таблица заказов -->
        <div class="card">
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>№ заказа</th>
                            <th>Клиент</th>
                            <th>Сумма</th>
                            <th>Статус</th>
                            <th>Оплата</th>
                            <th>Доставка</th>
                            <th>Дата</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($order = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>
                                        <span class="order-number"><?php echo htmlspecialchars($order['order_number']); ?></span>
                                    </td>
                                    <td>
                                        <div class="customer-info">
                                            <span class="customer-name"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                                            <span class="customer-contact"><?php echo htmlspecialchars($order['customer_email']); ?></span>
                                            <span class="customer-contact"><?php echo htmlspecialchars($order['customer_phone']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="amount"><?php echo number_format($order['total_amount'], 0, ',', ' '); ?> ₽</span>
                                    </td>
                                    <td>
                                        <span class="status-badge" style="background-color: <?php echo $order['status_color']; ?>20; color: <?php echo $order['status_color']; ?>;">
                                            <?php echo htmlspecialchars($order['status_name']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($order['payment_method'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($order['delivery_method'] ?? '-'); ?></td>
                                    <td class="date-cell">
                                        <?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="view.php?id=<?php echo $order['id']; ?>" 
                                               class="btn btn-sm btn-outline" title="Просмотр">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit.php?id=<?php echo $order['id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Редактировать">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete.php?id=<?php echo $order['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Вы уверены, что хотите удалить этот заказ?');"
                                               title="Удалить">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px;">
                                    <i class="fas fa-box-open" style="font-size: 48px; color: #e2e8f0; margin-bottom: 15px;"></i>
                                    <p style="color: #718096;">Заказы не найдены</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Пагинация -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page-1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $payment_filter ? '&payment=' . $payment_filter : ''; ?><?php echo $date_from ? '&date_from=' . $date_from : ''; ?><?php echo $date_to ? '&date_to=' . $date_to : ''; ?>" 
                           class="page-link">
                            <i class="fas fa-chevron-left"></i> Назад
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="page-link active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $payment_filter ? '&payment=' . $payment_filter : ''; ?><?php echo $date_from ? '&date_from=' . $date_from : ''; ?><?php echo $date_to ? '&date_to=' . $date_to : ''; ?>" 
                               class="page-link"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page+1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $payment_filter ? '&payment=' . $payment_filter : ''; ?><?php echo $date_from ? '&date_from=' . $date_from : ''; ?><?php echo $date_to ? '&date_to=' . $date_to : ''; ?>" 
                           class="page-link">
                            Вперед <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <script>
        function exportOrders() {
            // Создаем форму для экспорта
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'export.php';
            
            // Добавляем параметры фильтрации
            const params = [
                ['search', '<?php echo $search; ?>'],
                ['status', '<?php echo $status_filter; ?>'],
                ['payment', '<?php echo $payment_filter; ?>'],
                ['date_from', '<?php echo $date_from; ?>'],
                ['date_to', '<?php echo $date_to; ?>']
            ];
            
            params.forEach(([name, value]) => {
                if (value) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = value;
                    form.appendChild(input);
                }
            });
            
            document.body.appendChild(form);
            form.submit();
        }
        
        // Автозаполнение дат для быстрой фильтрации
        document.addEventListener('DOMContentLoaded', function() {
            // Кнопка "Сегодня"
            const todayBtn = document.createElement('button');
            todayBtn.type = 'button';
            todayBtn.className = 'btn btn-outline btn-sm';
            todayBtn.textContent = 'Сегодня';
            todayBtn.style.marginRight = '5px';
            todayBtn.onclick = function() {
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('date_from').value = today;
                document.getElementById('date_to').value = today;
            };
            
            // Кнопка "За неделю"
            const weekBtn = document.createElement('button');
            weekBtn.type = 'button';
            weekBtn.className = 'btn btn-outline btn-sm';
            weekBtn.textContent = 'Неделя';
            weekBtn.style.marginRight = '5px';
            weekBtn.onclick = function() {
                const today = new Date();
                const weekAgo = new Date();
                weekAgo.setDate(today.getDate() - 7);
                document.getElementById('date_from').value = weekAgo.toISOString().split('T')[0];
                document.getElementById('date_to').value = today.toISOString().split('T')[0];
            };
            
            // Кнопка "За месяц"
            const monthBtn = document.createElement('button');
            monthBtn.type = 'button';
            monthBtn.className = 'btn btn-outline btn-sm';
            monthBtn.textContent = 'Месяц';
            monthBtn.onclick = function() {
                const today = new Date();
                const monthAgo = new Date();
                monthAgo.setMonth(today.getMonth() - 1);
                document.getElementById('date_from').value = monthAgo.toISOString().split('T')[0];
                document.getElementById('date_to').value = today.toISOString().split('T')[0];
            };
            
            // Добавляем кнопки в контейнер дат
            const dateGroup = document.querySelector('.date-inputs').parentNode;
            const buttonContainer = document.createElement('div');
            buttonContainer.style.marginTop = '5px';
            buttonContainer.appendChild(todayBtn);
            buttonContainer.appendChild(weekBtn);
            buttonContainer.appendChild(monthBtn);
            dateGroup.appendChild(buttonContainer);
        });
    </script>
</body>
</html>