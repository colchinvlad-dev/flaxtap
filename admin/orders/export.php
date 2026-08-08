<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

// Получаем фильтры
$search = $_POST['search'] ?? '';
$status = $_POST['status'] ?? '';
$payment = $_POST['payment'] ?? '';
$date_from = $_POST['date_from'] ?? '';
$date_to = $_POST['date_to'] ?? '';

// Формируем WHERE
$where = ["1=1"];
if ($search) {
    $where[] = "(o.order_number LIKE '%$search%' OR o.customer_name LIKE '%$search%' OR o.customer_email LIKE '%$search%')";
}
if ($status && $status !== 'all') {
    $where[] = "o.status_id = $status";
}
if ($payment && $payment !== 'all') {
    $where[] = "o.payment_method_id = $payment";
}
if ($date_from) {
    $where[] = "DATE(o.created_at) >= '$date_from'";
}
if ($date_to) {
    $where[] = "DATE(o.created_at) <= '$date_to'";
}
$where_clause = implode(' AND ', $where);

// Получаем данные
$query = "SELECT o.*, 
          os.name as status_name,
          pm.name as payment_method,
          dm.name as delivery_method
          FROM orders o
          LEFT JOIN order_statuses os ON o.status_id = os.id
          LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
          LEFT JOIN delivery_methods dm ON o.delivery_method_id = dm.id
          WHERE $where_clause
          ORDER BY o.created_at DESC";

$result = mysqli_query($conn, $query);

// Устанавливаем заголовки для скачивания
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="orders_' . date('Y-m-d_H-i') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Выводим данные в Excel-формат
echo "<table border='1'>";
echo "<tr>
        <th>№ заказа</th>
        <th>Дата</th>
        <th>Клиент</th>
        <th>Email</th>
        <th>Телефон</th>
        <th>Статус</th>
        <th>Оплата</th>
        <th>Доставка</th>
        <th>Сумма</th>
        <th>Оплачено</th>
        <th>Адрес</th>
        <th>Комментарий</th>
      </tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['order_number']) . "</td>";
    echo "<td>" . date('d.m.Y H:i', strtotime($row['created_at'])) . "</td>";
    echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['customer_email']) . "</td>";
    echo "<td>" . htmlspecialchars($row['customer_phone']) . "</td>";
    echo "<td>" . htmlspecialchars($row['status_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['payment_method']) . "</td>";
    echo "<td>" . htmlspecialchars($row['delivery_method']) . "</td>";
    echo "<td>" . number_format($row['total_amount'], 2, '.', '') . "</td>";
    echo "<td>" . ($row['is_paid'] ? 'Да' : 'Нет') . "</td>";
    echo "<td>" . htmlspecialchars($row['delivery_address']) . "</td>";
    echo "<td>" . htmlspecialchars(substr($row['customer_notes'], 0, 100)) . "</td>";
    echo "</tr>";
}

echo "</table>";
exit;
?>