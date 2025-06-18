<?php
require 'db_connect.php';
try {
    $stmt = $pdo->query("
        SELECT 
            u.id_user as id,
            u.name,
            CASE 
                WHEN u.role_id = 1 THEN 'Администратор'
                WHEN u.role_id = 2 THEN 'Официант'
                WHEN u.role_id = 3 THEN 'Повар'
            END as role
        FROM user u
        WHERE u.role_id IN (2,3)
        ORDER BY u.name
    ");
    header('Content-Type: application/json');
    echo json_encode($stmt->fetchAll());
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка базы данных']);
}