<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "user") {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id='$user_id'")->fetch_assoc();

$payment_history = $conn->query("
    SELECT p.*, i.invoice_no, i.consumption
    FROM payments p
    JOIN invoices i ON p.invoice_id = i.id
    WHERE p.user_id='$user_id'
    ORDER BY p.paid_at DESC
");

$receipts = $conn->query("
    SELECT r.*, p.amount, p.payment_method, i.invoice_no
    FROM receipts r
    JOIN payments p ON r.payment_id = p.id
    JOIN invoices i ON p.invoice_id = i.id
    WHERE p.user_id='$user_id'
    ORDER BY r.issued_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>History</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<div class="layout">

<div class="sidebar">
<h2><?php echo $user['first_name']; ?></h2>
<ul>
    <li><a href="user_dashboard.php">Dashboard</a></li>
    <li><a href="user_billing.php">Billing</a></li>
    <li><a href="user_payments.php">Payment</a></li>
    <li><a href="user_history.php">History</a></li>
    <li><a href="user_complaints.php">Complaints</a></li>
    <li><a href="profile.php">Profile</a></li>
    <li><a href="logout.php">Logout</a></li>
</ul>
</div>

<div class="main">

<h1>Payment History</h1>

<table>
<tr>
    <th>Date</th>
    <th>Invoice No.</th>
    <th>Amount Paid</th>
    <th>MOP</th>
    <th>Reading</th>
    <th>Status</th>
</tr>

<?php while($ph = $payment_history->fetch_assoc()): ?>
<tr>
    <td><?php echo $ph['paid_at']; ?></td>
    <td><?php echo $ph['invoice_no']; ?></td>
    <td>₱<?php echo number_format($ph['amount'], 2); ?></td>
    <td><?php echo $ph['payment_method']; ?></td>
    <td><?php echo $ph['consumption']; ?></td>
    <td><?php echo $ph['status']; ?></td>
</tr>
<?php endwhile; ?>
</table>

<h1>Receipts</h1>

<table>
<tr>
    <th>Receipt No.</th>
    <th>Invoice No.</th>
    <th>Amount</th>
    <th>MOP</th>
    <th>Date Issued</th>
    <th>Print</th>
</tr>

<?php while($r = $receipts->fetch_assoc()): ?>
<tr>
    <td><?php echo $r['receipt_no']; ?></td>
    <td><?php echo $r['invoice_no']; ?></td>
    <td>₱<?php echo number_format($r['amount'], 2); ?></td>
    <td><?php echo $r['payment_method']; ?></td>
    <td><?php echo $r['issued_at']; ?></td>
    <td>
        <a href="print_receipt.php?id=<?php echo $r['id']; ?>" target="_blank">
            <button type="button">Print Receipt</button>
        </a>
    </td>
</tr>
<?php endwhile; ?>
</table>

</div>
</div>

</body>
</html>