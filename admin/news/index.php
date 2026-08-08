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
$author_filter = isset($_GET['author']) ? $_GET['author'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Построение запроса
$where = ["1=1"];
if ($search) {
    $where[] = "(n.title LIKE '%$search%' OR 
                 n.excerpt LIKE '%$search%' OR 
                 n.content LIKE '%$search%')";
}
if ($status_filter && $status_filter !== 'all') {
    $where[] = "n.status = '$status_filter'";
}
if ($category_filter && $category_filter !== 'all') {
    $where[] = "n.category_id = " . (int)$category_filter;
}
if ($author_filter && $author_filter !== 'all') {
    $where[] = "n.author_id = " . (int)$author_filter;
}
if ($date_from) {
    $where[] = "DATE(n.created_at) >= '$date_from'";
}
if ($date_to) {
    $where[] = "DATE(n.created_at) <= '$date_to'";
}

$where_clause = implode(' AND ', $where);

// Получение новостей
$query = "SELECT n.*, 
          nc.name as category_name,
          na.name as author_name,
          u1.name as created_by_name,
          u2.name as updated_by_name
          FROM news n
          LEFT JOIN news_categories nc ON n.category_id = nc.id
          LEFT JOIN news_authors na ON n.author_id = na.id
          LEFT JOIN users u1 ON n.created_by = u1.id
          LEFT JOIN users u2 ON n.updated_by = u2.id
          WHERE $where_clause
          ORDER BY n.created_at DESC 
          LIMIT $offset, $per_page";

$result = mysqli_query($conn, $query);

// Общее количество новостей для пагинации
$count_query = "SELECT COUNT(*) as total FROM news n WHERE $where_clause";
$count_result = mysqli_query($conn, $count_query);
$total_news = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_news / $per_page);

// Получение категорий для фильтра
$categories_query = "SELECT * FROM news_categories WHERE is_active = 1 ORDER BY sort_order";
$categories_result = mysqli_query($conn, $categories_query);

// Получение авторов для фильтра
$authors_query = "SELECT * FROM news_authors WHERE is_active = 1 ORDER BY name";
$authors_result = mysqli_query($conn, $authors_query);

