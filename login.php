<?php
require_once 'database.php';
db_init();
session_start();

// Проверка, если пользователь уже авторизован, перенаправляем его на другую страницу
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Проверка, если данные для входа были отправлены
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'database.php';

    $user_id = db_authorization($_POST['username'], $_POST['password']);
    if ($user_id !== false) {
        $_SESSION['user_id'] = $user_id; // просто пример ID пользователя, может быть что-то другое
        header("Location: index.php");
        exit();
    } else {
        $error = "Неверный логин или пароль";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>

<div class="login">
    <div class="login-screen">
        <div class="app-title">
            <a href="append-user.php">Добавить пользователя</a>
            <h1>Авторизация</h1>
        </div>

        <div class="login-form">
            <?php if (isset($error)) { ?>
                <div class="error"><?php echo $error; ?></div>
            <?php } ?>
            <form method="post" action="">
                <div class="control-group">
                    <input type="text" class="login-field" value="" placeholder="логин" id="login-name" name="username"
                           required>
                    <label class="login-field-icon fui-user" for="login-name"></label>
                </div>

                <div class="control-group">
                    <input type="password" class="login-field" value="" placeholder="пароль" id="login-pass"
                           name="password" required>
                    <label class="login-field-icon fui-lock" for="login-pass"></label>
                </div>

                <button type="submit" class="btn btn-primary btn-large btn-block">Войти</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
