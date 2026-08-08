  function togglePassword(id) {
            const input = document.getElementById(id);
            const icon = document.querySelector(`[onclick="togglePassword('${id}')"] i`);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }
        
        function checkPasswordStrength(password) {
            const meter = document.getElementById('passwordStrength');
            const hint = document.getElementById('passwordHint');
            
            if (!password) {
                meter.className = 'strength-meter';
                meter.style.width = '0%';
                hint.textContent = '';
                return;
            }
            
            let strength = 0;
            
            // Проверка длины
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            
            // Проверка наличия цифр
            if (/\d/.test(password)) strength++;
            
            // Проверка наличия букв в разных регистрах
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            
            // Проверка наличия специальных символов
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            // Обновляем индикатор
            if (strength <= 2) {
                meter.className = 'strength-meter weak';
                hint.textContent = 'Слабый пароль';
                hint.style.color = '#f56565';
            } else if (strength <= 4) {
                meter.className = 'strength-meter medium';
                hint.textContent = 'Средний пароль';
                hint.style.color = '#ed8936';
            } else {
                meter.className = 'strength-meter strong';
                hint.textContent = 'Сильный пароль';
                hint.style.color = '#48bb78';
            }
        }
        
        function generatePassword() {
            const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
            let password = "";
            for (let i = 0; i < 12; i++) {
                password += charset.charAt(Math.floor(Math.random() * charset.length));
            }
            
            document.getElementById('password').value = password;
            document.getElementById('confirm_password').value = password;
            
            // Проверяем силу пароля
            checkPasswordStrength(password);
            
            // Показываем пароль
            document.getElementById('password').type = 'text';
            document.getElementById('confirm_password').type = 'text';
            
            // Обновляем иконки
            document.querySelector('[onclick="togglePassword(\'password\')"] i').className = 'fas fa-eye-slash';
            document.querySelector('[onclick="togglePassword(\'confirm_password\')"] i').className = 'fas fa-eye-slash';
        }
        
        function validateEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        function validateForm() {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            const submitBtn = document.querySelector('button[type="submit"]');
            
            let isValid = true;
            
            // Валидация имени
            if (!name) {
                document.getElementById('name').classList.add('error');
                isValid = false;
            } else {
                document.getElementById('name').classList.remove('error');
            }
            
            // Валидация email
            if (!email || !validateEmail(email)) {
                document.getElementById('email').classList.add('error');
                isValid = false;
            } else {
                document.getElementById('email').classList.remove('error');
            }
            
            // Валидация пароля
            if (!password || password.length < 6) {
                document.getElementById('password').classList.add('error');
                isValid = false;
            } else {
                document.getElementById('password').classList.remove('error');
            }
            
            // Валидация подтверждения пароля
            if (!confirm || password !== confirm) {
                document.getElementById('confirm_password').classList.add('error');
                isValid = false;
            } else {
                document.getElementById('confirm_password').classList.remove('error');
            }
            
            submitBtn.disabled = !isValid;
            return isValid;
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Проверяем силу пароля при вводе
            const passwordInput = document.getElementById('password');
            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    checkPasswordStrength(this.value);
                    validateForm();
                });
            }
            
            // Проверяем совпадение паролей
            const confirmInput = document.getElementById('confirm_password');
            if (confirmInput) {
                confirmInput.addEventListener('input', validateForm);
            }
            
            // Валидация других полей
            document.getElementById('name').addEventListener('input', validateForm);
            document.getElementById('email').addEventListener('input', validateForm);
            
            // Инициализируем проверку формы
            validateForm();
        });