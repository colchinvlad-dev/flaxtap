<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог - FlaxTap - Профессиональная косметика</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="../assets/style/catalog.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php 
    ob_start(); // Включаем буферизацию вывода
    // Подключаем конфигурацию базы данных
    include '../config/database.php';
    
    // Функция для безопасного вывода
    function escape($value) {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
    
    // Получаем параметры для пагинации и сортировки
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 8; // Количество товаров на странице
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
        default: // popular
            $order_by = 'ORDER BY rating DESC, review_count DESC';
            break;
    }
    
    // Получаем общее количество товаров для пагинации
    $count_sql = "SELECT COUNT(*) as total FROM products WHERE in_stock = 1";
    $count_result = $connection->query($count_sql);
    $total_products = 0;
    
    if ($count_result && $count_result->num_rows > 0) {
        $row = $count_result->fetch_assoc();
        $total_products = $row['total'];
    }
    
    // Рассчитываем общее количество страниц
    $total_pages = ceil($total_products / $limit);
    
    // Получаем товары для текущей страницы
    $sql = "SELECT id, brand, name, short_description, image_url, current_price, old_price, 
                   rating, review_count, discount_percent, weight, volume, is_bestseller, 
                   is_featured, created_at
            FROM products 
            WHERE in_stock = 1 
            $order_by 
            LIMIT $limit OFFSET $offset";
    
    $result = $connection->query($sql);
    $products = [];
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    // Определяем, какие бренды есть в наличии
    $brands_sql = "SELECT DISTINCT brand FROM products WHERE in_stock = 1 ORDER BY brand";
    $brands_result = $connection->query($brands_sql);
    $brands = [];
    
    if ($brands_result && $brands_result->num_rows > 0) {
        while($row = $brands_result->fetch_assoc()) {
            $brands[] = $row['brand'];
        }
    }
    ?>
    
    <?php include "../inc/header.php"; ?>

    <main class="catalog-page">
        <!-- Баннер каталога -->
        <section class="catalog-banner">
            <div class="container">
                <div class="banner-content">
                    <h1 class="banner-title">Каталог продукции</h1>
                    <p class="banner-subtitle">Профессиональная косметика для мастеров и салонов красоты</p>
                    <div class="breadcrumb">
                        <a href="../index.php">Главная</a>
                        <i class="fas fa-chevron-right"></i>
                        <span>Каталог</span>
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

                    <!-- Категории -->
                    <div class="filter-section">
                        <div class="filter-header">
                            <h4>Категории</h4>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="filter-content">
                            <div class="filter-group">
                                <label class="filter-checkbox">
                                    <input type="checkbox" checked>
                                    <span class="checkmark"></span>
                                    <span class="filter-label">Уходовая косметика</span>
                                    <span class="filter-count">(24)</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkmark"></span>
                                    <span class="filter-label">Наши направления</span>
                                    <span class="filter-count">(12)</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkmark"></span>
                                    <span class="filter-label">Профессиональные наборы</span>
                                    <span class="filter-count">(8)</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkmark"></span>
                                    <span class="filter-label">Все для перманента</span>
                                    <span class="filter-count">(15)</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkmark"></span>
                                    <span class="filter-label">Ресницы и брови</span>
                                    <span class="filter-count">(18)</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkmark"></span>
                                    <span class="filter-label">Расходные материалы</span>
                                    <span class="filter-count">(32)</span>
                                </label>
                            </div>
                        </div>
                    </div>

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
                    <div class="filter-section">
                        <div class="filter-header">
                            <h4>Бренды</h4>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="filter-content">
                            <div class="filter-group">
                                <?php foreach($brands as $brand): ?>
                                <label class="filter-checkbox">
                                    <input type="checkbox" value="<?php echo escape($brand); ?>">
                                    <span class="checkmark"></span>
                                    <span class="filter-label"><?php echo escape($brand); ?></span>
                                    <?php 
                                    $count_sql = "SELECT COUNT(*) as count FROM products WHERE brand = ? AND in_stock = 1";
                                    $stmt = $connection->prepare($count_sql);
                                    $stmt->bind_param("s", $brand);
                                    $stmt->execute();
                                    $count_result = $stmt->get_result();
                                    $count_row = $count_result->fetch_assoc();
                                    $brand_count = $count_row['count'];
                                    ?>
                                    <span class="filter-count">(<?php echo $brand_count; ?>)</span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

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
                                    <option value="30">30 мл</option>
                                    <option value="50">50 мл</option>
                                    <option value="100">100 мл</option>
                                    <option value="150">150 мл</option>
                                    <option value="165">165 мл</option>
                                </select>
                            </div>
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
                            <h2>Все товары</h2>
                            <span class="product-count"><?php echo $total_products; ?> товаров</span>
                        </div>
                        <div class="sort-controls">
                            <label>Сортировка:</label>
                            <select class="sort-select" onchange="location.href='?sort='+this.value+'&page=1'">
                                <option value="popular" <?php echo $sort == 'popular' ? 'selected' : ''; ?>>По популярности</option>
                                <option value="price-asc" <?php echo $sort == 'price-asc' ? 'selected' : ''; ?>>По цене (дешевые сначала)</option>
                                <option value="price-desc" <?php echo $sort == 'price-desc' ? 'selected' : ''; ?>>По цене (дорогие сначала)</option>
                                <option value="new" <?php echo $sort == 'new' ? 'selected' : ''; ?>>По новизне</option>
                                <option value="discount" <?php echo $sort == 'discount' ? 'selected' : ''; ?>>По скидке</option>
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
                                
                                <?php if ($product['discount_percent'] > 0): ?>
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
                                                <a href="../subdirectory/single.php?id=<?php echo (int)$product['id']; ?>"><i class="fas fa-shopping-cart"></i></a>
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
                                <p>Товары не найдены</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Пагинация -->
                    <div class="pagination">
                        <button class="pagination-btn prev" <?php echo $page <= 1 ? 'disabled' : ''; ?> onclick="location.href='?sort=<?php echo $sort; ?>&page=<?php echo $page - 1; ?>'">
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
                                <button class="page-btn <?php echo $i == $page ? 'active' : ''; ?>" onclick="location.href='?sort=<?php echo $sort; ?>&page=<?php echo $i; ?>'">
                                    <?php echo $i; ?>
                                </button>
                            <?php endfor; ?>
                            
                            <?php if ($end_page < $total_pages): ?>
                                <span class="page-dots">...</span>
                                <button class="page-btn" onclick="location.href='?sort=<?php echo $sort; ?>&page=<?php echo $total_pages; ?>'">
                                    <?php echo $total_pages; ?>
                                </button>
                            <?php endif; ?>
                        </div>
                        <button class="pagination-btn next" <?php echo $page >= $total_pages ? 'disabled' : ''; ?> onclick="location.href='?sort=<?php echo $sort; ?>&page=<?php echo $page + 1; ?>'">
                            Вперед <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>

                    <!-- Описание категории -->
                    <div class="category-description">
                        <h3>Профессиональная косметика FlaxTap</h3>
                        <p>FlaxTap предлагает широкий ассортимент профессиональной косметики для салонов красоты и косметологов. 
                           В нашем каталоге представлены уходовая косметика для лица и тела, профессиональные наборы для процедур, 
                           материалы для перманентного макияжа, продукты для ухода за ресницами и бровями, а также расходные материалы.</p>
                        <p>Все продукты сертифицированы и разработаны в собственной лаборатории с использованием натуральных компонентов 
                           и инновационных технологий.</p>
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
    <script>
        // Обработка быстрого просмотра товара
        document.querySelectorAll('.quick-view').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                // Здесь можно добавить AJAX запрос для загрузки данных о товаре
                alert('Быстрый просмотр товара ID: ' + productId);
            });
        });
    </script>
</body>
</html>