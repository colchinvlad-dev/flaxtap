<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

$request_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$request_id) {
    header("Location: index.php");
    exit();
}

// Получаем данные заявки
$query = "SELECT tr.*, 
          u1.name as assigned_to_name,
          u2.name as updated_by_name
          FROM training_requests tr
          LEFT JOIN users u1 ON tr.assigned_to = u1.id
          LEFT JOIN users u2 ON tr.updated_by = u2.id
          WHERE tr.id = $request_id";
$result = mysqli_query($conn, $query);
$request = mysqli_fetch_assoc($result);

if (!$request) {
    header("Location: index.php");
    exit();
}

// Обновляем статус как прочитанный при просмотре
if (!$request['is_read']) {
    mysqli_query($conn, "UPDATE training_requests SET is_read = 1 WHERE id = $request_id");
    $request['is_read'] = 1;
}

// Получаем список администраторов для назначения заявки
$admins_query = "SELECT id, name, email FROM users WHERE role = 'admin' AND is_active = 1";
$admins_result = mysqli_query($conn, $admins_query);

// Обработка формы обновления
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $priority = mysqli_real_escape_string($conn, $_POST['priority']);
    $assigned_to = isset($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    $admin_id = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;
    
    $update_query = "UPDATE training_requests SET 
                    status = '$status',
                    priority = '$priority',
                    assigned_to = " . ($assigned_to ?: 'NULL') . ",
                    notes = '$notes',
                    updated_by = " . ($admin_id ?: 'NULL') . ",
                    updated_at = NOW()
                    WHERE id = $request_id";
    
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success'] = "Заявка успешно обновлена";
        header("Location: training_request_view.php?id=$request_id");
        exit();
    } else {
        $error = "Ошибка при обновлении заявки: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр заявки на обучение - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style/study/view.css">
</head>
<body>
    <?php include "../inc/sidebar.php"; ?>
    
    <main class="main-content">
        <?php include "../inc/header.php"; ?>
        
        <div class="header">
            <h1>Просмотр заявки на обучение</h1>
            <div>
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Назад к списку
                </a>
                <button onclick="window.print()" class="btn btn-outline">
                    <i class="fas fa-print"></i> Печать
                </button>
            </div>
        </div>
        
        <div class="view-container">
            <!-- Заголовок заявки -->
            <div class="request-header">
                <div class="header-top">
                    <div>
                        <h1 class="request-title">
                            <?php echo htmlspecialchars($request['name']); ?>
                            <span class="priority-badge-large 
                                <?php 
                                if ($request['priority'] == 'urgent') echo 'badge-urgent';
                                elseif ($request['priority'] == 'high') echo 'badge-high';
                                elseif ($request['priority'] == 'low') echo 'badge-low';
                                else echo 'badge-normal';
                                ?>">
                                <?php 
                                if ($request['priority'] == 'urgent') echo 'Срочно';
                                elseif ($request['priority'] == 'high') echo 'Высокий';
                                elseif ($request['priority'] == 'low') echo 'Низкий';
                                else echo 'Обычный';
                                ?>
                            </span>
                        </h1>
                        <div class="request-meta">
                            <div class="meta-item">
                                <i class="fas fa-calendar"></i>
                                <span>Создана: <?php echo date('d.m.Y H:i', strtotime($request['created_at'])); ?></span>
                            </div>
                            <?php if ($request['updated_at']): ?>
                                <div class="meta-item">
                                    <i class="fas fa-sync-alt"></i>
                                    <span>Обновлена: <?php echo date('d.m.Y H:i', strtotime($request['updated_at'])); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($request['assigned_to_name']): ?>
                                <div class="meta-item">
                                    <i class="fas fa-user"></i>
                                    <span>Назначено: <?php echo htmlspecialchars($request['assigned_to_name']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <div class="status-badge-large 
                            <?php 
                            if ($request['status'] == 'new') echo 'badge-new';
                            elseif ($request['status'] == 'contacted') echo 'badge-contacted';
                            elseif ($request['status'] == 'enrolled') echo 'badge-enrolled';
                            else echo 'badge-rejected';
                            ?>">
                            <?php 
                            if ($request['status'] == 'new') echo 'Новая';
                            elseif ($request['status'] == 'contacted') echo 'Связались';
                            elseif ($request['status'] == 'enrolled') echo 'Зачислен';
                            else echo 'Отклонен';
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="header-actions">
                    <a href="tel:<?php echo htmlspecialchars($request['phone']); ?>" class="btn btn-outline">
                        <i class="fas fa-phone"></i> Позвонить
                    </a>
                    <a href="mailto:<?php echo htmlspecialchars($request['email']); ?>" class="btn btn-outline">
                        <i class="fas fa-envelope"></i> Написать email
                    </a>
                    <?php if ($request['social_link']): ?>
                        <a href="<?php echo htmlspecialchars($request['social_link']); ?>" target="_blank" class="btn btn-outline">
                            <i class="fas fa-external-link-alt"></i> Соцсеть
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Основная информация -->
            <div class="details-card">
                <h3 class="section-title">Контактная информация</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Имя</div>
                        <div class="info-value"><?php echo htmlspecialchars($request['name']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Телефон</div>
                        <div class="info-value">
                            <a href="tel:<?php echo htmlspecialchars($request['phone']); ?>">
                                <?php echo htmlspecialchars($request['phone']); ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value">
                            <a href="mailto:<?php echo htmlspecialchars($request['email']); ?>">
                                <?php echo htmlspecialchars($request['email']); ?>
                            </a>
                        </div>
                    </div>
                    
                    <?php if ($request['social_link']): ?>
                        <div class="info-item">
                            <div class="info-label">Социальная сеть</div>
                            <div class="info-value">
                                <a href="<?php echo htmlspecialchars($request['social_link']); ?>" target="_blank">
                                    <?php echo htmlspecialchars($request['social_link']); ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($request['course_type']): ?>
                        <div class="info-item">
                            <div class="info-label">Интересующий курс</div>
                            <div class="info-value"><?php echo htmlspecialchars($request['course_type']); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Сообщение -->
            <div class="details-card">
                <h3 class="section-title">Сообщение от клиента</h3>
                <div class="content-box">
                    <?php echo nl2br(htmlspecialchars($request['message'])); ?>
                </div>
            </div>
            
            <!-- Форма редактирования -->
            <div class="details-card">
                <h3 class="section-title">Управление заявкой</h3>
                
                <form method="POST">
                    <div class="info-grid">
                        <div class="form-group">
                            <label for="status">Статус заявки</label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="new" <?php echo $request['status'] == 'new' ? 'selected' : ''; ?>>Новая</option>
                                <option value="contacted" <?php echo $request['status'] == 'contacted' ? 'selected' : ''; ?>>Связались</option>
                                <option value="enrolled" <?php echo $request['status'] == 'enrolled' ? 'selected' : ''; ?>>Зачислен</option>
                                <option value="rejected" <?php echo $request['status'] == 'rejected' ? 'selected' : ''; ?>>Отклонен</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="priority">Приоритет</label>
                            <select id="priority" name="priority" class="form-control" required>
                                <option value="low" <?php echo $request['priority'] == 'low' ? 'selected' : ''; ?>>Низкий</option>
                                <option value="normal" <?php echo $request['priority'] == 'normal' ? 'selected' : ''; ?>>Обычный</option>
                                <option value="high" <?php echo $request['priority'] == 'high' ? 'selected' : ''; ?>>Высокий</option>
                                <option value="urgent" <?php echo $request['priority'] == 'urgent' ? 'selected' : ''; ?>>Срочный</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="assigned_to">Назначить администратору</label>
                            <select id="assigned_to" name="assigned_to" class="form-control">
                                <option value="">Не назначено</option>
                                <?php mysqli_data_seek($admins_result, 0); ?>
                                <?php while($admin = mysqli_fetch_assoc($admins_result)): ?>
                                    <option value="<?php echo $admin['id']; ?>" 
                                        <?php echo $request['assigned_to'] == $admin['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($admin['name']); ?> (<?php echo htmlspecialchars($admin['email']); ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Заметки администратора</label>
                        <textarea id="notes" name="notes" class="form-control" 
                                  placeholder="Заметки по работе с клиентом..."><?php echo htmlspecialchars($request['notes'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Сохранить изменения
                        </button>
                        <button type="button" onclick="deleteRequest(<?php echo $request_id; ?>)" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Удалить заявку
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Техническая информация -->
            <div class="details-card">
                <h3 class="section-title">Техническая информация</h3>
                <div class="tech-info">
                    <span><i class="fas fa-id-card"></i> ID заявки: <?php echo $request_id; ?></span>
                    <span><i class="fas fa-globe"></i> IP-адрес: <?php echo htmlspecialchars($request['ip_address']); ?></span>
                    <span><i class="fas fa-desktop"></i> User Agent: <?php echo htmlspecialchars(substr($request['user_agent'], 0, 100)); ?>...</span>
                    <?php if ($request['updated_by_name']): ?>
                        <span><i class="fas fa-user-edit"></i> Изменено: <?php echo htmlspecialchars($request['updated_by_name']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        function deleteRequest(requestId) {
            if (confirm('Вы уверены, что хотите удалить эту заявку?')) {
                fetch('training_request_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'delete',
                        request_id: requestId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'index.php';
                    } else {
                        alert('Ошибка: ' + data.message);
                    }
                });
            }
        }
        
        // Автоматическое сохранение заметок при изменении
        let notesTimeout;
        document.getElementById('notes').addEventListener('input', function() {
            clearTimeout(notesTimeout);
            notesTimeout = setTimeout(function() {
                const formData = new FormData();
                formData.append('notes', document.getElementById('notes').value);
                formData.append('request_id', <?php echo $request_id; ?>);
                
                fetch('training_request_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'update_notes',
                        request_id: <?php echo $request_id; ?>,
                        notes: document.getElementById('notes').value
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Заметки сохранены');
                    }
                });
            }, 1000);
        });
    </script>
</body>
</html>