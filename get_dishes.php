<?php
session_start();
require 'db_connect.php';
if ($_SESSION['user_role'] != 'waiter') { 
    http_response_code(403);
    die(json_encode(['error' => 'Доступ запрещен']));
}
try {
    $stmt = $pdo->query("SELECT * FROM dish");
    $dishes = $stmt->fetchAll();
    header('Content-Type: application/json');
    echo json_encode($dishes);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка базы данных']);
}