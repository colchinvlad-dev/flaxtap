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
$city_filter = isset($_GET['city']) ? $_GET['city'] : '';
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';
$active_filter = isset($_GET['active']) ? $_GET['active'] : '';
$has_parking_filter = isset($_GET['has_parking']) ? $_GET['has_parking'] : '';

// Построение запроса
$where = ["1=1"];
if ($search) {
    $where[] = "(s.name LIKE '%$search%' OR 
                 s.address LIKE '%$search%' OR 
                 s.city LIKE '%$search%')";
}
if ($city_filter && $city_filter !== 'all') {
    $where[] = "s.city = '$city_filter'";
}
if ($type_filter && $type_filter !== 'all') {
    $where[] = "s.type = '$type_filter'";
}
if ($active_filter !== '') {
    $active_value = $active_filter == '1' ? 1 : 0;
    $where[] = "s.is_active = $active_value";
}
if ($has_parking_filter !== '') {
    $parking_value = $has_parking_filter == '1' ? 1 : 0;
    $where[] = "s.has_parking = $parking_value";
}

$where_clause = implode(' AND ', $where);

// Получение магазинов
$query = "SELECT s.*, 
          u1.name as created_by_name,
          u2.name as updated_by_name,
          (SELECT COUNT(*) FROM store_reviews sr WHERE sr.store_id = s.id) as reviews_count,
          (SELECT AVG(rating) FROM store_reviews sr WHERE sr.store_id = s.id AND sr.is_approved = 1) as avg_rating
          FROM stores s
          LEFT JOIN users u1 ON s.created_by = u1.id
          LEFT JOIN users u2 ON s.updated_by = u2.id
          WHERE $where_clause
          ORDER BY s.sort_order ASC, s.name ASC
          LIMIT $offset, $per_page";

$result = mysqli_query($conn, $query);

// Общее количество магазинов для пагинации
$count_query = "SELECT COUNT(*) as total FROM stores s WHERE $where_clause";
$count_result = mysqli_query($conn, $count_query);
$total_stores = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_stores / $per_page);

// Получение уникальных городов для фильтра
$cities_query = "SELECT DISTINCT city FROM stores WHERE city IS NOT NULL AND city != '' ORDER BY city";
$cities_result = mysqli_query($conn, $cities_query);

