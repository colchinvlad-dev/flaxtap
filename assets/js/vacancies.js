// Функционал страницы вакансий
document.addEventListener('DOMContentLoaded', function() {
    // Фильтрация вакансий
    const filterTabs = document.querySelectorAll('.filter-tab');
    const vacancyCards = document.querySelectorAll('.vacancy-card');
    const searchInput = document.getElementById('vacancySearch');
    const noVacanciesMessage = document.querySelector('.no-vacancies');
    
    // Функция фильтрации
    function filterVacancies() {
        const activeFilter = document.querySelector('.filter-tab.active').dataset.filter;
        const searchTerm = searchInput.value.toLowerCase();
        
        let visibleCount = 0;
        
        vacancyCards.forEach(card => {
            const categories = card.dataset.category;
            const title = card.querySelector('h3').textContent.toLowerCase();
            const description = card.querySelector('.vacancy-description p').textContent.toLowerCase();
            
            const matchesFilter = activeFilter === 'all' || categories.includes(activeFilter);
            const matchesSearch = !searchTerm || 
                title.includes(searchTerm) || 
                description.includes(searchTerm) ||
                categories.includes(searchTerm);
            
            if (matchesFilter && matchesSearch) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        
        // Показываем/скрываем сообщение "нет вакансий"
        if (visibleCount === 0) {
            noVacanciesMessage.style.display = 'block';
        } else {
            noVacanciesMessage.style.display = 'none';
        }
    }
    
    // Обработчики для фильтров
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            filterVacancies();
        });
    });
    
    // Поиск по вакансиям
    searchInput.addEventListener('input', filterVacancies);
    
    // Обработка кнопки "Откликнуться"
    const applyButtons = document.querySelectorAll('.apply-btn');
    const positionSelect = document.getElementById('appPosition');
    
    applyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const vacancyName = this.dataset.vacancy;
            
            // Устанавливаем выбранную вакансию в форме
            for (let option of positionSelect.options) {
                if (option.text === vacancyName) {
                    positionSelect.value = vacancyName;
                    break;
                }
            }
            
            // Прокручиваем к форме
            document.getElementById('applicationForm').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
            
            // Фокус на первом поле формы
            document.getElementById('appName').focus();
        });
    });
    
    // Загрузка файла резюме
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('appResume');
    const fileInfo = document.getElementById('fileInfo');
    
    uploadArea.addEventListener('click', function() {
        fileInput.click();
    });
    
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            const file = this.files[0];
            const fileSize = (file.size / 1024 / 1024).toFixed(2); // MB
            
            if (fileSize > 10) {
                alert('Файл слишком большой. Максимальный размер - 10MB.');
                this.value = '';
                return;
            }
            
            fileInfo.innerHTML = `
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <i class="fas fa-file-alt" style="color: var(--primary-color); margin-right: 10px;"></i>
                        <strong>${file.name}</strong>
                        <span style="color: var(--gray-color); font-size: 0.9rem; margin-left: 10px;">(${fileSize} MB)</span>
                    </div>
                    <button type="button" class="remove-file" style="background: none; border: none; color: var(--gray-color); cursor: pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            fileInfo.classList.add('active');
            
            // Удаление файла
            fileInfo.querySelector('.remove-file').addEventListener('click', function() {
                fileInput.value = '';
                fileInfo.classList.remove('active');
            });
        }
    });
    
    // Drag and drop для загрузки файла
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = 'var(--primary-color)';
        this.style.backgroundColor = 'rgba(255, 71, 87, 0.05)';
    });
    
    uploadArea.addEventListener('dragleave', function() {
        this.style.borderColor = 'var(--light-gray)';
        this.style.backgroundColor = '';
    });
    
    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderColor = 'var(--light-gray)';
        this.style.backgroundColor = '';
        
        if (e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            fileInput.dispatchEvent(new Event('change'));
        }
    });
    
    // Обработка отправки формы
    const vacancyForm = document.getElementById('vacancyForm');
    
    vacancyForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Проверка файла
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const validTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            
            if (!validTypes.includes(file.type)) {
                alert('Пожалуйста, загрузите файл в формате PDF, DOC или DOCX.');
                return;
            }
        }
        
        // Имитация отправки
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Отправка...';
        submitBtn.disabled = true;
        
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            
            // Показываем уведомление
            if (typeof modalManager !== 'undefined') {
                modalManager.showNotification('Ваш отклик успешно отправлен! Мы свяжемся с вами в ближайшее время.', 'success');
            } else {
                alert('Ваш отклик успешно отправлен! Мы свяжемся с вами в ближайшее время.');
            }
            
            // Сбрасываем форму
            vacancyForm.reset();
            fileInfo.classList.remove('active');
            
            // Прокручиваем вверх
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }, 2000);
    });
    
    // Кнопка "Подробнее" на вакансии
    const detailButtons = document.querySelectorAll('.vacancy-details-btn');
    
    detailButtons.forEach(button => {
        button.addEventListener('click', function() {
            const card = this.closest('.vacancy-card');
            const body = card.querySelector('.vacancy-body');
            const requirements = card.querySelector('.vacancy-requirements');
            
            if (body.style.maxHeight && body.style.maxHeight !== '0px') {
                body.style.maxHeight = '0px';
                body.style.opacity = '0';
                requirements.style.display = 'none';
                this.textContent = 'Подробнее';
            } else {
                body.style.maxHeight = body.scrollHeight + 'px';
                body.style.opacity = '1';
                requirements.style.display = 'block';
                this.textContent = 'Свернуть';
            }
        });
    });
});