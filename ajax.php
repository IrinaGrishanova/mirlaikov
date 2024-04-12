<?php

header('Content-Type: application/json');

// Сюда летит ajax запрос с id пользователя кому нужно поставить благодарность после нажатия кнопки Понравилось
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$fromUserId = $_SESSION['user_id'];
$toUserId = $_POST['user_id'] ?? null;
if (!$toUserId) {
    return;
}

require_once 'database.php';

// Если благодарность существует, удаляем, иначе добавляем
$isThank = dbThankExists($fromUserId, $toUserId);
if ($isThank) {
    dbRemoveThank($fromUserId, $toUserId);
    echo json_encode(true);
} else {
    dbAddThank($fromUserId, $toUserId);
    echo json_encode(false);
}
