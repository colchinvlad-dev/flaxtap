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
          LEFT JOIN users u2 ON n.updated_by = u2.id
          WHERE n.id = $news_id";

$result = mysqli_query($conn, $query);
$news = mysqli_fetch_assoc($result);

if (!$news) {
    header("Location: index.php");
    exit();
}

// Получаем выбранные теги
$tags_query = "SELECT nt.* 
               FROM news_tags nt
               INNER JOIN news_news_tags nnt ON nt.id = nnt.tag_id
               WHERE nnt.news_id = $news_id";
$tags_result = mysqli_query($conn, $tags_query);
$selected_tags = [];
while($tag = mysqli_fetch_assoc($tags_result)) {
    $selected_tags[] = $tag['id'];
}

// Получаем связанные новости
$related_query = "SELECT nr.related_news_id 
                  FROM news_related nr
                  WHERE nr.news_id = $news_id";
$related_result = mysqli_query($conn, $related_query);
$selected_related = [];
while($related = mysqli_fetch_assoc($related_result)) {
    $selected_related[] = $related['related_news_id'];
}

// Получаем все категории, авторы, теги и новости
$categories_query = "SELECT * FROM news_categories WHERE is_active = 1 ORDER BY sort_order";
$categories_result = mysqli_query($conn, $categories_query);

$authors_query = "SELECT * FROM news_authors WHERE is_active = 1 ORDER BY name";
$authors_result = mysqli_query($conn, $authors_query);

$all_tags_query = "SELECT * FROM news_tags WHERE is_active = 1 ORDER BY name";
$all_tags_result = mysqli_query($conn, $all_tags_query);

$all_news_query = "SELECT id, title, status FROM news WHERE status = 'published' AND id != $news_id ORDER BY created_at DESC LIMIT 50";
$all_news_result = mysqli_query($conn, $all_news_query);

