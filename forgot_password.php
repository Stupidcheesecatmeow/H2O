<?php
session_start();
include "db.php";

if(isset($_POST['check'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $meter_no = mysqli_real_escape_string($conn, $_POST['meter_number']);
    $account_name = mysqli_real_escape_string($conn, $_POST['account_name']);

    $query = "SELECT * FROM users WHERE email='$email' AND meter_number='$meter_no' AND first_name='$account_name'";
    $check = $conn->query($query);

    if($check->num_rows > 0){
        $_SESSION['reset_email'] = $email;
        header("Location: reset_password.php");
        exit();
    } else {
        echo "<script>alert('Account details do not match our records.'); window.location.href='forgot_password.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>H.O.H Forgot Password</title>
    <link rel="stylesheet" href="styles/forgot.css">
</head>
<body id="mainBody">

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

                <button type="submit" name="check" id="forgotBtn">Continue</button>

                <div class="back-link">
                    <a href="index.php" id="backToLogin">Back to Login</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // 1. Smooth Fade-in
        window.addEventListener('DOMContentLoaded', () => {
            document.body.classList.add('fade-in');
        });

        // 2. Transition for "Back to Login"
        document.getElementById('backToLogin').addEventListener('click', function(e) {
            e.preventDefault(); 
            const targetUrl = this.getAttribute('href');
            document.body.classList.add('fade-out');
            setTimeout(() => {
                window.location.href = targetUrl;
            }, 400); // Matches CSS transition time
        });

        // 3. Transition for "Continue" button
        document.getElementById('forgotForm').addEventListener('submit', function() {
            const btn = document.getElementById('forgotBtn');
            
            // Add a loading state to the button
            btn.innerHTML = "Verifying...";
            btn.style.opacity = "0.7";
            btn.style.pointerEvents = "none";

            // Trigger the visual fade out while the server processes
            document.body.classList.add('fade-out');
        });
    </script>

</body>
</html>
