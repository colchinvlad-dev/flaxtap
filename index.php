<?php
ob_start(); // Включаем буферизацию вывода
// Подключаем конфигурацию базы данных
include 'config/database.php';

// Функция для безопасного вывода
function escape($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
// Функция для получения товаров по категории
function getProducts($connection, $type) {
    $products = [];
    
    switch($type) {
        case 'hits':
            // Хиты продаж - товары с is_bestseller = 1
            $sql = "SELECT id, name, short_description, image_url, current_price, old_price, 
                           rating, review_count, discount_percent, weight, volume 
                    FROM products 
                    WHERE is_bestseller = 1 AND in_stock = 1 
                    ORDER BY rating DESC 
                    LIMIT 10";
            break;
            
        case 'new':
            // Новинки - товары с is_featured = 1 или новые добавленные
            $sql = "SELECT id, name, short_description, image_url, current_price, old_price, 
                           rating, review_count, discount_percent, weight, volume 
                    FROM products 
                    WHERE (is_featured = 1 OR created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) 
                    AND in_stock = 1 
                    ORDER BY created_at DESC 
                    LIMIT 10";
            break;
            
        case 'sale':
            // Акции - товары со скидкой
            $sql = "SELECT id, name, short_description, image_url, current_price, old_price, 
                           rating, review_count, discount_percent, weight, volume 
                    FROM products 
                    WHERE discount_percent > 0 AND in_stock = 1 
                    ORDER BY discount_percent DESC 
                    LIMIT 10";
            break;
    }
    
    $result = $connection->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    return $products;
}

// Получаем данные для всех секций
$hits_products = getProducts($connection, 'hits');
$new_products = getProducts($connection, 'new');
$sale_products = getProducts($connection, 'sale');
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlaxTap - Профессиональная косметика</title>
    <link rel="stylesheet" href="assets/style/style.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
      <!-- Яндекс Карты API -->
    <script src="https://api-maps.yandex.ru/2.1/?apikey=56bf0a7a-58dd-4188-8e08-8f00e6dd3cc3&lang=ru_RU" type="text/javascript"></script>
</head>
<body>
    <?php include "inc/header.php"; ?>

    <!-- Слайдер баннеров -->
    <section class="hero-slider">
        <div class="slider-container">
            <div class="slider-wrapper">
                <div class="slide active" style="background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/media/carousel.jpg') center/cover no-repeat;">
                    <div class="slide-content">
                        <h1 class="slide-title">Новая коллекция <span class="highlight">FlaxSerum</span></h1>
                        <p class="slide-text">Сыворотки с инновационными формулами для мгновенного результата</p>
                        <a href="#" class="btn btn-primary">Смотреть новинки</a>
                    </div>
                </div>
                <div class="slide" style="background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/media/carousel.jpg') center/cover no-repeat;">
                    <div class="slide-content">
                        <h1 class="slide-title">Скидка <span class="highlight">30%</span> на наборы</h1>
                        <p class="slide-text">Профессиональные комплекты для косметологов со скидкой</p>
                        <a href="#" class="btn btn-primary">Выбрать набор</a>
                    </div>
                </div>
                <div class="slide" style="background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/media/carousel-1.jpg') center/cover no-repeat;">
                    <div class="slide-content">
                        <h1 class="slide-title">Обучение <span class="highlight">онлайн</span></h1>
                        <p class="slide-text">Станьте сертифицированным специалистом FlaxTap</p>
                        <a href="training.html" class="btn btn-primary">Записаться</a>
                    </div>
                </div>
            </div>
            <div class="slider-controls">
                <button class="slider-prev"><i class="fas fa-chevron-left"></i></button>
                <div class="slider-dots">
                    <span class="dot active"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                </div>
                <button class="slider-next"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </section>

    <!-- Улучшенная секция "О компании" -->
    <section id="about" class="about-section improved">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">FlaxTap — инновации в косметологии</h2>
                <p class="section-subtitle">Российский производитель с 2020 года</p>
            </div>
            
            <div class="about-content">
                <div class="about-left">
                    <div class="about-image-container">
                        <img src="assets/media/re.png" alt="О компании FlaxTap" class="about-main-img">
                        <div class="about-badge">
                            <div class="badge-icon">
                                <i class="fas fa-award"></i>
                            </div>
                            <div class="badge-content">
                                <div class="badge-title">Сертифицировано</div>
                                <div class="badge-text">ГОСТ Р ИСО 9001-2015</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="about-right">
                    <div class="about-text-content">
                        <h3 class="about-heading">Наша миссия</h3>
                        <p class="about-description">FlaxTap создает профессиональную косметику, сочетающую передовые научные разработки и натуральные компоненты. Мы делаем эффективный уход доступным каждому.</p>
                        
                        <div class="about-features">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-flask"></i>
                                </div>
                                <div class="feature-content">
                                    <h4>Собственная лаборатория</h4>
                                    <p>Разработка и тестирование всех продуктов</p>
                                </div>
                            </div>
                            
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-leaf"></i>
                                </div>
                                <div class="feature-content">
                                    <h4>Натуральные компоненты</h4>
                                    <p>Без парабенов, SLS и искусственных красителей</p>
                                </div>
                            </div>
                            
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="feature-content">
                                    <h4>Экспертное обучение</h4>
                                    <p>Сертификация косметологов и мастеров</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stats-grid-compact">
                            <div class="stat-item-compact">
                                <div class="stat-number">70+</div>
                                <div class="stat-label">продуктов</div>
                            </div>
                            <div class="stat-item-compact">
                                <div class="stat-number">120+</div>
                                <div class="stat-label">городов</div>
                            </div>
                            <div class="stat-item-compact">
                                <div class="stat-number">5</div>
                                <div class="stat-label">стран</div>
                            </div>
                            <div class="stat-item-compact">
                                <div class="stat-number">50K+</div>
                                <div class="stat-label">клиентов</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

     <!-- Секция направлений (исправленная) -->
    <section class="directions-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Наши направления</h2>
                <p class="section-subtitle">Полный спектр профессиональной косметики</p>
            </div>
            
            <div class="directions-grid-fixed">
                <a href="subdirectory/care-cosmetics/index.html" class="direction-card-fixed">
                    <div class="direction-top">
                        <div class="direction-icon-fixed">
                            <img src="assets/media/directions/ukhodovaya-kosmetika.png" alt="Уходовая косметика">
                        </div>
                        <h3 class="direction-title">Уходовая косметика</h3>
                    </div>
                    <div class="direction-bottom">
                        <p class="direction-desc">Сыворотки, кремы, маски для лица и тела профессионального уровня</p>
                        <span class="direction-link">Подробнее <i class="fas fa-arrow-right"></i></span>
                    </div>
                </a>
                
                <a href="#" class="direction-card-fixed">
                    <div class="direction-top">
                        <div class="direction-icon-fixed">
                            <img src="assets/media/directions/professionalnye-nabory.png" alt="Профессиональные наборы">
                        </div>
                        <h3 class="direction-title">Профессиональные наборы</h3>
                    </div>
                    <div class="direction-bottom">
                        <p class="direction-desc">Готовые комплекты для косметологов и мастеров</p>
                        <span class="direction-link">Подробнее <i class="fas fa-arrow-right"></i></span>
                    </div>
                </a>
                
                <a href="#" class="direction-card-fixed">
                    <div class="direction-top">
                        <div class="direction-icon-fixed">
                            <img src="assets/media/directions/vse-dlya-permanenta.png" alt="Все для перманента">
                        </div>
                        <h3 class="direction-title">Для перманента</h3>
                    </div>
                    <div class="direction-bottom">
                        <p class="direction-desc">Пигменты, аппараты и материалы</p>
                        <span class="direction-link">Подробнее <i class="fas fa-arrow-right"></i></span>
                    </div>
                </a>
                
                <a href="#" class="direction-card-fixed">
                    <div class="direction-top">
                        <div class="direction-icon-fixed">
                            <img src="assets/media/directions/resnitsy-i-brovi.png" alt="Ресницы и брови">
                        </div>
                        <h3 class="direction-title">Ресницы и брови</h3>
                    </div>
                    <div class="direction-bottom">
                        <p class="direction-desc">Для наращивания, ламинирования и ухода</p>
                        <span class="direction-link">Подробнее <i class="fas fa-arrow-right"></i></span>
                    </div>
                </a>
                
                <a href="#" class="direction-card-fixed">
                    <div class="direction-top">
                        <div class="direction-icon-fixed">
                            <img src="assets/media/directions/raskhodnki.png" alt="Расходные материалы">
                        </div>
                        <h3 class="direction-title">Расходные материалы</h3>
                    </div>
                    <div class="direction-bottom">
                        <p class="direction-desc">Все необходимое для работы косметологического кабинета</p>
                        <span class="direction-link">Подробнее <i class="fas fa-arrow-right"></i></span>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Секция хитов продаж (с исправленными карточками) -->
    <section class="products-section" id="hits">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Хиты продаж</h2>
                <div class="section-controls">
                    <button class="section-prev"><i class="fas fa-chevron-left"></i></button>
                    <button class="section-next"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
            
            <div class="products-slider">
                <div class="products-track">
                    <?php if (!empty($hits_products)): ?>
                        <?php foreach ($hits_products as $product): ?>
                        <!-- Товар -->
                        <div class="product-card-fixed">
                            <div class="product-badge hit">Хит</div>
                            <div class="product-image-fixed">
                                <div class="image-container">
                                     <a href="subdirectory/single.php?id=<?php echo (int)$product['id']; ?>" class="product-image-link">
                                    <img src="<?php echo escape($product['image_url']); ?>" alt="<?php echo escape($product['name']); ?>">
                                    </a>
                                </div>
                            </div>
                            <div class="product-content-fixed">
                                <div class="product-top">
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
                                        <span class="rating-value"><?php echo number_format($rating, 1); ?></span>
                                    </div>
                                </div>
                                <div class="product-bottom">
                                    <div class="product-price">
                                        <span class="current-price"><?php echo number_format($product['current_price'], 0, '.', ' '); ?> ₽</span>
                                        <?php if (!empty($product['old_price']) && $product['old_price'] > $product['current_price']): ?>
                                            <span class="old-price"><?php echo number_format($product['old_price'], 0, '.', ' '); ?> ₽</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-actions">
                                        <a href="subdirectory/single.php?id=<?php echo (int)$product['id']; ?>" class="btn-cart">
                                            <i class="fas fa-eye"></i> Подробнее
                                        </a>
                                        <button class="btn-favorite"><i class="far fa-heart"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-products">
                            <p>Товары отсутствуют</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="slider-indicators">
                <span class="indicator active"></span>
                <span class="indicator"></span>
                <span class="indicator"></span>
            </div>
        </div>
    </section>

    <!-- Секция новинок (такой же слайдер) -->
    <section class="products-section new">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Новинки</h2>
                <div class="section-controls">
                    <button class="section-prev"><i class="fas fa-chevron-left"></i></button>
                    <button class="section-next"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
            
            <div class="products-slider">
                <div class="products-track">
                    <?php if (!empty($new_products)): ?>
                        <?php foreach ($new_products as $product): ?>
                        <!-- Товар -->
                        <div class="product-card-fixed">
                            <div class="product-badge new">Новинка</div>
                            <div class="product-image-fixed">
                                <div class="image-container">
                                     <a href="subdirectory/single.php?id=<?php echo (int)$product['id']; ?>" class="product-image-link">
                                    <img src="<?php echo escape($product['image_url']); ?>" alt="<?php echo escape($product['name']); ?>">
                                    </a>
                                </div>
                            </div>
                            <div class="product-content-fixed">
                                <div class="product-top">
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
                                        <span class="rating-value"><?php echo number_format($rating, 1); ?></span>
                                    </div>
                                </div>
                                <div class="product-bottom">
                                    <div class="product-price">
                                        <span class="current-price"><?php echo number_format($product['current_price'], 0, '.', ' '); ?> ₽</span>
                                        <?php if (!empty($product['old_price']) && $product['old_price'] > $product['current_price']): ?>
                                            <span class="old-price"><?php echo number_format($product['old_price'], 0, '.', ' '); ?> ₽</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-actions">
                                        <a href="subdirectory/single.php?id=<?php echo (int)$product['id']; ?>" class="btn-cart">
                                            <i class="fas fa-eye"></i> Подробнее
                                        </a>
                                        <button class="btn-favorite"><i class="far fa-heart"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-products">
                            <p>Товары отсутствуют</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="slider-indicators">
                <span class="indicator active"></span>
                <span class="indicator"></span>
                <span class="indicator"></span>
            </div>
        </div>
    </section>

    <!-- Секция акций (такой же слайдер) -->
    <section class="products-section sale">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Акции</h2>
                <div class="section-controls">
                    <button class="section-prev"><i class="fas fa-chevron-left"></i></button>
                    <button class="section-next"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
            
            <div class="products-slider">
                <div class="products-track">
                    <?php if (!empty($sale_products)): ?>
                        <?php foreach ($sale_products as $product): ?>
                        <!-- Товар -->
                        <div class="product-card-fixed">
                            <?php if (!empty($product['discount_percent']) && $product['discount_percent'] > 0): ?>
                                <div class="product-badge sale">-<?php echo (int)$product['discount_percent']; ?>%</div>
                            <?php else: ?>
                                <div class="product-badge sale">Акция</div>
                            <?php endif; ?>
                            <div class="product-image-fixed">
                                <div class="image-container">
                                     <a href="subdirectory/single.php?id=<?php echo (int)$product['id']; ?>" class="product-image-link">
                                    <img src="<?php echo escape($product['image_url']); ?>" alt="<?php echo escape($product['name']); ?>">
                                    </a>
                                </div>
                            </div>
                            <div class="product-content-fixed">
                                <div class="product-top">
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
                                        <span class="rating-value"><?php echo number_format($rating, 1); ?></span>
                                    </div>
                                </div>
                                <div class="product-bottom">
                                    <div class="product-price">
                                        <span class="current-price"><?php echo number_format($product['current_price'], 0, '.', ' '); ?> ₽</span>
                                        <?php if (!empty($product['old_price']) && $product['old_price'] > $product['current_price']): ?>
                                            <span class="old-price"><?php echo number_format($product['old_price'], 0, '.', ' '); ?> ₽</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-actions">
                                        <a href="subdirectory/single.php?id=<?php echo (int)$product['id']; ?>" class="btn-cart">
                                            <i class="fas fa-eye"></i> Подробнее
                                        </a>
                                        <button class="btn-favorite"><i class="far fa-heart"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-products">
                            <p>Товары отсутствуют</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="slider-indicators">
                <span class="indicator active"></span>
                <span class="indicator"></span>
                <span class="indicator"></span>
            </div>
        </div>
    </section>

    <!-- Улучшенный аккордеон -->
    <section class="faq-section improved">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Частые вопросы</h2>
                <p class="section-subtitle">Ответы на популярные вопросы о продукции и сотрудничестве</p>
            </div>
            
            <div class="faq-container">
                <div class="faq-item active">
                    <div class="faq-header">
                        <div class="faq-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3 class="faq-question">Почему выбирают продукцию FlaxTap?</h3>
                        <div class="faq-toggle">
                            <i class="fas fa-minus"></i>
                        </div>
                    </div>
                    <div class="faq-body">
                        <p>FlaxTap предлагает сертифицированную продукцию, разработанную в собственной лаборатории. Все средства проходят клинические испытания и соответствуют международным стандартам качества. Мы используем только проверенные ингредиенты и инновационные формулы.</p>
                        <ul class="faq-list">
                            <li><i class="fas fa-check"></i> Собственное производство и контроль качества</li>
                            <li><i class="fas fa-check"></i> Натуральные компоненты без вредных добавок</li>
                            <li><i class="fas fa-check"></i> Доказанная эффективность клиническими испытаниями</li>
                            <li><i class="fas fa-check"></i> Поддержка и обучение специалистов</li>
                        </ul>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-header">
                        <div class="faq-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h3 class="faq-question">Как начать сотрудничество с FlaxTap?</h3>
                        <div class="faq-toggle">
                            <i class="fas fa-plus"></i>
                        </div>
                    </div>
                    <div class="faq-body">
                        <p>Сотрудничество с FlaxTap открывает возможности для косметологов, салонов красоты и дистрибьюторов. Мы предлагаем гибкие условия и поддержку на всех этапах.</p>
                        <div class="steps">
                            <div class="step">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <h4>Консультация</h4>
                                    <p>Обсуждение условий и выбор формата сотрудничества</p>
                                </div>
                            </div>
                            <div class="step">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <h4>Обучение</h4>
                                    <p>Бесплатное обучение продуктам и методикам работы</p>
                                </div>
                            </div>
                            <div class="step">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <h4>Запуск</h4>
                                    <p>Получение продукции и начало работы</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-header">
                        <div class="faq-icon">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <h3 class="faq-question">Как осуществляется доставка и оплата?</h3>
                        <div class="faq-toggle">
                            <i class="fas fa-plus"></i>
                        </div>
                    </div>
                    <div class="faq-body">
                        <p>Мы предлагаем несколько удобных способов доставки и оплаты для вашего комфорта.</p>
                        <div class="delivery-methods">
                            <div class="method">
                                <i class="fas fa-truck"></i>
                                <h4>Курьерская доставка</h4>
                                <p>1-3 дня по Москве и Санкт-Петербургу</p>
                            </div>
                            <div class="method">
                                <i class="fas fa-box"></i>
                                <h4>Почта России</h4>
                                <p>5-14 дней по всей территории РФ</p>
                            </div>
                            <div class="method">
                                <i class="fas fa-store"></i>
                                <h4>Самовывоз</h4>
                                <p>Из наших пунктов выдачи в 120 городах</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-header">
                        <div class="faq-icon">
                            <i class="fas fa-undo"></i>
                        </div>
                        <h3 class="faq-question">Как осуществляется возврат товара?</h3>
                        <div class="faq-toggle">
                            <i class="fas fa-plus"></i>
                        </div>
                    </div>
                    <div class="faq-body">
                        <p>Мы гарантируем возврат средств в течение 14 дней при соблюдении условий:</p>
                        <ol class="return-steps">
                            <li>Товар не был в употреблении и сохранил товарный вид</li>
                            <li>Сохранилась оригинальная упаковка и документы</li>
                            <li>Возврат осуществляется тем же способом, что и оплата</li>
                        </ol>
                        <p class="note"><i class="fas fa-info-circle"></i> Для инициализации возврата свяжитесь с нашей службой поддержки.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php 
    include "inc/modal.php";
    include "inc/footer.php";
    ?>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/slider.js"></script>
    <script src="assets/js/modals.js"></script>
</body>
</html>