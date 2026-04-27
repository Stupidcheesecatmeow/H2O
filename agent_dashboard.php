<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "agent") {
    header("Location: index.php");
    exit();
}

$agent_id = $_SESSION['user_id'];

/* GET ASSIGNMENT */
$assignment = $conn->query("
    SELECT area, total_households 
    FROM agent_assignments 
    WHERE agent_id='$agent_id'
")->fetch_assoc();

$assigned_barangay = $assignment['area'] ?? "Not Assigned";
$assigned = $assignment['total_households'] ?? 0;

/* SAVE READING */
if(isset($_POST['save'])){
    $user_id = $_POST['user_id'];
    $curr = $_POST['current'];

    $last = $conn->query("
        SELECT current_reading 
        FROM meter_readings 
        WHERE user_id='$user_id'
        ORDER BY reading_date DESC, id DESC
        LIMIT 1
    ")->fetch_assoc();

    $prev = $last['current_reading'] ?? 0;
    $consumption = $curr - $prev;
    $date = date("Y-m-d");

    if($consumption < 0){
        echo "<script>alert('Invalid reading'); window.location='agent_dashboard.php';</script>";
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO meter_readings 
    (user_id, agent_id, previous_reading, current_reading, consumption, reading_date, status)
    VALUES (?, ?, ?, ?, ?, ?, 'completed')");

    $stmt->bind_param("iiiiis", $user_id, $agent_id, $prev, $curr, $consumption, $date);
    $stmt->execute();

    /* NOTIFICATION */
    $conn->query("
        INSERT INTO notifications (user_id, role_target, title, message, type, status)
        VALUES ('$agent_id','admin','Meter Reading Completed','Field agent submitted reading','reading','unread')
    ");

    echo "<script>alert('Reading saved'); window.location='agent_dashboard.php';</script>";
    exit();
}

/* COMPLETED */
$completed = $conn->query("
    SELECT COUNT(*) as total 
    FROM meter_readings mr
    JOIN users u ON mr.user_id = u.id
    WHERE mr.agent_id='$agent_id'
    AND u.barangay='$assigned_barangay'
")->fetch_assoc()['total'];

/* PENDING */
$pending = max($assigned - $completed, 0);

/* CUSTOMER LIST (FILTERED) */
$customers = $conn->query("
    SELECT * 
    FROM users 
    WHERE role='user' 
    AND status='active'
    AND barangay='$assigned_barangay'
");

/* RECENT READINGS */
$history = $conn->query("
    SELECT mr.*, u.first_name, u.last_name, u.street, u.meter_number
    FROM meter_readings mr
    JOIN users u ON mr.user_id = u.id
    WHERE mr.agent_id='$agent_id'
    AND u.barangay='$assigned_barangay'
    ORDER BY mr.created_at DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard | H2O</title>
    <link rel="stylesheet" href="agent_dashboard.css">
</head>
<body id="mainBody">

    <!-- MAIN CONTENT AREA -->
    <div class="main-content">
        <div class="header-row">
            <h1>FIELD AGENT DASHBOARD</h1>
        </div>

        <!-- STATS GRID -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-label">Assigned Barangay</span>
                <span class="stat-value"><?php echo strtoupper($assigned_barangay); ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Total Households</span>
                <span class="stat-value"><?php echo $assigned; ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Readings Completed</span>
                <span class="stat-value" style="color: var(--success);"><?php echo $completed; ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Pending Readings</span>
                <span class="stat-value" style="color: #f1c40f;"><?php echo $pending; ?></span>
            </div>
        </div>

        <!-- CUSTOMER LIST PANEL -->
        <div class="glass-panel">
            <div class="panel-title-bar">Active Customers - <?php echo $assigned_barangay; ?></div>
            <div class="table-area">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Full Name</th>
                            <th>Street</th>
                            <th>Meter No.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($customers->num_rows > 0): ?>
                            <?php while($c = $customers->fetch_assoc()): ?>
                            <tr>
                                <td><span class="type-badge"><?php echo $c['user_code']; ?></span></td>
                                <td><strong><?php echo strtoupper($c['first_name']." ".$c['last_name']); ?></strong></td>
                                <td><?php echo strtoupper($c['street']); ?></td>
                                <td><?php echo $c['meter_number']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align:center; opacity:0.5; padding:20px;">No active customers found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RECENT READINGS HISTORY -->
        <div class="glass-panel">
            <div class="panel-title-bar">Recent Reading History</div>
            <div class="table-area">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Meter</th>
                            <th>Prev</th>
                            <th>Curr</th>
                            <th>Cons.</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($h = $history->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $h['first_name']." ".$h['last_name']; ?></td>
                            <td><?php echo $h['meter_number']; ?></td>
                            <td><?php echo $h['previous_reading']; ?></td>
                            <td><?php echo $h['current_reading']; ?></td>
                            <td style="color: var(--accent-blue); font-weight:bold;"><?php echo $h['consumption']; ?> m³</td>
                            <td style="font-size:0.75rem; opacity:0.7;"><?php echo date('M d, Y', strtotime($h['reading_date'])); ?></td>
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
        <div class="agent-info">
            <h3>AGENT PORTAL</h3>
            <p>FIELD AGENT</p>
        </div>
        
        <nav class="nav-menu">
            <a href="agent_dashboard.php" class="nav-item active">DASHBOARD</a>
            <a href="meter_reading.php" class="nav-item">METER READING</a>
            <a href="profile.php" class="nav-item">PROFILE</a>
        </nav>

        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn-container">LOG OUT</a>
        </div>
    </div>

    <script>
        window.onload = () => { document.body.style.opacity = "1"; };
    </script>
</body>
</html>
