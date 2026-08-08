        document.addEventListener('DOMContentLoaded', function() {
            // Обновление статуса в реальном времени
            setInterval(checkOrderStatus, 30000); // Каждые 30 секунд
            
            function checkOrderStatus() {
                fetch('api/check_status.php?id=<?php echo $order_id; ?>')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status_changed) {
                            location.reload();
                        }
                    })
                    .catch(error => console.error('Ошибка:', error));
            }
        });