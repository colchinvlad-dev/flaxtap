 document.addEventListener('DOMContentLoaded', function() {
            // Переключение табов
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Убираем активный класс у всех кнопок и контента
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Добавляем активный класс текущей кнопке
                    button.classList.add('active');
                    
                    // Показываем соответствующий контент
                    const tabId = button.getAttribute('data-tab');
                    document.getElementById(`${tabId}-tab`).classList.add('active');
                });
            });
            
            // Автоматическое переключение на нужную вкладку из URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('tab') === 'contacts') {
                document.querySelector('[data-tab="contacts"]').click();
            }
        });