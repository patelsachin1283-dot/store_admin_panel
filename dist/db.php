<?php
$conn = mysqli_connect("localhost", "root", "", "store");

if(!$conn) {
    die ("Connection Failed: " . mysqli_connect_error());
}

?>