<?php
session_start();
include "db.php";
include "barangay.php";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

/* UPLOAD AVATAR */
if(isset($_POST['upload_avatar'])){

    if(isset($_FILES['avatar']) && $_FILES['avatar']['name'] != ""){

        if(!is_dir("uploads")){
            mkdir("uploads", 0777, true);
        }

        $file = $_FILES['avatar']['tmp_name'];
        $filename = "avatar_" . $user_id . "_" . time() . ".jpg";
        $target = "uploads/" . $filename;

        $imageInfo = getimagesize($file);

        if($imageInfo){
            $width = $imageInfo[0];
            $height = $imageInfo[1];

            $new_width = 300;
            $new_height = 300;

            $src = imagecreatefromstring(file_get_contents($file));
            $tmp = imagecreatetruecolor($new_width, $new_height);

            $min = min($width, $height);
            $src_x = ($width - $min) / 2;
            $src_y = ($height - $min) / 2;

            imagecopyresampled(
                $tmp,
                $src,
                0,
                0,
                $src_x,
                $src_y,
                $new_width,
                $new_height,
                $min,
                $min
            );

            imagejpeg($tmp, $target, 85);


            $stmt = $conn->prepare("UPDATE users SET avatar=? WHERE id=?");
            $stmt->bind_param("si", $filename, $user_id);
            $stmt->execute();

            echo "<script>alert('Avatar updated'); window.location='profile.php';</script>";
            exit();
        } else {
            echo "<script>alert('Invalid image file'); window.location='profile.php';</script>";
            exit();
        }
    }
}

