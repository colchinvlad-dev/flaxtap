// Мобильное меню
const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
const mainNav = document.querySelector('.main-nav');

mobileMenuBtn.addEventListener('click', () => {
    mainNav.classList.toggle('active');
    mobileMenuBtn.innerHTML = mainNav.classList.contains('active') 
        ? '<i class="fas fa-times"></i>' 
        : '<i class="fas fa-bars"></i>';
});

// FAQ аккордеон - исправленная версия
const faqItems = document.querySelectorAll('.faq-item');

faqItems.forEach(item => {
    const header = item.querySelector('.faq-header');
    const toggleIcon = item.querySelector('.faq-toggle i');
    
    header.addEventListener('click', () => {
        // Закрыть другие открытые элементы
        faqItems.forEach(otherItem => {
            if (otherItem !== item && otherItem.classList.contains('active')) {
                otherItem.classList.remove('active');
                const otherIcon = otherItem.querySelector('.faq-toggle i');
                if (otherIcon) {
                    otherIcon.classList.remove('fa-minus');
                    otherIcon.classList.add('fa-plus');
                }
            }
        });
        
        // Переключить текущий элемент
        item.classList.toggle('active');
        
        // Обновить иконку
        if (toggleIcon) {
            if (item.classList.contains('active')) {
                toggleIcon.classList.remove('fa-plus');
                toggleIcon.classList.add('fa-minus');
            } else {
                toggleIcon.classList.remove('fa-minus');
                toggleIcon.classList.add('fa-plus');
            }
        }
    });
});

// Функция для установки активного пункта меню
function setActiveMenuItem() {
    const currentPage = window.location.pathname.split('/').pop();
    const menuLinks = document.querySelectorAll('.main-nav a');
    
    menuLinks.forEach(link => {
        const linkHref = link.getAttribute('href');
        
        // Удаляем класс active у всех ссылок
        link.classList.remove('active');
        
        // Проверяем, совпадает ли ссылка с текущей страницей
        if (linkHref === currentPage || 
            (currentPage === '' && linkHref === '/') ||
            (currentPage === 'index.php' && linkHref === '/')) {
            link.classList.add('active');
        }
    });
}

// Закрытие меню при клике на ссылку
const navLinks = document.querySelectorAll('.nav-list a');
navLinks.forEach(link => {
    link.addEventListener('click', () => {
        if (window.innerWidth <= 768) {
            mainNav.classList.remove('active');
            mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
        }
    });
});

// Плавная прокрутка
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
            e.preventDefault();
            window.scrollTo({
                top: targetElement.offsetTop - 80,
                behavior: 'smooth'
            });
        }
    });
});

// Обновление года в футере
document.addEventListener('DOMContentLoaded', () => {
    // Добавьте класс для анимации при скролле
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
            }
        });
    }, observerOptions);
    
    // Наблюдаем за элементами для анимации
    document.querySelectorAll('.direction-card-fixed, .product-card-fixed, .stat-item-compact').forEach(el => {
        observer.observe(el);
    });
    
    // Закрытие выпадающего меню при клике вне его
    document.addEventListener('click', (e) => {
        if (window.innerWidth > 768) {
            const dropdowns = document.querySelectorAll('.dropdown');
            dropdowns.forEach(dropdown => {
                if (!dropdown.contains(e.target)) {
                    const menu = dropdown.querySelector('.dropdown-menu');
                    if (menu) {
                        menu.style.opacity = '0';
                        menu.style.visibility = 'hidden';
                        menu.style.transform = 'translateY(15px)';
                    }
                }
            });
        }
    });
});