<?php
// Очистка БД
require_once 'database.php';

$message = clearDatabase();

session_start();

// Удаляем все переменные сессии
session_unset();

// Уничтожаем сессию
session_destroy();

// Перенаправляем пользователя на страницу входа
header("Location: login.php");
exit();
