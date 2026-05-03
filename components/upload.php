<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    echo json_encode(array('success' => false, 'error' => 'Не авторизован'));
    exit;
}

// Папка для загрузки
$uploadDir = __DIR__ . '/../img/';

// Проверяем существование папки, если нет - создаем
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['product_image'])) {
    $file = $_FILES['product_image'];
    
    // Генерируем уникальное имя файла
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $fileName = time() . '_' . rand(1000, 9999) . '.' . $ext;
    $uploadFile = $uploadDir . $fileName;
    
    // Разрешенные типы
    $allowedTypes = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    
    if (!in_array($ext, $allowedTypes)) {
        echo json_encode(array('success' => false, 'error' => 'Недопустимый тип файла. Разрешены: jpg, png, gif, webp'));
        exit;
    }
    
    // Проверка размера (максимум 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(array('success' => false, 'error' => 'Файл слишком большой. Максимум 5MB'));
        exit;
    }
    
    // Загружаем файл
    if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
        echo json_encode(array('success' => true, 'filename' => $fileName));
    } else {
        echo json_encode(array('success' => false, 'error' => 'Ошибка при сохранении файла'));
    }
    exit;
}

echo json_encode(array('success' => false, 'error' => 'Файл не получен'));
?>