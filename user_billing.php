<?php
session_start();
include "db.php";

if ($_SESSION['role'] != "user") {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id='$user_id'")->fetch_assoc();

$bills = $conn->query("
SELECT i.*, mr.previous_reading, mr.current_reading
FROM invoices i
JOIN meter_readings mr ON i.reading_id = mr.id
WHERE i.user_id='$user_id'
ORDER BY i.created_at DESC
");
?>

<link rel="stylesheet" href="dashboard.css">

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

        <h1>Billing</h1>

        <table>
            <tr>
                <th>Invoice</th>
                <th>Reading</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Print</th>
            </tr>

            <?php while($b = $bills->fetch_assoc()): ?>
            <tr>
            <td><?php echo $b['invoice_no']; ?></td>
            <td><?php echo $b['previous_reading']." → ".$b['current_reading']; ?></td>
            <td>₱<?php echo number_format($b['amount'],2); ?></td>
            <td><?php echo $b['status']; ?></td>
            <td>
            <a href="print_invoice.php?id=<?php echo $b['id']; ?>" target="_blank">
            <button>Print</button>
            </a>
            </td>
            </tr>
            <?php endwhile; ?>

        </table>

    </div>
</div>