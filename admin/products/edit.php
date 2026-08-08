<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$product_id) {
    header("Location: index.php");
    exit();
}

// Получаем данные товара
$query = "SELECT p.* FROM products p WHERE p.id = $product_id";
$result = mysqli_query($conn, $query);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    header("Location: index.php");
    exit();
}

// Получаем категории
$categories_query = "SELECT id, name, parent_id FROM categories WHERE is_active = 1 ORDER BY parent_id, sort_order";
$categories_result = mysqli_query($conn, $categories_query);

// Получаем атрибуты товара
$attributes_query = "SELECT * FROM product_attributes WHERE product_id = $product_id ORDER BY sort_order";
$attributes_result = mysqli_query($conn, $attributes_query);

// Получаем дополнительные изображения
$images_query = "SELECT * FROM product_images WHERE product_id = $product_id ORDER BY sort_order";
$images_result = mysqli_query($conn, $images_query);

// Обработка формы
$error = '';
$success = isset($_GET['success']) ? $_GET['success'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы (аналогично create.php)
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
    
    // Проверяем, существует ли товар с таким slug (кроме текущего)
    $check_slug_query = "SELECT id FROM products WHERE slug = '$slug' AND id != $product_id";
    $check_slug_result = mysqli_query($conn, $check_slug_query);
    
    if (mysqli_num_rows($check_slug_result) > 0) {
        $error = 'Товар с таким URL уже существует';
    } else {
        // Начинаем транзакцию
        mysqli_begin_transaction($conn);
        
        try {
            // Обновляем товар
            $update_query = "UPDATE products SET 
                category_id = $category_id,
                brand = '$brand',
                name = '$name',
                slug = '$slug',
                short_description = '$short_description',
                full_description = '$full_description',
                image_url = '$image_url',
                current_price = $current_price,
                old_price = " . ($old_price ? $old_price : "NULL") . ",
                discount_percent = " . ($discount_percent ? $discount_percent : "NULL") . ",
                weight = '$weight',
                skin_type = '$skin_type',
                volume = '$volume',
                composition = '$composition',
                expiration = '$expiration',
                storage_conditions = '$storage_conditions',
                usage_method = '$usage_method',
                contraindications = '$contraindications',
                animal_testing = '$animal_testing',
                country_of_origin = '$country_of_origin',
                in_stock = $in_stock,
                is_featured = $is_featured,
                is_bestseller = $is_bestseller,
                delivery_time = '$delivery_time',
                warranty = '$warranty',
                sort_order = $sort_order,
                meta_title = '$meta_title',
                meta_description = '$meta_description',
                meta_keywords = '$meta_keywords',
                updated_at = NOW()
                WHERE id = $product_id";
            
            if (mysqli_query($conn, $update_query)) {
                // Удаляем старые атрибуты
                mysqli_query($conn, "DELETE FROM product_attributes WHERE product_id = $product_id");
                
                // Добавляем новые атрибуты
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
                
                // Удаляем старые изображения
                mysqli_query($conn, "DELETE FROM product_images WHERE product_id = $product_id");
                
                // Добавляем новые изображения
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
                
                $success = 'Товар успешно обновлен!';
                
                // Обновляем данные товара
                $query = "SELECT p.* FROM products p WHERE p.id = $product_id";
                $result = mysqli_query($conn, $query);
                $product = mysqli_fetch_assoc($result);
                
            } else {
                throw new Exception('Ошибка при обновлении товара: ' . mysqli_error($conn));
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
    <title>Редактирование товара - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css">
    <link rel="stylesheet" href="../assets/style/products/edit.css">
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Редактирование товара</h1>
            <div>
                <a href="view.php?id=<?php echo $product_id; ?>" class="btn btn-outline">
                    <i class="fas fa-eye"></i> Просмотр
                </a>
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Назад
                </a>
            </div>
        </div>
        
        <div class="product-status">
            <div class="status-info">
                <span>ID: <?php echo $product['id']; ?></span>
                <span>Создан: <?php echo date('d.m.Y H:i', strtotime($product['created_at'])); ?></span>
                <span>Обновлен: <?php echo date('d.m.Y H:i', strtotime($product['updated_at'])); ?></span>
                <span>Категория: <?php echo $product['category_id']; ?></span>
                <span>Статус: <?php echo $product['in_stock'] ? 'В наличии' : 'Нет в наличии'; ?></span>
                <?php if ($product['is_featured']): ?>
                    <span style="color: #9b59b6;"><i class="fas fa-star"></i> Рекомендуемый</span>
                <?php endif; ?>
                <?php if ($product['is_bestseller']): ?>
                    <span style="color: #e67e22;"><i class="fas fa-fire"></i> Хит продаж</span>
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
                                mysqli_data_seek($categories_result, 0);
                                while ($cat = mysqli_fetch_assoc($categories_result)) {
                                    $categories[$cat['id']] = $cat;
                                }
                                
                                // Функция для вывода категорий с вложенностью
                                function renderCategoryOptions($categories, $parent_id = 0, $level = 0, $selected_id = 0) {
                                    $options = '';
                                    foreach ($categories as $cat) {
                                        if ($cat['parent_id'] == $parent_id) {
                                            $prefix = str_repeat('&nbsp;&nbsp;&nbsp;', $level);
                                            $selected = ($cat['id'] == $selected_id) ? 'selected' : '';
                                            $options .= '<option value="' . $cat['id'] . '" ' . $selected . '>' . 
                                                    $prefix . htmlspecialchars($cat['name']) . '</option>';
                                            
                                            // Рекурсивно выводим дочерние категории
                                            $options .= renderCategoryOptions($categories, $cat['id'], $level + 1, $selected_id);
                                        }
                                    }
                                    return $options;
                                }
                                
                                echo renderCategoryOptions($categories, 0, 0, $product['category_id']);
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="brand" class="form-label required">Бренд</label>
                            <input type="text" id="brand" name="brand" class="form-control" required
                                   value="<?php echo htmlspecialchars($product['brand']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="name" class="form-label required">Название товара</label>
                        <input type="text" id="name" name="name" class="form-control" required
                               value="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="slug" class="form-label required">URL (slug)</label>
                        <input type="text" id="slug" name="slug" class="form-control" required
                               value="<?php echo htmlspecialchars($product['slug']); ?>">
                        <span class="hint">Только латинские буквы, цифры и дефисы</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="short_description" class="form-label required">Краткое описание</label>
                        <textarea id="short_description" name="short_description" class="form-control" required 
                                  rows="3"><?php echo htmlspecialchars($product['short_description']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_description" class="form-label">Полное описание</label>
                        <textarea id="full_description" name="full_description" class="form-control summernote" 
                                  rows="10"><?php echo htmlspecialchars($product['full_description']); ?></textarea>
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
                               value="<?php echo htmlspecialchars($product['image_url']); ?>">
                        <span class="hint">URL основного изображения товара</span>
                        <?php if ($product['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                 class="image-preview" alt="Превью">
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Дополнительные изображения</label>
                        <div id="additionalImagesContainer">
                            <?php
                            $image_index = 1;
                            if (mysqli_num_rows($images_result) > 0):
                                mysqli_data_seek($images_result, 0);
                                while ($image = mysqli_fetch_assoc($images_result)):
                            ?>
                                <div class="image-row">
                                    <input type="text" name="additional_images[]" class="form-control" 
                                           value="<?php echo htmlspecialchars($image['image_url']); ?>">
                                    <button type="button" class="btn-remove" onclick="removeImageRow(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <img src="<?php echo htmlspecialchars($image['image_url']); ?>" 
                                     class="image-preview" alt="Превью">
                            <?php 
                                    $image_index++;
                                endwhile;
                            else:
                            ?>
                                <div class="image-row">
                                    <input type="text" name="additional_images[]" class="form-control" 
                                           placeholder="/assets/media/products/product-image-2.jpg">
                                    <button type="button" class="btn-remove" onclick="removeImageRow(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn-add" onclick="addImageRow()">
                            <i class="fas fa-plus"></i> Добавить изображение
                        </button>
                    </div>
                    
                    <div class="price-row">
                        <div class="form-group">
                            <label for="current_price" class="form-label required">Текущая цена (₽)</label>
                            <input type="number" step="0.01" min="0" id="current_price" name="current_price" 
                                   class="form-control" required 
                                   value="<?php echo number_format($product['current_price'], 2); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="old_price" class="form-label">Старая цена (₽)</label>
                            <input type="number" step="0.01" min="0" id="old_price" name="old_price" 
                                   class="form-control" 
                                   value="<?php echo $product['old_price'] ? number_format($product['old_price'], 2) : ''; ?>" 
                                   oninput="calculateDiscount()">
                        </div>
                    </div>
                    
                    <div id="discountInfo" class="discount-info" 
                         style="<?php echo $product['discount_percent'] ? 'display: block;' : 'display: none;'; ?>">
                        Скидка: <span id="discountPercent"><?php echo $product['discount_percent'] ?? 0; ?></span>% 
                        (Экономия: <span id="discountAmount"><?php 
                            if ($product['old_price'] && $product['current_price']) {
                                echo number_format($product['old_price'] - $product['current_price'], 2);
                            } else {
                                echo '0';
                            }
                        ?></span> ₽)
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
                                   value="<?php echo htmlspecialchars($product['weight']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="skin_type" class="form-label">Тип кожи</label>
                            <input type="text" id="skin_type" name="skin_type" class="form-control" 
                                   value="<?php echo htmlspecialchars($product['skin_type']); ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="volume" class="form-label">Объем/размер</label>
                            <input type="text" id="volume" name="volume" class="form-control" 
                                   value="<?php echo htmlspecialchars($product['volume']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="country_of_origin" class="form-label">Страна производства</label>
                            <input type="text" id="country_of_origin" name="country_of_origin" class="form-control" 
                                   value="<?php echo htmlspecialchars($product['country_of_origin']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="composition" class="form-label">Состав</label>
                        <textarea id="composition" name="composition" class="form-control" rows="4"><?php echo htmlspecialchars($product['composition']); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="expiration" class="form-label">Срок годности</label>
                            <input type="text" id="expiration" name="expiration" class="form-control" 
                                   value="<?php echo htmlspecialchars($product['expiration']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="storage_conditions" class="form-label">Условия хранения</label>
                            <input type="text" id="storage_conditions" name="storage_conditions" class="form-control" 
                                   value="<?php echo htmlspecialchars($product['storage_conditions']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="usage_method" class="form-label">Способ применения</label>
                        <textarea id="usage_method" name="usage_method" class="form-control" rows="3"><?php echo htmlspecialchars($product['usage_method']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="contraindications" class="form-label">Противопоказания</label>
                        <textarea id="contraindications" name="contraindications" class="form-control" rows="3"><?php echo htmlspecialchars($product['contraindications']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="animal_testing" class="form-label">Тестирование на животных</label>
                        <input type="text" id="animal_testing" name="animal_testing" class="form-control" 
                               value="<?php echo htmlspecialchars($product['animal_testing']); ?>">
                    </div>
                </div>
                
                <!-- Дополнительные атрибуты -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-tags"></i>
                        <h2>Дополнительные атрибуты</h2>
                    </div>
                    
                    <div id="attributesContainer">
                        <?php
                        $attribute_index = 0;
                        if (mysqli_num_rows($attributes_result) > 0):
                            mysqli_data_seek($attributes_result, 0);
                            while ($attr = mysqli_fetch_assoc($attributes_result)):
                        ?>
                            <div class="attribute-row">
                                <input type="text" name="attribute_name[]" class="form-control" 
                                       value="<?php echo htmlspecialchars($attr['attribute_name']); ?>">
                                <input type="text" name="attribute_value[]" class="form-control" 
                                       value="<?php echo htmlspecialchars($attr['attribute_value']); ?>">
                                <button type="button" class="btn-remove" onclick="removeAttributeRow(this)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        <?php
                                $attribute_index++;
                            endwhile;
                        else:
                        ?>
                            <div class="attribute-row">
                                <input type="text" name="attribute_name[]" class="form-control" placeholder="Название атрибута">
                                <input type="text" name="attribute_value[]" class="form-control" placeholder="Значение">
                                <button type="button" class="btn-remove" onclick="removeAttributeRow(this)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        <?php endif; ?>
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
                                   value="<?php echo htmlspecialchars($product['delivery_time']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="warranty" class="form-label">Гарантия</label>
                            <input type="text" id="warranty" name="warranty" class="form-control" 
                                   value="<?php echo htmlspecialchars($product['warranty']); ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="sort_order" class="form-label">Порядок сортировки</label>
                            <input type="number" id="sort_order" name="sort_order" class="form-control" 
                                   value="<?php echo $product['sort_order']; ?>" min="0">
                        </div>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="in_stock" name="in_stock" value="1" 
                               <?php echo $product['in_stock'] ? 'checked' : ''; ?>>
                        <label for="in_stock">В наличии</label>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_featured" name="is_featured" value="1" 
                               <?php echo $product['is_featured'] ? 'checked' : ''; ?>>
                        <label for="is_featured">Рекомендуемый товар</label>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_bestseller" name="is_bestseller" value="1" 
                               <?php echo $product['is_bestseller'] ? 'checked' : ''; ?>>
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
                               value="<?php echo htmlspecialchars($product['meta_title']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea id="meta_description" name="meta_description" class="form-control" 
                                  rows="3"><?php echo htmlspecialchars($product['meta_description']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="meta_keywords" class="form-label">Meta Keywords</label>
                        <input type="text" id="meta_keywords" name="meta_keywords" class="form-control" 
                               value="<?php echo htmlspecialchars($product['meta_keywords']); ?>">
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Сохранить изменения
                    </button>
                    <button type="reset" class="btn btn-outline">
                        <i class="fas fa-redo"></i> Сбросить
                    </button>
                    <a href="view.php?id=<?php echo $product_id; ?>" class="btn btn-outline">
                        <i class="fas fa-times"></i> Отмена
                    </a>
                </div>
            </form>
        </div>
    </main>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/lang/summernote-ru-RU.min.js"></script>
    
    <script>
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
        
        // Добавление строки атрибута
        let attributeRowCount = <?php echo $attribute_index ? $attribute_index : 1; ?>;
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
        let imageRowCount = <?php echo $image_index ? $image_index : 1; ?>;
        function addImageRow() {
            const container = document.getElementById('additionalImagesContainer');
            const row = document.createElement('div');
            row.className = 'image-row';
            row.innerHTML = `
                <input type="text" name="additional_images[]" class="form-control" 
                       placeholder="/assets/media/products/product-image-${imageRowCount + 1}.jpg">
                <button type="button" class="btn-remove" onclick="removeImageRow(this)">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(row);
            imageRowCount++;
        }
        
        function removeImageRow(button) {
            if (imageRowCount > 1) {
                button.parentElement.remove();
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
    </script>
</body>
</html>