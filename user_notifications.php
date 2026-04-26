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
<html>
<head>
    <title>User Notifications</title>
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

<h1>Notifications</h1>

<h2>System Updates</h2>

<?php if($system_notifications->num_rows > 0): ?>
<table>
<tr>
    <th>Type</th>
    <th>Title</th>
    <th>Message</th>
    <th>Status</th>
    <th>Date</th>
    <th>Action</th>
</tr>

<?php while($n = $system_notifications->fetch_assoc()): ?>
<tr style="<?php echo ($n['status']=='unread') ? 'background:#eef8fc;' : ''; ?>">
    <td>
        <?php
        if($n['type'] == "payment"){
            echo "Payment";
        } elseif($n['type'] == "bill"){
            echo "Bill";
        } elseif($n['type'] == "complaint"){
            echo "Complaint";
        } else {
            echo "Notice";
        }
        ?>
    </td>
    <td><?php echo $n['title']; ?></td>
    <td><?php echo $n['message']; ?></td>
    <td><?php echo strtoupper($n['status']); ?></td>
    <td><?php echo $n['created_at']; ?></td>
    <td>
        <a href="?read=<?php echo $n['id']; ?>">
            <button type="button">
                <?php echo ($n['status'] == "unread") ? "Open / Mark Read" : "Open"; ?>
            </button>
        </a>
    </td>
</tr>
<?php endwhile; ?>
</table>
<?php else: ?>
<p>No system notifications yet.</p>
<?php endif; ?>

<h2>Announcements</h2>

<?php if($announcements->num_rows > 0): ?>
<table>
<tr>
    <th>Title</th>
    <th>Message</th>
    <th>Barangay</th>
    <th>Date</th>
</tr>

<?php while($a = $announcements->fetch_assoc()): ?>
<tr>
    <td><?php echo $a['title']; ?></td>
    <td><?php echo $a['message']; ?></td>
    <td>
        <?php echo ($a['target_type'] == "everyone") ? "All Barangays" : $a['barangay']; ?>
    </td>
    <td><?php echo $a['announcement_date']; ?></td>
</tr>
<?php endwhile; ?>
</table>
<?php else: ?>
<p>No announcements yet.</p>
<?php endif; ?>

</div>
</div>

</body>
</html>