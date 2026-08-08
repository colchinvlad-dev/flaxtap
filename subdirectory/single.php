<?php
ob_start(); // Включаем буферизацию вывода
// ПЕРВЫМ ДЕЛОМ подключаем конфигурацию базы данных
include '../config/database.php';

// Начинаем сессию
session_start();

// Функция для безопасного вывода
function escape($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Получаем ID товара из GET-параметра
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;



// ============================================
// ПОЛУЧАЕМ ИНФОРМАЦИЮ О ТОВАРЕ (ЛОГИКА ВЫВОДА ДАННЫХ)
// ============================================

$product = null;
$product_images = [];
$product_attributes = [];
$product_questions = [];
$recommended_products = [];
$category_name = '';

if ($product_id > 0) {
    try {
        // Основная информация о товаре
        $product_sql = "SELECT p.*, c.name as category_name 
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        WHERE p.id = ?";
        $stmt = $connection->prepare($product_sql);
        
        if ($stmt) {
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $product_result = $stmt->get_result();
            
            if ($product_result->num_rows > 0) {
                $product = $product_result->fetch_assoc();
                
                // Получаем изображения товара
                $images_sql = "SELECT image_url, alt_text FROM product_images WHERE product_id = ? ORDER BY sort_order, id";
                $stmt2 = $connection->prepare($images_sql);
                if ($stmt2) {
                    $stmt2->bind_param("i", $product_id);
                    $stmt2->execute();
                    $images_result = $stmt2->get_result();
                    
                    if ($images_result->num_rows > 0) {
                        while($row = $images_result->fetch_assoc()) {
                            $product_images[] = $row;
                        }
                    }
                    $stmt2->close();
                }
                
                // Получаем атрибуты товара
                $attrs_sql = "SELECT attribute_name, attribute_value FROM product_attributes WHERE product_id = ? ORDER BY sort_order";
                $stmt3 = $connection->prepare($attrs_sql);
                if ($stmt3) {
                    $stmt3->bind_param("i", $product_id);
                    $stmt3->execute();
                    $attrs_result = $stmt3->get_result();
                    
                    if ($attrs_result->num_rows > 0) {
                        while($row = $attrs_result->fetch_assoc()) {
                            $product_attributes[] = $row;
                        }
                    }
                    $stmt3->close();
                }
                
                // Получаем вопросы и ответы о товаре
                $questions_sql = "SELECT user_name, question, answer, DATE_FORMAT(created_at, '%d.%m.%Y %H:%i') as created_date 
                                 FROM product_questions 
                                 WHERE product_id = ? 
                                 ORDER BY created_at DESC";
                $stmt4 = $connection->prepare($questions_sql);
                if ($stmt4) {
                    $stmt4->bind_param("i", $product_id);
                    $stmt4->execute();
                    $questions_result = $stmt4->get_result();
                    
                    if ($questions_result->num_rows > 0) {
                        while($row = $questions_result->fetch_assoc()) {
                            $product_questions[] = $row;
                        }
                    }
                    $stmt4->close();
                }
                
                // Получаем категорию для хлебных крошек
                $category_id = $product['category_id'];
                if ($category_id > 0) {
                    $cat_sql = "SELECT name FROM categories WHERE id = ?";
                    $stmt_cat = $connection->prepare($cat_sql);
                    if ($stmt_cat) {
                        $stmt_cat->bind_param("i", $category_id);
                        $stmt_cat->execute();
                        $cat_result = $stmt_cat->get_result();
                        if ($cat_result->num_rows > 0) {
                            $cat_row = $cat_result->fetch_assoc();
                            $category_name = $cat_row['name'];
                        }
                        $stmt_cat->close();
                    }
                }
                
                // Получаем рекомендуемые товары из той же категории
                $recommended_sql = "SELECT id, brand, name, short_description, image_url, 
                                           current_price, old_price, discount_percent, rating, review_count,
                                           weight, volume
                                    FROM products 
                                    WHERE category_id = ? AND id != ? AND in_stock = 1 
                                    ORDER BY rating DESC, review_count DESC 
                                    LIMIT 4";
                $stmt_rec = $connection->prepare($recommended_sql);
                if ($stmt_rec) {
                    $stmt_rec->bind_param("ii", $product['category_id'], $product_id);
                    $stmt_rec->execute();
                    $recommended_result = $stmt_rec->get_result();
                    
                    if ($recommended_result->num_rows > 0) {
                        while($row = $recommended_result->fetch_assoc()) {
                            $recommended_products[] = $row;
                        }
                    }
                    $stmt_rec->close();
                }
            }
            $stmt->close();
        }
        
    } catch (Exception $e) {
        $product = null;
    }
}

// Если товар не найден, показываем заглушку
if (!$product) {
    $product_id = 0;
}

// Определяем заголовок страницы
$page_title = $product ? escape($product['name']) . ' - Каталог - FlaxTap' : 'Товар не найден - FlaxTap';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="../assets/style/catalog.css">
    <link rel="stylesheet" href="../assets/style/product.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include "../inc/header.php"; ?>

    <main class="single-product-page">
        <!-- Баннер с хлебными крошками -->
        <section class="catalog-banner single-banner">
            <div class="container">
                <div class="banner-content">
                    <h1 class="banner-title">
                        <?php echo $product ? escape($product['name']) : 'Товар не найден'; ?>
                    </h1>
                    <div class="breadcrumb">
                        <a href="../index.php">Главная</a>
                        <i class="fas fa-chevron-right"></i>
                        <a href="index.php">Каталог</a>
                        <i class="fas fa-chevron-right"></i>
                        <?php if ($category_name): ?>
                        <a href="category.php?id=<?php echo $product['category_id']; ?>"><?php echo escape($category_name); ?></a>
                        <i class="fas fa-chevron-right"></i>
                        <?php endif; ?>
                        <span><?php echo $product ? escape($product['name']) : 'Товар не найден'; ?></span>
                    </div>
                </div>
            </div>
        </section>

        <div class="container">
            <?php if (!$product): ?>
                <!-- Сообщение если товар не найден -->
                <div class="no-product-message">
                    <h2>Товар не найден</h2>
                    <p>К сожалению, запрашиваемый товар не существует или был удален.</p>
                    <a href="index.php" class="btn btn-primary">Вернуться в каталог</a>
                </div>
            <?php else: ?>
                <!-- Основной контент товара -->
                <div class="product-main">
                    <!-- Левая колонка - изображения -->
                    <div class="product-gallery">
                        <div class="main-image-container">
                            <img src="<?php echo escape($product['image_url']); ?>" 
                                 alt="<?php echo escape($product['name']); ?>" 
                                 class="main-product-image" id="mainProductImage">
                        </div>
                        <?php if (!empty($product_images)): ?>
                        <div class="image-thumbnails">
                            <?php foreach($product_images as $index => $image): ?>
                            <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>">
                                <img src="<?php echo escape($image['image_url']); ?>" 
                                     alt="<?php echo escape($image['alt_text'] ?? $product['name']); ?>" 
                                     data-full="<?php echo escape($image['image_url']); ?>">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Правая колонка - информация -->
                    <div class="product-info">
                        <div class="product-header">
                            <div class="product-category-badge">
                                <i class="fas fa-spa"></i> 
                                <?php echo escape($product['category_name'] ?? 'Каталог'); ?>
                                <?php if ($product['skin_type']): ?>
                                 • <?php echo escape($product['skin_type']); ?>
                                <?php endif; ?>
                            </div>
                            <h1 class="product-title"><?php echo escape($product['name']); ?></h1>
                            <div class="product-brand">
                                <?php echo escape($product['brand']); ?> 
                                <?php if ($product['weight']): ?>• <?php echo escape($product['weight']); ?><?php endif; ?>
                                <?php if ($product['volume']): ?>• <?php echo escape($product['volume']); ?><?php endif; ?>
                            </div>
                            
                            <div class="product-rating-info">
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
                                    <span class="rating-value"><?php echo number_format($rating, 1); ?></span>
                                    <span class="reviews-count">(<?php echo (int)$product['review_count']; ?> отзывов)</span>
                                </div>
                                <div class="stock-status <?php echo $product['in_stock'] ? 'in-stock' : 'out-of-stock'; ?>">
                                    <i class="fas <?php echo $product['in_stock'] ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                                    <?php echo $product['in_stock'] ? 'В наличии' : 'Нет в наличии'; ?>
                                </div>
                            </div>
                        </div>

                        <div class="product-price-section">
                            <div class="price-wrapper">
                                <span class="current-price"><?php echo number_format($product['current_price'], 0, '.', ' '); ?> ₽</span>
                                <?php if ($product['old_price'] && $product['old_price'] > $product['current_price']): ?>
                                <span class="old-price"><?php echo number_format($product['old_price'], 0, '.', ' '); ?> ₽</span>
                                <?php if ($product['discount_percent']): ?>
                                <span class="discount-badge">-<?php echo (int)$product['discount_percent']; ?>%</span>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <?php if ($product['old_price'] && $product['old_price'] > $product['current_price']): ?>
                            <div class="price-savings">
                                <i class="fas fa-piggy-bank"></i> 
                                Экономия <?php echo number_format($product['old_price'] - $product['current_price'], 0, '.', ' '); ?> ₽
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($product['short_description']): ?>
                        <div class="product-description-short">
                            <p><?php echo escape($product['short_description']); ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Характеристики -->
                        <div class="product-specs">
                            <?php if ($product['weight']): ?>
                            <div class="spec-item">
                                <i class="fas fa-weight"></i>
                                <span class="spec-label">Вес:</span>
                                <span class="spec-value"><?php echo escape($product['weight']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($product['skin_type']): ?>
                            <div class="spec-item">
                                <i class="fas fa-leaf"></i>
                                <span class="spec-label">Тип кожи:</span>
                                <span class="spec-value"><?php echo escape($product['skin_type']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($product['expiration']): ?>
                            <div class="spec-item">
                                <i class="fas fa-box"></i>
                                <span class="spec-label">Срок годности:</span>
                                <span class="spec-value"><?php echo escape($product['expiration']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="spec-item">
                                <i class="fas fa-shipping-fast"></i>
                                <span class="spec-label">Доставка:</span>
                                <span class="spec-value"><?php echo escape($product['delivery_time'] ?? '1-3 дня'); ?></span>
                            </div>
                        </div>

                        <!-- Кнопки действий -->
                        <div class="product-actions-single">
                            <?php if ($product['in_stock']): ?>
                            <div class="quantity-selector">
                                <button class="qty-btn minus" disabled><i class="fas fa-minus"></i></button>
                                <input type="number" class="qty-input" value="1" min="1" max="10">
                                <button class="qty-btn plus"><i class="fas fa-plus"></i></button>
                            </div>
                            
                            <button class="btn btn-primary btn-add-to-cart" data-id="<?php echo $product_id; ?>">
                                <i class="fas fa-shopping-cart"></i> Добавить в корзину
                            </button>
                            
                            <button class="btn btn-outline btn-buy-one-click" data-id="<?php echo $product_id; ?>">
                                <i class="fas fa-bolt"></i> Купить в 1 клик
                            </button>
                            <?php else: ?>
                            <button class="btn btn-secondary" disabled>
                                <i class="fas fa-ban"></i> Нет в наличии
                            </button>
                            <?php endif; ?>
                            
                            <button class="btn-favorite-single" data-id="<?php echo $product_id; ?>" title="Добавить в избранное">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>

                        <!-- Быстрые преимущества -->
                        <div class="product-benefits">
                            <div class="benefit-item">
                                <i class="fas fa-truck"></i>
                                <span>Бесплатная доставка от 5 000 ₽</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-shield-alt"></i>
                                <span>Официальная гарантия</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-undo"></i>
                                <span>Возврат в течение 14 дней</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Табы с детальной информацией -->
                <div class="product-tabs">
                    <div class="tabs-header">
                        <button class="tab-btn active" data-tab="description">
                            <i class="fas fa-info-circle"></i> Описание
                        </button>
                        <button class="tab-btn" data-tab="characteristics">
                            <i class="fas fa-list-alt"></i> Характеристики
                        </button>
                        <button class="tab-btn" data-tab="qa">
                            <i class="fas fa-question-circle"></i> Вопрос-ответ 
                            <?php if (!empty($product_questions)): ?>(<?php echo count($product_questions); ?>)<?php endif; ?>
                        </button>
                    </div>

                    <div class="tabs-content">
                        <!-- Описание -->
                        <div class="tab-pane active" id="description">
                            <h3><i class="fas fa-spa"></i> О продукте</h3>
                            <?php if ($product['full_description']): ?>
                                <?php echo nl2br(escape($product['full_description'])); ?>
                            <?php else: ?>
                                <p>Подробное описание товара.</p>
                            <?php endif; ?>
                            
                            <?php if ($product['composition'] || $product['usage_method']): ?>
                            <div class="feature-list">
                                <h4><i class="fas fa-check-circle"></i> Особенности:</h4>
                                <?php if ($product['composition']): ?>
                                <p><strong>Состав:</strong> <?php echo escape($product['composition']); ?></p>
                                <?php endif; ?>
                                
                                <?php if ($product['usage_method']): ?>
                                <p><strong>Способ применения:</strong> <?php echo escape($product['usage_method']); ?></p>
                                <?php endif; ?>
                                
                                <?php if ($product['contraindications']): ?>
                                <p><strong>Противопоказания:</strong> <?php echo escape($product['contraindications']); ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Характеристики -->
                        <div class="tab-pane" id="characteristics">
                            <h3><i class="fas fa-clipboard-list"></i> Технические характеристики</h3>
                            <div class="specs-table">
                                <div class="spec-row">
                                    <span class="spec-name">Бренд</span>
                                    <span class="spec-value"><?php echo escape($product['brand']); ?></span>
                                </div>
                                
                                <?php if ($product['weight']): ?>
                                <div class="spec-row">
                                    <span class="spec-name">Вес</span>
                                    <span class="spec-value"><?php echo escape($product['weight']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($product['volume']): ?>
                                <div class="spec-row">
                                    <span class="spec-name">Объем</span>
                                    <span class="spec-value"><?php echo escape($product['volume']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($product['skin_type']): ?>
                                <div class="spec-row">
                                    <span class="spec-name">Тип кожи</span>
                                    <span class="spec-value"><?php echo escape($product['skin_type']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($product['country_of_origin']): ?>
                                <div class="spec-row">
                                    <span class="spec-name">Страна производства</span>
                                    <span class="spec-value"><?php echo escape($product['country_of_origin']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($product['composition']): ?>
                                <div class="spec-row">
                                    <span class="spec-name">Состав</span>
                                    <span class="spec-value"><?php echo escape($product['composition']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($product['expiration']): ?>
                                <div class="spec-row">
                                    <span class="spec-name">Срок годности</span>
                                    <span class="spec-value"><?php echo escape($product['expiration']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($product['storage_conditions']): ?>
                                <div class="spec-row">
                                    <span class="spec-name">Условия хранения</span>
                                    <span class="spec-value"><?php echo escape($product['storage_conditions']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($product['usage_method']): ?>
                                <div class="spec-row">
                                    <span class="spec-name">Способ применения</span>
                                    <span class="spec-value"><?php echo escape($product['usage_method']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($product['contraindications']): ?>
                                <div class="spec-row">
                                    <span class="spec-name">Противопоказания</span>
                                    <span class="spec-value"><?php echo escape($product['contraindications']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($product['animal_testing']): ?>
                                <div class="spec-row">
                                    <span class="spec-name">Тестирование</span>
                                    <span class="spec-value"><?php echo escape($product['animal_testing']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php foreach($product_attributes as $attribute): ?>
                                <div class="spec-row">
                                    <span class="spec-name"><?php echo escape($attribute['attribute_name']); ?></span>
                                    <span class="spec-value"><?php echo escape($attribute['attribute_value']); ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Вопрос-ответ -->
                        <div class="tab-pane" id="qa">
                            <h3><i class="fas fa-question-circle"></i> Вопросы и ответы о товаре</h3>
                            
                            <!-- Информация о последней отправке -->
                            <?php if (isset($_SESSION['last_successful_question']) && $_SESSION['last_successful_question']['product_id'] == $product_id): ?>
                                <div class="alert alert-info" style="margin-bottom: 20px;" id="lastQuestionInfo">
                                    <i class="fas fa-info-circle"></i> Ваш последний вопрос был отправлен 
                                    <?php 
                                    $time_diff = time() - $_SESSION['last_successful_question']['time'];
                                    if ($time_diff < 60) {
                                        echo 'только что';
                                    } elseif ($time_diff < 3600) {
                                        echo floor($time_diff / 60) . ' минут назад';
                                    } else {
                                        echo floor($time_diff / 3600) . ' часов назад';
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($product_questions)): ?>
                            <div class="qa-list">
                                <?php foreach($product_questions as $question): ?>
                                <div class="qa-item">
                                    <div class="qa-question">
                                        <span>
                                            <strong><?php echo escape($question['user_name']); ?></strong> 
                                            - <?php echo escape($question['created_date']); ?>
                                            <br>
                                            <?php echo escape($question['question']); ?>
                                        </span>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                    <?php if (!empty($question['answer'])): ?>
                                    <div class="qa-answer" style="display: none;">
                                        <p><strong><i class="fas fa-reply"></i> Ответ FlaxTap:</strong><br>
                                        <?php echo nl2br(escape($question['answer'])); ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="no-questions">
                                <p><i class="fas fa-info-circle"></i> Пока нет вопросов об этом товаре. Будьте первым, кто задаст вопрос!</p>
                            </div>
                            <?php endif; ?>

                        <!-- Форма вопроса  -->
                        <div class="ask-question-form">
                            <h4><i class="fas fa-question"></i> Задать вопрос</h4>
                            
                            <?php 
                            // Простая обработка
                            $form_success = false;
                            
                            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_submit'])) {
                                $test_name = $_POST['test_name'] ?? '';
                                $test_email = $_POST['test_email'] ?? '';
                                $test_question = $_POST['test_question'] ?? '';
                                
                                if (!empty($test_name) && !empty($test_email) && !empty($test_question)) {
                                    // Прямой SQL запрос 
                                    $sql = "INSERT INTO product_questions (product_id, user_name, user_email, question, created_at) 
                                            VALUES ($product_id, 
                                            '" . $connection->real_escape_string($test_name) . "', 
                                            '" . $connection->real_escape_string($test_email) . "', 
                                            '" . $connection->real_escape_string($test_question) . "', 
                                            NOW())";
                                            
                                    if ($connection->query($sql)) {
                                        echo "<div class='alert alert-success'>✓ Данные успешно отправлены, ID: " . $connection->insert_id . "</div>";
                                        $form_success = true;
                                        
                                        // Очищаем переменные для очистки формы
                                        $test_name = '';
                                        $test_email = '';
                                        $test_question = '';
                                    } else {
                                        echo "<div class='alert alert-danger'>✗ Ошибка отправки: " . $connection->error . "</div>";
                                    }
                                } else {
                                    echo "<div class='alert alert-warning'>Все поля должны быть заполнены!</div>";
                                }
                            }
                            ?>
                            
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label>Имя:</label>
                                    <input type="text" name="test_name" placeholder="Введите ваше имя" 
                                        value="<?php echo htmlspecialchars($test_name ?? ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Email:</label>
                                    <input type="email" name="test_email" placeholder="Введите ваш email" 
                                        value="<?php echo htmlspecialchars($test_email ?? ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Ваш вопрос:</label>
                                    <textarea name="test_question" rows="4" placeholder="Введите ваш вопрос здесь..." required><?php 
                                        echo htmlspecialchars($test_question ?? ''); 
                                    ?></textarea>
                                    <small>Максимальная длина: 1000 символов</small>
                                </div>
                                
                                <button type="submit" name="test_submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Отправить 
                                </button>
                                
                                <?php if ($form_success): ?>
                                <script>
                                    // Дополнительно очищаем форму через JavaScript после успешной отправки
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const form = document.querySelector('.ask-question-form form');
                                        if (form) {
                                            form.reset();
                                        }
                                    });
                                </script>
                                <?php endif; ?>
                            </form>
                        </div>

                <!-- Рекомендуемые товары -->
                <?php if (!empty($recommended_products)): ?>
                <section class="recommended-products">
                    <div class="section-header">
                        <h2><i class="fas fa-star"></i> Рекомендуем посмотреть</h2>
                    </div>
                    
                    <div class="products-grid">
                        <?php foreach ($recommended_products as $rec_product): ?>
                        <div class="product-card catalog">
                            <?php if ($rec_product['discount_percent'] && $rec_product['discount_percent'] > 0): ?>
                            <div class="product-badge discount">-<?php echo (int)$rec_product['discount_percent']; ?>%</div>
                            <?php endif; ?>
                            
                            <div class="product-image">
                                <img src="<?php echo escape($rec_product['image_url']); ?>" 
                                     alt="<?php echo escape($rec_product['name']); ?>">
                                <a href="single.php?id=<?php echo (int)$rec_product['id']; ?>" class="quick-view">
                                    <i class="fas fa-eye"></i> Быстрый просмотр
                                </a>
                            </div>
                            <div class="product-content">
                                <div class="product-category"><?php echo escape($rec_product['brand']); ?></div>
                                <h3 class="product-title"><?php echo escape($rec_product['name']); ?></h3>
                                <p class="product-description">
                                    <?php 
                                    $description = escape($rec_product['short_description']);
                                    if (strlen($description) > 60) {
                                        echo substr($description, 0, 60) . '...';
                                    } else {
                                        echo $description;
                                    }
                                    ?>
                                </p>
                                <div class="product-rating">
                                    <div class="stars">
                                        <?php
                                        $rating = (float)$rec_product['rating'];
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
                                <div class="product-footer">
                                    <div class="product-price">
                                        <span class="current-price"><?php echo number_format($rec_product['current_price'], 0, '.', ' '); ?> ₽</span>
                                        <?php if ($rec_product['old_price'] && $rec_product['old_price'] > $rec_product['current_price']): ?>
                                        <span class="old-price"><?php echo number_format($rec_product['old_price'], 0, '.', ' '); ?> ₽</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-actions">
                                        <a href="single.php?id=<?php echo (int)$rec_product['id']; ?>" class="btn-cart">
                                            <i class="fas fa-shopping-cart"></i>
                                        </a>
                                        <button class="btn-favorite" data-id="<?php echo (int)$rec_product['id']; ?>">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php 
        include "../inc/modal.php";
        include "../inc/footer.php"; 
    ?>

    <script src="../assets/js/product.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/modals.js"></script>
    <script src="../assets/js/single.js"></script>
</body>
</html>