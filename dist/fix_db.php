<?php
$conn = mysqli_connect("localhost", "root", "", "admin_panel");
if(!$conn) die("Connection Failed: " . mysqli_connect_error());

$res = mysqli_query($conn, "DESCRIBE user");
while($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . "\n";
}
echo "---END DESCRIBE---\n";

$sql = "ALTER TABLE user ADD COLUMN profile_picture VARCHAR(255) DEFAULT 'default.png'";
if(mysqli_query($conn, $sql)) {
    echo "Added profile_picture successfully.\n";
} else {
    echo "Error adding profile_picture: " . mysqli_error($conn) . "\n";
}
?>
