<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: index.php");
    exit();
}

$transactions = $conn->query("
    SELECT 
        p.*,
        i.id AS invoice_id,
        i.invoice_no,
        u.first_name,
        u.last_name,
        u.meter_number
    FROM payments p
    JOIN invoices i ON p.invoice_id = i.id
    JOIN users u ON p.user_id = u.id
    ORDER BY p.paid_at DESC
");
?>

<link rel="stylesheet" href="dashboard.css">

<div class="layout">

<div class="sidebar">
<h2>Admin</h2>
<ul>
    <li><a href="admin_dashboard.php">Dashboard</a></li>
    <li><a href="announcements.php">Announcements</a></li>
    <li><a href="user_management.php">User Management</a></li>
    <li><a href="agent_management.php">Field Agents</a></li>
    <li><a href="invoices.php">Invoices</a></li>
    <li><a href="transactions.php">Transactions</a></li>
    <li><a href="complaints_admin.php">Complaints</a></li>
    <li><a href="reports.php">Reports</a></li>
    <li><a href="profile.php">Profile</a></li>
    <li><a href="logout.php">Logout</a></li>
</ul>
</div>

<div class="main">

<h1>Transaction Monitoring</h1>

<table>
<tr>
    <th>Transaction ID</th>
    <th>Invoice No.</th>
    <th>Customer Name</th>
    <th>Meter No.</th>
    <th>Amount Paid</th>
    <th>MOP</th>
    <th>Status</th>
    <th>Date</th>
    <th>Invoice</th>
</tr>

<?php while($t = $transactions->fetch_assoc()): ?>
<tr>
    <td><?php echo $t['transaction_no']; ?></td>
    <td><?php echo $t['invoice_no']; ?></td>
    <td><?php echo $t['first_name']." ".$t['last_name']; ?></td>
    <td><?php echo $t['meter_number']; ?></td>
    <td>₱<?php echo number_format($t['amount'], 2); ?></td>
    <td><?php echo $t['payment_method']; ?></td>
    <td><?php echo $t['status']; ?></td>
    <td><?php echo $t['paid_at']; ?></td>
    <td>
        <a href="print_invoice.php?id=<?php echo $t['invoice_id']; ?>" target="_blank">
            <button type="button">Print</button>
        </a>
    </td>
</tr>
<?php endwhile; ?>
</table>

</div>
</div>