// Статистика
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
    SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
    SUM(CASE WHEN status = 'archived' THEN 1 ELSE 0 END) as archived,
    SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured,
    SUM(CASE WHEN is_pinned = 1 THEN 1 ELSE 0 END) as pinned
    FROM news";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление новостями - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style/news/index.css">
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Управление новостями</h1>
            <div class="quick-actions">
                <a href="create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Добавить новость
                </a>
            </div>
        </div>
        
        <!-- Статистика -->
        <div class="stats-grid">
            <div class="stat-card total-news">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Всего новостей</div>
            </div>
            <div class="stat-card published-news">
                <div class="stat-number"><?php echo $stats['published']; ?></div>
                <div class="stat-label">Опубликовано</div>
            </div>
            <div class="stat-card draft-news">
                <div class="stat-number"><?php echo $stats['draft']; ?></div>
                <div class="stat-label">Черновиков</div>
            </div>
            <div class="stat-card archived-news">
                <div class="stat-number"><?php echo $stats['archived']; ?></div>
                <div class="stat-label">В архиве</div>
            </div>
            <div class="stat-card featured-news">
                <div class="stat-number"><?php echo $stats['featured']; ?></div>
                <div class="stat-label">Избранных</div>
            </div>
            <div class="stat-card pinned-news">
                <div class="stat-number"><?php echo $stats['pinned']; ?></div>
                <div class="stat-label">Закреплено</div>
            </div>
        </div>
        
        <!-- Фильтры -->
        <form method="GET" class="filter-form">
            <div class="filter-grid">
                <div class="form-group">
                    <label for="search">Поиск</label>
                    <input type="text" id="search" name="search" class="form-control" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Заголовок, краткое описание, текст">
                </div>
                
                <div class="form-group">
                    <label for="status">Статус</label>
                    <select id="status" name="status" class="form-control">
                        <option value="all">Все статусы</option>
                        <option value="published" <?php echo $status_filter == 'published' ? 'selected' : ''; ?>>Опубликовано</option>
                        <option value="draft" <?php echo $status_filter == 'draft' ? 'selected' : ''; ?>>Черновик</option>
                        <option value="archived" <?php echo $status_filter == 'archived' ? 'selected' : ''; ?>>Архив</option>
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
                    <label for="author">Автор</label>
                    <select id="author" name="author" class="form-control">
                        <option value="all">Все авторы</option>
                        <?php mysqli_data_seek($authors_result, 0); ?>
                        <?php while($author = mysqli_fetch_assoc($authors_result)): ?>
                            <option value="<?php echo $author['id']; ?>" 
                                <?php echo $author_filter == $author['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($author['name']); ?>
                            </option>
                        <?php endwhile; ?>
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
                    <?php if ($search || $status_filter != 'all' || $category_filter != 'all' || $author_filter != 'all' || $date_from || $date_to): ?>
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
        
        <!-- Таблица новостей -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 30%;">Заголовок</th>
                        <th style="width: 15%;">Категория</th>
                        <th style="width: 15%;">Автор</th>
                        <th style="width: 10%;">Статус</th>
                        <th style="width: 15%;">Дата</th>
                        <th style="width: 15%;">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php mysqli_data_seek($result, 0); ?>
                        <?php while ($news = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <div class="news-title">
                                        <?php echo htmlspecialchars($news['title']); ?>
                                        <?php if ($news['is_featured']): ?>
                                            <span class="badge-featured">Избранная</span>
                                        <?php endif; ?>
                                        <?php if ($news['is_pinned']): ?>
                                            <span class="badge-pinned">Закреплена</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="news-meta">
                                        <span><i class="fas fa-eye"></i> <?php echo $news['views_count']; ?></span>
                                        <span><i class="fas fa-comment"></i> <?php echo $news['comments_count']; ?></span>
                                        <span><i class="fas fa-share"></i> <?php echo $news['shares_count']; ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($news['category_name'] ?? 'Без категории'); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($news['author_name'] ?? 'Не указан'); ?>
                                </td>
                                <td>
                                    <?php 
                                    $status_class = 'badge-' . $news['status'];
                                    $status_text = '';
                                    switch($news['status']) {
                                        case 'published': $status_text = 'Опубликовано'; break;
                                        case 'draft': $status_text = 'Черновик'; break;
                                        case 'archived': $status_text = 'Архив'; break;
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                                <td class="date-cell">
                                    <?php echo date('d.m.Y H:i', strtotime($news['created_at'])); ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="view.php?id=<?php echo $news['id']; ?>" 
                                           class="btn btn-sm btn-outline" title="Просмотр">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit.php?id=<?php echo $news['id']; ?>" 
                                           class="btn btn-sm btn-primary" title="Редактировать">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="../news/<?php echo $news['slug']; ?>" target="_blank"
                                           class="btn btn-sm btn-success" title="На сайте">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                        <a href="delete.php?id=<?php echo $news['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Вы уверены, что хотите удалить эту новость?');"
                                           title="Удалить">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="no-data">
                                <i class="fas fa-newspaper"></i>
                                <p>Новости не найдены</p>
                                <a href="create.php" class="btn btn-primary" style="margin-top: 15px;">
                                    <i class="fas fa-plus"></i> Создать первую новость
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
                    <a href="?page=<?php echo $page-1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $category_filter ? '&category=' . $category_filter : ''; ?><?php echo $author_filter ? '&author=' . $author_filter : ''; ?><?php echo $date_from ? '&date_from=' . $date_from : ''; ?><?php echo $date_to ? '&date_to=' . $date_to : ''; ?>" 
                       class="page-link">
                        <i class="fas fa-chevron-left"></i> Назад
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="page-link active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $category_filter ? '&category=' . $category_filter : ''; ?><?php echo $author_filter ? '&author=' . $author_filter : ''; ?><?php echo $date_from ? '&date_from=' . $date_from : ''; ?><?php echo $date_to ? '&date_to=' . $date_to : ''; ?>" 
                           class="page-link"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page+1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $category_filter ? '&category=' . $category_filter : ''; ?><?php echo $author_filter ? '&author=' . $author_filter : ''; ?><?php echo $date_from ? '&date_from=' . $date_from : ''; ?><?php echo $date_to ? '&date_to=' . $date_to : ''; ?>" 
                       class="page-link">
                        Вперед <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
    
    <script src="../assets/js/news/index.js"></script>
</body>
</html>