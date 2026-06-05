<?php
$conn = mysqli_connect('localhost', 'root', '', 'admin_panel');
$res = mysqli_query($conn, 'DESCRIBE user');
while($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . "\n";
}
?>
