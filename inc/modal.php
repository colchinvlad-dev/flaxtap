<?php
// только HTML модальные окна
?>

<!-- Модальное окно избранного -->
<div id="favoritesModal" class="modal">
    <div class="modal-content wide">
        <div class="modal-header">
            <h2><i class="far fa-heart"></i> Избранные товары</h2>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="favorites-container">
                <div class="favorites-empty" id="emptyFavorites">
                    <div class="empty-icon">
                        <i class="far fa-heart"></i>
                    </div>
                    <h3>Список избранного пуст</h3>
                    <p>Добавляйте товары в избранное, чтобы не потерять</p>
                    <button class="btn btn-primary" id="goToCatalog">Перейти в каталог</button>
                </div>
                
                <div class="favorites-list" id="favoritesList">
                    <!-- Товары будут добавляться динамически -->
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="modal-close">Продолжить покупки</button>
            <button class="btn btn-primary" id="clearFavorites">Очистить избранное</button>
        </div>
    </div>
</div>

<!-- Модальное окно корзины -->
<div id="cartModal" class="modal">
    <div class="modal-content wide">
        <div class="modal-header">
            <h2><i class="fas fa-shopping-cart"></i> Корзина</h2>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="cart-container">
                <div class="cart-empty" id="emptyCart">
                    <div class="empty-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3>Ваша корзина пуста</h3>
                    <p>Добавьте товары из каталога</p>
                    <button class="btn btn-primary" id="goToCatalogFromCart">Перейти в каталог</button>
                </div>
                
                <div class="cart-content" id="cartContent">
                    <div class="cart-items">
                        <!-- Товары в корзине будут добавляться динамически -->
                    </div>
                    
                    <div class="cart-summary">
                        <div class="summary-row">
                            <span>Товары (3)</span>
                            <span>5 370 ₽</span>
                        </div>
                        <div class="summary-row">
                            <span>Скидка</span>
                            <span class="discount">-1 080 ₽</span>
                        </div>
                        <div class="summary-row">
                            <span>Доставка</span>
                            <span>Бесплатно</span>
                        </div>
                        <div class="summary-row total">
                            <span>Итого</span>
                            <span class="total-price">4 290 ₽</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="modal-close">Продолжить покупки</button>
            <button class="btn btn-primary" id="goToCheckout">Перейти к оформлению</button>
        </div>
    </div>
</div>

