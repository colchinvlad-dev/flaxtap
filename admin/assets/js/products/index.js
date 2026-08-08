        // Показать модальное окно с вопросами по товару
        function showProductQuestions(productId, showUnanswered = false) {
            const modal = document.getElementById('questionsModal');
            const content = document.getElementById('questionsContent');
            
            // Показать загрузку
            content.innerHTML = '<div style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Загрузка...</p></div>';
            
            // Открыть модальное окно
            modal.classList.add('active');
            
            // Загрузить вопросы через AJAX
            fetch(`get_questions.php?product_id=${productId}&unanswered=${showUnanswered ? 1 : 0}`)
                .then(response => response.text())
                .then(html => {
                    content.innerHTML = html;
                })
                .catch(error => {
                    content.innerHTML = `<div class="no-data"><i class="fas fa-exclamation-triangle"></i><p>Ошибка загрузки: ${error}</p></div>`;
                });
        }
        
        // Закрыть модальное окно
        function closeModal() {
            document.getElementById('questionsModal').classList.remove('active');
        }
        
        // Редактировать вопрос
        function editQuestion(questionId) {
            const form = document.getElementById(`answerForm${questionId}`);
            const textarea = document.getElementById(`answer${questionId}`);
            
            if (textarea) {
                textarea.focus();
                form.scrollIntoView({ behavior: 'smooth' });
            }
        }
        
        // Функция для массовых действий
        function bulkAction(action) {
            const selectedIds = [];
            document.querySelectorAll('.product-checkbox:checked').forEach(checkbox => {
                selectedIds.push(checkbox.value);
            });
            
            if (selectedIds.length === 0) {
                alert('Выберите хотя бы один товар');
                return;
            }
            
            let confirmMessage = '';
            switch(action) {
                case 'activate':
                    confirmMessage = 'Активировать выбранные товары?';
                    break;
                case 'deactivate':
                    confirmMessage = 'Деактивировать выбранные товары?';
                    break;
                case 'delete':
                    confirmMessage = 'Удалить выбранные товары?';
                    break;
            }
            
            if (confirm(confirmMessage)) {
                const formData = new FormData();
                formData.append('action', action);
                formData.append('ids', JSON.stringify(selectedIds));
                
                fetch('bulk_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Ошибка: ' + data.message);
                    }
                });
            }
        }
        
        // Закрыть модальное окно при клике вне его
        document.getElementById('questionsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Обработка отправки форм ответов
        document.querySelectorAll('.answer-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const textarea = this.querySelector('textarea');
                if (!textarea.value.trim()) {
                    e.preventDefault();
                    alert('Пожалуйста, введите ответ');
                    textarea.focus();
                }
            });
        });