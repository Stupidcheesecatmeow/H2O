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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>H.O.H Accountant Dashboard</title>
    <link rel="stylesheet" href="styles/accountant_dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body id="mainBody">

    <div class="main-content">
        <div class="header-row">
            <h1>ACCOUNTANT DASHBOARD</h1>
        </div>

        <!-- STATS -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-label">Total Collection</span>
                <span class="stat-value" style="color: var(--success);">₱<?php echo number_format($total_collection, 2); ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Payments Received</span>
                <span class="stat-value"><?php echo $payments_received; ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Pending Verification</span>
                <span class="stat-value" style="color: var(--warning);"><?php echo $pending_verification; ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Overdue Accounts</span>
                <span class="stat-value" style="color: var(--danger);"><?php echo $overdue_accounts; ?></span>
            </div>
        </div>

        <!-- CHARTS -->
        <div class="flex-grid">
            <div class="glass-panel">
                <div class="panel-title-bar">Daily Collection Chart</div>
                <div class="content-area">
                    <canvas id="dailyChart" style="max-height: 250px;"></canvas>
                </div>
            </div>
            <div class="glass-panel">
                <div class="panel-title-bar">Monthly Collection Chart</div>
                <div class="content-area">
                    <canvas id="monthlyChart" style="max-height: 250px;"></canvas>
                </div>
            </div>
        </div>

        <!-- TABLES -->
        <div class="flex-grid">
            <div class="glass-panel">
                <div class="panel-title-bar">Pending Verifications</div>
                <div class="content-area">
                    <?php if($pending_list->num_rows > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Transaction</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($p = $pending_list->fetch_assoc()): ?>
                            <tr>
                                <td><small><?php echo $p['transaction_no']; ?></small></td>
                                <td><strong><?php echo $p['first_name']; ?></strong></td>
                                <td style="color: var(--success);">₱<?php echo number_format($p['amount'], 2); ?></td>
                                <td style="font-size: 0.7rem; opacity: 0.7;"><?php echo date('M d', strtotime($p['paid_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <p style="opacity: 0.5; text-align: center;">No pending verifications.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="glass-panel">
                <div class="panel-title-bar">Recent Activity</div>
                <div class="content-area">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($p = $recent_payments->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $p['first_name']." ".$p['last_name']; ?></td>
                                <td>₱<?php echo number_format($p['amount'], 2); ?></td>
                                <td><span style="font-size:0.7rem; font-weight:bold; color: <?php echo ($p['status'] == 'verified') ? 'var(--success)' : 'var(--warning)'; ?>;"><?php echo strtoupper($p['status']); ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

        <!-- SIDEBAR -->
        <div class="sidebar-right">
            <img src="assets/logo_name.png" class="side-logo">
            <div class="agent-info" style="color: white; text-align: center; margin-bottom: 30px;">
                <h3>ACCOUNTANT</h3>
                <p style="font-size: 0.7rem; opacity: 0.6;">FINANCE DEPT</p>
            </div>
            <nav class="nav-menu" style="width: 100%;">
                <a href="accountant_dashboard.php" class="nav-item active">DASHBOARD</a>
                <a href="payments.php" class="nav-item">PAYMENTS</a>
                <a href="receipts.php" class="nav-item">RECEIPTS</a>
                <a href="reports_accountant.php" class="nav-item">REPORTS</a>
                <a href="balance.php" class="nav-item">BALANCE TRACKER</a>
                <a href="profile.php" class="nav-item">PROFILE</a>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn-container">LOG OUT</a>
            </div>
        </div>

    <script>
        window.onload = () => { document.body.style.opacity = "1"; };

        // Chart.js defaults for dark theme
        Chart.defaults.color = '#bdc3c7';
        Chart.defaults.borderColor = 'rgba(255,255,255,0.1)';

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
            options: { responsive: true, maintainAspectRatio: false }
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
            options: { responsive: true, maintainAspectRatio: false }
        });
    </script>
</body>
</html>
