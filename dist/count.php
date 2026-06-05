<?php
include 'db1.php';

$sql = mysqli_query($conn, "SELECT COUNT(*) FROM user");
$row = mysqli_fetch_array($sql);
echo $row[0];
?>