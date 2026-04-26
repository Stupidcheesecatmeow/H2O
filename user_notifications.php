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
    $conn->query("UPDATE notifications SET status='read' WHERE id='$id' AND user_id='$user_id'");
    header("Location: user_notifications.php");
    exit();
}

$notifications = $conn->query("
    SELECT * FROM notifications
    WHERE user_id='$user_id'
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
    <th>Type</th>
    <th>Title</th>
    <th>Message</th>
    <th>Status</th>
    <th>Date</th>
    <th>Action</th>
</tr>

<?php while($n = $notifications->fetch_assoc()): ?>
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
        <?php if($n['status'] == "unread"): ?>
            <a href="?read=<?php echo $n['id']; ?>">
                <button type="button">Mark as Read</button>
            </a>
        <?php else: ?>
            Done
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>

</table>

</div>
</div>

</body>
</html>