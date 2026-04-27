<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "user") {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id='$user_id'")->fetch_assoc();

/* SUBMIT COMPLAINT */
if (isset($_POST['submit_complaint'])) {

    $type = $_POST['complaint_type'] ?? '';
    $desc = $_POST['description'] ?? '';

    if ($type == "" || $desc == "") {
        echo "<script>alert('Please complete the complaint form'); window.location='user_complaints.php';</script>";
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO complaints 
(user_id, subject, message, status)
VALUES (?, ?, ?, 'open')");

$stmt->bind_param("iss", $user_id, $type, $desc);
$stmt->execute();


    /* NOTIFY ADMIN */
    $admin_title = "New Complaint Submitted";
    $admin_msg = "Complaint Type: $type | Description: $desc";

    $adminNotif = $conn->prepare("INSERT INTO notifications 
    (user_id, role_target, title, message, type, status, link)
    VALUES (?, 'admin', ?, ?, 'complaint', 'unread', 'complaints_admin.php')");

    $adminNotif->bind_param("iss", $user_id, $admin_title, $admin_msg);
    $adminNotif->execute();

    /* NOTIFY USER */
    $user_title = "Complaint Successfully Sent";
    $user_msg = "Your complaint about '$type' was submitted successfully. You can review its status anytime.";

    $userNotif = $conn->prepare("INSERT INTO notifications 
    (user_id, role_target, title, message, type, status, link)
    VALUES (?, 'user', ?, ?, 'complaint', 'unread', 'user_complaints.php')");

    $userNotif->bind_param("iss", $user_id, $user_title, $user_msg);
    $userNotif->execute();

    echo "<script>alert('Complaint submitted'); window.location='user_complaints.php';</script>";
    exit();
}

/* GET USER COMPLAINTS */
$complaints = $conn->query("
    SELECT * FROM complaints 
    WHERE user_id='$user_id'
    ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>H.O.H Complaints</title>
    <link rel="stylesheet" href="styles/user_complaints.css">
</head>
<body id="mainBody">

    <div class="main-content">
        <div class="header-row">
            <h1>CUSTOMER COMPLAINTS</h1>
        </div>

        <!-- NEW COMPLAINT FORM -->
        <div class="glass-panel">
            <div class="panel-title-bar">Submit a New Concern</div>
            <div class="content-area">
                <form method="POST" action="user_complaints.php">
                    <label>Complaint Category</label>
                    <select name="complaint_type" required>
                        <option value="">-- Choose Category --</option>
                        <option value="Billing Concern">Billing Concern</option>
                        <option value="Water Interruption">Water Interruption</option>
                        <option value="Meter Problem">Meter Problem</option>
                        <option value="Payment Concern">Payment Concern</option>
                        <option value="Others">Others</option>
                    </select>

                    <label>Description of Issue</label>
                    <textarea name="description" placeholder="Provide as much detail as possible to help us assist you..." required></textarea>

                    <button type="submit" name="submit_complaint" class="submit-btn">SUBMIT COMPLAINT</button>
                </form>
            </div>
        </div>

        <!-- COMPLAINT HISTORY -->
        <div class="glass-panel">
            <div class="panel-title-bar">Your Complaint History</div>
            <div class="table-area">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Admin Reply</th>
                            <th style="text-align:center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($c = $complaints->fetch_assoc()): 
                            $status = $c['status'] ?? 'open';
                        ?>
                        <tr>
                            <td><strong><?php echo $c['complaint_type'] ?? $c['subject'] ?></strong></td>
                            <td><small style="opacity:0.8;"><?php echo $c['description'] ?? $c['message'] ?></small></td>
                            <td>
                                <i style="color: var(--accent-blue);">
                                    <?php echo $c['reply'] ?? "Waiting for response..."; ?>
                                </i>
                            </td>
                            <td style="text-align:center">
                                <span class="status-pill <?php echo $status; ?>">
                                    <?php echo strtoupper(str_replace('_', ' ', $status)); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SIDEBAR -->
    <div class="sidebar-right">
        <img src="assets/logo_name.png" class="side-logo">
        <div class="agent-info">
            <h3><?php echo strtoupper($user['first_name'] . " " . $user['last_name']); ?></h3>
            <p>CONSUMER ACCOUNT</p>
        </div>
        
        <nav class="nav-menu">
            <a href="user_dashboard.php" class="nav-item">DASHBOARD</a>
            <a href="user_notifications.php" class="nav-item">NOTIFICATIONS</a>
            <a href="user_billing.php" class="nav-item">BILLING</a>
            <a href="user_payments.php" class="nav-item">PAYMENTS</a>
            <a href="user_history.php" class="nav-item">HISTORY</a>
            <a href="user_complaints.php" class="nav-item active">COMPLAINTS</a>
            <a href="profile.php" class="nav-item">PROFILE</a>
        </nav>

        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn-container">LOG OUT</a>
        </div>
    </div>

    <script>
        window.onload = () => { document.body.classList.add('fade-in'); };
    </script>
</body>
</html>