// Статистика
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive,
    SUM(CASE WHEN type = 'shop' THEN 1 ELSE 0 END) as shops,
    SUM(CASE WHEN type = 'warehouse_shop' THEN 1 ELSE 0 END) as warehouses,
    SUM(CASE WHEN type = 'pickup_point' THEN 1 ELSE 0 END) as pickup_points,
    SUM(CASE WHEN has_parking = 1 THEN 1 ELSE 0 END) as with_parking
    FROM stores";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление магазинами - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style/stores/index.css">
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Управление магазинами</h1>
            <div class="quick-actions">
                <a href="create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Добавить магазин
                </a>
            </div>
        </div>
        
        <!-- Статистика -->
        <div class="stats-grid">
            <div class="stat-card total-stores">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Всего магазинов</div>
            </div>
            <div class="stat-card active-stores">
                <div class="stat-number"><?php echo $stats['active']; ?></div>
                <div class="stat-label">Активных</div>
            </div>
            <div class="stat-card inactive-stores">
                <div class="stat-number"><?php echo $stats['inactive']; ?></div>
                <div class="stat-label">Неактивных</div>
            </div>
            <div class="stat-card shops-stores">
                <div class="stat-number"><?php echo $stats['shops']; ?></div>
                <div class="stat-label">Магазины</div>
            </div>
            <div class="stat-card warehouses-stores">
                <div class="stat-number"><?php echo $stats['warehouses']; ?></div>
                <div class="stat-label">Склады-магазины</div>
            </div>
            <div class="stat-card pickup-points">
                <div class="stat-number"><?php echo $stats['pickup_points']; ?></div>
                <div class="stat-label">Пункты выдачи</div>
            </div>
        </div>
        
        <!-- Фильтры -->
        <form method="GET" class="filter-form">
            <div class="filter-grid">
                <div class="form-group">
                    <label for="search">Поиск</label>
                    <input type="text" id="search" name="search" class="form-control" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Название, адрес, город">
                </div>
                
                <div class="form-group">
                    <label for="city">Город</label>
                    <select id="city" name="city" class="form-control">
                        <option value="all">Все города</option>
                        <?php while($city = mysqli_fetch_assoc($cities_result)): ?>
                            <option value="<?php echo htmlspecialchars($city['city']); ?>" 
                                <?php echo $city_filter == $city['city'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($city['city']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="type">Тип</label>
                    <select id="type" name="type" class="form-control">
                        <option value="all">Все типы</option>
                        <option value="shop" <?php echo $type_filter == 'shop' ? 'selected' : ''; ?>>Магазин</option>
                        <option value="warehouse_shop" <?php echo $type_filter == 'warehouse_shop' ? 'selected' : ''; ?>>Склад-магазин</option>
                        <option value="pickup_point" <?php echo $type_filter == 'pickup_point' ? 'selected' : ''; ?>>Пункт выдачи</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="active">Статус</label>
                    <select id="active" name="active" class="form-control">
                        <option value="">Все статусы</option>
                        <option value="1" <?php echo $active_filter === '1' ? 'selected' : ''; ?>>Активные</option>
                        <option value="0" <?php echo $active_filter === '0' ? 'selected' : ''; ?>>Неактивные</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="has_parking">Парковка</label>
                    <select id="has_parking" name="has_parking" class="form-control">
                        <option value="">Все</option>
                        <option value="1" <?php echo $has_parking_filter === '1' ? 'selected' : ''; ?>>С парковкой</option>
                        <option value="0" <?php echo $has_parking_filter === '0' ? 'selected' : ''; ?>>Без парковки</option>
                    </select>
                </div>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Применить фильтры
                    </button>
                    <?php if ($search || $city_filter != 'all' || $type_filter != 'all' || $active_filter !== '' || $has_parking_filter !== ''): ?>
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
        
        <!-- Таблица магазинов -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 30%;">Магазин</th>
                        <th style="width: 20%;">Адрес</th>
                        <th style="width: 15%;">Город</th>
                        <th style="width: 15%;">Статус</th>
                        <th style="width: 20%;">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php mysqli_data_seek($result, 0); ?>
                        <?php while ($store = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <div class="store-name">
                                        <?php echo htmlspecialchars($store['name']); ?>
                                        <?php if ($store['is_default']): ?>
                                            <span class="type-badge badge-shop">По умолчанию</span>
                                        <?php endif; ?>
                                        <span class="type-badge 
                                            <?php 
                                            if ($store['type'] == 'shop') echo 'badge-shop';
                                            elseif ($store['type'] == 'warehouse_shop') echo 'badge-warehouse';
                                            else echo 'badge-pickup';
                                            ?>">
                                            <?php 
                                            if ($store['type'] == 'shop') echo 'Магазин';
                                            elseif ($store['type'] == 'warehouse_shop') echo 'Склад-магазин';
                                            else echo 'Пункт выдачи';
                                            ?>
                                        </span>
                                    </div>
                                    <div class="store-meta">
                                        <?php if ($store['phone']): ?>
                                            <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($store['phone']); ?></span>
                                        <?php endif; ?>
                                        <?php if ($store['reviews_count'] > 0): ?>
                                            <span><i class="fas fa-star"></i> 
                                                <?php echo number_format($store['avg_rating'], 1); ?> 
                                                (<?php echo $store['reviews_count']; ?> отзывов)
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($store['has_parking']): ?>
                                            <span><i class="fas fa-parking"></i> Есть парковка</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($store['address']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($store['city']); ?>
                                </td>
                                <td>
                                    <?php if ($store['is_active']): ?>
                                        <span class="status-badge badge-active">Активен</span>
                                    <?php else: ?>
                                        <span class="status-badge badge-inactive">Неактивен</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="view.php?id=<?php echo $store['id']; ?>" 
                                           class="btn btn-sm btn-outline" title="Просмотр">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit.php?id=<?php echo $store['id']; ?>" 
                                           class="btn btn-sm btn-primary" title="Редактировать">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete.php?id=<?php echo $store['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Вы уверены, что хотите удалить этот магазин?');"
                                           title="Удалить">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <a href="reviews.php?store_id=<?php echo $store['id']; ?>" 
                                           class="btn btn-sm btn-success" title="Отзывы">
                                            <i class="fas fa-star"></i>
                                            <?php if ($store['reviews_count'] > 0): ?>
                                                <span style="background: var(--primary-color); color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 10px; margin-left: 5px;">
                                                    <?php echo $store['reviews_count']; ?>
                                                </span>
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="no-data">
                                <i class="fas fa-store"></i>
                                <p>Магазины не найдены</p>
                                <a href="create.php" class="btn btn-primary" style="margin-top: 15px;">
                                    <i class="fas fa-plus"></i> Добавить первый магазин
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
                    <a href="?page=<?php echo $page-1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $city_filter ? '&city=' . urlencode($city_filter) : ''; ?><?php echo $type_filter ? '&type=' . $type_filter : ''; ?><?php echo $active_filter !== '' ? '&active=' . $active_filter : ''; ?><?php echo $has_parking_filter !== '' ? '&has_parking=' . $has_parking_filter : ''; ?>" 
                       class="page-link">
                        <i class="fas fa-chevron-left"></i> Назад
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="page-link active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $city_filter ? '&city=' . urlencode($city_filter) : ''; ?><?php echo $type_filter ? '&type=' . $type_filter : ''; ?><?php echo $active_filter !== '' ? '&active=' . $active_filter : ''; ?><?php echo $has_parking_filter !== '' ? '&has_parking=' . $has_parking_filter : ''; ?>" 
                           class="page-link"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page+1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $city_filter ? '&city=' . urlencode($city_filter) : ''; ?><?php echo $type_filter ? '&type=' . $type_filter : ''; ?><?php echo $active_filter !== '' ? '&active=' . $active_filter : ''; ?><?php echo $has_parking_filter !== '' ? '&has_parking=' . $has_parking_filter : ''; ?>" 
                       class="page-link">
                        Вперед <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
    
    <script src="../assets/js/stores/index.js"></script>
</body>
</html>