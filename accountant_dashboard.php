<?php
session_start();
include "db.php";
include "barangay.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "accountant") {
    header("Location: index.php");
    exit();
}

$total_collection = $conn->query("
    SELECT SUM(amount) AS total 
    FROM payments 
    WHERE status='verified'
")->fetch_assoc()['total'] ?? 0;

$payments_received = $conn->query("
    SELECT COUNT(*) AS total 
    FROM payments
")->fetch_assoc()['total'];

$pending_verification = $conn->query("
    SELECT COUNT(*) AS total 
    FROM payments 
    WHERE status='pending'
")->fetch_assoc()['total'];

$overdue_accounts = $conn->query("
    SELECT COUNT(*) AS total 
    FROM invoices 
    WHERE status!='paid' AND due_date < CURDATE()
")->fetch_assoc()['total'];

$rejected_payments = $conn->query("
    SELECT COUNT(*) AS total
    FROM payments
    WHERE status='rejected'
")->fetch_assoc()['total'];

$verified_payments = $conn->query("
    SELECT COUNT(*) AS total
    FROM payments
    WHERE status='verified'
")->fetch_assoc()['total'];

$pending_list = $conn->query("
    SELECT p.*, i.invoice_no, u.first_name, u.last_name
    FROM payments p
    JOIN invoices i ON p.invoice_id = i.id
    JOIN users u ON p.user_id = u.id
    WHERE p.status='pending'
    ORDER BY p.paid_at DESC
    LIMIT 5
");

$recent_payments = $conn->query("
    SELECT p.*, i.invoice_no, u.first_name, u.last_name
    FROM payments p
    JOIN invoices i ON p.invoice_id = i.id
    JOIN users u ON p.user_id = u.id
    ORDER BY p.paid_at DESC
    LIMIT 5
");

$daily_chart = $conn->query("
    SELECT DATE(paid_at) AS day, SUM(amount) AS total
    FROM payments
    WHERE status='verified'
    GROUP BY DATE(paid_at)
    ORDER BY day ASC
");

$daily_labels = [];
$daily_data = [];

while($d = $daily_chart->fetch_assoc()){
    $daily_labels[] = $d['day'];
    $daily_data[] = $d['total'];
}

$monthly_chart = $conn->query("
    SELECT DATE_FORMAT(paid_at, '%Y-%m') AS month, SUM(amount) AS total
    FROM payments
    WHERE status='verified'
    GROUP BY DATE_FORMAT(paid_at, '%Y-%m')
    ORDER BY month ASC
");

$monthly_labels = [];
$monthly_data = [];

while($m = $monthly_chart->fetch_assoc()){
    $monthly_labels[] = $m['month'];
    $monthly_data[] = $m['total'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Accountant Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

            <h1>Accountant Dashboard</h1>

            <div class="cards">
                <div class="card">Total Collection<br><strong>₱<?php echo number_format($total_collection, 2); ?></strong></div>
                <div class="card">Payments Received<br><strong><?php echo $payments_received; ?></strong></div>
                <div class="card">Pending Verification<br><strong><?php echo $pending_verification; ?></strong></div>
                <div class="card">Overdue Accounts<br><strong><?php echo $overdue_accounts; ?></strong></div>
            </div>

            <div class="cards">
                <div class="card">
                    Verified Payments<br>
                    <strong><?php echo $verified_payments; ?></strong>
                </div>

                <div class="card">
                    Rejected Payments<br>
                    <strong><?php echo $rejected_payments; ?></strong>
                </div>
            </div>

            <div class="tables">
                <div class="table-box">
                    <h2>Daily Collection Chart</h2>
                    <canvas id="dailyChart"></canvas>
                </div>

                <div class="table-box">
                    <h2>Monthly Collection Chart</h2>
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>

            <div class="tables">

                <div class="table-box">
                    <h2>Pending Payment Notifications</h2>

                    <?php if($pending_list->num_rows > 0): ?>
                    <table>
                        <tr>
                            <th>Transaction</th>
                            <th>Customer</th>
                            <th>Invoice</th>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>

                        <?php while($p = $pending_list->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $p['transaction_no']; ?></td>
                            <td><?php echo $p['first_name']." ".$p['last_name']; ?></td>
                            <td><?php echo $p['invoice_no']; ?></td>
                            <td>₱<?php echo number_format($p['amount'], 2); ?></td>
                            <td><?php echo $p['paid_at']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                    <?php else: ?>
                    <p>No pending payment verification.</p>
                    <?php endif; ?>

                </div>

                <div class="table-box">
                    <h2>Recent Payments</h2>

                    <table>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Customer</th>
                            <th>Invoice No.</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>

                        <?php while($p = $recent_payments->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $p['transaction_no']; ?></td>
                            <td><?php echo $p['first_name']." ".$p['last_name']; ?></td>
                            <td><?php echo $p['invoice_no']; ?></td>
                            <td>₱<?php echo number_format($p['amount'], 2); ?></td>
                            <td><?php echo $p['status']; ?></td>
                            <td><?php echo $p['paid_at']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </table>

                </div>

            </div>

        </div>
    </div>

    <script>
        new Chart(document.getElementById("dailyChart"), {
            type: "bar",
            data: {
                labels: <?php echo json_encode($daily_labels); ?>,
                datasets: [{
                    label: "Daily Collection",
                    data: <?php echo json_encode($daily_data); ?>,
                    borderWidth: 1
                }]
            }
        });

        new Chart(document.getElementById("monthlyChart"), {
            type: "line",
            data: {
                labels: <?php echo json_encode($monthly_labels); ?>,
                datasets: [{
                    label: "Monthly Collection",
                    data: <?php echo json_encode($monthly_data); ?>,
                    borderWidth: 2
                }]
            }
        });
    </script>

</body>
</html>