<?php
session_start();
include "db.php";

if(isset($_POST['check'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $meter_no = mysqli_real_escape_string($conn, $_POST['meter_no']);
    $account_name = mysqli_real_escape_string($conn, $_POST['account_name']);

    $query = "SELECT * FROM users WHERE email='$email' AND meter_no='$meter_no' AND first_name='$account_name'";
    $check = $conn->query($query);

    if($check->num_rows > 0){
        $_SESSION['reset_email'] = $email;
        header("Location: reset_password.php");
        exit();
    } else {
        echo "<script>alert('Account details do not match our records.');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="forgot.css">
</head>
<body>

    <div class="container">

        <div class="left">
            <img src="assets/logo_name.png" class="logo">

            <form method="POST">
                <h2>Forgot Password</h2>
                <p class="subtitle">Please verify your account details to proceed.</p>

                <label>Account Name</label>
                <input type="text" name="account_name" placeholder="Enter Account Name" required>

                <label>Meter Number</label>
                <input type="text" name="meter_no" placeholder="Enter Meter Number" required>

                <label>Email</label>
                <input type="email" name="email" placeholder="Enter your registered email" required>

                <button name="check" id="forgotBtn">Continue</button>

                <div class="back-link">
                    <a href="index.php">Back to Login</a>
                </div>
            </form>
        </div>

    </div>

</body>
</html>