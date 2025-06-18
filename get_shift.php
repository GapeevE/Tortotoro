<?php
session_start();
require 'db_connect.php';
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['error' => 'Неавторизованный доступ']));
}
if ($_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    die(json_encode(['error' => 'Доступ запрещен']));
}
$shiftId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$shiftId || $shiftId < 1) {
    http_response_code(400);
    die(json_encode(['error' => 'Неверный ID смены']));
}
try {
    $stmt = $pdo->prepare("
        SELECT 
            id_shift AS id,
            DATE_FORMAT(time_start, '%Y-%m-%dT%H:%i') AS start,
            DATE_FORMAT(time_end, '%Y-%m-%dT%H:%i') AS end,
            status
        FROM shift 
        WHERE id_shift = ?
    ");
    $stmt->execute([$shiftId]);
    $shift = $stmt->fetch();
    if (!$shift) {
        http_response_code(404);
        die(json_encode(['error' => 'Смена не найдена']));
    }
    $stmt = $pdo->prepare("
        SELECT user_id 
        FROM assignment 
        WHERE shift_id = ?
    ");
    $stmt->execute([$shiftId]);
    $employees = $stmt->fetchAll(PDO::FETCH_COLUMN, 0); 
    $stmt = $pdo->prepare("
        SELECT 
            o.id_order,
            o.created_at,
            o.status,
            u.name,
            GROUP_CONCAT(CONCAT(d.name, '×', i.count) SEPARATOR ', ') as items,
            SUM(d.price * i.count) as total
        FROM `order` o
        JOIN item i ON o.id_order = i.order_id
        JOIN dish d ON i.dish_id = d.id_dish
        JOIN user u ON o.user_id = u.id_user
        WHERE o.shift_id = ?
        GROUP BY o.id_order
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$shiftId]);
    $orders = $stmt->fetchAll();
    $response = [
        'start' => $shift['start'],
        'end' => $shift['end'],
        'status' => $shift['status'],
        'employees' => $employees,
        'orders' => $orders 
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Ошибка базы данных',
        'details' => $e->getMessage()
    ]);
}