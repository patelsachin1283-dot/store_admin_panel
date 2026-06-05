<?php
$conn = mysqli_connect("localhost", "root", "", "admin_panel");

if(!$conn) {
    die ("Connection Failed: " . mysqli_connect_error());
}

?>