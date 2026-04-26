<?php
session_start();
include "db.php";

if(isset($_POST['check'])){

    $email = $_POST['email'];

    $check = $conn->query("SELECT * FROM users WHERE email='$email'");

    if($check->num_rows > 0){
        $_SESSION['reset_email'] = $email;
        header("Location: reset_password.php");
        exit();
    } else {
        echo "<script>alert('Email not found');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

<div class="left">
<img src="assets/spongy.png" class="logo">

<form method="POST">

<h2>Forgot Password</h2>

<input type="email" name="email" placeholder="Enter your email" required>

<button name="check">Continue</button>

<p><a href="index.php">Back to Login</a></p>

</form>
</div>

<div class="right">
<div class="overlay">
<h2>Reset your account</h2>
</div>
</div>

</div>

</body>
</html>