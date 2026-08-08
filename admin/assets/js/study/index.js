 // Функция для отметки как прочитано/не прочитано
        function toggleRead(requestId, newStatus) {
            fetch('training_request_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'toggle_read',
                    request_id: requestId,
                    is_read: newStatus
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
        
        // Функция для изменения статуса
        function changeStatus(requestId, newStatus) {
            fetch('training_request_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'change_status',
                    request_id: requestId,
                    status: newStatus
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
        
        // Функция для удаления заявки
        function deleteRequest(requestId) {
            if (confirm('Вы уверены, что хотите удалить эту заявку?')) {
                fetch('training_request_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'delete',
                        request_id: requestId
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
        
        // Функция для экспорта в Excel
        function exportToExcel() {
            const params = new URLSearchParams(window.location.search);
            window.open('training_requests_export.php?' + params.toString(), '_blank');
        }
        
        // Автообновление каждые 30 секунд (для новых заявок)
        setInterval(() => {
            if (document.visibilityState === 'visible') {
                const unreadBadge = document.querySelector('.unread-requests .stat-number');
                if (unreadBadge && unreadBadge.textContent > 0) {
                    location.reload();
                }
            }
        }, 30000);