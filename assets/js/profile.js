 // Переключение вкладок
    document.addEventListener('DOMContentLoaded', function() {
        const navItems = document.querySelectorAll('.nav-item');
        const tabContents = document.querySelectorAll('.tab-content');
        
        // Функция для переключения вкладок
        function switchTab(tabName) {
            // Скрываем все вкладки
            tabContents.forEach(tab => tab.classList.remove('active'));
            
            // Показываем выбранную вкладку
            const activeTab = document.getElementById(tabName + 'Tab');
            if (activeTab) {
                activeTab.classList.add('active');
            }
            
            // Обновляем активный пункт меню
            navItems.forEach(item => {
                item.classList.remove('active');
                if (item.getAttribute('data-tab') === tabName) {
                    item.classList.add('active');
                }
            });
            
            // Сохраняем в localStorage
            localStorage.setItem('activeProfileTab', tabName);
            
            // Добавляем в URL
            const url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            window.history.replaceState({}, '', url);
        }
        
        // Обработка кликов по навигации
        navItems.forEach(item => {
            if (!item.classList.contains('logout')) {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const tabName = this.getAttribute('data-tab');
                    switchTab(tabName);
                });
            }
        });
        
        // Восстанавливаем активную вкладку
        const savedTab = localStorage.getItem('activeProfileTab') || 'profile';
        const urlParams = new URLSearchParams(window.location.search);
        const urlTab = urlParams.get('tab');
        const activeTab = urlTab || savedTab;
        
        switchTab(activeTab);
        
        // Инициализация функций профиля
        initProfileFunctions();
        initOrderFunctions();
        initAddressFunctions();
    });
    
    // Функции профиля
    function initProfileFunctions() {
        const editProfileBtn = document.getElementById('editProfileBtn');
        const editProfileForm = document.getElementById('editProfileForm');
        const cancelEditProfile = document.getElementById('cancelEditProfile');
        const profileInfo = document.getElementById('profileInfo');
        
        const changePasswordBtn = document.getElementById('changePasswordBtn');
        const changePasswordForm = document.getElementById('changePasswordForm');
        const cancelChangePassword = document.getElementById('cancelChangePassword');
        
        if (editProfileBtn && editProfileForm) {
            editProfileBtn.addEventListener('click', function() {
                editProfileForm.style.display = 'block';
                profileInfo.style.display = 'none';
                editProfileBtn.style.display = 'none';
            });
        }
        
        if (cancelEditProfile) {
            cancelEditProfile.addEventListener('click', function() {
                editProfileForm.style.display = 'none';
                profileInfo.style.display = 'block';
                editProfileBtn.style.display = 'block';
            });
        }
        
        if (changePasswordBtn && changePasswordForm) {
            changePasswordBtn.addEventListener('click', function() {
                changePasswordForm.style.display = 'block';
                changePasswordBtn.style.display = 'none';
            });
        }
        
        if (cancelChangePassword) {
            cancelChangePassword.addEventListener('click', function() {
                changePasswordForm.style.display = 'none';
                changePasswordBtn.style.display = 'block';
            });
        }
    }
    
    // Функции заказов
    function initOrderFunctions() {
        const newOrderBtn = document.getElementById('newOrderBtn');
        const newOrderForm = document.getElementById('newOrderForm');
        const cancelNewOrder = document.getElementById('cancelNewOrder');
        const addOrderItem = document.getElementById('addOrderItem');
        const orderItemsContainer = document.getElementById('orderItemsContainer');
        const deliveryMethod = document.getElementById('deliveryMethod');
        
        if (newOrderBtn && newOrderForm) {
            newOrderBtn.addEventListener('click', function() {
                newOrderForm.style.display = 'block';
                this.style.display = 'none';
                
                // Скрываем список заказов если есть
                const ordersList = document.querySelector('.orders-list');
                const emptyState = document.querySelector('#ordersTab .empty-state');
                if (ordersList) ordersList.style.display = 'none';
                if (emptyState) emptyState.style.display = 'none';
            });
        }
        
        if (cancelNewOrder) {
            cancelNewOrder.addEventListener('click', function() {
                newOrderForm.style.display = 'none';
                newOrderBtn.style.display = 'block';
                
                // Показываем список заказов если есть
                const ordersList = document.querySelector('.orders-list');
                const emptyState = document.querySelector('#ordersTab .empty-state');
                if (ordersList) ordersList.style.display = 'block';
                if (emptyState) emptyState.style.display = 'flex';
            });
        }
        
        if (addOrderItem && orderItemsContainer) {
            let itemIndex = 1;
            
            addOrderItem.addEventListener('click', function() {
                const newItem = document.createElement('div');
                newItem.className = 'order-item form-row';
                newItem.innerHTML = `
                    <div class="form-group">
                        <label>Товар</label>
                        <select name="items[${itemIndex}][product_id]" class="product-select" required>
                            <option value="">Выберите товар</option>
                            <?php 
                            $all_products_result->data_seek(0);
                            while ($product = $all_products_result->fetch_assoc()): ?>
                                <option value="<?php echo $product['id']; ?>" data-price="<?php echo $product['current_price']; ?>">
                                    <?php echo escape($product['name']); ?> - <?php echo $product['current_price']; ?> ₽
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Количество</label>
                        <input type="number" name="items[${itemIndex}][quantity]" value="1" min="1" class="quantity-input" required>
                    </div>
                    <div class="form-group">
                        <label>Цена</label>
                        <input type="text" class="price-input" value="0 ₽" readonly>
                    </div>
                    <div class="form-group">
                        <label>Сумма</label>
                        <input type="text" class="subtotal-input" value="0 ₽" readonly>
                    </div>
                    <div class="form-group">
                        <button type="button" class="btn btn-danger btn-small remove-item">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                
                orderItemsContainer.appendChild(newItem);
                initOrderItemEvents(newItem);
                itemIndex++;
            });
        }
        
        // Инициализация событий для товаров в заказе
        function initOrderItemEvents(item) {
            const productSelect = item.querySelector('.product-select');
            const quantityInput = item.querySelector('.quantity-input');
            const priceInput = item.querySelector('.price-input');
            const subtotalInput = item.querySelector('.subtotal-input');
            const removeBtn = item.querySelector('.remove-item');
            
            function updateItem() {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const price = selectedOption ? parseFloat(selectedOption.dataset.price) || 0 : 0;
                const quantity = parseInt(quantityInput.value) || 0;
                const subtotal = price * quantity;
                
                priceInput.value = price.toFixed(2) + ' ₽';
                subtotalInput.value = subtotal.toFixed(2) + ' ₽';
                updateOrderTotal();
            }
            
            productSelect.addEventListener('change', updateItem);
            quantityInput.addEventListener('input', updateItem);
            
            if (removeBtn) {
                removeBtn.addEventListener('click', function() {
                    item.remove();
                    updateOrderTotal();
                });
            }
            
            updateItem();
        }
        
        // Обновление общей суммы заказа
        function updateOrderTotal() {
            let itemsTotal = 0;
            
            document.querySelectorAll('.order-item').forEach(item => {
                const subtotalInput = item.querySelector('.subtotal-input');
                const subtotal = parseFloat(subtotalInput.value) || 0;
                itemsTotal += subtotal;
            });
            
            // Стоимость доставки
            let deliveryCost = 0;
            if (deliveryMethod && deliveryMethod.value) {
                const selectedOption = deliveryMethod.options[deliveryMethod.selectedIndex];
                const deliveryPrice = parseFloat(selectedOption.dataset.price) || 0;
                const freeThreshold = parseFloat(selectedOption.dataset.free) || 0;
                
                if (freeThreshold > 0 && itemsTotal >= freeThreshold) {
                    deliveryCost = 0;
                } else {
                    deliveryCost = deliveryPrice;
                }
            }
            
            const orderTotal = itemsTotal + deliveryCost;
            
            document.getElementById('itemsTotal').textContent = itemsTotal.toFixed(2) + ' ₽';
            document.getElementById('deliveryCost').textContent = deliveryCost.toFixed(2) + ' ₽';
            document.getElementById('orderTotal').textContent = orderTotal.toFixed(2) + ' ₽';
        }
        
        // Инициализация для существующих товаров
        document.querySelectorAll('.order-item').forEach(initOrderItemEvents);
        
        if (deliveryMethod) {
            deliveryMethod.addEventListener('change', updateOrderTotal);
        }
        
        // Инициализация
        updateOrderTotal();
    }
    
    // Функции адресов
    function initAddressFunctions() {
        const addAddressBtn = document.getElementById('addAddressBtn');
        const addAddressForm = document.getElementById('addAddressForm');
        const cancelAddAddress = document.getElementById('cancelAddAddress');
        
        if (addAddressBtn && addAddressForm) {
            addAddressBtn.addEventListener('click', function() {
                addAddressForm.style.display = 'block';
                this.style.display = 'none';
                
                // Скрываем список адресов если есть
                const addressesList = document.querySelector('.addresses-list');
                const emptyState = document.querySelector('#addressesTab .empty-state');
                if (addressesList) addressesList.style.display = 'none';
                if (emptyState) emptyState.style.display = 'none';
            });
        }
        
        if (cancelAddAddress) {
            cancelAddAddress.addEventListener('click', function() {
                addAddressForm.style.display = 'none';
                addAddressBtn.style.display = 'block';
                
                // Показываем список адресов если есть
                const addressesList = document.querySelector('.addresses-list');
                const emptyState = document.querySelector('#addressesTab .empty-state');
                if (addressesList) addressesList.style.display = 'block';
                if (emptyState) emptyState.style.display = 'flex';
            });
        }
    }
    
    // Вспомогательные функции
    function viewOrderDetails(orderId) {
        alert('Просмотр деталей заказа #' + orderId);
        // window.location.href = '/order.php?id=' + orderId;
    }
    
    function payOrder(orderId) {
        alert('Оплата заказа #' + orderId);
        // window.location.href = '/payment.php?order_id=' + orderId;
    }
    
    function addToCart(productId) {
        alert('Товар добавлен в корзину!');
        // Здесь можно добавить AJAX запрос
    }
    
    function editAddress(addressId) {
        alert('Редактирование адреса #' + addressId);
    }
    
    function deleteAddress(addressId) {
        if (confirm('Удалить адрес?')) {
            // Здесь можно добавить AJAX запрос
            alert('Адрес удален');
            location.reload();
        }
    }
    
    // Настройки
    document.getElementById('exportDataBtn')?.addEventListener('click', function() {
        alert('Экспорт данных будет выполнен в течение 24 часов');
    });
    
    document.getElementById('deleteAccountBtn')?.addEventListener('click', function() {
        if (confirm('Вы уверены? Это действие нельзя отменить!')) {
            // Здесь можно добавить AJAX запрос
            alert('Аккаунт будет удален');
            window.location.href = '/?action=logout';
        }
    });
    
    document.getElementById('resetSettings')?.addEventListener('click', function() {
        if (confirm('Сбросить все настройки?')) {
            document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
            document.getElementById('language').value = 'ru';
            document.getElementById('currency').value = 'RUB';
        }
    });