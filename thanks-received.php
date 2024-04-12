<?php
require_once 'database.php';
require_once 'functions.php';

$user_id = $_GET['id'];
$currentPage = $_GET['page'] ?? 1;
$current_department = $_GET['department'] ?? null;
$dateFrom = $_GET['dateFrom'] ?? null;
$dateTo = $_GET['dateTo'] ?? null;

$user = db_getUserById($user_id);
$thanks = db_received_thanks($user_id);

$thanks = filterThanksByDate($thanks, $dateFrom, $dateTo);
$thanks = filterThanksByDepartmentReceived($thanks, $current_department);
$thanks = pageFilter($thanks, $currentPage);

$departments = db_getDepartments();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Полученные благодарности</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="/">Мир лайков</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
</nav>

<div class="container">
    <h1>Благодарности полученные пользователем <?php echo $user['name']; ?></h1>
    <div class="row mb-3">
        <div class="col-md-4">
            <label for="dateFrom">From:</label>
            <input type="date" class="form-control" id="dateFrom"
                   value="<?php echo isset($_GET['dateFrom']) ? date('Y-m-d', $_GET['dateFrom']) : ''; ?>">
        </div>
        <div class="col-md-4">
            <label for="dateTo">To:</label>
            <input type="date" class="form-control" id="dateTo"
                   value="<?php echo isset($_GET['dateTo']) ? date('Y-m-d', $_GET['dateTo']) : ''; ?>">
        </div>
        <div class="col-md-4">
            <label for="department">Department:</label>
            <select class="form-control" id="department">
                <option value="">Все</option>
                <?php foreach ($departments as $department): ?>
                    <?php if ($department['id'] == $current_department): ?>
                        <option selected value="<?php echo $department['id']; ?>"><?php echo $department['name']; ?></option>
                    <?php else: ?>
                        <option value="<?php echo $department['id']; ?>"><?php echo $department['name']; ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <table class="table">
        <thead>
        <tr>
            <th>От кого</th>
            <th>Дата</th>
            <th>Подразделения</th>
        </tr>
        </thead>
        <tbody id="tableBody">
        <?php foreach ($thanks['thanks'] as $thank):
            $from_user = db_getUserById($thank['user_from_id']);
            $departments = db_getUserDepartments($from_user['id']);
            $departments = implode(", ", $departments);
            ?>
            <tr>
                <td><?php echo $from_user['name']; ?></td>
                <td><?php echo $thank['date']; ?></td>
                <td><?php echo $departments; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <ul class="pagination justify-content-center" id="pagination">
        <?php for ($i = 1; $i <= $thanks['total_pages']; $i++):
            $activeClass = ($i == $currentPage) ? ' active' : '';
            echo '<li class="page-item' . $activeClass . '"><a class="page-link" value="' . $i . '">' . $i . '</a></li>';
        endfor; ?>
    </ul>
</div>

<script>
    document.getElementById("dateFrom").addEventListener("change", function () {
        var selectedDate = this.value;
        var url = new URL(window.location.href);
        var params = new URLSearchParams(url.search);

        if (selectedDate) {
            var unixTimestamp = new Date(selectedDate).getTime() / 1000;
            params.set('dateFrom', unixTimestamp);
        } else {
            params.delete('dateFrom');
        }
        params.delete('page');
        url.search = params.toString();
        window.location.href = url.toString();
    });

    document.getElementById("dateTo").addEventListener("change", function () {
        var selectedDate = this.value;
        var url = new URL(window.location.href);
        var params = new URLSearchParams(url.search);

        if (selectedDate) {
            var unixTimestamp = new Date(selectedDate).getTime() / 1000;
            params.set('dateTo', unixTimestamp);
        } else {
            params.delete('dateTo');
        }
        params.delete('page');
        url.search = params.toString();
        window.location.href = url.toString();
    });

    document.addEventListener('DOMContentLoaded', function () {
        let pageLinks = document.querySelectorAll('.page-link');
        pageLinks.forEach(function (pageLink) {
            pageLink.addEventListener('click', function () {
                let pageNumber = this.getAttribute('value');
                let url = new URL(window.location.href);
                let params = new URLSearchParams(url.search);
                params.set('page', pageNumber);
                url.search = params.toString();
                window.location.href = url.toString();
            });
        });
    });

    let selectElement = document.getElementById('department');
    selectElement.addEventListener('change', function () {
        let url = new URL(window.location.href);
        let params = new URLSearchParams(url.search);
        let department_id = selectElement.value;
        if (department_id) {
            params.set('department', department_id);
        } else {
            params.delete('department');
        }
        params.delete('page');
        url.search = params.toString();
        window.location.href = url.toString();
    });
</script>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
