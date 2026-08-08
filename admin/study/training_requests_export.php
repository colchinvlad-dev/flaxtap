<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

// Параметры фильтрации (такие же как в основном файле)
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$read_filter = isset($_GET['read']) ? $_GET['read'] : '';
$priority_filter = isset($_GET['priority']) ? $_GET['priority'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

$where = ["1=1"];
if ($search) {
    $where[] = "(tr.name LIKE '%$search%' OR 
                 tr.email LIKE '%$search%' OR 
                 tr.phone LIKE '%$search%' OR
                 tr.message LIKE '%$search%' OR
                 tr.course_type LIKE '%$search%')";
}
if ($status_filter && $status_filter !== 'all') {
    $where[] = "tr.status = '$status_filter'";
}
if ($read_filter !== '') {
    $read_value = $read_filter == '1' ? 1 : 0;
    $where[] = "tr.is_read = $read_value";
}
if ($priority_filter && $priority_filter !== 'all') {
    $where[] = "tr.priority = '$priority_filter'";
}
if ($date_from) {
    $where[] = "DATE(tr.created_at) >= '$date_from'";
}
if ($date_to) {
    $where[] = "DATE(tr.created_at) <= '$date_to'";
}

$where_clause = implode(' AND ', $where);

// Получаем все заявки
$query = "SELECT tr.*, 
          u.name as assigned_to_name
          FROM training_requests tr
          LEFT JOIN users u ON tr.assigned_to = u.id
          WHERE $where_clause
          ORDER BY tr.created_at DESC";

$result = mysqli_query($conn, $query);

// Устанавливаем заголовки для Excel
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="training_requests_' . date('Y-m-d_H-i') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Начинаем вывод Excel
echo '<html>';
echo '<head>';
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
echo '</head>';
echo '<body>';

echo '<table border="1">';
echo '<tr>';
echo '<th>ID</th>';
echo '<th>Дата</th>';
echo '<th>Имя</th>';
echo '<th>Телефон</th>';
echo '<th>Email</th>';
echo '<th>Соцсеть</th>';
echo '<th>Курс</th>';
echo '<th>Сообщение</th>';
echo '<th>Статус</th>';
echo '<th>Приоритет</th>';
echo '<th>Прочитано</th>';
echo '<th>Назначено</th>';
echo '<th>Заметки</th>';
echo '</tr>';

while ($row = mysqli_fetch_assoc($result)) {
    echo '<tr>';
    echo '<td>' . $row['id'] . '</td>';
    echo '<td>' . date('d.m.Y H:i', strtotime($row['created_at'])) . '</td>';
    echo '<td>' . htmlspecialchars($row['name']) . '</td>';
    echo '<td>' . htmlspecialchars($row['phone']) . '</td>';
    echo '<td>' . htmlspecialchars($row['email']) . '</td>';
    echo '<td>' . htmlspecialchars($row['social_link']) . '</td>';
    echo '<td>' . htmlspecialchars($row['course_type']) . '</td>';
    echo '<td>' . htmlspecialchars($row['message']) . '</td>';
    
    $status = '';
    switch ($row['status']) {
        case 'new': $status = 'Новая'; break;
        case 'contacted': $status = 'Связались'; break;
        case 'enrolled': $status = 'Зачислен'; break;
        case 'rejected': $status = 'Отклонен'; break;
    }
    echo '<td>' . $status . '</td>';
    
    $priority = '';
    switch ($row['priority']) {
        case 'urgent': $priority = 'Срочный'; break;
        case 'high': $priority = 'Высокий'; break;
        case 'normal': $priority = 'Обычный'; break;
        case 'low': $priority = 'Низкий'; break;
    }
    echo '<td>' . $priority . '</td>';
    
    echo '<td>' . ($row['is_read'] ? 'Да' : 'Нет') . '</td>';
    echo '<td>' . htmlspecialchars($row['assigned_to_name']) . '</td>';
    echo '<td>' . htmlspecialchars($row['notes']) . '</td>';
    echo '</tr>';
}

echo '</table>';
echo '</body>';
echo '</html>';