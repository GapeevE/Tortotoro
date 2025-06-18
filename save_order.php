<?php
session_start();
require 'db_connect.php';
if ($_SESSION['user_role'] === 'waiter' || $_SESSION['user_role'] === 'chief') {
    $data = json_decode(file_get_contents('php://input'), true);
    $errors = [];
    if (empty($data['shift_id'])) $errors[] = 'Не указана смена';
    if (empty($data['user_id'])) $errors[] = 'Не указан официант';
    if (empty($data['items'])) $errors[] = 'Нет позиций в заказе';
    if (!empty($errors)) {
        http_response_code(400);
        die(json_encode(['errors' => $errors]));
    }
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("
            INSERT INTO `order` 
            (created_at, shift_id, user_id, status) 
            VALUES (NOW(), ?, ?, 'pending')
        ");
        $stmt->execute([
            $data['shift_id'],
            $data['user_id']
        ]);
        $orderId = $pdo->lastInsertId();
        $stmt = $pdo->prepare("
            INSERT INTO item 
            (order_id, dish_id, count) 
            VALUES (?, ?, ?)
        ");
        foreach ($data['items'] as $item) {
            $stmt->execute([
                $orderId,
                $item['dishId'],
                $item['quantity']
            ]);
        }
        $pdo->commit();
        echo json_encode(['success' => true, 'order_id' => $orderId]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Ошибка базы данных: ' . $e->getMessage()]);
    }
} else {
    http_response_code(403);
    die(json_encode(['error' => 'Доступ запрещен']));
}
