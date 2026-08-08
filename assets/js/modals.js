class ModalManager {
    constructor() {
        this.modals = {};
        this.map = null;
        this.selectedAddress = null;
        this.mapObjects = [];
        this.init();
    }
    
    init() {
        console.log('ModalManager инициализирован');
        
        // Найти все модальные окна
        document.querySelectorAll('.modal').forEach(modal => {
            const id = modal.id;
            this.modals[id] = modal;
            console.log(`Найдено модальное окно: ${id}`);
            
            // Закрытие по кнопке
            const closeBtn = modal.querySelector('.modal-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.closeModal(id);
                });
            }
            
            // Закрытие по клику вне окна
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeModal(id);
                }
            });
        });
        
        // Добавляем обработчик Escape для всех модальных окон
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllModals();
            }
        });
        
        // Инициализация триггеров
        this.initTriggers();
        
        // Инициализация демо-данных
        this.initDemoData();
    }
    
    initTriggers() {
        console.log('Инициализация триггеров модальных окон');
        
        // Триггер для выбора адреса
        const addressTrigger = document.querySelector('.address-trigger');
        if (addressTrigger) {
            addressTrigger.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('Открытие модального окна адреса');
                this.openModal('addressModal');
            });
        }
        
        // Триггер для избранного
        const favoritesBtn = document.getElementById('openFavorites');
        if (favoritesBtn) {
            favoritesBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('Открытие модального окна избранного');
                this.openModal('favoritesModal');
            });
        }
        
        // Триггер для корзины
        const cartBtn = document.getElementById('openCart');
        if (cartBtn) {
            cartBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('Открытие модального окна корзины');
                this.openModal('cartModal');
            });
        }
        
        // Триггер для авторизации
        const authBtn = document.getElementById('openAuth');
        if (authBtn) {
            authBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('Открытие модального окна авторизации');
                this.openModal('authModal');
            });
        }
        
        // Подтверждение выбора адреса
        const confirmBtn = document.getElementById('confirmAddress');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('Подтверждение выбора адреса');
                this.confirmAddress();
            });
        }
        
        // Кнопки отмены в модалках
        document.querySelectorAll('#modal-close').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const modal = btn.closest('.modal');
                if (modal) {
                    this.closeModal(modal.id);
                }
            });
        });
        
        // Очистка избранного
        const clearFavoritesBtn = document.getElementById('clearFavorites');
        if (clearFavoritesBtn) {
            clearFavoritesBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.clearFavorites();
            });
        }
        
        // Переход к оформлению заказа
        const checkoutBtn = document.getElementById('goToCheckout');
        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.closeModal('cartModal');
                window.location.href = 'checkout.html';
            });
        }
        
        // Переход в каталог из пустых модалок
        document.querySelectorAll('[id^="goToCatalog"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.closeAllModals();
                window.location.href = 'catalog.html';
            });
        });
        
        // Табы в авторизации
        const tabHeaders = document.querySelectorAll('.tab-header');
        tabHeaders.forEach(header => {
            header.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const tabName = header.getAttribute('data-tab');
                this.switchTab(tabName);
            });
        });
        
        // Показать/скрыть пароль
        const showPasswordBtns = document.querySelectorAll('.show-password');
        showPasswordBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.togglePasswordVisibility(e);
            });
        });
        
        document.querySelectorAll('.auth-form').forEach(form => {
            form.removeEventListener('submit', this.handleAuthSubmit);
        });
        
        // Кнопки социальной авторизации
        document.querySelectorAll('.social-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const provider = btn.classList.contains('vk') ? 'VK' : 'Google';
                this.socialAuth(provider);
            });
        });
        
        // Кнопки добавления в избранное на главной странице
        document.addEventListener('click', (e) => {
            if (e.target.closest('.btn-favorite')) {
                const btn = e.target.closest('.btn-favorite');
                e.preventDefault();
                e.stopPropagation();
                this.toggleFavorite(btn);
            }
        });
        
        // Кнопки добавления в корзину на главной странице
        document.addEventListener('click', (e) => {
            if (e.target.closest('.btn-cart')) {
                const btn = e.target.closest('.btn-cart');
                e.preventDefault();
                e.stopPropagation();
                this.addToCart(btn);
            }
        });
    }
    
    initDemoData() {
        // Уже есть демо-данные, ничего не меняем
    }
    
    openModal(modalId) {
        if (this.modals[modalId]) {
            console.log(`Открытие модального окна: ${modalId}`);
            
            // Закрыть все другие модальные окна
            this.closeAllModals();
            
            // Показать текущее модальное окно
            this.modals[modalId].style.display = 'flex';
            
            // Добавить класс для блокировки скролла на body
            document.body.classList.add('modal-open');
            
            // Инициализация конкретного модального окна
            if (modalId === 'addressModal') {
                // Установить выбранный адрес по умолчанию
                const activeItem = document.querySelector('.address-item.active');
                if (activeItem) {
                    this.selectedAddress = activeItem;
                    console.log('Активный адрес установлен:', activeItem);
                } else {
                    // Если нет активного адреса, выбрать первый
                    const firstItem = document.querySelector('.address-item');
                    if (firstItem) {
                        firstItem.classList.add('active');
                        this.selectedAddress = firstItem;
                        console.log('Первый адрес выбран как активный:', firstItem);
                    }
                }
                
                setTimeout(() => this.initYandexMap(), 100);
            }
            
            // Автофокус на первом инпуте
            const firstInput = this.modals[modalId].querySelector('input:not([type="hidden"])');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 100);
            }
            
            // Назначаем обработчики для выбора адреса при открытии модального окна
            if (modalId === 'addressModal') {
                setTimeout(() => this.setupAddressSelection(), 100);
            }
        } else {
            console.error(`Модальное окно с ID "${modalId}" не найдено`);
        }
    }
    
    closeModal(modalId) {
        if (this.modals[modalId]) {
            console.log(`Закрытие модального окна: ${modalId}`);
            
            // Скрыть модальное окно
            this.modals[modalId].style.display = 'none';
            
            // Проверить, остались ли открытые модальные окна
            const anyModalOpen = Object.values(this.modals).some(modal => modal.style.display === 'flex');
            
            // Если нет открытых модальных окон, разблокировать скролл
            if (!anyModalOpen) {
                document.body.classList.remove('modal-open');
            }
        }
    }
    
    closeAllModals() {
        Object.keys(this.modals).forEach(id => {
            this.closeModal(id);
        });
    }
    
    setupAddressSelection() {
        const addressItems = document.querySelectorAll('.address-item');
        console.log('Найдено адресов для выбора:', addressItems.length);
        
        addressItems.forEach(item => {
            // Удаляем старые обработчики, если они есть
            item.removeEventListener('click', this.handleAddressItemClick);
            
            // Создаем новый обработчик
            const clickHandler = () => {
                console.log('Клик по адресу:', item.dataset.id);
                
                // Убрать активный класс у всех
                addressItems.forEach(i => i.classList.remove('active'));
                
                // Добавить активный класс текущему
                item.classList.add('active');
                this.selectedAddress = item;
                console.log('Выбран адрес:', item);
                
                // Центрировать карту на выбранном адресе
                const lat = parseFloat(item.dataset.lat);
                const lng = parseFloat(item.dataset.lng);
                if (this.map) {
                    this.map.setCenter([lat, lng], 15);
                    
                    // Активировать метку выбранного адреса
                    this.activateMarker(item.dataset.id);
                }
            };
            
            // Сохраняем ссылку на обработчик для последующего удаления
            item._clickHandler = clickHandler;
            
            // Добавляем обработчик
            item.addEventListener('click', clickHandler);
        });
    }
    
    initYandexMap() {
        if (typeof ymaps === 'undefined') {
            console.error('Yandex Maps API не загружен');
            const mapElement = document.getElementById('storeMap');
            if (mapElement) {
                mapElement.innerHTML = 
                    '<div class="map-error">Карта временно недоступна. Проверьте подключение к интернету.</div>';
            }
            return;
        }
        
        const mapElement = document.getElementById('storeMap');
        if (!mapElement) {
            console.error('Элемент карты не найден');
            return;
        }
        
        if (!this.map) {
            try {
                console.log('Инициализация Яндекс Карты');
                
                // Инициализация карты
                ymaps.ready(() => {
                    this.map = new ymaps.Map('storeMap', {
                        center: [59.9386, 30.3141], // Санкт-Петербург
                        zoom: 13,
                        controls: ['zoomControl', 'fullscreenControl']
                    });
                    
                    // Добавление меток для адресов
                    const addressItems = document.querySelectorAll('.address-item');
                    addressItems.forEach(item => {
                        const lat = parseFloat(item.dataset.lat);
                        const lng = parseFloat(item.dataset.lng);
                        const titleElement = item.querySelector('h4');
                        const addressInfo = item.querySelector('.address-info');
                        
                        if (!titleElement || !addressInfo) {
                            console.warn('Не найдены необходимые элементы в адресе:', item);
                            return;
                        }
                        
                        const addressParagraphs = addressInfo.querySelectorAll('p');
                        if (addressParagraphs.length < 3) {
                            console.warn('Недостаточно информации в адресе:', item);
                            return;
                        }
                        
                        const title = titleElement.textContent || 'Адрес';
                        const address = addressParagraphs[0].innerHTML || '';
                        const hours = addressParagraphs[1].innerHTML || '';
                        const phone = addressParagraphs[2].innerHTML || '';
                        
                        // Создаем метку с кастомной иконкой
                        const placemark = new ymaps.Placemark([lat, lng], {
                            balloonContentHeader: `<strong>${title}</strong>`,
                            balloonContentBody: `
                                <div style="padding: 5px 0;">
                                    <div>${address}</div>
                                    <div>${hours}</div>
                                    <div>${phone}</div>
                                </div>
                                <button onclick="modalManager.selectAddressFromYandexMap('${item.dataset.id}')" 
                                        style="background: var(--primary-color); color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; margin-top: 10px; width: 100%;">
                                    Выбрать этот адрес
                                </button>
                            `,
                            hintContent: title
                        }, {
                            iconLayout: 'default#image',
                            iconImageHref: 'data:image/svg+xml;base64,' + btoa(`
                                <svg width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="16" cy="16" r="14" fill="${item.classList.contains('active') ? '#ff4757' : '#2d3436'}" stroke="white" stroke-width="2"/>
                                    <circle cx="16" cy="16" r="6" fill="white"/>
                                </svg>
                            `),
                            iconImageSize: [32, 32],
                            iconImageOffset: [-16, -32]
                        });
                        
                        this.map.geoObjects.add(placemark);
                        this.mapObjects.push({
                            id: item.dataset.id,
                            placemark: placemark,
                            element: item
                        });
                        
                        // При клике на метку выбираем соответствующий адрес в списке
                        placemark.events.add('click', () => {
                            console.log('Клик по метке на карте:', item.dataset.id);
                            addressItems.forEach(i => i.classList.remove('active'));
                            item.classList.add('active');
                            this.selectedAddress = item;
                            
                            // Прокручиваем к выбранному адресу в списке
                            item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        });
                    });
                    
                    // Активируем метку для активного адреса
                    const activeItem = document.querySelector('.address-item.active');
                    if (activeItem) {
                        this.activateMarker(activeItem.dataset.id);
                    }
                });
                
            } catch (error) {
                console.error('Ошибка при инициализации Яндекс Карты:', error);
                if (mapElement) {
                    mapElement.innerHTML = 
                        '<div class="map-error">Ошибка загрузки карты. Попробуйте обновить страницу.</div>';
                }
            }
        }
    }
    
    activateMarker(id) {
        // Сбросить все метки к исходному состоянию
        this.mapObjects.forEach(obj => {
            const isActive = obj.id === id.toString();
            const color = isActive ? '#ff4757' : '#2d3436';
            
            // Обновляем иконку метки
            obj.placemark.options.set({
                iconImageHref: 'data:image/svg+xml;base64,' + btoa(`
                    <svg width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="16" cy="16" r="14" fill="${color}" stroke="white" stroke-width="2"/>
                        <circle cx="16" cy="16" r="6" fill="white"/>
                    </svg>
                `)
            });
            
            // Открываем балун для активной метки
            if (isActive) {
                obj.placemark.balloon.open();
            } else {
                obj.placemark.balloon.close();
            }
        });
    }
    
    selectAddressFromYandexMap(id) {
        const addressItem = document.querySelector(`.address-item[data-id="${id}"]`);
        if (addressItem) {
            document.querySelectorAll('.address-item').forEach(item => item.classList.remove('active'));
            addressItem.classList.add('active');
            this.selectedAddress = addressItem;
            
            // Прокручиваем к выбранному адресу в списке
            addressItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            
            // Активируем метку
            this.activateMarker(id);
        }
    }
    
    confirmAddress() {
        console.log('Начало подтверждения адреса...');
        
        let addressToConfirm = this.selectedAddress;
        console.log('Выбранный адрес (selectedAddress):', addressToConfirm);
        
        // Если адрес не выбран, попробуем найти активный
        if (!addressToConfirm) {
            addressToConfirm = document.querySelector('.address-item.active');
            console.log('Активный адрес найден:', addressToConfirm);
        }
        
        if (!addressToConfirm) {
            console.error('Адрес не выбран и активный адрес не найден');
            this.showNotification('Пожалуйста, выберите адрес из списка', 'warning');
            return;
        }
        
        const addressInfo = addressToConfirm.querySelector('.address-info');
        if (!addressInfo) {
            console.error('Не найден address-info в выбранном адресе');
            this.showNotification('Ошибка при выборе адреса', 'error');
            return;
        }
        
        // Получаем все параграфы внутри address-info
        const addressParagraphs = addressInfo.querySelectorAll('p');
        if (addressParagraphs.length === 0) {
            console.error('Не найдены параграфы в address-info');
            this.showNotification('Ошибка при выборе адреса', 'error');
            return;
        }
        
        // Берем первый параграф, в котором должен быть адрес
        const addressParagraph = addressParagraphs[0];
        
        // Получаем HTML содержимое адреса (с иконками)
        let addressHTML = addressParagraph.innerHTML;
        console.log('HTML адреса:', addressHTML);
        
        // Удаляем HTML-теги (иконки) и лишние пробелы
        let addressText = addressHTML.replace(/<i[^>]*>.*?<\/i>/g, '').trim();
        console.log('Текст адреса после удаления иконок:', addressText);
        
        // Удаляем возможные двойные пробелы и лишние запятые
        addressText = addressText.replace(/\s+/g, ' ').replace(/,\s*,/g, ',').trim();
        console.log('Окончательный текст адреса:', addressText);
        
        const currentAddressSpan = document.getElementById('current-address');
        if (currentAddressSpan && addressText) {
            currentAddressSpan.textContent = addressText;
            console.log('Адрес обновлен в шапке:', addressText);
            this.showNotification('Адрес успешно выбран!', 'success');
        } else {
            console.error('Не удалось обновить адрес. currentAddressSpan:', currentAddressSpan, 'addressText:', addressText);
            this.showNotification('Ошибка при обновлении адреса', 'error');
        }
        
        this.closeModal('addressModal');
    }
    
    clearFavorites() {
        const favoritesList = document.getElementById('favoritesList');
        const emptyFavorites = document.getElementById('emptyFavorites');
        
        if (favoritesList) {
            favoritesList.innerHTML = '';
            favoritesList.style.display = 'none';
            
            if (emptyFavorites) {
                emptyFavorites.style.display = 'flex';
            }
            
            // Обновить счетчик в шапке
            const badge = document.querySelector('.favorite-btn .badge');
            if (badge) {
                badge.textContent = '0';
                badge.style.display = 'none';
            }
            
            this.showNotification('Избранное очищено', 'success');
        }
    }
    
    toggleFavorite(btn) {
        if (!btn) return;
        
        btn.classList.toggle('active');
        const isActive = btn.classList.contains('active');
        
        // Обновляем иконку
        const icon = btn.querySelector('i');
        if (icon) {
            if (isActive) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                btn.style.color = 'var(--primary-color)';
                btn.style.borderColor = 'var(--primary-color)';
                btn.style.backgroundColor = 'rgba(255, 71, 87, 0.05)';
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                btn.style.color = 'var(--gray-color)';
                btn.style.borderColor = 'var(--light-gray)';
                btn.style.backgroundColor = 'white';
            }
        }
        
        // Обновляем счетчик
        const badge = document.querySelector('.favorite-btn .badge');
        if (badge) {
            let currentCount = parseInt(badge.textContent) || 0;
            currentCount = isActive ? currentCount + 1 : Math.max(0, currentCount - 1);
            badge.textContent = currentCount;
            badge.style.display = currentCount > 0 ? 'flex' : 'none';
        }
        
        // Показываем уведомление
        const productCard = btn.closest('.product-card-fixed');
        if (productCard) {
            const productTitle = productCard.querySelector('.product-title');
            if (productTitle) {
                const productName = productTitle.textContent;
                const message = isActive 
                    ? `Товар "${productName}" добавлен в избранное` 
                    : `Товар "${productName}" удален из избранного`;
                this.showNotification(message, isActive ? 'success' : 'info');
            }
        }
    }
    
    addToCart(btn) {
        if (!btn) return;
        
        // Обновляем счетчик
        const badge = document.querySelector('.cart-btn .badge');
        if (badge) {
            let currentCount = parseInt(badge.textContent) || 0;
            badge.textContent = currentCount + 1;
            badge.style.display = 'flex';
        }
        
        // Показываем анимацию
        btn.innerHTML = '<i class="fas fa-check"></i> Добавлено';
        btn.style.backgroundColor = 'var(--success-color)';
        
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-shopping-cart"></i> В корзину';
            btn.style.backgroundColor = '';
        }, 1500);
        
        // Показываем уведомление
        const productCard = btn.closest('.product-card-fixed');
        if (productCard) {
            const productTitle = productCard.querySelector('.product-title');
            if (productTitle) {
                const productName = productTitle.textContent;
                this.showNotification(`Товар "${productName}" добавлен в корзину`, 'success');
            }
        }
    }
    
    switchTab(tabName) {
        const tabHeaders = document.querySelectorAll('.tab-header');
        const tabContents = document.querySelectorAll('.tab-content');
        
        // Деактивировать все табы
        tabHeaders.forEach(header => header.classList.remove('active'));
        tabContents.forEach(content => content.classList.remove('active'));
        
        // Активировать выбранный таб
        const activeHeader = document.querySelector(`.tab-header[data-tab="${tabName}"]`);
        const activeContent = document.getElementById(`${tabName}Tab`);
        
        if (activeHeader) activeHeader.classList.add('active');
        if (activeContent) activeContent.classList.add('active');
    }
    
    togglePasswordVisibility(e) {
        const btn = e.currentTarget;
        const input = btn.parentElement.querySelector('input');
        const icon = btn.querySelector('i');
        
        if (!input || !icon) return;
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    
    socialAuth(provider) {
        console.log(`Авторизация через ${provider}`);
        
        // Показываем индикатор загрузки
        const buttons = document.querySelectorAll('.social-btn');
        buttons.forEach(btn => btn.disabled = true);
        
        // Имитация авторизации
        setTimeout(() => {
            buttons.forEach(btn => btn.disabled = false);
            this.closeModal('authModal');
            this.showNotification(`Успешная авторизация через ${provider}!`, 'success');
        }, 1500);
    }
    
    // УБИРАЕМ AJAX обработку форм авторизации
    // handleAuthSubmit(e) {
    //     // Удалено, теперь обработка на сервере через PHP
    // }
    
    showNotification(message, type = 'info') {
        // Удаляем предыдущие уведомления
        const oldNotification = document.querySelector('.notification');
        if (oldNotification) oldNotification.remove();
        
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close"><i class="fas fa-times"></i></button>
        `;
        
        document.body.appendChild(notification);
        
        // Анимация появления
        setTimeout(() => notification.classList.add('show'), 10);
        
        // Закрытие по кнопке
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        });
        
        // Автоматическое закрытие
        setTimeout(() => {
            if (notification.parentNode) {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }
}

// Создаем глобальную переменную для доступа из HTML
window.modalManager = new ModalManager();

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM загружен, инициализация модальных окон');
    
    // Добавляем обработчики для удаления товаров из корзины и избранного
    document.addEventListener('click', (e) => {
        if (e.target.closest('.remove-item')) {
            const btn = e.target.closest('.remove-item');
            const item = btn.closest('.cart-item, .favorite-item');
            
            if (item) {
                e.preventDefault();
                e.stopPropagation();
                item.remove();
                
                // Обновляем счетчики
                const isCartItem = item.classList.contains('cart-item');
                const badgeClass = isCartItem ? '.cart-btn .badge' : '.favorite-btn .badge';
                const badge = document.querySelector(badgeClass);
                
                if (badge) {
                    let currentCount = parseInt(badge.textContent) || 0;
                    badge.textContent = Math.max(0, currentCount - 1);
                    badge.style.display = badge.textContent > 0 ? 'flex' : 'none';
                }
                
                // Проверяем, пуст ли контейнер
                const container = item.closest('.cart-items, .favorites-list');
                if (container && container.children.length === 0) {
                    const modal = container.closest('.modal');
                    if (modal) {
                        const emptyContainer = modal.querySelector('.cart-empty, .favorites-empty');
                        const contentContainer = modal.querySelector('.cart-content, .favorites-list');
                        
                        if (emptyContainer) emptyContainer.style.display = 'flex';
                        if (contentContainer) contentContainer.style.display = 'none';
                    }
                }
            }
        }
    });
});