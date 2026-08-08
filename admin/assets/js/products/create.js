// Инициализация Summernote
        $(document).ready(function() {
            $('.summernote').summernote({
                height: 300,
                lang: 'ru-RU',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
        
        // Автогенерация slug из названия
        document.getElementById('name').addEventListener('input', function() {
            const name = this.value;
            const slugInput = document.getElementById('slug');
            
            if (!slugInput.dataset.manual) {
                const slug = name.toLowerCase()
                    .replace(/[а-яё]/g, function(ch) {
                        const ru = 'абвгдежзийклмнопрстуфхцчшщъыьэюя';
                        const en = 'abvgdeejziyklmnoprstufhcchshshchyeyuya';
                        const index = ru.indexOf(ch);
                        return index >= 0 ? en[index] : ch;
                    })
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .trim();
                
                slugInput.value = slug;
            }
        });
        
        // Пометить, что slug был изменен вручную
        document.getElementById('slug').addEventListener('input', function() {
            this.dataset.manual = 'true';
        });
        
        // Расчет скидки
        function calculateDiscount() {
            const currentPrice = parseFloat(document.getElementById('current_price').value) || 0;
            const oldPrice = parseFloat(document.getElementById('old_price').value) || 0;
            const discountInfo = document.getElementById('discountInfo');
            
            if (oldPrice > currentPrice && oldPrice > 0) {
                const discountPercent = Math.round(((oldPrice - currentPrice) / oldPrice) * 100);
                const discountAmount = oldPrice - currentPrice;
                
                document.getElementById('discountPercent').textContent = discountPercent;
                document.getElementById('discountAmount').textContent = discountAmount.toFixed(2);
                discountInfo.style.display = 'block';
            } else {
                discountInfo.style.display = 'none';
            }
        }
        
        // Превью изображений
        function updateImagePreview(url, previewId) {
            const preview = document.getElementById(previewId);
            if (url) {
                preview.src = url;
                preview.classList.add('visible');
            } else {
                preview.classList.remove('visible');
            }
        }
        
        // Добавление строки атрибута
        let attributeRowCount = 1;
        function addAttributeRow() {
            const container = document.getElementById('attributesContainer');
            const row = document.createElement('div');
            row.className = 'attribute-row';
            row.innerHTML = `
                <input type="text" name="attribute_name[]" class="form-control" placeholder="Название атрибута">
                <input type="text" name="attribute_value[]" class="form-control" placeholder="Значение">
                <button type="button" class="btn-remove" onclick="removeAttributeRow(this)">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(row);
            attributeRowCount++;
        }
        
        function removeAttributeRow(button) {
            if (attributeRowCount > 1) {
                button.parentElement.remove();
                attributeRowCount--;
            }
        }
        
        // Добавление строки изображения
        let imageRowCount = 1;
        function addImageRow() {
            const container = document.getElementById('additionalImagesContainer');
            const row = document.createElement('div');
            row.className = 'image-row';
            row.innerHTML = `
                <input type="text" name="additional_images[]" class="form-control" 
                       placeholder="/assets/media/products/product-image-${imageRowCount + 1}.jpg"
                       onchange="updateImagePreview(this.value, 'addImagePreview${imageRowCount + 1}')">
                <button type="button" class="btn-remove" onclick="removeImageRow(this)">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(row);
            
            // Добавляем превью
            const preview = document.createElement('img');
            preview.id = `addImagePreview${imageRowCount + 1}`;
            preview.className = 'image-preview';
            preview.alt = 'Превью';
            container.appendChild(preview);
            
            imageRowCount++;
        }
        
        function removeImageRow(button) {
            if (imageRowCount > 1) {
                const row = button.parentElement;
                const previewId = row.querySelector('input').getAttribute('onchange').match(/addImagePreview\d+/)[0];
                const preview = document.getElementById(previewId);
                
                if (preview) preview.remove();
                row.remove();
                imageRowCount--;
            }
        }
        
        // Валидация формы
        document.getElementById('productForm').addEventListener('submit', function(e) {
            const requiredFields = ['category_id', 'brand', 'name', 'slug', 'short_description', 
                                   'current_price', 'image_url'];
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
        
        // Инициализация превью для основного изображения
        document.getElementById('image_url').addEventListener('input', function() {
            updateImagePreview(this.value, 'mainImagePreview');
        });