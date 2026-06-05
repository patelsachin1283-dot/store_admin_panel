<?php
include 'db1.php';
$country_id = (int)$_GET['country_id'];
$result = mysqli_query($conn, "SELECT id, state_name FROM state WHERE country_id = $country_id");
$states = [];
while ($row = mysqli_fetch_assoc($result)) {
    $states[] = $row;
}
echo json_encode($states);