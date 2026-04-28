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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>H.O.H Profile Settings</title>
    <link rel="stylesheet" href="styles/profile.css">
    <link rel="stylesheet" href="styles/user_design.css">
</head>
<body id="mainBody">

    <div class="main-content">
        <div class="header-row">
            <h1 class="dash-title"><?php echo ucfirst($role); ?> DASHBOARD</h1>
        </div>

        <!-- SHARED PROFILE CONTAINER (Matches your image) -->
        <div class="profile-container-box">
            
            <!-- TOP SECTION: Avatar & Name -->
            <div class="profile-header-section">
                <div class="profile-flex">
                    <img src="uploads/<?php echo $avatar; ?>" class="avatar-large" alt="Avatar">
                    <div class="profile-text">
                        <h2 class="user-name-title"><?php echo strtoupper($user['first_name'] . " " . $user['last_name']); ?></h2>
                        <p class="user-role-label"><?php echo strtoupper($role); ?></p>
                        
                        <div class="profile-actions-row">
                            <button type="button" onclick="toggleForm('avatarForm')">Change Avatar</button>
                            <button type="button" onclick="toggleForm('editForm')">Edit Profile</button>
                            <button type="button" onclick="toggleForm('passwordForm')">Security</button>
                        </div>
                    </div>
                </div>
            </div>
            

            <!-- THE HEADER BAR (From your image) -->
            <div class="account-info-bar">ACCOUNT INFORMATION</div>

            <!-- BOTTOM SECTION: Content area -->
            <div class="account-details-content">
                <div class="info-grid">
                    <p><strong><?php echo ($role == "user") ? "User ID" : "Employee ID"; ?></strong><br><?php echo $display_id; ?></p>
                    <p><strong>Full Name</strong><br><?php echo $user['first_name']." ".$user['last_name']; ?></p>
                    <p><strong>Email Address</strong><br><?php echo $user['email']; ?></p>
                    <p><strong>Contact No.</strong><br><?php echo $user['contact_no']; ?></p>
                    <?php if($role == "user"): ?>
                        <p><strong>Address</strong><br><?php echo $user['barangay']." / ".$user['street']; ?></p>
                        <p><strong>Meter Number</strong><br><?php echo $user['meter_number']; ?></p>
                    <?php endif; ?>
                    <p><strong>Status</strong><br><span style="color: #2ecc71; font-weight:bold;"><?php echo strtoupper($user['status']); ?></span></p>
                </div>
            </div>
        </div>

        <!-- TOGGLEABLE FORMS (Stacked below) -->
        <!-- AVATAR FORM -->
        <div id="avatarForm" class="glass-panel hidden-form">
            <div class="panel-title-bar">Update Avatar</div>
            <div class="table-area" style="padding: 30px;">
                <form method="POST" enctype="multipart/form-data">
                    <p style="font-size: 0.75rem; color: #bdc3c7; margin-bottom: 10px; font-weight: bold; text-transform: uppercase;">Choose Image File</p>
                    
                    <input type="file" name="avatar" id="avatarInput" accept="image/*" required 
                           style="margin-bottom:20px; background: #f8f9fa; color: #333; padding: 12px; border-radius: 8px; width: 100%; border: 1px solid #ccc;">
                    <button type="submit" name="upload_avatar" class="print-btn" style="width:100%; padding: 15px; font-weight: 800;">UPLOAD PHOTO</button>
                </form>
            </div>
        </div>

        <!-- EDIT PROFILE FORM -->
<div id="editForm" class="glass-panel hidden-form">
    <div class="panel-title-bar">Edit Profile Details</div>
    <div class="table-area" style="padding: 40px;">
        <form method="POST" class="inline-form-grid">
            
            <?php if($role == "user"): ?>
            <!-- Row 1: First Name & Last Name -->
            <div class="form-row">
                <div class="input-group">
                    <label>FIRST NAME:</label>
                    <input type="text" name="first_name" value="<?php echo $user['first_name']; ?>" required>
                </div>
                <div class="input-group">
                    <label>LAST NAME:</label>
                    <input type="text" name="last_name" value="<?php echo $user['last_name']; ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="input-group">
                    <label>BARANGAY:</label>
                    <input type="text" name="barangay" value="<?php echo $user['barangay']; ?>" required>
                </div>
                <div class="input-group">
                    <label>STREET:</label>
                    <input type="text" name="street" value="<?php echo $user['street']; ?>" required>
                </div>
            </div>

            <!-- Row 2: Email & Contact -->
            <div class="form-row">
                <div class="input-group">
                    <label>EMAIL:</label>
                    <input type="email" name="email" value="<?php echo $user['email']; ?>" required>
                </div>
                <div class="input-group">
                    <label>CONTACT:</label>
                    <input type="text" name="contact_no" value="<?php echo $user['contact_no']; ?>" required>
                </div>
            </div>

            <?php else: ?>

                <div class="form-row">
                <div class="input-group">
                    <label>FIRST NAME:</label>
                    <input type="text" name="first_name" value="<?php echo $user['first_name']; ?>" required>
                </div>
                <div class="input-group">
                    <label>LAST NAME:</label>
                    <input type="text" name="last_name" value="<?php echo $user['last_name']; ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="input-group">
                    <label>EMAIL:</label>
                    <input type="email" name="email" value="<?php echo $user['email']; ?>" required>
                </div>
                <div class="input-group">
                    <label>CONTACT:</label>
                    <input type="text" name="contact_no" value="<?php echo $user['contact_no']; ?>" required>
                </div>
            </div>

            <?php endif; ?>

            <button type="submit" name="update_profile" class="save-btn">SAVE CHANGES</button>
            
        </form>
    </div>
