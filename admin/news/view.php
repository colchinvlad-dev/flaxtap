<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

$news_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$news_id) {
    header("Location: index.php");
    exit();
}

// Получаем данные новости
$query = "SELECT n.*, 
          nc.name as category_name,
          na.name as author_name,
          u1.name as created_by_name,
          u2.name as updated_by_name
          FROM news n
          LEFT JOIN news_categories nc ON n.category_id = nc.id
          LEFT JOIN news_authors na ON n.author_id = na.id
          LEFT JOIN users u1 ON n.created_by = u1.id
          LEFT JOIN users u2 ON n.updated_by = n.updated_by
          WHERE n.id = $news_id";
$result = mysqli_query($conn, $query);
$news = mysqli_fetch_assoc($result);

if (!$news) {
    header("Location: index.php");
    exit();
}

// Получаем теги новости
$tags_query = "SELECT nt.* 
               FROM news_tags nt
               INNER JOIN news_news_tags nnt ON nt.id = nnt.tag_id
               WHERE nnt.news_id = $news_id
               ORDER BY nt.name";
$tags_result = mysqli_query($conn, $tags_query);

// Получаем связанные новости
$related_query = "SELECT n2.* 
                  FROM news_related nr
                  INNER JOIN news n2 ON nr.related_news_id = n2.id
                  WHERE nr.news_id = $news_id AND n2.status = 'published'
                  ORDER BY n2.published_at DESC LIMIT 5";
$related_result = mysqli_query($conn, $related_query);

// Получаем комментарии
$comments_query = "SELECT nc.* 
                   FROM news_comments nc
                   WHERE nc.news_id = $news_id AND nc.is_approved = 1
                   ORDER BY nc.created_at DESC LIMIT 10";
$comments_result = mysqli_query($conn, $comments_query);

// Получаем вложения
$attachments_query = "SELECT * FROM news_attachments WHERE news_id = $news_id ORDER BY sort_order";
$attachments_result = mysqli_query($conn, $attachments_query);

