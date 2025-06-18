<?php
session_start();
require 'db_connect.php'; 
if ($_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit('Доступ запрещен');
}
$required = ['name', 'login', 'password', 'role_id'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        exit("Поле $field обязательно для заполнения");
    }
}
$name = trim($_POST['name']);
$login = trim($_POST['login']);
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$role_id = (int)$_POST['role_id'];
$photo_path = null;
if (!empty($_FILES['photo']['name'])) {
    $upload_dir = __DIR__ . '/images/' . $login . '/';
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            http_response_code(500);
            exit('Ошибка при создании директории');
        }
    }
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_info = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($file_info, $_FILES['photo']['tmp_name']);
    if (!in_array($mime_type, $allowed_types)) {
        http_response_code(400);
        exit('Недопустимый тип файла');
    }
    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $ext;
    $target_file = $upload_dir . $filename;

    if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
        $photo_path = 'images/' . $login . '/' . $filename;
    } else {
        http_response_code(500);
        exit('Ошибка загрузки файла');
    }
}
try {
    $stmt = $pdo->prepare("
        INSERT INTO user 
        (name, login, password, photo_file, role_id) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $name,
        $login,
        $password,
        $photo_path,
        $role_id
    ]);
    echo json_encode(['success' => true, 'message' => 'Пользователь создан']);
} catch (PDOException $e) {
    if ($e->errorInfo[1] === 1062) {
        http_response_code(400);
        exit('Пользователь с таким логином уже существует');
    }
    http_response_code(500);
    exit('Ошибка при сохранении в БД: ' . $e->getMessage());
}