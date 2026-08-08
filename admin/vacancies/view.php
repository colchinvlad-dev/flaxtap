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

// Получаем данные вакансии
$query = "SELECT v.*, 
          vc.name as category_name,
          u1.name as created_by_name,
          u2.name as updated_by_name
          FROM vacancies v
          LEFT JOIN vacancy_categories vc ON v.category_id = vc.id
          LEFT JOIN users u1 ON v.created_by = u1.id
          LEFT JOIN users u2 ON v.updated_by = v.updated_by
          WHERE v.id = $vacancy_id";
$result = mysqli_query($conn, $query);
$vacancy = mysqli_fetch_assoc($result);

if (!$vacancy) {
    header("Location: index.php");
    exit();
}

// Получаем навыки вакансии
$skills_query = "SELECT vs.*, vsp.level 
                 FROM vacancy_skills vs
                 INNER JOIN vacancy_skill_pivot vsp ON vs.id = vsp.skill_id
                 WHERE vsp.vacancy_id = $vacancy_id
                 ORDER BY vs.name";
$skills_result = mysqli_query($conn, $skills_query);

// Получаем теги вакансии
$tags_query = "SELECT vt.* 
               FROM vacancy_tags vt
               INNER JOIN vacancy_tag_pivot vtp ON vt.id = vtp.tag_id
               WHERE vtp.vacancy_id = $vacancy_id
               ORDER BY vt.name";
$tags_result = mysqli_query($conn, $tags_query);

