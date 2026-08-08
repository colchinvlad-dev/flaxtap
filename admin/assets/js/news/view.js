function copyLink() {
            const link = `${window.location.origin}/news/<?php echo $news['slug']; ?>`;
            navigator.clipboard.writeText(link)
                .then(() => {
                    alert('Ссылка скопирована в буфер обмена');
                })
                .catch(err => {
                    console.error('Ошибка копирования: ', err);
                });
        }
        
        // Переключение вкладок
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                const tabId = this.getAttribute('href').split('tab=')[1];
                
                // Обновляем URL без перезагрузки страницы
                history.pushState(null, '', `?id=<?php echo $news_id; ?>&tab=${tabId}`);
                
                // Активируем вкладку
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                document.getElementById(tabId + 'Tab').classList.add('active');
            });
        });
        
        // Функция для форматирования размера файла
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }