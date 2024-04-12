<?php
require_once 'database.php';
db_init();
session_start();

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Если пользователь авторизован, показываем защищенную информацию
$user_id = $_SESSION['user_id'];
$current_user = db_getUserById($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>МИР ЛАЙКОВ</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="/">Мир лайков</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img src="images/profile_icon.png" alt="Профиль" width="30" height="30" class="rounded-circle mr-2">
                    <?php echo $current_user['name']; ?>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="logout.php">Выйти из аккаунта</a>
                </div>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <div class="functional" style="display: flex;flex-direction: column;">
        <div>Функционал сайта:</div>
        <a href="users.php">1) Список всех пользователей</a>
        <a href="append-user.php">2) Добавить пользователя</a>
        <a href="departments.php">3) Список подразделений</a>
        <a href="clear-db.php">4) Очистить всю базу данных</a>
        <a href="fill.php">5) Сгенерировать данные</a>
    </div>
    <div class="row justify-content-center mt-5">
        <div class="col-12">
            <ul class="list-group">
                <?php
                require_once 'database.php';
                $users = db_getUsers_with_data();
                foreach ($users as $user) {?>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><?php echo $user['name'] ?></span>
                            <span><?php echo $user['departments'] ?></span>
                            <div>
                                <span><?php echo $user['thanks_count'] ?> лайков</span>
                                <button class="btn
                                <?php
                                $is_thank = dbThankExists($current_user['id'], $user['id']);
                                if ($is_thank) {
                                    echo "btn-success";
                                } else {
                                    echo "btn-secondary";
                                }
                                ?>
                                like-btn" id="user-<?php echo $user['id'] ?>">Понравилось</button>
                                <a class="btn btn-info" href="thanks-given.php/?id=<?php echo $user['id'] ?>">Отданные</a>
                                <a class="btn btn-info" href="thanks-received.php/?id=<?php echo $user['id'] ?>">Полученные</a>
                            </div>
                        </div>
                    </li>
                    <?php
                }
                ?>
            </ul>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
<script>
    let likeButtons = document.querySelectorAll('.like-btn');
    likeButtons.forEach((button) => {
        button.addEventListener('click', (e) => {
            let button_id = e.target.id;
            let user_id = parseInt(button_id.split("-")[1]);
            send_ajax({"user_id": user_id})
        })
    });

    function send_ajax(params) {
        $.ajax({
            url: "/ajax.php",
            dataType: "json",
            method: "POST",
            data: params,
            success: function (response) {
                location.reload();
            }
        });
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
