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
    (user_code, first_name, last_name, email, contact_no, password, role, meter_number, barangay, street, status)
    VALUES (?, ?, ?, ?, ?, ?, 'user', ?, ?, ?, 'active')");

    $stmt->bind_param("sssssssss", 
        $user_code,
        $fname, 
        $lname, 
        $email, 
        $contact, 
        $password, 
        $meter, 
        $barangay, 
        $street
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
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <div class="left">
        <img src="assets/spongy.png" alt="logo" class="logo">

        <form method="POST" class="register-form">

            <div class="form-row">
                <div class="field">
                    <label>First Name</label>
                    <input type="text" name="first_name" required>
                </div>

                <div class="field">
                    <label>Last Name</label>
                    <input type="text" name="last_name" required>
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="field">
                    <label>Contact No.</label>
                    <input type="text" name="contact_no" required>
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>Barangay</label>
                    <select name="barangay" required>
                        <option value="">Select Barangay</option>

                        <?php foreach($barangays as $b): ?>
                            <option value="<?php echo $b; ?>">
                                <?php echo $b; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label>Street</label>
                    <input type="text" name="street" required>
                </div>
            </div>

            <div class="field">
                <label>Meter Number</label>
                <input type="text" name="meter_number" required>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <div class="field">
                    <label>Verify Password</label>
                    <input type="password" name="verify_password" required>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="register">Register</button>
                <a href="index.php" class="btn-secondary">Back to Login</a>
            </div>

        </form>
    </div>

    <div class="right">
        <div class="overlay">
            <h2>Steady Flow, <br>Easy Go</h2>
        </div>
    </div>

</div>

</body>
</html>