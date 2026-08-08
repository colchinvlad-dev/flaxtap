<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

$error = '';
$success = '';

// Получаем категории
$categories_query = "SELECT * FROM news_categories WHERE is_active = 1 ORDER BY sort_order";
$categories_result = mysqli_query($conn, $categories_query);

// Получаем авторов
$authors_query = "SELECT * FROM news_authors WHERE is_active = 1 ORDER BY name";
$authors_result = mysqli_query($conn, $authors_query);

// Получаем теги
$tags_query = "SELECT * FROM news_tags WHERE is_active = 1 ORDER BY name";
$tags_result = mysqli_query($conn, $tags_query);

// Получаем все новости для связанных
$related_news_query = "SELECT id, title, status FROM news WHERE status = 'published' ORDER BY created_at DESC LIMIT 50";
$related_news_result = mysqli_query($conn, $related_news_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Основные данные
    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $slug = mysqli_real_escape_string($conn, trim($_POST['slug']));
    $excerpt = mysqli_real_escape_string($conn, trim($_POST['excerpt']));
    $content = mysqli_real_escape_string($conn, trim($_POST['content']));
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : NULL;
    $author_id = !empty($_POST['author_id']) ? (int)$_POST['author_id'] : NULL;
    
    // Статус и флаги
    $status = $_POST['status'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;
    $allow_comments = isset($_POST['allow_comments']) ? 1 : 1;
    
    // Мета-данные
    $meta_title = mysqli_real_escape_string($conn, trim($_POST['meta_title']));
    $meta_description = mysqli_real_escape_string($conn, trim($_POST['meta_description']));
    $meta_keywords = mysqli_real_escape_string($conn, trim($_POST['meta_keywords']));
    
    // Изображения
    $main_image_url = mysqli_real_escape_string($conn, trim($_POST['main_image_url']));
    $thumbnail_url = mysqli_real_escape_string($conn, trim($_POST['thumbnail_url']));
    
    // Теги и связанные новости
    $selected_tags = $_POST['tags'] ?? [];
    $selected_related = $_POST['related_news'] ?? [];
    
    // Валидация
    if (empty($title) || empty($slug)) {
        $error = 'Заполните обязательные поля: Заголовок и URL';
    } else {
        // Проверяем уникальность slug
        $check_query = "SELECT id FROM news WHERE slug = '$slug'";
        $check_result = mysqli_query($conn, $check_query);
        if (mysqli_num_rows($check_result) > 0) {
            $error = 'Новость с таким URL уже существует';
        } else {
            // Начинаем транзакцию
            mysqli_begin_transaction($conn);
            
            try {
                // Вставляем новость
                $current_user_id = $_SESSION['admin_id'];
                $published_at = $status == 'published' ? date('Y-m-d H:i:s') : NULL;
                
                $insert_query = "INSERT INTO news (
                    title, slug, excerpt, content, category_id, author_id,
                    status, is_featured, is_pinned, allow_comments,
                    meta_title, meta_description, meta_keywords,
                    main_image_url, thumbnail_url,
                    created_by, updated_by, created_at, updated_at, published_at
                ) VALUES (
                    '$title',
                    '$slug',
                    '$excerpt',
                    '$content',
                    " . ($category_id ? $category_id : "NULL") . ",
                    " . ($author_id ? $author_id : "NULL") . ",
                    '$status',
                    $is_featured,
                    $is_pinned,
                    $allow_comments,
                    '$meta_title',
                    '$meta_description',
                    '$meta_keywords',
                    '$main_image_url',
                    '$thumbnail_url',
                    $current_user_id,
                    $current_user_id,
                    NOW(),
                    NOW(),
                    " . ($published_at ? "'$published_at'" : "NULL") . "
                )";
                
                if (!mysqli_query($conn, $insert_query)) {
                    throw new Exception('Ошибка при создании новости: ' . mysqli_error($conn));
                }
                
                $news_id = mysqli_insert_id($conn);
                
                // Добавляем теги
                foreach ($selected_tags as $tag_id) {
                    $tag_id = (int)$tag_id;
                    $insert_tag = "INSERT INTO news_news_tags (news_id, tag_id) VALUES ($news_id, $tag_id)";
                    
                    if (!mysqli_query($conn, $insert_tag)) {
                        throw new Exception('Ошибка при добавлении тега: ' . mysqli_error($conn));
                    }
                }
                
                // Добавляем связанные новости
                foreach ($selected_related as $related_id) {
                    $related_id = (int)$related_id;
                    if ($related_id != $news_id) {
                        $insert_related = "INSERT INTO news_related (news_id, related_news_id) VALUES ($news_id, $related_id)";
                        
                        if (!mysqli_query($conn, $insert_related)) {
                            throw new Exception('Ошибка при добавлении связанной новости: ' . mysqli_error($conn));
                        }
                    }
                }
                
                // Фиксируем транзакцию
                mysqli_commit($conn);
                
                // Логируем действие
                $admin_name = $_SESSION['user_name'] ?? $_SESSION['admin_name'] ?? 'Неизвестный';
                $log_message = date('Y-m-d H:i:s') . " - Администратор {$admin_name} создал новость: {$title} (ID: $news_id)\n";
                file_put_contents('../admin_log.txt', $log_message, FILE_APPEND);
                
                $success = 'Новость успешно создана!';
                
                // Редирект на редактирование
                $_SESSION['success'] = $success;
                header("Location: edit.php?id=$news_id");
                exit();
                
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание новости - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style/news/create.css">
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Создание новости</h1>
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Назад к списку
            </a>
        </div>
        
        <div class="create-container">
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="newsForm" enctype="multipart/form-data">
                <!-- Основная информация -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-info-circle"></i>
                        <h2>Основная информация</h2>
                    </div>
                    
                    <div class="form-group">
                        <label for="title" class="form-label required">Заголовок новости</label>
                        <input type="text" id="title" name="title" class="form-control" required
                               maxlength="200" placeholder="Введите заголовок новости">
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="slug" class="form-label required">URL (slug)</label>
                            <input type="text" id="slug" name="slug" class="form-control" required
                                   placeholder="Пример: novaya-kollektsiya-kosmeticheskikh-sredstv">
                            <div class="slug-preview">
                                Ссылка: <a href="#" id="slugPreview" target="_blank"></a>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id" class="form-label">Категория</label>
                            <select id="category_id" name="category_id" class="form-control">
                                <option value="">Без категории</option>
                                <?php while($category = mysqli_fetch_assoc($categories_result)): ?>
                                    <option value="<?php echo $category['id']; ?>">
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
                                    <option value="<?php echo $author['id']; ?>">
                                        <?php echo htmlspecialchars($author['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="reading_time_minutes" class="form-label">Время чтения (минут)</label>
                            <input type="number" id="reading_time_minutes" name="reading_time_minutes" 
                                   class="form-control" min="1" max="60" value="5">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="excerpt" class="form-label">Краткое описание</label>
                        <textarea id="excerpt" name="excerpt" class="form-control" rows="3"
                                  placeholder="Краткое описание новости для превью"></textarea>
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
                            <div id="contentEditor"></div>
                        </div>
                        <textarea id="content" name="content" style="display: none;"></textarea>
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
                                   placeholder="URL главного изображения">
                            <div class="image-preview">
                                <div class="image-preview-item" id="mainImagePreview">
                                    <p>Предпросмотр</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="thumbnail_url" class="form-label">Миниатюра</label>
                            <input type="text" id="thumbnail_url" name="thumbnail_url" class="form-control"
                                   placeholder="URL миниатюры (необязательно)">
                            <div class="image-preview">
                                <div class="image-preview-item" id="thumbnailPreview">
                                    <p>Предпросмотр</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Теги и категории -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-tags"></i>
                        <h2>Теги и категории</h2>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Теги</label>
                        <div class="tags-container">
                            <?php mysqli_data_seek($tags_result, 0); ?>
                            <?php while($tag = mysqli_fetch_assoc($tags_result)): ?>
                                <label class="tag-item">
                                    <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>">
                                    <?php echo htmlspecialchars($tag['name']); ?>
                                </label>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Связанные новости</label>
                        <div class="related-news-list">
                            <?php mysqli_data_seek($related_news_result, 0); ?>
                            <?php while($related = mysqli_fetch_assoc($related_news_result)): ?>
                                <div class="related-item">
                                    <input type="checkbox" name="related_news[]" value="<?php echo $related['id']; ?>"
                                           id="related_<?php echo $related['id']; ?>">
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
                                <option value="draft">Черновик</option>
                                <option value="published">Опубликовано</option>
                                <option value="archived">Архив</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="published_at" class="form-label">Дата публикации</label>
                            <input type="datetime-local" id="published_at" name="published_at" class="form-control"
                                   value="<?php echo date('Y-m-d\TH:i'); ?>">
                        </div>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_featured" name="is_featured" value="1">
                        <label for="is_featured">Сделать избранной</label>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_pinned" name="is_pinned" value="1">
                        <label for="is_pinned">Закрепить вверху списка</label>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="allow_comments" name="allow_comments" value="1" checked>
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
                               maxlength="200" placeholder="Заголовок для поисковых систем">
                    </div>
                    
                    <div class="form-group">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea id="meta_description" name="meta_description" class="form-control" rows="3"
                                  maxlength="500" placeholder="Описание для поисковых систем"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="meta_keywords" class="form-label">Meta Keywords</label>
                        <input type="text" id="meta_keywords" name="meta_keywords" class="form-control"
                               placeholder="Ключевые слова через запятую">
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Создать новость
                    </button>
                    <button type="button" onclick="saveDraft()" class="btn btn-outline">
                        <i class="fas fa-save"></i> Сохранить черновик
                    </button>
                </div>
            </form>
        </div>
    </main>
    
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="../assets/js/news/create.js"></script>
</body>
</html>