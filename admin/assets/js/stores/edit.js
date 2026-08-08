 // Показ/скрытие раздела условий хранения в зависимости от типа
        document.getElementById('type').addEventListener('change', function() {
            const storageSection = document.getElementById('storageSection');
            if (this.value === 'warehouse_shop') {
                storageSection.style.display = 'block';
            } else {
                storageSection.style.display = 'none';
            }
        });
        
        // Инициализация при загрузке
        document.addEventListener('DOMContentLoaded', function() {
            // Триггерим событие для скрытия/показа раздела хранения
            document.getElementById('type').dispatchEvent(new Event('change'));
        });
        
        // Валидация формы
        document.getElementById('storeForm').addEventListener('submit', function(e) {
            // Проверяем заполненность обязательных полей
            const requiredFields = ['name', 'type', 'address', 'city'];
            let isValid = true;
            
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'var(--danger-color)';
                } else {
                    field.style.borderColor = '';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Заполните все обязательные поля');
            }
        });
        
        // Функция для определения координат по адресу
        function geocodeAddress() {
            const address = document.getElementById('address').value;
            const city = document.getElementById('city').value;
            
            if (!address || !city) {
                alert('Укажите адрес и город');
                return;
            }
            
            const fullAddress = city + ', ' + address;
            
            // Используем Яндекс.Карты для геокодирования
            // В реальном проекте нужно получить API ключ
            alert('Функция определения координат требует настройки API Яндекс.Карт\n\nАдрес для поиска: ' + fullAddress);
        }