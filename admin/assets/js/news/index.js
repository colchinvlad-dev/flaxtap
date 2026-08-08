// Функция для массовых действий
        function toggleAllCheckboxes() {
            const checkboxes = document.querySelectorAll('.news-checkbox');
            const toggleAll = document.getElementById('toggleAll');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = toggleAll.checked;
            });
        }
        
        function bulkAction(action) {
            const selectedIds = [];
            document.querySelectorAll('.news-checkbox:checked').forEach(checkbox => {
                selectedIds.push(checkbox.value);
            });
            
            if (selectedIds.length === 0) {
                alert('Выберите хотя бы одну новость');
                return;
            }
            
            let confirmMessage = '';
            switch(action) {
                case 'publish':
                    confirmMessage = 'Опубликовать выбранные новости?';
                    break;
                case 'draft':
                    confirmMessage = 'Перевести выбранные новости в черновики?';
                    break;
                case 'archive':
                    confirmMessage = 'Переместить выбранные новости в архив?';
                    break;
                case 'delete':
                    confirmMessage = 'Удалить выбранные новости?';
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
        
        // Экспорт новостей
        function exportNews(format) {
            let url = 'export.php?format=' + format;
            
            // Добавляем параметры фильтрации
            const params = new URLSearchParams(window.location.search);
            params.forEach((value, key) => {
                url += '&' + key + '=' + encodeURIComponent(value);
            });
            
            window.open(url, '_blank');
        }