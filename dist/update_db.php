<?php
include 'db1.php';
$query = "ALTER TABLE user ADD COLUMN profile_picture VARCHAR(255) DEFAULT 'default.png'";
if(mysqli_query($conn, $query)){
    echo "Column added successfully";
} else {
    echo "Error adding column: " . mysqli_error($conn);
}
?>
