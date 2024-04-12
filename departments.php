<?php

require_once 'database.php';

$departments = db_getDepartments();

echo "<pre>";
foreach ($departments as $department) {
    echo "Department ID: {$department['id']}, Name: {$department['name']}, Parent_id: {$department['parent']}\n";
}
echo "</pre>";
