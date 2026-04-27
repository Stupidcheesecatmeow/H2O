<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "user") {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* USER INFO */
$user = $conn->query("SELECT * FROM users WHERE id='$user_id'")->fetch_assoc();

/* CURRENT BILL */
$current_bill = $conn->query("
SELECT * FROM invoices 
WHERE user_id='$user_id' AND status!='paid'
ORDER BY due_date ASC LIMIT 1
")->fetch_assoc();

/* OVERDUE */
$overdue_count = $conn->query("
SELECT COUNT(*) as total 
FROM invoices 
WHERE user_id='$user_id' 
AND status!='paid' 
AND due_date < CURDATE()
")->fetch_assoc()['total'];

$account_status = ($overdue_count > 0) ? "Overdue" : "Active";

/* LATEST PAYMENT */
$latest_payment = $conn->query("
SELECT * FROM payments
WHERE user_id='$user_id'
ORDER BY paid_at DESC
LIMIT 1
")->fetch_assoc();

/* ANNOUNCEMENTS */
$announcements = $conn->query("
SELECT * FROM announcements
WHERE target_type='everyone'
OR (target_type='barangay' AND barangay='{$user['barangay']}')
ORDER BY created_at DESC
LIMIT 3
");

/* LATEST NOTIFICATIONS */
$notifications = $conn->query("
SELECT * FROM notifications
WHERE user_id='$user_id'
ORDER BY created_at DESC
LIMIT 3
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

    <div class="layout">

        <div class="sidebar">
            <h2><?php echo $user['first_name']; ?></h2>
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
        </div>

        <div class="main">

            <h1>Dashboard</h1>

            <!-- CARDS -->
            <div class="cards">

                <div class="card">
                    <h3>Current Bill</h3>
                    <p>₱<?php echo number_format($current_bill['amount'] ?? 0, 2); ?></p>
                    <small>Due: <?php echo $current_bill['due_date'] ?? "No bill"; ?></small>
                </div>

                <div class="card">
                    <h3>Status</h3>
                    <p><?php echo $account_status; ?></p>
                </div>

                <div class="card">
                    <h3>Meter</h3>
                    <p><?php echo $user['meter_number']; ?></p>
                </div>

            </div>

            <!-- NOTIFICATIONS -->
            <h2>Recent Notifications</h2>

            <?php if($notifications && $notifications->num_rows > 0): ?>
            <?php while($n = $notifications->fetch_assoc()): ?>
                <div class="card">
                    <strong><?php echo $n['title']; ?></strong><br>
                    <?php echo $n['message']; ?><br>
                    <small><?php echo $n['created_at']; ?></small>
                </div>
            <?php endwhile; ?>
            <?php else: ?>
            <p>No notifications</p>
            <?php endif; ?>

            <!-- ANNOUNCEMENTS -->
            <h2>Announcements</h2>

            <?php if($announcements && $announcements->num_rows > 0): ?>
            <?php while($a = $announcements->fetch_assoc()): ?>
                <div class="card">
                    <strong><?php echo $a['title']; ?></strong><br>
                    <?php echo $a['message']; ?><br>
                    <small><?php echo $a['announcement_date']; ?></small>
                </div>
            <?php endwhile; ?>
            <?php else: ?>
            <p>No announcements</p>
            <?php endif; ?>

            <!-- PAYMENT STATUS -->
            <h2>Latest Payment</h2>

            <?php if($latest_payment): ?>
            <div class="card">
                Transaction: <?php echo $latest_payment['transaction_no']; ?><br>
                Amount: ₱<?php echo number_format($latest_payment['amount'],2); ?><br>
                Status: <?php echo $latest_payment['status']; ?>
            </div>
            <?php else: ?>
            <p>No payment yet</p>
            <?php endif; ?>

        </div>
    </div>

</body>
</html>