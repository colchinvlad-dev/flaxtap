<?php
ob_start(); // Включаем буферизацию вывода
include '../config/database.php';
// Функция для безопасного вывода
function escape($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Получаем slug из URL
$slug = isset($_GET['slug']) ? mysqli_real_escape_string($connection, $_GET['slug']) : '';

if (empty($slug)) {
    // Редирект на список новостей, если slug не указан
    header('Location: index.php');
    exit();
}

// Получаем новость по slug
$query = "
    SELECT 
        n.*, 
        a.name as author_name, 
        a.position as author_position,
        a.avatar_url as author_avatar,
        a.bio as author_bio,
        nc.name as category_name,
        nc.color as category_color
    FROM news n
    LEFT JOIN news_authors a ON n.author_id = a.id
    LEFT JOIN news_categories nc ON n.category_id = nc.id
    WHERE n.slug = '$slug' AND n.status = 'published'
";

$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    // Новость не найдена
    header('Location: index.php');
    exit();
}

$news = mysqli_fetch_assoc($result);

// Увеличиваем счетчик просмотров
$updateViews = "UPDATE news SET views_count = views_count + 1 WHERE id = " . $news['id'];
mysqli_query($connection, $updateViews);

// Получаем галерею для новости
$galleryQuery = "SELECT * FROM news_gallery WHERE news_id = " . $news['id'] . " ORDER BY sort_order";
$galleryResult = mysqli_query($connection, $galleryQuery);
$gallery = [];
if ($galleryResult) {
    while ($row = mysqli_fetch_assoc($galleryResult)) {
        $gallery[] = $row;
    }
}

// Получаем вложения для новости
$attachmentsQuery = "SELECT * FROM news_attachments WHERE news_id = " . $news['id'] . " ORDER BY sort_order";
$attachmentsResult = mysqli_query($connection, $attachmentsQuery);
$attachments = [];
if ($attachmentsResult) {
    while ($row = mysqli_fetch_assoc($attachmentsResult)) {
        $attachments[] = $row;
    }
}

// Получаем похожие новости (из той же категории)
$relatedQuery = "
    SELECT 
        n.*, 
        a.name as author_name,
        nc.name as category_name
    FROM news n
    LEFT JOIN news_authors a ON n.author_id = a.id
    LEFT JOIN news_categories nc ON n.category_id = nc.id
    WHERE n.id != " . $news['id'] . " 
    AND n.status = 'published'
    AND (n.category_id = " . ($news['category_id'] ?? 0) . " OR n.id IN (
        SELECT related_news_id FROM news_related WHERE news_id = " . $news['id'] . "
    ))
    ORDER BY n.published_at DESC
    LIMIT 3
";

$relatedResult = mysqli_query($connection, $relatedQuery);
$relatedNews = [];
if ($relatedResult) {
    while ($row = mysqli_fetch_assoc($relatedResult)) {
        $relatedNews[] = $row;
    }
}

// Получаем теги новости
$tagsQuery = "
    SELECT t.* FROM news_tags t
    JOIN news_news_tags nt ON t.id = nt.tag_id
    WHERE nt.news_id = " . $news['id']
;
$tagsResult = mysqli_query($connection, $tagsQuery);
$tags = [];
if ($tagsResult) {
    while ($row = mysqli_fetch_assoc($tagsResult)) {
        $tags[] = $row;
    }
}

// Форматируем дату
$publishedDate = !empty($news['published_at']) ? date('d.m.Y', strtotime($news['published_at'])) : '';
$publishedDateFull = !empty($news['published_at']) ? date('d F Y', strtotime($news['published_at'])) : '';

