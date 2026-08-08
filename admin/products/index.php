<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

// Определяем активную вкладку (продукты или вопросы)
$active_tab = isset($_GET['tab']) && $_GET['tab'] === 'questions' ? 'questions' : 'products';

// Параметры пагинации для продуктов
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Параметры поиска и фильтрации для продуктов
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$brand_filter = isset($_GET['brand']) ? mysqli_real_escape_string($conn, $_GET['brand']) : '';
$in_stock_filter = isset($_GET['in_stock']) ? $_GET['in_stock'] : '';
$featured_filter = isset($_GET['featured']) ? $_GET['featured'] : '';
$bestseller_filter = isset($_GET['bestseller']) ? $_GET['bestseller'] : '';

// Построение запроса для продуктов
$where = ["p.id > 0"];
if ($search) {
    $where[] = "(p.name LIKE '%$search%' OR 
                 p.brand LIKE '%$search%' OR 
                 p.short_description LIKE '%$search%')";
}
if ($category_filter > 0) {
    $where[] = "p.category_id = $category_filter";
}
if ($brand_filter && $brand_filter !== 'all') {
    $where[] = "p.brand = '$brand_filter'";
}
if ($in_stock_filter !== '') {
    $in_stock_value = $in_stock_filter == '1' ? 1 : 0;
    $where[] = "p.in_stock = $in_stock_value";
}
if ($featured_filter !== '') {
    $featured_value = $featured_filter == '1' ? 1 : 0;
    $where[] = "p.is_featured = $featured_value";
}
if ($bestseller_filter !== '') {
    $bestseller_value = $bestseller_filter == '1' ? 1 : 0;
    $where[] = "p.is_bestseller = $bestseller_value";
}

$where_clause = implode(' AND ', $where);

// Получение продуктов
$products_query = "SELECT p.*, 
                  c.name as category_name,
                  (SELECT COUNT(*) FROM product_questions pq WHERE pq.product_id = p.id) as questions_count,
                  (SELECT COUNT(*) FROM product_questions pq WHERE pq.product_id = p.id AND pq.is_answered = 0) as unanswered_questions,
                  (SELECT AVG(rating) FROM product_reviews pr WHERE pr.product_id = p.id AND pr.is_approved = 1) as avg_rating,
                  (SELECT COUNT(*) FROM product_reviews pr WHERE pr.product_id = p.id AND pr.is_approved = 1) as reviews_count
                  FROM products p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE $where_clause
                  ORDER BY p.created_at DESC
                  LIMIT $offset, $per_page";

$products_result = mysqli_query($conn, $products_query);

// Общее количество продуктов для пагинации
$count_query = "SELECT COUNT(*) as total FROM products p WHERE $where_clause";
$count_result = mysqli_query($conn, $count_query);
$total_products = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_products / $per_page);

// Получение категорий для фильтра
$categories_query = "SELECT id, name FROM categories WHERE parent_id IS NOT NULL AND is_active = 1 ORDER BY name";
$categories_result = mysqli_query($conn, $categories_query);

// Получение уникальных брендов для фильтра
$brands_query = "SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL AND brand != '' ORDER BY brand";
$brands_result = mysqli_query($conn, $brands_query);

// Статистика продуктов
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN in_stock = 1 THEN 1 ELSE 0 END) as in_stock,
    SUM(CASE WHEN in_stock = 0 THEN 1 ELSE 0 END) as out_of_stock,
    SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured,
    SUM(CASE WHEN is_bestseller = 1 THEN 1 ELSE 0 END) as bestsellers,
    SUM(CASE WHEN discount_percent > 0 THEN 1 ELSE 0 END) as on_sale,
    AVG(current_price) as avg_price
    FROM products";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Параметры для вопросов
$questions_page = isset($_GET['q_page']) ? (int)$_GET['q_page'] : 1;
$questions_per_page = 15;
$questions_offset = ($questions_page - 1) * $questions_per_page;

// Фильтры для вопросов
$questions_search = isset($_GET['q_search']) ? mysqli_real_escape_string($conn, $_GET['q_search']) : '';
$questions_answered = isset($_GET['q_answered']) ? $_GET['q_answered'] : '';
$questions_product_filter = isset($_GET['q_product']) ? (int)$_GET['q_product'] : 0;

