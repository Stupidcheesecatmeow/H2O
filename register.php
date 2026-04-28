<?php
include "db.php";
include "barangay.php";

if(isset($_POST['register'])){

    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $email = $_POST['email'];
    $contact = $_POST['contact_no'];
    $barangay = $_POST['barangay'];
    $street = $_POST['street'];
    $meter = $_POST['meter_number'];
    $password = $_POST['password'];
    $verify = $_POST['verify_password'];

    if($password != $verify){
        echo "<script>alert('Password does not match'); window.location='register.php';</script>";
        exit();
    }

    $check = $conn->prepare("SELECT id FROM users WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if($result->num_rows > 0){
        echo "<script>alert('Email already exists'); window.location='register.php';</script>";
        exit();
    }

    $user_code = "USR-" . date("Ymd") . "-" . rand(1000,9999);

    $stmt = $conn->prepare("INSERT INTO users 
        (first_name, last_name, email, contact_no, password, role, meter_number, barangay, street, status, user_code)
        VALUES (?, ?, ?, ?, ?, 'user', ?, ?, ?, 'active', ?)");

    $stmt->bind_param("sssssssss", 
        $fname,        
        $lname,        
        $email,        
        $contact,      
        $password,     
        $meter,       
        $barangay,   
        $street,       
        $user_code     
    );

    if($stmt->execute()){
        echo "<script>
            alert('Registered Successfully! Your ID is: $user_code');
            window.location='index.php';
        </script>";
        exit();
    } else {
        echo "<script>alert('Registration failed'); window.location='register.php';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>H.O.H Sign Up</title>
    <link rel="stylesheet" href="styles/regis.css">
</head>
<body class="body_regis">
<div class="container_regis">
    <div class="left_regis">
    <img src="assets/logo_name.png" alt="logo" class="logo_regis">
    <h3 class="form-title">Registration Form</h3>

    <form action="register.php" method="POST">
        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="first_name" required>
        </div>
        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="last_name" required>
        </div>
        <div class="form-group">
            <label>Contact Number</label>
            <input type="text" name="contact_no" required>
        </div>
        <div class="form-group">
            <label>Meter Number</label>
            <input type="text" name="meter_number" required>
        </div>

        <div class="form-group">
            <label>Barangay</label>
            <select name="barangay" required>
                <option value="">Select Barangay</option>
                <?php foreach($barangays as $b): ?>
                    <option value="<?php echo htmlspecialchars($b); ?>">
                        <?php echo htmlspecialchars($b); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Street</label>
            <input type="text" name="street" required>
        </div>
        <div class="form-group full-width">
            <label>Email</label>
            <input type="email" name="email" placeholder="Enter your email" required>
        </div>
        <div class="form-group full-width">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter your password" required>
        </div>
        <div class="form-group full-width">
            <label>Confirm Password</label>
            <input type="password" name="verify_password" placeholder="Enter your password again" required>
        </div>

        <div class="login-links_regis">
            <p>Already have an account? <a href="index.php">Sign In</a></p>
        </div>
        <button type="submit" name="register" id="registerBtn">Register</button>
    </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const links = document.querySelectorAll("a");
        const body = document.querySelector("body");

        links.forEach(link => {
            link.addEventListener("click", e => {
                if (link.hostname === window.location.hostname && link.getAttribute('href') !== '#') {
                    e.preventDefault();
                    let target = link.href;
                    body.style.opacity = "0";
                    body.style.transition = "opacity 0.25s ease";
                    setTimeout(() => { window.location.href = target; }, 250); 
                }
            });
        });
    });
</script>
</body>
</html>
