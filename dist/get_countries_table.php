<?php
include 'db1.php';

header('Content-Type: application/json; charset=UTF-8');

$rows = [];
$result = mysqli_query($conn, "SELECT id, country_name FROM country ORDER BY id ASC");

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $id = (int)$row['id'];
        $countryName = htmlspecialchars($row['country_name'] ?? '', ENT_QUOTES, 'UTF-8');

        $actions = '<button type="button" class="btn btn-link p-0 me-2 countryEditBtn" data-bs-toggle="modal" data-bs-target="#editCountryModal" data-id="' . $id . '" data-name="' . $countryName . '"><i class="bi bi-pencil-fill fs-5 text-secondary"></i></button>';
        $actions .= '<form method="POST" class="d-inline countryDeleteForm"><input type="hidden" name="delete_id" value="' . $id . '"><button type="submit" name="country_delete_submit" class="btn btn-link p-0"><i class="bi bi-trash3-fill fs-5 text-danger"></i></button></form>';

        $rows[] = [
            'id' => $id,
            'country_name' => $countryName,
            'actions' => $actions
        ];
    }
}

echo json_encode(['data' => $rows]);
