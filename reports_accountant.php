<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "accountant") {
    header("Location: index.php");
    exit();
}

$daily_collection = $conn->query("
    SELECT DATE(paid_at) AS day, SUM(amount) AS total
    FROM payments
    WHERE status='verified'
    GROUP BY DATE(paid_at)
    ORDER BY day DESC
");

$monthly_collection = $conn->query("
    SELECT DATE_FORMAT(paid_at, '%Y-%m') AS month, SUM(amount) AS total
    FROM payments
    WHERE status='verified'
    GROUP BY DATE_FORMAT(paid_at, '%Y-%m')
    ORDER BY month DESC
");

$payment_history = $conn->query("
    SELECT 
        p.*,
        i.invoice_no,
        u.first_name,
        u.last_name
    FROM payments p
    JOIN invoices i ON p.invoice_id = i.id
    JOIN users u ON p.user_id = u.id
    ORDER BY p.paid_at DESC
");

/* CHART DATA */
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

$status_chart = $conn->query("
    SELECT status, COUNT(*) AS total
    FROM payments
    GROUP BY status
");

$status_labels = [];
$status_data = [];

while($s = $status_chart->fetch_assoc()){
    $status_labels[] = $s['status'];
    $status_data[] = $s['total'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Accountant Reports</title>
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

            <h1>Reports and Financial History</h1>

            <a href="print_accountant_report.php" target="_blank">
                <button type="button">Export Report</button>
            </a>

            <div class="tables">
                <div class="table-box">
                    <h3>Daily Collection Chart</h3>
                    <canvas id="dailyChart"></canvas>
                </div>

                <div class="table-box">
                    <h3>Monthly Collection Chart</h3>
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>

            <div class="table-box">
                <h3>Payment Status Overview</h3>
                <canvas id="statusChart"></canvas>
            </div>

            <div class="tables">

                <div class="table-box">
                    <h3>Daily Collection</h3>
                    <table>
                        <tr>
                            <th>Date</th>
                            <th>Total Collection</th>
                        </tr>

                        <?php while($d = $daily_collection->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $d['day']; ?></td>
                            <td>₱<?php echo number_format($d['total'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                </div>

                <div class="table-box">
                    <h3>Monthly Collection</h3>
                    <table>
                        <tr>
                            <th>Month</th>
                            <th>Total Collection</th>
                        </tr>

                        <?php while($m = $monthly_collection->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $m['month']; ?></td>
                            <td>₱<?php echo number_format($m['total'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                </div>

            </div>

            <h3>Payment History Per Customer</h3>

            <table>
                <tr>
                    <th>Customer</th>
                    <th>Invoice No.</th>
                    <th>Amount</th>
                    <th>MOP</th>
                    <th>Reference</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>

                <?php while($h = $payment_history->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $h['first_name']." ".$h['last_name']; ?></td>
                    <td><?php echo $h['invoice_no']; ?></td>
                    <td>₱<?php echo number_format($h['amount'], 2); ?></td>
                    <td><?php echo $h['payment_method']; ?></td>
                    <td><?php echo $h['reference_no']; ?></td>
                    <td><?php echo $h['status']; ?></td>
                    <td><?php echo $h['paid_at']; ?></td>
                </tr>
                <?php endwhile; ?>
            </table>

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

        new Chart(document.getElementById("statusChart"), {
            type: "bar",
            data: {
                labels: <?php echo json_encode($status_labels); ?>,
                datasets: [{
                    label: "Payment Status",
                    data: <?php echo json_encode($status_data); ?>,
                    borderWidth: 1
                }]
            }
        });
    </script>

</body>
</html>