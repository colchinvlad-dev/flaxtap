<?php
ob_start(); // Включаем буферизацию вывода
// ПЕРВЫМ ДЕЛОМ подключаем конфигурацию базы данных
include '../config/database.php';

// Функция для безопасного вывода
function escape($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Получаем ID категории из GET-параметра
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Получаем информацию о категории
$category = null;
if ($category_id > 0) {
    $category_sql = "SELECT id, name, description, image_url FROM categories WHERE id = ?";
    $stmt = $connection->prepare($category_sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $category_result = $stmt->get_result();
    
    if ($category_result->num_rows > 0) {
        $category = $category_result->fetch_assoc();
    }
}

// Если категория не найдена, показываем уходовую косметику по умолчанию (ID=1)
if (!$category) {
    $category_id = 1;
    $category_sql = "SELECT id, name, description, image_url FROM categories WHERE id = 1";
    $category_result = $connection->query($category_sql);
    if ($category_result->num_rows > 0) {
        $category = $category_result->fetch_assoc();
    }
}

// Определяем заголовок страницы ДО начала вывода HTML
$page_title = '';
if (isset($category) && !empty($category['name'])) {
    $page_title = htmlspecialchars($category['name']) . ' ';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="../assets/style/catalog.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php 
    // Получаем подкатегории (если есть)
    $subcategories_sql = "SELECT id, name, slug FROM categories WHERE parent_id = ? AND is_active = 1 ORDER BY sort_order";
    $stmt = $connection->prepare($subcategories_sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $subcategories_result = $stmt->get_result();
    $subcategories = [];
    
    if ($subcategories_result->num_rows > 0) {
        while($row = $subcategories_result->fetch_assoc()) {
            $subcategories[] = $row;
        }
    }
    
    // Получаем параметры для пагинации и сортировки
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 12; // Количество товаров на странице
    $offset = ($page - 1) * $limit;
    
    // Параметры сортировки
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'popular';
    $order_by = '';
    
    switch($sort) {
        case 'price-asc':
            $order_by = 'ORDER BY current_price ASC';
            break;
        case 'price-desc':
            $order_by = 'ORDER BY current_price DESC';
            break;
        case 'new':
            $order_by = 'ORDER BY created_at DESC';
            break;
        case 'discount':
            $order_by = 'ORDER BY discount_percent DESC';
            break;
        case 'name':
            $order_by = 'ORDER BY name ASC';
            break;
        default: // popular
            $order_by = 'ORDER BY rating DESC, review_count DESC';
            break;
    }
    
    // Получаем общее количество товаров в категории для пагинации
    $count_sql = "SELECT COUNT(*) as total FROM products WHERE category_id = ? AND in_stock = 1";
    $stmt = $connection->prepare($count_sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $count_result = $stmt->get_result();
    $total_products = 0;
    
    if ($count_result->num_rows > 0) {
        $row = $count_result->fetch_assoc();
        $total_products = $row['total'];
    }
    
    // Рассчитываем общее количество страниц
    $total_pages = ceil($total_products / $limit);
    
    // Получаем товары для текущей категории и страницы
    $sql = "SELECT id, brand, name, short_description, image_url, current_price, old_price, 
                   rating, review_count, discount_percent, weight, volume, is_bestseller, 
                   is_featured, created_at, skin_type
            FROM products 
            WHERE category_id = ? AND in_stock = 1 
            $order_by 
            LIMIT ? OFFSET ?";
    
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("iii", $category_id, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    // Получаем уникальные бренды в этой категории
    $brands_sql = "SELECT DISTINCT brand FROM products WHERE category_id = ? AND in_stock = 1 ORDER BY brand";
    $stmt = $connection->prepare($brands_sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $brands_result = $stmt->get_result();
    $brands = [];
    
    if ($brands_result->num_rows > 0) {
        while($row = $brands_result->fetch_assoc()) {
            $brands[] = $row['brand'];
        }
    }
    
    // Получаем уникальные типы кожи в этой категории
    $skin_types_sql = "SELECT DISTINCT skin_type FROM products WHERE category_id = ? AND skin_type IS NOT NULL AND skin_type != '' AND in_stock = 1 ORDER BY skin_type";
    $stmt = $connection->prepare($skin_types_sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $skin_types_result = $stmt->get_result();
    $skin_types = [];
    
    if ($skin_types_result->num_rows > 0) {
        while($row = $skin_types_result->fetch_assoc()) {
            $skin_types[] = $row['skin_type'];
        }
    }
    
    include "../inc/header.php"; 
    ?>

    <main class="catalog-page">
        <!-- Баннер категории -->
        <section class="catalog-banner">
            <div class="container">
                <div class="banner-content">
                    <h1 class="banner-title"><?php echo $page_title; ?></h1>
                    <p class="banner-subtitle">Профессиональные средства для ухода за кожей, волосами и телом</p>
                    <div class="breadcrumb">
                        <a href="../index.php">Главная</a>
                        <i class="fas fa-chevron-right"></i>
                        <a href="/subdirectory/">Каталог</a>
                        <i class="fas fa-chevron-right"></i>
                        <span><?php echo $page_title; ?></span>
                    </div>
                </div>
            </div>
        </section>

        <div class="container">
            <div class="catalog-layout">
                <!-- Боковая панель с фильтрами -->
                <aside class="catalog-sidebar">
                    <div class="sidebar-header">
                        <h3><i class="fas fa-filter"></i> Фильтры</h3>
                        <button class="clear-filters">Сбросить все</button>
                    </div>

                    <!-- Подкатегории -->
                    <?php if (!empty($subcategories)): ?>
                    <div class="filter-section">
                        <div class="filter-header">
                            <h4>Типы средств</h4>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="filter-content">
                            <div class="filter-group">
                                <?php foreach($subcategories as $subcategory): 
                                    // Подсчет товаров в подкатегории
                                    $subcount_sql = "SELECT COUNT(*) as count FROM products WHERE category_id = ? AND in_stock = 1";
                                    $stmt = $connection->prepare($subcount_sql);
                                    $stmt->bind_param("i", $subcategory['id']);
                                    $stmt->execute();
                                    $subcount_result = $stmt->get_result();
                                    $subcount_row = $subcount_result->fetch_assoc();
                                    $subcount = $subcount_row['count'];
                                ?>
                                <label class="filter-checkbox">
                                    <input type="checkbox" value="<?php echo (int)$subcategory['id']; ?>">
                                    <span class="checkmark"></span>
                                    <span class="filter-label"><?php echo escape($subcategory['name']); ?></span>
                                    <span class="filter-count">(<?php echo $subcount; ?>)</span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Статические категории, если нет подкатегорий в БД -->
                    <div class="filter-section">
                        <div class="filter-header">
                            <h4>Типы средств</h4>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="filter-content">
                            <div class="filter-group">
                                <label class="filter-checkbox">
                                    <input type="checkbox" checked>
                                    <span class="checkmark"></span>
                                    <span class="filter-label">Для лица</span>
                                    <span class="filter-count">(32)</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkmark"></span>
                                    <span class="filter-label">Для тела</span>
                                    <span class="filter-count">(18)</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkmark"></span>
                                    <span class="filter-label">Для волос</span>
                                    <span class="filter-count">(15)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Цена -->
                    <div class="filter-section">
                        <div class="filter-header">
                            <h4>Цена, ₽</h4>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="filter-content">
                            <div class="price-range">
                                <div class="price-inputs">
                                    <div class="price-field">
                                        <label>от</label>
                                        <input type="number" value="0" min="0">
                                    </div>
                                    <div class="price-field">
                                        <label>до</label>
                                        <input type="number" value="10000" min="0">
                                    </div>
                                </div>
                                <div class="price-slider">
                                    <div class="slider-track"></div>
                                    <input type="range" min="0" max="20000" value="0" class="slider-min">
                                    <input type="range" min="0" max="20000" value="10000" class="slider-max">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Бренды -->
                    <?php if (!empty($brands)): ?>
                    <div class="filter-section">
                        <div class="filter-header">
                            <h4>Бренды</h4>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="filter-content">
                            <div class="filter-group">
                                <?php foreach($brands as $brand): 
                                    // Подсчет товаров по бренду в категории
                                    $brandcount_sql = "SELECT COUNT(*) as count FROM products WHERE category_id = ? AND brand = ? AND in_stock = 1";
                                    $stmt = $connection->prepare($brandcount_sql);
                                    $stmt->bind_param("is", $category_id, $brand);
                                    $stmt->execute();
                                    $brandcount_result = $stmt->get_result();
                                    $brandcount_row = $brandcount_result->fetch_assoc();
                                    $brandcount = $brandcount_row['count'];
                                ?>
                                <label class="filter-checkbox">
                                    <input type="checkbox" value="<?php echo escape($brand); ?>">
                                    <span class="checkmark"></span>
                                    <span class="filter-label"><?php echo escape($brand); ?></span>
                                    <span class="filter-count">(<?php echo $brandcount; ?>)</span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Статические бренды, если нет в БД -->
                    <div class="filter-section">
                        <div class="filter-header">
                            <h4>Бренды</h4>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="filter-content">
                            <div class="filter-group">
                                <label class="filter-checkbox">
                                    <input type="checkbox" checked>
                                    <span class="checkmark"></span>
                                    <span class="filter-label">FlaxSerum</span>
                                    <span class="filter-count">(16)</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkmark"></span>
                                    <span class="filter-label">FlaxCream</span>
                                    <span class="filter-count">(12)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Характеристики -->
                    <div class="filter-section">
                        <div class="filter-header">
                            <h4>Характеристики</h4>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="filter-content">
                            <div class="filter-select">
                                <label>Объем</label>
                                <select>
                                    <option value="">Все</option>
                                    <option value="3">3 мл</option>
                                    <option value="10">10 мл</option>
                                    <option value="30">30 мл</option>
                                    <option value="50">50 мл</option>
                                    <option value="100">100 мл</option>
                                    <option value="150">150 мл</option>
                                    <option value="200">200 мл</option>
                                    <option value="250">250 гр</option>
                                </select>
                            </div>
                            <?php if (!empty($skin_types)): ?>
                            <div class="filter-select">
                                <label>Тип кожи</label>
                                <select>
                                    <option value="">Все</option>
                                    <?php foreach($skin_types as $type): ?>
                                    <option value="<?php echo escape($type); ?>"><?php echo escape($type); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php else: ?>
                            <div class="filter-select">
                                <label>Тип кожи</label>
                                <select>
                                    <option value="">Все</option>
                                    <option value="normal">Нормальная</option>
                                    <option value="dry">Сухая</option>
                                    <option value="oily">Жирная</option>
                                    <option value="combination">Комбинированная</option>
                                </select>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <button class="btn btn-primary apply-filters">
                        <i class="fas fa-check"></i> Применить фильтры
                    </button>
                </aside>

                <!-- Основной контент -->
                <section class="catalog-main">
                    <!-- Заголовок и сортировка -->
                    <div class="catalog-header">
                        <div class="catalog-info">
                            <h2><?php echo $page_title; ?></h2>
                            <span class="product-count"><?php echo $total_products; ?> товаров</span>
                        </div>
                        <div class="sort-controls">
                            <label>Сортировка:</label>
                            <select class="sort-select" onchange="location.href='?id=<?php echo $category_id; ?>&sort='+this.value+'&page=1'">
                                <option value="popular" <?php echo $sort == 'popular' ? 'selected' : ''; ?>>По популярности</option>
                                <option value="price-asc" <?php echo $sort == 'price-asc' ? 'selected' : ''; ?>>По цене (дешевые сначала)</option>
                                <option value="price-desc" <?php echo $sort == 'price-desc' ? 'selected' : ''; ?>>По цене (дорогие сначала)</option>
                                <option value="new" <?php echo $sort == 'new' ? 'selected' : ''; ?>>По новизне</option>
                                <option value="discount" <?php echo $sort == 'discount' ? 'selected' : ''; ?>>По скидке</option>
                                <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>По названию (А-Я)</option>
                            </select>
                            <div class="view-toggle">
                                <button class="view-btn active" data-view="grid">
                                    <i class="fas fa-th-large"></i>
                                </button>
                                <button class="view-btn" data-view="list">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Быстрые подкатегории -->
                    <div class="subcategories-quick">
                        <a href="?id=<?php echo $category_id; ?>" class="subcategory-btn active">Все товары</a>
                        <?php if (!empty($subcategories)): ?>
                            <?php foreach($subcategories as $subcategory): ?>
                            <a href="?id=<?php echo $category_id; ?>&subcategory=<?php echo (int)$subcategory['id']; ?>" class="subcategory-btn">
                                <?php echo escape($subcategory['name']); ?>
                            </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <a href="#" class="subcategory-btn">Для тела</a>
                            <a href="#" class="subcategory-btn">Для лица</a>
                            <a href="#" class="subcategory-btn">Для волос</a>
                            <a href="#" class="subcategory-btn">Сыворотки</a>
                            <a href="#" class="subcategory-btn">Кремы</a>
                            <a href="#" class="subcategory-btn">Маски</a>
                            <a href="#" class="subcategory-btn">Пробники</a>
                        <?php endif; ?>
                    </div>

                    <!-- Сетка товаров -->
                    <div class="products-grid" id="productsGrid">
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                            <!-- Карточка товара -->
                            <div class="product-card catalog">
                                <?php if ($product['is_bestseller']): ?>
                                    <div class="product-badge hit">Хит</div>
                                <?php endif; ?>
                                
                                <?php if ($product['is_featured']): ?>
                                    <div class="product-badge new">Новинка</div>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['discount_percent']) && $product['discount_percent'] > 0): ?>
                                    <div class="product-badge discount">-<?php echo (int)$product['discount_percent']; ?>%</div>
                                <?php endif; ?>
                                
                                <div class="product-image">
                                    <img src="<?php echo escape($product['image_url']); ?>" alt="<?php echo escape($product['name']); ?>">
                                    <button class="quick-view" data-id="<?php echo (int)$product['id']; ?>">
                                        <i class="fas fa-eye"></i> Быстрый просмотр
                                    </button>
                                </div>
                                <div class="product-content">
                                    <div class="product-category"><?php echo escape($product['brand']); ?></div>
                                    <h3 class="product-title"><?php echo escape($product['name']); ?></h3>
                                    <p class="product-description">
                                        <?php 
                                        $description = escape($product['short_description']);
                                        if (strlen($description) > 100) {
                                            echo substr($description, 0, 100) . '...';
                                        } else {
                                            echo $description;
                                        }
                                        ?>
                                    </p>
                                    <div class="product-rating">
                                        <div class="stars">
                                            <?php
                                            $rating = (float)$product['rating'];
                                            $fullStars = floor($rating);
                                            $hasHalfStar = ($rating - $fullStars) >= 0.5;
                                            $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
                                            
                                            for ($i = 0; $i < $fullStars; $i++): ?>
                                                <i class="fas fa-star"></i>
                                            <?php endfor; ?>
                                            
                                            <?php if ($hasHalfStar): ?>
                                                <i class="fas fa-star-half-alt"></i>
                                            <?php endif; ?>
                                            
                                            <?php for ($i = 0; $i < $emptyStars; $i++): ?>
                                                <i class="far fa-star"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="rating-value"><?php echo number_format($rating, 1); ?> (<?php echo (int)$product['review_count']; ?>)</span>
                                    </div>
                                    <div class="product-footer">
                                        <div class="product-price">
                                            <span class="current-price"><?php echo number_format($product['current_price'], 0, '.', ' '); ?> ₽</span>
                                            <?php if (!empty($product['old_price']) && $product['old_price'] > $product['current_price']): ?>
                                                <span class="old-price"><?php echo number_format($product['old_price'], 0, '.', ' '); ?> ₽</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-actions">
                                            <button class="btn-cart" data-id="<?php echo (int)$product['id']; ?>">
                                                <a href="single.php?id=<?php echo (int)$product['id']; ?>"><i class="fas fa-shopping-cart"></i></a>
                                            </button>
                                            <button class="btn-favorite" data-id="<?php echo (int)$product['id']; ?>">
                                                <i class="far fa-heart"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-products-message">
                                <p>Товары в этой категории не найдены</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Пагинация -->
                    <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <button class="pagination-btn prev" <?php echo $page <= 1 ? 'disabled' : ''; ?> onclick="location.href='?id=<?php echo $category_id; ?>&sort=<?php echo $sort; ?>&page=<?php echo $page - 1; ?>'">
                            <i class="fas fa-chevron-left"></i> Назад
                        </button>
                        <div class="pagination-pages">
                            <?php 
                            // Выводим номера страниц
                            $max_visible_pages = 5;
                            $start_page = max(1, $page - floor($max_visible_pages / 2));
                            $end_page = min($total_pages, $start_page + $max_visible_pages - 1);
                            
                            // Корректируем начало, если конец близко к общему количеству
                            $start_page = max(1, $end_page - $max_visible_pages + 1);
                            
                            for ($i = $start_page; $i <= $end_page; $i++): 
                            ?>
                                <button class="page-btn <?php echo $i == $page ? 'active' : ''; ?>" onclick="location.href='?id=<?php echo $category_id; ?>&sort=<?php echo $sort; ?>&page=<?php echo $i; ?>'">
                                    <?php echo $i; ?>
                                </button>
                            <?php endfor; ?>
                            
                            <?php if ($end_page < $total_pages): ?>
                                <span class="page-dots">...</span>
                                <button class="page-btn" onclick="location.href='?id=<?php echo $category_id; ?>&sort=<?php echo $sort; ?>&page=<?php echo $total_pages; ?>'">
                                    <?php echo $total_pages; ?>
                                </button>
                            <?php endif; ?>
                        </div>
                        <button class="pagination-btn next" <?php echo $page >= $total_pages ? 'disabled' : ''; ?> onclick="location.href='?id=<?php echo $category_id; ?>&sort=<?php echo $sort; ?>&page=<?php echo $page + 1; ?>'">
                            Вперед <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    <?php else: ?>
                    <!-- Статическая пагинация, если всего 1 страница -->
                    <div class="pagination">
                        <button class="pagination-btn prev" disabled>
                            <i class="fas fa-chevron-left"></i> Назад
                        </button>
                        <div class="pagination-pages">
                            <button class="page-btn active">1</button>
                        </div>
                        <button class="pagination-btn next" disabled>
                            Вперед <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    <?php endif; ?>

                    <!-- Описание категории -->
                    <div class="category-description">
                        <h3><?php echo $page_title; ?> FlaxTap</h3>
                        <?php if (!empty($category['description'])): ?>
                            <?php echo nl2br(escape($category['description'])); ?>
                        <?php else: ?>
                            <p>FlaxTap предлагает профессиональные средства для ежедневного ухода. 
                               В нашем ассортименте представлены лучшие продукты, разработанные в собственной лаборатории с использованием натуральных компонентов, 
                               растительных экстрактов и инновационных технологий.</p>
                            <p>Все продукты сертифицированы и прошли клинические испытания. Мы не используем парабены, сульфаты, 
                               минеральные масла и другие вредные компоненты.</p>
                        <?php endif; ?>
                        
                        <div class="category-features">
                            <h4>Преимущества нашей косметики:</h4>
                            <ul>
                                <li><i class="fas fa-check"></i> Натуральные компоненты и растительные экстракты</li>
                                <li><i class="fas fa-check"></i> Без парабенов, сульфатов и минеральных масел</li>
                                <li><i class="fas fa-check"></i> Подходит для профессионального и домашнего использования</li>
                                <li><i class="fas fa-check"></i> Клинически протестировано</li>
                                <li><i class="fas fa-check"></i> Сертифицированная продукция</li>
                                <li><i class="fas fa-check"></i> Действующие концентрации активных веществ</li>
                                <li><i class="fas fa-check"></i> Подходит для всех типов кожи</li>
                                <li><i class="fas fa-check"></i> Vegan friendly (не тестируется на животных)</li>
                            </ul>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <?php 
        include "../inc/modal.php";
        include "../inc/footer.php"; 
    ?>

    <!-- Модальное окно быстрого просмотра -->
    <div class="modal quick-view-modal">
        <div class="modal-content wide">
            <div class="modal-header">
                <h2><i class="fas fa-eye"></i> Быстрый просмотр</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="quick-view-content">
                    <!-- Контент будет заполняться через JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/catalog.js"></script>
    <script src="../assets/js/category.js"></script>
</body>
</html>