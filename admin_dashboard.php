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
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <div class="layout">

        <div class="sidebar">
            <h2>Admin</h2>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="admin_notifications.php">Notifications</a></li>
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

            <h1>Admin Dashboard</h1>

            <div class="cards">
                <div class="card">Total Users<br><strong><?php echo $total_users; ?></strong></div>
                <div class="card">Field Agents<br><strong><?php echo $total_agents; ?></strong></div>
                <div class="card">Total Collection<br><strong>₱<?php echo number_format($total_collection,2); ?></strong></div>
                <div class="card">Paid Bills<br><strong><?php echo $paid; ?></strong></div>
                <div class="card">Pending Bills<br><strong><?php echo $pending; ?></strong></div>
            </div>

            <div class="cards">
                <div class="card">
                    Open Complaints<br>
                    <strong><?php echo $new_complaints; ?></strong><br>
                    <a href="complaints_admin.php">View complaints</a>
                </div>

                <div class="card">
                    Pending Payment Verification<br>
                    <strong><?php echo $pending_payments; ?></strong><br>
                    <a href="transactions.php">View transactions</a>
                </div>

                <div class="card">
                    Overdue Bills<br>
                    <strong><?php echo $overdue; ?></strong><br>
                    <a href="reports.php">View report</a>
                </div>

                <div class="card">
                    Notifications<br>
                    <strong><?php echo $profile_update_count; ?></strong><br>
                    <a href="admin_notifications.php">View updates</a>
                </div>

            </div>

            <div class="tables">
                <div class="table-box">
                    <h2>Monthly Collection</h2>
                    <canvas id="collectionChart"></canvas>
                </div>

                <div class="table-box">
                    <h2>Water Usage Trend</h2>
                    <canvas id="usageChart"></canvas>
                </div>
            </div>

            <div class="tables">

                <div class="table-box">
                    <h2>Recent Announcements</h2>

                    <?php if($announcements->num_rows > 0): ?>
                        <?php while($a = $announcements->fetch_assoc()): ?>
                            <div style="border-bottom:1px solid #ddd; padding:10px;">
                                <strong><?php echo $a['title']; ?></strong><br>
                                <small><?php echo $a['announcement_date']; ?></small>
                                <p><?php echo $a['message']; ?></p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No announcements yet.</p>
                    <?php endif; ?>

                </div>

            </div>

        </div>
    </div>

    <script>
    new Chart(document.getElementById("collectionChart"), {
        type: "bar",
        data: {
            labels: <?php echo json_encode($collection_labels); ?>,
            datasets: [{
                label: "Collection",
                data: <?php echo json_encode($collection_data); ?>,
                borderWidth: 1
            }]
        }
    });

    new Chart(document.getElementById("usageChart"), {
        type: "line",
        data: {
            labels: <?php echo json_encode($usage_labels); ?>,
            datasets: [{
                label: "Usage",
                data: <?php echo json_encode($usage_data); ?>,
                borderWidth: 2
            }]
        }
    });
    </script>

</body>
</html>