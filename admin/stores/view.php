<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

$store_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$store_id) {
    header("Location: index.php");
    exit();
}

// Получаем данные магазина
$query = "SELECT s.*, 
          u1.name as created_by_name,
          u2.name as updated_by_name
          FROM stores s
          LEFT JOIN users u1 ON s.created_by = u1.id
          LEFT JOIN users u2 ON s.updated_by = u2.id
          WHERE s.id = $store_id";
$result = mysqli_query($conn, $query);
$store = mysqli_fetch_assoc($result);

if (!$store) {
    header("Location: index.php");
    exit();
}

// Получаем изображения магазина
$images_query = "SELECT * FROM store_images WHERE store_id = $store_id ORDER BY sort_order";
$images_result = mysqli_query($conn, $images_query);

// Получаем персонал магазина
$staff_query = "SELECT * FROM store_staff WHERE store_id = $store_id AND is_active = 1 ORDER BY sort_order";
$staff_result = mysqli_query($conn, $staff_query);

// Получаем отзывы
$reviews_query = "SELECT sr.*, u.name as user_name 
                  FROM store_reviews sr
                  LEFT JOIN users u ON sr.user_id = u.id
                  WHERE sr.store_id = $store_id AND sr.is_approved = 1
                  ORDER BY sr.created_at DESC LIMIT 5";
$reviews_result = mysqli_query($conn, $reviews_query);

// Статистика отзывов
$reviews_stats_query = "SELECT 
    COUNT(*) as total,
    AVG(rating) as avg_rating,
    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as rating_5,
    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as rating_4,
    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as rating_3,
    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as rating_2,
    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as rating_1
    FROM store_reviews 
    WHERE store_id = $store_id AND is_approved = 1";
