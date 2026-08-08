// Инициализация редактора
        const contentEditor = new Quill('#contentEditor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'script': 'sub'}, { 'script': 'super' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    [{ 'direction': 'rtl' }],
                    [{ 'size': ['small', false, 'large', 'huge'] }],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'font': [] }],
                    [{ 'align': [] }],
                    ['link', 'image', 'video'],
                    ['clean']
                ]
            }
        });
        
        // Синхронизация редактора с textarea
        contentEditor.on('text-change', function() {
            document.getElementById('content').value = contentEditor.root.innerHTML;
        });
        
        // Генерация slug из заголовка
        document.getElementById('title').addEventListener('input', function() {
            const title = this.value;
            const slug = title.toLowerCase()
                .replace(/[^\w\u0400-\u04FF\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/--+/g, '-')
                .trim();
            
            document.getElementById('slug').value = slug;
            updateSlugPreview();
            
            // Также обновляем meta title если он пустой
            if (!document.getElementById('meta_title').value) {
                document.getElementById('meta_title').value = title + ' - Новости - FlaxTap';
            }
            
            // Обновляем meta description если он пустой
            if (!document.getElementById('meta_description').value) {
                document.getElementById('meta_description').value = title;
            }
        });
        
        // Обновление предпросмотра slug
        document.getElementById('slug').addEventListener('input', updateSlugPreview);
        
        function updateSlugPreview() {
            const slug = document.getElementById('slug').value;
            const preview = document.getElementById('slugPreview');
            preview.textContent = `/news/${slug}`;
            preview.href = `/news/${slug}`;
        }
        
        // Предпросмотр изображений
        function updateImagePreview(inputId, previewId) {
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);
            
            input.addEventListener('input', function() {
                const url = this.value;
                if (url) {
                    preview.innerHTML = `
                        <img src="${url}" alt="Preview" onerror="this.onerror=null; this.parentElement.innerHTML='<p>Ошибка загрузки</p>';">
                        <p>${url.substring(url.lastIndexOf('/') + 1)}</p>
                    `;
                } else {
                    preview.innerHTML = '<p>Предпросмотр</p>';
                }
            });
        }
        
        // Сохранение черновика
        function saveDraft() {
            document.getElementById('status').value = 'draft';
            document.getElementById('newsForm').submit();
        }
        
        // Валидация формы
        document.getElementById('newsForm').addEventListener('submit', function(e) {
            // Синхронизируем редактор перед отправкой
            document.getElementById('content').value = contentEditor.root.innerHTML;
            
            // Проверяем заполненность обязательных полей
            const requiredFields = ['title', 'slug', 'content'];
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
        
        // Переключение состояния тегов при клике
        document.querySelectorAll('.tag-item').forEach(tag => {
            tag.addEventListener('click', function(e) {
                if (e.target.type !== 'checkbox') {
                    const checkbox = this.querySelector('input[type="checkbox"]');
                    checkbox.checked = !checkbox.checked;
                    this.classList.toggle('selected', checkbox.checked);
                }
            });
        });
        
        // Инициализация
        document.addEventListener('DOMContentLoaded', function() {
            updateSlugPreview();
            updateImagePreview('main_image_url', 'mainImagePreview');
            updateImagePreview('thumbnail_url', 'thumbnailPreview');
        });