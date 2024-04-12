<?php

function db_init() {
    // Открываем соединение с базой данных
    $db = new PDO('sqlite:mydatabase.db');

    // Создаем таблицу User, если она не существует
    $db->exec("CREATE TABLE IF NOT EXISTS User (
                id INTEGER PRIMARY KEY,
                name TEXT,
                password TEXT)");

    // Создаем таблицу Thank, если она не существует
    $db->exec("CREATE TABLE IF NOT EXISTS Thank (
                id INTEGER PRIMARY KEY,
                user_from_id INTEGER,
                user_to_id INTEGER,
                date DATETIME,
                FOREIGN KEY(user_from_id) REFERENCES User(id),
                FOREIGN KEY(user_to_id) REFERENCES User(id))");

    // Создаем таблицу Department, если она не существует
    $db->exec("CREATE TABLE IF NOT EXISTS Department (
                id INTEGER PRIMARY KEY,
                name TEXT,
                parent INTEGER,
                FOREIGN KEY(parent) REFERENCES Department(id))");

    // Создаем таблицу User_Department, если она не существует
    $db->exec("CREATE TABLE IF NOT EXISTS User_Department (
                id INTEGER PRIMARY KEY,
                user_id INTEGER,
                department_id INTEGER,
                FOREIGN KEY(user_id) REFERENCES User(id),
                FOREIGN KEY(department_id) REFERENCES Department(id))");

    // Проверяем, пуста ли таблица Department
    $department_count = $db->query("SELECT COUNT(*) FROM Department")->fetchColumn();

    // Если таблица Department пустая, добавляем данные
    if ($department_count == 0) {
        // Массив данных для заполнения
        $departments = array(
            array('name' => 'Отдел 1', 'parent' => null),
            array('name' => 'Отдел 1 1', 'parent' => 1),
            array('name' => 'Отдел 1 2', 'parent' => 1),
            array('name' => 'Отдел 2', 'parent' => null),
            array('name' => 'Отдел 2 1', 'parent' => 4),
            array('name' => 'Отдел 2 2', 'parent' => 4),
        );

        // Подготавливаем запрос на вставку данных
        $stmt = $db->prepare("INSERT INTO Department (name, parent) VALUES (:name, :parent)");

        // Выполняем вставку данных
        foreach ($departments as $dept) {
            $stmt->execute($dept);
        }
    }

    // Закрываем соединение с базой данных
    $db = null;
}

function db_append_user($username, $password, $department_ids) {
    // Подключаемся к базе данных
    $db = new PDO('sqlite:mydatabase.db');

    // Проверяем, существует ли пользователь с таким именем
    $query = $db->prepare("SELECT COUNT(*) FROM User WHERE name = :username");
    $query->bindParam(':username', $username);
    $query->execute();
    $count = $query->fetchColumn();

    if ($count > 0) {
        return "Пользователь с именем $username уже существует в базе данных.";
    }

    // Добавляем пользователя в базу данных
    $insertQuery = $db->prepare("INSERT INTO User (name, password) VALUES (:username, :password)");
    $insertQuery->bindParam(':username', $username);
    $insertQuery->bindParam(':password', $password);
    $insertQuery->execute();

    // Получаем ID только что добавленного пользователя
    $user_id = $db->lastInsertId();

    // Добавляем записи в таблицу User_Department для каждого переданного department_id
    foreach ($department_ids as $department_id) {
        $insertUserDepartmentQuery = $db->prepare("INSERT INTO User_Department (user_id, department_id) VALUES (:user_id, :department_id)");
        $insertUserDepartmentQuery->bindParam(':user_id', $user_id);
        $insertUserDepartmentQuery->bindParam(':department_id', $department_id);
        $insertUserDepartmentQuery->execute();
    }

    $department_ids_str = implode(', ', $department_ids);
    return "Пользователь $username успешно добавлен в базу данных и привязан к отделам с ID: $department_ids_str.";
}

function db_getUserById($userId) {
    // Подготовленный запрос для извлечения пользователя по его ID
    $db = new PDO('sqlite:mydatabase.db');
    $query = $db->prepare("SELECT * FROM User WHERE id = :id");
    $query->bindParam(':id', $userId);
    $query->execute();

    // Получаем результат запроса
    $user = $query->fetch(PDO::FETCH_ASSOC);
    return $user;
}

