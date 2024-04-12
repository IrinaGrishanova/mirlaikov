<?php
require_once 'database.php';

// После отправки форм с данными мы получаем их и добавляем нового пользователя
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Разделить строку по запятой и поместить каждый элемент в массив
    $departments_ids = explode(",", $_POST['departments']);
    if ($departments_ids[0] != "") {
        $message = db_append_user($_POST['username'], $_POST['password'], $departments_ids);
    } else {
        $message = "Укажите департамент к которому принадлежит пользователь";
    }
}

$departments = db_getDepartments();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить пользователя</title>
    <link rel="stylesheet" href="css/login.css">
    <!-- Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <!-- Or for RTL support -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.rtl.min.css" />

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.0/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>

</head>

<body>

<div class="login">
    <div class="login-screen">
        <div class="app-title">
            <h1>Добавить пользователя</h1>
        </div>

        <div class="login-form">
            <?php if (isset($message)) { ?>
                <div class="message"><?php echo $message; ?></div>
            <?php } ?>
            <form method="post" action="">
                <div class="control-group">
                    <input type="text" class="login-field" value="" placeholder="логин" id="login-name" name="username" required>
                    <label class="login-field-icon fui-user" for="login-name"></label>
                </div>

                <div class="control-group">
                    <input type="password" class="login-field" value="" placeholder="пароль" id="login-pass" name="password" required>
                    <label class="login-field-icon fui-lock" for="login-pass"></label>
                </div>
                <div class="control-group">
                    <select class="form-select" id="multiple-select-field" data-placeholder="Выберите подразделение" multiple>
                        <?php
                        foreach ($departments as $department) {
                            echo "<option value='" . $department['id'] . "' >" . $department['name'] . "</option>";
                        } ?>

                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-large btn-block">Добавить пользователя</button>
            </form>
        </div>
    </div>
</div>
<script>
    $('#multiple-select-field').select2({
        theme: "bootstrap-5",
        width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
        placeholder: $(this).data('placeholder'),
        closeOnSelect: false,
    });



    // Получаем форму по ее ID
    let form = document.querySelector('form');
    // Обрабатываем событие отправки формы
    form.addEventListener('submit', function(event) {
        // Предотвращаем отправку формы
        event.preventDefault();

        // Получаем выбранные значения из элемента <select>
        let selectElement = document.getElementById('multiple-select-field');
        let selectedValues = Array.from(selectElement.selectedOptions).map(option => option.value);

        // Создаем скрытое поле для передачи выбранных значений
        let selectedValuesInput = document.createElement('input');
        selectedValuesInput.setAttribute('type', 'hidden');
        selectedValuesInput.setAttribute('name', 'departments');
        selectedValuesInput.setAttribute('value', selectedValues.join(','));

        // Добавляем скрытое поле к форме
        form.appendChild(selectedValuesInput);

        // Отправляем форму
        form.submit();
    });
</script>
</body>

</html>
