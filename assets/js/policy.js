// Функционал страницы политики и безопасности
document.addEventListener('DOMContentLoaded', function() {
    // Плавная прокрутка по навигации
    const policyNavItems = document.querySelectorAll('.policy-nav-item');
    const sections = document.querySelectorAll('.policy-section, .sitemap-section');
    
    policyNavItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                // Удаляем активный класс у всех
                policyNavItems.forEach(i => i.classList.remove('active'));
                // Добавляем активный класс текущему
                this.classList.add('active');
                
                // Прокручиваем к секции
                window.scrollTo({
                    top: targetSection.offsetTop - 100,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Отслеживание активной секции при скролле
    window.addEventListener('scroll', function() {
        let current = '';
        const scrollPosition = window.scrollY + 150;
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            
            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                current = section.getAttribute('id');
            }
        });
        
        if (current) {
            policyNavItems.forEach(item => {
                item.classList.remove('active');
                if (item.getAttribute('href') === `#${current}`) {
                    item.classList.add('active');
                }
            });
        }
    });
    
    // Аккордеон политики
    const policyHeaders = document.querySelectorAll('.policy-header');
    
    policyHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const policyItem = this.parentElement;
            const isActive = policyItem.classList.contains('active');
            
            // Закрываем другие открытые элементы
            document.querySelectorAll('.policy-item.active').forEach(item => {
                if (item !== policyItem) {
                    item.classList.remove('active');
                    const toggleIcon = item.querySelector('.policy-toggle i');
                    if (toggleIcon) {
                        toggleIcon.classList.remove('fa-minus');
                        toggleIcon.classList.add('fa-plus');
                    }
                }
            });
            
            // Переключаем текущий элемент
            policyItem.classList.toggle('active');
            
            // Обновляем иконку
            const toggleIcon = policyItem.querySelector('.policy-toggle i');
            if (toggleIcon) {
                if (policyItem.classList.contains('active')) {
                    toggleIcon.classList.remove('fa-plus');
                    toggleIcon.classList.add('fa-minus');
                } else {
                    toggleIcon.classList.remove('fa-minus');
                    toggleIcon.classList.add('fa-plus');
                }
            }
        });
    });
    
    // Поиск по карте сайта
    const sitemapSearch = document.getElementById('sitemapSearch');
    const sitemapCategories = document.querySelectorAll('.sitemap-category');
    
    if (sitemapSearch) {
        sitemapSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            if (searchTerm === '') {
                // Показываем все категории и ссылки
                sitemapCategories.forEach(category => {
                    category.style.display = 'block';
                    const links = category.querySelectorAll('li');
                    links.forEach(link => link.style.display = 'block');
                });
                return;
            }
            
            sitemapCategories.forEach(category => {
                const categoryTitle = category.querySelector('h3').textContent.toLowerCase();
                const links = category.querySelectorAll('li');
                let hasVisibleLinks = false;
                
                links.forEach(link => {
                    const linkText = link.textContent.toLowerCase();
                    if (linkText.includes(searchTerm)) {
                        link.style.display = 'block';
                        hasVisibleLinks = true;
                        
                        // Подсветка найденного текста
                        const originalText = link.textContent;
                        const regex = new RegExp(`(${searchTerm})`, 'gi');
                        link.innerHTML = link.innerHTML.replace(regex, '<mark>$1</mark>');
                    } else {
                        link.style.display = 'none';
                    }
                });
                
                // Показываем/скрываем категорию
                if (categoryTitle.includes(searchTerm) || hasVisibleLinks) {
                    category.style.display = 'block';
                } else {
                    category.style.display = 'none';
                }
            });
        });
    }
    
    // Обработка быстрых ссылок
    const quickLinks = document.querySelectorAll('.quick-link[href^="#"]');
    
    quickLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            if (href === '#') return;
            
            if (href.startsWith('#')) {
                e.preventDefault();
                const targetSection = document.querySelector(href);
                
                if (targetSection) {
                    window.scrollTo({
                        top: targetSection.offsetTop - 100,
                        behavior: 'smooth'
                    });
                    
                    // Обновляем активный пункт навигации
                    policyNavItems.forEach(item => {
                        item.classList.remove('active');
                        if (item.getAttribute('href') === href) {
                            item.classList.add('active');
                        }
                    });
                }
            }
        });
    });
    
    // Автоматическое раскрытие секции при загрузке с якорем
    const hash = window.location.hash;
    if (hash) {
        const targetSection = document.querySelector(hash);
        if (targetSection) {
            // Прокручиваем к секции
            setTimeout(() => {
                window.scrollTo({
                    top: targetSection.offsetTop - 100,
                    behavior: 'smooth'
                });
            }, 100);
            
            // Обновляем активный пункт навигации
            policyNavItems.forEach(item => {
                item.classList.remove('active');
                if (item.getAttribute('href') === hash) {
                    item.classList.add('active');
                }
            });
            
            // Если это секция политики, открываем первый пункт аккордеона
            if (hash === '#privacy') {
                const firstPolicyItem = document.querySelector('.policy-item');
                if (firstPolicyItem && !firstPolicyItem.classList.contains('active')) {
                    firstPolicyItem.classList.add('active');
                    const toggleIcon = firstPolicyItem.querySelector('.policy-toggle i');
                    if (toggleIcon) {
                        toggleIcon.classList.remove('fa-plus');
                        toggleIcon.classList.add('fa-minus');
                    }
                }
            }
        }
    }
    
    // Добавляем стили для подсветки найденного текста
    const style = document.createElement('style');
    style.textContent = `
        mark {
            background-color: #ffeaa7;
            color: #d63031;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: 600;
        }
    `;
    document.head.appendChild(style);
});