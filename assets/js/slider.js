// Баннер слайдер
class HeroSlider {
    constructor() {
        this.slides = document.querySelectorAll('.hero-slider .slide');
        this.dots = document.querySelectorAll('.hero-slider .dot');
        this.prevBtn = document.querySelector('.hero-slider .slider-prev');
        this.nextBtn = document.querySelector('.hero-slider .slider-next');
        this.currentSlide = 0;
        this.slideInterval = null;
        this.slideDuration = 5000;
        
        this.init();
    }
    
    init() {
        if (this.slides.length === 0) return;
        
        // Начальная активация
        this.activateSlide(this.currentSlide);
        
        // Обработчики кнопок
        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', () => this.prevSlide());
        }
        
        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', () => this.nextSlide());
        }
        
        // Обработчики точек
        this.dots.forEach((dot, index) => {
            dot.addEventListener('click', () => this.goToSlide(index));
        });
        
        // Автопрокрутка
        this.startAutoSlide();
        
        // Пауза при наведении
        const slider = document.querySelector('.hero-slider');
        if (slider) {
            slider.addEventListener('mouseenter', () => this.stopAutoSlide());
            slider.addEventListener('mouseleave', () => this.startAutoSlide());
        }
    }
    
    activateSlide(index) {
        // Сбросить все слайды
        this.slides.forEach(slide => slide.classList.remove('active'));
        this.dots.forEach(dot => dot.classList.remove('active'));
        
        // Активировать выбранный слайд
        this.slides[index].classList.add('active');
        this.dots[index].classList.add('active');
        this.currentSlide = index;
    }
    
    nextSlide() {
        let nextIndex = this.currentSlide + 1;
        if (nextIndex >= this.slides.length) {
            nextIndex = 0;
        }
        this.activateSlide(nextIndex);
        this.resetAutoSlide();
    }
    
    prevSlide() {
        let prevIndex = this.currentSlide - 1;
        if (prevIndex < 0) {
            prevIndex = this.slides.length - 1;
        }
        this.activateSlide(prevIndex);
        this.resetAutoSlide();
    }
    
    goToSlide(index) {
        this.activateSlide(index);
        this.resetAutoSlide();
    }
    
    startAutoSlide() {
        this.stopAutoSlide();
        this.slideInterval = setInterval(() => this.nextSlide(), this.slideDuration);
    }
    
    stopAutoSlide() {
        if (this.slideInterval) {
            clearInterval(this.slideInterval);
            this.slideInterval = null;
        }
    }
    
    resetAutoSlide() {
        this.stopAutoSlide();
        this.startAutoSlide();
    }
}

// Слайдер товаров
class ProductsSlider {
    constructor(sectionElement) {
        this.section = sectionElement;
        if (!this.section) return;
        
        this.track = this.section.querySelector('.products-track');
        this.products = this.section.querySelectorAll('.product-card-fixed');
        this.prevBtn = this.section.querySelector('.section-prev');
        this.nextBtn = this.section.querySelector('.section-next');
        this.indicators = this.section.querySelectorAll('.indicator');
        
        this.currentSlide = 0;
        this.productsPerView = this.calculateProductsPerView();
        this.totalSlides = Math.ceil(this.products.length / this.productsPerView);
        
        this.init();
        window.addEventListener('resize', () => this.handleResize());
    }
    
    calculateProductsPerView() {
        const width = window.innerWidth;
        if (width <= 480) return 1;
        if (width <= 768) return 2;
        if (width <= 992) return 3;
        return 4;
    }
    
