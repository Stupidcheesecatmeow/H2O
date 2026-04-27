<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: index.php");
    exit();
}

/* SUMMARY */
$total_users = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='user'")->fetch_assoc()['total'];
$total_agents = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='agent'")->fetch_assoc()['total'];
$total_collection = $conn->query("SELECT SUM(amount) AS total FROM payments WHERE status='verified'")->fetch_assoc()['total'] ?? 0;
$paid = $conn->query("SELECT COUNT(*) AS total FROM invoices WHERE status='paid'")->fetch_assoc()['total'];
$pending = $conn->query("SELECT COUNT(*) AS total FROM invoices WHERE status!='paid'")->fetch_assoc()['total'];
$overdue = $conn->query("SELECT COUNT(*) AS total FROM invoices WHERE status!='paid' AND due_date < CURDATE()")->fetch_assoc()['total'];

/* NOTIFICATIONS */
$new_complaints = $conn->query("SELECT COUNT(*) AS total FROM complaints WHERE status='open'")->fetch_assoc()['total'];
$pending_payments = $conn->query("SELECT COUNT(*) AS total FROM payments WHERE status='pending'")->fetch_assoc()['total'];

$announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5");

$profile_updates = $conn->query("
    SELECT n.*, u.first_name, u.last_name
    FROM notifications n
    JOIN users u ON n.user_id = u.id
    WHERE n.role_target='admin' AND n.status='unread'
    ORDER BY n.created_at DESC
    LIMIT 5
");

$profile_update_count = $conn->query("
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE role_target='admin' AND status='unread'")->fetch_assoc()['total'];

$latest_complaints = $conn->query("
    SELECT c.*, u.first_name, u.last_name
    FROM complaints c
    JOIN users u ON c.user_id = u.id
    WHERE c.status='open'
    ORDER BY c.created_at DESC
    LIMIT 5
");

/* COLLECTION CHART */
$collection_query = $conn->query("
    SELECT DATE_FORMAT(paid_at, '%Y-%m') AS month, SUM(amount) AS total
    FROM payments
    WHERE status='verified'
    GROUP BY DATE_FORMAT(paid_at, '%Y-%m')
    ORDER BY month ASC
");

$collection_labels = [];
$collection_data = [];

while($row = $collection_query->fetch_assoc()){
    $collection_labels[] = $row['month'];
    $collection_data[] = $row['total'];
}

/* USAGE CHART */
$usage_query = $conn->query("
    SELECT DATE_FORMAT(reading_date, '%Y-%m') AS month, SUM(consumption) AS total
    FROM meter_readings
    GROUP BY DATE_FORMAT(reading_date, '%Y-%m')
    ORDER BY month ASC
");

$usage_labels = [];
$usage_data = [];

while($row = $usage_query->fetch_assoc()){
    $usage_labels[] = $row['month'];
    $usage_data[] = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>H.O.H Admin Dashboard</title>
    <link rel="stylesheet" href="styles/admin_dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body id="mainBody">

    <div class="main-content">
        <div class="header-row">
            <h1>ADMIN DASHBOARD</h1>
        </div>

        <!-- ROW 1 -->
        <div class="stats-grid">
            <div class="stat-card"><span class="stat-label">Total Users</span><span class="stat-value"><?php echo $total_users; ?></span></div>
            <div class="stat-card"><span class="stat-label">Field Agents</span><span class="stat-value"><?php echo $total_agents; ?></span></div>
            <div class="stat-card"><span class="stat-label">Total Collection</span><span class="stat-value" style="color:var(--success);">₱<?php echo number_format($total_collection,2); ?></span></div>
            <div class="stat-card"><span class="stat-label">Paid Bills</span><span class="stat-value"><?php echo $paid; ?></span></div>
            <div class="stat-card"><span class="stat-label">Pending</span><span class="stat-value" style="color:var(--warning);"><?php echo $pending; ?></span></div>
        </div>

        <!-- ROW 2 -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-label">Complaints</span>
                <span class="stat-value"><?php echo $new_complaints; ?></span>
                <a href="complaints_admin.php" class="stat-link">View Details →</a>
            </div>
            <div class="stat-card">
                <span class="stat-label">Pending Verify</span>
                <span class="stat-value"><?php echo $pending_payments; ?></span>
                <a href="transactions.php" class="stat-link">Verifications →</a>
            </div>
            <div class="stat-card">
                <span class="stat-label">Overdue Bills</span>
                <span class="stat-value" style="color:var(--danger);"><?php echo $overdue; ?></span>
                <a href="reports.php" class="stat-link">View Reports →</a>
            </div>
            <div class="stat-card">
                <span class="stat-label">Notifications</span>
                <span class="stat-value"><?php echo $profile_update_count; ?></span>
                <a href="admin_notifications.php" class="stat-link">View Updates →</a>
            </div>
        </div>

        <!-- CHARTS -->
        <div class="flex-grid">
            <div class="glass-panel">
                <div class="panel-title-bar">Monthly Collection Graph</div>
                <div class="content-area"><canvas id="collectionChart" height="200"></canvas></div>
            </div>
            <div class="glass-panel">
                <div class="panel-title-bar">Water Usage Trend</div>
                <div class="content-area"><canvas id="usageChart" height="200"></canvas></div>
            </div>
        </div>

        <!-- ANNOUNCEMENTS -->
        <div class="glass-panel">
            <div class="panel-title-bar">Recent System Announcements</div>
            <div class="content-area" style="padding: 0 20px;">
                <?php if($announcements->num_rows > 0): ?>
                    <?php while($a = $announcements->fetch_assoc()): ?>
                        <div class="announcement-row">
                            <strong><?php echo $a['title']; ?></strong> • <small><?php echo date('M d, Y', strtotime($a['announcement_date'])); ?></small>
                            <p><?php echo $a['message']; ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="padding: 20px; opacity: 0.5; text-align: center;">No announcements published.</p>
                <?php endif; ?>
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
                <a href="admin_dashboard.php" class="nav-item active">DASHBOARD</a>
                <a href="admin_notifications.php" class="nav-item">NOTIFICATIONS</a>
                <a href="announcements.php" class="nav-item">ANNOUNCEMENTS</a>
                <a href="user_management.php" class="nav-item">USER MANAGEMENT</a>
                <a href="agent_management.php" class="nav-item">FIELD AGENTS</a>
                <a href="invoices.php" class="nav-item">INVOICES</a>
                <a href="transactions.php" class="nav-item">TRANSACTIONS</a>
                <a href="complaints_admin.php" class="nav-item">COMPLAINTS</a>
                <a href="reports.php" class="nav-item">REPORTS</a>
                <a href="profile.php" class="nav-item">PROFILE</a>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn-container">LOG OUT</a>
            </div>
        </div>

    <script>
        window.onload = () => { document.body.classList.add('fade-in'); };

        // Chart styling for dark theme
        Chart.defaults.color = '#bdc3c7';
        Chart.defaults.borderColor = 'rgba(255,255,255,0.05)';

        new Chart(document.getElementById("collectionChart"), {
            type: "bar",
            data: {
                labels: <?php echo json_encode($collection_labels); ?>,
                datasets: [{
                    label: "₱ Collection",
                    data: <?php echo json_encode($collection_data); ?>,
                    backgroundColor: '#3498db',
                    borderRadius: 5
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        new Chart(document.getElementById("usageChart"), {
            type: "line",
            data: {
                labels: <?php echo json_encode($usage_labels); ?>,
                datasets: [{
                    label: "m³ Usage",
                    data: <?php echo json_encode($usage_data); ?>,
                    borderColor: '#2ecc71',
                    backgroundColor: 'rgba(46, 204, 113, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // Fast link transitions
        document.querySelectorAll('.nav-item, .logout-btn').forEach(link => {
            link.addEventListener('click', function(e) {
                const target = this.getAttribute('href');
                if (target && target !== '#') {
                    e.preventDefault();
                    document.body.classList.add('fade-out-fast');
                    setTimeout(() => { window.location.href = target; }, 200);
                }
            });
        });
    </script>
</body>
</html>
