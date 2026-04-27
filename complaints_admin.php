<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: index.php");
    exit();
}

if (isset($_POST['reply_complaint'])) {

    $complaint_id = $_POST['complaint_id'];
    $reply = $_POST['reply'];
    $status = $_POST['status'];

    /* KUHANIN USER NG COMPLAINT */
    $result = $conn->query("
        SELECT 
            user_id,
            COALESCE(subject) AS complaint_title
        FROM complaints 
        WHERE id='$complaint_id'
    ");

    if (!$result) {
        die("SQL Error: " . $conn->error);
    }

    if ($result->num_rows == 0) {
        echo "<script>alert('Complaint not found'); window.location='complaints_admin.php';</script>";
        exit();
    }

    $c = $result->fetch_assoc();

    /* UPDATE COMPLAINT */
    $stmt = $conn->prepare("UPDATE complaints 
    SET reply=?, status=?, replied_at=NOW() 
    WHERE id=?");

    $stmt->bind_param("ssi", $reply, $status, $complaint_id);
    $stmt->execute();

    /* NOTIFY USER */
    $title = "Complaint Update";
    $message = "Your complaint '{$c['complaint_title']}' has been reviewed. Status: $status. Click to view reply.";

    $notif = $conn->prepare("INSERT INTO notifications
    (user_id, role_target, title, message, type, status, link)
    VALUES (?, 'user', ?, ?, 'complaint', 'unread', 'user_complaints.php')");

    $notif->bind_param("iss", $c['user_id'], $title, $message);
    $notif->execute();

    echo "<script>alert('Complaint updated'); window.location='complaints_admin.php';</script>";
    exit();
}

$complaints = $conn->query("
    SELECT c.*, u.first_name, u.last_name
    FROM complaints c
    JOIN users u ON c.user_id = u.id
    ORDER BY c.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>H.O.H Complaint Management</title>
    <link rel="stylesheet" href="styles/complaints_admin.css">
</head>
<body id="mainBody">

    <div class="main-content">
        <div class="header-row">
            <h1>COMPLAINT MANAGEMENT</h1>
        </div>

        <div class="glass-panel">
            <div class="panel-title-bar">Customer Support Tickets</div>
            <div class="table-area">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Customer</th>
                            <th style="width: 15%;">Case/Subject</th>
                            <th style="width: 20%;">Message</th>
                            <th style="width: 15%;">Current Reply</th>
                            <th style="width: 10%;">Status</th>
                            <th style="width: 25%;">Admin Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($c = $complaints->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo strtoupper($c['first_name']." ".$c['last_name']); ?></strong></td>
                            <td><span class="case-tag"><?php echo $c['subject']; ?></span></td>
                            <td><small style="opacity: 0.8;"><?php echo $c['message']; ?></small></td>
                            <td><small style="color: var(--accent-blue);"><?php echo $c['reply'] ?: 'No reply yet'; ?></small></td>
                            <td>
                                <span class="status-pill <?php echo ($c['status'] == 'resolved') ? 'paid' : 'unpaid'; ?>">
                                    <?php echo strtoupper(str_replace('_', ' ', $c['status'])); ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" class="reply-form">
                                    <input type="hidden" name="complaint_id" value="<?php echo $c['id']; ?>">
                                    <textarea name="reply" placeholder="Type resolution or update..." required></textarea>
                                    
                                    <div class="action-row">
                                        <select name="status">
                                            <option value="open" <?php if($c['status']=='open') echo 'selected'; ?>>Open</option>
                                            <option value="in_progress" <?php if($c['status']=='in_progress') echo 'selected'; ?>>In Progress</option>
                                            <option value="resolved" <?php if($c['status']=='resolved') echo 'selected'; ?>>Resolved</option>
                                        </select>
                                        <button name="reply_complaint" class="btn-reply">SUBMIT</button>
                                    </div>
                                </form>
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
            <div class="agent-info" style="color: white; text-align: center; margin-bottom: 30px;">
                <h3>ADMIN</h3>
                <p style="font-size: 0.7rem; opacity: 0.6;">ADMIN DEPT</p>
            </div>
            <nav class="nav-menu">
                <a href="admin_dashboard.php" class="nav-item">DASHBOARD</a>
                <a href="admin_notifications.php" class="nav-item">NOTIFICATIONS</a>
                <a href="announcements.php" class="nav-item">ANNOUNCEMENTS</a>
                <a href="user_management.php" class="nav-item">USER MANAGEMENT</a>
                <a href="agent_management.php" class="nav-item">FIELD AGENTS</a>
                <a href="invoices.php" class="nav-item">INVOICES</a>
                <a href="transactions.php" class="nav-item">TRANSACTIONS</a>
                <a href="complaints_admin.php" class="nav-item  active">COMPLAINTS</a>
                <a href="reports.php" class="nav-item">REPORTS</a>
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
