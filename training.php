<?php
ob_start(); // Включаем буферизацию вывода
// ПЕРВЫМ ДЕЛОМ подключаем конфигурацию базы данных
include 'config/database.php';

// Начинаем сессию
session_start();

// Функция для безопасного вывода
function escape($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// ============================================
// ОБРАБОТКА ФОРМЫ ОБУЧЕНИЯ 
// ============================================
$form_success = false;
$form_error = '';

// Проверяем, была ли отправлена форма 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['training_submit'])) {
    
    // Получаем данные 
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $social_link = $_POST['social_link'] ?? '';
    $message = $_POST['message'] ?? '';
    // Согласие проверяется через HTML required, не сохраняем в БД
    
    $course_type = $_POST['course_type'] ?? 'Обучение FlaxTap';
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    

    if (!empty($name) && !empty($phone) && !empty($email) && !empty($message)) {
        $sql = "INSERT INTO training_requests 
                (name, phone, email, social_link, message, course_type, 
                 status, ip_address, user_agent, created_at, is_read, priority) 
                VALUES (
                    '" . $connection->real_escape_string($name) . "', 
                    '" . $connection->real_escape_string($phone) . "', 
                    '" . $connection->real_escape_string($email) . "', 
                    '" . $connection->real_escape_string($social_link) . "', 
                    '" . $connection->real_escape_string($message) . "', 
                    '" . $connection->real_escape_string($course_type) . "', 
                    'new',
                    '" . $connection->real_escape_string($ip_address) . "', 
                    '" . $connection->real_escape_string($user_agent) . "', 
                    NOW(),
                    0,
                    'normal'
                )";
        
        if ($connection->query($sql)) {
            $form_success = true;
            
            // Сохраняем в сессии 
            $_SESSION['last_training_request'] = [
                'time' => time(),
                'name' => $name,
                'email' => $email
            ];
            
        } else {
            $form_error = "Ошибка отправки. Пожалуйста, попробуйте позже.";
        }
    } else {
        $form_error = "Заполните все обязательные поля!";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Обучение - FlaxTap</title>
    <link rel="stylesheet" href="assets/style/training.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .field-error {
            color: #e74c3c !important;
            font-size: 0.85rem !important;
            margin-top: 5px !important;
        }

        input.error,
        textarea.error {
            border-color: #e74c3c !important;
            box-shadow: 0 0 0 2px rgba(231, 76, 60, 0.1) !important;
        }

        button[type="reset"]:hover {
            background-color: #e74c3c !important;
            color: white !important;
            border-color: #e74c3c !important;
        }
    </style>
</head>
<body class="training-page">
    <!-- Header -->
    <header class="training-header">
        <a href="index.php">
            <img src="assets/media/logo/logo-not.png" alt="FlaxTap">
        </a>
    </header>

    <!-- Welcome Section -->
    <section class="welcome-section">
        <div class="training-container">
            <img src="assets/media/logo/logo.png" alt="FlaxTap Academy" class="welcome-logo">
            <h1 class="training-title">Добро пожаловать!</h1>
            <p class="training-subtitle">
                Добро пожаловать в официальное представительство международной академии взгляда Марины Беляевой.
            </p>
            <p class="training-text">
                Мы разработали уникальную методику работы с кожей, получившую название FlaxTap® - это авторская техника микроблейдинга, 
                которая выполняется специальными сертифицированными игловыми модулями, имеющими лабораторные исследования и сертифицированными пигментами.
            </p>
            <p class="training-text">
                Методика #FlaxTap® является <strong>ПЕРВОЙ ЗАРЕГИСТРИРОВАННОЙ</strong> методикой работы с кожей!
            </p>
            <p class="training-text" style="font-weight: 700; font-size: 1.2rem;">
                Наша Академия Лицензирована Комитетом Образования и выдает официальные документы!
            </p>
        </div>
    </section>

    <!-- Advantages Section -->
    <section class="advantages-section">
        <div class="training-container">
            <h2 class="training-title" style="text-align: center;">
                Станьте мастером FlaxTap® - первой официально зарегистрированной методикой работы с кожей
            </h2>
            
            <div class="advantages-grid">
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3>Официальные документы</h3>
                    <p>
                        По результатам аттестации после обучения вы получите официальный документ о дополнительном образовании, 
                        что значительно повысит вашу конкурентоспособность среди коллег.
                    </p>
                </div>
                
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h3>Лицензированное обучение</h3>
                    <p>
                        Мы имеем все необходимые государственные разрешения на ведение образовательной деятельности, 
                        что гарантирует достойный уровень образования и соответствие высоким стандартам.
                    </p>
                </div>
                
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Собственные мероприятия</h3>
                    <p>
                        Все выпускники Академии FlaxTap® - это большая международная команда специалистов. 
                        Мы проводим внутренние онлайн-чемпионаты и поддерживаем друг друга.
                    </p>
                </div>
                
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h3>Сертифицированная продукция</h3>
                    <p>
                        Методика FlaxTap® подразуменвает работу на качественной и проверенной продукции. 
                        Используются сертифицированные игловые модули и пигменты с исследованиями.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Director Section -->
    <section class="director-section">
        <div class="training-container">
            <div class="director-wrapper">
                <div class="director-image">
                    <img src="assets/media/personal/director.jpg" alt="Марина Беляева">
                    <div class="director-badge">
                        <i class="fas fa-award"></i>
                        <span>10+ лет опыта</span>
                    </div>
                </div>
                
                <div class="director-content">
                    <h2>Марина Беляева</h2>
                    <p class="director-position">Генеральный директор</p>
                    <p class="training-text">
                        Инноватор и перфекционист, начавшая совершенствовать и развивать техники микроблейдинга со своих первых работ. 
                        Сегодня Марина Беляева - признанный авторитет в индустрии перманентного макияжа, победитель самых престижных соревнований.
                    </p>
                    
                    <div class="achievements-grid">
                        <div class="achievement">
                            <i class="fas fa-trophy"></i>
                            <div>
                                <h4>Награды</h4>
                                <p>Победитель премии "Лучший инновационный продукт"</p>
                            </div>
                        </div>
                        
                        <div class="achievement">
                            <i class="fas fa-graduation-cap"></i>
                            <div>
                                <h4>Образование</h4>
                                <p>СПбГУ, химический факультет, кандидат наук</p>
                            </div>
                        </div>
                        
                        <div class="achievement">
                            <i class="fas fa-briefcase"></i>
                            <div>
                                <h4>Опыт</h4>
                                <p>Более 10 лет в косметической индустрии</p>
                            </div>
                        </div>
                    </div>
                    
                    <button class="training-btn training-btn-primary open-courses-modal">
                        <i class="fas fa-calendar-check"></i> Записаться на обучение
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="training-container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">> 50</div>
                    <div class="stat-text">Преподавателей в России и за рубежом</div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-number">> 1K</div>
                    <div class="stat-text">Мастеров по всему миру</div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-number">> 100</div>
                    <div class="stat-text">Сертифицированных курсов</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Programs Section -->
    <section class="programs-section">
        <div class="training-container">
            <h2 class="training-title" style="text-align: center;">Наши учебные программы</h2>
            <p class="training-subtitle" style="text-align: center; margin-bottom: 40px;">Выберите подходящий курс для вашего развития</p>
            
            <div class="programs-grid">
                <div class="program-card">
                    <div class="program-image">
                        <img src="assets/media/training-programs/y1.jpg" alt="Базовый курс">
                    </div>
                    <div class="program-content">
                        <h3>Базовый курс FlaxTap®</h3>
                        <p>Основы методики FlaxTap®, работа с инструментами и пигментами. Идеально для начинающих мастеров.</p>
                        <button class="training-btn training-btn-primary open-courses-modal">
                            Подробнее
                        </button>
                    </div>
                </div>
                
                <div class="program-card">
                    <div class="program-image">
                        <img src="assets/media/training-programs/y2.jpg" alt="Продвинутый курс">
                    </div>
                    <div class="program-content">
                        <h3>Продвинутый курс</h3>
                        <p>Углубленное изучение техник коррекции и сложных случаев. Для опытных специалистов.</p>
                        <button class="training-btn training-btn-primary open-courses-modal">
                            Подробнее
                        </button>
                    </div>
                </div>
                
                <div class="program-card">
                    <div class="program-image">
                        <img src="assets/media/training-programs/y3.jpg" alt="Курс для преподавателей">
                    </div>
                    <div class="program-content">
                        <h3>Курс для преподавателей</h3>
                        <p>Методика преподавания и сертификация инструкторов. Станьте тренером академии.</p>
                        <button class="training-btn training-btn-primary open-courses-modal">
                            Подробнее
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Steps Section -->
    <section class="steps-section">
        <div class="training-container">
            <h2 class="training-title" style="text-align: center;">Как пройти обучение?</h2>
            
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h3>Выбрать курс</h3>
                    <p>Изучите наши учебные программы и выберите подходящий курс для вашего уровня и целей.</p>
                    <button class="training-btn training-btn-primary open-courses-modal">
                        Выбрать курс
                    </button>
                </div>
                
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h3>Найти преподавателя</h3>
                    <p>Подберите наставника по территориальной близости или схожести профессиональных взглядов.</p>
                    <button class="training-btn training-btn-primary open-representatives-modal">
                        Найти преподавателя
                    </button>
                </div>
                
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h3>Стать частью команды</h3>
                    <p>После обучения получите сертификат и станете частью международного сообщества FlaxTap®.</p>
                    <a href="index.php" class="training-btn training-btn-primary">
                        Перейти в магазин
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section">
        <div class="training-container">
            <h2 class="training-title" style="text-align: center;">Продукция академии FlaxTap®</h2>
            
            <div class="products-grid">
                <div class="product-main">
                    <img src="assets/media/171_pigment-dlya-vek-flaxtap-101-e4fb6f67.jpg" alt="Пигменты FlaxTap">
                    <h3 style="color: var(--training-primary); margin: 20px 0 10px;">Сертифицированные пигменты</h3>
                    <p style="color: var(--training-secondary); margin-bottom: 20px;">
                        С заключением центра контроля качества Онкологического Научного Центра
                    </p>
                    <a href="index.php" class="training-btn training-btn-primary training-btn-block">
                        <i class="fas fa-shopping-cart"></i> Перейти в магазин
                    </a>
                </div>
                
                <div class="product-categories">
                    <div class="category-item">
                        <img src="assets/media/icons/3.png" alt="Ручки">
                        <p>Ручки</p>
                    </div>
                    
                    <div class="category-item">
                        <img src="assets/media/icons/1.png" alt="Анестетики">
                        <p>Анестетики</p>
                    </div>
                    
                    <div class="category-item">
                        <img src="assets/media/icons/4.png" alt="Микробраши">
                        <p>Микробраши</p>
                    </div>
                    
                    <div class="category-item">
                        <img src="assets/media/icons/5.png" alt="Иглы">
                        <p>Иглы</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Form Section -->
    <section class="form-section">
        <div class="training-container">
            <h2 class="training-title" style="text-align: center;">Нужна помощь?</h2>
            <p class="training-subtitle" style="text-align: center; margin-bottom: 40px;">Наши администраторы всегда на связи</p>
            
            <!-- Сообщения об успехе/ошибке  -->
            <?php if ($form_success): ?>
            <div class="success-message" style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 30px; text-align: center; border: 1px solid #c3e6cb;">
                <i class="fas fa-check-circle"></i> 
                <strong>Ваша заявка успешно отправлена!</strong><br>
                Мы свяжемся с вами в ближайшее время для уточнения деталей обучения.
            </div>
            <?php endif; ?>
            
            <?php if ($form_error): ?>
            <div class="error-message" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 30px; text-align: center; border: 1px solid #f5c6cb;">
                <i class="fas fa-exclamation-triangle"></i> 
                <?php echo escape($form_error); ?>
            </div>
            <?php endif; ?>
            
            <!-- Информация о последней отправке  -->
            <?php if (isset($_SESSION['last_training_request'])): ?>
                <div class="info-message" style="background-color: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 8px; margin-bottom: 30px; text-align: center; border: 1px solid #bee5eb;">
                    <i class="fas fa-info-circle"></i> 
                    Ваша последняя заявка была отправлена 
                    <?php 
                    $time_diff = time() - $_SESSION['last_training_request']['time'];
                    if ($time_diff < 60) {
                        echo 'только что';
                    } elseif ($time_diff < 3600) {
                        echo floor($time_diff / 60) . ' минут назад';
                    } else {
                        echo floor($time_diff / 3600) . ' часов назад';
                    }
                    ?>
                    (<?php echo escape($_SESSION['last_training_request']['name']); ?>)
                </div>
            <?php endif; ?>
            
            <form id="helpForm" class="help-form" method="POST" action="" novalidate>
                <!-- Скрытое поле для типа курса -->
                <input type="hidden" name="course_type" value="Обучение FlaxTap">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="helpName">Ваше имя *</label>
                        <input type="text" id="helpName" name="name" placeholder="Иван Иванов" required 
                               value="<?php echo isset($_POST['name']) ? escape($_POST['name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="helpPhone">Ваш телефон *</label>
                        <input type="tel" id="helpPhone" name="phone" placeholder="+7 (999) 123-45-67" required
                               value="<?php echo isset($_POST['phone']) ? escape($_POST['phone']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="helpEmail">Ваш email *</label>
                        <input type="email" id="helpEmail" name="email" placeholder="example@mail.ru" required
                               value="<?php echo isset($_POST['email']) ? escape($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="helpSocial">Ссылка на социальную сеть</label>
                        <input type="text" id="helpSocial" name="social_link" placeholder="https://vk.com/username"
                               value="<?php echo isset($_POST['social_link']) ? escape($_POST['social_link']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="helpMessage">Сообщение *</label>
                    <textarea id="helpMessage" name="message" placeholder="Опишите ваш вопрос..." required><?php 
                        echo isset($_POST['message']) ? escape($_POST['message']) : ''; 
                    ?></textarea>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="helpAgree" name="agree" required>
                    <label for="helpAgree">
                        Я соглашаюсь с <a href="#" style="color: var(--training-secondary);">обработкой персональных данных</a>
                    </label>
                </div>
                
                <div style="display: flex; gap: 15px; justify-content: center; margin-top: 30px;">
                    <button type="submit" name="training_submit" class="training-btn training-btn-primary">
                        <i class="fas fa-paper-plane"></i> Отправить
                    </button>
                    
                    <button type="button" id="clearFormBtn" class="training-btn training-btn-secondary">
                        <i class="fas fa-redo"></i> Очистить
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer class="training-footer">
        <div class="training-container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="assets/media/logo/logo.png" alt="FlaxTap Academy">
                    <p style="color: var(--training-secondary); margin-top: 15px;">
                        Международная академия перманентного макияжа
                    </p>
                </div>
                
                <div class="footer-links">
                    <a href="index.php" class="training-btn training-btn-primary">
                        <i class="fas fa-store"></i> Перейти в магазин
                    </a>
                    <button class="training-btn training-btn-primary open-courses-modal">
                        <i class="fas fa-graduation-cap"></i> Курсы
                    </button>
                    <button class="training-btn training-btn-primary open-representatives-modal">
                        <i class="fas fa-users"></i> Представители
                    </button>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 FlaxTap Academy. Все права защищены.</p>
            </div>
        </div>
    </footer>

    <!-- Modal: Courses -->
    <div id="coursesModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-graduation-cap"></i> Наши курсы</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p class="training-subtitle" style="margin-bottom: 25px;">
                    Выберите подходящий курс обучения и станьте сертифицированным специалистом FlaxTap®
                </p>
                
                <div class="courses-grid">
                    <div class="course-card">
                        <div class="course-image">
                            <img src="assets/media/training-programs/y1.jpg" alt="Базовый курс">
                        </div>
                        <div class="course-info">
                            <h3>Базовый курс FlaxTap®</h3>
                            <div class="course-price">25 000 ₽</div>
                            <ul class="course-features">
                                <li><i class="fas fa-check"></i> Длительность: 3 дня</li>
                                <li><i class="fas fa-check"></i> Теория + практика</li>
                                <li><i class="fas fa-check"></i> Сертификат государственного образца</li>
                                <li><i class="fas fa-check"></i> Набор для практики</li>
                            </ul>
                            <button class="training-btn training-btn-primary course-signup-btn" data-course="Базовый курс FlaxTap®">
                                <i class="fas fa-calendar-check"></i> Записаться
                            </button>
                        </div>
                    </div>
                    
                    <div class="course-card">
                        <div class="course-image">
                            <img src="assets/media/training-programs/y2.jpg" alt="Продвинутый курс">
                        </div>
                        <div class="course-info">
                            <h3>Продвинутый курс</h3>
                            <div class="course-price">35 000 ₽</div>
                            <ul class="course-features">
                                <li><i class="fas fa-check"></i> Длительность: 5 дней</li>
                                <li><i class="fas fa-check"></i> Для опытных мастеров</li>
                                <li><i class="fas fa-check"></i> Сложные случаи и коррекция</li>
                                <li><i class="fas fa-check"></i> Работа с пигментами</li>
                            </ul>
                            <button class="training-btn training-btn-primary course-signup-btn" data-course="Продвинутый курс">
                                <i class="fas fa-calendar-check"></i> Записаться
                            </button>
                        </div>
                    </div>
                    
                    <div class="course-card">
                        <div class="course-image">
                            <img src="assets/media/training-programs/y3.jpg" alt="Курс для преподавателей">
                        </div>
                        <div class="course-info">
                            <h3>Курс для преподавателей</h3>
                            <div class="course-price">50 000 ₽</div>
                            <ul class="course-features">
                                <li><i class="fas fa-check"></i> Длительность: 7 дней</li>
                                <li><i class="fas fa-check"></i> Методика преподавания</li>
                                <li><i class="fas fa-check"></i> Сертификация инструкторов</li>
                                <li><i class="fas fa-check"></i> Авторские права</li>
                            </ul>
                            <button class="training-btn training-btn-primary course-signup-btn" data-course="Курс для преподавателей">
                                <i class="fas fa-calendar-check"></i> Записаться
                            </button>
                        </div>
                    </div>
                    
                    <div class="course-card">
                        <div class="course-image">
                            <img src="assets/media/training-programs/y4.jpg" alt="Индивидуальное обучение">
                        </div>
                        <div class="course-info">
                            <h3>Индивидуальное обучение</h3>
                            <div class="course-price">80 000 ₽</div>
                            <ul class="course-features">
                                <li><i class="fas fa-check"></i> Персонально с Мариной Беляевой</li>
                                <li><i class="fas fa-check"></i> Гибкий график</li>
                                <li><i class="fas fa-check"></i> Индивидуальная программа</li>
                                <li><i class="fas fa-check"></i> Пожизненная поддержка</li>
                            </ul>
                            <button class="training-btn training-btn-primary course-signup-btn" data-course="Индивидуальное обучение">
                                <i class="fas fa-calendar-check"></i> Записаться
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Representatives -->
    <div id="representativesModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-users"></i> Наши представители</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="representatives-search">
                    <input type="text" class="search-input" placeholder="Поиск по городу или региону...">
                    <div class="search-icon">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
                
                <div class="representatives-list">
                    <div class="representative-item">
                        <div class="representative-avatar">
                            <div style="width: 150px; height: 150px; background-color: #185592; color: white; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 3rem; font-weight: bold;">
                                АС
                            </div>
                        </div>
                        <div class="representative-info">
                            <span class="representative-region">Москва</span>
                            <h4>Анна Смирнова</h4>
                            <p class="representative-position">Старший преподаватель</p>
                            <div class="representative-contacts">
                                <div class="contact-item">
                                    <i class="fas fa-phone"></i>
                                    <span>+7 (999) 123-45-67</span>
                                </div>
                                <div class="contact-item">
                                    <i class="fas fa-envelope"></i>
                                    <span>anna.smirnova@flaxtap.ru</span>
                                </div>
                                <div class="contact-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>Москва, ул. Тверская, 10</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="representative-item">
                        <div class="representative-avatar">
                            <div style="width: 150px; height: 150px; background-color: #1976D2; color: white; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 3rem; font-weight: bold;">
                                ЕИ
                            </div>
                        </div>
                        <div class="representative-info">
                            <span class="representative-region">Санкт-Петербург</span>
                            <h4>Екатерина Иванова</h4>
                            <p class="representative-position">Ведущий специалист</p>
                            <div class="representative-contacts">
                                <div class="contact-item">
                                    <i class="fas fa-phone"></i>
                                    <span>+7 (999) 987-65-43</span>
                                </div>
                                <div class="contact-item">
                                    <i class="fas fa-envelope"></i>
                                    <span>ekaterina.ivanova@flaxtap.ru</span>
                                </div>
                                <div class="contact-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>Санкт-Петербург, Невский пр., 25</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="representative-item">
                        <div class="representative-avatar">
                            <div style="width: 150px; height: 150px; background-color: #185592; color: white; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 3rem; font-weight: bold;">
                                ОП
                            </div>
                        </div>
                        <div class="representative-info">
                            <span class="representative-region">Екатеринбург</span>
                            <h4>Ольга Петрова</h4>
                            <p class="representative-position">Региональный представитель</p>
                            <div class="representative-contacts">
                                <div class="contact-item">
                                    <i class="fas fa-phone"></i>
                                    <span>+7 (999) 456-78-90</span>
                                </div>
                                <div class="contact-item">
                                    <i class="fas fa-envelope"></i>
                                    <span>olga.petrova@flaxtap.ru</span>
                                </div>
                                <div class="contact-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>Екатеринбург, ул. Ленина, 45</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="representative-item">
                        <div class="representative-avatar">
                            <div style="width: 150px; height: 150px; background-color: #1976D2; color: white; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 3rem; font-weight: bold;">
                                МС
                            </div>
                        </div>
                        <div class="representative-info">
                            <span class="representative-region">Новосибирск</span>
                            <h4>Мария Сидорова</h4>
                            <p class="representative-position">Сертифицированный инструктор</p>
                            <div class="representative-contacts">
                                <div class="contact-item">
                                    <i class="fas fa-phone"></i>
                                    <span>+7 (999) 321-54-76</span>
                                </div>
                                <div class="contact-item">
                                    <i class="fas fa-envelope"></i>
                                    <span>maria.sidorova@flaxtap.ru</span>
                                </div>
                                <div class="contact-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>Новосибирск, Красный пр., 100</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="representatives-map">
                    <div>
                        <i class="fas fa-map-marked-alt fa-3x" style="color: var(--training-secondary); margin-bottom: 20px;"></i>
                        <h3 style="color: var(--training-primary); margin-bottom: 10px;">Карта представителей</h3>
                        <p style="color: var(--training-secondary); max-width: 400px; margin: 0 auto;">
                            Здесь будет интерактивная карта с отображением всех наших представителей по регионам России
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Простые модальные окна
        const openButtons = document.querySelectorAll('.open-courses-modal, .open-representatives-modal');
        const closeButtons = document.querySelectorAll('.modal-close');
        const modals = document.querySelectorAll('.modal-overlay');
        
        // Открытие модалок
        openButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const modalId = this.classList.contains('open-courses-modal') ? 'coursesModal' : 'representativesModal';
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }
            });
        });
        
        // Закрытие модалок
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const modal = this.closest('.modal-overlay');
                if (modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = '';
                }
            });
        });
        
        // Закрытие по клику вне окна
        modals.forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                    document.body.style.overflow = '';
                }
            });
        });
        
        // Закрытие по Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                modals.forEach(modal => {
                    if (modal.style.display === 'flex') {
                        modal.style.display = 'none';
                        document.body.style.overflow = '';
                    }
                });
            }
        });
        
        // Простой поиск по представителям
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                const reps = document.querySelectorAll('.representative-item');
                
                reps.forEach(rep => {
                    const text = rep.textContent.toLowerCase();
                    if (searchTerm === '' || text.includes(searchTerm)) {
                        rep.style.display = 'flex';
                    } else {
                        rep.style.display = 'none';
                    }
                });
            });
        }
        
        // ============================================
        // КНОПКИ В МОДАЛЬНОМ ОКНЕ КУРСОВ
        // ============================================
        const courseButtons = document.querySelectorAll('.course-signup-btn');
        courseButtons.forEach(button => {
            button.addEventListener('click', function() {
                const courseName = this.getAttribute('data-course') || 'выбранный курс';
                
                // Заполняем поле сообщения
                const messageField = document.getElementById('helpMessage');
                if (messageField) {
                    messageField.value = `Здравствуйте! Интересуюсь курсом: ${courseName}. Хотел(а) бы узнать подробности о программе, стоимости и датах начала.`;
                }
                
                // Закрываем модальное окно
                const coursesModal = document.getElementById('coursesModal');
                if (coursesModal) {
                    coursesModal.style.display = 'none';
                    document.body.style.overflow = '';
                }
                
                // Прокручиваем к форме
                const formSection = document.querySelector('.form-section');
                if (formSection) {
                    formSection.scrollIntoView({ behavior: 'smooth' });
                }
                
                // Фокус на поле сообщения
                if (messageField) {
                    setTimeout(() => {
                        messageField.focus();
                    }, 500);
                }
            });
        });
        
        // ============================================
        // КНОПКА "ОЧИСТИТЬ" В ФОРМЕ - РАБОЧАЯ ВЕРСИЯ
        // ============================================
        const clearButton = document.getElementById('clearFormBtn');
        if (clearButton) {
            clearButton.addEventListener('click', function() {
                if (confirm('Вы уверены, что хотите очистить все поля формы?')) {
                    // Очищаем текстовые поля
                    document.getElementById('helpName').value = '';
                    document.getElementById('helpPhone').value = '';
                    document.getElementById('helpEmail').value = '';
                    document.getElementById('helpSocial').value = '';
                    document.getElementById('helpMessage').value = '';
                    
                    // Сбрасываем чекбокс
                    const checkbox = document.getElementById('helpAgree');
                    if (checkbox) {
                        checkbox.checked = false;
                    }
                    
                    // Убираем классы ошибок
                    const inputs = document.querySelectorAll('#helpForm input, #helpForm textarea');
                    inputs.forEach(input => {
                        input.classList.remove('error');
                    });
                    
                    // Удаляем сообщения об ошибках
                    const errorMessages = document.querySelectorAll('.field-error');
                    errorMessages.forEach(msg => msg.remove());
                    
                    // Фокус на первое поле
                    document.getElementById('helpName').focus();
                    
                    // Показать сообщение об успехе
                    alert('Форма очищена!');
                }
            });
        }
        
        // ============================================
        // ВАЛИДАЦИЯ ФОРМЫ
        // ============================================
        const form = document.getElementById('helpForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Убираем старые сообщения об ошибках
                const oldErrors = document.querySelectorAll('.field-error');
                oldErrors.forEach(error => error.remove());
                
                let hasErrors = false;
                
                // Проверяем обязательные поля
                const requiredFields = [
                    { id: 'helpName', name: 'Имя' },
                    { id: 'helpPhone', name: 'Телефон' },
                    { id: 'helpEmail', name: 'Email' },
                    { id: 'helpMessage', name: 'Сообщение' }
                ];
                
                requiredFields.forEach(field => {
                    const input = document.getElementById(field.id);
                    if (input && !input.value.trim()) {
                        hasErrors = true;
                        input.classList.add('error');
                        
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'field-error';
                        errorMsg.textContent = `Поле "${field.name}" обязательно для заполнения`;
                        input.parentNode.appendChild(errorMsg);
                    }
                });
                
                // Проверка email
                const emailField = document.getElementById('helpEmail');
                if (emailField && emailField.value.trim()) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(emailField.value.trim())) {
                        hasErrors = true;
                        emailField.classList.add('error');
                        
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'field-error';
                        errorMsg.textContent = 'Введите корректный email адрес';
                        emailField.parentNode.appendChild(errorMsg);
                    }
                }
                
                // Проверка чекбокса
                const agreeCheckbox = document.getElementById('helpAgree');
                if (agreeCheckbox && !agreeCheckbox.checked) {
                    hasErrors = true;
                    
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'field-error';
                    errorMsg.textContent = 'Необходимо согласие с обработкой персональных данных';
                    agreeCheckbox.closest('.checkbox-group').appendChild(errorMsg);
                }
                
                if (hasErrors) {
                    e.preventDefault();
                    
                    // Прокручиваем к первой ошибке
                    const firstError = document.querySelector('.error');
                    if (firstError) {
                        firstError.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'center' 
                        });
                    }
                }
            });
        }
        
        // ============================================
        // МАСКА ДЛЯ ТЕЛЕФОНА
        // ============================================
        const phoneInput = document.getElementById('helpPhone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let value = this.value.replace(/\D/g, '');
                
                if (value.length === 0) {
                    this.value = '';
                } else if (value.length <= 1) {
                    this.value = '+7 (' + value;
                } else if (value.length <= 4) {
                    this.value = '+7 (' + value.substring(1, 4);
                } else if (value.length <= 7) {
                    this.value = '+7 (' + value.substring(1, 4) + ') ' + value.substring(4, 7);
                } else if (value.length <= 9) {
                    this.value = '+7 (' + value.substring(1, 4) + ') ' + value.substring(4, 7) + '-' + value.substring(7, 9);
                } else {
                    this.value = '+7 (' + value.substring(1, 4) + ') ' + value.substring(4, 7) + '-' + value.substring(7, 9) + '-' + value.substring(9, 11);
                }
            });
        }
    });
    </script>
</body>
</html>