// Построение запроса для вопросов
$questions_where = ["1=1"];
if ($questions_search) {
    $questions_where[] = "(pq.question LIKE '%$questions_search%' OR 
                          pq.answer LIKE '%$questions_search%' OR 
                          pq.user_name LIKE '%$questions_search%')";
}
if ($questions_answered !== '') {
    $answered_value = $questions_answered == '1' ? 1 : 0;
    $questions_where[] = "pq.is_answered = $answered_value";
}
if ($questions_product_filter > 0) {
    $questions_where[] = "pq.product_id = $questions_product_filter";
}

$questions_where_clause = implode(' AND ', $questions_where);

// Получение вопросов
$questions_query = "SELECT pq.*, 
                   p.name as product_name,
                   p.slug as product_slug,
                   u.name as answered_by_name
                   FROM product_questions pq
                   LEFT JOIN products p ON pq.product_id = p.id
                   LEFT JOIN users u ON pq.answered_by = u.id
                   WHERE $questions_where_clause
                   ORDER BY pq.is_answered ASC, pq.created_at DESC
                   LIMIT $questions_offset, $questions_per_page";

$questions_result = mysqli_query($conn, $questions_query);

// Общее количество вопросов для пагинации
$questions_count_query = "SELECT COUNT(*) as total FROM product_questions pq WHERE $questions_where_clause";
$questions_count_result = mysqli_query($conn, $questions_count_query);
$total_questions = mysqli_fetch_assoc($questions_count_result)['total'];
$questions_total_pages = ceil($total_questions / $questions_per_page);

// Получение продуктов для фильтра вопросов
$questions_products_query = "SELECT DISTINCT p.id, p.name 
                            FROM product_questions pq
                            JOIN products p ON pq.product_id = p.id
                            ORDER BY p.name";
$questions_products_result = mysqli_query($conn, $questions_products_query);

// Обработка ответа на вопрос
if (isset($_POST['answer_question'])) {
    $question_id = (int)$_POST['question_id'];
    $answer = mysqli_real_escape_string($conn, $_POST['answer']);
    $admin_id = $_SESSION['admin_id'] ?? 0;
    
    $update_query = "UPDATE product_questions SET 
                    answer = '$answer',
                    answered_by = $admin_id,
                    is_answered = 1,
                    answered_at = NOW()
                    WHERE id = $question_id";
    
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success'] = "Ответ успешно добавлен";
    } else {
        $_SESSION['error'] = "Ошибка при добавлении ответа: " . mysqli_error($conn);
    }
    
    // Перенаправляем, чтобы избежать повторной отправки формы
    header("Location: ?tab=questions");
    exit();
}

