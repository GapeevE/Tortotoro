<?php
session_start();
require 'db_connect.php'; 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}
$login = trim($_POST['login']);
$password = trim($_POST['password']);
try {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    die("Ошибка запроса: " . $e->getMessage());
}
if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['login_error'] = 'Неверный логин или пароль';
    header("Location: index.php");
    exit;
}
$_SESSION['user_id'] = $user['id_user'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['photo_file'] = $user['photo_file'];
if ($user['role_id'] !== 1) {
    try {
        $stmt = $pdo->prepare("
            SELECT id_shift 
            FROM `shift` 
            WHERE status = 'active' 
            ORDER BY time_start DESC 
            LIMIT 1
        ");
        $stmt->execute();
        $shift = $stmt->fetch();
        if (!$shift || !isset($shift['id_shift'])) {
            $_SESSION['login_error'] = 'Нет активной смены';
            header("Location: index.php");
            exit;
        }
        $_SESSION['shift_id'] = $shift['id_shift'];
    } catch (PDOException $e) {
        die("Ошибка при проверке смены: " . $e->getMessage());
    }
}
switch ($user['role_id']) {
    case 1:
        $_SESSION['user_role'] = 'admin';
        header("Location: admin.php");
        break;
    case 2:
        $_SESSION['user_role'] = 'waiter';
        header("Location: waiter.php");
        break;
    case 3:
        $_SESSION['user_role'] = 'chief';
        header("Location: chief.php");
        break;
    default:
        $_SESSION['login_error'] = 'Неизвестная роль пользователя';
        header("Location: index.php");
}
exit;