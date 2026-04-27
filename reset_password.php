<?php
session_start();
include "db.php";

if(!isset($_SESSION['reset_email'])){
    header("Location: forgot_password.php");
    exit();
}

if(isset($_POST['reset'])){
    $new_pass = $_POST['password'];
    $conf_pass = $_POST['confirm_password'];
    $email = $_SESSION['reset_email'];

    if($new_pass === $conf_pass){
        $update = $conn->query("UPDATE users SET password='$new_pass' WHERE email='$email'");
        
        if($update){
            unset($_SESSION['reset_email']);
            echo "<script>alert('Password updated successfully!'); window.location.href='index.php';</script>";
        }
    } else {
        echo "<script>alert('Passwords do not match.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="forgot.css">
</head>
<body>

    <div class="container">
        <div class="left">
            <img src="assets/logo_name.png" class="logo">

            <form method="POST">
                <h2>Create New Password</h2>
                <p class="subtitle">Enter your new password below.</p>

                <label>New Password</label>
                <input type="password" name="password" placeholder="Minimum 8 characters" required>

                <label>Confirm Password</label>
                <input type="password" name="confirm_password" placeholder="Repeat new password" required>

                <button name="reset" id="forgotBtn">Update Password</button>

                <div class="back-link">
                    <a href="forgot_password.php">Back</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            document.body.style.opacity = "1";
        });
    </script>

</body>
</html>