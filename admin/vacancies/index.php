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
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$work_type_filter = isset($_GET['work_type']) ? $_GET['work_type'] : '';

// Построение запроса
$where = ["1=1"];
if ($search) {
    $where[] = "(v.title LIKE '%$search%' OR 
                 v.description LIKE '%$search%' OR 
                 v.requirements LIKE '%$search%')";
}
if ($status_filter && $status_filter !== 'all') {
    $where[] = "v.status = '$status_filter'";
}
if ($category_filter && $category_filter !== 'all') {
    $where[] = "v.category_id = " . (int)$category_filter;
}
if ($work_type_filter && $work_type_filter !== 'all') {
    $where[] = "v.work_type = '$work_type_filter'";
}

$where_clause = implode(' AND ', $where);

// Получение вакансий
$query = "SELECT v.*, 
          vc.name as category_name,
          u1.name as created_by_name,
          u2.name as updated_by_name,
          (SELECT COUNT(*) FROM vacancy_applications va WHERE va.vacancy_id = v.id) as applications_count
          FROM vacancies v
          LEFT JOIN vacancy_categories vc ON v.category_id = vc.id
          LEFT JOIN users u1 ON v.created_by = u1.id
          LEFT JOIN users u2 ON v.updated_by = u2.id
          WHERE $where_clause
          ORDER BY v.created_at DESC 
          LIMIT $offset, $per_page";

$result = mysqli_query($conn, $query);

// Общее количество вакансий для пагинации
$count_query = "SELECT COUNT(*) as total FROM vacancies v WHERE $where_clause";
$count_result = mysqli_query($conn, $count_query);
$total_vacancies = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_vacancies / $per_page);

// Получение категорий для фильтра
$categories_query = "SELECT * FROM vacancy_categories WHERE is_active = 1 ORDER BY sort_order";
$categories_result = mysqli_query($conn, $categories_query);

