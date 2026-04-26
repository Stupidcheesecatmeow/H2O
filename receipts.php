<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "accountant") {
    header("Location: index.php");
    exit();
}

$receipts = $conn->query("
    SELECT 
        r.*, 
        p.id AS payment_id,
        p.amount,
        p.payment_method,
        p.reference_no,
        p.paid_at,
        i.id AS invoice_id,
        i.invoice_no,
        u.first_name,
        u.last_name
    FROM receipts r
    JOIN payments p ON r.payment_id = p.id
    JOIN invoices i ON p.invoice_id = i.id
    JOIN users u ON p.user_id = u.id
    ORDER BY r.issued_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Receipts</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<div class="layout">

<div class="sidebar">
<h2>Accountant</h2>
<ul>
    <li><a href="accountant_dashboard.php">Dashboard</a></li>
    <li><a href="payments.php">Payments</a></li>
    <li><a href="receipts.php">Receipts</a></li>
    <li><a href="reports_accountant.php">Reports</a></li>
    <li><a href="balance.php">Balance Tracker</a></li>
    <li><a href="profile.php">Profile</a></li>
    <li><a href="logout.php">Logout</a></li>
</ul>
</div>

<div class="main">

<h1>Receipt Generator / Receipt History</h1>

<table>
<tr>
    <th>Receipt No.</th>
    <th>Invoice No.</th>
    <th>Customer</th>
    <th>Amount</th>
    <th>MOP</th>
    <th>Reference No.</th>
    <th>Issued At</th>
    <th>Print Receipt</th>
    <th>Invoice</th>
</tr>

<?php while($r = $receipts->fetch_assoc()): ?>
<tr>
    <td><?php echo $r['receipt_no']; ?></td>
    <td><?php echo $r['invoice_no']; ?></td>
    <td><?php echo $r['first_name']." ".$r['last_name']; ?></td>
    <td>₱<?php echo number_format($r['amount'], 2); ?></td>
    <td><?php echo $r['payment_method']; ?></td>
    <td><?php echo $r['reference_no']; ?></td>
    <td><?php echo $r['issued_at']; ?></td>
    <td>
        <a href="print_receipt.php?id=<?php echo $r['id']; ?>" target="_blank">
            <button type="button">Print Receipt</button>
        </a>
    </td>
    <td>
        <a href="print_invoice.php?id=<?php echo $r['invoice_id']; ?>" target="_blank">
            <button type="button">Print Invoice</button>
        </a>
    </td>
</tr>
<?php endwhile; ?>
</table>

</div>
</div>

</body>
</html>