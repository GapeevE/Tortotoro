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
    if (empty($data['employees']) || count($data['employees']) === 0) {
        if ($data['status'] === 'pending') {
            throw new Exception('Для смены должен быть назначен хотя бы один сотрудник');
        }
    }
    $pdo->beginTransaction();
    if ($data['status'] === 'active') {
        $checkActiveStmt = $pdo->prepare("
            SELECT id_shift 
            FROM shift 
            WHERE status = 'active'
            " . ($shiftId ? "AND id_shift != ?" : "")
        );
        $params = [];
        if ($shiftId) {
            $params[] = $shiftId;
        }
        $checkActiveStmt->execute($params);
        $activeShift = $checkActiveStmt->fetch();
        if ($activeShift) {
            throw new Exception('Уже существует активная смена #' . $activeShift['id_shift'] . 
                               '. Закройте текущую активную смену перед созданием новой.');
        }
    }
    if ($shiftId) {
        $stmt = $pdo->prepare("SELECT status FROM shift WHERE id_shift = ?");
        $stmt->execute([$shiftId]);
        $currentStatus = $stmt->fetchColumn();
        if ($data['status'] !== $currentStatus) {
            if ($data['status'] === 'closed') {
                $checkOrdersStmt = $pdo->prepare("
                    SELECT COUNT(*) 
                    FROM `order` 
                    WHERE shift_id = ? AND status NOT IN ('paid', 'canceled')
                ");
                $checkOrdersStmt->execute([$shiftId]);
                $unpaidCount = $checkOrdersStmt->fetchColumn();
                
                if ($unpaidCount > 0) {
                    throw new Exception('Невозможно закрыть смену: ' . $unpaidCount . 
                                       ' заказ(ов) не оплачены. Все заказы должны быть оплачены перед закрытием смены.');
                }
            }
        } 
        if ($currentStatus === 'pending') {
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
        } elseif ($currentStatus === 'active' && $data['status'] === 'closed') {
            $stmt = $pdo->prepare("
                UPDATE shift 
                SET status = 'closed'
                WHERE id_shift = ?
            ");
            $stmt->execute([$shiftId]);
        } 
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
    echo json_encode(['success' => true, 'message' => $shiftId ? 'Смена обновлена!' : 'Смена создана!']);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка базы данных: ' . $e->getMessage()]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}