    init() {
        this.updateSlider();
        
        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', () => this.prev());
        }
        
        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', () => this.next());
        }
        
        if (this.indicators.length > 0) {
            this.indicators.forEach((indicator, index) => {
                indicator.addEventListener('click', () => this.goToSlide(index));
            });
        }
        
        // Добавляем перетаскивание для мобильных устройств
        this.addDragSupport();
    }
    
    updateSlider() {
        const slideWidth = 100 / this.productsPerView;
        const translateX = -this.currentSlide * slideWidth;
        this.track.style.transform = `translateX(${translateX}%)`;
        
        // Обновить индикаторы
        if (this.indicators.length > 0) {
            this.indicators.forEach((indicator, index) => {
                indicator.classList.toggle('active', index === this.currentSlide);
            });
        }
    }
    
    next() {
        if (this.currentSlide < this.totalSlides - 1) {
            this.currentSlide++;
        } else {
            this.currentSlide = 0;
        }
        this.updateSlider();
    }
    
    prev() {
        if (this.currentSlide > 0) {
            this.currentSlide--;
        } else {
            this.currentSlide = this.totalSlides - 1;
        }
        this.updateSlider();
    }
    
    goToSlide(slideIndex) {
        if (slideIndex >= 0 && slideIndex < this.totalSlides) {
            this.currentSlide = slideIndex;
            this.updateSlider();
        }
    }
    
    handleResize() {
        this.productsPerView = this.calculateProductsPerView();
        this.totalSlides = Math.ceil(this.products.length / this.productsPerView);
        
        // Скорректировать текущий слайд, если он вне диапазона
        if (this.currentSlide >= this.totalSlides) {
            this.currentSlide = Math.max(0, this.totalSlides - 1);
        }
        
        this.updateSlider();
    }
    
    addDragSupport() {
        let isDragging = false;
        let startPos = 0;
        let currentTranslate = 0;
        let prevTranslate = 0;
        
        this.track.addEventListener('mousedown', (e) => {
            isDragging = true;
            startPos = e.clientX;
            this.track.style.cursor = 'grabbing';
        });
        
        this.track.addEventListener('touchstart', (e) => {
            isDragging = true;
            startPos = e.touches[0].clientX;
        });
        
        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            e.preventDefault();
            const currentPosition = e.clientX;
            const diff = currentPosition - startPos;
            
            if (Math.abs(diff) > 50) {
                if (diff > 0) {
                    this.prev();
                } else {
                    this.next();
                }
                isDragging = false;
            }
        });
        
        document.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            const currentPosition = e.touches[0].clientX;
            const diff = currentPosition - startPos;
            
            if (Math.abs(diff) > 30) {
                if (diff > 0) {
                    this.prev();
                } else {
                    this.next();
                }
                isDragging = false;
            }
        });
        
        document.addEventListener('mouseup', () => {
            isDragging = false;
            this.track.style.cursor = 'grab';
        });
        
        document.addEventListener('touchend', () => {
            isDragging = false;
        });
    }
}

// Инициализация слайдеров при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    // Инициализация баннера
    new HeroSlider();
    
    // Инициализация слайдеров товаров
    const productSections = document.querySelectorAll('.products-section');
    productSections.forEach(section => {
        new ProductsSlider(section);
    });
    
    // Добавляем кнопкам "В корзину" и "В избранное" функционал
    document.querySelectorAll('.btn-cart').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const productCard = this.closest('.product-card-fixed');
            const productName = productCard.querySelector('.product-title').textContent;
            const productPrice = productCard.querySelector('.current-price').textContent;
            
            // Обновляем счетчик в корзине
            const cartBadge = document.querySelector('.cart-btn .badge');
            if (cartBadge) {
                let currentCount = parseInt(cartBadge.textContent) || 0;
                cartBadge.textContent = currentCount + 1;
                cartBadge.style.display = 'flex';
            }
            
            // Показываем уведомление
            showNotification(`Товар "${productName}" добавлен в корзину`, 'success');
        });
    });
    
    document.querySelectorAll('.btn-favorite').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.toggle('active');
            this.innerHTML = this.classList.contains('active') 
                ? '<i class="fas fa-heart"></i>' 
                : '<i class="far fa-heart"></i>';
            
            // Обновляем счетчик в избранном
            const favBadge = document.querySelector('.favorite-btn .badge');
            if (favBadge) {
                let currentCount = parseInt(favBadge.textContent) || 0;
                if (this.classList.contains('active')) {
                    favBadge.textContent = currentCount + 1;
                } else {
                    favBadge.textContent = Math.max(0, currentCount - 1);
                }
                favBadge.style.display = favBadge.textContent > 0 ? 'flex' : 'none';
            }
            
            // Показываем уведомление
            const productName = this.closest('.product-card-fixed').querySelector('.product-title').textContent;
            if (this.classList.contains('active')) {
                showNotification(`Товар "${productName}" добавлен в избранное`, 'success');
            }
        });
    });
    
    // Функция показа уведомлений
    function showNotification(message, type = 'info') {
        // Удаляем предыдущие уведомления
        const oldNotification = document.querySelector('.notification');
        if (oldNotification) oldNotification.remove();
        
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
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
});