document.addEventListener('DOMContentLoaded', function() {
    // Фильтрация новостей
    const filterButtons = document.querySelectorAll('.filter-btn');
    const newsCards = document.querySelectorAll('.news-card');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Удаляем активный класс у всех кнопок
            filterButtons.forEach(btn => btn.classList.remove('active'));
            
            // Добавляем активный класс текущей кнопке
            this.classList.add('active');
            
            const filter = this.textContent;
            
            // Показываем/скрываем карточки в зависимости от фильтра
            newsCards.forEach(card => {
                if (filter === 'Все новости') {
                    card.style.display = 'flex';
                } else {
                    const category = card.querySelector('.news-category').textContent;
                    card.style.display = category === filter ? 'flex' : 'none';
                }
            });
        });
    });
    
    // Поиск по новостям
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const searchInput = this.querySelector('input');
            const searchTerm = searchInput.value.toLowerCase().trim();
            
            if (searchTerm) {
                newsCards.forEach(card => {
                    const title = card.querySelector('.news-title').textContent.toLowerCase();
                    const excerpt = card.querySelector('.news-excerpt').textContent.toLowerCase();
                    
                    if (title.includes(searchTerm) || excerpt.includes(searchTerm)) {
                        card.style.display = 'flex';
                        card.style.animation = 'highlight 1.5s ease';
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                // Добавляем стиль для анимации выделения
                const style = document.createElement('style');
                style.textContent = `
                    @keyframes highlight {
                        0% { background-color: transparent; }
                        50% { background-color: rgba(255, 71, 87, 0.1); }
                        100% { background-color: transparent; }
                    }
                `;
                document.head.appendChild(style);
            }
        });
    }
    
    // Пагинация
    const paginationButtons = document.querySelectorAll('.pagination-btn:not(:disabled)');
    paginationButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (!this.classList.contains('active') && !this.querySelector('i')) {
                // Удаляем активный класс у всех кнопок
                document.querySelectorAll('.pagination-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                
                // Добавляем активный класс текущей кнопке
                this.classList.add('active');
                
                // Имитация загрузки новых новостей
                simulatePageLoad();
            }
        });
    });
    
    function simulatePageLoad() {
        const newsGrid = document.querySelector('.news-grid');
        if (newsGrid) {
            newsGrid.style.opacity = '0.5';
            newsGrid.style.transition = 'opacity 0.3s ease';
            
            setTimeout(() => {
                newsGrid.style.opacity = '1';
            }, 300);
        }
    }
    
    // Подписка на рассылку
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = this.querySelector('input[type="email"]');
            const email = emailInput.value.trim();
            
            if (email && validateEmail(email)) {
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                // Показываем индикатор загрузки
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Отправка...';
                submitBtn.disabled = true;
                
                // Имитация отправки
                setTimeout(() => {
                    submitBtn.innerHTML = '<i class="fas fa-check"></i> Успешно!';
                    submitBtn.style.backgroundColor = 'var(--success-color)';
                    
                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                        submitBtn.style.backgroundColor = '';
                        emailInput.value = '';
                        
                        // Показываем уведомление
                        showNotification('Вы успешно подписались на новости!', 'success');
                    }, 1500);
                }, 1500);
            } else {
                showNotification('Пожалуйста, введите корректный email', 'warning');
            }
        });
    }
    
    function validateEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }
    
    function showNotification(message, type = 'info') {
        // Используем функцию из modals.js, если она существует
        if (typeof window.modalManager !== 'undefined' && typeof window.modalManager.showNotification === 'function') {
            window.modalManager.showNotification(message, type);
        } else {
            // Альтернативная реализация
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                    <span>${message}</span>
                </div>
            `;
            
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: white;
                border-radius: 12px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
                padding: 15px 20px;
                display: flex;
                align-items: center;
                gap: 15px;
                min-width: 300px;
                z-index: 9999;
                border-left: 4px solid ${type === 'success' ? '#00b894' : '#ff4757'};
                animation: slideIn 0.3s ease;
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
            
            // Добавляем стили для анимации
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOut {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    // Обработка галереи в новости
    const galleryItems = document.querySelectorAll('.gallery-item');
    galleryItems.forEach(item => {
        item.addEventListener('click', function() {
            const imgSrc = this.querySelector('img').src;
            openImageModal(imgSrc);
        });
    });
    
    function openImageModal(src) {
        const modal = document.createElement('div');
        modal.className = 'image-modal';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        
        modal.innerHTML = `
            <div class="modal-content" style="max-width: 90vw; max-height: 90vh;">
                <img src="${src}" alt="Увеличенное изображение" style="width: 100%; height: auto; border-radius: 8px;">
                <button class="modal-close" style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.2); border: none; color: white; font-size: 30px; width: 50px; height: 50px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;">×</button>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Анимация появления
        setTimeout(() => {
            modal.style.opacity = '1';
        }, 10);
        
        // Закрытие по кнопке
        const closeBtn = modal.querySelector('.modal-close');
        closeBtn.addEventListener('click', () => {
            modal.style.opacity = '0';
            setTimeout(() => modal.remove(), 300);
        });
        
        // Закрытие по клику вне изображения
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.opacity = '0';
                setTimeout(() => modal.remove(), 300);
            }
        });
        
        // Закрытие по Escape
        document.addEventListener('keydown', function closeModal(e) {
            if (e.key === 'Escape') {
                modal.style.opacity = '0';
                setTimeout(() => {
                    modal.remove();
                    document.removeEventListener('keydown', closeModal);
                }, 300);
            }
        });
    }
});