// Получаем галерею
$gallery_query = "SELECT * FROM news_gallery WHERE news_id = $news_id ORDER BY sort_order";
$gallery_result = mysqli_query($conn, $gallery_query);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр новости - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style/news/view.css">
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Просмотр новости</h1>
            <div>
                <a href="edit.php?id=<?php echo $news_id; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Редактировать
                </a>
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Назад
                </a>
            </div>
        </div>
        
        <div class="view-container">
            <!-- Заголовок новости -->
            <div class="news-header">
                <div class="header-top">
                    <div>
                        <h1 class="news-title"><?php echo htmlspecialchars($news['title']); ?></h1>
                        <div class="news-meta">
                            <div class="meta-item">
                                <i class="fas fa-tag"></i>
                                <span><?php echo htmlspecialchars($news['category_name'] ?? 'Без категории'); ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-user"></i>
                                <span><?php echo htmlspecialchars($news['author_name'] ?? 'Не указан'); ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-eye"></i>
                                <span><?php echo $news['views_count']; ?> просмотров</span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-comment"></i>
                                <span><?php echo $news['comments_count']; ?> комментариев</span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-share"></i>
                                <span><?php echo $news['shares_count']; ?> репостов</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <?php 
                        $status_class = 'badge-' . $news['status'];
                        $status_text = '';
                        switch($news['status']) {
                            case 'published': $status_text = 'Опубликовано'; break;
                            case 'draft': $status_text = 'Черновик'; break;
                            case 'archived': $status_text = 'Архив'; break;
                        }
                        ?>
                        <div class="status-badge-large <?php echo $status_class; ?>">
                            <?php echo $status_text; ?>
                        </div>
                    </div>
                </div>
                
                <div class="header-actions">
                    <?php if ($news['status'] == 'published'): ?>
                        <a href="/news/<?php echo $news['slug']; ?>" target="_blank" class="btn btn-outline">
                            <i class="fas fa-external-link-alt"></i> Перейти на сайт
                        </a>
                        <button onclick="copyLink()" class="btn btn-outline">
                            <i class="fas fa-copy"></i> Скопировать ссылку
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Вкладки -->
            <div class="tabs">
                <a href="?id=<?php echo $news_id; ?>&tab=content" 
                   class="tab active">
                    <i class="fas fa-file-alt"></i> Содержимое
                </a>
                <a href="?id=<?php echo $news_id; ?>&tab=meta" 
                   class="tab">
                    <i class="fas fa-search"></i> SEO
                </a>
                <a href="?id=<?php echo $news_id; ?>&tab=gallery" 
                   class="tab">
                    <i class="fas fa-images"></i> Галерея
                    <?php if (mysqli_num_rows($gallery_result) > 0): ?>
                        <span style="background: var(--primary-color); color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; margin-left: 5px;">
                            <?php echo mysqli_num_rows($gallery_result); ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="?id=<?php echo $news_id; ?>&tab=attachments" 
                   class="tab">
                    <i class="fas fa-paperclip"></i> Вложения
                    <?php if (mysqli_num_rows($attachments_result) > 0): ?>
                        <span style="background: var(--primary-color); color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; margin-left: 5px;">
                            <?php echo mysqli_num_rows($attachments_result); ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="?id=<?php echo $news_id; ?>&tab=related" 
                   class="tab">
                    <i class="fas fa-link"></i> Связанные
                    <?php if (mysqli_num_rows($related_result) > 0): ?>
                        <span style="background: var(--primary-color); color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; margin-left: 5px;">
                            <?php echo mysqli_num_rows($related_result); ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="?id=<?php echo $news_id; ?>&tab=comments" 
                   class="tab">
                    <i class="fas fa-comments"></i> Комментарии
                    <?php if ($news['comments_count'] > 0): ?>
                        <span style="background: var(--primary-color); color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; margin-left: 5px;">
                            <?php echo $news['comments_count']; ?>
                        </span>
                    <?php endif; ?>
                </a>
            </div>
            
            <!-- Содержимое вкладок -->
            <div id="contentTab" class="tab-content active">
                <!-- Краткое описание -->
                <?php if ($news['excerpt']): ?>
                    <div class="details-card">
                        <h3 class="section-title">Краткое описание</h3>
                        <div class="content-box">
                            <?php echo nl2br(htmlspecialchars($news['excerpt'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Основной текст -->
                <div class="details-card">
                    <h3 class="section-title">Текст новости</h3>
                    <div class="content-box">
                        <?php echo $news['content']; ?>
                    </div>
                </div>
                
                <!-- Главное изображение -->
                <?php if ($news['main_image_url']): ?>
                    <div class="details-card">
                        <h3 class="section-title">Главное изображение</h3>
                        <div style="text-align: center;">
                            <img src="<?php echo htmlspecialchars($news['main_image_url']); ?>" 
                                 alt="Главное изображение" style="max-width: 100%; max-height: 400px; border-radius: 8px;">
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Теги -->
                <?php if (mysqli_num_rows($tags_result) > 0): ?>
                    <div class="details-card">
                        <h3 class="section-title">Теги</h3>
                        <div class="tags-list">
                            <?php mysqli_data_seek($tags_result, 0); ?>
                            <?php while($tag = mysqli_fetch_assoc($tags_result)): ?>
                                <div class="tag-item">
                                    <?php echo htmlspecialchars($tag['name']); ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Вкладка SEO -->
            <div id="metaTab" class="tab-content">
                <div class="details-card">
                    <h3 class="section-title">SEO информация</h3>
                    <div class="content-box">
                        <h4>Meta Title</h4>
                        <p><?php echo htmlspecialchars($news['meta_title']); ?></p>
                        
                        <h4>Meta Description</h4>
                        <p><?php echo htmlspecialchars($news['meta_description']); ?></p>
                        
                        <h4>Meta Keywords</h4>
                        <p><?php echo htmlspecialchars($news['meta_keywords'] ??''); ?></p>
                        
                        <h4>URL (slug)</h4>
                        <p>/news/<?php echo htmlspecialchars($news['slug']); ?></p>
                    </div>
                </div>
                
                <div class="details-card">
                    <h3 class="section-title">Настройки публикации</h3>
                    <div class="content-box">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <h4>Статус</h4>
                                <p>
                                    <?php 
                                    switch($news['status']) {
                                        case 'published': echo 'Опубликовано'; break;
                                        case 'draft': echo 'Черновик'; break;
                                        case 'archived': echo 'Архив'; break;
                                    }
                                    ?>
                                </p>
                            </div>
                            <div>
                                <h4>Избранная</h4>
                                <p><?php echo $news['is_featured'] ? 'Да' : 'Нет'; ?></p>
                            </div>
                            <div>
                                <h4>Закреплена</h4>
                                <p><?php echo $news['is_pinned'] ? 'Да' : 'Нет'; ?></p>
                            </div>
                            <div>
                                <h4>Комментарии</h4>
                                <p><?php echo $news['allow_comments'] ? 'Разрешены' : 'Запрещены'; ?></p>
                            </div>
                            <div>
                                <h4>Время чтения</h4>
                                <p><?php echo $news['reading_time_minutes']; ?> минут</p>
                            </div>
                            <div>
                                <h4>Дата публикации</h4>
                                <p>
                                    <?php echo $news['published_at'] ? date('d.m.Y H:i', strtotime($news['published_at'])) : 'Не опубликовано'; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Вкладка галереи -->
            <div id="galleryTab" class="tab-content">
                <?php if (mysqli_num_rows($gallery_result) > 0): ?>
                    <div class="details-card">
                        <h3 class="section-title">Галерея изображений</h3>
                        <div class="gallery-grid">
                            <?php mysqli_data_seek($gallery_result, 0); ?>
                            <?php while($image = mysqli_fetch_assoc($gallery_result)): ?>
                                <div class="gallery-item">
                                    <img src="<?php echo htmlspecialchars($image['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($image['alt_text']); ?>">
                                    <?php if ($image['caption']): ?>
                                        <p><?php echo htmlspecialchars($image['caption']); ?></p>
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
            
            <!-- Вкладка вложений -->
            <div id="attachmentsTab" class="tab-content">
                <?php if (mysqli_num_rows($attachments_result) > 0): ?>
                    <div class="details-card">
                        <h3 class="section-title">Вложения</h3>
                        <div class="attachments-list">
                            <?php mysqli_data_seek($attachments_result, 0); ?>
                            <?php while($attachment = mysqli_fetch_assoc($attachments_result)): ?>
                                <div class="attachment-item">
                                    <div class="attachment-icon">
                                        <?php 
                                        $file_ext = pathinfo($attachment['file_url'], PATHINFO_EXTENSION);
                                        switch(strtolower($file_ext)) {
                                            case 'pdf': echo '<i class="fas fa-file-pdf"></i>'; break;
                                            case 'doc': case 'docx': echo '<i class="fas fa-file-word"></i>'; break;
                                            case 'xls': case 'xlsx': echo '<i class="fas fa-file-excel"></i>'; break;
                                            case 'ppt': case 'pptx': echo '<i class="fas fa-file-powerpoint"></i>'; break;
                                            case 'jpg': case 'jpeg': case 'png': case 'gif': echo '<i class="fas fa-file-image"></i>'; break;
                                            case 'zip': case 'rar': echo '<i class="fas fa-file-archive"></i>'; break;
                                            default: echo '<i class="fas fa-file"></i>';
                                        }
                                        ?>
                                    </div>
                                    <div class="attachment-info">
                                        <div class="attachment-name">
                                            <?php echo htmlspecialchars($attachment['original_name']); ?>
                                        </div>
                                        <div class="attachment-size">
                                            <?php echo formatFileSize($attachment['file_size']); ?>
                                        </div>
                                    </div>
                                    <a href="<?php echo htmlspecialchars($attachment['file_url']); ?>" 
                                       target="_blank" class="btn btn-sm btn-outline">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-paperclip"></i>
                        <p>Вложений нет</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Вкладка связанных новостей -->
            <div id="relatedTab" class="tab-content">
                <?php if (mysqli_num_rows($related_result) > 0): ?>
                    <div class="details-card">
                        <h3 class="section-title">Связанные новости</h3>
                        <div class="related-news-grid">
                            <?php mysqli_data_seek($related_result, 0); ?>
                            <?php while($related = mysqli_fetch_assoc($related_result)): ?>
                                <div class="related-item">
                                    <div class="related-title">
                                        <a href="view.php?id=<?php echo $related['id']; ?>" 
                                           style="color: var(--primary-color); text-decoration: none;">
                                            <?php echo htmlspecialchars($related['title']); ?>
                                        </a>
                                    </div>
                                    <div class="related-date">
                                        <?php echo date('d.m.Y', strtotime($related['created_at'])); ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-link"></i>
                        <p>Связанных новостей нет</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Вкладка комментариев -->
            <div id="commentsTab" class="tab-content">
                <?php if (mysqli_num_rows($comments_result) > 0): ?>
                    <div class="details-card">
                        <h3 class="section-title">Комментарии (последние 10)</h3>
                        <div class="comments-list">
                            <?php mysqli_data_seek($comments_result, 0); ?>
                            <?php while($comment = mysqli_fetch_assoc($comments_result)): ?>
                                <div class="comment-item">
                                    <div class="comment-header">
                                        <div class="comment-author">
                                            <?php echo htmlspecialchars($comment['author_name']); ?>
                                        </div>
                                        <div class="comment-date">
                                            <?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?>
                                        </div>
                                    </div>
                                    <div class="comment-content">
                                        <?php echo htmlspecialchars($comment['content']); ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-comments"></i>
                        <p>Комментариев нет</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <script src="../assets/js/news/view.js"></script>
</body>
</html>
<?php
// Функция для форматирования размера файла
function formatFileSize($bytes) {
    if ($bytes == 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return number_format($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
?>