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
    (user_id, complaint_type, description, status)
    VALUES (?, ?, ?, 'open')");

    $stmt->bind_param("iss", $user_id, $type, $desc);
    $stmt->execute();

    $notif_title = "New Complaint Submitted";
    $notif_msg = "Complaint Type: $type | Description: $desc";

    $notif = $conn->prepare("INSERT INTO notifications 
    (user_id, role_target, title, message, type, status)
    VALUES (?, 'admin', ?, ?, 'complaint', 'unread')");

    $notif->bind_param("iss", $user_id, $notif_title, $notif_msg);
    $notif->execute();

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
<html>
<head>
    <title>Complaints</title>
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

<h1>Complaints</h1>

<div class="table-box">
<form method="POST" action="user_complaints.php">

    <label>Complaint Type</label>
    <select name="complaint_type" required>
        <option value="">Select type</option>
        <option value="Billing Concern">Billing Concern</option>
        <option value="Water Interruption">Water Interruption</option>
        <option value="Meter Problem">Meter Problem</option>
        <option value="Payment Concern">Payment Concern</option>
        <option value="Others">Others</option>
    </select>

    <label>Description</label>
    <textarea name="description" placeholder="Describe your concern..." required></textarea>

    <button type="submit" name="submit_complaint">Submit Complaint</button>

</form>
</div>

<h2>Your Complaints</h2>

<table>
<tr>
    <th>Type</th>
    <th>Description</th>
    <th>Reply</th>
    <th>Status</th>
</tr>

<?php while($c = $complaints->fetch_assoc()): ?>
<tr>
    <td><?php echo $c['complaint_type'] ?? $c['subject'] ?? "N/A"; ?></td>
    <td><?php echo $c['description'] ?? $c['message'] ?? "N/A"; ?></td>
    <td><?php echo $c['reply'] ?? "No reply yet"; ?></td>
    <td><?php echo $c['status'] ?? "open"; ?></td>
</tr>
<?php endwhile; ?>

</table>

</div>
</div>

</body>
</html>