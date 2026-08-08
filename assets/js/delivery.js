        document.addEventListener('DOMContentLoaded', function() {
            // Аккордеон для FAQ
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
            
            // Калькулятор доставки (имитация)
            const calculateBtn = document.getElementById('calculateBtn');
            if (calculateBtn) {
                calculateBtn.addEventListener('click', function() {
                    const city = document.getElementById('city').value;
                    const weight = document.getElementById('weight').value;
                    const amount = parseInt(document.getElementById('amount').value);
                    
                    if (!city) {
                        showNotification('Введите город для расчета доставки', 'warning');
                        return;
                    }
                    
                    // Имитация расчета
                    const results = calculateDelivery(city, weight, amount);
                    
                    // Обновляем результаты
                    updateResults(results);
                    
                    showNotification('Стоимость доставки рассчитана для ' + city, 'success');
                });
            }
            
            function calculateDelivery(city, weight, amount) {
                // Базовая логика расчета (упрощенная)
                const isFreeDelivery = amount >= 5000;
                
                return {
                    russianPost: {
                        price: isFreeDelivery ? 0 : 150,
                        days: '7-14 дней',
                        free: isFreeDelivery
                    },
                    cdekPoint: {
                        price: isFreeDelivery ? 0 : 250,
                        days: '3-7 дней',
                        free: isFreeDelivery
                    },
                    cdekCourier: {
                        price: isFreeDelivery ? 300 : 450,
                        days: '2-5 дней',
                        free: isFreeDelivery
                    }
                };
            }
            
            function updateResults(results) {
                // Обновляем карточки с результатами
                const resultCards = document.querySelectorAll('.result-card');
                
                if (resultCards.length >= 3) {
                    // Почта России
                    resultCards[0].querySelector('.result-value').textContent = 
                        results.russianPost.price === 0 ? 'Бесплатно' : results.russianPost.price + ' ₽';
                    resultCards[0].querySelector('.result-note').textContent = 
                        results.russianPost.free ? 'Бесплатно при заказе от 5000 ₽' : 'Бесплатно при заказе от 5000 ₽';
                    
                    // СДЕК пункт выдачи
                    resultCards[1].querySelector('.result-value').textContent = 
                        results.cdekPoint.price === 0 ? 'Бесплатно' : results.cdekPoint.price + ' ₽';
                    resultCards[1].querySelector('.result-note').textContent = 
                        results.cdekPoint.free ? 'Бесплатно при заказе от 5000 ₽' : 'Бесплатно при заказе от 5000 ₽';
                    
                    // СДЕК курьер
                    resultCards[2].querySelector('.result-value').textContent = 
                        results.cdekCourier.price + ' ₽';
                    resultCards[2].querySelector('.result-note').textContent = 
                        'Быстрая доставка до двери';
                }
            }
            
            function showNotification(message, type = 'info') {
                // Создаем уведомление
                const notification = document.createElement('div');
                notification.className = `notification notification-${type}`;
                notification.innerHTML = `
                    <div class="notification-content">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
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
            
            // Обработчик для кнопок выбора тарифа
            document.querySelectorAll('.pricing-card .btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const plan = this.closest('.pricing-card').querySelector('h3').textContent;
                    showNotification(`Тариф "${plan}" выбран. Теперь вы можете оформить заказ.`, 'success');
                });
            });
        });