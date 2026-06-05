<?php
include 'db1.php';

header('Content-Type: application/json; charset=UTF-8');

$state_id = isset($_GET['state_id']) ? (int)$_GET['state_id'] : 0;

// If state_id is provided, return plain city list for dependent dropdowns.
if ($state_id > 0) {
    $cities = [];
    $result = mysqli_query($conn, "SELECT id, city_name FROM city WHERE state_id = $state_id ORDER BY city_name ASC");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $cities[] = $row;
        }
    }
    echo json_encode($cities);
    exit;
}

// Default response for DataTables.
$rows = [];
$result = mysqli_query(
    $conn,
    "SELECT city.id, city.city_name, city.state_id, state.state_name, state.country_id, country.country_name
     FROM city
     LEFT JOIN state ON city.state_id = state.id
     LEFT JOIN country ON state.country_id = country.id
     ORDER BY city.id ASC"
);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $id = (int)$row['id'];
        $cityName = htmlspecialchars($row['city_name'] ?? '', ENT_QUOTES, 'UTF-8');
        $stateName = htmlspecialchars($row['state_name'] ?? '', ENT_QUOTES, 'UTF-8');
        $countryName = htmlspecialchars($row['country_name'] ?? '', ENT_QUOTES, 'UTF-8');
        $countryId = (int)($row['country_id'] ?? 0);
        $stateId = (int)($row['state_id'] ?? 0);

        $actions = '<button type="button" class="btn btn-link p-0 me-2 cityEditBtn" data-bs-toggle="modal" data-bs-target="#editCityModal" data-id="' . $id . '" data-name="' . $cityName . '" data-country-id="' . $countryId . '" data-state-id="' . $stateId . '"><i class="bi bi-pencil-fill fs-5 text-secondary"></i></button>';
        $actions .= '<form method="POST" class="d-inline cityDeleteForm"><input type="hidden" name="delete_id" value="' . $id . '"><button type="submit" name="city_delete_submit" class="btn btn-link p-0"><i class="bi bi-trash3-fill fs-5 text-danger"></i></button></form>';

        $rows[] = [
            'id' => $id,
            'city_name' => $cityName,
            'state_name' => $stateName,
            'country_name' => $countryName,
            'actions' => $actions
        ];
    }
}

echo json_encode(['data' => $rows]);

