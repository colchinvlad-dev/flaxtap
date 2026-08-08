 // Функция для массовых действий
        function toggleAllCheckboxes() {
            const checkboxes = document.querySelectorAll('.store-checkbox');
            const toggleAll = document.getElementById('toggleAll');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = toggleAll.checked;
            });
        }
        
        function bulkAction(action) {
            const selectedIds = [];
            document.querySelectorAll('.store-checkbox:checked').forEach(checkbox => {
                selectedIds.push(checkbox.value);
            });
            
            if (selectedIds.length === 0) {
                alert('Выберите хотя бы один магазин');
                return;
            }
            
            let confirmMessage = '';
            switch(action) {
                case 'activate':
                    confirmMessage = 'Активировать выбранные магазины?';
                    break;
                case 'deactivate':
                    confirmMessage = 'Деактивировать выбранные магазины?';
                    break;
                case 'delete':
                    confirmMessage = 'Удалить выбранные магазины?';
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
        
        // Функция для установки магазина по умолчанию
        function setAsDefault(storeId) {
            if (confirm('Установить этот магазин как магазин по умолчанию?')) {
                fetch('set_default.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        store_id: storeId
                    })
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