</div>



        <!-- SECURITY FORM -->
<div id="passwordForm" class="glass-panel hidden-form">
    <div class="panel-title-bar">Security Settings</div>
    <div class="table-area" style="padding: 40px;">
        <form method="POST" class="inline-form-grid">
            
            <!-- Row 1: Current Password -->
            <div class="form-row">
                <div class="input-group">
                    <label>CURRENT PASSWORD:</label>
                    <input type="password" name="current" placeholder="Enter Current Password" required>
                </div>
            </div>

            <!-- Row 2: New Password & Confirm (Side by Side) -->
            <div class="form-row">
                <div class="input-group">
                    <label>NEW PASSWORD:</label>
                    <input type="password" name="new" placeholder="Enter New Password" required>
                </div>
                <div class="input-group">
                    <label>CONFIRM:</label>
                    <input type="password" name="confirm" placeholder="Confirm New Password" required>
                </div>
            </div>

            <button type="submit" name="update_password" class="save-btn">UPDATE PASSWORD</button>
            
        </form>
    </div>
</div>

    </div>

    <!-- SIDEBAR RIGHT -->
    <div class="sidebar-right">
        <img src="assets/logo_name.png" class="side-logo">
        <div class="agent-info">
            <h3><?php echo strtoupper($user['first_name']); ?></h3>
            <p><?php echo strtoupper($role); ?></p>
        </div>
        <nav class="nav-menu">
            <?php if($role == "admin"): ?>
                <a href="admin_dashboard.php" class="nav-item">DASHBOARD</a>
                <a href="announcements.php" class="nav-item">ANNOUNCEMENTS</a>
                <a href="agent_management.php" class="nav-item">USER MANAGEMENT</a>
                <a href="invoices.php" class="nav-item">INVOICES</a>
                <a href="transactions.php" class="nav-item">TRANSACTIONS</a>
                <a href="complaints_admin.php" class="nav-item">COMPLAINTS</a>
                <a href="reports.php" class="nav-item">REPORTS</a>

            <?php elseif($role == "accountant"): ?>
                <a href="accountant_dashboard.php" class="nav-item">DASHBOARD</a>
                <a href="payments.php" class="nav-item">PAYMENTS</a>
                <a href="receipts.php" class="nav-item">RECEIPTS</a>
                <a href="reports_accountant.php" class="nav-item">REPORTS</a>
                <a href="balance.php" class="nav-item">BALANCE TRACKER</a>

            <?php elseif($role == "agent"): ?>
                <a href="agent_dashboard.php" class="nav-item">DASHBOARD</a>
                <a href="meter_reading.php" class="nav-item">METER READINGS</a>
                
            <?php elseif($role == "user"): ?>
                <a href="user_dashboard.php" class="nav-item">DASHBOARD</a>
                <a href="user_notifications.php" class="nav-item">NOTIFICATIONS</a>
                <a href="user_billing.php" class="nav-item">BILLING</a>
                <a href="user_payments.php" class="nav-item">PAYMENT</a>
                <a href="user_history.php" class="nav-item">HISTORY</a>
           
            <?php endif; ?>
            <a href="profile.php" class="nav-item active">PROFILE</a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn-container">LOG OUT</a>
        </div>
    </div>

    <script>
        // Trigger Page Fade In
        window.onload = () => { document.body.style.opacity = "1"; };

        // Toggle Form Display
        function toggleForm(formId) {
            // Hide all forms first
            document.querySelectorAll('.hidden-form').forEach(f => f.style.display = 'none');
            
            // Show the selected one
            const target = document.getElementById(formId);
            target.style.display = 'block';
            
            // Smooth scroll to the form
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        document.getElementById('avatarInput').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewAvatar').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        function toggleForm(id) {
    // 1. Hide all toggleable forms
    document.querySelectorAll('.hidden-form').forEach(f => f.style.display = 'none');
    
    // 2. Hide the main account info details to save space
    const infoContent = document.querySelector('.account-details-content');
    infoContent.style.display = 'none';

    // 3. Show the selected form
    const target = document.getElementById(id);
    target.style.display = 'block';
    
    // 4. Smoothly scroll to the form
    target.scrollIntoView({ behavior: 'smooth' });
}

    </script>
</body>
</html>