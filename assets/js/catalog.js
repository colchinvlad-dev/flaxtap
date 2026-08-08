document.addEventListener('DOMContentLoaded', function() {
    // Переключение вида сетки
    const viewBtns = document.querySelectorAll('.view-btn');
    const productsGrid = document.getElementById('productsGrid');
    
    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const viewType = this.getAttribute('data-view');
            
            viewBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            if (viewType === 'list') {
                productsGrid.classList.add('list-view');
            } else {
                productsGrid.classList.remove('list-view');
            }
        });
    });
    
    // Фильтрация по категориям
    const filterCheckboxes = document.querySelectorAll('.filter-checkbox input');
    const clearFiltersBtn = document.querySelector('.clear-filters');
    
    clearFiltersBtn.addEventListener('click', function() {
        filterCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        
        // Сброс слайдера цены
        const priceInputs = document.querySelectorAll('.price-field input');
        priceInputs[0].value = 0;
        priceInputs[1].value = 10000;
        
        // Сброс селектов
        const selects = document.querySelectorAll('select');
        selects.forEach(select => {
            select.selectedIndex = 0;
        });
        
        // Здесь можно добавить сброс фильтров на сервере
        showNotification('Фильтры сброшены', 'success');
    });
    
    // Слайдер цены
    const sliderMin = document.querySelector('.slider-min');
    const sliderMax = document.querySelector('.slider-max');
    const priceMinInput = document.querySelector('.price-field:nth-child(1) input');
    const priceMaxInput = document.querySelector('.price-field:nth-child(2) input');
    const sliderTrack = document.querySelector('.slider-track');
    
    function updateSlider() {
        const minVal = parseInt(sliderMin.value);
        const maxVal = parseInt(sliderMax.value);
        
        if (minVal > maxVal) {
            sliderMin.value = maxVal;
            sliderMax.value = minVal;
        }
        
        priceMinInput.value = minVal;
        priceMaxInput.value = maxVal;
        
        updateSliderTrack();
    }
    
    function updateSliderTrack() {
        const minVal = parseInt(sliderMin.value);
        const maxVal = parseInt(sliderMax.value);
        const minPercent = (minVal / sliderMax.max) * 100;
        const maxPercent = (maxVal / sliderMax.max) * 100;
        
        sliderTrack.style.background = `linear-gradient(to right, 
            var(--light-gray) ${minPercent}%, 
            var(--primary-color) ${minPercent}%, 
            var(--primary-color) ${maxPercent}%, 
            var(--light-gray) ${maxPercent}%)`;
    }
    
    sliderMin.addEventListener('input', updateSlider);
    sliderMax.addEventListener('input', updateSlider);
    
    priceMinInput.addEventListener('change', function() {
        sliderMin.value = this.value;
        updateSliderTrack();
    });
    
    priceMaxInput.addEventListener('change', function() {
        sliderMax.value = this.value;
        updateSliderTrack();
    });
    
    updateSliderTrack();
    
    // Быстрый просмотр товара
    const quickViewBtns = document.querySelectorAll('.quick-view');
    const quickViewModal = document.querySelector('.quick-view-modal');
    const quickViewContent = document.querySelector('.quick-view-content');
    
    // Пример данных для быстрого просмотра 
    const productsData = {
        1: {
            title: 'Сыворотка с кофеином FlaxSerum',
            category: 'Уходовая косметика',
            price: '1 790 ₽',
            oldPrice: '1 999 ₽',
            image: 'assets/media/products/2154_syvorotka-s-kofeinom-flaxserum.jpg',
            description: 'Сыворотка с кофеином для уменьшения отечности и тонуса кожи. Подходит для всех типов кожи. Объем: 30мл.',
            features: [
                'Уменьшает отечность и темные круги под глазами',
                'Тонизирует и освежает кожу',
                'Содержит натуральный кофеин и гиалуроновую кислоту',
                'Подходит для ежедневного использования'
            ]
        }
        // Добавьте данные для других товаров
    };
    
    quickViewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const product = productsData[productId] || productsData[1];
            
            quickViewContent.innerHTML = `
                <div class="quick-view-image">
                    <img src="${product.image}" alt="${product.title}">
                </div>
                <div class="quick-view-info">
                    <span class="quick-view-category">${product.category}</span>
                    <h2>${product.title}</h2>
                    <p class="quick-view-description">${product.description}</p>
                    
                    <div class="quick-view-features">
                        <h4>Особенности:</h4>
                        <ul>
                            ${product.features.map(feature => `
                                <li><i class="fas fa-check"></i> ${feature}</li>
                            `).join('')}
                        </ul>
                    </div>
                    
                    <div class="quick-view-price">
                        <span class="current-price">${product.price}</span>
                        ${product.oldPrice ? `<span class="old-price">${product.oldPrice}</span>` : ''}
                    </div>
                    
                    <div class="quick-view-actions">
                        <button class="btn-cart" data-id="${productId}">
                            <i class="fas fa-shopping-cart"></i> Добавить в корзину
                        </button>
                        <button class="btn-favorite" data-id="${productId}">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                </div>
            `;
            
            quickViewModal.style.display = 'flex';
            document.body.classList.add('modal-open');
        });
    });
    
    // Закрытие модального окна быстрого просмотра
    const quickViewClose = quickViewModal.querySelector('.modal-close');
    
    quickViewClose.addEventListener('click', function() {
        quickViewModal.style.display = 'none';
        document.body.classList.remove('modal-open');
    });
    
    // Закрытие по клику вне модального окна
    quickViewModal.addEventListener('click', function(e) {
        if (e.target === this) {
            quickViewModal.style.display = 'none';
            document.body.classList.remove('modal-open');
        }
    });
    
    // Добавление товаров в корзину и избранное
    document.addEventListener('click', function(e) {
        // Добавление в корзину
        if (e.target.classList.contains('btn-cart') || e.target.closest('.btn-cart')) {
            const btn = e.target.classList.contains('btn-cart') ? e.target : e.target.closest('.btn-cart');
            const productId = btn.getAttribute('data-id');
            
            // Здесь будет запрос к серверу
            showNotification('Товар добавлен в корзину', 'success');
            
            // Анимация кнопки
            btn.innerHTML = '<i class="fas fa-check"></i>';
            btn.style.background = 'var(--success-color)';
            
            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-shopping-cart"></i>';
                btn.style.background = '';
            }, 2000);
        }
        
        // Добавление в избранное
        if (e.target.classList.contains('btn-favorite') || e.target.closest('.btn-favorite')) {
            const btn = e.target.classList.contains('btn-favorite') ? e.target : e.target.closest('.btn-favorite');
            const productId = btn.getAttribute('data-id');
            const icon = btn.querySelector('i');
            
            if (icon.classList.contains('far')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                btn.classList.add('active');
                showNotification('Товар добавлен в избранное', 'success');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                btn.classList.remove('active');
                showNotification('Товар удален из избранного', 'info');
            }
        }
    });
    
    // Функция показа уведомлений
    function showNotification(message, type = 'success') {
        // Используйте существующую функцию уведомлений из main.js
        if (window.showNotification) {
            window.showNotification(message, type);
        } else {
            // Fallback если функция не определена
            alert(message);
        }
    }
    
    // Аккордеон фильтров
    const filterHeaders = document.querySelectorAll('.filter-header');
    
    filterHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const content = this.nextElementSibling;
            const icon = this.querySelector('i');
            
            content.style.maxHeight = content.style.maxHeight ? null : content.scrollHeight + 'px';
            icon.style.transform = content.style.maxHeight ? 'rotate(180deg)' : 'rotate(0deg)';
        });
        
        // Открыть все фильтры по умолчанию
        const content = header.nextElementSibling;
        content.style.maxHeight = content.scrollHeight + 'px';
        header.querySelector('i').style.transform = 'rotate(180deg)';
    });
    
    // Применение фильтров
    const applyFiltersBtn = document.querySelector('.apply-filters');
    
    applyFiltersBtn.addEventListener('click', function() {
        // Здесь будет логика применения фильтров
        showNotification('Фильтры применены', 'success');
        
        // Показать индикатор загрузки
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Применение...';
        this.disabled = true;
        
        // Имитация загрузки
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-check"></i> Фильтры применены';
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-check"></i> Применить фильтры';
                this.disabled = false;
            }, 1500);
        }, 1000);
    });
});