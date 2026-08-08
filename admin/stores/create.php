<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Основные данные
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $type = mysqli_real_escape_string($conn, trim($_POST['type']));
    $address = mysqli_real_escape_string($conn, trim($_POST['address']));
    $city = mysqli_real_escape_string($conn, trim($_POST['city']));
    
    // Координаты
    $latitude = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : NULL;
    $longitude = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : NULL;
    
    // Контакты
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $manager_name = mysqli_real_escape_string($conn, trim($_POST['manager_name']));
    
    // Время работы
    $working_hours_weekdays = mysqli_real_escape_string($conn, trim($_POST['working_hours_weekdays']));
    $working_hours_saturday = mysqli_real_escape_string($conn, trim($_POST['working_hours_saturday']));
    $working_hours_sunday = mysqli_real_escape_string($conn, trim($_POST['working_hours_sunday']));
    $working_hours_notes = mysqli_real_escape_string($conn, trim($_POST['working_hours_notes']));
    $is_24_7 = isset($_POST['is_24_7']) ? 1 : 0;
    
    // Описание и удобства
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $facilities = mysqli_real_escape_string($conn, trim($_POST['facilities']));
    $area_size = !empty($_POST['area_size']) ? (float)$_POST['area_size'] : NULL;
    
    // Парковка и доступность
    $has_parking = isset($_POST['has_parking']) ? 1 : 0;
    $parking_spots = !empty($_POST['parking_spots']) ? (int)$_POST['parking_spots'] : 0;
    $is_wheelchair_accessible = isset($_POST['is_wheelchair_accessible']) ? 1 : 0;
    
    // Хранение
    $max_order_weight = !empty($_POST['max_order_weight']) ? (float)$_POST['max_order_weight'] : NULL;
    $storage_temperature = mysqli_real_escape_string($conn, trim($_POST['storage_temperature']));
    $has_refrigeration = isset($_POST['has_refrigeration']) ? 1 : 0;
    
    // Статус
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_default = isset($_POST['is_default']) ? 1 : 0;
    $sort_order = !empty($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
    
    // Мета-данные
    $meta_title = mysqli_real_escape_string($conn, trim($_POST['meta_title']));
    $meta_description = mysqli_real_escape_string($conn, trim($_POST['meta_description']));
    $meta_keywords = mysqli_real_escape_string($conn, trim($_POST['meta_keywords']));
    
    // Валидация
    if (empty($name) || empty($address) || empty($city)) {
        $error = 'Заполните обязательные поля: Название, Адрес и Город';
    } else {
        // Начинаем транзакцию
        mysqli_begin_transaction($conn);
        
        try {
            // Если устанавливается магазин по умолчанию, сбрасываем флаг у других
            if ($is_default) {
                $reset_default_query = "UPDATE stores SET is_default = 0 WHERE is_default = 1";
                if (!mysqli_query($conn, $reset_default_query)) {
                    throw new Exception('Ошибка при сбросе магазина по умолчанию: ' . mysqli_error($conn));
                }
            }
            
            // Вставляем магазин
            $current_user_id = $_SESSION['admin_id'];
            
            $insert_query = "INSERT INTO stores (
                name, type, address, city, latitude, longitude,
                phone, email, manager_name,
                working_hours_weekdays, working_hours_saturday, working_hours_sunday,
                working_hours_notes, is_24_7,
                description, facilities, area_size,
                has_parking, parking_spots, is_wheelchair_accessible,
                max_order_weight, storage_temperature, has_refrigeration,
                is_active, is_default, sort_order,
                meta_title, meta_description, meta_keywords,
                created_by, updated_by, created_at, updated_at
            ) VALUES (
                '$name',
                '$type',
                '$address',
                '$city',
                " . ($latitude ? $latitude : "NULL") . ",
                " . ($longitude ? $longitude : "NULL") . ",
                '$phone',
                '$email',
                '$manager_name',
                '$working_hours_weekdays',
                '$working_hours_saturday',
                '$working_hours_sunday',
                '$working_hours_notes',
                $is_24_7,
                '$description',
                '$facilities',
                " . ($area_size ? $area_size : "NULL") . ",
                $has_parking,
                $parking_spots,
                $is_wheelchair_accessible,
                " . ($max_order_weight ? $max_order_weight : "NULL") . ",
                '$storage_temperature',
                $has_refrigeration,
                $is_active,
                $is_default,
                $sort_order,
                '$meta_title',
                '$meta_description',
                '$meta_keywords',
                $current_user_id,
                $current_user_id,
                NOW(),
                NOW()
            )";
            
            if (!mysqli_query($conn, $insert_query)) {
                throw new Exception('Ошибка при создании магазина: ' . mysqli_error($conn));
            }
            
            $store_id = mysqli_insert_id($conn);
            
            // Фиксируем транзакцию
            mysqli_commit($conn);
            
            // Логируем действие
            $admin_name = $_SESSION['user_name'] ?? $_SESSION['admin_name'] ?? 'Неизвестный';
            $log_message = date('Y-m-d H:i:s') . " - Администратор {$admin_name} создал магазин: {$name} (ID: $store_id)\n";
            file_put_contents('../admin_log.txt', $log_message, FILE_APPEND);
            
            $success = 'Магазин успешно создан!';
            
            // Редирект на редактирование
            $_SESSION['success'] = $success;
            header("Location: edit.php?id=$store_id");
            exit();
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание магазина - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style/stores/create.css">
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Создание магазина</h1>
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Назад к списку
            </a>
        </div>
        
        <div class="create-container">
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
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
                               placeholder="Например: Основной магазин">
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="type" class="form-label required">Тип точки</label>
                            <select id="type" name="type" class="form-control" required>
                                <option value="shop">Магазин</option>
                                <option value="warehouse_shop">Склад-магазин</option>
                                <option value="pickup_point">Пункт выдачи</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="city" class="form-label required">Город</label>
                            <input type="text" id="city" name="city" class="form-control" required
                                   placeholder="Например: Санкт-Петербург">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address" class="form-label required">Адрес</label>
                        <textarea id="address" name="address" class="form-control" required rows="2"
                                  placeholder="Полный адрес магазина"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Описание</label>
                        <textarea id="description" name="description" class="form-control" rows="3"
                                  placeholder="Краткое описание магазина"></textarea>
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
                                   placeholder="+7 (XXX) XXX-XX-XX">
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-control"
                                   placeholder="example@flaxtap.ru">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="manager_name" class="form-label">Имя менеджера</label>
                        <input type="text" id="manager_name" name="manager_name" class="form-control"
                               placeholder="ФИО менеджера магазина">
                    </div>
                </div>
                
                <!-- Время работы -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-clock"></i>
                        <h2>Время работы</h2>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_24_7" name="is_24_7" value="1">
                        <label for="is_24_7">Круглосуточно</label>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="working_hours_weekdays" class="form-label">Пн-Пт</label>
                            <input type="text" id="working_hours_weekdays" name="working_hours_weekdays" 
                                   class="form-control" value="9:00-20:00">
                        </div>
                        
                        <div class="form-group">
                            <label for="working_hours_saturday" class="form-label">Суббота</label>
                            <input type="text" id="working_hours_saturday" name="working_hours_saturday" 
                                   class="form-control" value="10:00-18:00">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="working_hours_sunday" class="form-label">Воскресенье</label>
                            <input type="text" id="working_hours_sunday" name="working_hours_sunday" 
                                   class="form-control" value="10:00-18:00">
                        </div>
                        
                        <div class="form-group">
                            <label for="working_hours_notes" class="form-label">Примечания</label>
                            <input type="text" id="working_hours_notes" name="working_hours_notes" 
                                   class="form-control" placeholder="Особые дни, перерывы и т.д.">
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
                                   placeholder="59.938600">
                        </div>
                        
                        <div class="form-group">
                            <label for="longitude" class="form-label">Долгота</label>
                            <input type="number" step="any" id="longitude" name="longitude" class="form-control"
                                   placeholder="30.314100">
                        </div>
                    </div>
                    
                    <div class="map-container" id="map">
                        <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--gray-color);">
                            <i class="fas fa-map-marked-alt" style="font-size: 48px; margin-right: 15px;"></i>
                            <div>
                                <p>Карта будет доступна после указания координат</p>
                                <small>Для определения координат можно использовать <a href="https://yandex.ru/maps" target="_blank">Яндекс.Карты</a></small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Удобства и характеристики -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-concierge-bell"></i>
                        <h2>Удобства и характеристики</h2>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="has_parking" name="has_parking" value="1">
                        <label for="has_parking">Есть парковка</label>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="parking_spots" class="form-label">Количество парковочных мест</label>
                            <input type="number" id="parking_spots" name="parking_spots" class="form-control"
                                   min="0" value="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="area_size" class="form-label">Площадь (м²)</label>
                            <input type="number" step="0.01" id="area_size" name="area_size" class="form-control"
                                   min="0" placeholder="Например: 150.5">
                        </div>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_wheelchair_accessible" name="is_wheelchair_accessible" value="1">
                        <label for="is_wheelchair_accessible">Доступно для инвалидов-колясочников</label>
                    </div>
                    
                    <div class="form-group">
                        <label for="facilities" class="form-label">Удобства и услуги</label>
                        <textarea id="facilities" name="facilities" class="form-control" rows="3"
                                  placeholder="Парковка, примерочные, консультация, бесплатный Wi-Fi и т.д."></textarea>
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
                                   class="form-control" min="0" placeholder="Для самовывоза">
                        </div>
                        
                        <div class="form-group">
                            <label for="storage_temperature" class="form-label">Температурный режим</label>
                            <input type="text" id="storage_temperature" name="storage_temperature" 
                                   class="form-control" placeholder="+15°C до +25°C">
                        </div>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="has_refrigeration" name="has_refrigeration" value="1">
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
                                   min="0" value="0">
                            <span class="hint">Чем меньше число, тем выше в списке</span>
                        </div>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_active" name="is_active" value="1" checked>
                        <label for="is_active">Активный магазин</label>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_default" name="is_default" value="1">
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
                               maxlength="200" placeholder="Заголовок для поисковых систем">
                    </div>
                    
                    <div class="form-group">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea id="meta_description" name="meta_description" class="form-control" rows="3"
                                  maxlength="500" placeholder="Описание для поисковых систем"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="meta_keywords" class="form-label">Meta Keywords</label>
                        <input type="text" id="meta_keywords" name="meta_keywords" class="form-control"
                               placeholder="Ключевые слова через запятую">
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Создать магазин
                    </button>
                    <button type="button" onclick="window.history.back()" class="btn btn-outline">
                        <i class="fas fa-times"></i> Отмена
                    </button>
                </div>
            </form>
        </div>
    </main>
    
    <script src="../assets/js/stores/create.js"></script>
</body>
</html>