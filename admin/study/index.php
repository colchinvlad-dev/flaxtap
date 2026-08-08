<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

// Параметры пагинации
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Параметры фильтрации
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$read_filter = isset($_GET['read']) ? $_GET['read'] : '';
$priority_filter = isset($_GET['priority']) ? $_GET['priority'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Построение запроса
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

// Получение заявок
$query = "SELECT tr.*, 
          u.name as assigned_to_name
          FROM training_requests tr
          LEFT JOIN users u ON tr.assigned_to = u.id
          WHERE $where_clause
          ORDER BY 
            CASE tr.priority 
                WHEN 'urgent' THEN 1
                WHEN 'high' THEN 2
                WHEN 'normal' THEN 3
                WHEN 'low' THEN 4
            END,
            tr.created_at DESC
          LIMIT $offset, $per_page";

$result = mysqli_query($conn, $query);

// Общее количество заявок
$count_query = "SELECT COUNT(*) as total FROM training_requests tr WHERE $where_clause";
$count_result = mysqli_query($conn, $count_query);
$total_requests = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_requests / $per_page);

// Статистика
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new,
    SUM(CASE WHEN status = 'contacted' THEN 1 ELSE 0 END) as contacted,
    SUM(CASE WHEN status = 'enrolled' THEN 1 ELSE 0 END) as enrolled,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
    SUM(CASE WHEN priority = 'urgent' THEN 1 ELSE 0 END) as urgent,
    SUM(CASE WHEN priority = 'high' THEN 1 ELSE 0 END) as high
    FROM training_requests";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Заявки на обучение - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style/study/index.css">
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Заявки на обучение</h1>
            <div class="quick-actions">
                <button onclick="exportToExcel()" class="btn btn-outline">
                    <i class="fas fa-file-export"></i> Экспорт в Excel
                </button>
            </div>
        </div>
        
        <!-- Статистика -->
        <div class="stats-grid">
            <div class="stat-card total-requests">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Всего заявок</div>
            </div>
            <div class="stat-card new-requests">
                <div class="stat-number"><?php echo $stats['new']; ?></div>
                <div class="stat-label">Новые</div>
            </div>
            <div class="stat-card unread-requests">
                <div class="stat-number"><?php echo $stats['unread']; ?></div>
                <div class="stat-label">Непрочитанные</div>
            </div>
            <div class="stat-card contacted-requests">
                <div class="stat-number"><?php echo $stats['contacted']; ?></div>
                <div class="stat-label">Связались</div>
            </div>
            <div class="stat-card enrolled-requests">
                <div class="stat-number"><?php echo $stats['enrolled']; ?></div>
                <div class="stat-label">Зачислены</div>
            </div>
            <div class="stat-card rejected-requests">
                <div class="stat-number"><?php echo $stats['rejected']; ?></div>
                <div class="stat-label">Отклонены</div>
            </div>
            <div class="stat-card urgent-requests">
                <div class="stat-number"><?php echo $stats['urgent']; ?></div>
                <div class="stat-label">Срочные</div>
            </div>
            <div class="stat-card high-requests">
                <div class="stat-number"><?php echo $stats['high']; ?></div>
                <div class="stat-label">Высокий приоритет</div>
            </div>
        </div>
        
        <!-- Фильтры -->
        <form method="GET" class="filter-form">
            <div class="filter-grid">
                <div class="form-group">
                    <label for="search">Поиск</label>
                    <input type="text" id="search" name="search" class="form-control" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Имя, email, телефон, сообщение">
                </div>
                
                <div class="form-group">
                    <label for="status">Статус</label>
                    <select id="status" name="status" class="form-control">
                        <option value="all">Все статусы</option>
                        <option value="new" <?php echo $status_filter == 'new' ? 'selected' : ''; ?>>Новые</option>
                        <option value="contacted" <?php echo $status_filter == 'contacted' ? 'selected' : ''; ?>>Связались</option>
                        <option value="enrolled" <?php echo $status_filter == 'enrolled' ? 'selected' : ''; ?>>Зачислены</option>
                        <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Отклонены</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="read">Прочитано</label>
                    <select id="read" name="read" class="form-control">
                        <option value="">Все</option>
                        <option value="0" <?php echo $read_filter === '0' ? 'selected' : ''; ?>>Непрочитанные</option>
                        <option value="1" <?php echo $read_filter === '1' ? 'selected' : ''; ?>>Прочитанные</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="priority">Приоритет</label>
                    <select id="priority" name="priority" class="form-control">
                        <option value="all">Все приоритеты</option>
                        <option value="urgent" <?php echo $priority_filter == 'urgent' ? 'selected' : ''; ?>>Срочный</option>
                        <option value="high" <?php echo $priority_filter == 'high' ? 'selected' : ''; ?>>Высокий</option>
                        <option value="normal" <?php echo $priority_filter == 'normal' ? 'selected' : ''; ?>>Обычный</option>
                        <option value="low" <?php echo $priority_filter == 'low' ? 'selected' : ''; ?>>Низкий</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="date_from">Дата от</label>
                    <input type="date" id="date_from" name="date_from" class="form-control" 
                           value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                
                <div class="form-group">
                    <label for="date_to">Дата до</label>
                    <input type="date" id="date_to" name="date_to" class="form-control" 
                           value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Применить фильтры
                    </button>
                    <?php if ($search || $status_filter != 'all' || $read_filter !== '' || $priority_filter != 'all' || $date_from || $date_to): ?>
                        <a href="?" class="btn btn-outline" style="margin-left: 10px;">
                            <i class="fas fa-times"></i> Сбросить
                        </a>
                    <?php endif; ?>
                </div>
                <div class="form-group" style="flex-direction: row; align-items: center; gap: 10px;">
                    <label for="per_page" style="margin-bottom: 0;">На странице:</label>
                    <select id="per_page" name="per_page" class="form-control" style="width: auto;" onchange="this.form.submit()">
                        <option value="10" <?php echo $per_page == 10 ? 'selected' : ''; ?>>10</option>
                        <option value="20" <?php echo $per_page == 20 ? 'selected' : ''; ?>>20</option>
                        <option value="50" <?php echo $per_page == 50 ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo $per_page == 100 ? 'selected' : ''; ?>>100</option>
                    </select>
                </div>
            </div>
        </form>
        
        <!-- Таблица заявок -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Контактные данные</th>
                        <th style="width: 20%;">Информация о заявке</th>
                        <th style="width: 15%;">Статус</th>
                        <th style="width: 15%;">Дата</th>
                        <th style="width: 25%;">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php mysqli_data_seek($result, 0); ?>
                        <?php while ($request = mysqli_fetch_assoc($result)): ?>
                            <?php 
                            $row_class = '';
                            if ($request['priority'] == 'urgent') $row_class = 'urgent-row';
                            elseif ($request['priority'] == 'high') $row_class = 'high-row';
                            if (!$request['is_read']) $row_class .= ' unread-row';
                            ?>
                            <tr class="<?php echo $row_class; ?>">
                                <td>
                                    <div class="request-name">
                                        <span class="read-badge <?php echo $request['is_read'] ? 'badge-read' : 'badge-unread'; ?>"></span>
                                        <?php echo htmlspecialchars($request['name']); ?>
                                        <?php if ($request['priority'] != 'normal'): ?>
                                            <span class="priority-badge 
                                                <?php 
                                                if ($request['priority'] == 'urgent') echo 'badge-urgent';
                                                elseif ($request['priority'] == 'high') echo 'badge-high';
                                                elseif ($request['priority'] == 'low') echo 'badge-low';
                                                else echo 'badge-normal';
                                                ?>">
                                                <?php 
                                                if ($request['priority'] == 'urgent') echo 'Срочно';
                                                elseif ($request['priority'] == 'high') echo 'Высокий';
                                                elseif ($request['priority'] == 'low') echo 'Низкий';
                                                else echo 'Обычный';
                                                ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="request-meta">
                                        <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($request['phone']); ?></span><br>
                                        <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($request['email']); ?></span><br>
                                        <?php if ($request['social_link']): ?>
                                            <span><i class="fas fa-link"></i> <?php echo htmlspecialchars($request['social_link']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="message-preview" title="<?php echo htmlspecialchars($request['message']); ?>">
                                        <?php 
                                        echo mb_strlen($request['message']) > 100 
                                            ? htmlspecialchars(mb_substr($request['message'], 0, 100)) . '...' 
                                            : htmlspecialchars($request['message']); 
                                        ?>
                                    </div>
                                    <?php if ($request['course_type']): ?>
                                        <div style="margin-top: 5px;">
                                            <small><strong>Курс:</strong> <?php echo htmlspecialchars($request['course_type']); ?></small>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($request['assigned_to_name']): ?>
                                        <div style="margin-top: 5px;">
                                            <small><strong>Назначено:</strong> <?php echo htmlspecialchars($request['assigned_to_name']); ?></small>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge 
                                        <?php 
                                        if ($request['status'] == 'new') echo 'badge-new';
                                        elseif ($request['status'] == 'contacted') echo 'badge-contacted';
                                        elseif ($request['status'] == 'enrolled') echo 'badge-enrolled';
                                        else echo 'badge-rejected';
                                        ?>">
                                        <?php 
                                        if ($request['status'] == 'new') echo 'Новая';
                                        elseif ($request['status'] == 'contacted') echo 'Связались';
                                        elseif ($request['status'] == 'enrolled') echo 'Зачислен';
                                        else echo 'Отклонен';
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo date('d.m.Y H:i', strtotime($request['created_at'])); ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="training_request_view.php?id=<?php echo $request['id']; ?>" 
                                           class="btn btn-sm btn-primary" title="Просмотреть">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button onclick="toggleRead(<?php echo $request['id']; ?>, <?php echo $request['is_read'] ? 0 : 1; ?>)" 
                                                class="btn btn-sm <?php echo $request['is_read'] ? 'btn-outline' : 'btn-success'; ?>" 
                                                title="<?php echo $request['is_read'] ? 'Отметить как непрочитанное' : 'Отметить как прочитанное'; ?>">
                                            <i class="fas fa-<?php echo $request['is_read'] ? 'envelope' : 'envelope-open'; ?>"></i>
                                        </button>
                                        <?php if ($request['status'] == 'new'): ?>
                                            <button onclick="changeStatus(<?php echo $request['id']; ?>, 'contacted')" 
                                                    class="btn btn-sm btn-info" title="Отметить как связались">
                                                <i class="fas fa-phone"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($request['status'] != 'enrolled'): ?>
                                            <button onclick="changeStatus(<?php echo $request['id']; ?>, 'enrolled')" 
                                                    class="btn btn-sm btn-success" title="Отметить как зачислен">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button onclick="deleteRequest(<?php echo $request['id']; ?>)" 
                                                class="btn btn-sm btn-danger" title="Удалить">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div style="margin-top: 5px;">
                                        <?php if ($request['notes']): ?>
                                            <small><i class="fas fa-sticky-note"></i> Есть заметки</small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="no-data">
                                <i class="fas fa-inbox"></i>
                                <p>Заявки не найдены</p>
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
                    <a href="?page=<?php echo $page-1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $read_filter !== '' ? '&read=' . $read_filter : ''; ?><?php echo $priority_filter ? '&priority=' . $priority_filter : ''; ?><?php echo $date_from ? '&date_from=' . $date_from : ''; ?><?php echo $date_to ? '&date_to=' . $date_to : ''; ?>" 
                       class="page-link">
                        <i class="fas fa-chevron-left"></i> Назад
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="page-link active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $read_filter !== '' ? '&read=' . $read_filter : ''; ?><?php echo $priority_filter ? '&priority=' . $priority_filter : ''; ?><?php echo $date_from ? '&date_from=' . $date_from : ''; ?><?php echo $date_to ? '&date_to=' . $date_to : ''; ?>" 
                           class="page-link"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page+1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $read_filter !== '' ? '&read=' . $read_filter : ''; ?><?php echo $priority_filter ? '&priority=' . $priority_filter : ''; ?><?php echo $date_from ? '&date_from=' . $date_from : ''; ?><?php echo $date_to ? '&date_to=' . $date_to : ''; ?>" 
                       class="page-link">
                        Вперед <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
    
    <script src="../assets/js/study/index.js"></script>
</body>
</html>