function db_authorization($login, $password) {
    // Открываем соединение с базой данных
    $db = new PDO('sqlite:mydatabase.db');
    
    // Подготавливаем SQL запрос
    $query = $db->prepare("SELECT id FROM User WHERE name = :login AND password = :password");
    
    // Выполняем запрос с переданными параметрами
    $query->execute(array(':login' => $login, ':password' => $password));
    
    // Получаем результат запроса
    $result = $query->fetch(PDO::FETCH_ASSOC);
    
    // Если есть результат, возвращаем имя пользователя, иначе возвращаем false
    if ($result) {
        return $result['id'];
    } else {
        return false;
    }
}

function getAllUsers() {
    $db = new PDO('sqlite:mydatabase.db');
    $query = $db->query('SELECT * FROM User');
    $users = $query->fetchAll(PDO::FETCH_ASSOC);
    return $users;
}

function dbThankExists($user_from_id, $user_to_id) {
    $db = new PDO('sqlite:mydatabase.db');
    // Проверяем, существует ли уже благодарность между этими пользователями
    $query = $db->prepare("SELECT COUNT(*) FROM Thank WHERE user_from_id = ? AND user_to_id = ?");
    $query->execute([$user_from_id, $user_to_id]);
    $count = $query->fetchColumn();

    // Если благодарность уже существует, возвращаем false
    if ($count > 0) {
        return true;
    }
    else{
        return false;
    }
}

function db_getUsers_with_data() {
    $db = new PDO('sqlite:mydatabase.db');
    $users = array();

    // Запрос для получения пользователей и общего количества благодарностей для каждого пользователя
    $query = "SELECT User.id, User.name, COUNT(Thank.id) AS thanks_count
              FROM User
              LEFT JOIN Thank ON User.id = Thank.user_to_id
              GROUP BY User.id
              ORDER BY thanks_count DESC"; // Сортировка по thanks_count в обратном порядке (от большего к меньшему)

    // Подготовка запроса
    $query = $db->prepare($query);

    // Выполнение запроса
    $query->execute();

    // Обработка результатов запроса
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        // Запрос для получения департаментов, в которых состоит пользователь
        $departmentQuery = "SELECT Department.name 
                            FROM Department
                            INNER JOIN User_Department ON Department.id = User_Department.department_id
                            WHERE User_Department.user_id = :user_id";
        $departmentStatement = $db->prepare($departmentQuery);
        $departmentStatement->bindParam(':user_id', $row['id']);
        $departmentStatement->execute();
        $departments = $departmentStatement->fetchAll(PDO::FETCH_COLUMN);
        $departments = implode(", ", $departments);

        // Создание массива с информацией о пользователе
        $user = array(
            'id' => $row['id'],
            'name' => $row['name'],
            'thanks_count' => $row['thanks_count'],
            'departments' => $departments // Добавление названий департаментов
        );
        $users[] = $user;
    }

    return $users;
}

function dbAddThank($user_from_id, $user_to_id) {
    $db = new PDO('sqlite:mydatabase.db');

    // Вставляем новую благодарность
    $query = $db->prepare("INSERT INTO Thank (user_from_id, user_to_id, date) VALUES (?, ?, datetime('now'))");
    $query->execute([$user_from_id, $user_to_id]);

}

function dbRemoveThank($user_from_id, $user_to_id) {
    $db = new PDO('sqlite:mydatabase.db');

    // Удаляем благодарность
    $query = $db->prepare("DELETE FROM Thank WHERE user_from_id = ? AND user_to_id = ?");
    $query->execute([$user_from_id, $user_to_id]);
}

function db_received_thanks($user_to_id){
    $db = new PDO('sqlite:mydatabase.db');
    $query = $db->prepare("SELECT * FROM Thank WHERE user_to_id = :user_to_id");
    $query->bindParam(':user_to_id', $user_to_id);
    $query->execute();
    $thanks = $query->fetchAll(PDO::FETCH_ASSOC);

    return $thanks;
}

function db_given_thanks($user_from_id) {
    $db = new PDO('sqlite:mydatabase.db');
    $query = $db->prepare("SELECT * FROM Thank WHERE user_from_id = :user_from_id");
    $query->bindParam(':user_from_id', $user_from_id);
    $query->execute();
    $thanks = $query->fetchAll(PDO::FETCH_ASSOC);

    return $thanks;

}

