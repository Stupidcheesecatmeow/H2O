<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: index.php");
    exit();
}

/* MONTHLY REVENUE */
$monthly_revenue = $conn->query("
    SELECT DATE_FORMAT(paid_at, '%Y-%m') AS month, SUM(amount) AS total
    FROM payments
    WHERE status='verified'
    GROUP BY DATE_FORMAT(paid_at, '%Y-%m')
    ORDER BY month DESC
");

/* ANNUAL COLLECTION */
$annual_collection = $conn->query("
    SELECT YEAR(paid_at) AS year, SUM(amount) AS total
    FROM payments
    WHERE status='verified'
    GROUP BY YEAR(paid_at)
    ORDER BY year DESC
");

/* UNPAID BILLS */
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

/* USAGE TREND GRAPH */
$usage_trend = $conn->query("
    SELECT DATE_FORMAT(reading_date, '%Y-%m') AS month, SUM(consumption) AS total
    FROM meter_readings
    GROUP BY DATE_FORMAT(reading_date, '%Y-%m')
    ORDER BY month ASC
");

$usageLabels = [];
$usageData = [];

while($u = $usage_trend->fetch_assoc()){
    $usageLabels[] = $u['month'];
    $usageData[] = $u['total'];
}

/* COLLECTION GRAPH */
$collection_graph = $conn->query("
    SELECT DATE_FORMAT(paid_at, '%Y-%m') AS month, SUM(amount) AS total
    FROM payments
    WHERE status='verified'
    GROUP BY DATE_FORMAT(paid_at, '%Y-%m')
    ORDER BY month ASC
");

$collectionLabels = [];
$collectionData = [];

while($c = $collection_graph->fetch_assoc()){
    $collectionLabels[] = $c['month'];
    $collectionData[] = $c['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>H.O.H System Reports Admin</title>
    <link rel="stylesheet" href="styles/reports.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body id="mainBody">

    <div class="main-content">
        <div class="header-row" style="display: flex; justify-content: space-between; align-items: center;">
            <h1>ANALYTICS & REPORTS</h1>
            <a href="print_report.php" target="_blank" class="export-btn">
                EXPORT PDF REPORT
            </a>
        </div>

        <!-- CHARTS -->
        <div class="flex-grid">
            <div class="glass-panel">
                <div class="panel-title-bar">Collection Summary Graph</div>
                <div class="content-area">
                    <canvas id="collectionChart"></canvas>
                </div>
            </div>
            <div class="glass-panel">
                <div class="panel-title-bar">Usage Trend Graph</div>
                <div class="content-area">
                    <canvas id="usageChart"></canvas>
                </div>
            </div>
        </div>

        <!-- REVENUE -->
        <div class="flex-grid">
            <div class="glass-panel">
                <div class="panel-title-bar">Monthly Revenue Summary</div>
                <div class="table-area">
                    <table class="data-table">
                        <thead>
                            <tr><th>Month</th><th>Total Revenue</th></tr>
                        </thead>
                        <tbody>
                            <?php while($m = $monthly_revenue->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo $m['month']; ?></strong></td>
                                <td style="color: var(--success); font-weight: bold;">₱<?php echo number_format($m['total'], 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="glass-panel">
                <div class="panel-title-bar">Annual Collection Log</div>
                <div class="table-area">
                    <table class="data-table">
                        <thead>
                            <tr><th>Year</th><th>Total Collection</th></tr>
                        </thead>
                        <tbody>
                            <?php while($a = $annual_collection->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo $a['year']; ?></strong></td>
                                <td style="color: var(--accent-blue); font-weight: bold;">₱<?php echo number_format($a['total'], 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- UNPAID BILLS -->
        <div class="glass-panel">
            <div class="panel-title-bar">Delinquent / Unpaid Accounts</div>
            <div class="table-area">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Invoice No.</th>
                            <th>Customer</th>
                            <th>Meter No.</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th style="text-align:center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($u = $unpaid->fetch_assoc()): ?>
                        <tr>
                            <td><span class="id-badge"><?php echo $u['invoice_no']; ?></span></td>
                            <td><strong><?php echo strtoupper($u['first_name']." ".$u['last_name']); ?></strong></td>
                            <td><small style="font-family:monospace;"><?php echo $u['meter_number']; ?></small></td>
                            <td style="color: #fff; font-weight:bold;">₱<?php echo number_format($u['amount'], 2); ?></td>
                            <td><small><?php echo date('M d, Y', strtotime($u['due_date'])); ?></small></td>
                            <td style="text-align:center">
                                <span class="status-pill unpaid"><?php echo strtoupper($u['status']); ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SIDEBAR -->
    <div class="sidebar-right">
        <img src="assets/logo_name.png" class="side-logo">
        <div class="agent-info" style="color: white; text-align: center; margin-bottom: 30px;">
            <h3>ADMIN</h3>
            <p style="font-size: 0.7rem; opacity: 0.6;">ADMIN DEPT</p>
        </div>
        <nav class="nav-menu">
            <a href="admin_dashboard.php" class="nav-item">DASHBOARD</a>
            <a href="admin_notifications.php" class="nav-item">NOTIFICATIONS</a>
            <a href="announcements.php" class="nav-item">ANNOUNCEMENTS</a>
            <a href="user_management.php" class="nav-item">USER MANAGEMENT</a>
            <a href="agent_management.php" class="nav-item">FIELD AGENTS</a>
            <a href="invoices.php" class="nav-item">INVOICES</a>
            <a href="transactions.php" class="nav-item">TRANSACTIONS</a>
            <a href="complaints_admin.php" class="nav-item">COMPLAINTS</a>
            <a href="reports.php" class="nav-item  active">REPORTS</a>
            <a href="profile.php" class="nav-item">PROFILE</a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn-container">LOG OUT</a>
        </div>
    </div>

    <script>
        window.onload = () => { document.body.style.opacity = "1"; };

        Chart.defaults.color = '#bdc3c7';
        Chart.defaults.borderColor = 'rgba(255,255,255,0.05)';

        new Chart(document.getElementById("collectionChart"), {
            type: "bar",
            data: {
                labels: <?php echo json_encode($collectionLabels); ?>,
                datasets: [{
                    label: "₱ Collection",
                    data: <?php echo json_encode($collectionData); ?>,
                    backgroundColor: '#3498db',
                    borderRadius: 5
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        new Chart(document.getElementById("usageChart"), {
            type: "line",
            data: {
                labels: <?php echo json_encode($usageLabels); ?>,
                datasets: [{
                    label: "m³ Usage",
                    data: <?php echo json_encode($usageData); ?>,
                    borderColor: '#2ecc71',
                    backgroundColor: 'rgba(46, 204, 113, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    </script>
</body>
</html>