/* UPDATE PROFILE */
if (isset($_POST['update_profile'])) {

    if ($role == "user") {
        $fname = $_POST['first_name'];
        $lname = $_POST['last_name'];
        $email = $_POST['email'];
        $contact = $_POST['contact_no'];
        $barangay = $_POST['barangay'];
        $street = $_POST['street'];

        $stmt = $conn->prepare("UPDATE users SET 
            first_name=?, last_name=?, email=?, contact_no=?, barangay=?, street=? 
            WHERE id=?");

        $stmt->bind_param("ssssssi", $fname, $lname, $email, $contact, $barangay, $street, $user_id);

    } else {
        $fname = $_POST['first_name'];
        $lname = $_POST['last_name'];
        $email = $_POST['email'];
        $contact = $_POST['contact_no'];

        $stmt = $conn->prepare("UPDATE users SET 
            first_name=?, last_name=?, email=?, contact_no=? 
            WHERE id=?");

        $stmt->bind_param("ssssi", $fname, $lname, $email, $contact, $user_id);
    }

    $stmt->execute();

if ($role == "user") {
    $notif_title = "User Profile Updated";
    $notif_msg = $fname . " " . $lname . " updated profile information.";

    $notif = $conn->prepare("INSERT INTO notifications 
    (user_id, role_target, title, message, type, status)
    VALUES (?, 'admin', ?, ?, 'profile', 'unread')");

    $notif->bind_param("iss", $user_id, $notif_title, $notif_msg);
    $notif->execute();
}

echo "<script>alert('Profile updated'); window.location='profile.php';</script>";
exit();
}

/* CHANGE PASSWORD */
if (isset($_POST['update_password'])) {
    $current = $_POST['current'];
    $new = $_POST['new'];
    $confirm = $_POST['confirm'];

    $userCheck = $conn->query("SELECT password FROM users WHERE id='$user_id'")->fetch_assoc();

    if ($current != $userCheck['password']) {
        echo "<script>alert('Wrong current password'); window.location='profile.php';</script>";
        exit();
    } elseif ($new != $confirm) {
        echo "<script>alert('Password mismatch'); window.location='profile.php';</script>";
        exit();
    } else {
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $new, $user_id);
        $stmt->execute();

        echo "<script>alert('Password updated'); window.location='profile.php';</script>";
        exit();
    }
}

$user = $conn->query("SELECT * FROM users WHERE id='$user_id'")->fetch_assoc();

$display_id = !empty($user['user_code']) ? $user['user_code'] : $user['id'];
$avatar = !empty($user['avatar']) ? $user['avatar'] : "default.png";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile Settings</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="profile.css">
</head>
<body>

    <div class="layout">

        <div class="sidebar">
            <h2><?php echo ucfirst($role); ?></h2>

            <?php if($role == "admin"): ?>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="announcements.php">Announcements</a></li>
                <li><a href="user_management.php">User Management</a></li>
                <li><a href="agent_management.php">Field Agents</a></li>
                <li><a href="invoices.php">Invoices</a></li>
                <li><a href="transactions.php">Transactions</a></li>
                <li><a href="complaints_admin.php">Complaints</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>

            <?php elseif($role == "accountant"): ?>
            <ul>
                <li><a href="accountant_dashboard.php">Dashboard</a></li>
                <li><a href="payments.php">Payments</a></li>
                <li><a href="receipts.php">Receipts</a></li>
                <li><a href="reports_accountant.php">Reports</a></li>
                <li><a href="balance.php">Balance Tracker</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>

            <?php elseif($role == "agent"): ?>
            <ul>
                <li><a href="agent_dashboard.php">Dashboard</a></li>
                <li><a href="customers.php">Customers</a></li>
                <li><a href="meter_reading.php">Meter Reading</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>

            <?php else: ?>
            <ul>
                <li><a href="user_dashboard.php">Dashboard</a></li>
                <li><a href="user_notifications.php">Notifications</a></li>
                <li><a href="user_billing.php">Billing</a></li>
                <li><a href="user_payments.php">Payment</a></li>
                <li><a href="user_history.php">History</a></li>
                <li><a href="user_complaints.php">Complaints</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
            <?php endif; ?>

        </div>

        <div class="main">

            <h1>Profile Settings</h1>

            <div class="profile-card">

                <img src="uploads/<?php echo $avatar; ?>" class="avatar" alt="Avatar">

                <h2><?php echo $user['first_name']." ".$user['last_name']; ?></h2>
                <p><?php echo $user['email']; ?></p>

                <p>
                    <strong><?php echo ($role == "user") ? "User ID" : "Employee ID"; ?>:</strong>
                    <?php echo $display_id; ?>
                </p>

                <span class="status"><?php echo $user['status']; ?></span>

                <div class="profile-actions">
                    <button type="button" onclick="toggleForm('avatarForm')">Change Avatar</button>
                    <button type="button" onclick="toggleForm('editForm')">Edit Profile</button>
                    <button type="button" onclick="toggleForm('passwordForm')">Change Password</button>
                </div>

            </div>

            <div class="card hidden-form" id="avatarForm">
                <h3>Change Avatar</h3>

                <form method="POST" enctype="multipart/form-data">

                    <img id="previewAvatar" src="uploads/<?php echo $avatar; ?>" class="avatar-preview" alt="Preview">

                    <input type="file" name="avatar" id="avatarInput" accept="image/*" required>

                    <button type="submit" name="upload_avatar">Upload Avatar</button>

                </form>
            </div>

            <div class="card">
                <h3>Account Information</h3>

                <div class="info-grid">

                    <p>
                        <strong><?php echo ($role == "user") ? "User ID" : "Employee ID"; ?>:</strong><br>
                        <?php echo $display_id; ?>
                    </p>

                    <p>
                        <strong>Full Name:</strong><br>
                        <?php echo $user['first_name']." ".$user['last_name']; ?>
                    </p>

                    <p>
                        <strong>Email:</strong><br>
                        <?php echo $user['email']; ?>
                    </p>

                    <p>
                        <strong>Contact:</strong><br>
                        <?php echo $user['contact_no']; ?>
                    </p>

                    <?php if($role == "user"): ?>
                        <p>
                            <strong>Address:</strong><br>
                            <?php echo $user['barangay']." / ".$user['street']; ?>
                        </p>

                        <p>
                            <strong>Meter Number:</strong><br>
                            <?php echo $user['meter_number']; ?>
                        </p>
                    <?php else: ?>
                        <p>
                            <strong>Role:</strong><br>
                            <?php echo $user['role']; ?>
                        </p>
                    <?php endif; ?>

                    <p>
                        <strong>Status:</strong><br>
                        <?php echo $user['status']; ?>
                    </p>

                </div>
            </div>

            <div class="card hidden-form" id="editForm">
                <h3>Edit Profile</h3>

                <form method="POST">

                    <?php if($role == "user"): ?>

                        <input type="text" name="first_name" placeholder="First Name" value="<?php echo $user['first_name']; ?>" required>
                        <input type="text" name="last_name" placeholder="Last Name" value="<?php echo $user['last_name']; ?>" required>
                        <input type="email" name="email" placeholder="Email" value="<?php echo $user['email']; ?>" required>
                        <input type="text" name="contact_no" placeholder="Contact No." value="<?php echo $user['contact_no']; ?>" required>
                        <input type="text" name="barangay" placeholder="Barangay" value="<?php echo $user['barangay']; ?>" required>
                        <input type="text" name="street" placeholder="Street" value="<?php echo $user['street']; ?>" required>

                    <?php else: ?>

                        <input type="text" name="first_name" placeholder="First Name" value="<?php echo $user['first_name']; ?>" required>
                        <input type="text" name="last_name" placeholder="Last Name" value="<?php echo $user['last_name']; ?>" required>
                        <input type="email" name="email" placeholder="Email" value="<?php echo $user['email']; ?>" required>
                        <input type="text" name="contact_no" placeholder="Contact No." value="<?php echo $user['contact_no']; ?>" required>

                    <?php endif; ?>

                    <button type="submit" name="update_profile">Save Changes</button>
                </form>
            </div>

            <div class="card hidden-form" id="passwordForm">
                <h3>Change Password</h3>

                <form method="POST">
                    <input type="password" name="current" placeholder="Current Password" required>
                    <input type="password" name="new" placeholder="New Password" required>
                    <input type="password" name="confirm" placeholder="Confirm Password" required>

                    <button type="submit" name="update_password">Update Password</button>
                </form>
            </div>

        </div>
    </div>

    <script>
        function toggleForm(id){
            const form = document.getElementById(id);

            if(form.style.display === "block"){
                form.style.display = "none";
            }else{
                form.style.display = "block";
            }
        }

        document.getElementById("avatarInput")?.addEventListener("change", function(e){
            const file = e.target.files[0];

            if(file){
                const reader = new FileReader();

                reader.onload = function(e){
                    document.getElementById("previewAvatar").src = e.target.result;
                }

                reader.readAsDataURL(file);
            }
        });
    </script>

</body>
</html>