<?php
include 'db1.php';

header('Content-Type: application/json; charset=UTF-8');

$rows = [];
$result = mysqli_query(
    $conn,
    "SELECT user.id, user.name, user.email, user.password,
            user.country_id, user.state_id, user.city_id,
            country.country_name AS country_name,
            state.state_name AS state_name,
            city.city_name AS city_name
     FROM user
     LEFT JOIN country ON user.country_id = country.id
     LEFT JOIN state ON user.state_id = state.id
     LEFT JOIN city ON user.city_id = city.id
     ORDER BY user.id ASC"
);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $id = (int)$row['id'];
        $name = htmlspecialchars($row['name'] ?? '', ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8');
        $password = htmlspecialchars($row['password'] ?? '', ENT_QUOTES, 'UTF-8');
        $city = htmlspecialchars($row['city_name'] ?? '', ENT_QUOTES, 'UTF-8');
        $state = htmlspecialchars($row['state_name'] ?? '', ENT_QUOTES, 'UTF-8');
        $country = htmlspecialchars($row['country_name'] ?? '', ENT_QUOTES, 'UTF-8');
        $countryId = (int)($row['country_id'] ?? 0);
        $stateId = (int)($row['state_id'] ?? 0);
        $cityId = (int)($row['city_id'] ?? 0);

        $actions = '<button type="button" class="btn btn-link p-0 me-2 editBtn" data-bs-toggle="modal" data-bs-target="#editUserModal" data-id="' . $id . '" data-name="' . $name . '" data-email="' . $email . '" data-password="' . $password . '" data-country-id="' . $countryId . '" data-state-id="' . $stateId . '" data-city-id="' . $cityId . '"><i class="bi bi-pencil-fill fs-5 text-secondary"></i></button>';
        $actions .= '<form method="POST" class="d-inline deleteForm"><input type="hidden" name="delete_id" value="' . $id . '"><button type="submit" name="delete_btn" class="btn btn-link p-0"><i class="bi bi-trash3-fill fs-5 text-danger"></i></button></form>';

        $rows[] = [
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'city' => $city,
            'state' => $state,
            'country' => $country,
            'actions' => $actions
        ];
    }
}

echo json_encode(['data' => $rows]);
