<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();
$admin = getAdminData();

// Параметры пагинации
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Параметры поиска и фильтрации
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Построение запроса для пользователей
$user_where = [];
if ($search) {
    $user_where[] = "(name LIKE '%$search%' OR email LIKE '%$search%' OR telephone LIKE '%$search%')";
}
if ($role_filter && $role_filter !== 'all') {
    $user_where[] = "role = '$role_filter'";
}
if ($status_filter && $status_filter !== 'all') {
    $user_where[] = "is_active = " . ($status_filter === 'active' ? '1' : '0');
}
$user_where_clause = $user_where ? 'WHERE ' . implode(' AND ', $user_where) : '';

// Получение пользователей
$user_query = "SELECT * FROM users $user_where_clause ORDER BY created_at DESC LIMIT $offset, $per_page";
$user_result = mysqli_query($conn, $user_query);

// Общее количество пользователей для пагинации
$count_query = "SELECT COUNT(*) as total FROM users $user_where_clause";
$count_result = mysqli_query($conn, $count_query);
$total_users = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_users / $per_page);

// Получение контактов (сообщений)
$contact_where = [];
if (isset($_GET['contact_search'])) {
    $contact_search = mysqli_real_escape_string($conn, $_GET['contact_search']);
    if ($contact_search) {
        $contact_where[] = "(name LIKE '%$contact_search%' OR email LIKE '%$contact_search%' OR phone LIKE '%$contact_search%')";
    }
}
if (isset($_GET['contact_status']) && $_GET['contact_status'] !== 'all') {
    $contact_where[] = "status = '" . mysqli_real_escape_string($conn, $_GET['contact_status']) . "'";
}
$contact_where_clause = $contact_where ? 'WHERE ' . implode(' AND ', $contact_where) : '';

$contact_query = "SELECT * FROM contact_messages $contact_where_clause ORDER BY created_at DESC";
$contact_result = mysqli_query($conn, $contact_query);

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_contact'])) {
        $contact_id = (int)$_POST['contact_id'];
        $delete_query = "DELETE FROM contact_messages WHERE id = $contact_id";
        if (mysqli_query($conn, $delete_query)) {
            $success_message = "Сообщение успешно удалено";
        } else {
            $error_message = "Ошибка при удалении сообщения: " . mysqli_error($conn);
        }
        // Обновляем список сообщений
        $contact_result = mysqli_query($conn, $contact_query);
    }
    
    if (isset($_POST['update_contact_status'])) {
        $contact_id = (int)$_POST['contact_id'];
        $new_status = mysqli_real_escape_string($conn, $_POST['new_status']);
        $update_query = "UPDATE contact_messages SET status = '$new_status', updated_at = NOW() WHERE id = $contact_id";
        if (mysqli_query($conn, $update_query)) {
            $success_message = "Статус сообщения обновлен";
        }
        $contact_result = mysqli_query($conn, $contact_query);
    }
}

// Получение статистики
$user_stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
    SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as users,
    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive
    FROM users";
$user_stats_result = mysqli_query($conn, $user_stats_query);
$user_stats = mysqli_fetch_assoc($user_stats_result);

$contact_stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new,
    SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as `read`,
    SUM(CASE WHEN status = 'answered' THEN 1 ELSE 0 END) as answered
    FROM contact_messages";
