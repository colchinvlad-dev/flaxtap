<!-- Верхняя шапка с контактами -->
<div class="top-header">
    <div class="container">
        <div class="contact-info">
            <div class="contact-item">
                <i class="fas fa-phone-alt"></i>
                <span>+7 (912) 345-67-89</span>
            </div>
            <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <span>info@flaxtap.ru</span>
            </div>
            <div class="contact-item address-trigger">
                <i class="fas fa-map-marker-alt"></i>
                <span id="current-address">Санкт-Петербург, ул. Косметологов, 15</span>
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
        <div class="working-hours">
            <i class="fas fa-clock"></i>
            <span>Пн-Пт: 9:00-20:00, Сб-Вс: 10:00-18:00</span>
        </div>
    </div>
</div>

<!-- Модальное окно выбора адреса с картой -->
<div id="addressModal" class="modal map-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-map-marker-alt"></i> Выберите адрес самовывоза</h2>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="map-container">
                <div id="storeMap"></div>
            </div>
            <div class="address-list">
                <div class="address-item active" data-lat="59.9386" data-lng="30.3141" data-id="1">
                    <div class="address-radio">
                        <div class="radio-circle"></div>
                    </div>
                    <div class="address-info">
                        <h4>Основной магазин</h4>
                        <p><i class="fas fa-map-marker-alt"></i> Санкт-Петербург, ул. Косметологов, 15</p>
                        <p><i class="fas fa-clock"></i> 9:00-20:00 (без выходных)</p>
                        <p><i class="fas fa-phone"></i> +7 (812) 123-45-67</p>
                    </div>
                </div>
                
                <div class="address-item" data-lat="59.9311" data-lng="30.3609" data-id="2">
                    <div class="address-radio">
                        <div class="radio-circle"></div>
                    </div>
                    <div class="address-info">
                        <h4>Магазин на Невском</h4>
                        <p><i class="fas fa-map-marker-alt"></i> Санкт-Петербург, Невский пр., 100</p>
                        <p><i class="fas fa-clock"></i> 10:00-22:00 (без выходных)</p>
                        <p><i class="fas fa-phone"></i> +7 (812) 234-56-78</p>
                    </div>
                </div>
                
                <div class="address-item" data-lat="59.9343" data-lng="30.3351" data-id="3">
                    <div class="address-radio">
                        <div class="radio-circle"></div>
                    </div>
                    <div class="address-info">
                        <h4>Склад-магазин</h4>
                        <p><i class="fas fa-map-marker-alt"></i> Санкт-Петербург, Лиговский пр., 50</p>
                        <p><i class="fas fa-clock"></i> 8:00-18:00 (Пн-Пт)</p>
                        <p><i class="fas fa-phone"></i> +7 (812) 345-67-89</p>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn btn-secondary" id="modal-close">Отмена</button>
                <button class="btn btn-primary" id="confirmAddress">Выбрать этот адрес</button>
            </div>
        </div>
    </div>
</div>

<?php
// Получаем категории из базы данных
$categories_sql = "SELECT id, name, slug FROM categories WHERE parent_id IS NULL AND is_active = 1 ORDER BY sort_order";
$categories_result = $connection->query($categories_sql);
$categories = [];

if ($categories_result && $categories_result->num_rows > 0) {
    while($row = $categories_result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>

<!-- Основная навигация -->
<header class="main-header">
    <div class="container">
        <div class="logo">
            <a href="/"><img src="/assets/media/logo/logo.svg" alt="FlaxTap"></a>
        </div>

        <nav class="main-nav">
            <ul class="nav-list">
                <li><a href="/">Главная</a></li>
                <li class="dropdown">
                    <a href="/subdirectory/" class="dropdown-toggle">Каталог <i class="fas fa-chevron-down"></i></a>
                    <ul class="dropdown-menu">
                        <?php foreach ($categories as $category): ?>
                        <li><a href="/subdirectory/category.php?id=<?php echo (int)$category['id']; ?>"><?php echo escape($category['name']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li><a href="/delivery.php">Доставка</a></li>
                <li><a href="/contacts.php">Контакты</a></li>
                <li><a href="/vacancy.php">Вакансии</a></li>
                <li><a href="/news/">Новости</a></li>
                <li><a href="/training.php" target="_blank">Обучение</a></li>
            </ul>
        </nav>

        <div class="user-actions">
            <button class="icon-btn search-btn" aria-label="Поиск">
                <i class="fas fa-search"></i>
            </button>
            <button class="icon-btn favorite-btn" id="openFavorites" aria-label="Избранное">
                <i class="far fa-heart"></i>
                <span class="badge">3</span>
            </button>
            <button class="icon-btn cart-btn" id="openCart" aria-label="Корзина">
                <i class="fas fa-shopping-cart"></i>
                <span class="badge">3</span>
            </button>
            <button class="icon-btn user-btn" id="openAuth" aria-label="Профиль">
                <i class="far fa-user"></i>
            </button>
            <button class="mobile-menu-btn" aria-label="Меню">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
</header>