function db_getDepartments() {
    $db = new PDO('sqlite:mydatabase.db');
    $query = "SELECT * FROM Department";
    $query = $db->query($query);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

function db_getUserDepartments($userId) {
    $db = new PDO('sqlite:mydatabase.db');
    $query = $db->prepare("SELECT Department.name FROM Department 
                               INNER JOIN User_Department ON Department.id = User_Department.department_id
                               WHERE User_Department.user_id = :userId");

    // Привязываем параметр :userId к значению $userId
    $query->bindParam(':userId', $userId);

    // Выполнение запроса
    $query->execute();

    // Извлечение результатов запроса в виде массива
    $departments = $query->fetchAll(PDO::FETCH_COLUMN);

    // Возвращаем массив с названиями департаментов пользователя
    return $departments;
}




function clearDatabase() {
    // Подключаемся к базе данных
    $db = new PDO('sqlite:mydatabase.db');

    $db->exec("DELETE FROM User");
    $db->exec("DELETE FROM Thank");
    $db->exec("DELETE FROM Department");
    $db->exec("DELETE FROM User_Department");


    // Закрываем соединение с базой данных
    $db = null;

    return "База данных успешно очищена.";

}

function generateData(){
    $db = new PDO('sqlite:mydatabase.db');

    // Генерация случайного имени пользователя
    function generateRandomName() {
        static $names = ['Alice', 'Bob', 'Charlie', 'David', 'Eve', 'Frank', 'Grace', 'Henry', 'Ivy', 'Jack', 'Katie', 'Liam', 'Mia', 'Noah', 'Olivia', 'Peter', 'Quinn', 'Rachel', 'Sam', 'Taylor', 'Uma', 'Victor', 'Wendy', 'Xander', 'Yara', 'Zane'];
        $nameCount = count($names);
        $index = rand(0, $nameCount - 1);
        $name = $names[$index];
        // Удаляем использованное имя из массива
        array_splice($names, $index, 1);
        // Если массив имен опустел, восстанавливаем его
        if ($nameCount <= 1) {
            $names = ['Alice', 'Bob', 'Charlie', 'David', 'Eve', 'Frank', 'Grace', 'Henry', 'Ivy', 'Jack', 'Katie', 'Liam', 'Mia', 'Noah', 'Olivia', 'Peter', 'Quinn', 'Rachel', 'Sam', 'Taylor', 'Uma', 'Victor', 'Wendy', 'Xander', 'Yara', 'Zane'];
        }
        return $name;
    }

    // Получение случайной группы (1, 2, 3) или (4, 5, 6)
    function getRandomGroup() {
        $groups = [[1, 2, 3], [4, 5, 6]];
        return $groups[rand(0, 1)];
    }

    // Получение случайных департаментов
    function getRandomDepartments() {
        $departments = [];
        $numDepartments = rand(1, 3);
        for ($i = 0; $i < $numDepartments; $i++) {
            $departments[] = rand(1, 6); // Предполагается, что у вас есть 6 департаментов с id от 1 до 6
        }
        return $departments;
    }

// Вставка пользователей в таблицу User
    for ($i = 0; $i < rand(30, 50); $i++) {
        $name = generateRandomName();
        $group = getRandomGroup();
        $departments = getRandomDepartments();

        // Вставка пользователя в таблицу User
        $stmt = $db->prepare("INSERT INTO User (name, password) VALUES (:name, :password)");
        $stmt->execute([':name' => $name, ':password' => $name]);
        $userId = $db->lastInsertId();

        // Привязка пользователя к департаментам
        foreach ($departments as $departmentId) {
            $stmt = $db->prepare("INSERT INTO User_Department (user_id, department_id) VALUES (:user_id, :department_id)");
            $stmt->execute([':user_id' => $userId, ':department_id' => $departmentId]);
        }
    }

    // Получение списка всех пользователей
    $stmt = $db->query("SELECT id FROM User");
    $users = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Создание случайных благодарностей
    foreach ($users as $user) {
        $numThanks = rand(5, 30);
        $alreadyThanked = []; // Структура данных для хранения уже выданных благодарностей для данного пользователя
        for ($i = 0; $i < $numThanks; $i++) {
            $userTo = $users[rand(0, count($users) - 1)];
            // Проверка на то, чтобы пользователь не благодарил сам себя и не повторял благодарность
            if ($userTo != $user && !in_array($userTo, $alreadyThanked)) {
                $alreadyThanked[] = $userTo; // Добавляем пользователя в список уже выданных благодарностей
                $date = date('Y-m-d H:i:s', rand(time() - 86400 * 30, time())); // Благодарности за последний месяц
                $stmt = $db->prepare("INSERT INTO Thank (user_from_id, user_to_id, date) VALUES (:user_from_id, :user_to_id, :date)");
                $stmt->execute([':user_from_id' => $user, ':user_to_id' => $userTo, ':date' => $date]);
            }
        }
    }



    echo "Таблицы успешно заполнены!";
}