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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Financial Reports | H2O</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="report_accountant.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body id="mainBody">

    <div class="main-content">
        <div class="header-row" style="display: flex; justify-content: space-between; align-items: center;">
            <h1>FINANCIAL REPORTS</h1>
            <a href="print_accountant_report.php" target="_blank" class="export-btn">
                EXPORT PDF REPORT
            </a>
        </div>

        <!-- CHARTS SECTION -->
        <div class="flex-grid">
            <div class="glass-panel">
                <div class="panel-title-bar">Daily Collection Graph</div>
                <div class="content-area">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>
            <div class="glass-panel">
                <div class="panel-title-bar">Monthly Revenue Trend</div>
                <div class="content-area">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>

        <div class="glass-panel">
            <div class="panel-title-bar">Payment Status Overview</div>
            <div class="content-area" style="max-height: 300px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- COLLECTION SUMMARY TABLES -->
        <div class="flex-grid">
            <div class="glass-panel">
                <div class="panel-title-bar">Daily Collection Log</div>
                <div class="table-area">
                    <table class="data-table">
                        <thead>
                            <tr><th>Date</th><th>Total</th></tr>
                        </thead>
                        <tbody>
                            <?php while($d = $daily_collection->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($d['day'])); ?></td>
                                <td style="color: var(--success); font-weight: bold;">₱<?php echo number_format($d['total'], 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="glass-panel">
                <div class="panel-title-bar">Monthly Revenue Summary</div>
                <div class="table-area">
                    <table class="data-table">
                        <thead>
                            <tr><th>Month</th><th>Total</th></tr>
                        </thead>
                        <tbody>
                            <?php while($m = $monthly_collection->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $m['month']; ?></td>
                                <td style="color: var(--accent-blue); font-weight: bold;">₱<?php echo number_format($m['total'], 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- FULL HISTORY TABLE -->
        <div class="glass-panel">
            <div class="panel-title-bar">Detailed Customer Payment History</div>
            <div class="table-area">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Invoice</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($h = $payment_history->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo strtoupper($h['first_name']." ".$h['last_name']); ?></strong></td>
                            <td><small><?php echo $h['invoice_no']; ?></small></td>
                            <td>₱<?php echo number_format($h['amount'], 2); ?></td>
                            <td><?php echo $h['payment_method']; ?></td>
                            <td><span class="status-pill <?php echo ($h['status']=='verified') ? 'paid' : 'unpaid'; ?>"><?php echo $h['status']; ?></span></td>
                            <td><small><?php echo date('M d, Y', strtotime($h['paid_at'])); ?></small></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SIDEBAR RIGHT -->
    <div class="sidebar-right">
        <img src="assets/logo_name.png" class="side-logo">
        <div class="agent-info" style="color: white; text-align: center; margin-bottom: 30px;">
            <h3>ACCOUNTANT</h3>
            <p style="font-size: 0.7rem; opacity: 0.6;">FINANCE DEPT</p>
        </div>
        <nav class="nav-menu" style="width: 100%;">
            <a href="accountant_dashboard.php" class="nav-item">DASHBOARD</a>
            <a href="payments.php" class="nav-item">PAYMENTS</a>
            <a href="receipts.php" class="nav-item">RECEIPTS</a>
            <a href="reports_accountant.php" class="nav-item active">REPORTS</a>
            <a href="balance.php" class="nav-item">BALANCE TRACKER</a>
            <a href="profile.php" class="nav-item">PROFILE</a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn-container">LOG OUT</a>
        </div>
    </div>

    <script>
        window.onload = () => { document.body.style.opacity = "1"; };

        Chart.defaults.color = '#bdc3c7';
        Chart.defaults.borderColor = 'rgba(255,255,255,0.1)';

        const commonOptions = { responsive: true, maintainAspectRatio: false };

        new Chart(document.getElementById("dailyChart"), {
            type: "bar",
            data: {
                labels: <?php echo json_encode($daily_labels); ?>,
                datasets: [{
                    label: "Daily Collection",
                    data: <?php echo json_encode($daily_data); ?>,
                    backgroundColor: '#3498db',
                    borderRadius: 5
                }]
            },
            options: commonOptions
        });

        new Chart(document.getElementById("monthlyChart"), {
            type: "line",
            data: {
                labels: <?php echo json_encode($monthly_labels); ?>,
                datasets: [{
                    label: "Monthly Collection",
                    data: <?php echo json_encode($monthly_data); ?>,
                    borderColor: '#2ecc71',
                    backgroundColor: 'rgba(46, 204, 113, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: commonOptions
        });

        new Chart(document.getElementById("statusChart"), {
            type: "bar",
            data: {
                labels: <?php echo json_encode($status_labels); ?>,
                datasets: [{
                    label: "Transaction Status Count",
                    data: <?php echo json_encode($status_data); ?>,
                    backgroundColor: ['#2ecc71', '#f1c40f', '#e74c3c'],
                    borderRadius: 5
                }]
            },
            options: commonOptions
        });
    </script>
</body>
</html>
