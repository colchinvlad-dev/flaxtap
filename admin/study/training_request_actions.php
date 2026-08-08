<?php
ob_start(); // Включаем буферизацию вывода
session_start();
include "../config/database.php";
checkAdminAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Метод не поддерживается']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$request_id = isset($data['request_id']) ? (int)$data['request_id'] : 0;

if (!$request_id) {
    echo json_encode(['success' => false, 'message' => 'ID заявки не указан']);
    exit();
}

switch ($action) {
    case 'toggle_read':
        $is_read = isset($data['is_read']) ? (int)$data['is_read'] : 0;
        $query = "UPDATE training_requests SET is_read = $is_read WHERE id = $request_id";
        
        if (mysqli_query($conn, $query)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        break;
        
    case 'change_status':
        $status = mysqli_real_escape_string($conn, $data['status'] ?? '');
        if (!in_array($status, ['new', 'contacted', 'enrolled', 'rejected'])) {
            echo json_encode(['success' => false, 'message' => 'Неверный статус']);
            exit();
        }
        
        $query = "UPDATE training_requests SET status = '$status' WHERE id = $request_id";
        
        if (mysqli_query($conn, $query)) {
            // Логируем изменение статуса
            $admin_name = $_SESSION['user_name'] ?? $_SESSION['admin_name'] ?? 'Неизвестный';
            $log_message = date('Y-m-d H:i:s') . " - Администратор {$admin_name} изменил статус заявки #{$request_id} на '{$status}'\n";
            file_put_contents('../training_log.txt', $log_message, FILE_APPEND);
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        break;
        
    case 'update_notes':
        $notes = mysqli_real_escape_string($conn, $data['notes'] ?? '');
        $query = "UPDATE training_requests SET notes = '$notes' WHERE id = $request_id";
        
        if (mysqli_query($conn, $query)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        break;
        
    case 'delete':
        // Проверяем существование заявки
        $check_query = "SELECT * FROM training_requests WHERE id = $request_id";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) == 0) {
            echo json_encode(['success' => false, 'message' => 'Заявка не найдена']);
            exit();
        }
        
        $request = mysqli_fetch_assoc($check_result);
        
        // Логируем удаление
        $admin_name = $_SESSION['user_name'] ?? $_SESSION['admin_name'] ?? 'Неизвестный';
        $log_message = date('Y-m-d H:i:s') . " - Администратор {$admin_name} удалил заявку на обучение: {$request['name']} (ID: {$request['id']})\n";
        file_put_contents('../training_log.txt', $log_message, FILE_APPEND);
        
        // Удаляем заявку
        $query = "DELETE FROM training_requests WHERE id = $request_id";
        
        if (mysqli_query($conn, $query)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Неизвестное действие']);
        break;
}