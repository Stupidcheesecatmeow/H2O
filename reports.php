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
<html>
<head>
    <title>Reports</title>
    <link rel="stylesheet" href="dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <div class="layout">

        <div class="sidebar">
            <h2>Admin</h2>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
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

            <h1>Reports</h1>

            <a href="print_report.php" target="_blank">
                <button type="button">Export Report</button>
            </a>

            <div class="tables">

                <div class="table-box">
                    <h2>Collection Summary Graph</h2>
                    <canvas id="collectionChart"></canvas>
                </div>

                <div class="table-box">
                    <h2>Usage Trend Graph</h2>
                    <canvas id="usageChart"></canvas>
                </div>

            </div>

            <div class="tables">

                <div class="table-box">
                    <h2>Monthly Revenue</h2>

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
                </div>

                <div class="table-box">
                    <h2>Annual Collection</h2>

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
                </div>

            </div>

            <h2>Unpaid Bills</h2>

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

        </div>
    </div>

    <script>
        new Chart(document.getElementById("collectionChart"), {
            type: "bar",
            data: {
                labels: <?php echo json_encode($collectionLabels); ?>,
                datasets: [{
                    label: "Monthly Collection",
                    data: <?php echo json_encode($collectionData); ?>,
                    borderWidth: 1
                }]
            }
        });

        new Chart(document.getElementById("usageChart"), {
            type: "line",
            data: {
                labels: <?php echo json_encode($usageLabels); ?>,
                datasets: [{
                    label: "Water Usage",
                    data: <?php echo json_encode($usageData); ?>,
                    borderWidth: 2
                }]
            }
        });
    </script>

</body>
</html>