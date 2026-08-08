<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$product_id) {
    header("Location: index.php");
    exit();
}

// Получаем данные товара с категорией
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.id = $product_id";
$result = mysqli_query($conn, $query);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    header("Location: index.php");
    exit();
}

// Получаем атрибуты товара
$attributes_query = "SELECT * FROM product_attributes WHERE product_id = $product_id ORDER BY sort_order";
$attributes_result = mysqli_query($conn, $attributes_query);

// Получаем дополнительные изображения
$images_query = "SELECT * FROM product_images WHERE product_id = $product_id ORDER BY sort_order";
$images_result = mysqli_query($conn, $images_query);

// Получаем вопросы к товару
$questions_query = "SELECT pq.*, u.name as user_name, u2.name as answered_by_name 
                   FROM product_questions pq
                   LEFT JOIN users u ON pq.user_id = u.id
                   LEFT JOIN users u2 ON pq.answered_by = u2.id
                   WHERE pq.product_id = $product_id 
                   ORDER BY pq.is_answered ASC, pq.created_at DESC 
                   LIMIT 10";
$questions_result = mysqli_query($conn, $questions_query);

// Получаем отзывы о товаре
$reviews_query = "SELECT pr.*, u.name as user_name 
                  FROM product_reviews pr
                  LEFT JOIN users u ON pr.user_id = u.id
                  WHERE pr.product_id = $product_id AND pr.is_approved = 1
                  ORDER BY pr.created_at DESC 
                  LIMIT 5";
$reviews_result = mysqli_query($conn, $reviews_query);

