<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

$error = '';
$success = '';

// Получаем категории
$categories_query = "SELECT * FROM vacancy_categories WHERE is_active = 1 ORDER BY sort_order";
$categories_result = mysqli_query($conn, $categories_query);

// Получаем навыки
$skills_query = "SELECT * FROM vacancy_skills ORDER BY name";
$skills_result = mysqli_query($conn, $skills_query);

// Получаем теги
$tags_query = "SELECT * FROM vacancy_tags ORDER BY name";
$tags_result = mysqli_query($conn, $tags_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Основные данные
    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $slug = mysqli_real_escape_string($conn, trim($_POST['slug']));
    $category_id = (int)$_POST['category_id'];
    $status = $_POST['status'];
    $priority = $_POST['priority'];
    $work_type = $_POST['work_type'];
    $employment_type = $_POST['employment_type'];
    $experience_level = $_POST['experience_level'];
    
    // Описание
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $responsibilities = mysqli_real_escape_string($conn, trim($_POST['responsibilities']));
    $requirements = mysqli_real_escape_string($conn, trim($_POST['requirements']));
    $benefits = mysqli_real_escape_string($conn, trim($_POST['benefits']));
    
    // Зарплата
    $salary_from = !empty($_POST['salary_from']) ? (float)$_POST['salary_from'] : NULL;
    $salary_to = !empty($_POST['salary_to']) ? (float)$_POST['salary_to'] : NULL;
    $salary_type = $_POST['salary_type'];
    $salary_note = mysqli_real_escape_string($conn, trim($_POST['salary_note']));
    $is_negotiable = isset($_POST['is_negotiable']) ? 1 : 0;
    
    // Локация
    $location_city = mysqli_real_escape_string($conn, trim($_POST['location_city']));
    $location_address = mysqli_real_escape_string($conn, trim($_POST['location_address']));
    $is_remote = isset($_POST['is_remote']) ? 1 : 0;
    
    // Дополнительно
    $education = mysqli_real_escape_string($conn, trim($_POST['education']));
    $languages = mysqli_real_escape_string($conn, trim($_POST['languages']));
    $department = mysqli_real_escape_string($conn, trim($_POST['department']));
    $working_hours = mysqli_real_escape_string($conn, trim($_POST['working_hours']));
    $selection_process = mysqli_real_escape_string($conn, trim($_POST['selection_process']));
    $application_deadline = !empty($_POST['application_deadline']) ? $_POST['application_deadline'] : NULL;
    
    // Контакты
    $contact_person = mysqli_real_escape_string($conn, trim($_POST['contact_person']));
    $contact_email = mysqli_real_escape_string($conn, trim($_POST['contact_email']));
    $contact_phone = mysqli_real_escape_string($conn, trim($_POST['contact_phone']));
    
    // Мета-данные
    $meta_title = mysqli_real_escape_string($conn, trim($_POST['meta_title']));
    $meta_description = mysqli_real_escape_string($conn, trim($_POST['meta_description']));
    $meta_keywords = mysqli_real_escape_string($conn, trim($_POST['meta_keywords']));
    
    // Статус публикации
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $published_at = $is_published ? date('Y-m-d H:i:s') : NULL;
    
    // Навыки и теги
    $selected_skills = $_POST['skills'] ?? [];
    $selected_tags = $_POST['tags'] ?? [];
    
    // Валидация
    if (empty($title) || empty($slug)) {
        $error = 'Заполните обязательные поля: Название и URL';
    } else {
        // Проверяем уникальность slug
        $check_query = "SELECT id FROM vacancies WHERE slug = '$slug'";
        $check_result = mysqli_query($conn, $check_query);
        if (mysqli_num_rows($check_result) > 0) {
            $error = 'Вакансия с таким URL уже существует';
        } else {
            // Начинаем транзакцию
            mysqli_begin_transaction($conn);
            
            try {
                // Вставляем вакансию
                $insert_query = "INSERT INTO vacancies (
                    title, slug, category_id, status, priority, work_type, employment_type,
                    experience_level, description, responsibilities, requirements, benefits,
                    salary_from, salary_to, salary_type, salary_note, is_negotiable,
                    location_city, location_address, is_remote, education, languages,
                    department, working_hours, selection_process, application_deadline,
                    contact_person, contact_email, contact_phone,
                    meta_title, meta_description, meta_keywords,
                    is_published, published_at, created_by, updated_by, created_at, updated_at
                ) VALUES (
                    '$title',
                    '$slug',
                    " . ($category_id ? $category_id : "NULL") . ",
                    '$status',
                    '$priority',
                    '$work_type',
                    '$employment_type',
                    '$experience_level',
                    '$description',
                    '$responsibilities',
                    '$requirements',
                    '$benefits',
                    " . ($salary_from ? $salary_from : "NULL") . ",
                    " . ($salary_to ? $salary_to : "NULL") . ",
                    '$salary_type',
                    '$salary_note',
                    $is_negotiable,
                    '$location_city',
                    '$location_address',
                    $is_remote,
                    '$education',
                    '$languages',
                    '$department',
                    '$working_hours',
                    '$selection_process',
                    " . ($application_deadline ? "'$application_deadline'" : "NULL") . ",
                    '$contact_person',
                    '$contact_email',
                    '$contact_phone',
                    '$meta_title',
                    '$meta_description',
                    '$meta_keywords',
                    $is_published,
                    " . ($published_at ? "'$published_at'" : "NULL") . ",
                    " . $_SESSION['admin_id'] . ",
                    " . $_SESSION['admin_id'] . ",
                    NOW(),
                    NOW()
                )";
                
                if (!mysqli_query($conn, $insert_query)) {
                    throw new Exception('Ошибка при создании вакансии: ' . mysqli_error($conn));
                }
                
                $new_vacancy_id = mysqli_insert_id($conn);
                
                // Добавляем навыки
                foreach ($selected_skills as $skill_id) {
                    $skill_id = (int)$skill_id;
                    $level = $_POST['skill_level_' . $skill_id] ?? 'basic';
                    
                    $insert_skill = "INSERT INTO vacancy_skill_pivot (vacancy_id, skill_id, level) 
                                    VALUES ($new_vacancy_id, $skill_id, '$level')";
                    
                    if (!mysqli_query($conn, $insert_skill)) {
                        throw new Exception('Ошибка при добавлении навыка: ' . mysqli_error($conn));
                    }
                }
                
                // Добавляем теги
                foreach ($selected_tags as $tag_id) {
                    $tag_id = (int)$tag_id;
                    
                    $insert_tag = "INSERT INTO vacancy_tag_pivot (vacancy_id, tag_id) 
                                  VALUES ($new_vacancy_id, $tag_id)";
                    
                    if (!mysqli_query($conn, $insert_tag)) {
                        throw new Exception('Ошибка при добавлении тега: ' . mysqli_error($conn));
                    }
                }
                
                // Фиксируем транзакцию
                mysqli_commit($conn);
                
                // Логируем действие
                $admin_name = $_SESSION['user_name'] ?? $_SESSION['admin_name'] ?? 'Неизвестный';
                $log_message = date('Y-m-d H:i:s') . " - Администратор {$admin_name} создал новую вакансию: {$title} (ID: $new_vacancy_id)\n";
                file_put_contents('../admin_log.txt', $log_message, FILE_APPEND);
                
                $success = 'Вакансия успешно создана!';
                
                // Редирект на редактирование
                $_SESSION['success'] = $success;
                header("Location: edit.php?id=$new_vacancy_id");
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
    <title>Создание вакансии - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style/vacancies/create.css">
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Создание новой вакансии</h1>
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
                            <input type="text" id="title" name="title" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="slug" class="form-label required">URL (slug)</label>
                            <input type="text" id="slug" name="slug" class="form-control" required>
                            <div class="slug-preview">
                                Ссылка: <a href="#" id="slugPreview" target="_blank"></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
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
                        
                        <div class="form-group">
                            <label for="department" class="form-label">Отдел/Департамент</label>
                            <input type="text" id="department" name="department" class="form-control">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="status" class="form-label required">Статус</label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="draft">Черновик</option>
                                <option value="active">Активная</option>
                                <option value="archived">Архив</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="priority" class="form-label">Приоритет</label>
                            <select id="priority" name="priority" class="form-control">
                                <option value="normal">Обычный</option>
                                <option value="urgent">Срочный</option>
                                <option value="low">Низкий</option>
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
                            <div id="descriptionEditor"></div>
                        </div>
                        <textarea id="description" name="description" style="display: none;"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="responsibilities" class="form-label">Обязанности</label>
                        <textarea id="responsibilities" name="responsibilities" class="form-control"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="requirements" class="form-label">Требования</label>
                        <textarea id="requirements" name="requirements" class="form-control"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="benefits" class="form-label">Мы предлагаем</label>
                        <textarea id="benefits" name="benefits" class="form-control"></textarea>
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
                                <option value="office">Офис</option>
                                <option value="remote">Удаленно</option>
                                <option value="hybrid">Гибрид</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="employment_type" class="form-label required">Тип занятости</label>
                            <select id="employment_type" name="employment_type" class="form-control" required>
                                <option value="full_time">Полная занятость</option>
                                <option value="part_time">Частичная занятость</option>
                                <option value="freelance">Фриланс</option>
                                <option value="internship">Стажировка</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="experience_level" class="form-label">Уровень опыта</label>
                            <select id="experience_level" name="experience_level" class="form-control">
                                <option value="no_experience">Без опыта</option>
                                <option value="junior">Junior</option>
                                <option value="middle" selected>Middle</option>
                                <option value="senior">Senior</option>
                                <option value="lead">Lead</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="working_hours" class="form-label">График работы</label>
                            <input type="text" id="working_hours" name="working_hours" class="form-control" placeholder="Например: 5/2, с 9:00 до 18:00">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="education" class="form-label">Образование</label>
                        <input type="text" id="education" name="education" class="form-control" placeholder="Например: Высшее образование">
                    </div>
                    
                    <div class="form-group">
                        <label for="languages" class="form-label">Языки</label>
                        <input type="text" id="languages" name="languages" class="form-control" placeholder="Например: Русский (носитель), Английский (B1)">
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
                            <input type="number" id="salary_from" name="salary_from" class="form-control" step="1000" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="salary_to" class="form-label">До</label>
                            <input type="number" id="salary_to" name="salary_to" class="form-control" step="1000" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="salary_type" class="form-label">Тип оплаты</label>
                            <select id="salary_type" name="salary_type" class="form-control">
                                <option value="monthly">В месяц</option>
                                <option value="hourly">В час</option>
                                <option value="project">За проект</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="salary_note" class="form-label">Примечание к зарплате</label>
                            <input type="text" id="salary_note" name="salary_note" class="form-control" placeholder="Например: + бонусы, обсуждается на собеседовании">
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox-group" style="margin-top: 28px;">
                                <input type="checkbox" id="is_negotiable" name="is_negotiable" value="1">
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
                        <input type="checkbox" id="is_remote" name="is_remote" value="1">
                        <label for="is_remote">Удаленная работа</label>
                    </div>
                    
                    <div class="row" id="locationFields">
                        <div class="form-group">
                            <label for="location_city" class="form-label">Город</label>
                            <input type="text" id="location_city" name="location_city" class="form-control" placeholder="Например: Санкт-Петербург">
                        </div>
                        
                        <div class="form-group">
                            <label for="location_address" class="form-label">Адрес</label>
                            <input type="text" id="location_address" name="location_address" class="form-control" placeholder="Например: ул. Косметологов, 15">
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
                            <?php mysqli_data_seek($skills_result, 0); ?>
                            <?php while($skill = mysqli_fetch_assoc($skills_result)): ?>
                                <div class="skill-item">
                                    <div class="skill-header">
                                        <div class="skill-name"><?php echo htmlspecialchars($skill['name']); ?></div>
                                        <input type="checkbox" name="skills[]" value="<?php echo $skill['id']; ?>" 
                                               id="skill_<?php echo $skill['id']; ?>" class="skill-checkbox">
                                    </div>
                                    <select name="skill_level_<?php echo $skill['id']; ?>" class="level-select" disabled>
                                        <option value="basic">Базовый</option>
                                        <option value="intermediate">Средний</option>
                                        <option value="advanced">Продвинутый</option>
                                    </select>
                                </div>
                            <?php endwhile; ?>
                        </div>
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
                            <input type="date" id="application_deadline" name="application_deadline" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="selection_process" class="form-label">Описание процесса отбора</label>
                            <textarea id="selection_process" name="selection_process" class="form-control" rows="3"></textarea>
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
                            <input type="text" id="contact_person" name="contact_person" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_email" class="form-label">Email для откликов</label>
                            <input type="email" id="contact_email" name="contact_email" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="contact_phone" class="form-label">Телефон для связи</label>
                        <input type="tel" id="contact_phone" name="contact_phone" class="form-control">
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
                            <input type="text" id="meta_title" name="meta_title" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="meta_description" class="form-label">Meta Description</label>
                            <textarea id="meta_description" name="meta_description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="meta_keywords" class="form-label">Meta Keywords</label>
                        <input type="text" id="meta_keywords" name="meta_keywords" class="form-control" placeholder="через запятую">
                    </div>
                    
                    <div class="checkbox-group" style="margin-top: 20px;">
                        <input type="checkbox" id="is_published" name="is_published" value="1">
                        <label for="is_published">Опубликовать сразу</label>
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Создать вакансию
                    </button>
                    <button type="button" onclick="saveDraft()" class="btn btn-outline">
                        <i class="fas fa-save"></i> Сохранить черновик
                    </button>
                </div>
            </form>
        </div>
    </main>
    
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="../assets/js/vacancies/create.js"></script>
</body>
</html>