        // Функция для защиты от повторной отправки формы
        function preventDuplicateSubmit(formId, buttonId) {
            const form = document.getElementById(formId);
            const submitBtn = document.getElementById(buttonId);
            
            if (!form || !submitBtn) return;
            
            let isSubmitting = false;
            
            form.addEventListener('submit', function(e) {
                if (isSubmitting) {
                    e.preventDefault();
                    alert('Пожалуйста, подождите. Форма уже отправляется...');
                    return false;
                }
                
                isSubmitting = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Отправка...';
                submitBtn.disabled = true;
                
                // Автоматическое восстановление через 10 секунд
                setTimeout(() => {
                    if (submitBtn.disabled) {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                        isSubmitting = false;
                    }
                }, 10000);
                
                return true;
            });
        }
        
        // Инициализация защиты для формы вопроса
        preventDuplicateSubmit('questionForm', 'submitQuestionBtn');
        
        // Обработка кликов на миниатюры
        document.querySelectorAll('.image-thumbnails .thumbnail').forEach(thumb => {
            thumb.addEventListener('click', function() {
                const fullImage = this.querySelector('img').getAttribute('data-full');
                document.getElementById('mainProductImage').src = fullImage;
                
                // Убираем активный класс у всех миниатюр
                document.querySelectorAll('.image-thumbnails .thumbnail').forEach(t => {
                    t.classList.remove('active');
                });
                
                // Добавляем активный класс текущей миниатюре
                this.classList.add('active');
            });
        });
        
        // Обработка вкладок
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // Убираем активный класс у всех кнопок и панелей
                document.querySelectorAll('.tab-btn').forEach(b => {
                    b.classList.remove('active');
                });
                document.querySelectorAll('.tab-pane').forEach(p => {
                    p.classList.remove('active');
                });
                
                // Добавляем активный класс текущей кнопке и соответствующей панели
                this.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // Обработка раскрытия вопросов
        document.querySelectorAll('.qa-question').forEach(question => {
            question.addEventListener('click', function() {
                const answer = this.nextElementSibling;
                if (answer) {
                    const isHidden = answer.style.display === 'none' || answer.style.display === '';
                    answer.style.display = isHidden ? 'block' : 'none';
                    const icon = this.querySelector('i');
                    if (icon) {
                        if (isHidden) {
                            icon.classList.remove('fa-chevron-down');
                            icon.classList.add('fa-chevron-up');
                        } else {
                            icon.classList.remove('fa-chevron-up');
                            icon.classList.add('fa-chevron-down');
                        }
                    }
                }
            });
        });
        
        // Обработка количества товара
        const plusBtn = document.querySelector('.qty-btn.plus');
        const minusBtn = document.querySelector('.qty-btn.minus');
        const qtyInput = document.querySelector('.qty-input');
        
        if (plusBtn && minusBtn && qtyInput) {
            plusBtn.addEventListener('click', function() {
                let value = parseInt(qtyInput.value) || 1;
                if (value < 10) {
                    value++;
                    qtyInput.value = value;
                    minusBtn.disabled = false;
                }
                if (value >= 10) {
                    this.disabled = true;
                }
            });
            
            minusBtn.addEventListener('click', function() {
                let value = parseInt(qtyInput.value) || 1;
                if (value > 1) {
                    value--;
                    qtyInput.value = value;
                    plusBtn.disabled = false;
                }
                if (value <= 1) {
                    this.disabled = true;
                }
            });
            
            qtyInput.addEventListener('change', function() {
                let value = parseInt(this.value) || 1;
                if (value < 1) value = 1;
                if (value > 10) value = 10;
                this.value = value;
                
                minusBtn.disabled = value <= 1;
                plusBtn.disabled = value >= 10;
            });
        }
        
        // Обработка "Купить в 1 клик"
        const buyOneClickBtn = document.querySelector('.btn-buy-one-click');
        const oneClickModal = document.querySelector('.one-click-modal');
        
        if (buyOneClickBtn && oneClickModal) {
            buyOneClickBtn.addEventListener('click', function() {
                oneClickModal.style.display = 'block';
            });
        }
        
        // Закрытие модальных окон
        document.querySelectorAll('.modal-close').forEach(closeBtn => {
            closeBtn.addEventListener('click', function() {
                this.closest('.modal').style.display = 'none';
            });
        });
        
        // Закрытие модального окна при клике вне его
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        });
        
        // Счетчик символов для поля вопроса
        const questionText = document.getElementById('questionText');
        const charsLeft = document.getElementById('charsLeft');
        
        if (questionText && charsLeft) {
            questionText.addEventListener('input', function() {
                const maxLength = 1000;
                const currentLength = this.value.length;
                const remaining = maxLength - currentLength;
                
                charsLeft.textContent = remaining;
                
                if (remaining < 0) {
                    charsLeft.style.color = 'red';
                    this.value = this.value.substring(0, maxLength);
                    charsLeft.textContent = 0;
                } else if (remaining < 50) {
                    charsLeft.style.color = 'orange';
                } else {
                    charsLeft.style.color = 'green';
                }
            });
            
            // Инициализация счетчика
            if (questionText.value.length > 0) {
                questionText.dispatchEvent(new Event('input'));
            }
        }
        
