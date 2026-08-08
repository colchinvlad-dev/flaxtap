function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'fas fa-eye';
            }
        }
        
        function validateForm() {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const submitBtn = document.querySelector('.btn-login');
            
            if (email && password) {
                submitBtn.disabled = false;
                return true;
            } else {
                submitBtn.disabled = true;
                return false;
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const form = document.querySelector('form');
            
            // Проверка заполненности формы
            if (emailInput && passwordInput) {
                emailInput.addEventListener('input', validateForm);
                passwordInput.addEventListener('input', validateForm);
            }
            
            // Валидация email при потере фокуса
            if (emailInput) {
                emailInput.addEventListener('blur', function() {
                    const email = this.value;
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    
                    if (email && !emailRegex.test(email)) {
                        this.classList.add('error');
                        showTooltip(this, 'Введите корректный email адрес');
                    } else {
                        this.classList.remove('error');
                        hideTooltip(this);
                    }
                });
            }
            
            // Показать подсказку
            function showTooltip(element, message) {
                let tooltip = element.nextElementSibling;
                
                if (!tooltip || !tooltip.classList.contains('tooltip')) {
                    tooltip = document.createElement('div');
                    tooltip.className = 'tooltip';
                    element.parentNode.insertBefore(tooltip, element.nextSibling);
                }
                
                tooltip.textContent = message;
                tooltip.style.display = 'block';
            }
            
            // Скрыть подсказку
            function hideTooltip(element) {
                const tooltip = element.nextElementSibling;
                if (tooltip && tooltip.classList.contains('tooltip')) {
                    tooltip.style.display = 'none';
                }
            }
            
            // Обработка отправки формы
            if (form) {
                form.addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('.btn-login');
                    
                    // Показываем индикатор загрузки
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<div class="loading"></div> Подождите...';
                    submitBtn.disabled = true;
                    
                    // Позволяем форме отправиться
                    return true;
                });
            }
            
            // Автофокус на поле email
            if (emailInput) {
                emailInput.focus();
            }
            
            // Восстановить содержимое кнопки при возврате на страницу
            window.addEventListener('pageshow', function(event) {
                const submitBtn = document.querySelector('.btn-login');
                if (submitBtn && submitBtn.innerHTML.includes('loading')) {
                    submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Войти';
                    submitBtn.disabled = false;
                }
            });
        });