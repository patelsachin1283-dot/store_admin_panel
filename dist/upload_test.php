<?php
// Let's create a test upload page with the exact same variables
$target_dir = "uploads/profile_pictures/";
if (!is_dir($target_dir)) {
  mkdir($target_dir, 0755, true);
}
if(isset($_FILES["test"])) {
    $target_file = $target_dir . "test.jpg";
    if (move_uploaded_file($_FILES["test"]["tmp_name"], $target_file)) {
        echo "Uploaded.";
    } else {
        echo "Failed to move.";
    }
}
?>
<form method="POST" enctype="multipart/form-data">
<input type="file" name="test"><input type="submit">
</form>
