<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "accountant") {
    header("Location: index.php");
    exit();
}

$unpaid = $conn->query("
    SELECT 
        i.*,
        u.first_name,
        u.last_name,
        u.meter_number,
        DATEDIFF(CURDATE(), i.due_date) AS overdue_days
    FROM invoices i
    JOIN users u ON i.user_id = u.id
    WHERE i.status!='paid'
    ORDER BY i.due_date ASC
");

$total_unpaid = $conn->query("
    SELECT SUM(amount) AS total
    FROM invoices
    WHERE status!='paid'
")->fetch_assoc()['total'] ?? 0;

$total_overdue = $conn->query("
    SELECT COUNT(*) AS total
    FROM invoices
    WHERE status!='paid' AND due_date < CURDATE()
")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Balance Tracker</title>
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

            <h1>Balance Tracker</h1>

            <div class="cards">
                <div class="card">
                    Total Unpaid Balance<br>
                    <strong>₱<?php echo number_format($total_unpaid, 2); ?></strong>
                </div>

                <div class="card">
                    Overdue Accounts<br>
                    <strong><?php echo $total_overdue; ?></strong>
                </div>
            </div>

            <table>
                <tr>
                    <th>Customer</th>
                    <th>Meter No.</th>
                    <th>Invoice No.</th>
                    <th>Amount</th>
                    <th>Due Date</th>
                    <th>Overdue Days</th>
                    <th>Status</th>
                </tr>

                <?php while($u = $unpaid->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $u['first_name']." ".$u['last_name']; ?></td>
                    <td><?php echo $u['meter_number']; ?></td>
                    <td><?php echo $u['invoice_no']; ?></td>
                    <td>₱<?php echo number_format($u['amount'], 2); ?></td>
                    <td><?php echo $u['due_date']; ?></td>
                    <td>
                        <?php echo ($u['overdue_days'] > 0) ? $u['overdue_days'] : 0; ?>
                    </td>
                    <td><?php echo $u['status']; ?></td>
                </tr>
                <?php endwhile; ?>
            </table>

        </div>
    </div>

</body>
</html>