// Устанавливаем title страницы
$pageTitle = !empty($news['meta_title']) ? $news['meta_title'] : $news['title'] . ' - Новости FlaxTap';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($pageTitle); ?></title>
    
    <!-- Мета-теги для SEO -->
    <?php if (!empty($news['meta_description'])): ?>
        <meta name="description" content="<?php echo escape($news['meta_description']); ?>">
    <?php endif; ?>
    
    <?php if (!empty($news['meta_keywords'])): ?>
        <meta name="keywords" content="<?php echo escape($news['meta_keywords']); ?>">
    <?php endif; ?>
    
    <!-- Open Graph теги для соцсетей -->
    <meta property="og:title" content="<?php echo escape($news['title']); ?>">
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    
    <?php if (!empty($news['main_image_url'])): ?>
        <meta property="og:image" content="<?php echo escape($news['main_image_url']); ?>">
    <?php endif; ?>
    
    <?php if (!empty($news['excerpt'])): ?>
        <meta property="og:description" content="<?php echo escape($news['excerpt']); ?>">
    <?php endif; ?>
    
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="../assets/style/news.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include "../inc/header.php"; ?>

    <!-- Страница отдельной новости -->
    <section class="single-news-section">
        <div class="container">
            <!-- Шапка новости -->
            <div class="single-news-header">
                <a href="index.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Все новости
                </a>
                
                <h1 class="single-news-title"><?php echo escape($news['title']); ?></h1>
                
                <div class="single-news-meta">
                    <?php if (!empty($publishedDate)): ?>
                    <div class="meta-item">
                        <i class="far fa-calendar"></i>
                        <span><?php echo $publishedDateFull; ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($news['reading_time_minutes'])): ?>
                    <div class="meta-item">
                        <i class="far fa-clock"></i>
                        <span><?php echo $news['reading_time_minutes']; ?> минут чтения</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($news['views_count'])): ?>
                    <div class="meta-item">
                        <i class="far fa-eye"></i>
                        <span><?php echo number_format($news['views_count'], 0, ',', ' '); ?> просмотров</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($tags)): ?>
                    <div class="meta-item">
                        <i class="fas fa-tag"></i>
                        <span>
                            <?php 
                            $tagNames = array_map(function($tag) {
                                return escape($tag['name']);
                            }, $tags);
                            echo implode(', ', $tagNames);
                            ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($news['author_name'])): ?>
                    <div class="meta-item">
                        <i class="fas fa-user-edit"></i>
                        <span>
                            <?php echo escape($news['author_name']); ?>
                            <?php if (!empty($news['author_position'])): ?>
                                , <?php echo escape($news['author_position']); ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Главное изображение -->
            <?php if (!empty($news['main_image_url'])): ?>
            <div class="single-news-image">
                <img src="<?php echo escape($news['main_image_url']); ?>" alt="<?php echo escape($news['title']); ?>">
            </div>
            <?php endif; ?>
            
            <!-- Контент новости -->
            <div class="single-news-content">
                <article class="news-article">
                    <?php echo !empty($news['content']) ? $news['content'] : '<p>Содержание новости скоро будет добавлено...</p>'; ?>
                    
                    <!-- Галерея изображений -->
                    <?php if (!empty($gallery)): ?>
                    <div class="news-gallery">
                        <?php foreach ($gallery as $image): ?>
                        <div class="gallery-item">
                            <img src="<?php echo escape($image['image_url']); ?>" alt="<?php echo escape($image['alt_text'] ?? $news['title']); ?>">
                            <?php if (!empty($image['caption'])): ?>
                                <p class="gallery-caption"><?php echo escape($image['caption']); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Дополнительные материалы -->
                    <?php if (!empty($attachments)): ?>
                    <div class="news-attachments">
                        <h3 class="attachments-title">
                            <i class="fas fa-paperclip"></i> Дополнительные материалы
                        </h3>
                        
                        <?php foreach ($attachments as $attachment): ?>
                        <div class="attachment-item">
                            <div class="attachment-icon">
                                <?php if (!empty($attachment['icon_class'])): ?>
                                    <i class="<?php echo escape($attachment['icon_class']); ?>"></i>
                                <?php else: ?>
                                    <i class="fas fa-file"></i>
                                <?php endif; ?>
                            </div>
                            <div class="attachment-info">
                                <div class="attachment-name">
                                    <?php echo escape($attachment['original_name'] ?? basename($attachment['file_url'])); ?>
                                </div>
                                <?php if (!empty($attachment['file_size'])): ?>
                                <div class="attachment-size">
                                    <?php 
                                    $size = $attachment['file_size'];
                                    if ($size < 1024) {
                                        echo $size . ' Б';
                                    } elseif ($size < 1048576) {
                                        echo round($size / 1024, 2) . ' КБ';
                                    } else {
                                        echo round($size / 1048576, 2) . ' МБ';
                                    }
                                    ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <a href="<?php echo escape($attachment['file_url']); ?>" class="download-btn" download>
                                <i class="fas fa-download"></i> Скачать
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </article>
                
                <!-- Социальные кнопки -->
                <div class="news-social">
                    <h3 class="social-share-title">Поделиться новостью</h3>
                    <div class="social-share-buttons">
                        <?php
                        $currentUrl = urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
                        $title = urlencode($news['title']);
                        ?>
                        <a href="https://vk.com/share.php?url=<?php echo $currentUrl; ?>&title=<?php echo $title; ?>" 
                           target="_blank" class="social-share-btn vk">
                            <i class="fab fa-vk"></i> ВКонтакте
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $currentUrl; ?>" 
                           target="_blank" class="social-share-btn facebook">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </a>
                        <a href="https://t.me/share/url?url=<?php echo $currentUrl; ?>&text=<?php echo $title; ?>" 
                           target="_blank" class="social-share-btn telegram">
                            <i class="fab fa-telegram-plane"></i> Telegram
                        </a>
                        <a href="https://api.whatsapp.com/send?text=<?php echo $title . ' ' . $currentUrl; ?>" 
                           target="_blank" class="social-share-btn whatsapp">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                    </div>
                </div>
                
                <!-- Похожие новости -->
                <?php if (!empty($relatedNews)): ?>
                <div class="related-news">
                    <h3 class="related-news-title">Читайте также</h3>
                    <div class="related-news-grid">
                        <?php foreach ($relatedNews as $related): ?>
                        <article class="related-news-card">
                            <div class="related-news-image">
                                <?php if (!empty($related['thumbnail_url'])): ?>
                                    <img src="<?php echo escape($related['thumbnail_url']); ?>" alt="<?php echo escape($related['title']); ?>">
                                <?php else: ?>
                                    <img src="../assets/media/news/default.jpg" alt="<?php echo escape($related['title']); ?>">
                                <?php endif; ?>
                            </div>
                            <div class="related-news-content">
                                <h4 class="news-title"><?php echo escape($related['title']); ?></h4>
                                <?php if (!empty($related['excerpt'])): ?>
                                    <p class="news-excerpt"><?php echo escape(mb_substr($related['excerpt'], 0, 100)) . '...'; ?></p>
                                <?php endif; ?>
                                <a href="single.php?slug=<?php echo escape($related['slug']); ?>" class="read-more">
                                    Читать <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include "../inc/modal.php"; ?>
    <?php include "../inc/footer.php"; ?>

    <script src="../assets/js/nav.js"></script>
    <script src="../assets/js/news.js"></script>
    <script src="../assets/js/modals.js"></script>
</body>
</html>