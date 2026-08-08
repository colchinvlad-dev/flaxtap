// Инициализация редактора
        const descriptionEditor = new Quill('#descriptionEditor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link', 'image'],
                    ['clean']
                ]
            }
        });
        
        // Синхронизация редактора с textarea
        descriptionEditor.on('text-change', function() {
            document.getElementById('description').value = descriptionEditor.root.innerHTML;
        });
        
        // Генерация slug из названия
        document.getElementById('title').addEventListener('input', function() {
            const title = this.value;
            const slug = title.toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/--+/g, '-')
                .trim();
            
            document.getElementById('slug').value = slug;
            updateSlugPreview();
            
            // Также обновляем meta title если он пустой
            if (!document.getElementById('meta_title').value) {
                document.getElementById('meta_title').value = title + ' - Вакансия - FlaxTap';
            }
        });
        
        // Обновление предпросмотра slug
        document.getElementById('slug').addEventListener('input', updateSlugPreview);
        
        function updateSlugPreview() {
            const slug = document.getElementById('slug').value;
            const preview = document.getElementById('slugPreview');
            preview.textContent = `/career/${slug}`;
            preview.href = `/career/${slug}`;
        }
        
        // Активация/деактивация полей локации
        document.getElementById('is_remote').addEventListener('change', function() {
            const locationFields = document.getElementById('locationFields');
            if (this.checked) {
                locationFields.style.display = 'none';
                document.getElementById('location_city').required = false;
                document.getElementById('location_address').required = false;
            } else {
                locationFields.style.display = 'grid';
                document.getElementById('location_city').required = true;
                document.getElementById('location_address').required = true;
            }
        });
        
        // Управление уровнем навыков
        document.querySelectorAll('.skill-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const levelSelect = this.parentNode.parentNode.querySelector('.level-select');
                levelSelect.disabled = !this.checked;
            });
        });
        
        // Сохранение черновика
        function saveDraft() {
            document.getElementById('status').value = 'draft';
            document.getElementById('vacancyForm').submit();
        }
        
        // Валидация формы
        document.getElementById('vacancyForm').addEventListener('submit', function(e) {
            // Синхронизируем редактор перед отправкой
            document.getElementById('description').value = descriptionEditor.root.innerHTML;
            
            // Проверяем заполненность обязательных полей
            const requiredFields = ['title', 'slug', 'contact_email'];
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
        
        // Инициализация
        document.addEventListener('DOMContentLoaded', function() {
            updateSlugPreview();
            // Триггерим событие для скрытия полей локации если выбрана удаленная работа
            document.getElementById('is_remote').dispatchEvent(new Event('change'));
        });