<!-- Модальное окно авторизации/профиля -->
<div id="authModal" class="modal">
    <div class="modal-content auth">
        <div class="modal-header">
            <h2><i class="far fa-user"></i> Вход в личный кабинет</h2>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Блок для отображения ошибок -->
            <?php if (isset($_SESSION['auth_error'])): ?>
            <div class="alert alert-danger mb-3">
                <?php 
                echo htmlspecialchars($_SESSION['auth_error'], ENT_QUOTES, 'UTF-8');
                unset($_SESSION['auth_error']); // Удаляем ошибку после показа
                ?>
            </div>
            <?php endif; ?>
            
            <!-- Блок для отображения успешных сообщений -->
            <?php if (isset($_SESSION['auth_success'])): ?>
            <div class="alert alert-success mb-3">
                <?php 
                echo htmlspecialchars($_SESSION['auth_success'], ENT_QUOTES, 'UTF-8');
                unset($_SESSION['auth_success']);
                ?>
            </div>
            <?php endif; ?>
            
            <div class="auth-tabs">
                <div class="tab-headers">
                    <button class="tab-header active" data-tab="login">Вход</button>
                    <button class="tab-header" data-tab="register">Регистрация</button>
                </div>
                
                <div class="tab-content active" id="loginTab">
                    <form class="auth-form" method="POST" action="/inc/auth_handler.php">
                        <input type="hidden" name="action" value="login">
                        <!-- Передаем URL для редиректа после успешного входа -->
                        <input type="hidden" name="redirect" value="/profile/index.php">
                        
                        <div class="form-group">
                            <label for="loginEmail">Email или телефон</label>
                            <input type="text" id="loginEmail" name="login" placeholder="example@mail.ru" required>
                        </div>
                        <div class="form-group">
                            <label for="loginPassword">Пароль</label>
                            <input type="password" id="loginPassword" name="password" placeholder="Введите пароль" required>
                            <button type="button" class="show-password"><i class="far fa-eye"></i></button>
                        </div>
                        <div class="form-options">
                            <label class="checkbox">
                                <input type="checkbox" name="remember" checked>
                                <span>Запомнить меня</span>
                            </label>
                            <a href="/forgot-password.php" class="forgot-password">Забыли пароль?</a>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Войти</button>
                    </form>
                    
                    <div class="social-login">
                        <p class="divider">Или войдите через</p>
                        <div class="social-buttons">
                            <button class="social-btn vk"><i class="fab fa-vk"></i> ВКонтакте</button>
                            <button class="social-btn google"><i class="fab fa-google"></i> Google</button>
                        </div>
                    </div>
                </div>
                
                <div class="tab-content" id="registerTab">
                    <form class="auth-form" method="POST" action="/inc/auth_handler.php">
                        <input type="hidden" name="action" value="register">
                        <input type="hidden" name="redirect" value="/profile/index.php">
                        
                        <div class="form-group">
                            <label for="regName">Имя</label>
                            <input type="text" id="regName" name="name" placeholder="Ваше имя" required>
                        </div>
                        <div class="form-group">
                            <label for="regEmail">Email</label>
                            <input type="email" id="regEmail" name="email" placeholder="example@mail.ru" required>
                        </div>
                        <div class="form-group">
                            <label for="regPhone">Телефон</label>
                            <input type="tel" id="regPhone" name="telephone" placeholder="+7 (999) 123-45-67" required>
                        </div>
                        <div class="form-group">
                            <label for="regPassword">Пароль</label>
                            <input type="password" id="regPassword" name="password" placeholder="Не менее 6 символов" required>
                            <button type="button" class="show-password"><i class="far fa-eye"></i></button>
                        </div>
                        <div class="form-group">
                            <label for="regConfirmPassword">Подтвердите пароль</label>
                            <input type="password" id="regConfirmPassword" name="confirm_password" placeholder="Повторите пароль" required>
                        </div>
                        <label class="checkbox agreement">
                            <input type="checkbox" name="agreement" required>
                            <span>Я соглашаюсь с <a href="/policy.php">правилами обработки персональных данных</a> и <a href="/terms.php">пользовательским соглашением</a></span>
                        </label>
                        <button type="submit" class="btn btn-primary btn-block">Зарегистрироваться</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Функция для показа/скрытия пароля
document.querySelectorAll('.show-password').forEach(button => {
    button.addEventListener('click', function() {
        const input = this.parentElement.querySelector('input');
        const icon = this.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
});

// Переключение вкладок в модальном окне авторизации
document.querySelectorAll('.tab-header').forEach(header => {
    header.addEventListener('click', function() {
        const tabId = this.getAttribute('data-tab');
        
        // Убираем активный класс у всех вкладок
        document.querySelectorAll('.tab-header').forEach(h => h.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        // Добавляем активный класс текущей вкладке
        this.classList.add('active');
        document.getElementById(tabId + 'Tab').classList.add('active');
        
        // Очищаем сообщения при переключении вкладок
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => alert.style.display = 'none');
    });
});

// Функция для открытия модального окна с определенной вкладкой
function openAuthModal(tab = 'login') {
    const modal = document.getElementById('authModal');
    if (!modal) return;
    
    // Показываем модальное окно
    modal.style.display = 'block';
    
    // Переключаем на нужную вкладку
    document.querySelectorAll('.tab-header').forEach(h => {
        if (h.getAttribute('data-tab') === tab) {
            h.click();
        }
    });
}
</script>