// Обработка удаления вопроса
if (isset($_GET['delete_question'])) {
    $question_id = (int)$_GET['delete_question'];
    $delete_query = "DELETE FROM product_questions WHERE id = $question_id";
    
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['success'] = "Вопрос успешно удален";
    } else {
        $_SESSION['error'] = "Ошибка при удалении вопроса: " . mysqli_error($conn);
    }
    
    header("Location: ?tab=questions");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление продукцией - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style/products/index.css">
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Управление продукцией</h1>
            <div class="quick-actions">
                <a href="create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Добавить продукт
                </a>
            </div>
        </div>
        
        <!-- Статистика -->
        <div class="stats-grid">
            <div class="stat-card total-products">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Всего товаров</div>
            </div>
            <div class="stat-card in-stock">
                <div class="stat-number"><?php echo $stats['in_stock']; ?></div>
                <div class="stat-label">В наличии</div>
            </div>
            <div class="stat-card out-of-stock">
                <div class="stat-number"><?php echo $stats['out_of_stock']; ?></div>
                <div class="stat-label">Нет в наличии</div>
            </div>
            <div class="stat-card featured">
                <div class="stat-number"><?php echo $stats['featured']; ?></div>
                <div class="stat-label">Рекомендуемые</div>
            </div>
            <div class="stat-card bestsellers">
                <div class="stat-number"><?php echo $stats['bestsellers']; ?></div>
                <div class="stat-label">Хиты продаж</div>
            </div>
            <div class="stat-card on-sale">
                <div class="stat-number"><?php echo $stats['on_sale']; ?></div>
                <div class="stat-label">Со скидкой</div>
            </div>
            <div class="stat-card avg-price">
                <div class="stat-number"><?php echo number_format($stats['avg_price'], 2); ?> ₽</div>
                <div class="stat-label">Средняя цена</div>
            </div>
        </div>
        
        <!-- Вкладки -->
        <div class="tabs">
            <a href="?tab=products" class="tab <?php echo $active_tab === 'products' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i> Товары
                <span class="tab-badge"><?php echo $total_products; ?></span>
            </a>
            <a href="?tab=questions" class="tab <?php echo $active_tab === 'questions' ? 'active' : ''; ?>">
                <i class="fas fa-question-circle"></i> Вопросы и ответы
                <?php 
                $unanswered_count = mysqli_fetch_assoc(mysqli_query($conn, 
                    "SELECT COUNT(*) as count FROM product_questions WHERE is_answered = 0"))['count'];
                ?>
                <span class="tab-badge" style="background: <?php echo $unanswered_count > 0 ? 'var(--danger-color)' : 'var(--primary-color)'; ?>">
                    <?php echo $unanswered_count; ?>
                </span>
            </a>
        </div>
        
        <!-- Содержимое вкладки товаров -->
        <?php if ($active_tab === 'products'): ?>
            <!-- Фильтры товаров -->
            <form method="GET" class="filter-form">
                <input type="hidden" name="tab" value="products">
                <div class="filter-grid">
                    <div class="form-group">
                        <label for="search">Поиск</label>
                        <input type="text" id="search" name="search" class="form-control" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Название, бренд, описание">
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Категория</label>
                        <select id="category" name="category" class="form-control">
                            <option value="0">Все категории</option>
                            <?php while($category = mysqli_fetch_assoc($categories_result)): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                    <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="brand">Бренд</label>
                        <select id="brand" name="brand" class="form-control">
                            <option value="all">Все бренды</option>
                            <?php mysqli_data_seek($brands_result, 0); ?>
                            <?php while($brand = mysqli_fetch_assoc($brands_result)): ?>
                                <option value="<?php echo htmlspecialchars($brand['brand']); ?>" 
                                    <?php echo $brand_filter == $brand['brand'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($brand['brand']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="in_stock">Наличие</label>
                        <select id="in_stock" name="in_stock" class="form-control">
                            <option value="">Все</option>
                            <option value="1" <?php echo $in_stock_filter === '1' ? 'selected' : ''; ?>>В наличии</option>
                            <option value="0" <?php echo $in_stock_filter === '0' ? 'selected' : ''; ?>>Нет в наличии</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="featured">Рекомендуемые</label>
                        <select id="featured" name="featured" class="form-control">
                            <option value="">Все</option>
                            <option value="1" <?php echo $featured_filter === '1' ? 'selected' : ''; ?>>Только рекомендованные</option>
                            <option value="0" <?php echo $featured_filter === '0' ? 'selected' : ''; ?>>Не рекомендованные</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="bestseller">Хиты продаж</label>
                        <select id="bestseller" name="bestseller" class="form-control">
                            <option value="">Все</option>
                            <option value="1" <?php echo $bestseller_filter === '1' ? 'selected' : ''; ?>>Только хиты</option>
                            <option value="0" <?php echo $bestseller_filter === '0' ? 'selected' : ''; ?>>Не хиты</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Применить фильтры
                        </button>
                        <?php if ($search || $category_filter > 0 || $brand_filter !== 'all' || $in_stock_filter !== '' || $featured_filter !== '' || $bestseller_filter !== ''): ?>
                            <a href="?tab=products" class="btn btn-outline" style="margin-left: 10px;">
                                <i class="fas fa-times"></i> Сбросить
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
            
            <!-- Таблица товаров -->
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">Фото</th>
                            <th style="width: 25%;">Товар</th>
                            <th style="width: 15%;">Категория</th>
                            <th style="width: 15%;">Цена</th>
                            <th style="width: 15%;">Статус</th>
                            <th style="width: 15%;">Вопросы</th>
                            <th style="width: 20%;">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($products_result) > 0): ?>
                            <?php mysqli_data_seek($products_result, 0); ?>
                            <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                             class="product-image"
                                             onerror="this.src='https://via.placeholder.com/60?text=No+Image'">
                                    </td>
                                    <td>
                                        <div class="product-name">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                            <?php if ($product['is_featured']): ?>
                                                <span style="color: #9b59b6; font-size: 12px; margin-left: 5px;">
                                                    <i class="fas fa-star"></i>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($product['is_bestseller']): ?>
                                                <span style="color: #e67e22; font-size: 12px; margin-left: 5px;">
                                                    <i class="fas fa-fire"></i>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-meta">
                                            <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['brand']); ?></span>
                                            <?php if ($product['reviews_count'] > 0): ?>
                                                <span>
                                                    <i class="fas fa-star" style="color: #f39c12;"></i> 
                                                    <?php echo number_format($product['avg_rating'], 1); ?> 
                                                    (<?php echo $product['reviews_count']; ?>)
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($product['weight']): ?>
                                                <span><i class="fas fa-weight"></i> <?php echo htmlspecialchars($product['weight']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($product['category_name']); ?>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="price-current">
                                                <?php echo number_format($product['current_price'], 2); ?> ₽
                                            </span>
                                            <?php if ($product['old_price'] && $product['old_price'] > $product['current_price']): ?>
                                                <br>
                                                <span class="price-old">
                                                    <?php echo number_format($product['old_price'], 2); ?> ₽
                                                </span>
                                                <?php if ($product['discount_percent']): ?>
                                                    <span class="discount-badge">-<?php echo $product['discount_percent']; ?>%</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($product['in_stock']): ?>
                                            <span class="status-badge badge-active">В наличии</span>
                                        <?php else: ?>
                                            <span class="status-badge badge-inactive">Нет в наличии</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($product['questions_count'] > 0): ?>
                                            <div style="display: flex; gap: 5px;">
                                                <span class="questions-badge total" 
                                                      onclick="showProductQuestions(<?php echo $product['id']; ?>)">
                                                    <i class="fas fa-question"></i> <?php echo $product['questions_count']; ?>
                                                </span>
                                                <?php if ($product['unanswered_questions'] > 0): ?>
                                                    <span class="questions-badge unanswered" 
                                                          onclick="showProductQuestions(<?php echo $product['id']; ?>, true)">
                                                        <i class="fas fa-exclamation"></i> <?php echo $product['unanswered_questions']; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span style="color: var(--gray-color); font-size: 12px;">Нет вопросов</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="view.php?id=<?php echo $product['id']; ?>" 
                                               class="btn btn-sm btn-outline" title="Просмотр">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit.php?id=<?php echo $product['id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Редактировать">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete.php?id=<?php echo $product['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Вы уверены, что хотите удалить этот товар?');"
                                               title="Удалить">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <a href="?tab=questions&q_product=<?php echo $product['id']; ?>" 
                                               class="btn btn-sm btn-success" title="Вопросы">
                                                <i class="fas fa-comments"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-data">
                                    <i class="fas fa-box-open"></i>
                                    <p>Товары не найдены</p>
                                    <a href="create.php" class="btn btn-primary" style="margin-top: 15px;">
                                        <i class="fas fa-plus"></i> Добавить первый товар
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Пагинация товаров -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?tab=products&page=<?php echo $page-1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category_filter ? '&category=' . $category_filter : ''; ?><?php echo $brand_filter !== 'all' ? '&brand=' . urlencode($brand_filter) : ''; ?><?php echo $in_stock_filter !== '' ? '&in_stock=' . $in_stock_filter : ''; ?><?php echo $featured_filter !== '' ? '&featured=' . $featured_filter : ''; ?><?php echo $bestseller_filter !== '' ? '&bestseller=' . $bestseller_filter : ''; ?>" 
                           class="page-link">
                            <i class="fas fa-chevron-left"></i> Назад
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="page-link active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?tab=products&page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category_filter ? '&category=' . $category_filter : ''; ?><?php echo $brand_filter !== 'all' ? '&brand=' . urlencode($brand_filter) : ''; ?><?php echo $in_stock_filter !== '' ? '&in_stock=' . $in_stock_filter : ''; ?><?php echo $featured_filter !== '' ? '&featured=' . $featured_filter : ''; ?><?php echo $bestseller_filter !== '' ? '&bestseller=' . $bestseller_filter : ''; ?>" 
                               class="page-link"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?tab=products&page=<?php echo $page+1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category_filter ? '&category=' . $category_filter : ''; ?><?php echo $brand_filter !== 'all' ? '&brand=' . urlencode($brand_filter) : ''; ?><?php echo $in_stock_filter !== '' ? '&in_stock=' . $in_stock_filter : ''; ?><?php echo $featured_filter !== '' ? '&featured=' . $featured_filter : ''; ?><?php echo $bestseller_filter !== '' ? '&bestseller=' . $bestseller_filter : ''; ?>" 
                           class="page-link">
                            Вперед <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
        <!-- Содержимое вкладки вопросов -->
        <?php elseif ($active_tab === 'questions'): ?>
            <!-- Статистика вопросов -->
            <?php
            $questions_stats_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN is_answered = 0 THEN 1 ELSE 0 END) as unanswered,
                SUM(CASE WHEN is_answered = 1 THEN 1 ELSE 0 END) as answered
                FROM product_questions";
            $questions_stats_result = mysqli_query($conn, $questions_stats_query);
            $questions_stats = mysqli_fetch_assoc($questions_stats_result);
            ?>
            
            <div class="questions-stats">
                <div class="stat-card total-products">
                    <div class="stat-number"><?php echo $questions_stats['total']; ?></div>
                    <div class="stat-label">Всего вопросов</div>
                </div>
                <div class="stat-card in-stock">
                    <div class="stat-number"><?php echo $questions_stats['answered']; ?></div>
                    <div class="stat-label">Отвеченные</div>
                </div>
                <div class="stat-card out-of-stock">
                    <div class="stat-number"><?php echo $questions_stats['unanswered']; ?></div>
                    <div class="stat-label">Без ответа</div>
                </div>
            </div>
            
            <!-- Фильтры вопросов -->
            <form method="GET" class="filter-form">
                <input type="hidden" name="tab" value="questions">
                <div class="filter-grid">
                    <div class="form-group">
                        <label for="q_search">Поиск</label>
                        <input type="text" id="q_search" name="q_search" class="form-control" 
                               value="<?php echo htmlspecialchars($questions_search); ?>" 
                               placeholder="Вопрос, ответ, имя пользователя">
                    </div>
                    
                    <div class="form-group">
                        <label for="q_product">Товар</label>
                        <select id="q_product" name="q_product" class="form-control">
                            <option value="0">Все товары</option>
                            <?php while($product = mysqli_fetch_assoc($questions_products_result)): ?>
                                <option value="<?php echo $product['id']; ?>" 
                                    <?php echo $questions_product_filter == $product['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="q_answered">Статус</label>
                        <select id="q_answered" name="q_answered" class="form-control">
                            <option value="">Все вопросы</option>
                            <option value="1" <?php echo $questions_answered === '1' ? 'selected' : ''; ?>>Только отвеченные</option>
                            <option value="0" <?php echo $questions_answered === '0' ? 'selected' : ''; ?>>Только без ответа</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Применить фильтры
                        </button>
                        <?php if ($questions_search || $questions_product_filter > 0 || $questions_answered !== ''): ?>
                            <a href="?tab=questions" class="btn btn-outline" style="margin-left: 10px;">
                                <i class="fas fa-times"></i> Сбросить
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
            
            <!-- Список вопросов -->
            <?php if (mysqli_num_rows($questions_result) > 0): ?>
                <?php mysqli_data_seek($questions_result, 0); ?>
                <?php while ($question = mysqli_fetch_assoc($questions_result)): ?>
                    <div class="question-item <?php echo $question['is_answered'] ? 'answered' : 'unanswered'; ?>">
                        <div class="question-header">
                            <div>
                                <div class="question-product">
                                    <a href="../products/view.php?id=<?php echo $question['product_id']; ?>" target="_blank">
                                        <?php echo htmlspecialchars($question['product_name']); ?>
                                    </a>
                                </div>
                                <div class="question-author">
                                    <?php if ($question['user_name']): ?>
                                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($question['user_name']); ?>
                                    <?php else: ?>
                                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($question['user_name'] ?: 'Аноним'); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="question-date">
                                <?php echo date('d.m.Y H:i', strtotime($question['created_at'])); ?>
                            </div>
                        </div>
                        
                        <div class="question-content">
                            <strong>Вопрос:</strong>
                            <p><?php echo nl2br(htmlspecialchars($question['question'])); ?></p>
                        </div>
                        
                        <?php if ($question['is_answered'] && $question['answer']): ?>
                            <div class="answer-content">
                                <div class="answer-header">
                                    <span>
                                        <i class="fas fa-reply"></i> Ответ администратора
                                        <?php if ($question['answered_by_name']): ?>
                                            (<?php echo htmlspecialchars($question['answered_by_name']); ?>)
                                        <?php endif; ?>
                                    </span>
                                    <span>
                                        <?php echo date('d.m.Y H:i', strtotime($question['answered_at'])); ?>
                                    </span>
                                </div>
                                <p><?php echo nl2br(htmlspecialchars($question['answer'])); ?></p>
                            </div>
                        <?php elseif (!$question['is_answered']): ?>
                            <form method="POST" class="answer-form" id="answerForm<?php echo $question['id']; ?>">
                                <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                <div class="form-group">
                                    <label for="answer<?php echo $question['id']; ?>">Ответить на вопрос:</label>
                                    <textarea id="answer<?php echo $question['id']; ?>" name="answer" required placeholder="Введите ответ на вопрос..."></textarea>
                                </div>
                                <button type="submit" name="answer_question" class="btn btn-primary btn-sm">
                                    <i class="fas fa-paper-plane"></i> Отправить ответ
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <div style="margin-top: 15px; display: flex; gap: 10px;">
                            <button onclick="editQuestion(<?php echo $question['id']; ?>)" class="btn btn-outline btn-sm">
                                <i class="fas fa-edit"></i> Редактировать
                            </button>
                            <a href="?tab=questions&delete_question=<?php echo $question['id']; ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Вы уверены, что хотите удалить этот вопрос?');">
                                <i class="fas fa-trash"></i> Удалить
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-question-circle"></i>
                    <p>Вопросы не найдены</p>
                </div>
            <?php endif; ?>
            
            <!-- Пагинация вопросов -->
            <?php if ($questions_total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($questions_page > 1): ?>
                        <a href="?tab=questions&q_page=<?php echo $questions_page-1; ?><?php echo $questions_search ? '&q_search=' . urlencode($questions_search) : ''; ?><?php echo $questions_product_filter ? '&q_product=' . $questions_product_filter : ''; ?><?php echo $questions_answered !== '' ? '&q_answered=' . $questions_answered : ''; ?>" 
                           class="page-link">
                            <i class="fas fa-chevron-left"></i> Назад
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $questions_total_pages; $i++): ?>
                        <?php if ($i == $questions_page): ?>
                            <span class="page-link active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?tab=questions&q_page=<?php echo $i; ?><?php echo $questions_search ? '&q_search=' . urlencode($questions_search) : ''; ?><?php echo $questions_product_filter ? '&q_product=' . $questions_product_filter : ''; ?><?php echo $questions_answered !== '' ? '&q_answered=' . $questions_answered : ''; ?>" 
                               class="page-link"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($questions_page < $questions_total_pages): ?>
                        <a href="?tab=questions&q_page=<?php echo $questions_page+1; ?><?php echo $questions_search ? '&q_search=' . urlencode($questions_search) : ''; ?><?php echo $questions_product_filter ? '&q_product=' . $questions_product_filter : ''; ?><?php echo $questions_answered !== '' ? '&q_answered=' . $questions_answered : ''; ?>" 
                           class="page-link">
                            Вперед <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- Модальное окно для вопросов по товару -->
        <div class="modal" id="questionsModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Вопросы о товаре</h2>
                    <button class="modal-close" onclick="closeModal()">&times;</button>
                </div>
                <div id="questionsContent">
                    <!-- Контент будет загружен через AJAX -->
                </div>
            </div>
        </div>
    </main>
    
    <script src="../assets/js/products/index.js"></script>
</body>
</html>