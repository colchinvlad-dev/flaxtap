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
    <title>Доставка и оплата - FlaxTap</title>
    <link rel="stylesheet" href="assets/style/style.css">
    <link rel="stylesheet" href="assets/style/delivery.css">
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
                        <h1 class="slide-title">Доставка <span class="highlight">и оплата</span></h1>
                        <p class="slide-text">Удобные способы получения товаров и безопасные методы оплаты</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Секция способов доставки -->
    <section class="about-section improved">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Способы доставки</h2>
                <p class="section-subtitle">Отправляем по всей России с 2020 года</p>
            </div>
            
            <div class="delivery-methods">
                <div class="method">
                    <div class="method-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <div class="method-content">
                        <h3>Курьерская служба СДЕК</h3>
                        <p class="method-description">Быстрая доставка в 4700+ пунктов выдачи и адресная доставка по всей России</p>
                        <div class="method-details">
                            <div class="detail-item">
                                <i class="fas fa-clock"></i>
                                <span>Срок: 2-7 дней</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-ruble-sign"></i>
                                <span>Стоимость: от 250 ₽</span>
                            </div>
                        </div>
                        <div class="method-advantages">
                            <span class="advantage-tag">Трекинг</span>
                            <span class="advantage-tag">Страхование</span>
                            <span class="advantage-tag">Наличный расчет</span>
                        </div>
                    </div>
                </div>
                
                <div class="method">
                    <div class="method-icon">
                        <i class="fas fa-mail-bulk"></i>
                    </div>
                    <div class="method-content">
                        <h3>Почта России</h3>
                        <p class="method-description">Доступная доставка в любой населенный пункт Российской Федерации</p>
                        <div class="method-details">
                            <div class="detail-item">
                                <i class="fas fa-clock"></i>
                                <span>Срок: 5-14 дней</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-ruble-sign"></i>
                                <span>Стоимость: от 150 ₽</span>
                            </div>
                        </div>
                        <div class="method-advantages">
                            <span class="advantage-tag">Доступно везде</span>
                            <span class="advantage-tag">Наложенный платеж</span>
                            <span class="advantage-tag">Эконом-вариант</span>
                        </div>
                    </div>
                </div>
                
                <div class="method">
                    <div class="method-icon">
                        <i class="fas fa-store"></i>
                    </div>
                    <div class="method-content">
                        <h3>Самовывоз в Санкт-Петербурге</h3>
                        <p class="method-description">Заберите заказ бесплатно в одном из наших магазинов</p>
                        <div class="method-details">
                            <div class="detail-item">
                                <i class="fas fa-clock"></i>
                                <span>Сегодня или завтра</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-ruble-sign"></i>
                                <span>Бесплатно</span>
                            </div>
                        </div>
                        <div class="method-advantages">
                            <span class="advantage-tag">Бесплатно</span>
                            <span class="advantage-tag">Моментально</span>
                            <span class="advantage-tag">Консультация</span>
                        </div>
                        <button class="btn btn-outline btn-small address-trigger" style="margin-top: 15px;">
                            <i class="fas fa-map-marker-alt"></i> Выбрать пункт самовывоза
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="delivery-note">
                <div class="note-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="note-content">
                    <h4>Важная информация</h4>
                    <p>Отправка товаров осуществляется после 100% оплаты. Бесплатная доставка при заказе от 5000 ₽.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Секция способов оплаты -->
    <section class="faq-section improved">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Способы оплаты</h2>
                <p class="section-subtitle">Безопасные и удобные варианты оплаты заказа</p>
            </div>
            
            <div class="payment-methods">
                <div class="payment-method">
                    <div class="payment-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3>Онлайн-оплата картой</h3>
                    <p>Безопасная оплата банковской картой через защищенное соединение</p>
                    <div class="payment-cards">
                        <i class="fab fa-cc-visa"></i>
                        <i class="fab fa-cc-mastercard"></i>
                        <i class="fab fa-cc-mir"></i>
                    </div>
                </div>
                
                <div class="payment-method">
                    <div class="payment-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Электронные кошельки</h3>
                    <p>Оплата через популярные платежные системы</p>
                    <div class="payment-cards">
                        <i class="fab fa-cc-paypal"></i>
                        <span style="font-weight: 600;">ЮMoney</span>
                        <span style="font-weight: 600;">QIWI</span>
                    </div>
                </div>
                
                <div class="payment-method">
                    <div class="payment-icon">
                        <i class="fas fa-university"></i>
                    </div>
                    <h3>Банковский перевод</h3>
                    <p>Оплата по счету для юридических и физических лиц</p>
                    <div class="payment-cards">
                        <i class="fas fa-file-invoice"></i>
                        <span style="font-weight: 600;">Счет</span>
                        <span style="font-weight: 600;">Реквизиты</span>
                    </div>
                </div>
                
                <div class="payment-method">
                    <div class="payment-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h3>Наличными</h3>
                    <p>Оплата наличными при получении в пункте выдачи или курьеру</p>
                    <div class="payment-cards">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span style="font-weight: 600;">Наличкой</span>
                        <span style="font-weight: 600;">В магазине</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Секция тарифов доставки -->
    <section class="about-section improved" style="background: linear-gradient(135deg, #f9fdff 0%, #ffffff 100%);">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Тарифы на доставку</h2>
                <p class="section-subtitle">Прозрачные цены и условия</p>
            </div>
            
            <div class="pricing-table">
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3>Эконом</h3>
                        <div class="price">150 ₽</div>
                        <p class="price-subtitle">Почта России</p>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> Доставка в отделение</li>
                        <li><i class="fas fa-check"></i> Срок: 5-14 дней</li>
                        <li><i class="fas fa-check"></i> Трекинг-номер</li>
                        <li><i class="fas fa-check"></i> Страхование 1000 ₽</li>
                        <li><i class="fas fa-times"></i> Доставка курьером</li>
                    </ul>
                    <button class="btn btn-outline btn-block">Выбрать</button>
                </div>
                
                <div class="pricing-card featured">
                    <div class="pricing-badge">Популярный</div>
                    <div class="pricing-header">
                        <h3>Стандарт</h3>
                        <div class="price">250 ₽</div>
                        <p class="price-subtitle">СДЕК до пункта выдачи</p>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> Доставка в пункт выдачи</li>
                        <li><i class="fas fa-check"></i> Срок: 2-7 дней</li>
                        <li><i class="fas fa-check"></i> Трекинг-номер</li>
                        <li><i class="fas fa-check"></i> Страхование 5000 ₽</li>
                        <li><i class="fas fa-check"></i> СМС-уведомления</li>
                    </ul>
                    <button class="btn btn-primary btn-block">Выбрать</button>
                </div>
                
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3>Экспресс</h3>
                        <div class="price">450 ₽</div>
                        <p class="price-subtitle">СДЕК курьером</p>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> Курьерская доставка</li>
                        <li><i class="fas fa-check"></i> Срок: 1-3 дня</li>
                        <li><i class="fas fa-check"></i> Трекинг-номер</li>
                        <li><i class="fas fa-check"></i> Страхование 10000 ₽</li>
                        <li><i class="fas fa-check"></i> Доставка до двери</li>
                    </ul>
                    <button class="btn btn-outline btn-block">Выбрать</button>
                </div>
            </div>
            
            <div class="free-delivery-banner">
                <div class="banner-icon">
                    <i class="fas fa-gift"></i>
                </div>
                <div class="banner-content">
                    <h3>Бесплатная доставка от 5000 ₽</h3>
                    <p>При заказе на сумму от 5000 рублей доставка по России бесплатно (кроме экспресс-доставки)</p>
                </div>
                <div class="banner-action">
                    <a href="catalog.html" class="btn btn-primary">Перейти в каталог</a>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ по доставке и оплате -->
    <section class="faq-section improved">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Частые вопросы</h2>
                <p class="section-subtitle">Ответы на популярные вопросы о доставке и оплате</p>
            </div>
            
            <div class="faq-container">
                <div class="faq-item active">
                    <div class="faq-header">
                        <div class="faq-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h3 class="faq-question">Как отследить посылку?</h3>
                        <div class="faq-toggle">
                            <i class="fas fa-minus"></i>
                        </div>
                    </div>
                    <div class="faq-body">
                        <p>После отправки заказа мы высылаем трекинг-номер на вашу электронную почту и в СМС. Отследить посылку можно:</p>
                        <ul class="faq-list">
                            <li><i class="fas fa-external-link-alt"></i> <strong>СДЕК:</strong> на сайте <a href="https://cdek.ru" target="_blank">cdek.ru</a></li>
                            <li><i class="fas fa-external-link-alt"></i> <strong>Почта России:</strong> на сайте <a href="https://www.pochta.ru" target="_blank">pochta.ru</a></li>
                            <li><i class="fas fa-external-link-alt"></i> В нашем личном кабинете в разделе "Мои заказы"</li>
                        </ul>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-header">
                        <div class="faq-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3 class="faq-question">Сколько времени занимает доставка?</h3>
                        <div class="faq-toggle">
                            <i class="fas fa-plus"></i>
                        </div>
                    </div>
                    <div class="faq-body">
                        <p>Сроки доставки зависят от выбранного способа и города назначения:</p>
                        <div class="steps">
                            <div class="step">
                                <div class="step-number">1-3</div>
                                <div class="step-content">
                                    <h4>дня по Санкт-Петербургу</h4>
                                    <p>Курьерская доставка или самовывоз</p>
                                </div>
                            </div>
                            <div class="step">
                                <div class="step-number">2-7</div>
                                <div class="step-content">
                                    <h4>дней по России</h4>
                                    <p>Доставка СДЕК до пункта выдачи</p>
                                </div>
                            </div>
                            <div class="step">
                                <div class="step-number">5-14</div>
                                <div class="step-content">
                                    <h4>дней по всей РФ</h4>
                                    <p>Доставка Почтой России</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-header">
                        <div class="faq-icon">
                            <i class="fas fa-undo"></i>
                        </div>
                        <h3 class="faq-question">Можно ли изменить адрес доставки после оформления заказа?</h3>
                        <div class="faq-toggle">
                            <i class="fas fa-plus"></i>
                        </div>
                    </div>
                    <div class="faq-body">
                        <p>Да, вы можете изменить адрес доставки до момента отправки заказа. Для этого:</p>
                        <ol class="return-steps">
                            <li>Позвоните по телефону <strong>+7 (912) 345-67-89</strong></li>
                            <li>Напишите на email <strong>info@flaxtap.ru</strong></li>
                            <li>Напишите в нашу группу ВКонтакте</li>
                        </ol>
                        <p class="note"><i class="fas fa-info-circle"></i> После отправки заказа изменить адрес доставки невозможно.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-header">
                        <div class="faq-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3 class="faq-question">Безопасна ли онлайн-оплата?</h3>
                        <div class="faq-toggle">
                            <i class="fas fa-plus"></i>
                        </div>
                    </div>
                    <div class="faq-body">
                        <p>Да, оплата на нашем сайте полностью безопасна. Мы используем:</p>
                        <div class="delivery-methods">
                            <div class="method">
                                <i class="fas fa-lock"></i>
                                <h4>SSL-шифрование</h4>
                                <p>Защищенное соединение по протоколу HTTPS</p>
                            </div>
                            <div class="method">
                                <i class="fas fa-credit-card"></i>
                                <h4>Безопасные платежи</h4>
                                <p>Соблюдение стандарта PCI DSS для работы с картами</p>
                            </div>
                            <div class="method">
                                <i class="fas fa-user-shield"></i>
                                <h4>Защита данных</h4>
                                <p>Мы не храним данные вашей карты на своих серверах</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Калькулятор доставки -->
    <section class="about-section improved" style="background: linear-gradient(135deg, #fffbf0 0%, #ffffff 100%);">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Калькулятор доставки</h2>
                <p class="section-subtitle">Рассчитайте стоимость и срок доставки для вашего города</p>
            </div>
            
            <div class="delivery-calculator">
                <div class="calculator-form">
                    <div class="form-group">
                        <label for="city">Город доставки</label>
                        <input type="text" id="city" placeholder="Введите город">
                    </div>
                    <div class="form-group">
                        <label for="weight">Вес заказа</label>
                        <select id="weight">
                            <option value="light">До 1 кг</option>
                            <option value="medium">1-3 кг</option>
                            <option value="heavy">3-5 кг</option>
                            <option value="xheavy">Более 5 кг</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="amount">Сумма заказа</label>
                        <input type="number" id="amount" placeholder="₽" value="3000">
                    </div>
                    <button class="btn btn-primary" id="calculateBtn">Рассчитать</button>
                </div>
                
                <div class="calculator-results">
                    <div class="result-card">
                        <h4>Почта России</h4>
                        <div class="result-details">
                            <div class="result-item">
                                <span class="result-label">Стоимость:</span>
                                <span class="result-value">150 ₽</span>
                            </div>
                            <div class="result-item">
                                <span class="result-label">Срок:</span>
                                <span class="result-value">7-14 дней</span>
                            </div>
                        </div>
                        <div class="result-note">Бесплатно при заказе от 5000 ₽</div>
                    </div>
                    
                    <div class="result-card">
                        <h4>СДЕК (пункт выдачи)</h4>
                        <div class="result-details">
                            <div class="result-item">
                                <span class="result-label">Стоимость:</span>
                                <span class="result-value">250 ₽</span>
                            </div>
                            <div class="result-item">
                                <span class="result-label">Срок:</span>
                                <span class="result-value">3-7 дней</span>
                            </div>
                        </div>
                        <div class="result-note">Бесплатно при заказе от 5000 ₽</div>
                    </div>
                    
                    <div class="result-card">
                        <h4>СДЕК (курьер)</h4>
                        <div class="result-details">
                            <div class="result-item">
                                <span class="result-label">Стоимость:</span>
                                <span class="result-value">450 ₽</span>
                            </div>
                            <div class="result-item">
                                <span class="result-label">Срок:</span>
                                <span class="result-value">2-5 дней</span>
                            </div>
                        </div>
                        <div class="result-note">Быстрая доставка до двери</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include "inc/modal.php"; ?>
    <?php include "inc/footer.php"; ?>

  

    <script src="assets/js/delivery.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="assets/js/nav.js"></script>
    <script src="assets/js/modals.js"></script>
</body>
</html>