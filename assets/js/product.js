  document.addEventListener('DOMContentLoaded', function() {
            // Переключение миниатюр
            const thumbnails = document.querySelectorAll('.thumbnail');
            const mainImage = document.getElementById('mainProductImage');
            
            thumbnails.forEach(thumb => {
                thumb.addEventListener('click', function() {
                    // Убираем активный класс у всех миниатюр
                    thumbnails.forEach(t => t.classList.remove('active'));
                    // Добавляем активный класс текущей миниатюре
                    this.classList.add('active');
                    // Меняем основное изображение
                    const newSrc = this.querySelector('img').dataset.full;
                    mainImage.src = newSrc;
                });
            });
            
            // Переключение табов
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabPanes = document.querySelectorAll('.tab-pane');
            
            tabBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const tabId = this.dataset.tab;
                    
                    // Убираем активный класс у всех кнопок и панелей
                    tabBtns.forEach(b => b.classList.remove('active'));
                    tabPanes.forEach(p => p.classList.remove('active'));
                    
                    // Добавляем активный класс текущей кнопке и панели
                    this.classList.add('active');
                    document.getElementById(tabId).classList.add('active');
                });
            });
            
            // Переключение FAQ
            const qaQuestions = document.querySelectorAll('.qa-question');
            
            qaQuestions.forEach(question => {
                question.addEventListener('click', function() {
                    const item = this.parentElement;
                    item.classList.toggle('active');
                });
            });
            
            // Управление количеством товара
            const minusBtn = document.querySelector('.qty-btn.minus');
            const plusBtn = document.querySelector('.qty-btn.plus');
            const qtyInput = document.querySelector('.qty-input');
            
            function updateQuantityButtons() {
                minusBtn.disabled = parseInt(qtyInput.value) <= 1;
                plusBtn.disabled = parseInt(qtyInput.value) >= 10;
            }
            
            minusBtn.addEventListener('click', function() {
                let value = parseInt(qtyInput.value);
                if (value > 1) {
                    qtyInput.value = value - 1;
                    updateQuantityButtons();
                }
            });
            
            plusBtn.addEventListener('click', function() {
                let value = parseInt(qtyInput.value);
                if (value < 10) {
                    qtyInput.value = value + 1;
                    updateQuantityButtons();
                }
            });
            
            qtyInput.addEventListener('change', function() {
                let value = parseInt(this.value);
                if (isNaN(value) || value < 1) value = 1;
                if (value > 10) value = 10;
                this.value = value;
                updateQuantityButtons();
            });
            
            updateQuantityButtons();
            
            // Модальное окно быстрого заказа
            const oneClickBtn = document.querySelector('.btn-buy-one-click');
            const oneClickModal = document.querySelector('.one-click-modal');
            const modalClose = document.querySelector('.one-click-modal .modal-close');
            
            oneClickBtn.addEventListener('click', function() {
                oneClickModal.style.display = 'flex';
                document.body.classList.add('modal-open');
            });
            
            modalClose.addEventListener('click', function() {
                oneClickModal.style.display = 'none';
                document.body.classList.remove('modal-open');
            });
            
            // Закрытие модального окна при клике вне его
            oneClickModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                    document.body.classList.remove('modal-open');
                }
            });
            
            // Форма быстрого заказа
            const quickOrderForm = document.querySelector('.quick-order-form');
            
            quickOrderForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Здесь можно добавить отправку формы на сервер
                const formData = new FormData(this);
                console.log('Данные быстрого заказа:', Object.fromEntries(formData));
                
                // Показываем уведомление об успехе
                showNotification('Заказ успешно оформлен! Мы свяжемся с вами в ближайшее время.', 'success');
                
                // Закрываем модальное окно
                oneClickModal.style.display = 'none';
                document.body.classList.remove('modal-open');
                
                // Очищаем форму
                this.reset();
            });
            
            // Форма вопроса
            const questionForm = document.querySelector('.question-form');
            
            questionForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Здесь можно добавить отправку формы на сервер
                const formData = new FormData(this);
                console.log('Данные вопроса:', Object.fromEntries(formData));
                
                // Показываем уведомление об успехе
                showNotification('Ваш вопрос успешно отправлен! Мы ответим вам в ближайшее время.', 'success');
                
                // Очищаем форму
                this.reset();
            });
            
            // Функция показа уведомлений
            function showNotification(message, type) {
                const notification = document.createElement('div');
                notification.className = `notification ${type === 'success' ? 'notification-success' : ''}`;
                notification.innerHTML = `
                    <div class="notification-content">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                        <span>${message}</span>
                    </div>
                    <button class="notification-close">&times;</button>
                `;
                
                document.body.appendChild(notification);
                
                // Показываем уведомление
                setTimeout(() => notification.classList.add('show'), 10);
                
                // Закрытие уведомления
                const closeBtn = notification.querySelector('.notification-close');
                closeBtn.addEventListener('click', () => {
                    notification.classList.remove('show');
                    setTimeout(() => notification.remove(), 300);
                });
                
                // Автоматическое закрытие через 5 секунд
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.classList.remove('show');
                        setTimeout(() => notification.remove(), 300);
                    }
                }, 5000);
            }
            
            // Добавление в избранное
            const favoriteBtn = document.querySelector('.btn-favorite-single');
            
            favoriteBtn.addEventListener('click', function() {
                this.classList.toggle('active');
                this.querySelector('i').classList.toggle('far');
                this.querySelector('i').classList.toggle('fas');
                
                const message = this.classList.contains('active') 
                    ? 'Товар добавлен в избранное' 
                    : 'Товар удален из избранного';
                
                showNotification(message, 'success');
            });
            
            // Добавление в корзину
            const addToCartBtn = document.querySelector('.btn-add-to-cart');
            
            addToCartBtn.addEventListener('click', function() {
                const quantity = parseInt(qtyInput.value);
                showNotification(`Товар добавлен в корзину (${quantity} шт.)`, 'success');
            });
        });