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
        
        function generatePassword() {
            const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
            let password = "";
            for (let i = 0; i < 12; i++) {
                password += charset.charAt(Math.floor(Math.random() * charset.length));
            }
            document.getElementById('password').value = password;
            document.getElementById('confirm_password').value = password;
            
            // Показать пароль
            const passwordField = document.getElementById('password');
            const confirmField = document.getElementById('confirm_password');
            passwordField.type = 'text';
            confirmField.type = 'text';
            
            // Обновить иконки
            document.querySelector('[onclick="togglePassword(\'password\')"] i').className = 'fas fa-eye-slash';
            document.querySelector('[onclick="togglePassword(\'confirm_password\')"] i').className = 'fas fa-eye-slash';
            
            // Проверить сложность пароля
            checkPasswordStrength(password);
            
            // Показать уведомление
            showNotification('Пароль сгенерирован!', 'success');
        }
        
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            let text = '';
            let color = '';
            
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            switch(strength) {
                case 0:
                case 1:
                    width = '25%';
                    text = 'Слабый';
                    color = '#f56565';
                    break;
                case 2:
                    width = '50%';
                    text = 'Средний';
                    color = '#ed8936';
                    break;
                case 3:
                    width = '75%';
                    text = 'Хороший';
                    color = '#48bb78';
                    break;
                case 4:
                    width = '100%';
                    text = 'Отличный';
                    color = '#38a169';
                    break;
            }
            
            strengthBar.style.width = width;
            strengthBar.style.background = color;
            strengthText.textContent = text;
            strengthText.style.color = color;
        }
        
        function showNotification(message, type) {
            // Создаем временное уведомление
            const notification = document.createElement('div');
            notification.className = `alert alert-${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            `;
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.zIndex = '1000';
            notification.style.maxWidth = '300px';
            
            document.body.appendChild(notification);
            
            // Удаляем уведомление через 3 секунды
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
        
        // Проверка сложности пароля при вводе
        document.getElementById('password').addEventListener('input', function(e) {
            checkPasswordStrength(e.target.value);
        });