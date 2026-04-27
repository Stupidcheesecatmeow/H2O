<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: index.php");
    exit();
}

$monthly_revenue = $conn->query("
    SELECT DATE_FORMAT(paid_at, '%Y-%m') AS month, SUM(amount) AS total
    FROM payments
    WHERE status='verified'
    GROUP BY DATE_FORMAT(paid_at, '%Y-%m')
    ORDER BY month DESC
");

$annual_collection = $conn->query("
    SELECT YEAR(paid_at) AS year, SUM(amount) AS total
    FROM payments
    WHERE status='verified'
    GROUP BY YEAR(paid_at)
    ORDER BY year DESC
");

$unpaid = $conn->query("
    SELECT 
        i.*, 
        u.first_name, 
        u.last_name,
        u.meter_number
    FROM invoices i
    JOIN users u ON i.user_id = u.id
    WHERE i.status!='paid'
    ORDER BY i.due_date ASC
");

$total_collection = $conn->query("
    SELECT SUM(amount) AS total 
    FROM payments 
    WHERE status='verified'
")->fetch_assoc()['total'] ?? 0;

$total_unpaid = $conn->query("
    SELECT SUM(amount) AS total 
    FROM invoices 
    WHERE status!='paid'
")->fetch_assoc()['total'] ?? 0;

$total_consumers = $conn->query("
    SELECT COUNT(*) AS total 
    FROM users 
    WHERE role='user'
")->fetch_assoc()['total'];

$total_paid = $conn->query("
    SELECT COUNT(*) AS total 
    FROM invoices 
    WHERE status='paid'
")->fetch_assoc()['total'];

$total_unpaid_count = $conn->query("
    SELECT COUNT(*) AS total 
    FROM invoices 
    WHERE status!='paid'
")->fetch_assoc()['total'];

$generated_date = date("F d, Y");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Report</title>
    <link rel="stylesheet" href="report.css">
</head>
<body>

    <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>

    <div class="report">

        <div class="watermark">
            <img src="assets/logo_blk_name.png" alt="Watermark">
        </div>

        <div class="report-header">
            <div class="brand">
                <img src="assets/logo_blk_name.png" alt="Logo">
                <div>
                    <h1>H2O</h1>
                    <p>Hydro Operations Hub</p>
                    <p>Water Billing and Collection System</p>
                </div>
            </div>

            <div class="report-title">
                <h2>ADMIN REPORT</h2>
                <p>Generated: <?php echo $generated_date; ?></p>
            </div>
        </div>

        <h3>Report Summary</h3>

        <div class="summary-grid">
            <div class="summary-card">
                <span>Total Consumers</span>
                <strong><?php echo $total_consumers; ?></strong>
            </div>

            <div class="summary-card">
                <span>Total Collection</span>
                <strong>₱<?php echo number_format($total_collection, 2); ?></strong>
            </div>

            <div class="summary-card">
                <span>Total Unpaid Amount</span>
                <strong>₱<?php echo number_format($total_unpaid, 2); ?></strong>
            </div>

            <div class="summary-card">
                <span>Paid Invoices</span>
                <strong><?php echo $total_paid; ?></strong>
            </div>

            <div class="summary-card">
                <span>Unpaid Bills</span>
                <strong><?php echo $total_unpaid_count; ?></strong>
            </div>
        </div>

        <h3>Monthly Revenue</h3>

        <table>
            <tr>
                <th>Month</th>
                <th>Total Revenue</th>
            </tr>

            <?php while($m = $monthly_revenue->fetch_assoc()): ?>
            <tr>
                <td><?php echo $m['month']; ?></td>
                <td>₱<?php echo number_format($m['total'], 2); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>

        <h3>Annual Collection Summary</h3>

        <table>
            <tr>
                <th>Year</th>
                <th>Total Collection</th>
            </tr>

            <?php while($a = $annual_collection->fetch_assoc()): ?>
            <tr>
                <td><?php echo $a['year']; ?></td>
                <td>₱<?php echo number_format($a['total'], 2); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>

        <h3>Unpaid Bills</h3>

        <table>
            <tr>
                <th>Invoice No.</th>
                <th>Customer</th>
                <th>Meter No.</th>
                <th>Amount</th>
                <th>Due Date</th>
                <th>Status</th>
            </tr>

            <?php while($u = $unpaid->fetch_assoc()): ?>
            <tr>
                <td><?php echo $u['invoice_no']; ?></td>
                <td><?php echo $u['first_name']." ".$u['last_name']; ?></td>
                <td><?php echo $u['meter_number']; ?></td>
                <td>₱<?php echo number_format($u['amount'], 2); ?></td>
                <td><?php echo $u['due_date']; ?></td>
                <td><?php echo strtoupper($u['status']); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>

        <div class="signature-section">
            <div>
                <p>Prepared by:</p>
                <span>________________________</span>
                <small>Administrator</small>
            </div>

            <div>
                <p>Checked by:</p>
                <span>________________________</span>
                <small>Authorized Personnel</small>
            </div>
        </div>

        <div class="footer">
            <p>This is a system-generated report from Hydro Operations Hub.</p>
        </div>

    </div>

</body>
</html>