<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

$store_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$store_id) {
    header("Location: index.php");
    exit();
}

// Получаем данные магазина
$query = "SELECT s.*, 
          u1.name as created_by_name,
          u2.name as updated_by_name
          FROM stores s
          LEFT JOIN users u1 ON s.created_by = u1.id
          LEFT JOIN users u2 ON s.updated_by = u2.id
          WHERE s.id = $store_id";

$result = mysqli_query($conn, $query);
$store = mysqli_fetch_assoc($result);

if (!$store) {
    header("Location: index.php");
    exit();
}

// Обработка формы
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $latitude = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : NULL;
    $longitude = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : NULL;
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $manager_name = mysqli_real_escape_string($conn, $_POST['manager_name']);
    $working_hours_weekdays = mysqli_real_escape_string($conn, $_POST['working_hours_weekdays']);
    $working_hours_saturday = mysqli_real_escape_string($conn, $_POST['working_hours_saturday']);
    $working_hours_sunday = mysqli_real_escape_string($conn, $_POST['working_hours_sunday']);
    $working_hours_notes = mysqli_real_escape_string($conn, $_POST['working_hours_notes']);
    $is_24_7 = isset($_POST['is_24_7']) ? 1 : 0;
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $facilities = mysqli_real_escape_string($conn, $_POST['facilities']);
    $area_size = !empty($_POST['area_size']) ? (float)$_POST['area_size'] : NULL;
    $has_parking = isset($_POST['has_parking']) ? 1 : 0;
    $parking_spots = !empty($_POST['parking_spots']) ? (int)$_POST['parking_spots'] : 0;
    $is_wheelchair_accessible = isset($_POST['is_wheelchair_accessible']) ? 1 : 0;
    $max_order_weight = !empty($_POST['max_order_weight']) ? (float)$_POST['max_order_weight'] : NULL;
    $storage_temperature = mysqli_real_escape_string($conn, $_POST['storage_temperature']);
    $has_refrigeration = isset($_POST['has_refrigeration']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_default = isset($_POST['is_default']) ? 1 : 0;
    $sort_order = !empty($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
    $meta_title = mysqli_real_escape_string($conn, $_POST['meta_title']);
    $meta_description = mysqli_real_escape_string($conn, $_POST['meta_description']);
    $meta_keywords = mysqli_real_escape_string($conn, $_POST['meta_keywords']);
    
    // Текущий ID пользователя
    $current_user_id = $_SESSION['admin_id'] ?? 0;
    
    // Начинаем транзакцию
    mysqli_begin_transaction($conn);
    
    try {
        // Если устанавливается магазин по умолчанию, сбрасываем флаг у других
        if ($is_default && !$store['is_default']) {
            $reset_default_query = "UPDATE stores SET is_default = 0 WHERE is_default = 1";
            if (!mysqli_query($conn, $reset_default_query)) {
                throw new Exception('Ошибка при сбросе магазина по умолчанию: ' . mysqli_error($conn));
            }
        }
        
        // Подготавливаем запрос на обновление
        $update_query = "UPDATE stores SET 
            name = '$name',
            type = '$type',
            address = '$address',
            city = '$city',
            latitude = " . ($latitude ? $latitude : "NULL") . ",
            longitude = " . ($longitude ? $longitude : "NULL") . ",
            phone = '$phone',
            email = '$email',
            manager_name = '$manager_name',
            working_hours_weekdays = '$working_hours_weekdays',
            working_hours_saturday = '$working_hours_saturday',
            working_hours_sunday = '$working_hours_sunday',
            working_hours_notes = '$working_hours_notes',
            is_24_7 = $is_24_7,
            description = '$description',
            facilities = '$facilities',
            area_size = " . ($area_size ? $area_size : "NULL") . ",
            has_parking = $has_parking,
            parking_spots = $parking_spots,
            is_wheelchair_accessible = $is_wheelchair_accessible,
            max_order_weight = " . ($max_order_weight ? $max_order_weight : "NULL") . ",
            storage_temperature = '$storage_temperature',
            has_refrigeration = $has_refrigeration,
            is_active = $is_active,
            is_default = $is_default,
            sort_order = $sort_order,
            meta_title = '$meta_title',
            meta_description = '$meta_description',
            meta_keywords = '$meta_keywords',
            updated_by = $current_user_id,
            updated_at = NOW()
            WHERE id = $store_id";
        
        // Обновляем магазин
        if (mysqli_query($conn, $update_query)) {
            mysqli_commit($conn);
            
            $success = 'Магазин успешно обновлен';
            
            // Обновляем данные магазина для отображения
            $query = "SELECT s.*, 
                      u1.name as created_by_name,
                      u2.name as updated_by_name
                      FROM stores s
                      LEFT JOIN users u1 ON s.created_by = u1.id
                      LEFT JOIN users u2 ON s.updated_by = u2.id
                      WHERE s.id = $store_id";
            $result = mysqli_query($conn, $query);
            $store = mysqli_fetch_assoc($result);
            
        } else {
            throw new Exception('Ошибка при обновлении магазина: ' . mysqli_error($conn));
        }
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование магазина - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style/stores/edit.css">
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Редактирование магазина</h1>
            <div>
                <a href="view.php?id=<?php echo $store_id; ?>" class="btn btn-outline">
                    <i class="fas fa-eye"></i> Просмотр
                </a>
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Назад
                </a>
            </div>
        </div>
        
        <div class="store-status">
            <div class="status-info">
                <span>Создан: <?php echo date('d.m.Y H:i', strtotime($store['created_at'])); ?></span>
                <span>Обновлен: <?php echo date('d.m.Y H:i', strtotime($store['updated_at'])); ?></span>
                <span>Тип: 
                    <?php 
                    if ($store['type'] == 'shop') echo 'Магазин';
                    elseif ($store['type'] == 'warehouse_shop') echo 'Склад-магазин';
                    else echo 'Пункт выдачи';
                    ?>
                </span>
                <span>Статус: <?php echo $store['is_active'] ? 'Активен' : 'Неактивен'; ?></span>
                <?php if ($store['is_default']): ?>
                    <span style="color: var(--primary-color); font-weight: 600;">Магазин по умолчанию</span>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>
        
        <div class="edit-container">
            <form method="POST" id="storeForm">
                <!-- Основная информация -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-info-circle"></i>
                        <h2>Основная информация</h2>
                    </div>
                    
                    <div class="form-group">
                        <label for="name" class="form-label required">Название магазина</label>
                        <input type="text" id="name" name="name" class="form-control" required
                               value="<?php echo htmlspecialchars($store['name']); ?>">
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="type" class="form-label required">Тип точки</label>
                            <select id="type" name="type" class="form-control" required>
                                <option value="shop" <?php echo $store['type'] == 'shop' ? 'selected' : ''; ?>>Магазин</option>
                                <option value="warehouse_shop" <?php echo $store['type'] == 'warehouse_shop' ? 'selected' : ''; ?>>Склад-магазин</option>
                                <option value="pickup_point" <?php echo $store['type'] == 'pickup_point' ? 'selected' : ''; ?>>Пункт выдачи</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="city" class="form-label required">Город</label>
                            <input type="text" id="city" name="city" class="form-control" required
                                   value="<?php echo htmlspecialchars($store['city']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address" class="form-label required">Адрес</label>
                        <textarea id="address" name="address" class="form-control" required rows="2"><?php echo htmlspecialchars($store['address']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Описание</label>
                        <textarea id="description" name="description" class="form-control" rows="3"><?php echo htmlspecialchars($store['description']); ?></textarea>
                    </div>
                </div>
                
                <!-- Контактная информация -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-address-book"></i>
                        <h2>Контактная информация</h2>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="phone" class="form-label">Телефон</label>
                            <input type="tel" id="phone" name="phone" class="form-control"
                                   value="<?php echo htmlspecialchars($store['phone']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-control"
                                   value="<?php echo htmlspecialchars($store['email']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="manager_name" class="form-label">Имя менеджера</label>
                        <input type="text" id="manager_name" name="manager_name" class="form-control"
                               value="<?php echo htmlspecialchars($store['manager_name']); ?>">
                    </div>
                </div>
                
                <!-- Время работы -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-clock"></i>
                        <h2>Время работы</h2>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_24_7" name="is_24_7" value="1"
                               <?php echo $store['is_24_7'] ? 'checked' : ''; ?>>
                        <label for="is_24_7">Круглосуточно</label>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="working_hours_weekdays" class="form-label">Пн-Пт</label>
                            <input type="text" id="working_hours_weekdays" name="working_hours_weekdays" 
                                   class="form-control" value="<?php echo htmlspecialchars($store['working_hours_weekdays']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="working_hours_saturday" class="form-label">Суббота</label>
                            <input type="text" id="working_hours_saturday" name="working_hours_saturday" 
                                   class="form-control" value="<?php echo htmlspecialchars($store['working_hours_saturday']); ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="working_hours_sunday" class="form-label">Воскресенье</label>
                            <input type="text" id="working_hours_sunday" name="working_hours_sunday" 
                                   class="form-control" value="<?php echo htmlspecialchars($store['working_hours_sunday']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="working_hours_notes" class="form-label">Примечания</label>
                            <input type="text" id="working_hours_notes" name="working_hours_notes" 
                                   class="form-control" value="<?php echo htmlspecialchars($store['working_hours_notes']); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Координаты на карте -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-map-marker-alt"></i>
                        <h2>Координаты на карте</h2>
                    </div>
                    
                    <div class="coordinates-row">
                        <div class="form-group">
                            <label for="latitude" class="form-label">Широта</label>
                            <input type="number" step="any" id="latitude" name="latitude" class="form-control"
                                   value="<?php echo $store['latitude']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="longitude" class="form-label">Долгота</label>
                            <input type="number" step="any" id="longitude" name="longitude" class="form-control"
                                   value="<?php echo $store['longitude']; ?>">
                        </div>
                    </div>
                    
                    <div class="map-container" id="map">
                        <?php if ($store['latitude'] && $store['longitude']): ?>
                            <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--gray-color);">
                                <i class="fas fa-map-marked-alt" style="font-size: 48px; margin-right: 15px;"></i>
                                <div>
                                    <p>Координаты: <?php echo $store['latitude']; ?>, <?php echo $store['longitude']; ?></p>
                                    <small>Для просмотра на карте нужна интеграция с API Яндекс.Карт</small>
                                </div>
                            </div>
                        <?php else: ?>
                            <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--gray-color);">
                                <i class="fas fa-map-marked-alt" style="font-size: 48px; margin-right: 15px;"></i>
                                <div>
                                    <p>Координаты не указаны</p>
                                    <small>Для определения координат можно использовать <a href="https://yandex.ru/maps" target="_blank">Яндекс.Карты</a></small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Удобства и характеристики -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-concierge-bell"></i>
                        <h2>Удобства и характеристики</h2>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="has_parking" name="has_parking" value="1"
                               <?php echo $store['has_parking'] ? 'checked' : ''; ?>>
                        <label for="has_parking">Есть парковка</label>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="parking_spots" class="form-label">Количество парковочных мест</label>
                            <input type="number" id="parking_spots" name="parking_spots" class="form-control"
                                   min="0" value="<?php echo $store['parking_spots']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="area_size" class="form-label">Площадь (м²)</label>
                            <input type="number" step="0.01" id="area_size" name="area_size" class="form-control"
                                   min="0" value="<?php echo $store['area_size']; ?>">
                        </div>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_wheelchair_accessible" name="is_wheelchair_accessible" value="1"
                               <?php echo $store['is_wheelchair_accessible'] ? 'checked' : ''; ?>>
                        <label for="is_wheelchair_accessible">Доступно для инвалидов-колясочников</label>
                    </div>
                    
                    <div class="form-group">
                        <label for="facilities" class="form-label">Удобства и услуги</label>
                        <textarea id="facilities" name="facilities" class="form-control" rows="3"><?php echo htmlspecialchars($store['facilities']); ?></textarea>
                        <span class="hint">Укажите через запятую или список</span>
                    </div>
                </div>
                
                <!-- Условия хранения (для складов) -->
                <div class="form-section" id="storageSection">
                    <div class="section-header">
                        <i class="fas fa-temperature-low"></i>
                        <h2>Условия хранения</h2>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="max_order_weight" class="form-label">Макс. вес заказа (кг)</label>
                            <input type="number" step="0.1" id="max_order_weight" name="max_order_weight" 
                                   class="form-control" min="0" value="<?php echo $store['max_order_weight']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="storage_temperature" class="form-label">Температурный режим</label>
                            <input type="text" id="storage_temperature" name="storage_temperature" 
                                   class="form-control" value="<?php echo htmlspecialchars($store['storage_temperature']); ?>">
                        </div>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="has_refrigeration" name="has_refrigeration" value="1"
                               <?php echo $store['has_refrigeration'] ? 'checked' : ''; ?>>
                        <label for="has_refrigeration">Есть холодильное оборудование</label>
                    </div>
                </div>
                
                <!-- Настройки магазина -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-cog"></i>
                        <h2>Настройки магазина</h2>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="sort_order" class="form-label">Порядок сортировки</label>
                            <input type="number" id="sort_order" name="sort_order" class="form-control"
                                   min="0" value="<?php echo $store['sort_order']; ?>">
                            <span class="hint">Чем меньше число, тем выше в списке</span>
                        </div>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                               <?php echo $store['is_active'] ? 'checked' : ''; ?>>
                        <label for="is_active">Активный магазин</label>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_default" name="is_default" value="1"
                               <?php echo $store['is_default'] ? 'checked' : ''; ?>>
                        <label for="is_default">Магазин по умолчанию</label>
                        <span class="hint">Будет выбран по умолчанию при оформлении заказа</span>
                    </div>
                </div>
                
                <!-- SEO оптимизация -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-search"></i>
                        <h2>SEO оптимизация</h2>
                    </div>
                    
                    <div class="form-group">
                        <label for="meta_title" class="form-label">Meta Title</label>
                        <input type="text" id="meta_title" name="meta_title" class="form-control"
                               value="<?php echo htmlspecialchars($store['meta_title']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea id="meta_description" name="meta_description" class="form-control" rows="3"><?php echo htmlspecialchars($store['meta_description']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="meta_keywords" class="form-label">Meta Keywords</label>
                        <input type="text" id="meta_keywords" name="meta_keywords" class="form-control"
                               value="<?php echo htmlspecialchars($store['meta_keywords']); ?>">
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Сохранить изменения
                    </button>
                    <a href="view.php?id=<?php echo $store_id; ?>" class="btn btn-outline">
                        <i class="fas fa-times"></i> Отмена
                    </a>
                </div>
            </form>
        </div>
    </main>
    
    <script src="../assets/js/stores/edit.js"></script>
</body>
</html>