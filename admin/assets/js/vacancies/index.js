 // Быстрая смена статуса
        function changeStatus(vacancyId, newStatus) {
            if (confirm('Изменить статус вакансии?')) {
                fetch('change_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${vacancyId}&status=${newStatus}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Ошибка: ' + data.error);
                    }
                });
            }
        }
        
        // Копирование ссылки на вакансию
        function copyVacancyLink(vacancyId) {
            const link = `${window.location.origin}/career/vacancy/${vacancyId}`;
            navigator.clipboard.writeText(link)
                .then(() => {
                    alert('Ссылка скопирована в буфер обмена');
                })
                .catch(err => {
                    console.error('Ошибка копирования: ', err);
                });
        }
        
        // Массовые действия
        document.addEventListener('DOMContentLoaded', function() {
            const checkAll = document.getElementById('checkAll');
            const checkboxes = document.querySelectorAll('.vacancy-checkbox');
            
            if (checkAll) {
                checkAll.addEventListener('change', function() {
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                });
            }
        });
        
        function bulkAction(action) {
            const selectedIds = [];
            document.querySelectorAll('.vacancy-checkbox:checked').forEach(checkbox => {
                selectedIds.push(checkbox.value);
            });
            
            if (selectedIds.length === 0) {
                alert('Выберите хотя бы одну вакансию');
                return;
            }
            
            if (confirm(`Вы уверены, что хотите ${action} выбранные вакансии?`)) {
                fetch('bulk_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: action,
                        ids: selectedIds
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Ошибка: ' + data.error);
                    }
                });
            }
        }