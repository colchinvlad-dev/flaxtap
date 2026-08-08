<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

$vacancy_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$vacancy_id) {
    header("Location: index.php");
    exit();
}

// Получаем информацию о вакансии
$query = "SELECT v.* FROM vacancies v WHERE v.id = $vacancy_id";
$result = mysqli_query($conn, $query);
$vacancy = mysqli_fetch_assoc($result);

if (!$vacancy) {
    header("Location: index.php");
    exit();
}

// Проверяем, есть ли отклики на вакансию
$applications_query = "SELECT COUNT(*) as count FROM vacancy_applications WHERE vacancy_id = $vacancy_id";
$applications_result = mysqli_query($conn, $applications_query);
$applications_count = mysqli_fetch_assoc($applications_result)['count'];

// Обработка удаления
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm = isset($_POST['confirm']) ? $_POST['confirm'] : '';
    
    if ($confirm === 'yes') {
        // Начинаем транзакцию
        mysqli_begin_transaction($conn);
        
        try {
            // Удаляем связанные записи
            $queries = [
                "DELETE FROM vacancy_tag_pivot WHERE vacancy_id = $vacancy_id",
                "DELETE FROM vacancy_skill_pivot WHERE vacancy_id = $vacancy_id",
                "DELETE FROM vacancy_applications WHERE vacancy_id = $vacancy_id",
                "DELETE FROM vacancies WHERE id = $vacancy_id"
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
            $log_message = date('Y-m-d H:i:s') . " - Администратор {$admin_name} удалил вакансию: {$vacancy['title']} (ID: {$vacancy['id']})\n";
            file_put_contents('../admin_log.txt', $log_message, FILE_APPEND);
            
            $_SESSION['success'] = "Вакансия успешно удалена";
            header("Location: index.php");
            exit();
            
        } catch (Exception $e) {
            // Откатываем транзакцию при ошибке
            mysqli_rollback($conn);
            $error = 'Ошибка при удалении вакансии: ' . $e->getMessage();
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
    <title>Удаление вакансии - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style/vacancies/delete.css">
</head>
<body>
    <div class="delete-container">
        <div class="warning-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        
        <h1>Подтверждение удаления</h1>
        
        <div class="vacancy-info">
            <div class="info-row">
                <span class="info-label">Название вакансии:</span>
                <span class="info-value"><?php echo htmlspecialchars($vacancy['title']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Статус:</span>
                <span class="info-value">
                    <?php 
                    switch($vacancy['status']) {
                        case 'active': echo 'Активная'; break;
                        case 'draft': echo 'Черновик'; break;
                        case 'archived': echo 'Архив'; break;
                        case 'closed': echo 'Закрыта'; break;
                    }
                    ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Дата создания:</span>
                <span class="info-value"><?php echo date('d.m.Y H:i', strtotime($vacancy['created_at'])); ?></span>
            </div>
            <?php if ($applications_count > 0): ?>
                <div class="info-row">
                    <span class="info-label">Откликов:</span>
                    <span class="info-value" style="color: var(--danger-color);">
                        <?php echo $applications_count; ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($applications_count > 0): ?>
            <div class="danger-alert">
                <i class="fas fa-exclamation-circle"></i>
                <strong>Внимание!</strong> У этой вакансии есть отклики. При удалении все отклики также будут удалены безвозвратно.
            </div>
        <?php endif; ?>
        
        <div class="danger-alert">
            <i class="fas fa-exclamation-circle"></i>
            <strong>Внимание!</strong> Это действие нельзя отменить. Все данные вакансии будут удалены безвозвратно.
            <br><br>
            <small>Будут удалены: вакансия, связанные навыки и теги, все отклики кандидатов.</small>
        </div>
        
        <form method="POST">
            <div class="button-group">
                <button type="submit" name="confirm" value="yes" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Удалить вакансию
                </button>
                <a href="index.php" class="btn btn-outline">Отмена</a>
            </div>
        </form>
    </div>
</body>
</html>