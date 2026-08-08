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

// Получаем данные вакансии (ИСПРАВЛЕНО: JOIN для updated_by)
$query = "SELECT v.*, 
          vc.name as category_name,
          u1.name as created_by_name,
          u2.name as updated_by_name
          FROM vacancies v
          LEFT JOIN vacancy_categories vc ON v.category_id = vc.id
          LEFT JOIN users u1 ON v.created_by = u1.id
          LEFT JOIN users u2 ON v.updated_by = u2.id
          WHERE v.id = $vacancy_id";
$result = mysqli_query($conn, $query);
$vacancy = mysqli_fetch_assoc($result);

if (!$vacancy) {
    header("Location: index.php");
    exit();
}

// Получаем выбранные навыки
$skills_query = "SELECT vs.*, vsp.level 
                 FROM vacancy_skills vs
                 INNER JOIN vacancy_skill_pivot vsp ON vs.id = vsp.skill_id
                 WHERE vsp.vacancy_id = $vacancy_id";
$skills_result = mysqli_query($conn, $skills_query);
$selected_skills = [];
while($skill = mysqli_fetch_assoc($skills_result)) {
    $selected_skills[$skill['id']] = $skill['level'];
}

// Получаем выбранные теги
$tags_query = "SELECT vt.* 
               FROM vacancy_tags vt
               INNER JOIN vacancy_tag_pivot vtp ON vt.id = vtp.tag_id
               WHERE vtp.vacancy_id = $vacancy_id";
$tags_result = mysqli_query($conn, $tags_query);
$selected_tags = [];
while($tag = mysqli_fetch_assoc($tags_result)) {
    $selected_tags[] = $tag['id'];
}

// Получаем все категории, навыки и теги
$categories_query = "SELECT * FROM vacancy_categories WHERE is_active = 1 ORDER BY sort_order";
$categories_result = mysqli_query($conn, $categories_query);

$all_skills_query = "SELECT * FROM vacancy_skills ORDER BY name";
$all_skills_result = mysqli_query($conn, $all_skills_query);

$all_tags_query = "SELECT * FROM vacancy_tags ORDER BY name";
$all_tags_result = mysqli_query($conn, $all_tags_query);

