<?php
ob_start(); // Включаем буферизацию вывода
include 'config/database.php';
// Функция для безопасного вывода
function escape($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// ОБРАБОТЧИК ОТКЛИКА
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_name'])) {
    $name = trim($_POST['app_name']);
    $phone = trim($_POST['app_phone']);
    $email = trim($_POST['app_email']);
    $position = trim($_POST['app_position']);
    
    if (!empty($name) && !empty($phone) && !empty($email) && !empty($position)) {
        // Найдем ID вакансии по названию
        $vacancy_id = 0; // значение по умолчанию
        $find_sql = "SELECT id FROM vacancies WHERE title = '$position' LIMIT 1";
        $find_result = $connection->query($find_sql);
        if ($find_result && $find_result->num_rows > 0) {
            $row = $find_result->fetch_assoc();
            $vacancy_id = $row['id'];
        }
        
        $sql = "INSERT INTO vacancy_applications (vacancy_id, full_name, email, phone, cover_letter, created_at) 
                VALUES ('$vacancy_id', '$name', '$email', '$phone', '$position', NOW())";
        
        if ($connection->query($sql)) {
            $success = true;
        } else {
            $sql2 = "INSERT INTO vacancy_applications (full_name, email, phone, cover_letter, created_at) 
                    VALUES ('$name', '$email', '$phone', '$position', NOW())";
            $connection->query($sql2);
            $success = true;
        }
    }
}

// ПОЛУЧАЕМ ВАКАНСИИ
$vacancies_sql = "SELECT * FROM vacancies WHERE status = 'active' ORDER BY created_at DESC";
$vacancies_result = $connection->query($vacancies_sql);
$vacancies = [];
if ($vacancies_result) {
    while ($row = $vacancies_result->fetch_assoc()) {
        $vacancies[] = $row;
    }
}

