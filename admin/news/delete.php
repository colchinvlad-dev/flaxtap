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

// Получаем информацию о новости
$query = "SELECT n.* FROM news n WHERE n.id = $news_id";
$result = mysqli_query($conn, $query);
$news = mysqli_fetch_assoc($result);

if (!$news) {
    header("Location: index.php");
    exit();
}

// Обработка удаления
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm = isset($_POST['confirm']) ? $_POST['confirm'] : '';
    
    if ($confirm === 'yes') {
        // Начинаем транзакцию
        mysqli_begin_transaction($conn);
        
        try {
            // Удаляем связанные записи
            $queries = [
                "DELETE FROM news_news_tags WHERE news_id = $news_id",
                "DELETE FROM news_related WHERE news_id = $news_id OR related_news_id = $news_id",
                "DELETE FROM news_comments WHERE news_id = $news_id",
                "DELETE FROM news_gallery WHERE news_id = $news_id",
                "DELETE FROM news_attachments WHERE news_id = $news_id",
                "DELETE FROM news WHERE id = $news_id"
            ];
            
            foreach ($queries as $query) {
                if (!mysqli_query($conn, $query)) {
                    throw new Exception(mysqli_error($conn));
                }
            }
            
            // Фиксируем транзакцию
            mysqli_commit($conn);
            
            // Логируем действие
            $admin_name = $_SESSION['user_name'] ?? $_SESSION['admin_name'] ?? 'Неизвестный';
            $log_message = date('Y-m-d H:i:s') . " - Администратор {$admin_name} удалил новость: {$news['title']} (ID: {$news['id']})\n";
            file_put_contents('../admin_log.txt', $log_message, FILE_APPEND);
            
            $_SESSION['success'] = "Новость успешно удалена";
            header("Location: index.php");
            exit();
            
        } catch (Exception $e) {
            // Откатываем транзакцию при ошибке
            mysqli_rollback($conn);
            $error = 'Ошибка при удалении новости: ' . $e->getMessage();
        }
    } else {
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удаление новости - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style/news/delete.css">
</head>
<body>
    <div class="delete-container">
        <div class="warning-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        
        <h1>Подтверждение удаления</h1>
        
        <div class="news-info">
            <div class="info-row">
                <span class="info-label">Заголовок новости:</span>
                <span class="info-value"><?php echo htmlspecialchars($news['title']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Статус:</span>
                <span class="info-value">
                    <?php 
                    switch($news['status']) {
                        case 'published': echo 'Опубликовано'; break;
                        case 'draft': echo 'Черновик'; break;
                        case 'archived': echo 'Архив'; break;
                    }
                    ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Дата создания:</span>
                <span class="info-value"><?php echo date('d.m.Y H:i', strtotime($news['created_at'])); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Просмотры:</span>
                <span class="info-value"><?php echo $news['views_count']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Комментарии:</span>
                <span class="info-value"><?php echo $news['comments_count']; ?></span>
            </div>
        </div>
        
        <div class="danger-alert">
            <i class="fas fa-exclamation-circle"></i>
            <strong>Внимание!</strong> Это действие нельзя отменить. Все данные новости будут удалены безвозвратно.
            <br><br>
            <small>Будут удалены: новость, связанные теги, связанные новости, комментарии, галерея, вложения.</small>
        </div>
        
        <form method="POST">
            <div class="button-group">
                <button type="submit" name="confirm" value="yes" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Удалить новость
                </button>
                <a href="index.php" class="btn btn-outline">Отмена</a>
            </div>
        </form>
    </div>
</body>
</html>