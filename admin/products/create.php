<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

// Получаем категории для выпадающего списка
$categories_query = "SELECT id, name, parent_id FROM categories WHERE is_active = 1 ORDER BY parent_id, sort_order";
$categories_result = mysqli_query($conn, $categories_query);

// Обработка формы добавления
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $category_id = (int)$_POST['category_id'];
    $brand = mysqli_real_escape_string($conn, $_POST['brand']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $slug = mysqli_real_escape_string($conn, $_POST['slug']);
    $short_description = mysqli_real_escape_string($conn, $_POST['short_description']);
    $full_description = mysqli_real_escape_string($conn, $_POST['full_description']);
    $image_url = mysqli_real_escape_string($conn, $_POST['image_url']);
    $current_price = (float)$_POST['current_price'];
    $old_price = !empty($_POST['old_price']) ? (float)$_POST['old_price'] : NULL;
    $weight = mysqli_real_escape_string($conn, $_POST['weight']);
    $skin_type = mysqli_real_escape_string($conn, $_POST['skin_type']);
    $volume = mysqli_real_escape_string($conn, $_POST['volume']);
    $composition = mysqli_real_escape_string($conn, $_POST['composition']);
    $expiration = mysqli_real_escape_string($conn, $_POST['expiration']);
    $storage_conditions = mysqli_real_escape_string($conn, $_POST['storage_conditions']);
    $usage_method = mysqli_real_escape_string($conn, $_POST['usage_method']);
    $contraindications = mysqli_real_escape_string($conn, $_POST['contraindications']);
    $animal_testing = mysqli_real_escape_string($conn, $_POST['animal_testing']);
    $country_of_origin = mysqli_real_escape_string($conn, $_POST['country_of_origin']);
    $in_stock = isset($_POST['in_stock']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_bestseller = isset($_POST['is_bestseller']) ? 1 : 0;
    $delivery_time = mysqli_real_escape_string($conn, $_POST['delivery_time']);
    $warranty = mysqli_real_escape_string($conn, $_POST['warranty']);
    $sort_order = (int)$_POST['sort_order'];
    $meta_title = mysqli_real_escape_string($conn, $_POST['meta_title']);
    $meta_description = mysqli_real_escape_string($conn, $_POST['meta_description']);
    $meta_keywords = mysqli_real_escape_string($conn, $_POST['meta_keywords']);
    
    // Рассчитываем процент скидки
    $discount_percent = NULL;
    if ($old_price && $old_price > $current_price) {
        $discount_percent = round((($old_price - $current_price) / $old_price) * 100);
    }
    
    // Проверяем, существует ли товар с таким slug
    $check_slug_query = "SELECT id FROM products WHERE slug = '$slug'";
    $check_slug_result = mysqli_query($conn, $check_slug_query);
    
    if (mysqli_num_rows($check_slug_result) > 0) {
        $error = 'Товар с таким URL уже существует';
    } else {
        // Начинаем транзакцию
        mysqli_begin_transaction($conn);
        
        try {
            // Вставляем товар
            $insert_query = "INSERT INTO products (
                category_id, brand, name, slug, short_description, full_description,
                image_url, current_price, old_price, discount_percent, weight, skin_type,
                volume, composition, expiration, storage_conditions, usage_method,
                contraindications, animal_testing, country_of_origin, in_stock,
                is_featured, is_bestseller, delivery_time, warranty, sort_order,
                meta_title, meta_description, meta_keywords, created_at, updated_at
            ) VALUES (
                $category_id, '$brand', '$name', '$slug', '$short_description', '$full_description',
                '$image_url', $current_price, " . ($old_price ? $old_price : "NULL") . ", 
                " . ($discount_percent ? $discount_percent : "NULL") . ", '$weight', '$skin_type',
                '$volume', '$composition', '$expiration', '$storage_conditions', '$usage_method',
                '$contraindications', '$animal_testing', '$country_of_origin', $in_stock,
                $is_featured, $is_bestseller, '$delivery_time', '$warranty', $sort_order,
                '$meta_title', '$meta_description', '$meta_keywords', NOW(), NOW()
            )";
            
            if (mysqli_query($conn, $insert_query)) {
                $product_id = mysqli_insert_id($conn);
                
                // Добавляем атрибуты
                if (isset($_POST['attribute_name']) && isset($_POST['attribute_value'])) {
                    $attributes_names = $_POST['attribute_name'];
                    $attributes_values = $_POST['attribute_value'];
                    
                    for ($i = 0; $i < count($attributes_names); $i++) {
                        $attr_name = mysqli_real_escape_string($conn, $attributes_names[$i]);
                        $attr_value = mysqli_real_escape_string($conn, $attributes_values[$i]);
                        
                        if (!empty($attr_name) && !empty($attr_value)) {
                            $attr_query = "INSERT INTO product_attributes (product_id, attribute_name, attribute_value, sort_order) 
                                         VALUES ($product_id, '$attr_name', '$attr_value', $i)";
                            mysqli_query($conn, $attr_query);
                        }
                    }
                }
                
                // Добавляем дополнительные изображения
                if (isset($_POST['additional_images'])) {
                    $images = $_POST['additional_images'];
                    $image_order = 0;
                    
                    foreach ($images as $image_url) {
                        $image_url = mysqli_real_escape_string($conn, trim($image_url));
                        if (!empty($image_url)) {
                            $image_query = "INSERT INTO product_images (product_id, image_url, sort_order) 
                                          VALUES ($product_id, '$image_url', $image_order)";
                            mysqli_query($conn, $image_query);
                            $image_order++;
                        }
                    }
                }
                
                mysqli_commit($conn);
                
                $success = 'Товар успешно добавлен! ID: ' . $product_id;
                
                // Перенаправляем на редактирование для добавления дополнительных данных
                header("Location: edit.php?id=$product_id&success=" . urlencode($success));
                exit();
                
            } else {
                throw new Exception('Ошибка при добавлении товара: ' . mysqli_error($conn));
            }
            
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
    <title>Добавление товара - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css">
    <link rel="stylesheet" href="../assets/style/products/create.css">
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Добавление товара</h1>
            <div>
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Назад к списку
                </a>
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
        
        <div class="create-container">
            <form method="POST" id="productForm" enctype="multipart/form-data">
                <!-- Основная информация -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-info-circle"></i>
                        <h2>Основная информация</h2>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="category_id" class="form-label required">Категория</label>
                            <select id="category_id" name="category_id" class="form-control" required>
                                <option value="">Выберите категорию</option>
                                <?php
                                $categories = [];
                                while ($cat = mysqli_fetch_assoc($categories_result)) {
                                    $categories[$cat['id']] = $cat;
                                }
                                
                                // Функция для вывода категорий с вложенностью
                                function renderCategoryOptions($categories, $parent_id = 0, $level = 0) {
                                    $options = '';
                                    foreach ($categories as $cat) {
                                        if ($cat['parent_id'] == $parent_id) {
                                            $prefix = str_repeat('&nbsp;&nbsp;&nbsp;', $level);
                                            $selected = '';
                                            $options .= '<option value="' . $cat['id'] . '">' . 
                                                    $prefix . htmlspecialchars($cat['name']) . '</option>';
                                            
                                            // Рекурсивно выводим дочерние категории
                                            $options .= renderCategoryOptions($categories, $cat['id'], $level + 1);
                                        }
                                    }
                                    return $options;
                                }
                                
                                echo renderCategoryOptions($categories);
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="brand" class="form-label required">Бренд</label>
                            <input type="text" id="brand" name="brand" class="form-control" required
                                   placeholder="Например: FlaxScrub Professional">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="name" class="form-label required">Название товара</label>
                        <input type="text" id="name" name="name" class="form-control" required
                               placeholder="Полное название товара">
                    </div>
                    
                    <div class="form-group">
                        <label for="slug" class="form-label required">URL (slug)</label>
                        <input type="text" id="slug" name="slug" class="form-control" required
                               placeholder="nazvanie-tovara">
                        <span class="hint">Только латинские буквы, цифры и дефисы. Будет использоваться в URL</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="short_description" class="form-label required">Краткое описание</label>
                        <textarea id="short_description" name="short_description" class="form-control" required 
                                  rows="3" placeholder="Краткое описание для карточки товара"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_description" class="form-label">Полное описание</label>
                        <textarea id="full_description" name="full_description" class="form-control summernote" 
                                  rows="10"></textarea>
                    </div>
                </div>
                
                <!-- Изображения и цена -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-image"></i>
                        <h2>Изображения и цена</h2>
                    </div>
                    
                    <div class="form-group">
                        <label for="image_url" class="form-label required">Основное изображение</label>
                        <input type="text" id="image_url" name="image_url" class="form-control" required
                               placeholder="/assets/media/products/product-image.jpg"
                               onchange="updateImagePreview(this.value, 'mainImagePreview')">
                        <span class="hint">URL основного изображения товара</span>
                        <img id="mainImagePreview" class="image-preview" src="" alt="Превью">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Дополнительные изображения</label>
                        <div id="additionalImagesContainer">
                            <div class="image-row">
                                <input type="text" name="additional_images[]" class="form-control" 
                                       placeholder="/assets/media/products/product-image-2.jpg"
                                       onchange="updateImagePreview(this.value, 'addImagePreview1')">
                                <button type="button" class="btn-remove" onclick="removeImageRow(this)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <img id="addImagePreview1" class="image-preview" src="" alt="Превью">
                        </div>
                        <button type="button" class="btn-add" onclick="addImageRow()">
                            <i class="fas fa-plus"></i> Добавить изображение
                        </button>
                    </div>
                    
                    <div class="price-row">
                        <div class="form-group">
                            <label for="current_price" class="form-label required">Текущая цена (₽)</label>
                            <input type="number" step="0.01" min="0" id="current_price" name="current_price" 
                                   class="form-control" required value="0.00">
                        </div>
                        
                        <div class="form-group">
                            <label for="old_price" class="form-label">Старая цена (₽)</label>
                            <input type="number" step="0.01" min="0" id="old_price" name="old_price" 
                                   class="form-control" value="0.00" oninput="calculateDiscount()">
                        </div>
                    </div>
                    
                    <div id="discountInfo" class="discount-info" style="display: none;">
                        Скидка: <span id="discountPercent">0</span>% 
                        (Экономия: <span id="discountAmount">0</span> ₽)
                    </div>
                </div>
                
                <!-- Характеристики товара -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-list-alt"></i>
                        <h2>Характеристики товара</h2>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="weight" class="form-label">Вес/объем</label>
                            <input type="text" id="weight" name="weight" class="form-control" 
                                   placeholder="Например: 320гр, 50мл">
                        </div>
                        
                        <div class="form-group">
                            <label for="skin_type" class="form-label">Тип кожи</label>
                            <input type="text" id="skin_type" name="skin_type" class="form-control" 
                                   placeholder="Например: Все типы, Сухая, Жирная">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="volume" class="form-label">Объем/размер</label>
                            <input type="text" id="volume" name="volume" class="form-control" 
                                   placeholder="Например: 50 мл, 100 г">
                        </div>
                        
                        <div class="form-group">
                            <label for="country_of_origin" class="form-label">Страна производства</label>
                            <input type="text" id="country_of_origin" name="country_of_origin" class="form-control" 
                                   placeholder="Например: Россия, Германия">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="composition" class="form-label">Состав</label>
                        <textarea id="composition" name="composition" class="form-control" rows="4"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="expiration" class="form-label">Срок годности</label>
                            <input type="text" id="expiration" name="expiration" class="form-control" 
                                   placeholder="Например: 24 месяца">
                        </div>
                        
                        <div class="form-group">
                            <label for="storage_conditions" class="form-label">Условия хранения</label>
                            <input type="text" id="storage_conditions" name="storage_conditions" class="form-control" 
                                   placeholder="Например: При температуре от +5 до +25°C">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="usage_method" class="form-label">Способ применения</label>
                        <textarea id="usage_method" name="usage_method" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="contraindications" class="form-label">Противопоказания</label>
                        <textarea id="contraindications" name="contraindications" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="animal_testing" class="form-label">Тестирование на животных</label>
                        <input type="text" id="animal_testing" name="animal_testing" class="form-control" 
                               placeholder="Например: Не тестируется на животных">
                    </div>
                </div>
                
                <!-- Дополнительные атрибуты -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-tags"></i>
                        <h2>Дополнительные атрибуты</h2>
                    </div>
                    
                    <div id="attributesContainer">
                        <div class="attribute-row">
                            <input type="text" name="attribute_name[]" class="form-control" placeholder="Название атрибута">
                            <input type="text" name="attribute_value[]" class="form-control" placeholder="Значение">
                            <button type="button" class="btn-remove" onclick="removeAttributeRow(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="button" class="btn-add" onclick="addAttributeRow()">
                        <i class="fas fa-plus"></i> Добавить атрибут
                    </button>
                </div>
                
                <!-- Настройки товара -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-cog"></i>
                        <h2>Настройки товара</h2>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="delivery_time" class="form-label">Срок доставки</label>
                            <input type="text" id="delivery_time" name="delivery_time" class="form-control" 
                                   value="1-3 дня" placeholder="Например: 1-3 дня">
                        </div>
                        
                        <div class="form-group">
                            <label for="warranty" class="form-label">Гарантия</label>
                            <input type="text" id="warranty" name="warranty" class="form-control" 
                                   placeholder="Например: 12 месяцев">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="sort_order" class="form-label">Порядок сортировки</label>
                            <input type="number" id="sort_order" name="sort_order" class="form-control" 
                                   value="0" min="0">
                            <span class="hint">Чем меньше число, тем выше в списке</span>
                        </div>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="in_stock" name="in_stock" value="1" checked>
                        <label for="in_stock">В наличии</label>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_featured" name="is_featured" value="1">
                        <label for="is_featured">Рекомендуемый товар</label>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_bestseller" name="is_bestseller" value="1">
                        <label for="is_bestseller">Хит продаж</label>
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
                               placeholder="Заголовок для SEO">
                        <span class="hint">Рекомендуемая длина: 50-60 символов</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea id="meta_description" name="meta_description" class="form-control" 
                                  rows="3" placeholder="Описание для SEO"></textarea>
                        <span class="hint">Рекомендуемая длина: 150-160 символов</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="meta_keywords" class="form-label">Meta Keywords</label>
                        <input type="text" id="meta_keywords" name="meta_keywords" class="form-control" 
                               placeholder="Ключевые слова через запятую">
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Сохранить товар
                    </button>
                    <button type="reset" class="btn btn-outline">
                        <i class="fas fa-redo"></i> Сбросить
                    </button>
                    <a href="index.php" class="btn btn-outline">
                        <i class="fas fa-times"></i> Отмена
                    </a>
                </div>
            </form>
        </div>
    </main>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/lang/summernote-ru-RU.min.js"></script>
    
    <script src="../assets/js/products/create.js"></script>
</body>
</html>