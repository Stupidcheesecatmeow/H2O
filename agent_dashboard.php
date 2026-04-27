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

<link rel="stylesheet" href="dashboard.css">

<div class="layout">

    <div class="sidebar">
        <h2>Field Agent</h2>
        <ul>
            <li><a href="agent_dashboard.php">Dashboard</a></li>
            <li><a href="meter_reading.php">Meter Reading</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main">

        <h1>Agent Dashboard</h1>

        <!-- ASSIGNED BARANGAY -->
        <div class="card" style="margin-bottom:15px;">
            Assigned Barangay<br>
            <strong><?php echo $assigned_barangay; ?></strong>
        </div>

        <!-- DASHBOARD CARDS -->
        <div class="cards">
            <div class="card">Assigned Households<br><strong><?php echo $assigned; ?></strong></div>
            <div class="card">Completed Readings<br><strong><?php echo $completed; ?></strong></div>
            <div class="card">Pending Readings<br><strong><?php echo $pending; ?></strong></div>
        </div>

        <!-- CUSTOMER LIST -->
        <h2>Customers (<?php echo $assigned_barangay; ?>)</h2>

        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Street</th>
                <th>Meter</th>
            </tr>

            <?php while($c = $customers->fetch_assoc()): ?>
            <tr>
                <td><?php echo $c['user_code']; ?></td>
                <td><?php echo $c['first_name']." ".$c['last_name']; ?></td>
                <td><?php echo $c['street']; ?></td>
                <td><?php echo $c['meter_number']; ?></td>
            </tr>
            <?php endwhile; ?>
        </table>

        <!-- RECENT READINGS -->
        <h2>Recent Readings</h2>

        <table>
            <tr>
                <th>Customer</th>
                <th>Meter</th>
                <th>Previous</th>
                <th>Current</th>
                <th>Consumption</th>
                <th>Date</th>
            </tr>

            <?php while($h = $history->fetch_assoc()): ?>
            <tr>
                <td><?php echo $h['first_name']." ".$h['last_name']; ?></td>
                <td><?php echo $h['meter_number']; ?></td>
                <td><?php echo $h['previous_reading']; ?></td>
                <td><?php echo $h['current_reading']; ?></td>
                <td><?php echo $h['consumption']; ?></td>
                <td><?php echo $h['reading_date']; ?></td>
            </tr>
            <?php endwhile; ?>
        </table>

    </div>
</div>