$contact_stats_result = mysqli_query($conn, $contact_stats_query);
$contact_stats = mysqli_fetch_assoc($contact_stats_result);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление пользователями - FlaxTap</title>
        <link rel="stylesheet" href="../assets/style/users/index.css">
        <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <!-- Сообщения -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($success_message); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Статистика -->
        <div class="stats-grid">
            <div class="stat-card users">
                <div class="stat-number"><?php echo $user_stats['total']; ?></div>
                <div class="stat-label">Всего пользователей</div>
            </div>
            <div class="stat-card admins">
                <div class="stat-number"><?php echo $user_stats['admins']; ?></div>
                <div class="stat-label">Администраторов</div>
            </div>
            <div class="stat-card active-users">
                <div class="stat-number"><?php echo $user_stats['active']; ?></div>
                <div class="stat-label">Активных</div>
            </div>
            <div class="stat-card inactive-users">
                <div class="stat-number"><?php echo $user_stats['inactive']; ?></div>
                <div class="stat-label">Неактивных</div>
            </div>
            <div class="stat-card total-contacts">
                <div class="stat-number"><?php echo $contact_stats['total']; ?></div>
                <div class="stat-label">Всего обращений</div>
            </div>
            <div class="stat-card new-contacts">
                <div class="stat-number"><?php echo $contact_stats['new']; ?></div>
                <div class="stat-label">Новых</div>
            </div>
            <div class="stat-card answered-contacts">
                <div class="stat-number"><?php echo $contact_stats['answered']; ?></div>
                <div class="stat-label">Отвеченных</div>
            </div>
        </div>
        
        <!-- Табы -->
        <div class="tab-container">
            <div class="tab-buttons">
                <button class="tab-btn active" data-tab="users">Пользователи (<?php echo $user_stats['total']; ?>)</button>
                <button class="tab-btn" data-tab="contacts">Обратная связь (<?php echo $contact_stats['total']; ?>)</button>
            </div>
            
            <!-- Вкладка пользователей -->
            <div class="tab-content active" id="users-tab">
                <div class="card">
                    <div class="card-header">
                        <h2>Список пользователей</h2>
                        <a href="create.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Добавить пользователя
                        </a>
                    </div>
                    
                    <!-- Фильтры -->
                    <form method="GET" class="filter-form">
                        <div class="form-group">
                            <label for="search">Поиск</label>
                            <input type="text" id="search" name="search" class="form-control" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Поиск по имени, email или телефону">
                        </div>
                        <div class="form-group">
                            <label for="role">Роль</label>
                            <select id="role" name="role" class="form-control">
                                <option value="all">Все роли</option>
                                <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Администратор</option>
                                <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>Пользователь</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="status">Статус</label>
                            <select id="status" name="status" class="form-control">
                                <option value="all">Все статусы</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Активные</option>
                                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Неактивные</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Применить фильтры
                            </button>
                            <?php if ($search || $role_filter || $status_filter): ?>
                                <a href="?" class="btn btn-outline">
                                    <i class="fas fa-times"></i> Сбросить
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                    
                    <!-- Таблица пользователей -->
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Имя</th>
                                    <th>Email</th>
                                    <th>Телефон</th>
                                    <th>Роль</th>
                                    <th>Статус</th>
                                    <th>Дата регистрации</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($user_result) > 0): ?>
                                    <?php while ($user = mysqli_fetch_assoc($user_result)): ?>
                                        <tr>
                                            <td>#<?php echo $user['id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo $user['telephone'] ? htmlspecialchars($user['telephone']) : '-'; ?></td>
                                            <td>
                                                <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-danger' : 'badge-info'; ?>">
                                                    <?php echo $user['role'] === 'admin' ? 'Админ' : 'Пользователь'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $user['is_active'] ? 'badge-success' : 'badge-danger'; ?>">
                                                    <?php echo $user['is_active'] ? 'Активен' : 'Неактивен'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="view.php?id=<?php echo $user['id']; ?>" 
                                                       class="btn btn-sm btn-outline" title="Просмотр">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit.php?id=<?php echo $user['id']; ?>" 
                                                       class="btn btn-sm btn-primary" title="Редактировать">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="change_password.php?id=<?php echo $user['id']; ?>" 
                                                       class="btn btn-sm btn-warning" title="Сменить пароль">
                                                        <i class="fas fa-key"></i>
                                                    </a>
                                                    <a href="delete.php?id=<?php echo $user['id']; ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?');"
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
                                            <i class="fas fa-users" style="font-size: 48px; color: #e2e8f0; margin-bottom: 15px;"></i>
                                            <p style="color: #718096;">Пользователи не найдены</p>
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
                                <a href="?page=<?php echo $page-1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $role_filter ? '&role=' . $role_filter : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>" 
                                   class="page-link">
                                    <i class="fas fa-chevron-left"></i> Назад
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="page-link active"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $role_filter ? '&role=' . $role_filter : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>" 
                                       class="page-link"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page+1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $role_filter ? '&role=' . $role_filter : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>" 
                                   class="page-link">
                                    Вперед <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Вкладка обратной связи -->
            <div class="tab-content" id="contacts-tab">
                <div class="card">
                    <div class="card-header">
                        <h2>Обратная связь</h2>
                    </div>
                    
                    <!-- Фильтры для контактов -->
                    <form method="GET" class="filter-form">
                        <input type="hidden" name="tab" value="contacts">
                        <div class="form-group">
                            <label for="contact_search">Поиск</label>
                            <input type="text" id="contact_search" name="contact_search" class="form-control" 
                                   value="<?php echo isset($_GET['contact_search']) ? htmlspecialchars($_GET['contact_search']) : ''; ?>" 
                                   placeholder="Поиск по имени, email или телефону">
                        </div>
                        <div class="form-group">
                            <label for="contact_status">Статус</label>
                            <select id="contact_status" name="contact_status" class="form-control">
                                <option value="all">Все статусы</option>
                                <option value="new" <?php echo isset($_GET['contact_status']) && $_GET['contact_status'] === 'new' ? 'selected' : ''; ?>>Новые</option>
                                <option value="read" <?php echo isset($_GET['contact_status']) && $_GET['contact_status'] === 'read' ? 'selected' : ''; ?>>Прочитанные</option>
                                <option value="answered" <?php echo isset($_GET['contact_status']) && $_GET['contact_status'] === 'answered' ? 'selected' : ''; ?>>Отвеченные</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Применить фильтры
                            </button>
                            <?php if (isset($_GET['contact_search']) || isset($_GET['contact_status'])): ?>
                                <a href="?tab=contacts" class="btn btn-outline">
                                    <i class="fas fa-times"></i> Сбросить
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                    
                    <!-- Таблица контактов -->
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Имя</th>
                                    <th>Телефон</th>
                                    <th>Email</th>
                                    <th>Тема</th>
                                    <th>Сообщение</th>
                                    <th>Статус</th>
                                    <th>Дата</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($contact_result) > 0): ?>
                                    <?php while ($contact = mysqli_fetch_assoc($contact_result)): ?>
                                        <tr>
                                            <td>#<?php echo $contact['id']; ?></td>
                                            <td><?php echo htmlspecialchars($contact['name']); ?></td>
                                            <td><?php echo htmlspecialchars($contact['phone']); ?></td>
                                            <td><?php echo $contact['email'] ? htmlspecialchars($contact['email']) : '-'; ?></td>
                                            <td><?php echo $contact['subject'] ? htmlspecialchars($contact['subject']) : '-'; ?></td>
                                            <td class="message-preview" title="<?php echo htmlspecialchars($contact['message']); ?>">
                                                <?php echo mb_strlen($contact['message']) > 50 ? htmlspecialchars(mb_substr($contact['message'], 0, 50)) . '...' : htmlspecialchars($contact['message']); ?>
                                            </td>
                                            <td>
                                                <span class="badge 
                                                    <?php echo $contact['status'] === 'new' ? 'badge-warning' : 
                                                           ($contact['status'] === 'read' ? 'badge-info' : 'badge-success'); ?>">
                                                    <?php echo $contact['status'] === 'new' ? 'Новый' : 
                                                          ($contact['status'] === 'read' ? 'Прочитан' : 'Отвечен'); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($contact['created_at'])); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                                                        <select name="new_status" class="status-select <?php echo $contact['status']; ?>" 
                                                                onchange="this.form.submit()" name="update_contact_status">
                                                            <option value="new" <?php echo $contact['status'] === 'new' ? 'selected' : ''; ?>>Новый</option>
                                                            <option value="read" <?php echo $contact['status'] === 'read' ? 'selected' : ''; ?>>Прочитан</option>
                                                            <option value="answered" <?php echo $contact['status'] === 'answered' ? 'selected' : ''; ?>>Отвечен</option>
                                                        </select>
                                                    </form>
                                                    <?php if ($contact['status'] === 'answered'): ?>
                                                        <form method="POST" style="display: inline;" 
                                                              onsubmit="return confirm('Вы уверены, что хотите удалить это сообщение?');">
                                                            <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                                                            <button type="submit" name="delete_contact" class="btn btn-sm btn-danger" title="Удалить">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" style="text-align: center; padding: 40px;">
                                            <i class="fas fa-envelope" style="font-size: 48px; color: #e2e8f0; margin-bottom: 15px;"></i>
                                            <p style="color: #718096;">Сообщения не найдены</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="../assets/js/users/index.js"></script>
</body>
</html>