// Определяем активную вкладку
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'details';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр вакансии - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style/vacancies/view.css">
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Просмотр вакансии</h1>
            <div>
                <a href="edit.php?id=<?php echo $vacancy_id; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Редактировать
                </a>
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Назад
                </a>
            </div>
        </div>
        
        <div class="view-container">
            <!-- Заголовок вакансии -->
            <div class="vacancy-header">
                <div class="header-top">
                    <div>
                        <h1 class="vacancy-title"><?php echo htmlspecialchars($vacancy['title']); ?></h1>
                        <div class="vacancy-meta">
                            <div class="meta-item">
                                <i class="fas fa-tag"></i>
                                <span><?php echo htmlspecialchars($vacancy['category_name'] ?? 'Без категории'); ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>
                                    <?php if ($vacancy['is_remote']): ?>
                                        Удаленная работа
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($vacancy['location_city']); ?>, <?php echo htmlspecialchars($vacancy['location_address']); ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-eye"></i>
                                <span><?php echo $vacancy['views_count']; ?> просмотров</span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-calendar"></i>
                                <span>Создана: <?php echo date('d.m.Y', strtotime($vacancy['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <?php 
                        $status_class = 'badge-' . $vacancy['status'];
                        $status_text = '';
                        switch($vacancy['status']) {
                            case 'active': $status_text = 'Активная'; break;
                            case 'draft': $status_text = 'Черновик'; break;
                            case 'archived': $status_text = 'Архив'; break;
                            case 'closed': $status_text = 'Закрыта'; break;
                        }
                        ?>
                        <div class="status-badge-large <?php echo $status_class; ?>">
                            <?php echo $status_text; ?>
                        </div>
                    </div>
                </div>
                
                <div class="header-actions">
                    <?php if ($vacancy['is_published']): ?>
                        <a href="/career/<?php echo $vacancy['slug']; ?>" target="_blank" class="btn btn-outline">
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
                <a href="?id=<?php echo $vacancy_id; ?>&tab=details" 
                   class="tab <?php echo $active_tab == 'details' ? 'active' : ''; ?>">
                    <i class="fas fa-info-circle"></i> Детали
                </a>
                <a href="?id=<?php echo $vacancy_id; ?>&tab=applications" 
                   class="tab <?php echo $active_tab == 'applications' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Отклики
                    <?php 
                    $count_query = "SELECT COUNT(*) as count FROM vacancy_applications WHERE vacancy_id = $vacancy_id";
                    $count_result = mysqli_query($conn, $count_query);
                    $count = mysqli_fetch_assoc($count_result)['count'];
                    if ($count > 0): ?>
                        <span style="background: var(--primary-color); color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; margin-left: 5px;">
                            <?php echo $count; ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="?id=<?php echo $vacancy_id; ?>&tab=seo" 
                   class="tab <?php echo $active_tab == 'seo' ? 'active' : ''; ?>">
                    <i class="fas fa-search"></i> SEO
                </a>
            </div>
            
            <!-- Содержимое вкладок -->
            <div id="detailsTab" class="tab-content <?php echo $active_tab == 'details' ? 'active' : ''; ?>">
                <!-- Детали вакансии -->
                <div class="details-card">
                    <h3 class="section-title">Описание вакансии</h3>
                    <div class="content-box">
                        <?php echo $vacancy['description']; ?>
                    </div>
                </div>
                
                <div class="details-card">
                    <h3 class="section-title">Обязанности</h3>
                    <div class="content-box">
                        <?php echo nl2br(htmlspecialchars($vacancy['responsibilities'])); ?>
                    </div>
                </div>
                
                <div class="details-card">
                    <h3 class="section-title">Требования</h3>
                    <div class="content-box">
                        <?php echo nl2br(htmlspecialchars($vacancy['requirements'])); ?>
                    </div>
                </div>
                
                <div class="details-card">
                    <h3 class="section-title">Условия работы</h3>
                    <div class="row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <h4>Тип работы</h4>
                            <p>
                                <?php 
                                switch($vacancy['work_type']) {
                                    case 'office': echo 'Офис'; break;
                                    case 'remote': echo 'Удаленно'; break;
                                    case 'hybrid': echo 'Гибрид'; break;
                                }
                                ?>
                            </p>
                        </div>
                        <div>
                            <h4>Тип занятости</h4>
                            <p>
                                <?php 
                                switch($vacancy['employment_type']) {
                                    case 'full_time': echo 'Полная занятость'; break;
                                    case 'part_time': echo 'Частичная занятость'; break;
                                    case 'freelance': echo 'Фриланс'; break;
                                    case 'internship': echo 'Стажировка'; break;
                                }
                                ?>
                            </p>
                        </div>
                        <div>
                            <h4>Уровень опыта</h4>
                            <p>
                                <?php 
                                switch($vacancy['experience_level']) {
                                    case 'no_experience': echo 'Без опыта'; break;
                                    case 'junior': echo 'Junior'; break;
                                    case 'middle': echo 'Middle'; break;
                                    case 'senior': echo 'Senior'; break;
                                    case 'lead': echo 'Lead'; break;
                                }
                                ?>
                            </p>
                        </div>
                        <div>
                            <h4>График работы</h4>
                            <p><?php echo htmlspecialchars($vacancy['working_hours']); ?></p>
                        </div>
                    </div>
                </div>
                
                <?php if (mysqli_num_rows($skills_result) > 0): ?>
                    <div class="details-card">
                        <h3 class="section-title">Ключевые навыки</h3>
                        <div class="skills-list">
                            <?php mysqli_data_seek($skills_result, 0); ?>
                            <?php while($skill = mysqli_fetch_assoc($skills_result)): ?>
                                <div class="skill-item">
                                    <?php echo htmlspecialchars($skill['name']); ?>
                                    <span class="skill-level">
                                        (<?php 
                                        switch($skill['level']) {
                                            case 'basic': echo 'базовый'; break;
                                            case 'intermediate': echo 'средний'; break;
                                            case 'advanced': echo 'продвинутый'; break;
                                        }
                                        ?>)
                                    </span>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (mysqli_num_rows($tags_result) > 0): ?>
                    <div class="details-card">
                        <h3 class="section-title">Теги</h3>
                        <div class="skills-list">
                            <?php mysqli_data_seek($tags_result, 0); ?>
                            <?php while($tag = mysqli_fetch_assoc($tags_result)): ?>
                                <div class="skill-item">
                                    <?php echo htmlspecialchars($tag['name']); ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Вкладка откликов -->
            <div id="applicationsTab" class="tab-content <?php echo $active_tab == 'applications' ? 'active' : ''; ?>">
                <?php
                // Получаем отклики
                $applications_query = "SELECT va.* 
                                       FROM vacancy_applications va
                                       WHERE va.vacancy_id = $vacancy_id
                                       ORDER BY va.created_at DESC";
                $applications_result = mysqli_query($conn, $applications_query);
                ?>
                
                <?php if (mysqli_num_rows($applications_result) > 0): ?>
                    <div class="table-container">
                        <table class="applications-table">
                            <thead>
                                <tr>
                                    <th>Кандидат</th>
                                    <th>Контакты</th>
                                    <th>Опыт</th>
                                    <th>Зарплата</th>
                                    <th>Статус</th>
                                    <th>Дата</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($application = mysqli_fetch_assoc($applications_result)): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($application['full_name']); ?></strong><br>
                                            <small><?php echo htmlspecialchars($application['last_position'] ?? 'Не указано'); ?></small>
                                        </td>
                                        <td>
                                            <div><?php echo htmlspecialchars($application['email']); ?></div>
                                            <div><?php echo htmlspecialchars($application['phone']); ?></div>
                                        </td>
                                        <td>
                                            <?php echo $application['experience_years'] ?? '0'; ?> лет
                                        </td>
                                        <td>
                                            <?php if ($application['expected_salary']): ?>
                                                <?php echo number_format($application['expected_salary'], 0, ',', ' '); ?> ₽
                                            <?php else: ?>
                                                Не указана
                                            <?php endif; ?>
                                        </td>
                                        <td>
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
                                            <span class="status-badge <?php echo $status_class; ?>">
                                                <?php echo $status_text; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo date('d.m.Y', strtotime($application['created_at'])); ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="application_view.php?id=<?php echo $application['id']; ?>" 
                                                   class="btn btn-sm btn-outline" title="Просмотр">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="application_edit.php?id=<?php echo $application['id']; ?>" 
                                                   class="btn btn-sm btn-primary" title="Редактировать">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="application_delete.php?id=<?php echo $application['id']; ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Вы уверены, что хотите удалить этот отклик?');"
                                                   title="Удалить">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-users"></i>
                        <p>Откликов на эту вакансию пока нет</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Вкладка SEO -->
            <div id="seoTab" class="tab-content <?php echo $active_tab == 'seo' ? 'active' : ''; ?>">
                <div class="details-card">
                    <h3 class="section-title">SEO информация</h3>
                    <div class="content-box">
                        <h4>Meta Title</h4>
                        <p><?php echo htmlspecialchars($vacancy['meta_title']); ?></p>
                        
                        <h4>Meta Description</h4>
                        <p><?php echo htmlspecialchars($vacancy['meta_description']); ?></p>
                        
                        <h4>Meta Keywords</h4>
                        <p><?php echo htmlspecialchars($vacancy['meta_keywords']); ?></p>
                        
                        <h4>URL (slug)</h4>
                        <p>/career/<?php echo htmlspecialchars($vacancy['slug']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        function copyLink() {
            const link = `${window.location.origin}/career/<?php echo $vacancy['slug']; ?>`;
            navigator.clipboard.writeText(link)
                .then(() => {
                    alert('Ссылка скопирована в буфер обмена');
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
                history.pushState(null, '', `?id=<?php echo $vacancy_id; ?>&tab=${tabId}`);
                
                // Активируем вкладку
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                document.getElementById(tabId + 'Tab').classList.add('active');
            });
        });
    </script>
</body>
</html>