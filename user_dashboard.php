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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>H.O.H User Dashboard</title>
    <link rel="stylesheet" href="styles/user_design.css">
</head>
<body id="mainBody">

    <div class="main-content">
        <div class="header-row">
            <h1>WELCOME, <?php echo strtoupper($user['first_name']); ?>!</h1>
        </div>

        <!-- STATS -->
        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 25px;">
            <div class="stat-card">
                <span class="stat-label">Current Bill</span>
                <span class="stat-value">₱<?php echo number_format($current_bill['amount'] ?? 0, 2); ?></span>
                <small style="font-size: 0.6rem; opacity: 0.7;">Due: <?php echo $current_bill['due_date'] ?? "N/A"; ?></small>
            </div>

            <div class="stat-card">
                <span class="stat-label">Account Status</span>
                <span class="stat-value" style="color: <?php echo ($account_status == 'Active') ? '#2ecc71' : '#e74c3c'; ?>;">
                    <?php echo strtoupper($account_status); ?>
                </span>
            </div>

            <div class="stat-card">
                <span class="stat-label">Meter Number</span>
                <span class="stat-value"><?php echo $user['meter_number']; ?></span>
            </div>
        </div>

        <!-- ANNOUNCEMENTS -->
        <div class="glass-panel">
            <div class="panel-title-bar">Announcements</div>
            <div class="table-area" style="padding: 20px;">
                <?php if($announcements && $announcements->num_rows > 0): ?>
                    <?php while($a = $announcements->fetch_assoc()): ?>
                    <div style="background: rgba(255,255,255,0.05); padding: 15px; border-radius: 8px; margin-bottom: 10px; border-left: 4px solid var(--accent-blue);">
                        <strong style="color: var(--accent-blue);"><?php echo $a['title']; ?></strong>
                        <p style="font-size: 0.85rem; margin: 5px 0;"><?php echo $a['message']; ?></p>
                        <small style="opacity: 0.5; font-size: 0.7rem;"><?php echo date('M d, Y', strtotime($a['created_at'])); ?></small>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; opacity: 0.5;">No current announcements.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- NOTIFS AND PAYMENTS -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <!-- NOTIFICATIONS -->
            <div class="glass-panel">
                <div class="panel-title-bar">Recent Notifications</div>
                <div class="table-area" style="padding: 15px;">
                    <?php if($notifications && $notifications->num_rows > 0): ?>
                        <?php while($n = $notifications->fetch_assoc()): ?>
                        <div style="border-bottom: 1px solid rgba(255,255,255,0.1); padding: 10px 0;">
                            <div style="font-size: 0.85rem; font-weight: bold;"><?php echo $n['title']; ?></div>
                            <div style="font-size: 0.75rem; opacity: 0.8;"><?php echo $n['message']; ?></div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="opacity: 0.5; font-size: 0.8rem;">No new notifications.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- LATEST PAYMENT -->
            <div class="glass-panel">
                <div class="panel-title-bar">Latest Payment</div>
                <div class="table-area" style="padding: 15px;">
                    <?php if($latest_payment): ?>
                        <div style="text-align: center; padding: 10px;">
                            <div style="font-size: 0.7rem; opacity: 0.6;">TRANSACTION NO</div>
                            <div style="font-weight: bold; margin-bottom: 10px;"><?php echo $latest_payment['transaction_no']; ?></div>
                            <div style="font-size: 1.5rem; color: #2ecc71; font-weight: 800;">₱<?php echo number_format($latest_payment['amount'],2); ?></div>
                            <div style="font-size: 0.7rem; margin-top: 5px; background: rgba(46, 204, 113, 0.2); display: inline-block; padding: 2px 10px; border-radius: 10px;"><?php echo strtoupper($latest_payment['status']); ?></div>
                        </div>
                    <?php else: ?>
                        <p style="opacity: 0.5; font-size: 0.8rem;">No payment records found.</p>
                    <?php endif; ?>
                </div>
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
            <a href="user_dashboard.php" class="nav-item  active">DASHBOARD</a>
            <a href="user_notifications.php" class="nav-item">NOTIFICATIONS</a>
            <a href="user_billing.php" class="nav-item">BILLING</a>
            <a href="user_payments.php" class="nav-item">PAYMENTS</a>
            <a href="user_history.php" class="nav-item">HISTORY</a>
            <a href="profile.php" class="nav-item">PROFILE</a>
        </nav>

        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn-container">LOG OUT</a>
        </div>
    </div>

    <script>
        window.onload = () => { document.body.style.opacity = "1"; };
    </script>
</body>
</html>
