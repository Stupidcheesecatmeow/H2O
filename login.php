<?php
session_start();
include "db.php";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if($result && $result->num_rows > 0){

        $user = $result->fetch_assoc();

        if($password == $user['password']){

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            if($user['role'] == "admin"){
                header("Location: admin_dashboard.php");
            } elseif($user['role'] == "accountant"){
                header("Location: accountant_dashboard.php");
            } elseif($user['role'] == "agent"){
                header("Location: agent_dashboard.php");
            } else {
                header("Location: user_dashboard.php");
            }

        } else {
            echo "<script>alert('Invalid password'); window.location='index.php';</script>";
        }

    } else {
        echo "<script>alert('Email not found'); window.location='index.php';</script>";
    }
}
?>