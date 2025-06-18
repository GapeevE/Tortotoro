<?php
session_start();
require 'db_connect.php';
if ($_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    die(json_encode(['error' => 'Доступ запрещен']));
}
$data = json_decode(file_get_contents('php://input'), true);
$shiftId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
try {
    $pdo->beginTransaction();
    if ($shiftId) {
        $stmt = $pdo->prepare("SELECT status FROM shift WHERE id_shift = ?");
        $stmt->execute([$shiftId]);
        $currentStatus = $stmt->fetchColumn();
        $allowedStatuses = ['pending', 'active', 'closed'];
        if (!in_array($data['status'], $allowedStatuses)) {
            throw new Exception('Недопустимый статус смены');
        }
        if ($data['status'] !== $currentStatus) {
            if ($currentStatus === 'closed') {
                throw new Exception('Нельзя изменить статус закрытой смены');
            }
            if ($currentStatus === 'active' && $data['status'] !== 'closed') {
                throw new Exception('Активную смену можно изменить только на закрытую');
            }
        }
        $stmt = $pdo->prepare("
            UPDATE shift 
            SET time_start = ?, 
                time_end = ?, 
                status = ?
            WHERE id_shift = ?
        ");
        $stmt->execute([
            $data['start'],
            $data['end'],
            $data['status'],
            $shiftId
        ]);
        $pdo->prepare("DELETE FROM assignment WHERE shift_id = ?")
            ->execute([$shiftId]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO shift 
            (time_start, time_end, status)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $data['start'],
            $data['end'],
            'pending'
        ]);
        $shiftId = $pdo->lastInsertId();
    }
    $stmt = $pdo->prepare("
        INSERT INTO assignment 
        (shift_id, user_id)
        VALUES (?, ?)
    ");
    foreach ($data['employees'] as $userId) {
        $stmt->execute([$shiftId, $userId]);
    }
    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка базы данных: ' . $e->getMessage()]);
}