// Статистика вопросов и отзывов
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM product_questions WHERE product_id = $product_id) as questions_count,
    (SELECT COUNT(*) FROM product_questions WHERE product_id = $product_id AND is_answered = 0) as unanswered_questions,
    (SELECT COUNT(*) FROM product_reviews WHERE product_id = $product_id AND is_approved = 1) as reviews_count,
    (SELECT AVG(rating) FROM product_reviews WHERE product_id = $product_id AND is_approved = 1) as avg_rating";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр товара - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style/products/view.css">
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Просмотр товара</h1>
            <div>
                <a href="edit.php?id=<?php echo $product_id; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Редактировать
                </a>
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Назад к списку
                </a>
            </div>
        </div>
        
        <!-- Заголовок товара -->
        <div class="product-header">
            <div class="header-top">
                <div>
                    <h1 class="product-title">
                        <?php echo htmlspecialchars($product['name']); ?>
                        <?php if ($product['is_featured']): ?>
                            <span class="featured-badge badge-featured">Рекомендуемый</span>
                        <?php endif; ?>
                        <?php if ($product['is_bestseller']): ?>
                            <span class="featured-badge badge-bestseller">Хит продаж</span>
                        <?php endif; ?>
                    </h1>
                    <div class="product-meta">
                        <div class="meta-item">
                            <i class="fas fa-tag"></i>
                            <span><?php echo htmlspecialchars($product['brand']); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-folder"></i>
                            <span><?php echo htmlspecialchars($product['category_name']); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-calendar"></i>
                            <span>Добавлен: <?php echo date('d.m.Y', strtotime($product['created_at'])); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-sync-alt"></i>
                            <span>Обновлен: <?php echo date('d.m.Y', strtotime($product['updated_at'])); ?></span>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="status-badge-large <?php echo $product['in_stock'] ? 'badge-instock' : 'badge-outstock'; ?>">
                        <?php echo $product['in_stock'] ? 'В наличии' : 'Нет в наличии'; ?>
                    </div>
                </div>
            </div>
            
            <div class="header-actions">
                <button onclick="copyProductInfo()" class="btn btn-outline">
                    <i class="fas fa-copy"></i> Скопировать информацию
                </button>
                <a href="delete.php?id=<?php echo $product_id; ?>" class="btn btn-danger"
                   onclick="return confirm('Вы уверены, что хотите удалить этот товар?');">
                    <i class="fas fa-trash"></i> Удалить товар
                </a>
            </div>
        </div>
        
        <!-- Вкладки -->
        <div class="tabs">
            <a href="?id=<?php echo $product_id; ?>&tab=info" class="tab active">
                <i class="fas fa-info-circle"></i> Информация
            </a>
            <a href="?id=<?php echo $product_id; ?>&tab=images" class="tab">
                <i class="fas fa-images"></i> Изображения
                <?php if (mysqli_num_rows($images_result) > 0): ?>
                    <span class="tab-badge"><?php echo mysqli_num_rows($images_result); ?></span>
                <?php endif; ?>
            </a>
            <a href="?id=<?php echo $product_id; ?>&tab=attributes" class="tab">
                <i class="fas fa-tags"></i> Атрибуты
                <?php if (mysqli_num_rows($attributes_result) > 0): ?>
                    <span class="tab-badge"><?php echo mysqli_num_rows($attributes_result); ?></span>
                <?php endif; ?>
            </a>
            <a href="?id=<?php echo $product_id; ?>&tab=questions" class="tab">
                <i class="fas fa-question-circle"></i> Вопросы
                <span class="tab-badge"><?php echo $stats['questions_count']; ?></span>
                <?php if ($stats['unanswered_questions'] > 0): ?>
                    <span class="tab-badge danger"><?php echo $stats['unanswered_questions']; ?></span>
                <?php endif; ?>
            </a>
            <a href="?id=<?php echo $product_id; ?>&tab=reviews" class="tab">
                <i class="fas fa-star"></i> Отзывы
                <?php if ($stats['reviews_count'] > 0): ?>
                    <span class="tab-badge"><?php echo $stats['reviews_count']; ?></span>
                <?php endif; ?>
            </a>
            <a href="?id=<?php echo $product_id; ?>&tab=meta" class="tab">
                <i class="fas fa-search"></i> SEO
            </a>
        </div>
        
        <!-- Содержимое вкладок -->
        <div id="infoTab" class="tab-content active">
            <div class="row" style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                <!-- Левая колонка -->
                <div>
                    <!-- Основное изображение и цена -->
                    <div class="details-card">
                        <?php if ($product['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                 class="image-main" alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 onerror="this.src='https://via.placeholder.com/400x400?text=No+Image'">
                        <?php else: ?>
                            <div class="no-data">
                                <i class="fas fa-image"></i>
                                <p>Изображение не загружено</p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="price-box">
                            <?php if ($product['discount_percent']): ?>
                                <div class="discount-badge-large">-<?php echo $product['discount_percent']; ?>%</div>
                            <?php endif; ?>
                            
                            <div class="current-price">
                                <?php echo number_format($product['current_price'], 2); ?> ₽
                            </div>
                            
                            <?php if ($product['old_price'] && $product['old_price'] > $product['current_price']): ?>
                                <div class="old-price">
                                    <?php echo number_format($product['old_price'], 2); ?> ₽
                                </div>
                            <?php endif; ?>
                            
                            <div style="font-size: 14px; opacity: 0.9;">
                                ID: <?php echo $product['id']; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Краткое описание -->
                    <?php if ($product['short_description']): ?>
                        <div class="details-card">
                            <h3 class="section-title">Краткое описание</h3>
                            <div class="content-box">
                                <?php echo nl2br(htmlspecialchars($product['short_description'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Полное описание -->
                    <?php if ($product['full_description']): ?>
                        <div class="details-card">
                            <h3 class="section-title">Полное описание</h3>
                            <div class="content-box">
                                <?php echo $product['full_description']; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Правая колонка -->
                <div>
                    <!-- Характеристики -->
                    <div class="details-card">
                        <h3 class="section-title">Характеристики</h3>
                        <div class="info-grid">
                            <?php if ($product['weight']): ?>
                                <div class="info-item">
                                    <div class="info-label">Вес/объем</div>
                                    <div class="info-value"><?php echo htmlspecialchars($product['weight']); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($product['skin_type']): ?>
                                <div class="info-item">
                                    <div class="info-label">Тип кожи</div>
                                    <div class="info-value"><?php echo htmlspecialchars($product['skin_type']); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($product['volume']): ?>
                                <div class="info-item">
                                    <div class="info-label">Объем/размер</div>
                                    <div class="info-value"><?php echo htmlspecialchars($product['volume']); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($product['country_of_origin']): ?>
                                <div class="info-item">
                                    <div class="info-label">Страна производства</div>
                                    <div class="info-value"><?php echo htmlspecialchars($product['country_of_origin']); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($product['expiration']): ?>
                                <div class="info-item">
                                    <div class="info-label">Срок годности</div>
                                    <div class="info-value"><?php echo htmlspecialchars($product['expiration']); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($product['storage_conditions']): ?>
                                <div class="info-item">
                                    <div class="info-label">Условия хранения</div>
                                    <div class="info-value"><?php echo htmlspecialchars($product['storage_conditions']); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($product['animal_testing']): ?>
                                <div class="info-item">
                                    <div class="info-label">Тестирование на животных</div>
                                    <div class="info-value"><?php echo htmlspecialchars($product['animal_testing']); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($product['delivery_time']): ?>
                                <div class="info-item">
                                    <div class="info-label">Срок доставки</div>
                                    <div class="info-value"><?php echo htmlspecialchars($product['delivery_time']); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($product['warranty']): ?>
                                <div class="info-item">
                                    <div class="info-label">Гарантия</div>
                                    <div class="info-value"><?php echo htmlspecialchars($product['warranty']); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="info-item">
                                <div class="info-label">Порядок сортировки</div>
                                <div class="info-value"><?php echo $product['sort_order']; ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Состав -->
                    <?php if ($product['composition']): ?>
                        <div class="details-card">
                            <h3 class="section-title">Состав</h3>
                            <div class="content-box" style="font-size: 13px; line-height: 1.4;">
                                <?php echo nl2br(htmlspecialchars($product['composition'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Способ применения -->
                    <?php if ($product['usage_method']): ?>
                        <div class="details-card">
                            <h3 class="section-title">Способ применения</h3>
                            <div class="content-box">
                                <?php echo nl2br(htmlspecialchars($product['usage_method'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Противопоказания -->
                    <?php if ($product['contraindications']): ?>
                        <div class="details-card">
                            <h3 class="section-title">Противопоказания</h3>
                            <div class="content-box">
                                <?php echo nl2br(htmlspecialchars($product['contraindications'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Вкладка изображений -->
        <div id="imagesTab" class="tab-content">
            <?php if (mysqli_num_rows($images_result) > 0): ?>
                <div class="details-card">
                    <h3 class="section-title">Дополнительные изображения</h3>
                    <div class="gallery-grid">
                        <?php mysqli_data_seek($images_result, 0); ?>
                        <?php while($image = mysqli_fetch_assoc($images_result)): ?>
                            <div class="gallery-item">
                                <img src="<?php echo htmlspecialchars($image['image_url']); ?>" 
                                     alt="Изображение товара"
                                     onerror="this.src='https://via.placeholder.com/200x200?text=No+Image'">
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-images"></i>
                    <p>Дополнительных изображений нет</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Вкладка атрибутов -->
        <div id="attributesTab" class="tab-content">
            <?php if (mysqli_num_rows($attributes_result) > 0): ?>
                <div class="details-card">
                    <h3 class="section-title">Дополнительные атрибуты</h3>
                    <div class="info-grid">
                        <?php mysqli_data_seek($attributes_result, 0); ?>
                        <?php while($attr = mysqli_fetch_assoc($attributes_result)): ?>
                            <div class="info-item">
                                <div class="info-label"><?php echo htmlspecialchars($attr['attribute_name']); ?></div>
                                <div class="info-value"><?php echo htmlspecialchars($attr['attribute_value']); ?></div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-tags"></i>
                    <p>Дополнительных атрибутов нет</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Вкладка вопросов -->
        <div id="questionsTab" class="tab-content">
            <!-- Статистика -->
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $stats['questions_count']; ?></div>
                    <div>Всего вопросов</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $stats['unanswered_questions']; ?></div>
                    <div>Без ответа</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $stats['questions_count'] - $stats['unanswered_questions']; ?></div>
                    <div>Отвеченные</div>
                </div>
            </div>
            
            <?php if (mysqli_num_rows($questions_result) > 0): ?>
                <div class="details-card">
                    <h3 class="section-title">Последние вопросы</h3>
                    <?php mysqli_data_seek($questions_result, 0); ?>
                    <?php while($question = mysqli_fetch_assoc($questions_result)): ?>
                        <div class="question-item <?php echo $question['is_answered'] ? 'answered' : 'unanswered'; ?>">
                            <div class="question-header">
                                <div class="question-author">
                                    <?php echo htmlspecialchars($question['user_name'] ?: 'Аноним'); ?>
                                </div>
                                <div class="question-date">
                                    <?php echo date('d.m.Y H:i', strtotime($question['created_at'])); ?>
                                </div>
                            </div>
                            <div class="question-content">
                                <?php echo nl2br(htmlspecialchars($question['question'])); ?>
                            </div>
                            <?php if ($question['is_answered'] && $question['answer']): ?>
                                <div class="answer-content">
                                    <strong>Ответ:</strong> 
                                    <?php if ($question['answered_by_name']): ?>
                                        <em>(<?php echo htmlspecialchars($question['answered_by_name']); ?>)</em><br>
                                    <?php endif; ?>
                                    <?php echo nl2br(htmlspecialchars($question['answer'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-question-circle"></i>
                    <p>Вопросов по этому товару пока нет</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Вкладка отзывов -->
        <div id="reviewsTab" class="tab-content">
            <!-- Статистика -->
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $stats['reviews_count']; ?></div>
                    <div>Всего отзывов</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo number_format($stats['avg_rating'] ?? 0, 1); ?></div>
                    <div class="rating-stars">
                        <?php
                       $avg_rating = round($stats['avg_rating'] ?? 0);
                        for ($i = 1; $i <= 5; $i++):
                            if ($i <= $avg_rating):
                                echo '<i class="fas fa-star"></i>';
                            else:
                                echo '<i class="far fa-star"></i>';
                            endif;
                        endfor;
                        ?>
                    </div>
                    <div>Средний рейтинг</div>
                </div>
            </div>
            
            <?php if (mysqli_num_rows($reviews_result) > 0): ?>
                <div class="details-card">
                    <h3 class="section-title">Последние отзывы</h3>
                    <?php mysqli_data_seek($reviews_result, 0); ?>
                    <?php while($review = mysqli_fetch_assoc($reviews_result)): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="review-author">
                                    <?php echo htmlspecialchars($review['user_name'] ?: 'Аноним'); ?>
                                    <?php if ($review['title']): ?>
                                        - <?php echo htmlspecialchars($review['title']); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="review-date">
                                    <?php echo date('d.m.Y', strtotime($review['created_at'])); ?>
                                </div>
                            </div>
                            <div class="review-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= $review['rating']): ?>
                                        <i class="fas fa-star"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <?php if ($review['comment']): ?>
                                <div class="question-content">
                                    <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-star"></i>
                    <p>Отзывов по этому товару пока нет</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Вкладка SEO -->
        <div id="metaTab" class="tab-content">
            <div class="details-card">
                <h3 class="section-title">SEO информация</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Meta Title</div>
                        <div class="info-value"><?php echo htmlspecialchars($product['meta_title']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Meta Description</div>
                        <div class="info-value"><?php echo htmlspecialchars($product['meta_description']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Meta Keywords</div>
                        <div class="info-value"><?php echo htmlspecialchars($product['meta_keywords']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">URL товара</div>
                        <div class="info-value">/product/<?php echo htmlspecialchars($product['slug']); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        function copyProductInfo() {
            const productInfo = `
Название: <?php echo htmlspecialchars($product['name']); ?>
Бренд: <?php echo htmlspecialchars($product['brand']); ?>
Категория: <?php echo htmlspecialchars($product['category_name']); ?>
Цена: <?php echo number_format($product['current_price'], 2); ?> ₽
Статус: <?php echo $product['in_stock'] ? 'В наличии' : 'Нет в наличии'; ?>
ID: <?php echo $product['id']; ?>
            `.trim();
            
            navigator.clipboard.writeText(productInfo)
                .then(() => {
                    alert('Информация о товаре скопирована в буфер обмена');
                })
                .catch(err => {
                    console.error('Ошибка копирования: ', err);
                });
        }
        
        // Переключение вкладок
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                const tabId = this.getAttribute('href').split('tab=')[1];
                
                // Обновляем URL без перезагрузки страницы
                history.pushState(null, '', `?id=<?php echo $product_id; ?>&tab=${tabId}`);
                
                // Активируем вкладку
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                document.getElementById(tabId + 'Tab').classList.add('active');
            });
        });
        
        // Инициализация активной вкладки из URL
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab') || 'info';
            
            // Активируем вкладку
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
                if (tab.getAttribute('href').includes(`tab=${activeTab}`)) {
                    tab.classList.add('active');
                }
            });
            
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
                if (content.id === activeTab + 'Tab') {
                    content.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>