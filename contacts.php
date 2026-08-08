<?php
ob_start(); // Включаем буферизацию вывода
include 'config/database.php';
// Функция для безопасного вывода
function escape($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$contact_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    // Получаем данные
    $contact_name = $_POST['contact_name'] ?? '';
    $contact_phone = $_POST['contact_phone'] ?? '';
    $contact_email = $_POST['contact_email'] ?? '';
    $contact_subject = $_POST['contact_subject'] ?? '';
    $contact_message = $_POST['contact_message'] ?? '';
    $contact_agreement = isset($_POST['contact_agreement']);
    
    // Проверяем обязательные поля
    if (!empty($contact_name) && !empty($contact_phone) && !empty($contact_message) && $contact_agreement) {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $sql = "INSERT INTO contact_messages (name, phone, email, subject, message, ip_address, user_agent, status, created_at) 
                VALUES (
                '" . $connection->real_escape_string($contact_name) . "', 
                '" . $connection->real_escape_string($contact_phone) . "', 
                '" . $connection->real_escape_string($contact_email) . "', 
                '" . $connection->real_escape_string($contact_subject) . "', 
                '" . $connection->real_escape_string($contact_message) . "', 
                '" . $connection->real_escape_string($ip_address) . "', 
                '" . $connection->real_escape_string($user_agent) . "', 
                'new', 
                NOW())";
        
        if ($connection->query($sql)) {
            $contact_success = true;
            $success_message = '<div class="alert alert-success">✓ Ваше сообщение успешно отправлено! Мы свяжемся с вами в ближайшее время.</div>';
            
            // Очищаем переменные для очистки формы
            $contact_name = '';
            $contact_phone = '';
            $contact_email = '';
            $contact_subject = '';
            $contact_message = '';
            $contact_agreement = false;
        } else {
            $error_message = '<div class="alert alert-danger">✗ Ошибка отправки: ' . $connection->error . '</div>';
        }
    } else {
        $error_message = '<div class="alert alert-warning">Пожалуйста, заполните все обязательные поля и согласитесь с политикой обработки данных!</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Контакты - FlaxTap</title>
    <link rel="stylesheet" href="assets/style/style.css">
    <link rel="stylesheet" href="assets/style/contacts.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Яндекс Карты API -->
    <script src="https://api-maps.yandex.ru/2.1/?apikey=56bf0a7a-58dd-4188-8e08-8f00e6dd3cc3&lang=ru_RU" type="text/javascript"></script>
</head>
<body>
    <?php include "inc/header.php"; ?>

    <!-- Герой секция -->
    <section class="hero-slider" style="margin-bottom:0; height:100%;">
        <div class="slider-container" style="height: 400px;">
            <div class="slider-wrapper">
                <div class="slide active" style="background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/media/carousel.jpg') center/cover no-repeat;">
                    <div class="slide-content">
                        <h1 class="slide-title">Наши <span class="highlight">контакты</span></h1>
                        <p class="slide-text">Свяжитесь с нами удобным способом. Мы всегда рады помочь!</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Секция контактной информации -->
    <section class="about-section improved">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Контактная информация</h2>
                <p class="section-subtitle">Свяжитесь с нами любым удобным способом</p>
            </div>
            
            <div class="contact-info-grid">
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="contact-content">
                        <h3>Наш адрес</h3>
                        <p>Россия, Санкт-Петербург, набережная реки Смоленки, 3к2</p>
                        <p><strong>График работы:</strong> Пн-Пт: 11:00-19:00</p>
                        <button class="btn btn-outline btn-small address-trigger" style="margin-top: 15px;">
                            <i class="fas fa-map-marked-alt"></i> Показать на карте
                        </button>
                    </div>
                </div>
                
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div class="contact-content">
                        <h3>Телефоны</h3>
                        <p><strong>Основной:</strong> +7 (812) 600-30-60</p>
                        <p><strong>Отдел продаж:</strong> +7 (912) 345-67-89</p>
                        <p><strong>Техническая поддержка:</strong> +7 (999) 123-45-67</p>
                        <button class="btn btn-outline btn-small" style="margin-top: 15px;" onclick="window.location.href='tel:+78126003060'">
                            <i class="fas fa-phone"></i> Позвонить
                        </button>
                    </div>
                </div>
                
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-content">
                        <h3>Электронная почта</h3>
                        <p><strong>Общие вопросы:</strong> info@flaxtap.ru</p>
                        <p><strong>Отдел продаж:</strong> sales@flaxtap.ru</p>
                        <p><strong>Поддержка:</strong> support@flaxtap.ru</p>
                        <button class="btn btn-outline btn-small" style="margin-top: 15px;" onclick="window.location.href='mailto:info@flaxtap.ru'">
                            <i class="fas fa-envelope"></i> Написать письмо
                        </button>
                    </div>
                </div>
                
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="contact-content">
                        <h3>Время работы</h3>
                        <p><strong>Понедельник - Пятница:</strong> 11:00 - 19:00</p>
                        <p><strong>Суббота:</strong> 11:00 - 17:00</p>
                        <p><strong>Воскресенье:</strong> Выходной</p>
                        <p class="note"><i class="fas fa-info-circle"></i> В праздничные дни график может меняться</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Секция карты -->
    <section class="about-section improved" style="background: linear-gradient(135deg, #f9fdff 0%, #ffffff 100%);">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Мы на карте</h2>
                <p class="section-subtitle">Найдите нас в Санкт-Петербурге</p>
            </div>
            
            <div class="map-section">
                <div id="contactMap" style="height: 500px; border-radius: var(--border-radius); overflow: hidden; box-shadow: var(--box-shadow);"></div>
                <div class="map-info">
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h4>Адрес офиса</h4>
                            <p>Санкт-Петербург, набережная реки Смоленки, 3к2</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-subway"></i>
                        <div>
                            <h4>Ближайшее метро</h4>
                            <p>Станция "Василеостровская" (5 минут пешком)</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-parking"></i>
                        <div>
                            <h4>Парковка</h4>
                            <p>Бесплатная парковка для клиентов (10 мест)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Секция руководителя -->
    <section class="about-section improved">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Руководство компании</h2>
                <p class="section-subtitle">Наши опытные специалисты</p>
            </div>
            
            <div class="director-card">
                <div class="director-image">
                    <img src="assets/media/personal/director.jpg" alt="Директор компании" style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--border-radius);">
                    <div class="director-badge">
                        <i class="fas fa-award"></i>
                        <span>10+ лет опыта</span>
                    </div>
                </div>
                <div class="director-info">
                    <h3>Беляева Мария Николаевна</h3>
                    <p class="director-position">Генеральный директор</p>
                    
                    <div class="director-achievements">
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
                    
                    <div class="director-contacts">
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>+7 (912) 345-67-89</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>director@flaxtap.ru</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <span>Приемные дни: Вт, Чт 14:00-17:00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Секция формы обратной связи -->
    <section class="faq-section improved" style="background: linear-gradient(135deg, #fffbf0 0%, #ffffff 100%);">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Остались вопросы?</h2>
                <p class="section-subtitle">Заполните форму и мы свяжемся с вами в течение 30 минут</p>
            </div>
            
            <div class="contact-form-section">
                <div class="form-container">
                    <!-- Вывод сообщений -->
                    <?php 
                    if (isset($success_message)) echo $success_message;
                    if (isset($error_message)) echo $error_message;
                    ?>
                    
                    <form class="contact-form" method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="contact_name">Ваше имя *</label>
                                <input type="text" id="contact_name" name="contact_name" placeholder="Иван Иванов" required 
                                       value="<?php echo escape($contact_name ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="contact_phone">Телефон *</label>
                                <input type="tel" id="contact_phone" name="contact_phone" placeholder="+7 (999) 123-45-67" required 
                                       value="<?php echo escape($contact_phone ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_email">Email</label>
                            <input type="email" id="contact_email" name="contact_email" placeholder="example@mail.ru"
                                   value="<?php echo escape($contact_email ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_subject">Тема обращения</label>
                            <select id="contact_subject" name="contact_subject">
                                <option value="">Выберите тему</option>
                                <option value="order" <?php echo (isset($contact_subject) && $contact_subject == 'order') ? 'selected' : ''; ?>>Вопрос по заказу</option>
                                <option value="product" <?php echo (isset($contact_subject) && $contact_subject == 'product') ? 'selected' : ''; ?>>Консультация по продукции</option>
                                <option value="cooperation" <?php echo (isset($contact_subject) && $contact_subject == 'cooperation') ? 'selected' : ''; ?>>Сотрудничество</option>
                                <option value="other" <?php echo (isset($contact_subject) && $contact_subject == 'other') ? 'selected' : ''; ?>>Другое</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_message">Сообщение *</label>
                            <textarea id="contact_message" name="contact_message" placeholder="Опишите ваш вопрос подробнее..." rows="5" required><?php echo escape($contact_message ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-options">
                            <label class="checkbox">
                                <input type="checkbox" name="contact_agreement" <?php echo (isset($contact_agreement) && $contact_agreement) ? 'checked' : ''; ?> required>
                                <span>Я соглашаюсь с <a href="#">политикой обработки персональных данных</a></span>
                            </label>
                        </div>
                        
                        <button type="submit" name="contact_submit" class="btn btn-primary btn-block">
                            <i class="fas fa-paper-plane"></i> Отправить сообщение
                        </button>
                        
                        <?php if ($contact_success): ?>
                        <script>
                            // Очищаем форму через JavaScript после успешной отправки
                            document.addEventListener('DOMContentLoaded', function() {
                                const form = document.querySelector('.contact-form');
                                if (form) {
                                    form.reset();
                                }
                            });
                        </script>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="form-info">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-comment-dots"></i>
                        </div>
                        <h4>Быстрый ответ</h4>
                        <p>Мы отвечаем на все обращения в течение 30 минут в рабочее время</p>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h4>Поддержка 24/7</h4>
                        <p>Наши операторы готовы помочь даже в нерабочие часы через чат</p>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4>Конфиденциальность</h4>
                        <p>Все ваши данные защищены и не передаются третьим лицам</p>
                    </div>
                    
                    <div class="social-contact">
                        <h4>Или напишите нам в соцсетях:</h4>
                        <div class="social-buttons">
                            <a href="#" class="social-btn vk">
                                <i class="fab fa-vk"></i> ВКонтакте
                            </a>
                            <a href="#" class="social-btn telegram">
                                <i class="fab fa-telegram"></i> Telegram
                            </a>
                            <a href="#" class="social-btn whatsapp">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ по контактам -->
    <section class="faq-section improved">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Частые вопросы</h2>
                <p class="section-subtitle">Ответы на популярные вопросы о сотрудничестве</p>
            </div>
            
            <div class="faq-container">
                <div class="faq-item active">
                    <div class="faq-header">
                        <div class="faq-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h3 class="faq-question">Как забрать заказ самовывозом?</h3>
                        <div class="faq-toggle">
                            <i class="fas fa-minus"></i>
                        </div>
                    </div>
                    <div class="faq-body">
                        <p>Самовывоз доступен по адресу: Санкт-Петербург, набережная реки Смоленки, 3к2. Перед визитом обязательно:</p>
                        <ul class="faq-list">
                            <li><i class="fas fa-check"></i> Дождитесь СМС о готовности заказа</li>
                            <li><i class="fas fa-check"></i> Возьмите с собой паспорт</li>
                            <li><i class="fas fa-check"></i> Имейте номер заказа (приходит в СМС)</li>
                        </ul>
                        <p><strong>Время самовывоза:</strong> Пн-Пт 11:00-19:00, Сб 11:00-17:00</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-header">
                        <div class="faq-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h3 class="faq-question">Как стать дистрибьютором FlaxTap?</h3>
                        <div class="faq-toggle">
                            <i class="fas fa-plus"></i>
                        </div>
                    </div>
                    <div class="faq-body">
                        <p>Мы рады новым партнерам! Чтобы стать дистрибьютором:</p>
                        <ol class="return-steps">
                            <li>Заполните заявку на сотрудничество</li>
                            <li>Наш менеджер свяжется с вами в течение 24 часов</li>
                            <li>Обсудим условия и подпишем договор</li>
                            <li>Получите персональные скидки и доступ к обучению</li>
                        </ol>
                        <button class="btn btn-outline" style="margin-top: 15px;">
                            <i class="fas fa-file-contract"></i> Заполнить заявку на сотрудничество
                        </button>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-header">
                        <div class="faq-icon">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <h3 class="faq-question">Можно ли вернуть товар в офис?</h3>
                        <div class="faq-toggle">
                            <i class="fas fa-plus"></i>
                        </div>
                    </div>
                    <div class="faq-body">
                        <p>Да, возврат товара возможен в нашем офисе при соблюдении условий:</p>
                        <div class="return-conditions">
                            <div class="condition">
                                <i class="fas fa-calendar-check"></i>
                                <div>
                                    <h4>Срок возврата</h4>
                                    <p>14 дней с момента получения заказа</p>
                                </div>
                            </div>
                            <div class="condition">
                                <i class="fas fa-box"></i>
                                <div>
                                    <h4>Состояние товара</h4>
                                    <p>Товарный вид и оригинальная упаковка сохранены</p>
                                </div>
                            </div>
                            <div class="condition">
                                <i class="fas fa-receipt"></i>
                                <div>
                                    <h4>Документы</h4>
                                    <p>Наличие чека или номера заказа</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include "inc/modal.php"; ?>
    <?php include "inc/footer.php"; ?>

    <script src="assets/js/contacts.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/modals.js"></script>
    
    <script>
        // Инициализация Яндекс Карты для страницы контактов
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof ymaps !== 'undefined') {
                ymaps.ready(initContactMap);
            }
        });

        function initContactMap() {
            var contactMap = new ymaps.Map('contactMap', {
                center: [59.949346, 30.262204],
                zoom: 16,
                controls: ['zoomControl', 'fullscreenControl']
            });

            // Добавляем метку
            var myPlacemark = new ymaps.Placemark([59.949346, 30.262204], {
                balloonContentHeader: 'FlaxTap',
                balloonContentBody: 'Санкт-Петербург, набережная реки Смоленки, 3к2<br>Телефон: +7 (812) 600-30-60',
                balloonContentFooter: 'График работы: Пн-Пт 11:00-19:00',
                hintContent: 'FlaxTap'
            }, {
                iconLayout: 'default#image',
                iconImageHref: 'https://cdn-icons-png.flaticon.com/512/684/684908.png',
                iconImageSize: [40, 40],
                iconImageOffset: [-20, -40]
            });

            contactMap.geoObjects.add(myPlacemark);
            
            // Открываем балун при загрузке
            myPlacemark.balloon.open();
            
            // Адаптация карты под размер контейнера
            contactMap.container.fitToViewport();
        }
    </script>
</body>
</html>