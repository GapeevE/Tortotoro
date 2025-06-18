<?php
ob_clean();
session_start();
require 'db_connect.php';
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Доступ запрещен']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Метод не поддерживается']);
    exit;
}
$userId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$userId || $userId < 1) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Неверный ID пользователя']);
    exit;
}
if (isset($_SESSION['user_id']) && $userId == $_SESSION['user_id']) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Вы не можете удалить собственный аккаунт']);
    exit;
}

function safeDeleteUserDirectory($userDir) {
    if (!is_dir($userDir)) {
        return false;
    }
    $realPath = realpath($userDir);
    $basePath = realpath('images');
    if (strpos($realPath, $basePath) !== 0) {
        throw new Exception("Попытка удалить директорию вне images");
    }
    $files = array_diff(scandir($realPath), ['.', '..']);
    foreach ($files as $file) {
        $path = $realPath . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            safeDeleteUserDirectory($path);
        } else {
            unlink($path);
        }
    }
    return rmdir($realPath);
}

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("SELECT id_user, photo_file, login FROM user WHERE id_user = ? FOR UPDATE");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch();
    if (!$userData) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['error' => 'Пользователь не найден']);
        exit;
    }
    $photoFile = $userData['photo_file'];
    $userLogin = $userData['login'];
    $stmt = $pdo->prepare("
        SELECT s.id_shift 
        FROM assignment a
        JOIN shift s ON a.shift_id = s.id_shift
        WHERE a.user_id = ? AND s.status = 'active'
    ");
    $stmt->execute([$userId]);
    if ($stmt->rowCount() > 0) {
        $pdo->rollBack();
        http_response_code(403);
        echo json_encode(['error' => 'Нельзя удалить сотрудника, который находится на активной смене']);
        exit;
    }
    $stmt = $pdo->prepare("SELECT shift_id FROM assignment WHERE user_id = ?");
    $stmt->execute([$userId]);
    $shiftIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($shiftIds)) {
        $placeholders = rtrim(str_repeat('?,', count($shiftIds)), ',');
        $stmt = $pdo->prepare("
            SELECT id_order 
            FROM `order` 
            WHERE user_id = ? AND shift_id IN ($placeholders)
        ");
        $params = array_merge([$userId], $shiftIds);
        $stmt->execute($params);
        $orderIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($orderIds)) {
            $orderPlaceholders = rtrim(str_repeat('?,', count($orderIds)), ',');
            $stmt = $pdo->prepare("DELETE FROM item WHERE order_id IN ($orderPlaceholders)");
            $stmt->execute($orderIds);
        }
        $stmt = $pdo->prepare("
            DELETE FROM `order` 
            WHERE user_id = ? AND shift_id IN ($placeholders)
        ");
        $stmt->execute($params);
        $stmt = $pdo->prepare("DELETE FROM assignment WHERE user_id = ?");
        $stmt->execute([$userId]);
    }
    $stmt = $pdo->prepare("DELETE FROM user WHERE id_user = ?");
    $stmt->execute([$userId]);
    if ($stmt->rowCount() === 0) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['error' => 'Пользователь не найден']);
        exit;
    }
    $pdo->commit();
    if ($photoFile) {
        $userDir = 'images/' . $userLogin;
        if (file_exists($photoFile)) {
            unlink($photoFile);
        }
        if (is_dir($userDir)) {
            safeDeleteUserDirectory($userDir);
        }
    }
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Ошибка базы данных',
        'details' => $e->getMessage()
    ]);
    error_log('Delete User Error: ' . $e->getMessage());
}
exit;