// ПОЛУЧАЕМ СПИСОК ВАКАНСИЙ ДЛЯ ФОРМЫ
$titles_sql = "SELECT DISTINCT title FROM vacancies WHERE status = 'active' ORDER BY title";
$titles_result = $connection->query($titles_sql);
$titles = [];
if ($titles_result) {
    while ($row = $titles_result->fetch_assoc()) {
        $titles[] = $row['title'];
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вакансии - FlaxTap</title>
    <link rel="stylesheet" href="assets/style/style.css">
    <link rel="stylesheet" href="assets/style/vacancies.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include "inc/header.php"; ?>

    <!-- Герой секция -->
    <section class="hero-slider" style="margin-bottom:0; height:100%;">
        <div class="slider-container" style="height: 400px;">
            <div class="slider-wrapper">
                <div class="slide active" style="background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/media/carousel.jpg') center/cover no-repeat;">
                    <div class="slide-content">
                        <h1 class="slide-title">Присоединяйтесь <span class="highlight">к команде</span></h1>
                        <p class="slide-text">Станьте частью лидера на рынке профессиональной косметики</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Секция преимуществ работы -->
    <section class="about-section improved">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Почему работают у нас</h2>
                <p class="section-subtitle">Мы создаем лучшие условия для профессионального роста</p>
            </div>
            
            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Карьерный рост</h3>
                    <p>Четкая система грейдов и возможность роста от специалиста до руководителя отдела</p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3>Обучение за счет компании</h3>
                    <p>Корпоративные тренинги, внешнее обучение и участие в отраслевых конференциях</p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Медицинская страховка</h3>
                    <p>Полис ДМС с расширенной программой для сотрудников и скидки на косметику</p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-sun"></i>
                    </div>
                    <h3>Гибкий график</h3>
                    <p>Возможность гибкого графика работы и удаленного формата для некоторых позиций</p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-glass-cheers"></i>
                    </div>
                    <h3>Корпоративная жизнь</h3>
                    <p>Тимбилдинги, праздничные мероприятия и спортивные активности</p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-coffee"></i>
                    </div>
                    <h3>Комфортный офис</h3>
                    <p>Современное рабочее пространство, кухня с напитками и зоны отдыха</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Секция актуальных вакансий -->
    <section class="faq-section improved" style="background: linear-gradient(135deg, #f9fdff 0%, #ffffff 100%);">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Актуальные вакансии</h2>
                <p class="section-subtitle">Выберите подходящую позицию и отправьте резюме</p>
            </div>
            
            <div class="vacancies-filter">
                <div class="filter-tabs">
                    <button class="filter-tab active" data-filter="all">Все вакансии</button>
                    <button class="filter-tab" data-filter="office">Офис</button>
                    <button class="filter-tab" data-filter="remote">Удаленно</button>
                    <button class="filter-tab" data-filter="sales">Продажи</button>
                    <button class="filter-tab" data-filter="marketing">Маркетинг</button>
                </div>
                
                <div class="search-box">
                    <input type="text" placeholder="Поиск по названию вакансии..." id="vacancySearch">
                    <button class="search-btn"><i class="fas fa-search"></i></button>
                </div>
            </div>
            
            <div class="vacancies-list">
                <?php if (empty($vacancies)): ?>
                    <!-- Вакансия 1 -->
                    <div class="vacancy-card" data-category="sales office">
                        <div class="vacancy-header">
                            <div class="vacancy-title">
                                <h3>Менеджер по продажам</h3>
                                <span class="vacancy-badge office">Офис</span>
                                <span class="vacancy-badge urgent">Срочно</span>
                            </div>
                            <div class="vacancy-salary">
                                <span class="salary">от 80 000 ₽</span>
                                <span class="salary-note">+ бонусы до 120%</span>
                            </div>
                        </div>
                        
                        <div class="vacancy-body">
                            <div class="vacancy-info">
                                <div class="info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>Санкт-Петербург, офис</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-briefcase"></i>
                                    <span>Опыт от 1 года</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-clock"></i>
                                    <span>Полный день</span>
                                </div>
                            </div>
                            
                            <div class="vacancy-description">
                                <p>Ищем активного менеджера по продажам для работы с существующей клиентской базой и привлечения новых партнеров.</p>
                                <div class="vacancy-tags">
                                    <span class="tag">B2B продажи</span>
                                    <span class="tag">Косметика</span>
                                    <span class="tag">Веду переговоры</span>
                                </div>
                            </div>
                            
                            <div class="vacancy-requirements">
                                <h4>Требования:</h4>
                                <ul>
                                    <li>Опыт продаж в B2B от 1 года</li>
                                    <li>Умение вести переговоры и закрывать сделки</li>
                                    <li>Знание CRM-систем (Bitrix24, AmoCRM)</li>
                                    <li>Навыки презентации продукции</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="vacancy-footer">
                            <button class="btn btn-outline vacancy-details-btn">Подробнее</button>
                            <button class="btn btn-primary apply-btn" data-vacancy="Менеджер по продажам">Откликнуться</button>
                        </div>
                    </div>
                    
                    <!-- Вакансия 2 -->
                    <div class="vacancy-card" data-category="marketing remote">
                        <div class="vacancy-header">
                            <div class="vacancy-title">
                                <h3>SMM-специалист</h3>
                                <span class="vacancy-badge remote">Удаленно</span>
                            </div>
                            <div class="vacancy-salary">
                                <span class="salary">от 60 000 ₽</span>
                                <span class="salary-note">по результатам собеседования</span>
                            </div>
                        </div>
                        
                        <div class="vacancy-body">
                            <div class="vacancy-info">
                                <div class="info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>Удаленная работа</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-briefcase"></i>
                                    <span>Опыт от 2 лет</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-clock"></i>
                                    <span>Гибкий график</span>
                                </div>
                            </div>
                            
                            <div class="vacancy-description">
                                <p>Нужен креативный SMM-специалист для ведения социальных сетей бренда и привлечения новой аудитории.</p>
                                <div class="vacancy-tags">
                                    <span class="tag">SMM</span>
                                    <span class="tag">Контент</span>
                                    <span class="tag">Instagram</span>
                                    <span class="tag">ВКонтакте</span>
                                </div>
                            </div>
                            
                            <div class="vacancy-requirements">
                                <h4>Требования:</h4>
                                <ul>
                                    <li>Опыт ведения соцсетей бьюти-бренда</li>
                                    <li>Портфолио с результатами работ</li>
                                    <li>Знание графических редакторов (Photoshop, Figma)</li>
                                    <li>Умение анализировать метрики и строить стратегии</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="vacancy-footer">
                            <button class="btn btn-outline vacancy-details-btn">Подробнее</button>
                            <button class="btn btn-primary apply-btn" data-vacancy="SMM-специалист">Откликнуться</button>
                        </div>
                    </div>
                    
                    <!-- Вакансия 3 -->
                    <div class="vacancy-card" data-category="sales remote">
                        <div class="vacancy-header">
                            <div class="vacancy-title">
                                <h3>Менеджер по работе с клиентами</h3>
                                <span class="vacancy-badge remote">Удаленно</span>
                                <span class="vacancy-badge new">Новая</span>
                            </div>
                            <div class="vacancy-salary">
                                <span class="salary">от 55 000 ₽</span>
                                <span class="salary-note">+ премии по KPI</span>
                            </div>
                        </div>
                        
                        <div class="vacancy-body">
                            <div class="vacancy-info">
                                <div class="info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>Удаленная работа</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-briefcase"></i>
                                    <span>Опыт от 6 месяцев</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-clock"></i>
                                    <span>Сменный график</span>
                                </div>
                            </div>
                            
                            <div class="vacancy-description">
                                <p>Консультирование клиентов по продукции, обработка входящих обращений, помощь в подборе косметики.</p>
                                <div class="vacancy-tags">
                                    <span class="tag">Консультации</span>
                                    <span class="tag">Онлайн   чат</span>
                                    <span class="tag">Клиентский сервис</span>
                                </div>
                            </div>
                            
                            <div class="vacancy-requirements">
                                <h4>Требования:</h4>
                                <ul>
                                    <li>Грамотная речь и письмо</li>
                                    <li>Опыт работы в поддержке или продажах</li>
                                    <li>Базовые знания косметологии (будет преимуществом)</li>
                                    <li>Умение работать в multitasking режиме</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="vacancy-footer">
                            <button class="btn btn-outline vacancy-details-btn">Подробнее</button>
                            <button class="btn btn-primary apply-btn" data-vacancy="Менеджер по работе с клиентами">Откликнуться</button>
                        </div>
                    </div>
                    
                    <!-- Вакансия 4 -->
                    <div class="vacancy-card" data-category="office">
                        <div class="vacancy-header">
                            <div class="vacancy-title">
                                <h3>Маркетолог</h3>
                                <span class="vacancy-badge office">Офис</span>
                            </div>
                            <div class="vacancy-salary">
                                <span class="salary">от 90 000 ₽</span>
                                <span class="salary-note">по результатам собеседования</span>
                            </div>
                        </div>
                        
                        <div class="vacancy-body">
                            <div class="vacancy-info">
                                <div class="info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>Санкт-Петербург, офис</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-briefcase"></i>
                                    <span>Опыт от 3 лет</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-clock"></i>
                                    <span>Полный день</span>
                                </div>
                            </div>
                            
                            <div class="vacancy-description">
                                <p>Разработка и реализация маркетинговой стратегии бренда, анализ рынка, планирование рекламных кампаний.</p>
                                <div class="vacancy-tags">
                                    <span class="tag">Маркетинг</span>
                                    <span class="tag">Аналитика</span>
                                    <span class="tag">Стратегия</span>
                                    <span class="tag">Реклама</span>
                                </div>
                            </div>
                            
                            <div class="vacancy-requirements">
                                <h4>Требования:</h4>
                                <ul>
                                    <li>Опыт в маркетинге FMCG или beauty-индустрии</li>
                                    <li>Знание digital-инструментов и аналитики</li>
                                    <li>Опыт управления бюджетами</li>
                                    <li>Умение строить воронки продаж</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="vacancy-footer">
                            <button class="btn btn-outline vacancy-details-btn">Подробнее</button>
                            <button class="btn btn-primary apply-btn" data-vacancy="Маркетолог">Откликнуться</button>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($vacancies as $vacancy): ?>
                        <div class="vacancy-card" data-category="<?php echo ($vacancy['work_type'] == 'remote' || $vacancy['is_remote']) ? 'remote' : 'office'; ?>">
                            <div class="vacancy-header">
                                <div class="vacancy-title">
                                    <h3><?php echo htmlspecialchars($vacancy['title']); ?></h3>
                                    <?php if ($vacancy['work_type'] == 'remote' || $vacancy['is_remote']): ?>
                                        <span class="vacancy-badge remote">Удаленно</span>
                                    <?php else: ?>
                                        <span class="vacancy-badge office">Офис</span>
                                    <?php endif; ?>
                                    <?php if ($vacancy['badges']): ?>
                                        <span class="vacancy-badge"><?php echo htmlspecialchars($vacancy['badges']); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="vacancy-salary">
                                    <?php if ($vacancy['salary_from'] || $vacancy['salary_to']): ?>
                                        <span class="salary">
                                            <?php 
                                            if ($vacancy['salary_from'] && $vacancy['salary_to']) {
                                                echo 'от ' . number_format($vacancy['salary_from'], 0, '', ' ') . ' до ' . number_format($vacancy['salary_to'], 0, '', ' ') . ' ' . ($vacancy['salary_currency'] ?: '₽');
                                            } elseif ($vacancy['salary_from']) {
                                                echo 'от ' . number_format($vacancy['salary_from'], 0, '', ' ') . ' ' . ($vacancy['salary_currency'] ?: '₽');
                                            } elseif ($vacancy['salary_to']) {
                                                echo 'до ' . number_format($vacancy['salary_to'], 0, '', ' ') . ' ' . ($vacancy['salary_currency'] ?: '₽');
                                            }
                                            ?>
                                        </span>
                                        <?php if ($vacancy['salary_note']): ?>
                                            <span class="salary-note"><?php echo htmlspecialchars($vacancy['salary_note']); ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="salary">По договоренности</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="vacancy-body">
                                <div class="vacancy-info">
                                    <div class="info-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>
                                            <?php if ($vacancy['work_type'] == 'remote' || $vacancy['is_remote']): ?>
                                                Удаленная работа
                                            <?php else: ?>
                                                <?php echo htmlspecialchars($vacancy['location_city'] ?: 'Санкт-Петербург'); ?>, офис
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <?php if ($vacancy['experience_level']): ?>
                                        <div class="info-item">
                                            <i class="fas fa-briefcase"></i>
                                            <span>
                                                <?php 
                                                switch ($vacancy['experience_level']) {
                                                    case 'no_experience': echo 'Без опыта'; break;
                                                    case 'junior': echo 'Опыт от 6 месяцев'; break;
                                                    case 'middle': echo 'Опыт от 1 года'; break;
                                                    case 'senior': echo 'Опыт от 3 лет'; break;
                                                    case 'lead': echo 'Опыт от 5 лет'; break;
                                                    default: echo 'Опыт по договоренности';
                                                }
                                                ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($vacancy['employment_type']): ?>
                                        <div class="info-item">
                                            <i class="fas fa-clock"></i>
                                            <span>
                                                <?php 
                                                switch ($vacancy['employment_type']) {
                                                    case 'full_time': echo 'Полный день'; break;
                                                    case 'part_time': echo 'Частичная занятость'; break;
                                                    case 'freelance': echo 'Проектная работа'; break;
                                                    case 'internship': echo 'Стажировка'; break;
                                                    default: echo 'Полный день';
                                                }
                                                ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($vacancy['description']): ?>
                                    <div class="vacancy-description">
                                        <p><?php echo nl2br(htmlspecialchars($vacancy['description'])); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($vacancy['department']): ?>
                                    <div class="vacancy-tags">
                                        <span class="tag"><?php echo htmlspecialchars($vacancy['department']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($vacancy['requirements']): ?>
                                    <div class="vacancy-requirements">
                                        <h4>Требования:</h4>
                                        <ul>
                                            <?php 
                                            $requirements = explode("\n", $vacancy['requirements']);
                                            foreach ($requirements as $req):
                                                if (trim($req)): ?>
                                                    <li><?php echo htmlspecialchars(trim($req)); ?></li>
                                            <?php endif;
                                            endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="vacancy-footer">
                                <button class="btn btn-outline vacancy-details-btn">Подробнее</button>
                                <button class="btn btn-primary apply-btn" data-vacancy="<?php echo htmlspecialchars($vacancy['title']); ?>">Откликнуться</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="no-vacancies" style="display: none;">
                <div class="empty-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Вакансии не найдены</h3>
                <p>Попробуйте изменить параметры поиска или загляните позже</p>
            </div>
        </div>
    </section>

    <!-- Секция процесса отбора -->
    <section class="about-section improved">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Как устроен процесс отбора</h2>
                <p class="section-subtitle">Прозрачные этапы от отклика до трудоустройства</p>
            </div>
            
            <div class="process-steps">
                <div class="process-step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h4>Отклик на вакансию</h4>
                        <p>Заполните форму на сайте или отправьте резюме на почту</p>
                    </div>
                </div>
                
                <div class="process-step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h4>Первичный отбор</h4>
                        <p>Наш HR-специалист свяжется с вами в течение 3 рабочих дней</p>
                    </div>
                </div>
                
                <div class="process-step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h4>Собеседование</h4>
                        <p>Встреча с руководителем отдела и тестовое задание</p>
                    </div>
                </div>
                
                <div class="process-step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h4>Решение и оффер</h4>
                        <p>Мы сообщим решение в течение 2 дней и отправим оффер</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Секция формы отклика -->
    <section class="faq-section improved" id="applicationForm" style="background: linear-gradient(135deg, #fffbf0 0%, #ffffff 100%);">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Откликнуться на вакансию</h2>
                <p class="section-subtitle">Заполните форму и мы свяжемся с вами в ближайшее время</p>
            </div>
            
            <div class="application-form-section">
                <form class="application-form" id="vacancyForm" method="POST">
                    <?php if (isset($success) && $success): ?>
                        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                            ✓ Ваш отклик успешно отправлен!
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="appName">ФИО *</label>
                            <input type="text" id="appName" name="app_name" placeholder="Иванов Иван Иванович" required>
                        </div>
                        <div class="form-group">
                            <label for="appPhone">Телефон *</label>
                            <input type="tel" id="appPhone" name="app_phone" placeholder="+7 (999) 123-45-67" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="appEmail">Email *</label>
                            <input type="email" id="appEmail" name="app_email" placeholder="example@mail.ru" required>
                        </div>
                        <div class="form-group">
                            <label for="appPosition">Вакансия *</label>
                            <select id="appPosition" name="app_position" required>
                                <option value="">Выберите вакансию</option>
                                <?php foreach ($titles as $title): ?>
                                    <option value="<?php echo htmlspecialchars($title); ?>"><?php echo htmlspecialchars($title); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-paper-plane"></i> Отправить отклик
                    </button>
                </form>
                
                <div class="form-sidebar">
                    <div class="sidebar-card">
                        <div class="card-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <h4>Частые вопросы</h4>
                        <div class="faq-item">
                            <div class="faq-question">Сколько времени занимает рассмотрение резюме?</div>
                            <div class="faq-answer">Мы рассматриваем все резюме в течение 3 рабочих дней.</div>
                        </div>
                        <div class="faq-item">
                            <div class="faq-question">Нужно ли присылать портфолио?</div>
                            <div class="faq-answer">Для творческих вакансий портфолио обязательно.</div>
                        </div>
                    </div>
                    
                    <div class="sidebar-card">
                        <div class="card-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h4>Альтернативный способ</h4>
                        <p>Вы также можете отправить резюме напрямую на почту:</p>
                        <a href="mailto:hr@flaxtap.ru" class="email-link">hr@flaxtap.ru</a>
                        <p class="note">В теме письма укажите название вакансии</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include "inc/modal.php"; ?>
    <?php include "inc/footer.php"; ?>

    <script src="assets/js/vacancies.js"></script>
    <script src="assets/js/nav.js"></script>
    <script src="assets/js/modals.js"></script>
    
    <script>
        // Обработка кликов на кнопки "Откликнуться"
        document.addEventListener('DOMContentLoaded', function() {
            const applyButtons = document.querySelectorAll('.apply-btn');
            const positionSelect = document.getElementById('appPosition');
            
            applyButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const vacancyTitle = this.getAttribute('data-vacancy');
                    
                    // Устанавливаем значение в select
                    for (let i = 0; i < positionSelect.options.length; i++) {
                        if (positionSelect.options[i].value === vacancyTitle) {
                            positionSelect.selectedIndex = i;
                            break;
                        }
                    }
                    
                    // Прокручиваем к форме
                    document.getElementById('applicationForm').scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });
        });
    </script>
</body>
</html>
