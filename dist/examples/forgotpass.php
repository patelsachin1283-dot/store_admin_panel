<?php
session_start();
include '../db1.php'; 


$secret = "my_secret_key_123";

$email = isset($_GET['email']) ? $_GET['email'] : (isset($_POST['email']) ? $_POST['email'] : '');
$token = isset($_GET['token']) ? $_GET['token'] : '';
$timestamp = isset($_GET['ts']) ? $_GET['ts'] : '';

$message = '';
$message_type = '';
$valid_link = false;

if (isset($_GET['email'], $_GET['token'], $_GET['ts'])) {

    if (time() - $timestamp > 300) {
        $message = "❌ Link expired. Please request a new one.";
        $message_type = "danger";
    } else {
        $validToken = hash('sha256', $email . $timestamp . $secret);

        if ($token === $validToken) {
            $valid_link = true;
        } else {
            $message = "❌ Invalid reset link.";
            $message_type = "danger";
        }
    }
}


if (isset($_POST['reset_submit'])) {

    $email = $_POST['email'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($new_password) || empty($confirm_password)) {
        $message = "Please fill in all fields.";
        $message_type = "danger";
    } elseif ($new_password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = "danger";
    } else {
        $update = mysqli_query($conn, "UPDATE user SET password = '$new_password' WHERE email = '$email'");
        if ($update) {
            $get_user = mysqli_query($conn, "SELECT * FROM user WHERE email = '$email'");
            $data = mysqli_fetch_array($get_user);

            $_SESSION['username'] = $data['name'];
            $_SESSION['profile_picture'] = !empty($data['profile_picture']) ? $data['profile_picture'] : 'default.png';

            $message = "Password updated successfully! Redirecting to dashboard...";
            $message_type = "success";
            header("refresh:2;url=../index.php");
        } else {
            $message = "Error updating password. Please try again.";
            $message_type = "danger";
        }
    }
    $valid_link = true;
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>AdminLTE 4 | Reset Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="../css/adminlte.css" />
</head>

<body class="login-page bg-body-secondary">
    <div class="login-box">
        <div class="login-logo">
            <a href="../index.php"><b>Admin</b>LTE</a>
        </div>
        <div class="card shadow-lg border-0">
            <div class="card-body login-card-body rounded">
                <h4 class="login-box-msg fw-bold text-primary">Reset Your Password</h4>
                <p class="text-center text-muted small mb-4">Enter your new password below for <b><?php echo htmlspecialchars($email); ?></b></p>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($valid_link): ?>
                    <form action="forgotpass.php" method="post">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        <input type="hidden" name="ts" value="<?php echo htmlspecialchars($timestamp); ?>">

                        <div class="mb-3">
                            <label class="form-label small fw-bold">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" placeholder="New Password" name="new_password" required />
                                <div class="input-group-text"><span class="bi bi-lock-fill"></span></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Confirm New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" placeholder="Re-Enter Password" name="confirm_password" required />
                                <div class="input-group-text"><span class="bi bi-shield-lock-fill"></span></div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="reset_submit" class="btn btn-primary fw-bold py-2">
                                <i class="bi bi-check-circle me-2"></i> Update Password
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning">
                        Invalid reset link. Please request a new one from the login page.
                    </div>
                    <div class="text-center">
                        <a href="login.php" class="btn btn-link">Back to Login</a>
                    </div>
                <?php endif; ?>

                <div class="mt-4 text-center">
                    <p class="mb-0 small">
                        <a href="login.php" class="text-decoration-none">Back to Login</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="../js/adminlte.js"></script>
</body>

</html>