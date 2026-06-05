<?php
include '../db1.php';

// jQuery Validation sends the input field name (e.g. 'email' or 'edit_email') as the parameter.
$email = '';
$edit_id = 0;

if (isset($_POST['edit_email'])) {
    $email = trim($_POST['edit_email']);
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
} elseif (isset($_POST['email'])) {
    $email = trim($_POST['email']);
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;

}

if ($email !== '') {
    if ($edit_id > 0) {
        // Edit Mode: Check if email exists for other users
        $stmt = mysqli_prepare($conn, "SELECT id FROM user WHERE email = ? AND id != ?");
        mysqli_stmt_bind_param($stmt, "si", $email, $edit_id);
    } else {
        // Add Mode: Check if email exists
        $stmt = mysqli_prepare($conn, "SELECT id FROM user WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
    }

    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);


    echo json_encode(mysqli_stmt_num_rows($stmt) == 0);
                        

    mysqli_stmt_close($stmt);
} else {
    echo json_encode(false);
}
