<?php 
$conn = mysqli_connect("localhost", "root", "", "Login");
if(!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
} else {
   // echo "connected successfully";
}

?>