// Обработка формы
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $slug = mysqli_real_escape_string($conn, $_POST['slug']);
    $excerpt = mysqli_real_escape_string($conn, $_POST['excerpt']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : NULL;
    $author_id = !empty($_POST['author_id']) ? (int)$_POST['author_id'] : NULL;
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;
    $allow_comments = isset($_POST['allow_comments']) ? 1 : 0;
    $reading_time_minutes = (int)$_POST['reading_time_minutes'];
    $main_image_url = mysqli_real_escape_string($conn, $_POST['main_image_url']);
    $thumbnail_url = mysqli_real_escape_string($conn, $_POST['thumbnail_url']);
    $meta_title = mysqli_real_escape_string($conn, $_POST['meta_title']);
    $meta_description = mysqli_real_escape_string($conn, $_POST['meta_description']);
    $meta_keywords = mysqli_real_escape_string($conn, $_POST['meta_keywords']);
    $selected_tags = $_POST['tags'] ?? [];
    $selected_related = $_POST['related_news'] ?? [];
    
    // Текущий ID пользователя
    $current_user_id = $_SESSION['admin_id'] ?? 0;
    
    // Подготавливаем запрос на обновление
    $update_query = "UPDATE news SET 
        title = '$title',
        slug = '$slug',
        excerpt = '$excerpt',
        content = '$content',
        category_id = " . ($category_id ? $category_id : "NULL") . ",
        author_id = " . ($author_id ? $author_id : "NULL") . ",
        status = '$status',
        is_featured = $is_featured,
        is_pinned = $is_pinned,
        allow_comments = $allow_comments,
        reading_time_minutes = $reading_time_minutes,
        main_image_url = '$main_image_url',
        thumbnail_url = '$thumbnail_url',
        meta_title = '$meta_title',
        meta_description = '$meta_description',
        meta_keywords = '$meta_keywords',
        updated_by = $current_user_id,
        updated_at = NOW()";
    
    // Если новость публикуется впервые, устанавливаем дату публикации
    if ($status == 'published' && !$news['published_at']) {
        $update_query .= ", published_at = NOW()";
    }
    
    $update_query .= " WHERE id = $news_id";
    
    // Обновляем новость
    if (mysqli_query($conn, $update_query)) {
        // Обработка тегов
        // Удаляем старые теги
        mysqli_query($conn, "DELETE FROM news_news_tags WHERE news_id = $news_id");
        
        // Добавляем новые теги
        foreach ($selected_tags as $tag_id) {
            $tag_id = (int)$tag_id;
            mysqli_query($conn, "INSERT INTO news_news_tags (news_id, tag_id) VALUES ($news_id, $tag_id)");
        }
        
        // Обработка связанных новостей
        // Удаляем старые связи
        mysqli_query($conn, "DELETE FROM news_related WHERE news_id = $news_id");
        
        // Добавляем новые связи
        foreach ($selected_related as $related_id) {
            $related_id = (int)$related_id;
            if ($related_id != $news_id) {
                mysqli_query($conn, "INSERT INTO news_related (news_id, related_news_id) VALUES ($news_id, $related_id)");
            }
        }
        
        $success = 'Новость успешно обновлена';
        
        // Обновляем данные новости для отображения
        $query = "SELECT n.*, 
                  nc.name as category_name,
                  na.name as author_name,
                  u1.name as created_by_name,
                  u2.name as updated_by_name
                  FROM news n
                  LEFT JOIN news_categories nc ON n.category_id = nc.id
                  LEFT JOIN news_authors na ON n.author_id = na.id
                  LEFT JOIN users u1 ON n.created_by = u1.id
                  LEFT JOIN users u2 ON n.updated_by = u2.id
                  WHERE n.id = $news_id";
        $result = mysqli_query($conn, $query);
        $news = mysqli_fetch_assoc($result);
        
        // Обновляем выбранные теги
        $tags_query = "SELECT nt.* 
                       FROM news_tags nt
                       INNER JOIN news_news_tags nnt ON nt.id = nnt.tag_id
                       WHERE nnt.news_id = $news_id";
        $tags_result = mysqli_query($conn, $tags_query);
        $selected_tags = [];
        while($tag = mysqli_fetch_assoc($tags_result)) {
            $selected_tags[] = $tag['id'];
        }
        
        // Обновляем связанные новости
        $related_query = "SELECT nr.related_news_id 
                          FROM news_related nr
                          WHERE nr.news_id = $news_id";
        $related_result = mysqli_query($conn, $related_query);
        $selected_related = [];
        while($related = mysqli_fetch_assoc($related_result)) {
            $selected_related[] = $related['related_news_id'];
        }
        
        // Сбрасываем результаты запросов
        mysqli_data_seek($categories_result, 0);
        mysqli_data_seek($authors_result, 0);
        mysqli_data_seek($all_tags_result, 0);
        mysqli_data_seek($all_news_result, 0);
        
    } else {
        $error = 'Ошибка при обновлении новости: ' . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование новости - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style/news/edit.css">
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Редактирование новости</h1>
            <div>
                <a href="view.php?id=<?php echo $news_id; ?>" class="btn btn-outline">
                    <i class="fas fa-eye"></i> Просмотр
                </a>
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Назад
                </a>
            </div>
        </div>
        
        <div class="news-status">
            <div class="status-info">
                <span>Создана: <?php echo date('d.m.Y H:i', strtotime($news['created_at'])); ?></span>
                <span>Обновлена: <?php echo date('d.m.Y H:i', strtotime($news['updated_at'])); ?></span>
                <span>Просмотры: <?php echo $news['views_count']; ?></span>
                <span>Комментарии: <?php echo $news['comments_count']; ?></span>
                <span>Репосты: <?php echo $news['shares_count']; ?></span>
                <?php if ($news['published_at']): ?>
                    <span>Опубликована: <?php echo date('d.m.Y H:i', strtotime($news['published_at'])); ?></span>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>
        
        <div class="edit-container">
            <form method="POST" id="newsForm">
                <!-- Основная информация -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-info-circle"></i>
                        <h2>Основная информация</h2>
                    </div>
                    
                    <div class="form-group">
                        <label for="title" class="form-label required">Заголовок новости</label>
                        <input type="text" id="title" name="title" class="form-control" required
                               value="<?php echo htmlspecialchars($news['title']); ?>">
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="slug" class="form-label required">URL (slug)</label>
                            <input type="text" id="slug" name="slug" class="form-control" required
                                   value="<?php echo htmlspecialchars($news['slug']); ?>">
                            <div class="slug-preview">
                                Ссылка: <a href="/news/<?php echo htmlspecialchars($news['slug']); ?>" id="slugPreview" target="_blank">
                                    /news/<?php echo htmlspecialchars($news['slug']); ?>
                                </a>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id" class="form-label">Категория</label>
                            <select id="category_id" name="category_id" class="form-control">
                                <option value="">Без категории</option>
                                <?php mysqli_data_seek($categories_result, 0); ?>
                                <?php while($category = mysqli_fetch_assoc($categories_result)): ?>
                                    <option value="<?php echo $category['id']; ?>"
                                        <?php echo $news['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="author_id" class="form-label">Автор</label>
                            <select id="author_id" name="author_id" class="form-control">
                                <option value="">Не указан</option>
                                <?php mysqli_data_seek($authors_result, 0); ?>
                                <?php while($author = mysqli_fetch_assoc($authors_result)): ?>
                                    <option value="<?php echo $author['id']; ?>"
                                        <?php echo $news['author_id'] == $author['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($author['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="reading_time_minutes" class="form-label">Время чтения (минут)</label>
                            <input type="number" id="reading_time_minutes" name="reading_time_minutes" 
                                   class="form-control" min="1" max="60" 
                                   value="<?php echo $news['reading_time_minutes']; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="excerpt" class="form-label">Краткое описание</label>
                        <textarea id="excerpt" name="excerpt" class="form-control" rows="3"><?php echo htmlspecialchars($news['excerpt']); ?></textarea>
                    </div>
                </div>
                
                <!-- Содержимое новости -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-file-alt"></i>
                        <h2>Содержимое новости</h2>
                    </div>
                    
                    <div class="form-group">
                        <label for="content" class="form-label required">Текст новости</label>
                        <div class="editor-container">
                            <div id="contentEditor"><?php echo $news['content']; ?></div>
                        </div>
                        <textarea id="content" name="content" style="display: none;"><?php echo htmlspecialchars($news['content']); ?></textarea>
                    </div>
                </div>
                
                <!-- Изображения -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-images"></i>
                        <h2>Изображения</h2>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="main_image_url" class="form-label">Главное изображение</label>
                            <input type="text" id="main_image_url" name="main_image_url" class="form-control"
                                   value="<?php echo htmlspecialchars($news['main_image_url']); ?>">
                            <div class="image-preview">
                                <div class="image-preview-item" id="mainImagePreview">
                                    <?php if ($news['main_image_url']): ?>
                                        <img src="<?php echo htmlspecialchars($news['main_image_url']); ?>" alt="Preview">
                                        <p><?php echo basename($news['main_image_url']); ?></p>
                                    <?php else: ?>
                                        <p>Предпросмотр</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="thumbnail_url" class="form-label">Миниатюра</label>
                            <input type="text" id="thumbnail_url" name="thumbnail_url" class="form-control"
                                   value="<?php echo htmlspecialchars($news['thumbnail_url']); ?>">
                            <div class="image-preview">
                                <div class="image-preview-item" id="thumbnailPreview">
                                    <?php if ($news['thumbnail_url']): ?>
                                        <img src="<?php echo htmlspecialchars($news['thumbnail_url']); ?>" alt="Preview">
                                        <p><?php echo basename($news['thumbnail_url']); ?></p>
                                    <?php else: ?>
                                        <p>Предпросмотр</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Теги и связанные новости -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-tags"></i>
                        <h2>Теги и связанные новости</h2>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Теги</label>
                        <div class="tags-container">
                            <?php mysqli_data_seek($all_tags_result, 0); ?>
                            <?php while($tag = mysqli_fetch_assoc($all_tags_result)): ?>
                                <label class="tag-item <?php echo in_array($tag['id'], $selected_tags) ? 'selected' : ''; ?>">
                                    <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>"
                                           <?php echo in_array($tag['id'], $selected_tags) ? 'checked' : ''; ?>>
                                    <?php echo htmlspecialchars($tag['name']); ?>
                                </label>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Связанные новости</label>
                        <div class="related-news-list">
                            <?php mysqli_data_seek($all_news_result, 0); ?>
                            <?php while($related = mysqli_fetch_assoc($all_news_result)): ?>
                                <div class="related-item">
                                    <input type="checkbox" name="related_news[]" value="<?php echo $related['id']; ?>"
                                           id="related_<?php echo $related['id']; ?>"
                                           <?php echo in_array($related['id'], $selected_related) ? 'checked' : ''; ?>>
                                    <label for="related_<?php echo $related['id']; ?>" style="cursor: pointer; flex: 1;">
                                        <?php echo htmlspecialchars($related['title']); ?>
                                        <span style="color: var(--gray-color); font-size: 12px;">
                                            (<?php echo $related['status']; ?>)
                                        </span>
                                    </label>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Публикация и настройки -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-cog"></i>
                        <h2>Публикация и настройки</h2>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="status" class="form-label required">Статус</label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="draft" <?php echo $news['status'] == 'draft' ? 'selected' : ''; ?>>Черновик</option>
                                <option value="published" <?php echo $news['status'] == 'published' ? 'selected' : ''; ?>>Опубликовано</option>
                                <option value="archived" <?php echo $news['status'] == 'archived' ? 'selected' : ''; ?>>Архив</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="published_at" class="form-label">Дата публикации</label>
                            <input type="datetime-local" id="published_at" name="published_at" class="form-control"
                                   value="<?php echo $news['published_at'] ? date('Y-m-d\TH:i', strtotime($news['published_at'])) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_featured" name="is_featured" value="1"
                               <?php echo $news['is_featured'] ? 'checked' : ''; ?>>
                        <label for="is_featured">Избранная новость</label>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_pinned" name="is_pinned" value="1"
                               <?php echo $news['is_pinned'] ? 'checked' : ''; ?>>
                        <label for="is_pinned">Закрепить вверху списка</label>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="allow_comments" name="allow_comments" value="1"
                               <?php echo $news['allow_comments'] ? 'checked' : ''; ?>>
                        <label for="allow_comments">Разрешить комментарии</label>
                    </div>
                </div>
                
                <!-- SEO оптимизация -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-search"></i>
                        <h2>SEO оптимизация</h2>
                    </div>
                    
                    <div class="form-group">
                        <label for="meta_title" class="form-label">Meta Title</label>
                        <input type="text" id="meta_title" name="meta_title" class="form-control"
                               value="<?php echo htmlspecialchars($news['meta_title']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea id="meta_description" name="meta_description" class="form-control" rows="3"><?php echo htmlspecialchars($news['meta_description']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="meta_keywords" class="form-label">Meta Keywords</label>
                        <input type="text" id="meta_keywords" name="meta_keywords" class="form-control"
                               value="<?php echo htmlspecialchars($news['meta_keywords'] ??''); ?>">
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Сохранить изменения
                    </button>
                    <a href="index.php" class="btn btn-outline">
                        <i class="fas fa-times"></i> Отмена
                    </a>
                </div>
            </form>
        </div>
    </main>
    
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="../assets/js/news/edit.js"></script>
</body>
</html>