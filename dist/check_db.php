<?php
include 'db1.php';
$res = mysqli_query($conn, "DESCRIBE user");
while($r = mysqli_fetch_assoc($res)) {
    print_r($r);
}
?>
