<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "user") {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id='$user_id'")->fetch_assoc();

/* MARK AS READ */
if (isset($_GET['read'])) {
    $id = $_GET['read'];
    $conn->query("UPDATE notifications SET status='read' WHERE id='$id'");
    header("Location: user_notifications.php");
    exit();
}

/* GET NOTIFICATIONS */
$notifications = $conn->query("
    SELECT * FROM notifications
    WHERE user_id='$user_id' OR role_target='user'
    ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Notifications</title>
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

<table>
<tr>
    <th>Title</th>
    <th>Message</th>
    <th>Type</th>
    <th>Status</th>
    <th>Date</th>
    <th>Action</th>
</tr>

<?php while($n = $notifications->fetch_assoc()): ?>
<tr style="<?php echo $n['status']=='unread' ? 'background:#eef;' : ''; ?>">
    <td><?php echo $n['title']; ?></td>
    <td><?php echo $n['message']; ?></td>
    <td><?php echo $n['type']; ?></td>
    <td><?php echo $n['status']; ?></td>
    <td><?php echo $n['created_at']; ?></td>
    <td>
        <?php if($n['status'] == "unread"): ?>
            <a href="?read=<?php echo $n['id']; ?>">
                <button>Mark as Read</button>
            </a>
        <?php else: ?>
            ✔
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>

</table>

</div>
</div>

</body>
</html>