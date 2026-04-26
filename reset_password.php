<?php
session_start();
include "db.php";

if(!isset($_SESSION['reset_email'])){
    header("Location: forgot_password.php");
    exit();
}

$email = $_SESSION['reset_email'];

if(isset($_POST['reset'])){

    $new = $_POST['new'];
    $confirm = $_POST['confirm'];

    if($new != $confirm){
        echo "<script>alert('Password mismatch');</script>";
    } else {

        $conn->query("UPDATE users SET password='$new' WHERE email='$email'");

        unset($_SESSION['reset_email']);

        echo "<script>
        alert('Password updated');
        window.location='index.php';
        </script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

<div class="left">
<img src="assets/spongy.png" class="logo">

<form method="POST">

<h2>Reset Password</h2>

<p><?php echo $email; ?></p>

<input type="password" name="new" placeholder="New Password" required>
<input type="password" name="confirm" placeholder="Confirm Password" required>

<button name="reset">Reset Password</button>

</form>
</div>

<div class="right">
<div class="overlay">
<h2>Almost done</h2>
</div>
</div>

</div>

</body>
</html>