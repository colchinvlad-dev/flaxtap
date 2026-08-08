<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

$application_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$application_id) {
    header("Location: index.php");
    exit();
}

// Получаем данные отклика
$query = "SELECT va.*, v.title as vacancy_title
          FROM vacancy_applications va
          LEFT JOIN vacancies v ON va.vacancy_id = v.id
          WHERE va.id = $application_id";
$result = mysqli_query($conn, $query);
$application = mysqli_fetch_assoc($result);

if (!$application) {
    header("Location: index.php");
    exit();
}

// Получаем историю статусов
$history_query = "SELECT h.*, u.name as changed_by_name
                  FROM vacancy_application_history h
                  LEFT JOIN users u ON h.changed_by = u.id
                  WHERE h.application_id = $application_id
                  ORDER BY h.created_at DESC";
$history_result = mysqli_query($conn, $history_query);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр отклика - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style/vacancies/feedback.css">
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Просмотр отклика</h1>
            <div>
                <a href="view.php?id=<?php echo $application['vacancy_id']; ?>&tab=applications" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Назад к вакансии
                </a>
            </div>
        </div>
        
        <div class="view-container">
            <!-- Информация о кандидате -->
            <div class="application-card">
                <div class="card-header">
                    <div>
                        <div class="applicant-name"><?php echo htmlspecialchars($application['full_name']); ?></div>
                        <a href="view.php?id=<?php echo $application['vacancy_id']; ?>" class="vacancy-link">
                            <i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($application['vacancy_title']); ?>
                        </a>
                    </div>
                    <div>
                        <?php 
                        $status_class = 'badge-' . $application['status'];
                        $status_text = '';
                        switch($application['status']) {
                            case 'new': $status_text = 'Новый'; break;
                            case 'reviewed': $status_text = 'Просмотрено'; break;
                            case 'interview': $status_text = 'Собеседование'; break;
                            case 'rejected': $status_text = 'Отклонено'; break;
                            case 'accepted': $status_text = 'Принято'; break;
                        }
                        ?>
                        <div class="status-badge-large <?php echo $status_class; ?>">
                            <?php echo $status_text; ?>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="info-group">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($application['email']); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Телефон</div>
                        <div class="info-value"><?php echo htmlspecialchars($application['phone']); ?></div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="info-group">
                        <div class="info-label">Дата рождения</div>
                        <div class="info-value">
                            <?php echo $application['birth_date'] ? date('d.m.Y', strtotime($application['birth_date'])) : 'Не указано'; ?>
                        </div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Город проживания</div>
                        <div class="info-value"><?php echo htmlspecialchars($application['city'] ?? 'Не указан'); ?></div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="info-group">
                        <div class="info-label">Ожидаемая зарплата</div>
                        <div class="info-value">
                            <?php if ($application['expected_salary']): ?>
                                <?php echo number_format($application['expected_salary'], 0, ',', ' '); ?> ₽
                            <?php else: ?>
                                Не указана
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Опыт работы</div>
                        <div class="info-value">
                            <?php echo $application['experience_years'] ?? '0'; ?> лет
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="info-group">
                        <div class="info-label">Последняя должность</div>
                        <div class="info-value"><?php echo htmlspecialchars($application['last_position'] ?? 'Не указано'); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Последнее место работы</div>
                        <div class="info-value"><?php echo htmlspecialchars($application['last_company'] ?? 'Не указано'); ?></div>
                    </div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Уровень образования</div>
                    <div class="info-value"><?php echo htmlspecialchars($application['education_level'] ?? 'Не указано'); ?></div>
                </div>
                
                <?php if ($application['portfolio_url'] || $application['linkedin_url'] || $application['github_url']): ?>
                    <div class="info-group">
                        <div class="info-label">Ссылки</div>
                        <div class="info-value">
                            <?php if ($application['portfolio_url']): ?>
                                <a href="<?php echo htmlspecialchars($application['portfolio_url']); ?>" target="_blank" class="btn btn-sm btn-outline">
                                    <i class="fas fa-globe"></i> Портфолио
                                </a>
                            <?php endif; ?>
                            <?php if ($application['linkedin_url']): ?>
                                <a href="<?php echo htmlspecialchars($application['linkedin_url']); ?>" target="_blank" class="btn btn-sm btn-outline">
                                    <i class="fab fa-linkedin"></i> LinkedIn
                                </a>
                            <?php endif; ?>
                            <?php if ($application['github_url']): ?>
                                <a href="<?php echo htmlspecialchars($application['github_url']); ?>" target="_blank" class="btn btn-sm btn-outline">
                                    <i class="fab fa-github"></i> GitHub
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($application['resume_path']): ?>
                    <div class="info-group">
                        <div class="info-label">Резюме</div>
                        <div class="info-value">
                            <a href="<?php echo htmlspecialchars($application['resume_path']); ?>" 
                               target="_blank" 
                               class="btn btn-download">
                                <i class="fas fa-download"></i> Скачать резюме
                            </a>
                            <small style="margin-left: 10px;">
                                <?php echo htmlspecialchars($application['resume_original_name']); ?>
                            </small>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($application['cover_letter']): ?>
                    <div class="info-group">
                        <div class="info-label">Сопроводительное письмо</div>
                        <div class="cover-letter"><?php echo nl2br(htmlspecialchars($application['cover_letter'])); ?></div>
                    </div>
                <?php endif; ?>
                
                <div class="info-group">
                    <div class="info-label">Дата отклика</div>
                    <div class="info-value"><?php echo date('d.m.Y H:i', strtotime($application['created_at'])); ?></div>
                </div>
            </div>
            
            <!-- История статусов -->
            <div class="application-card">
                <h3 style="font-size: 20px; font-weight: 700; margin-bottom: 20px;">История статусов</h3>
                
                <?php if (mysqli_num_rows($history_result) > 0): ?>
                    <?php while($history = mysqli_fetch_assoc($history_result)): ?>
                        <div class="history-item">
                            <div class="history-date">
                                <?php echo date('d.m.Y H:i', strtotime($history['created_at'])); ?>
                            </div>
                            <div class="history-status">
                                <?php 
                                switch($history['new_status']) {
                                    case 'new': echo 'Новый'; break;
                                    case 'reviewed': echo 'Просмотрено'; break;
                                    case 'interview': echo 'Собеседование'; break;
                                    case 'rejected': echo 'Отклонено'; break;
                                    case 'accepted': echo 'Принято'; break;
                                }
                                ?>
                            </div>
                            <?php if ($history['change_notes']): ?>
                                <div class="history-reason">
                                    <?php echo htmlspecialchars($history['change_notes']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($history['changed_by_name']): ?>
                                <div class="history-by">
                                    Изменено: <?php echo htmlspecialchars($history['changed_by_name']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: var(--gray-color); text-align: center; padding: 20px;">
                        История изменений статусов отсутствует
                    </p>
                <?php endif; ?>
            </div>
            
            <div class="action-buttons">
                <a href="application_edit.php?id=<?php echo $application_id; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Редактировать
                </a>
                <a href="application_delete.php?id=<?php echo $application_id; ?>" 
                   class="btn btn-danger"
                   onclick="return confirm('Вы уверены, что хотите удалить этот отклик?');">
                    <i class="fas fa-trash"></i> Удалить отклик
                </a>
            </div>
        </div>
    </main>
</body>
</html>