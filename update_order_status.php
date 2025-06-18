<?php
session_start();
require 'db_connect.php';
if ($_SESSION['user_role'] === 'waiter' || $_SESSION['user_role'] === 'chief') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['order_id']) || !isset($data['status'])) {
        http_response_code(400);
        die(json_encode(['error' => 'Неверные данные']));
    }
    try {
        $stmt = $pdo->prepare("SELECT status FROM `order` WHERE id_order = ?");
        $stmt->execute([$data['order_id']]);
        $currentStatus = $stmt->fetchColumn();
        if (!$currentStatus) {
            throw new Exception('Заказ не найден');
        }
        if ($data['status'] !== $currentStatus) {
            if ($currentStatus === 'paid') {
                throw new Exception('Статус paid нельзя изменить');
            }
            switch ($currentStatus) {
                case 'pending':
                    if (!in_array($data['status'], ['preparing', 'canceled'])) {
                        throw new Exception('Можно изменить статус pending только на preparing или canceled');
                    }
                    break;
                case 'preparing':
                    if (!in_array($data['status'], ['ready', 'canceled'])) {
                        throw new Exception('Можно изменить статус preparing только на ready или canceled');
                    }
                    break;
                case 'ready':
                    if (!in_array($data['status'], ['paid', 'canceled'])) {
                        throw new Exception('Можно изменить статус ready только на paid или canceled');
                    }
                    break;
                case 'canceled':
                    throw new Exception('Статус canceled нельзя изменить');
                default:
                    throw new Exception('Недопустимый текущий статус');
            }
        }
        $sql = "UPDATE `order` SET status = ? WHERE id_order = ?";
        $params = [$data['status'], $data['order_id']];
        if ($_SESSION['user_role'] === 'waiter') {
            $sql .= " AND user_id = ?";
            $params[] = $_SESSION['user_id'];
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if ($stmt->rowCount() === 0) {
            throw new Exception('Заказ не найден или нет прав для изменения');
        }
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Ошибка базы данных: ' . $e->getMessage()]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(403);
    die(json_encode(['error' => 'Доступ запрещен']));
}
