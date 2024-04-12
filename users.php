<?php
// Список всех пользователей
require_once 'database.php';

$users = getAllUsers();
echo "<pre>";
foreach ($users as $user) {
    echo "User ID: {$user['id']}, Name: {$user['name']}, Password: {$user['password']}\n";
}
echo "</pre>";
