<?php
include 'db1.php';

header('Content-Type: application/json; charset=UTF-8');

$rows = [];
$result = mysqli_query(
    $conn,
    "SELECT state.id, state.state_name, state.country_id, country.country_name
     FROM state
     LEFT JOIN country ON state.country_id = country.id
     ORDER BY state.id ASC"
);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $id = (int)$row['id'];
        $stateName = htmlspecialchars($row['state_name'] ?? '', ENT_QUOTES, 'UTF-8');
        $countryName = htmlspecialchars($row['country_name'] ?? '', ENT_QUOTES, 'UTF-8');
        $countryId = (int)($row['country_id'] ?? 0);

        $actions = '<button type="button" class="btn btn-link p-0 me-2 stateEditBtn" data-bs-toggle="modal" data-bs-target="#editStateModal" data-id="' . $id . '" data-name="' . $stateName . '" data-country-id="' . $countryId . '"><i class="bi bi-pencil-fill fs-5 text-secondary"></i></button>';
        $actions .= '<form method="POST" class="d-inline stateDeleteForm"><input type="hidden" name="delete_id" value="' . $id . '"><button type="submit" name="state_delete_submit" class="btn btn-link p-0"><i class="bi bi-trash3-fill fs-5 text-danger"></i></button></form>';

        $rows[] = [
            'id' => $id,
            'state_name' => $stateName,
            'country_name' => $countryName,
            'actions' => $actions
        ];
    }
}

echo json_encode(['data' => $rows]);
