<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "user") {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id='$user_id'")->fetch_assoc();

if (isset($_GET['read'])) {
    $id = $_GET['read'];

    $result = $conn->query("
    SELECT link 
    FROM notifications 
    WHERE id='$id' AND user_id='$user_id'
");

if ($result && $result->num_rows > 0) {
    $n = $result->fetch_assoc();
} else {
    $n = null;
}

    $conn->query("
    UPDATE notifications 
    SET status='read' 
    WHERE id='$id' AND user_id='$user_id'
");

if ($n && !empty($n['link'])) {
    header("Location: " . $n['link']);
    exit();
}

    header("Location: user_notifications.php");
    exit();
}

$system_notifications = $conn->query("
    SELECT * FROM notifications
    WHERE user_id='$user_id' AND role_target='user'
    ORDER BY created_at DESC
");

if (!$system_notifications) {
    die("Error in notifications query: " . $conn->error);
}

$announcements = $conn->query("
    SELECT * FROM announcements
    WHERE target_type='everyone'
    OR (target_type='barangay' AND barangay='{$user['barangay']}')
    ORDER BY created_at DESC
");

if (!$announcements) {
    die("Error in announcements query: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>H.O.H Notifications</title>
    <link rel="stylesheet" href="styles/user_design.css">
</head>
<body id="mainBody">

    <div class="main-content">
        <div class="header-row">
            <h1>NOTIFICATIONS & ANNOUNCEMENTS</h1>
        </div>

        <!-- SYSTEM NOTIFICATIONS -->
        <h2>System Updates</h2>
        <div class="glass-panel">
            <div class="table-area">
                <?php if($system_notifications->num_rows > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th style="text-align:right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($n = $system_notifications->fetch_assoc()): 
                            $isUnread = ($n['status'] == 'unread');
                        ?>
                        <tr class="<?php echo $isUnread ? 'unread-row' : ''; ?>">
                            <td><span class="type-badge"><?php echo strtoupper($n['type']); ?></span></td>
                            <td><strong><?php echo $n['title']; ?></strong></td>
                            <td style="font-size: 0.8rem; opacity: 0.9;"><?php echo $n['message']; ?></td>
                            <td style="font-size: 0.75rem; opacity: 0.6;"><?php echo date('M d, g:i A', strtotime($n['created_at'])); ?></td>
                            <td style="text-align:right">
                                <a href="?read=<?php echo $n['id']; ?>">
                                    <button class="open-btn">
                                        <?php echo $isUnread ? "MARK AS READ" : "VIEW"; ?>
                                    </button>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p style="padding: 20px; opacity: 0.5; text-align: center;">No system notifications yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- ANNOUNCEMENTS -->
        <h2>Announcements</h2>
        <div class="glass-panel">
            <div class="table-area">
                <?php if($announcements->num_rows > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Message</th>
                            <th>Target</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($a = $announcements->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo $a['title']; ?></strong></td>
                            <td style="font-size: 0.8rem; opacity: 0.9;"><?php echo $a['message']; ?></td>
                            <td><span class="type-badge"><?php echo ($a['target_type'] == "everyone") ? "ALL" : $a['barangay']; ?></span></td>
                            <td style="font-size: 0.75rem; opacity: 0.6;"><?php echo date('M d, Y', strtotime($a['announcement_date'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p style="padding: 20px; opacity: 0.5; text-align: center;">No announcements yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SIDEBAR RIGHT -->
    <div class="sidebar-right">
        <img src="assets/logo_name.png" class="side-logo">
        <div class="agent-info">
            <h3><?php echo strtoupper($user['first_name'] . " " . $user['last_name']); ?></h3>
            <p>CONSUMER ACCOUNT</p>
        </div>
        <nav class="nav-menu">
            <a href="user_dashboard.php" class="nav-item">DASHBOARD</a>
            <a href="user_notifications.php" class="nav-item active">NOTIFICATIONS</a>
            <a href="user_billing.php" class="nav-item">BILLING</a>
            <a href="user_payments.php" class="nav-item">PAYMENTS</a>
            <a href="user_history.php" class="nav-item">HISTORY</a>
            <a href="profile.php" class="nav-item">PROFILE</a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn-container">LOG OUT</a>
        </div>
    </div>

    <script>window.onload = () => { document.body.style.opacity = "1"; };</script>
</body>
</html>