// Статистика
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
    SUM(CASE WHEN status = 'archived' THEN 1 ELSE 0 END) as archived,
    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed,
    SUM(CASE WHEN is_published = 1 THEN 1 ELSE 0 END) as published
    FROM vacancies";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление вакансиями - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style/vacancies/index.css">
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Управление вакансиями</h1>
            <div class="quick-actions">
                <a href="create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Новая вакансия
                </a>
            </div>
        </div>
        
        <!-- Статистика -->
        <div class="stats-grid">
            <div class="stat-card total-vacancies">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Всего вакансий</div>
            </div>
            <div class="stat-card active-vacancies">
                <div class="stat-number"><?php echo $stats['active']; ?></div>
                <div class="stat-label">Активных</div>
            </div>
            <div class="stat-card draft-vacancies">
                <div class="stat-number"><?php echo $stats['draft']; ?></div>
                <div class="stat-label">Черновиков</div>
            </div>
            <div class="stat-card archived-vacancies">
                <div class="stat-number"><?php echo $stats['archived']; ?></div>
                <div class="stat-label">В архиве</div>
            </div>
            <div class="stat-card published-vacancies">
                <div class="stat-number"><?php echo $stats['published']; ?></div>
                <div class="stat-label">Опубликовано</div>
            </div>
        </div>
        
        <!-- Фильтры -->
        <form method="GET" class="filter-form">
            <div class="form-group">
                <label for="search">Поиск</label>
                <input type="text" id="search" name="search" class="form-control" 
                       value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Название, описание, требования">
            </div>
            
            <div class="form-group">
                <label for="status">Статус</label>
                <select id="status" name="status" class="form-control">
                    <option value="all">Все статусы</option>
                    <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Активные</option>
                    <option value="draft" <?php echo $status_filter == 'draft' ? 'selected' : ''; ?>>Черновики</option>
                    <option value="archived" <?php echo $status_filter == 'archived' ? 'selected' : ''; ?>>Архив</option>
                    <option value="closed" <?php echo $status_filter == 'closed' ? 'selected' : ''; ?>>Закрытые</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="category">Категория</label>
                <select id="category" name="category" class="form-control">
                    <option value="all">Все категории</option>
                    <?php while($category = mysqli_fetch_assoc($categories_result)): ?>
                        <option value="<?php echo $category['id']; ?>" 
                            <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="work_type">Тип работы</label>
                <select id="work_type" name="work_type" class="form-control">
                    <option value="all">Все типы</option>
                    <option value="office" <?php echo $work_type_filter == 'office' ? 'selected' : ''; ?>>Офис</option>
                    <option value="remote" <?php echo $work_type_filter == 'remote' ? 'selected' : ''; ?>>Удаленно</option>
                    <option value="hybrid" <?php echo $work_type_filter == 'hybrid' ? 'selected' : ''; ?>>Гибрид</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>&nbsp;</label>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Применить
                    </button>
                    <?php if ($search || $status_filter || $category_filter || $work_type_filter): ?>
                        <a href="?" class="btn btn-outline">
                            <i class="fas fa-times"></i> Сбросить
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
        
        <!-- Таблица вакансий -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 40%;">Вакансия</th>
                        <th style="width: 15%;">Статус</th>
                        <th style="width: 15%;">Тип работы</th>
                        <th style="width: 15%;">Дата</th>
                        <th style="width: 15%;">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php mysqli_data_seek($result, 0); ?>
                        <?php while ($vacancy = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <div class="vacancy-title">
                                        <?php echo htmlspecialchars($vacancy['title']); ?>
                                        <?php if ($vacancy['is_published']): ?>
                                            <span class="priority-badge badge-normal">Опубликовано</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="vacancy-meta">
                                        <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($vacancy['category_name'] ?? 'Без категории'); ?></span>
                                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($vacancy['location_city'] ?? 'Не указан'); ?></span>
                                        <?php if ($vacancy['applications_count'] > 0): ?>
                                            <span><i class="fas fa-users"></i> Откликов: <?php echo $vacancy['applications_count']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $status_class = 'badge-' . $vacancy['status'];
                                    $status_text = '';
                                    switch($vacancy['status']) {
                                        case 'active': $status_text = 'Активная'; break;
                                        case 'draft': $status_text = 'Черновик'; break;
                                        case 'archived': $status_text = 'Архив'; break;
                                        case 'closed': $status_text = 'Закрыта'; break;
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                    <?php if ($vacancy['priority'] == 'urgent'): ?>
                                        <span class="priority-badge badge-urgent">Срочно</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $work_type_text = '';
                                    switch($vacancy['work_type']) {
                                        case 'office': $work_type_text = 'Офис'; break;
                                        case 'remote': $work_type_text = 'Удаленно'; break;
                                        case 'hybrid': $work_type_text = 'Гибрид'; break;
                                    }
                                    echo $work_type_text;
                                    ?>
                                </td>
                                <td class="date-cell">
                                    <?php echo date('d.m.Y', strtotime($vacancy['created_at'])); ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="view.php?id=<?php echo $vacancy['id']; ?>" 
                                           class="btn btn-sm btn-outline" title="Просмотр">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit.php?id=<?php echo $vacancy['id']; ?>" 
                                           class="btn btn-sm btn-primary" title="Редактировать">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="view.php?id=<?php echo $vacancy['id']; ?>&tab=applications" 
                                           class="btn btn-sm btn-success" title="Отклики">
                                            <i class="fas fa-users"></i>
                                            <?php if ($vacancy['applications_count'] > 0): ?>
                                                <span class="applications-count"><?php echo $vacancy['applications_count']; ?></span>
                                            <?php endif; ?>
                                        </a>
                                        <a href="delete.php?id=<?php echo $vacancy['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Вы уверены, что хотите удалить эту вакансию?');"
                                           title="Удалить">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px;">
                                <i class="fas fa-briefcase" style="font-size: 48px; color: #e2e8f0; margin-bottom: 15px;"></i>
                                <p style="color: #718096;">Вакансии не найдены</p>
                                <a href="create.php" class="btn btn-primary" style="margin-top: 15px;">
                                    <i class="fas fa-plus"></i> Создать первую вакансию
                                </a>
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
                    <a href="?page=<?php echo $page-1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $category_filter ? '&category=' . $category_filter : ''; ?><?php echo $work_type_filter ? '&work_type=' . $work_type_filter : ''; ?>" 
                       class="page-link">
                        <i class="fas fa-chevron-left"></i> Назад
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="page-link active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $category_filter ? '&category=' . $category_filter : ''; ?><?php echo $work_type_filter ? '&work_type=' . $work_type_filter : ''; ?>" 
                           class="page-link"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page+1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $category_filter ? '&category=' . $category_filter : ''; ?><?php echo $work_type_filter ? '&work_type=' . $work_type_filter : ''; ?>" 
                       class="page-link">
                        Вперед <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
    
    <script src="../assets/js/vacancies/index.js"></script>
</body>
</html>