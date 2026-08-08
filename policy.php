<?php
ob_start(); // Включаем буферизацию вывода
include 'config/database.php';
// Функция для безопасного вывода
function escape($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Политика и безопасность - FlaxTap</title>
    <link rel="stylesheet" href="assets/style/style.css">
    <link rel="stylesheet" href="assets/style/policy.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include "inc/header.php"; ?>

    <!-- Герой секция -->
    <section class="hero-slider" style="margin-bottom:0; height:100%;">
        <div class="slider-container" style="height: 300px;">
            <div class="slider-wrapper">
                <div class="slide active" style="background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/media/policy-hero.jpg') center/cover no-repeat;">
                    <div class="slide-content">
                        <h1 class="slide-title">Политика и <span class="highlight">безопасность</span></h1>
                        <p class="slide-text">Наша приверженность защите ваших данных и прозрачности</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Навигация по разделам -->
    <section class="policy-nav-section">
        <div class="container">
            <div class="policy-nav">
                <a href="#privacy" class="policy-nav-item active">
                    <div class="nav-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Политика конфиденциальности</h3>
                    <p>Как мы защищаем ваши данные</p>
                </a>
                
                <a href="#security" class="policy-nav-item">
                    <div class="nav-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3>Политика безопасности</h3>
                    <p>Наши стандарты защиты</p>
                </a>
                
                <a href="#sitemap" class="policy-nav-item">
                    <div class="nav-icon">
                        <i class="fas fa-sitemap"></i>
                    </div>
                    <h3>Карта сайта</h3>
                    <p>Структура нашего сайта</p>
                </a>
            </div>
        </div>
    </section>

    <!-- Секция политики конфиденциальности -->
    <section id="privacy" class="policy-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Политика конфиденциальности</h2>
                <p class="section-subtitle">Последнее обновление: 15 марта 2025 года</p>
            </div>
            
            <div class="policy-content">
                <div class="policy-intro">
                    <div class="intro-card">
                        <i class="fas fa-user-shield"></i>
                        <h3>Ваша конфиденциальность — наш приоритет</h3>
                        <p>Мы серьезно относимся к защите ваших персональных данных и соблюдаем требования Федерального закона № 152-ФЗ "О персональных данных".</p>
                    </div>
                </div>
                
                <div class="policy-accordion">
                    <!-- Пункт 1 -->
                    <div class="policy-item active">
                        <div class="policy-header">
                            <div class="policy-number">01</div>
                            <h3>Какие данные мы собираем</h3>
                            <div class="policy-toggle">
                                <i class="fas fa-minus"></i>
                            </div>
                        </div>
                        <div class="policy-body">
                            <div class="policy-text">
                                <p>Мы собираем только те данные, которые необходимы для предоставления наших услуг:</p>
                                
                                <div class="data-types">
                                    <div class="data-type">
                                        <div class="type-icon">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <h4>Идентификационные данные</h4>
                                        <ul>
                                            <li>ФИО</li>
                                            <li>Контактная информация</li>
                                            <li>Адрес доставки</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="data-type">
                                        <div class="type-icon">
                                            <i class="fas fa-shopping-cart"></i>
                                        </div>
                                        <h4>Данные о заказах</h4>
                                        <ul>
                                            <li>История покупок</li>
                                            <li>Предпочтения</li>
                                            <li>Способы оплаты</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="data-type">
                                        <div class="type-icon">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                        <h4>Технические данные</h4>
                                        <ul>
                                            <li>IP-адрес</li>
                                            <li>Тип браузера</li>
                                            <li>Время посещения</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="policy-note">
                                    <i class="fas fa-info-circle"></i>
                                    <p>Мы не собираем данные, не относящиеся к предоставлению наших услуг.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Пункт 2 -->
                    <div class="policy-item">
                        <div class="policy-header">
                            <div class="policy-number">02</div>
                            <h3>Как мы используем ваши данные</h3>
                            <div class="policy-toggle">
                                <i class="fas fa-plus"></i>
                            </div>
                        </div>
                        <div class="policy-body">
                            <div class="policy-text">
                                <p>Ваши данные используются строго в соответствии с целями, для которых они были собраны:</p>
                                
                                <div class="usage-list">
                                    <div class="usage-item">
                                        <div class="usage-check">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="usage-content">
                                            <h4>Обработка заказов</h4>
                                            <p>Для оформления, доставки и обслуживания ваших заказов</p>
                                        </div>
                                    </div>
                                    
                                    <div class="usage-item">
                                        <div class="usage-check">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="usage-content">
                                            <h4>Служба поддержки</h4>
                                            <p>Для ответа на ваши вопросы и решения проблем</p>
                                        </div>
                                    </div>
                                    
                                    <div class="usage-item">
                                        <div class="usage-check">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="usage-content">
                                            <h4>Улучшение сервиса</h4>
                                            <p>Для анализа и совершенствования нашего сайта и услуг</p>
                                        </div>
                                    </div>
                                    
                                    <div class="usage-item">
                                        <div class="usage-check">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="usage-content">
                                            <h4>Маркетинг</h4>
                                            <p>Только с вашего согласия — для информирования о акциях и новинках</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="policy-table">
                                    <h4>Сроки хранения данных:</h4>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Тип данных</th>
                                                <th>Срок хранения</th>
                                                <th>Основание</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Данные заказа</td>
                                                <td>5 лет</td>
                                                <td>Налоговое законодательство РФ</td>
                                            </tr>
                                            <tr>
                                                <td>Персональные данные</td>
                                                <td>Действует согласие</td>
                                                <td>Федеральный закон №152-ФЗ</td>
                                            </tr>
                                            <tr>
                                                <td>Логи сайта</td>
                                                <td>90 дней</td>
                                                <td>Безопасность и аналитика</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Пункт 3 -->
                    <div class="policy-item">
                        <div class="policy-header">
                            <div class="policy-number">03</div>
                            <h3>Передача данных третьим лицам</h3>
                            <div class="policy-toggle">
                                <i class="fas fa-plus"></i>
                            </div>
                        </div>
                        <div class="policy-body">
                            <div class="policy-text">
                                <p>Мы не продаем и не передаем ваши персональные данные третьим лицам, за исключением:</p>
                                
                                <div class="third-party-list">
                                    <div class="party-card">
                                        <div class="party-icon">
                                            <i class="fas fa-truck"></i>
                                        </div>
                                        <h4>Службы доставки</h4>
                                        <p>Только необходимые данные для доставки заказа (ФИО, адрес, телефон)</p>
                                        <span class="party-tag">СДЕК, Почта России</span>
                                    </div>
                                    
                                    <div class="party-card">
                                        <div class="party-icon">
                                            <i class="fas fa-credit-card"></i>
                                        </div>
                                        <h4>Платежные системы</h4>
                                        <p>Данные, необходимые для обработки платежа (без сохранения данных карты)</p>
                                        <span class="party-tag">ЮKassa, Тинькофф</span>
                                    </div>
                                    
                                    <div class="party-card">
                                        <div class="party-icon">
                                            <i class="fas fa-server"></i>
                                        </div>
                                        <h4>Хостинг-провайдеры</h4>
                                        <p>Техническая обработка данных на защищенных серверах</p>
                                        <span class="party-tag">Selectel, Yandex.Cloud</span>
                                    </div>
                                </div>
                                
                                <div class="policy-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <div>
                                        <h4>Важное уведомление</h4>
                                        <p>Мы передаем данные только партнерам, соответствующим требованиям ФЗ-152 и имеющим соответствующие соглашения о защите данных.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Пункт 4 -->
                    <div class="policy-item">
                        <div class="policy-header">
                            <div class="policy-number">04</div>
                            <h3>Ваши права</h3>
                            <div class="policy-toggle">
                                <i class="fas fa-plus"></i>
                            </div>
                        </div>
                        <div class="policy-body">
                            <div class="policy-text">
                                <p>В соответствии с законодательством РФ, вы имеете право:</p>
                                
                                <div class="rights-grid">
                                    <div class="right-card">
                                        <h4><i class="fas fa-eye"></i> Право на доступ</h4>
                                        <p>Запросить информацию о том, какие ваши данные мы обрабатываем</p>
                                    </div>
                                    
                                    <div class="right-card">
                                        <h4><i class="fas fa-edit"></i> Право на исправление</h4>
                                        <p>Исправить неточные или неполные персональные данные</p>
                                    </div>
                                    
                                    <div class="right-card">
                                        <h4><i class="fas fa-trash-alt"></i> Право на удаление</h4>
                                        <p>Удалить ваши данные, когда они больше не нужны для заявленных целей</p>
                                    </div>
                                    
                                    <div class="right-card">
                                        <h4><i class="fas fa-ban"></i> Право на отзыв согласия</h4>
                                        <p>В любой момент отозвать свое согласие на обработку данных</p>
                                    </div>
                                </div>
                                
                                <div class="contact-rights">
                                    <h4>Как реализовать свои права:</h4>
                                    <p>Для реализации своих прав отправьте запрос на email: <a href="mailto:privacy@flaxtap.ru">privacy@flaxtap.ru</a></p>
                                    <p>Мы ответим в течение 30 календарных дней с момента получения запроса.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="policy-summary">
                    <div class="summary-card">
                        <h3><i class="fas fa-file-signature"></i> Согласие на обработку данных</h3>
                        <p>Используя наш сайт, вы даете согласие на обработку ваших персональных данных в соответствии с настоящей Политикой конфиденциальности.</p>
                        <p class="update-info">Документ актуален на: <strong>15 марта 2025 г.</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Секция политики безопасности -->
    <section id="security" class="policy-section security-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Политика безопасности</h2>
                <p class="section-subtitle">Наши меры по защите ваших данных и транзакций</p>
            </div>
            
            <div class="security-content">
                <div class="security-features">
                    <div class="feature-card">
                        <div class="feature-icon ssl">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h3>SSL-шифрование</h3>
                        <p>Все данные передаются по защищенному протоколу HTTPS с 256-битным шифрованием</p>
                        <div class="feature-status active">
                            <i class="fas fa-check-circle"></i> Активно
                        </div>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon pci">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h3>PCI DSS соответствие</h3>
                        <p>Соблюдение стандартов безопасности при обработке платежных карт</p>
                        <div class="feature-status active">
                            <i class="fas fa-check-circle"></i> Активно
                        </div>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon firewall">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Защита от атак</h3>
                        <p>Многоуровневая система защиты от DDoS и других кибератак</p>
                        <div class="feature-status active">
                            <i class="fas fa-check-circle"></i> Активно
                        </div>
                    </div>
                </div>
                
                <div class="security-details">
                    <div class="detail-block">
                        <h3><i class="fas fa-user-lock"></i> Безопасность аккаунта</h3>
                        <ul>
                            <li>Обязательная двухфакторная аутентификация для сотрудников</li>
                            <li>Пароли хранятся в зашифрованном виде с использованием хеширования</li>
                            <li>Автоматическое блокирование при подозрительной активности</li>
                            <li>Регулярная смена паролей и проверка их надежности</li>
                        </ul>
                    </div>
                    
                    <div class="detail-block">
                        <h3><i class="fas fa-server"></i> Защита серверов</h3>
                        <ul>
                            <li>Сервера расположены в защищенных дата-центрах в РФ</li>
                            <li>Ежедневное резервное копирование всех данных</li>
                            <li>Регулярные обновления безопасности и патчи</li>
                            <li>Мониторинг 24/7 на предмет несанкционированного доступа</li>
                        </ul>
                    </div>
                    
                    <div class="detail-block">
                        <h3><i class="fas fa-money-check-alt"></i> Безопасность платежей</h3>
                        <div class="payment-security">
                            <div class="payment-method">
                                <i class="fab fa-cc-visa"></i>
                                <span>Visa Secure</span>
                            </div>
                            <div class="payment-method">
                                <i class="fab fa-cc-mastercard"></i>
                                <span>Mastercard SecureCode</span>
                            </div>
                            <div class="payment-method">
                                <i class="fas fa-shield-alt"></i>
                                <span>3-D Secure</span>
                            </div>
                        </div>
                        <p>Мы не храним данные ваших банковских карт на своих серверах. Все платежи обрабатываются через защищенные шлюзы.</p>
                    </div>
                </div>
                
                <div class="compliance-section">
                    <h3><i class="fas fa-certificate"></i> Соответствие стандартам</h3>
                    <div class="compliance-grid">
                        <div class="compliance-item">
                            <h4>Федеральный закон № 152-ФЗ</h4>
                            <p>О персональных данных</p>
                            <span class="compliance-status compliant">Соответствует</span>
                        </div>
                        
                        <div class="compliance-item">
                            <h4>ГОСТ Р 57580.1-2017</h4>
                            <p>Требования к защите информации</p>
                            <span class="compliance-status compliant">Соответствует</span>
                        </div>
                        
                        <div class="compliance-item">
                            <h4>PCI DSS 4.0</h4>
                            <p>Стандарт безопасности индустрии платежных карт</p>
                            <span class="compliance-status in-progress">В процессе</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Секция карты сайта -->
    <section id="sitemap" class="policy-section sitemap-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Карта сайта</h2>
                <p class="section-subtitle">Полная структура нашего сайта для удобной навигации</p>
            </div>
            
            <div class="sitemap-content">
                <div class="sitemap-search">
                    <input type="text" placeholder="Поиск по карте сайта..." id="sitemapSearch">
                    <button class="sitemap-search-btn"><i class="fas fa-search"></i></button>
                </div>
                
                <div class="sitemap-grid">
                    <!-- Основные разделы -->
                    <div class="sitemap-category">
                        <h3><i class="fas fa-home"></i> Главная страница</h3>
                        <ul>
                            <li><a href="/">Главная</a></li>
                            <li><a href="/#about">О компании</a></li>
                            <li><a href="/#hits">Хиты продаж</a></li>
                            <li><a href="/#faq">Частые вопросы</a></li>
                        </ul>
                    </div>
                    
                    <!-- Каталог -->
                    <div class="sitemap-category">
                        <h3><i class="fas fa-th-large"></i> Каталог продукции</h3>
                        <ul>
                            <li><a href="subdirectory/care-cosmetics/index.html">Уходовая косметика</a></li>
                            <li><a href="#">Профессиональные наборы</a></li>
                            <li><a href="#">Все для перманента</a></li>
                            <li><a href="#">Ресницы и брови</a></li>
                            <li><a href="#">Расходные материалы</a></li>
                            <li><a href="#">Новинки</a></li>
                            <li><a href="#">Акции и скидки</a></li>
                        </ul>
                    </div>
                    
                    <!-- Информация -->
                    <div class="sitemap-category">
                        <h3><i class="fas fa-info-circle"></i> Информация</h3>
                        <ul>
                            <li><a href="/delivery.php">Доставка и оплата</a></li>
                            <li><a href="/contacts.php">Контакты</a></li>
                            <li><a href="/training.php">Обучение</a></li>
                            <li><a href="/vacancies.php">Вакансии</a></li>
                            <li class="active"><a href="/policy.php">Политика конфиденциальности</a></li>
                        </ul>
                    </div>
                    
                    <!-- Пользователь -->
                    <div class="sitemap-category">
                        <h3><i class="fas fa-user"></i> Личный кабинет</h3>
                        <ul>
                            <li><a href="#">Вход / Регистрация</a></li>
                            <li><a href="#">История заказов</a></li>
                            <li><a href="#">Избранное</a></li>
                            <li><a href="#">Настройки профиля</a></li>
                            <li><a href="#">Бонусная программа</a></li>
                        </ul>
                    </div>
                    
                    <!-- Поддержка -->
                    <div class="sitemap-category">
                        <h3><i class="fas fa-headset"></i> Поддержка</h3>
                        <ul>
                            <li><a href="#">Частые вопросы</a></li>
                            <li><a href="#">Инструкции по применению</a></li>
                            <li><a href="#">Гарантия и возврат</a></li>
                            <li><a href="#">Служба поддержки</a></li>
                            <li><a href="#">Оставить отзыв</a></li>
                        </ul>
                    </div>
                    
                    <!-- Для партнеров -->
                    <div class="sitemap-category">
                        <h3><i class="fas fa-handshake"></i> Для партнеров</h3>
                        <ul>
                            <li><a href="#">Сотрудничество</a></li>
                            <li><a href="#">Дистрибьюция</a></li>
                            <li><a href="#">Франшиза</a></li>
                            <li><a href="#">Оптовые закупки</a></li>
                            <li><a href="#">Корпоративным клиентам</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="sitemap-stats">
                    <div class="stat-item">
                        <div class="stat-number">150+</div>
                        <div class="stat-label">страниц</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">6</div>
                        <div class="stat-label">основных разделов</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">100%</div>
                        <div class="stat-label">доступность</div>
                    </div>
                </div>
                
                <div class="sitemap-note">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>Если вы не можете найти нужную страницу, воспользуйтесь поиском по сайту или обратитесь в службу поддержки.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Быстрые ссылки -->
    <section class="quick-links-section">
        <div class="container">
            <div class="quick-links">
                <a href="#privacy" class="quick-link">
                    <i class="fas fa-shield-alt"></i>
                    <span>Вернуться к политике конфиденциальности</span>
                </a>
                <a href="#security" class="quick-link">
                    <i class="fas fa-lock"></i>
                    <span>Вернуться к политике безопасности</span>
                </a>
                <a href="#sitemap" class="quick-link">
                    <i class="fas fa-sitemap"></i>
                    <span>Вернуться к карте сайта</span>
                </a>
                <a href="/" class="quick-link">
                    <i class="fas fa-home"></i>
                    <span>На главную страницу</span>
                </a>
            </div>
        </div>
    </section>

    <?php include "inc/modal.php"; ?>
    <?php include "inc/footer.php"; ?>

    <script src="assets/js/policy.js"></script>
    <script src="assets/js/nav.js"></script>
    <script src="assets/js/modals.js"></script>
</body>
</html>