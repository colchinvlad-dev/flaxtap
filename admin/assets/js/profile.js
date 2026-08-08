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
        
        function calculateTimeSince(dateString) {
            if (!dateString) return 'Никогда';
            
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
            const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
            const diffMinutes = Math.floor(diffMs / (1000 * 60));
            
            if (diffMinutes < 60) {
                return `${diffMinutes} мин. назад`;
            } else if (diffHours < 24) {
                return `${diffHours} час. назад`;
            } else {
                return `${diffDays} дн. назад`;
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Проверяем силу пароля при вводе
            const newPasswordInput = document.getElementById('new_password');
            if (newPasswordInput) {
                newPasswordInput.addEventListener('input', function() {
                    checkPasswordStrength(this.value);
                });
            }
            
            // Проверяем совпадение паролей
            const confirmPasswordInput = document.getElementById('confirm_password');
            if (confirmPasswordInput) {
                confirmPasswordInput.addEventListener('input', function() {
                    const newPassword = document.getElementById('new_password').value;
                    const confirm = this.value;
                    const hint = document.getElementById('confirmHint');
                    const submitBtn = document.querySelector('button[name="change_password"]');
                    
                    if (confirm && newPassword !== confirm) {
                        hint.textContent = 'Пароли не совпадают';
                        hint.style.color = '#f56565';
                        if (submitBtn) submitBtn.disabled = true;
                    } else if (confirm && newPassword === confirm) {
                        hint.textContent = 'Пароли совпадают';
                        hint.style.color = '#48bb78';
                        if (submitBtn) submitBtn.disabled = false;
                    } else {
                        hint.textContent = '';
                        if (submitBtn) submitBtn.disabled = false;
                    }
                });
            }
            
            // Обновляем время с последнего входа в реальном времени
            const lastLoginElement = document.querySelector('.last-login-time');
            if (lastLoginElement && lastLoginElement.dataset.time) {
                const timeSince = calculateTimeSince(lastLoginElement.dataset.time);
                lastLoginElement.textContent = timeSince;
            }
        });