$reviews_stats_result = mysqli_query($conn, $reviews_stats_query);
$reviews_stats = mysqli_fetch_assoc($reviews_stats_result);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр магазина - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style/stores/view.css">
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Просмотр магазина</h1>
            <div>
                <a href="edit.php?id=<?php echo $store_id; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Редактировать
                </a>
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Назад
                </a>
            </div>
        </div>
        
        <div class="view-container">
            <!-- Заголовок магазина -->
            <div class="store-header">
                <div class="header-top">
                    <div>
                        <h1 class="store-title">
                            <?php echo htmlspecialchars($store['name']); ?>
                            <span class="type-badge-large 
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
                            <?php if ($store['is_default']): ?>
                                <span class="type-badge-large badge-shop" style="background-color: #c6f6d5; color: #22543d;">
                                    По умолчанию
                                </span>
                            <?php endif; ?>
                        </h1>
                        <div class="store-meta">
                            <div class="meta-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($store['city'] . ', ' . $store['address']); ?></span>
                            </div>
                            <?php if ($store['phone']): ?>
                                <div class="meta-item">
                                    <i class="fas fa-phone"></i>
                                    <span><?php echo htmlspecialchars($store['phone']); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="meta-item">
                                <i class="fas fa-calendar"></i>
                                <span>Создан: <?php echo date('d.m.Y', strtotime($store['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="status-badge-large <?php echo $store['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                            <?php echo $store['is_active'] ? 'Активен' : 'Неактивен'; ?>
                        </div>
                    </div>
                </div>
                
                <div class="header-actions">
                    <button onclick="copyAddress()" class="btn btn-outline">
                        <i class="fas fa-copy"></i> Скопировать адрес
                    </button>
                    <?php if ($store['phone']): ?>
                        <a href="tel:<?php echo htmlspecialchars($store['phone']); ?>" class="btn btn-outline">
                            <i class="fas fa-phone"></i> Позвонить
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Вкладки -->
            <div class="tabs">
                <a href="?id=<?php echo $store_id; ?>&tab=info" 
                   class="tab active">
                    <i class="fas fa-info-circle"></i> Информация
                </a>
                <a href="?id=<?php echo $store_id; ?>&tab=hours" 
                   class="tab">
                    <i class="fas fa-clock"></i> Время работы
                </a>
                <a href="?id=<?php echo $store_id; ?>&tab=gallery" 
                   class="tab">
                    <i class="fas fa-images"></i> Галерея
                    <?php if (mysqli_num_rows($images_result) > 0): ?>
                        <span style="background: var(--primary-color); color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; margin-left: 5px;">
                            <?php echo mysqli_num_rows($images_result); ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="?id=<?php echo $store_id; ?>&tab=staff" 
                   class="tab">
                    <i class="fas fa-users"></i> Персонал
                    <?php if (mysqli_num_rows($staff_result) > 0): ?>
                        <span style="background: var(--primary-color); color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; margin-left: 5px;">
                            <?php echo mysqli_num_rows($staff_result); ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="?id=<?php echo $store_id; ?>&tab=reviews" 
                   class="tab">
                    <i class="fas fa-star"></i> Отзывы
                    <?php if ($reviews_stats['total'] > 0): ?>
                        <span style="background: var(--primary-color); color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; margin-left: 5px;">
                            <?php echo $reviews_stats['total']; ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="?id=<?php echo $store_id; ?>&tab=meta" 
                   class="tab">
                    <i class="fas fa-search"></i> SEO
                </a>
            </div>
            
            <!-- Содержимое вкладок -->
            <div id="infoTab" class="tab-content active">
                <!-- Контактная информация -->
                <div class="details-card">
                    <h3 class="section-title">Контактная информация</h3>
                    <div class="info-grid">
                        <?php if ($store['phone']): ?>
                            <div class="info-item">
                                <div class="info-label">Телефон</div>
                                <div class="info-value"><?php echo htmlspecialchars($store['phone']); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($store['email']): ?>
                            <div class="info-item">
                                <div class="info-label">Email</div>
                                <div class="info-value"><?php echo htmlspecialchars($store['email']); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($store['manager_name']): ?>
                            <div class="info-item">
                                <div class="info-label">Менеджер</div>
                                <div class="info-value"><?php echo htmlspecialchars($store['manager_name']); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="info-item">
                            <div class="info-label">Город</div>
                            <div class="info-value"><?php echo htmlspecialchars($store['city']); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Адрес</div>
                            <div class="info-value"><?php echo htmlspecialchars($store['address']); ?></div>
                        </div>
                        
                        <?php if ($store['latitude'] && $store['longitude']): ?>
                            <div class="info-item">
                                <div class="info-label">Координаты</div>
                                <div class="info-value"><?php echo $store['latitude']; ?>, <?php echo $store['longitude']; ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Описание -->
                <?php if ($store['description']): ?>
                    <div class="details-card">
                        <h3 class="section-title">Описание</h3>
                        <div class="content-box">
                            <?php echo nl2br(htmlspecialchars($store['description'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Удобства -->
                <?php if ($store['facilities']): ?>
                    <div class="details-card">
                        <h3 class="section-title">Удобства и услуги</h3>
                        <div class="content-box">
                            <?php echo nl2br(htmlspecialchars($store['facilities'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Характеристики -->
                <div class="details-card">
                    <h3 class="section-title">Характеристики</h3>
                    <div class="info-grid">
                        <?php if ($store['area_size']): ?>
                            <div class="info-item">
                                <div class="info-label">Площадь</div>
                                <div class="info-value"><?php echo $store['area_size']; ?> м²</div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="info-item">
                            <div class="info-label">Парковка</div>
                            <div class="info-value"><?php echo $store['has_parking'] ? 'Есть' : 'Нет'; ?></div>
                        </div>
                        
                        <?php if ($store['has_parking'] && $store['parking_spots'] > 0): ?>
                            <div class="info-item">
                                <div class="info-label">Количество мест</div>
                                <div class="info-value"><?php echo $store['parking_spots']; ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="info-item">
                            <div class="info-label">Доступность</div>
                            <div class="info-value"><?php echo $store['is_wheelchair_accessible'] ? 'Для инвалидов-колясочников' : 'Ограничена'; ?></div>
                        </div>
                        
                        <?php if ($store['max_order_weight']): ?>
                            <div class="info-item">
                                <div class="info-label">Макс. вес заказа</div>
                                <div class="info-value"><?php echo $store['max_order_weight']; ?> кг</div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($store['storage_temperature']): ?>
                            <div class="info-item">
                                <div class="info-label">Температурный режим</div>
                                <div class="info-value"><?php echo htmlspecialchars($store['storage_temperature']); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($store['type'] == 'warehouse_shop'): ?>
                            <div class="info-item">
                                <div class="info-label">Холодильное оборудование</div>
                                <div class="info-value"><?php echo $store['has_refrigeration'] ? 'Есть' : 'Нет'; ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Вкладка времени работы -->
            <div id="hoursTab" class="tab-content">
                <div class="details-card">
                    <h3 class="section-title">Время работы</h3>
                    
                    <?php if ($store['is_24_7']): ?>
                        <div class="content-box">
                            <h4>Круглосуточно</h4>
                        </div>
                    <?php else: ?>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Понедельник - Пятница</div>
                                <div class="info-value"><?php echo htmlspecialchars($store['working_hours_weekdays']); ?></div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Суббота</div>
                                <div class="info-value"><?php echo htmlspecialchars($store['working_hours_saturday']); ?></div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Воскресенье</div>
                                <div class="info-value"><?php echo htmlspecialchars($store['working_hours_sunday']); ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($store['working_hours_notes']): ?>
                        <div class="content-box" style="margin-top: 20px;">
                            <h4>Примечания</h4>
                            <p><?php echo htmlspecialchars($store['working_hours_notes']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Вкладка галереи -->
            <div id="galleryTab" class="tab-content">
                <?php if (mysqli_num_rows($images_result) > 0): ?>
                    <div class="details-card">
                        <h3 class="section-title">Галерея изображений</h3>
                        <div class="gallery-grid">
                            <?php mysqli_data_seek($images_result, 0); ?>
                            <?php while($image = mysqli_fetch_assoc($images_result)): ?>
                                <div class="gallery-item">
                                    <img src="<?php echo htmlspecialchars($image['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($image['alt_text']); ?>">
                                    <?php if ($image['alt_text']): ?>
                                        <p><?php echo htmlspecialchars($image['alt_text']); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-images"></i>
                        <p>Изображений в галерее нет</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Вкладка персонала -->
            <div id="staffTab" class="tab-content">
                <?php if (mysqli_num_rows($staff_result) > 0): ?>
                    <div class="details-card">
                        <h3 class="section-title">Персонал магазина</h3>
                        <div class="staff-grid">
                            <?php mysqli_data_seek($staff_result, 0); ?>
                            <?php while($staff = mysqli_fetch_assoc($staff_result)): ?>
                                <div class="staff-item">
                                    <?php if ($staff['photo_url']): ?>
                                        <img src="<?php echo htmlspecialchars($staff['photo_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($staff['name']); ?>" class="staff-photo">
                                    <?php else: ?>
                                        <div class="staff-photo">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="staff-name"><?php echo htmlspecialchars($staff['name']); ?></div>
                                    <div class="staff-position"><?php echo htmlspecialchars($staff['position']); ?></div>
                                    <?php if ($staff['phone']): ?>
                                        <div style="font-size: 13px; color: var(--gray-color);">
                                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($staff['phone']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($staff['email']): ?>
                                        <div style="font-size: 13px; color: var(--gray-color);">
                                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($staff['email']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-users"></i>
                        <p>Информации о персонале нет</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Вкладка отзывов -->
            <div id="reviewsTab" class="tab-content">
                <?php if ($reviews_stats['total'] > 0): ?>
                    <div class="details-card">
                        <h3 class="section-title">Отзывы о магазине</h3>
                        
                        <!-- Статистика -->
                        <div class="reviews-stats">
                            <div class="stat-item">
                                <div class="stat-number"><?php echo number_format($reviews_stats['avg_rating'], 1); ?></div>
                                <div class="rating-stars">
                                    <?php
                                    $avg_rating = round($reviews_stats['avg_rating']);
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
                            
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $reviews_stats['total']; ?></div>
                                <div>Всего отзывов</div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $reviews_stats['rating_5']; ?></div>
                                <div class="rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Список отзывов -->
                        <div class="reviews-list">
                            <h4 style="margin-bottom: 15px;">Последние отзывы:</h4>
                            <?php mysqli_data_seek($reviews_result, 0); ?>
                            <?php while($review = mysqli_fetch_assoc($reviews_result)): ?>
                                <div class="review-item">
                                    <div class="review-header">
                                        <div class="review-author">
                                            <?php echo htmlspecialchars($review['user_name'] ?: $review['author_name']); ?>
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
                                        <div class="review-content">
                                            <?php echo htmlspecialchars($review['comment']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-star"></i>
                        <p>Отзывов о магазине пока нет</p>
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
                            <div class="info-value"><?php echo htmlspecialchars($store['meta_title']); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Meta Description</div>
                            <div class="info-value"><?php echo htmlspecialchars($store['meta_description']); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Meta Keywords</div>
                            <div class="info-value"><?php echo htmlspecialchars($store['meta_keywords']); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">URL магазина</div>
                            <div class="info-value">/stores/<?php echo $store['id']; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        function copyAddress() {
            const address = `<?php echo htmlspecialchars($store['city'] . ', ' . $store['address']); ?>`;
            navigator.clipboard.writeText(address)
                .then(() => {
                    alert('Адрес скопирован в буфер обмена');
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
                history.pushState(null, '', `?id=<?php echo $store_id; ?>&tab=${tabId}`);
                
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