// Обработка формы
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $slug = mysqli_real_escape_string($conn, $_POST['slug']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : NULL;
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $priority = mysqli_real_escape_string($conn, $_POST['priority']);
    $responsibilities = mysqli_real_escape_string($conn, $_POST['responsibilities']);
    $requirements = mysqli_real_escape_string($conn, $_POST['requirements']);
    $benefits = mysqli_real_escape_string($conn, $_POST['benefits']);
    $work_type = mysqli_real_escape_string($conn, $_POST['work_type']);
    $employment_type = mysqli_real_escape_string($conn, $_POST['employment_type']);
    $experience_level = mysqli_real_escape_string($conn, $_POST['experience_level']);
    $working_hours = mysqli_real_escape_string($conn, $_POST['working_hours']);
    $education = mysqli_real_escape_string($conn, $_POST['education']);
    $languages = mysqli_real_escape_string($conn, $_POST['languages']);
    $salary_from = !empty($_POST['salary_from']) ? (float)$_POST['salary_from'] : NULL;
    $salary_to = !empty($_POST['salary_to']) ? (float)$_POST['salary_to'] : NULL;
    $salary_type = mysqli_real_escape_string($conn, $_POST['salary_type']);
    $salary_note = mysqli_real_escape_string($conn, $_POST['salary_note']);
    $is_negotiable = isset($_POST['is_negotiable']) ? 1 : 0;
    $is_remote = isset($_POST['is_remote']) ? 1 : 0;
    $location_city = mysqli_real_escape_string($conn, $_POST['location_city']);
    $location_address = mysqli_real_escape_string($conn, $_POST['location_address']);
    $application_deadline = !empty($_POST['application_deadline']) ? $_POST['application_deadline'] : NULL;
    $selection_process = mysqli_real_escape_string($conn, $_POST['selection_process']);
    $contact_person = mysqli_real_escape_string($conn, $_POST['contact_person']);
    $contact_email = mysqli_real_escape_string($conn, $_POST['contact_email']);
    $contact_phone = mysqli_real_escape_string($conn, $_POST['contact_phone']);
    $meta_title = mysqli_real_escape_string($conn, $_POST['meta_title']);
    $meta_description = mysqli_real_escape_string($conn, $_POST['meta_description']);
    $meta_keywords = mysqli_real_escape_string($conn, $_POST['meta_keywords']);
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    
    // Текущий ID пользователя (администратора) - ИСПРАВЛЕНО: используем admin_id
    $current_user_id = $_SESSION['admin_id'] ?? 0;
    
    // Подготавливаем запрос на обновление - ИСПРАВЛЕНО: правильная подготовка значений
    $update_query = "UPDATE vacancies SET 
        title = '$title',
        slug = '$slug',
        description = '$description',
        category_id = " . ($category_id ? $category_id : "NULL") . ",
        status = '$status',
        department = '$department',
        priority = '$priority',
        responsibilities = '$responsibilities',
        requirements = '$requirements',
        benefits = '$benefits',
        work_type = '$work_type',
        employment_type = '$employment_type',
        experience_level = '$experience_level',
        working_hours = '$working_hours',
        education = '$education',
        languages = '$languages',
        salary_from = " . ($salary_from !== NULL ? "'$salary_from'" : "NULL") . ",
        salary_to = " . ($salary_to !== NULL ? "'$salary_to'" : "NULL") . ",
        salary_type = '$salary_type',
        salary_note = '$salary_note',
        is_negotiable = $is_negotiable,
        is_remote = $is_remote,
        location_city = '$location_city',
        location_address = '$location_address',
        application_deadline = " . ($application_deadline ? "'$application_deadline'" : "NULL") . ",
        selection_process = '$selection_process',
        contact_person = '$contact_person',
        contact_email = '$contact_email',
        contact_phone = '$contact_phone',
        meta_title = '$meta_title',
        meta_description = '$meta_description',
        meta_keywords = '$meta_keywords',
        is_published = $is_published,
        updated_by = $current_user_id,
        updated_at = NOW()";
    
    // Если вакансия публикуется впервые, устанавливаем дату публикации
    if ($is_published && !$vacancy['published_at']) {
        $update_query .= ", published_at = NOW()";
    }
    
    $update_query .= " WHERE id = $vacancy_id";
    
    // Обновляем основную информацию вакансии
    if (mysqli_query($conn, $update_query)) {
        // Обработка навыков
        // Удаляем старые навыки
        mysqli_query($conn, "DELETE FROM vacancy_skill_pivot WHERE vacancy_id = $vacancy_id");
        
        // Добавляем новые навыки
        if (isset($_POST['skills']) && is_array($_POST['skills'])) {
            foreach ($_POST['skills'] as $skill_id) {
                $skill_id = (int)$skill_id;
                if (isset($_POST["skill_level_$skill_id"])) {
                    $level = mysqli_real_escape_string($conn, $_POST["skill_level_$skill_id"]);
                    mysqli_query($conn, "INSERT INTO vacancy_skill_pivot (vacancy_id, skill_id, level) VALUES ($vacancy_id, $skill_id, '$level')");
                }
            }
        }
        
        // Обработка тегов
        // Удаляем старые теги
        mysqli_query($conn, "DELETE FROM vacancy_tag_pivot WHERE vacancy_id = $vacancy_id");
        
        // Добавляем новые теги
        if (isset($_POST['tags']) && is_array($_POST['tags'])) {
            foreach ($_POST['tags'] as $tag_id) {
                $tag_id = (int)$tag_id;
                mysqli_query($conn, "INSERT INTO vacancy_tag_pivot (vacancy_id, tag_id) VALUES ($vacancy_id, $tag_id)");
            }
        }
        
        $success = 'Вакансия успешно обновлена';
        
        // Обновляем данные вакансии для отображения
        $query = "SELECT v.*, 
                  vc.name as category_name,
                  u1.name as created_by_name,
                  u2.name as updated_by_name
                  FROM vacancies v
                  LEFT JOIN vacancy_categories vc ON v.category_id = vc.id
                  LEFT JOIN users u1 ON v.created_by = u1.id
                  LEFT JOIN users u2 ON v.updated_by = u2.id
                  WHERE v.id = $vacancy_id";
        $result = mysqli_query($conn, $query);
        $vacancy = mysqli_fetch_assoc($result);
        
        // Обновляем выбранные навыки и теги
        $skills_query = "SELECT vs.*, vsp.level 
                         FROM vacancy_skills vs
                         INNER JOIN vacancy_skill_pivot vsp ON vs.id = vsp.skill_id
                         WHERE vsp.vacancy_id = $vacancy_id";
        $skills_result = mysqli_query($conn, $skills_query);
        $selected_skills = [];
        while($skill = mysqli_fetch_assoc($skills_result)) {
            $selected_skills[$skill['id']] = $skill['level'];
        }
        
        $tags_query = "SELECT vt.* 
                       FROM vacancy_tags vt
                       INNER JOIN vacancy_tag_pivot vtp ON vt.id = vtp.tag_id
                       WHERE vtp.vacancy_id = $vacancy_id";
        $tags_result = mysqli_query($conn, $tags_query);
        $selected_tags = [];
        while($tag = mysqli_fetch_assoc($tags_result)) {
            $selected_tags[] = $tag['id'];
        }
        
        // Сбрасываем результаты запросов для категорий, навыков и тегов
        mysqli_data_seek($categories_result, 0);
        mysqli_data_seek($all_skills_result, 0);
        mysqli_data_seek($all_tags_result, 0);
    } else {
        $error = 'Ошибка при обновлении вакансии: ' . mysqli_error($conn);
        // Для отладки можно добавить:
        // $error .= '<br>Запрос: ' . $update_query;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование вакансии - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style/vacancies/edit.css">
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Редактирование вакансии</h1>
            <div>
                <a href="view.php?id=<?php echo $vacancy_id; ?>" class="btn btn-outline">
                    <i class="fas fa-eye"></i> Просмотр
                </a>
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Назад
                </a>
            </div>
        </div>
        
        <div class="vacancy-status">
            <div class="status-info">
                <span>Создана: <?php echo date('d.m.Y', strtotime($vacancy['created_at'])); ?></span>
                <span>Обновлена: <?php echo date('d.m.Y', strtotime($vacancy['updated_at'])); ?></span>
                <span>Просмотры: <?php echo $vacancy['views_count']; ?></span>
                <a href="view.php?id=<?php echo $vacancy_id; ?>&tab=applications" class="applications-badge">
                    Откликов: <?php 
                    $apps_query = "SELECT COUNT(*) as count FROM vacancy_applications WHERE vacancy_id = $vacancy_id";
                    $apps_result = mysqli_query($conn, $apps_query);
                    $apps = mysqli_fetch_assoc($apps_result);
                    echo $apps['count'];
                    ?>
                </a>
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
        
<!-- Форма редактирования -->
<div class="edit-container">
    <form method="POST" id="vacancyForm">
        <!-- Основная информация -->
        <div class="form-section">
            <div class="section-header">
                <i class="fas fa-info-circle"></i>
                <h2>Основная информация</h2>
            </div>
            
            <div class="row">
                <div class="form-group">
                    <label for="title" class="form-label required">Название вакансии</label>
                    <input type="text" id="title" name="title" class="form-control" required
                           value="<?php echo htmlspecialchars($vacancy['title']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="slug" class="form-label required">URL (slug)</label>
                    <input type="text" id="slug" name="slug" class="form-control" required
                           value="<?php echo htmlspecialchars($vacancy['slug']); ?>">
                    <div class="slug-preview">
                        Ссылка: <a href="/career/<?php echo htmlspecialchars($vacancy['slug']); ?>" id="slugPreview" target="_blank">
                            /career/<?php echo htmlspecialchars($vacancy['slug']); ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="form-group">
                    <label for="category_id" class="form-label">Категория</label>
                    <select id="category_id" name="category_id" class="form-control">
                        <option value="">Без категории</option>
                        <?php mysqli_data_seek($categories_result, 0); ?>
                        <?php while($category = mysqli_fetch_assoc($categories_result)): ?>
                            <option value="<?php echo $category['id']; ?>"
                                <?php echo $vacancy['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="department" class="form-label">Отдел/Департамент</label>
                    <input type="text" id="department" name="department" class="form-control"
                           value="<?php echo htmlspecialchars($vacancy['department']); ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="form-group">
                    <label for="status" class="form-label required">Статус</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="draft" <?php echo $vacancy['status'] == 'draft' ? 'selected' : ''; ?>>Черновик</option>
                        <option value="active" <?php echo $vacancy['status'] == 'active' ? 'selected' : ''; ?>>Активная</option>
                        <option value="archived" <?php echo $vacancy['status'] == 'archived' ? 'selected' : ''; ?>>Архив</option>
                        <option value="closed" <?php echo $vacancy['status'] == 'closed' ? 'selected' : ''; ?>>Закрыта</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="priority" class="form-label">Приоритет</label>
                    <select id="priority" name="priority" class="form-control">
                        <option value="normal" <?php echo $vacancy['priority'] == 'normal' ? 'selected' : ''; ?>>Обычный</option>
                        <option value="urgent" <?php echo $vacancy['priority'] == 'urgent' ? 'selected' : ''; ?>>Срочный</option>
                        <option value="low" <?php echo $vacancy['priority'] == 'low' ? 'selected' : ''; ?>>Низкий</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Описание вакансии -->
        <div class="form-section">
            <div class="section-header">
                <i class="fas fa-file-alt"></i>
                <h2>Описание вакансии</h2>
            </div>
            
            <div class="form-group">
                <label for="description" class="form-label required">Описание</label>
                <div class="editor-container">
                    <div id="descriptionEditor"><?php echo $vacancy['description']; ?></div>
                </div>
                <textarea id="description" name="description" style="display: none;"><?php echo htmlspecialchars($vacancy['description']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="responsibilities" class="form-label">Обязанности</label>
                <textarea id="responsibilities" name="responsibilities" class="form-control" rows="4"><?php echo htmlspecialchars($vacancy['responsibilities']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="requirements" class="form-label">Требования</label>
                <textarea id="requirements" name="requirements" class="form-control" rows="4"><?php echo htmlspecialchars($vacancy['requirements']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="benefits" class="form-label">Мы предлагаем</label>
                <textarea id="benefits" name="benefits" class="form-control" rows="4"><?php echo htmlspecialchars($vacancy['benefits']); ?></textarea>
            </div>
        </div>
        
        <!-- Условия работы -->
        <div class="form-section">
            <div class="section-header">
                <i class="fas fa-briefcase"></i>
                <h2>Условия работы</h2>
            </div>
            
            <div class="row">
                <div class="form-group">
                    <label for="work_type" class="form-label required">Тип работы</label>
                    <select id="work_type" name="work_type" class="form-control" required>
                        <option value="office" <?php echo $vacancy['work_type'] == 'office' ? 'selected' : ''; ?>>Офис</option>
                        <option value="remote" <?php echo $vacancy['work_type'] == 'remote' ? 'selected' : ''; ?>>Удаленно</option>
                        <option value="hybrid" <?php echo $vacancy['work_type'] == 'hybrid' ? 'selected' : ''; ?>>Гибрид</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="employment_type" class="form-label required">Тип занятости</label>
                    <select id="employment_type" name="employment_type" class="form-control" required>
                        <option value="full_time" <?php echo $vacancy['employment_type'] == 'full_time' ? 'selected' : ''; ?>>Полная занятость</option>
                        <option value="part_time" <?php echo $vacancy['employment_type'] == 'part_time' ? 'selected' : ''; ?>>Частичная занятость</option>
                        <option value="freelance" <?php echo $vacancy['employment_type'] == 'freelance' ? 'selected' : ''; ?>>Фриланс</option>
                        <option value="internship" <?php echo $vacancy['employment_type'] == 'internship' ? 'selected' : ''; ?>>Стажировка</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="form-group">
                    <label for="experience_level" class="form-label">Уровень опыта</label>
                    <select id="experience_level" name="experience_level" class="form-control">
                        <option value="no_experience" <?php echo $vacancy['experience_level'] == 'no_experience' ? 'selected' : ''; ?>>Без опыта</option>
                        <option value="junior" <?php echo $vacancy['experience_level'] == 'junior' ? 'selected' : ''; ?>>Junior</option>
                        <option value="middle" <?php echo $vacancy['experience_level'] == 'middle' ? 'selected' : ''; ?>>Middle</option>
                        <option value="senior" <?php echo $vacancy['experience_level'] == 'senior' ? 'selected' : ''; ?>>Senior</option>
                        <option value="lead" <?php echo $vacancy['experience_level'] == 'lead' ? 'selected' : ''; ?>>Lead</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="working_hours" class="form-label">График работы</label>
                    <input type="text" id="working_hours" name="working_hours" class="form-control"
                           value="<?php echo htmlspecialchars($vacancy['working_hours']); ?>"
                           placeholder="Например: 5/2, с 9:00 до 18:00">
                </div>
            </div>
            
            <div class="form-group">
                <label for="education" class="form-label">Образование</label>
                <input type="text" id="education" name="education" class="form-control"
                       value="<?php echo htmlspecialchars($vacancy['education']); ?>"
                       placeholder="Например: Высшее образование">
            </div>
            
            <div class="form-group">
                <label for="languages" class="form-label">Языки</label>
                <input type="text" id="languages" name="languages" class="form-control"
                       value="<?php echo htmlspecialchars($vacancy['languages']); ?>"
                       placeholder="Например: Русский (носитель), Английский (B1)">
            </div>
        </div>
        
        <!-- Зарплата -->
        <div class="form-section">
            <div class="section-header">
                <i class="fas fa-money-bill-wave"></i>
                <h2>Зарплата</h2>
            </div>
            
            <div class="salary-row">
                <div class="form-group">
                    <label for="salary_from" class="form-label">От</label>
                    <input type="number" id="salary_from" name="salary_from" class="form-control" step="1000" min="0"
                           value="<?php echo $vacancy['salary_from'] ? htmlspecialchars($vacancy['salary_from']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="salary_to" class="form-label">До</label>
                    <input type="number" id="salary_to" name="salary_to" class="form-control" step="1000" min="0"
                           value="<?php echo $vacancy['salary_to'] ? htmlspecialchars($vacancy['salary_to']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="salary_type" class="form-label">Тип оплаты</label>
                    <select id="salary_type" name="salary_type" class="form-control">
                        <option value="monthly" <?php echo $vacancy['salary_type'] == 'monthly' ? 'selected' : ''; ?>>В месяц</option>
                        <option value="hourly" <?php echo $vacancy['salary_type'] == 'hourly' ? 'selected' : ''; ?>>В час</option>
                        <option value="project" <?php echo $vacancy['salary_type'] == 'project' ? 'selected' : ''; ?>>За проект</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="form-group">
                    <label for="salary_note" class="form-label">Примечание к зарплате</label>
                    <input type="text" id="salary_note" name="salary_note" class="form-control"
                           value="<?php echo htmlspecialchars($vacancy['salary_note']); ?>"
                           placeholder="Например: + бонусы, обсуждается на собеседовании">
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group" style="margin-top: 28px;">
                        <input type="checkbox" id="is_negotiable" name="is_negotiable" value="1"
                               <?php echo $vacancy['is_negotiable'] ? 'checked' : ''; ?>>
                        <label for="is_negotiable">Зарплата по договоренности</label>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Локация -->
        <div class="form-section">
            <div class="section-header">
                <i class="fas fa-map-marker-alt"></i>
                <h2>Локация</h2>
            </div>
            
            <div class="checkbox-group" style="margin-bottom: 20px;">
                <input type="checkbox" id="is_remote" name="is_remote" value="1"
                       <?php echo $vacancy['is_remote'] ? 'checked' : ''; ?>>
                <label for="is_remote">Удаленная работа</label>
            </div>
            
            <div class="row" id="locationFields">
                <div class="form-group">
                    <label for="location_city" class="form-label">Город</label>
                    <input type="text" id="location_city" name="location_city" class="form-control"
                           value="<?php echo htmlspecialchars($vacancy['location_city']); ?>"
                           placeholder="Например: Санкт-Петербург">
                </div>
                
                <div class="form-group">
                    <label for="location_address" class="form-label">Адрес</label>
                    <input type="text" id="location_address" name="location_address" class="form-control"
                           value="<?php echo htmlspecialchars($vacancy['location_address']); ?>"
                           placeholder="Например: ул. Косметологов, 15">
                </div>
            </div>
        </div>
        
        <!-- Навыки и теги -->
        <div class="form-section">
            <div class="section-header">
                <i class="fas fa-tags"></i>
                <h2>Навыки и теги</h2>
            </div>
            
            <div class="form-group">
                <label class="form-label">Ключевые навыки</label>
                <div class="skills-list">
                    <?php mysqli_data_seek($all_skills_result, 0); ?>
                    <?php while($skill = mysqli_fetch_assoc($all_skills_result)): ?>
                        <div class="skill-item">
                            <div class="skill-header">
                                <div class="skill-name"><?php echo htmlspecialchars($skill['name']); ?></div>
                                <input type="checkbox" name="skills[]" value="<?php echo $skill['id']; ?>" 
                                       id="skill_<?php echo $skill['id']; ?>" class="skill-checkbox"
                                       <?php echo isset($selected_skills[$skill['id']]) ? 'checked' : ''; ?>>
                            </div>
                            <select name="skill_level_<?php echo $skill['id']; ?>" class="level-select" 
                                    <?php echo isset($selected_skills[$skill['id']]) ? '' : 'disabled'; ?>>
                                <option value="basic" <?php echo (isset($selected_skills[$skill['id']]) && $selected_skills[$skill['id']] == 'basic') ? 'selected' : ''; ?>>Базовый</option>
                                <option value="intermediate" <?php echo (isset($selected_skills[$skill['id']]) && $selected_skills[$skill['id']] == 'intermediate') ? 'selected' : ''; ?>>Средний</option>
                                <option value="advanced" <?php echo (isset($selected_skills[$skill['id']]) && $selected_skills[$skill['id']] == 'advanced') ? 'selected' : ''; ?>>Продвинутый</option>
                            </select>
                        </div>
                    <?php endwhile; ?>
                </div>
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
        </div>
        
        <!-- Процесс отбора -->
        <div class="form-section">
            <div class="section-header">
                <i class="fas fa-list-ol"></i>
                <h2>Процесс отбора</h2>
            </div>
            
            <div class="row">
                <div class="form-group">
                    <label for="application_deadline" class="form-label">Срок подачи заявок</label>
                    <input type="date" id="application_deadline" name="application_deadline" class="form-control"
                           value="<?php echo $vacancy['application_deadline'] ? date('Y-m-d', strtotime($vacancy['application_deadline'])) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="selection_process" class="form-label">Описание процесса отбора</label>
                    <textarea id="selection_process" name="selection_process" class="form-control" rows="3"><?php echo htmlspecialchars($vacancy['selection_process']); ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- Контактная информация -->
        <div class="form-section">
            <div class="section-header">
                <i class="fas fa-address-book"></i>
                <h2>Контактная информация</h2>
            </div>
            
            <div class="row">
                <div class="form-group">
                    <label for="contact_person" class="form-label">Контактное лицо</label>
                    <input type="text" id="contact_person" name="contact_person" class="form-control"
                           value="<?php echo htmlspecialchars($vacancy['contact_person']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="contact_email" class="form-label required">Email для откликов</label>
                    <input type="email" id="contact_email" name="contact_email" class="form-control" required
                           value="<?php echo htmlspecialchars($vacancy['contact_email']); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="contact_phone" class="form-label">Телефон для связи</label>
                <input type="tel" id="contact_phone" name="contact_phone" class="form-control"
                       value="<?php echo htmlspecialchars($vacancy['contact_phone']); ?>">
            </div>
        </div>
        
        <!-- SEO и публикация -->
        <div class="form-section">
            <div class="section-header">
                <i class="fas fa-search"></i>
                <h2>SEO и публикация</h2>
            </div>
            
            <div class="row">
                <div class="form-group">
                    <label for="meta_title" class="form-label">Meta Title</label>
                    <input type="text" id="meta_title" name="meta_title" class="form-control"
                           value="<?php echo htmlspecialchars($vacancy['meta_title']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="meta_description" class="form-label">Meta Description</label>
                    <textarea id="meta_description" name="meta_description" class="form-control" rows="2"><?php echo htmlspecialchars($vacancy['meta_description']); ?></textarea>
                </div>
            </div>
            
            <div class="form-group">
                <label for="meta_keywords" class="form-label">Meta Keywords</label>
                <input type="text" id="meta_keywords" name="meta_keywords" class="form-control"
                       value="<?php echo htmlspecialchars($vacancy['meta_keywords']); ?>"
                       placeholder="через запятую">
            </div>
            
            <div class="checkbox-group" style="margin-top: 20px;">
                <input type="checkbox" id="is_published" name="is_published" value="1"
                       <?php echo $vacancy['is_published'] ? 'checked' : ''; ?>>
                <label for="is_published">Опубликована</label>
            </div>
            
            <?php if ($vacancy['published_at']): ?>
                <div class="info-group" style="margin-top: 10px;">
                    <div class="info-label">Дата публикации</div>
                    <div class="info-value"><?php echo date('d.m.Y H:i', strtotime($vacancy['published_at'])); ?></div>
                </div>
            <?php endif; ?>
        </div>
        
        <div style="display: flex; gap: 15px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Сохранить изменения
            </button>
            <a href="view.php?id=<?php echo $vacancy_id; ?>" class="btn btn-outline">
                <i class="fas fa-times"></i> Отмена
            </a>
        </div>
    </form>
</div>
    </main>
    
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        // Инициализация редактора
        const descriptionEditor = new Quill('#descriptionEditor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link', 'image'],
                    ['clean']
                ]
            }
        });
        
        // Устанавливаем существующее содержимое
        descriptionEditor.root.innerHTML = `<?php echo addslashes($vacancy['description']); ?>`;
        
        // Синхронизация редактора с textarea
        descriptionEditor.on('text-change', function() {
            document.getElementById('description').value = descriptionEditor.root.innerHTML;
        });
        
        // Генерация slug из названия
        document.getElementById('title').addEventListener('input', function() {
            const title = this.value;
            const slug = title.toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/--+/g, '-')
                .trim();
            
            document.getElementById('slug').value = slug;
            updateSlugPreview();
            
            // Также обновляем meta title если он пустой
            if (!document.getElementById('meta_title').value) {
                document.getElementById('meta_title').value = title + ' - Вакансия - FlaxTap';
            }
        });
        
        // Обновление предпросмотра slug
        document.getElementById('slug').addEventListener('input', updateSlugPreview);
        
        function updateSlugPreview() {
            const slug = document.getElementById('slug').value;
            const preview = document.getElementById('slugPreview');
            preview.textContent = `/career/${slug}`;
            preview.href = `/career/${slug}`;
        }
        
        // Активация/деактивация полей локации
        function toggleLocationFields() {
            const locationFields = document.getElementById('locationFields');
            const isRemoteCheckbox = document.getElementById('is_remote');
            
            if (isRemoteCheckbox.checked) {
                locationFields.style.display = 'none';
            } else {
                locationFields.style.display = 'grid';
            }
        }
        
        // Управление уровнем навыков
        document.querySelectorAll('.skill-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const levelSelect = this.parentNode.parentNode.querySelector('.level-select');
                levelSelect.disabled = !this.checked;
                if (!this.checked) {
                    levelSelect.value = 'basic'; // Сброс значения
                }
            });
        });
        
        // Переключение состояния тегов при клике
        document.querySelectorAll('.tag-item').forEach(tag => {
            tag.addEventListener('click', function(e) {
                if (e.target.type !== 'checkbox') {
                    const checkbox = this.querySelector('input[type="checkbox"]');
                    checkbox.checked = !checkbox.checked;
                    this.classList.toggle('selected', checkbox.checked);
                }
            });
        });
        
        // Валидация формы
        document.getElementById('vacancyForm').addEventListener('submit', function(e) {
            // Синхронизируем редактор перед отправкой
            document.getElementById('description').value = descriptionEditor.root.innerHTML;
            
            // Проверяем заполненность обязательных полей
            const requiredFields = ['title', 'slug', 'contact_email'];
            let isValid = true;
            
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'var(--danger-color)';
                } else {
                    field.style.borderColor = '';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Заполните все обязательные поля');
            }
        });
        
        // Инициализация при загрузке
        document.addEventListener('DOMContentLoaded', function() {
            updateSlugPreview();
            
            // Инициализация полей локации
            toggleLocationFields();
            
            // Обработчик изменения чекбокса удаленной работы
            const isRemoteCheckbox = document.getElementById('is_remote');
            if (isRemoteCheckbox) {
                isRemoteCheckbox.addEventListener('change', toggleLocationFields);
            }
            
            // Активируем/деактивируем выпадающие списки навыков в зависимости от состояния чекбоксов
            document.querySelectorAll('.skill-checkbox').forEach(checkbox => {
                // Создаем событие change для инициализации
                const event = new Event('change');
                checkbox.dispatchEvent(event);
            });
        });
    </script>
</body>
</html>