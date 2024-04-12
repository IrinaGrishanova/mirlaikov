<?php

function filterThanksByDate($thanks, $dateFrom, $dateTo) {
    $filteredThanks = array();

    foreach ($thanks as $thank) {
        $thankDateUnix = strtotime($thank['date']);

        if (($dateFrom === null || $thankDateUnix >= $dateFrom) &&
            ($dateTo === null || $thankDateUnix <= ($dateTo + 86399))) {
            $filteredThanks[] = $thank;
        }
    }

    return $filteredThanks;
}

function filterThanksByDepartmentReceived($thanks, $department_id) {
    $db = new PDO('sqlite:mydatabase.db');

    $query = "SELECT * FROM Thank
              WHERE user_from_id IN (
                  SELECT user_id FROM User_Department
                  WHERE department_id = :department_id
              ) OR :department_id IS NULL";

    $query = $db->prepare($query);
    $query->bindValue(':department_id', $department_id);
    $query->execute();
    $filteredThanks = $query->fetchAll(PDO::FETCH_ASSOC);

    $thanksIds = array_column($thanks, 'id');

    $filteredThanks = array_filter($filteredThanks, function ($item) use ($thanksIds) {
        return in_array($item['id'], $thanksIds);
    });

    return $filteredThanks;
}

function filterThanksByDepartmentGiven($thanks, $department_id) {
    $db = new PDO('sqlite:mydatabase.db');

    $query = "SELECT * FROM Thank
              WHERE user_to_id IN (
                  SELECT user_id FROM User_Department
                  WHERE department_id = :department_id
              ) OR :department_id IS NULL";

    $query = $db->prepare($query);
    $query->bindValue(':department_id', $department_id);
    $query->execute();
    $filteredThanks = $query->fetchAll(PDO::FETCH_ASSOC);

    $thanksIds = array_column($thanks, 'id');

    $filteredThanks = array_filter($filteredThanks, function ($item) use ($thanksIds) {
        return in_array($item['id'], $thanksIds);
    });

    return $filteredThanks;
}

function pageFilter($thanks, $currentPage) {
    $itemsPerPage = 20;
    $totalItems = count($thanks);
    $totalPages = ceil($totalItems / $itemsPerPage);
    $offset = ($currentPage - 1) * $itemsPerPage;

    $pagedArray = array_slice($thanks, $offset, $itemsPerPage);

    return [
        'thanks' => $pagedArray,
        'total_pages' => $totalPages
    ];
}
