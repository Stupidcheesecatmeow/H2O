<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "accountant") {
    header("Location: index.php");
    exit();
}

$total_collection = $conn->query("
    SELECT SUM(amount) AS total 
    FROM payments 
    WHERE status='verified'
")->fetch_assoc()['total'] ?? 0;

$total_payments = $conn->query("
    SELECT COUNT(*) AS total 
    FROM payments
")->fetch_assoc()['total'];

$verified = $conn->query("
    SELECT COUNT(*) AS total 
    FROM payments 
    WHERE status='verified'
")->fetch_assoc()['total'];

$pending = $conn->query("
    SELECT COUNT(*) AS total 
    FROM payments 
    WHERE status='pending'
")->fetch_assoc()['total'];

$rejected = $conn->query("
    SELECT COUNT(*) AS total 
    FROM payments 
    WHERE status='rejected'
")->fetch_assoc()['total'];

$daily = $conn->query("
    SELECT DATE(paid_at) AS day, SUM(amount) AS total
    FROM payments
    WHERE status='verified'
    GROUP BY DATE(paid_at)
    ORDER BY day DESC
");

$monthly = $conn->query("
    SELECT DATE_FORMAT(paid_at,'%Y-%m') AS month, SUM(amount) AS total
    FROM payments
    WHERE status='verified'
    GROUP BY DATE_FORMAT(paid_at,'%Y-%m')
    ORDER BY month DESC
");

$history = $conn->query("
    SELECT 
        p.*,
        i.invoice_no,
        u.first_name,
        u.last_name,
        u.meter_number
    FROM payments p
    JOIN invoices i ON p.invoice_id = i.id
    JOIN users u ON p.user_id = u.id
    ORDER BY p.paid_at DESC
");

$generated_date = date("F d, Y");
?>

<!DOCTYPE html>
<html>
<head>
    <title>H.O.H Accountant Report</title>
    <link rel="stylesheet" href="styles/report.css">
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
                    <h1>H.O.H</h1>
                    <p>Hydro Operations Hub</p>
                    <p>Water Billing and Collection System</p>
                </div>
            </div>

            <div class="report-title">
                <h2>ACCOUNTANT REPORT</h2>
                <p>Generated: <?php echo $generated_date; ?></p>
            </div>
        </div>

        <h3>Financial Summary</h3>

        <div class="summary-grid">
            <div class="summary-card">
                <span>Total Collection</span>
                <strong>₱<?php echo number_format($total_collection, 2); ?></strong>
            </div>

            <div class="summary-card">
                <span>Total Payments</span>
                <strong><?php echo $total_payments; ?></strong>
            </div>

            <div class="summary-card">
                <span>Verified</span>
                <strong><?php echo $verified; ?></strong>
            </div>

            <div class="summary-card">
                <span>Pending</span>
                <strong><?php echo $pending; ?></strong>
            </div>

            <div class="summary-card">
                <span>Rejected</span>
                <strong><?php echo $rejected; ?></strong>
            </div>
        </div>

        <h3>Daily Collection</h3>

        <table>
            <tr>
                <th>Date</th>
                <th>Total Collection</th>
            </tr>

            <?php while($d = $daily->fetch_assoc()): ?>
            <tr>
                <td><?php echo $d['day']; ?></td>
                <td>₱<?php echo number_format($d['total'], 2); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>

        <h3>Monthly Collection</h3>

        <table>
            <tr>
                <th>Month</th>
                <th>Total Collection</th>
            </tr>

            <?php while($m = $monthly->fetch_assoc()): ?>
            <tr>
                <td><?php echo $m['month']; ?></td>
                <td>₱<?php echo number_format($m['total'], 2); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>

        <h3>Payment History</h3>

        <table>
            <tr>
                <th>Transaction ID</th>
                <th>Customer</th>
                <th>Meter No.</th>
                <th>Invoice No.</th>
                <th>Amount</th>
                <th>MOP</th>
                <th>Status</th>
                <th>Date</th>
            </tr>

            <?php while($h = $history->fetch_assoc()): ?>
            <tr>
                <td><?php echo $h['transaction_no']; ?></td>
                <td><?php echo $h['first_name']." ".$h['last_name']; ?></td>
                <td><?php echo $h['meter_number']; ?></td>
                <td><?php echo $h['invoice_no']; ?></td>
                <td>₱<?php echo number_format($h['amount'], 2); ?></td>
                <td><?php echo $h['payment_method']; ?></td>
                <td><?php echo strtoupper($h['status']); ?></td>
                <td><?php echo $h['paid_at']; ?></td>
            </tr>
            <?php endwhile; ?>
        </table>

        <div class="signature-section">
            <div>
                <p>Prepared by:</p>
                <span>________________________</span>
                <small>Accountant</small>
            </div>

            <div>
                <p>Checked by:</p>
                <span>________________________</span>
                <small>Authorized Personnel</small>
            </div>
        </div>

        <div class="footer">
            <p>This is a system-generated financial report from Hydro Operations Hub.</p>
        </div>

    </div>

</body>
</html>