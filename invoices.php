<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: index.php");
    exit();
}

$admin_id = $_SESSION['user_id'];

/* RELEASE INVOICE */
if (isset($_GET['release_invoice'])) {
    $reading_id = $_GET['release_invoice'];

    $check = $conn->prepare("SELECT id FROM invoices WHERE reading_id=?");
    $check->bind_param("i", $reading_id);
    $check->execute();
    $existing = $check->get_result();

    if ($existing->num_rows > 0) {
        echo "<script>alert('Invoice already released'); window.location='invoices.php';</script>";
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM meter_readings WHERE id=?");
    $stmt->bind_param("i", $reading_id);
    $stmt->execute();
    $reading = $stmt->get_result()->fetch_assoc();

    if (!$reading) {
        echo "<script>alert('Meter reading not found'); window.location='invoices.php';</script>";
        exit();
    }

    $invoice_no = "INV-" . date("Ymd") . "-" . rand(1000, 9999);
    $consumption = $reading['consumption'];

    // RATE CALCULATION
$minimum_charge = 165.00;
$amount = 0;

if ($consumption <= 10) {
    $amount = $minimum_charge;
} elseif ($consumption <= 30) {
    $amount = $minimum_charge + (($consumption - 10) * 18.15);
} else {
    $amount = $minimum_charge + (20 * 18.15) + (($consumption - 30) * 22.28);
}

$rate = 0;
    $due_date = date("Y-m-d", strtotime("+15 days"));

    $insert = $conn->prepare("INSERT INTO invoices
    (user_id, reading_id, invoice_no, consumption, rate, amount, due_date, status, issued_by)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'unpaid', ?)");

    $insert->bind_param(
        "iisiddsi",
        $reading['user_id'],
        $reading_id,
        $invoice_no,
        $consumption,
        $rate,
        $amount,
        $due_date,
        $admin_id
    );

    if ($insert->execute()) {

    $notif = $conn->prepare("INSERT INTO notifications 
    (user_id, role_target, title, message, type, status, link)
    VALUES (?, 'user', 'New Bill Released', 'A new water bill has been released. Please check your billing page.', 'bill', 'unread', 'user_billing.php')");

    $notif->bind_param("i", $reading['user_id']);
    $notif->execute();

    echo "<script>alert('Billing invoice released'); window.location='invoices.php';</script>";
    exit();
    
} else {
    echo "<script>alert('Failed to release invoice'); window.location='invoices.php';</script>";
    exit();
}
}

/* UNRELEASED READINGS */
$readings = $conn->query("
    SELECT 
        mr.*, 
        u.first_name, 
        u.last_name, 
        u.barangay, 
        u.street, 
        u.meter_number
    FROM meter_readings mr
    JOIN users u ON mr.user_id = u.id
    WHERE mr.id NOT IN (SELECT reading_id FROM invoices)
    ORDER BY mr.created_at DESC
");

/* RELEASED INVOICES */
$invoices = $conn->query("
    SELECT 
        i.*, 
        u.first_name, 
        u.last_name, 
        u.barangay, 
        u.street, 
        u.meter_number
    FROM invoices i
    JOIN users u ON i.user_id = u.id
    ORDER BY i.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>H.O.H Invoices | Admin</title>
    <link rel="stylesheet" href="styles/invoices.css">
</head>
<body id="mainBody">

    <div class="main-content">
        <div class="header-row">
            <h1>BILLING INVOICE ISSUANCE</h1>
        </div>

        <!-- PENDING READINGS -->
        <h2>Pending Meter Readings</h2>
        <div class="glass-panel" id="pendingPanel">
            <div class="panel-title-bar">Awaiting Invoice Release</div>
            <div class="table-area">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Address</th>
                            <th>Meter No.</th>
                            <th>Prev</th>
                            <th>Curr</th>
                            <th>Cons.</th>
                            <th>Reading Date</th>
                            <th style="text-align:center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($r = $readings->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo strtoupper($r['first_name']." ".$r['last_name']); ?></strong></td>
                            <td><small><?php echo $r['barangay']." / ".$r['street']; ?></small></td>
                            <td><span style="font-family:monospace;"><?php echo $r['meter_number']; ?></span></td>
                            <td><?php echo $r['previous_reading']; ?></td>
                            <td><?php echo $r['current_reading']; ?></td>
                            <td style="color: var(--accent-blue); font-weight:bold;"><?php echo $r['consumption']; ?> m³</td>
                            <td><small><?php echo date('M d, Y', strtotime($r['reading_date'])); ?></small></td>
                            <td style="text-align:center">
                                <a href="?release_invoice=<?php echo $r['id']; ?>" onclick="return confirm('Release billing invoice?')">
                                    <button class="btn-action btn-release">Release</button>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RELEASED INVOICES -->
        <h2>Released Invoices</h2>
        <div class="glass-panel" id="historyPanel">
            <div class="panel-title-bar">Billing History Records</div>
            <div class="table-area">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Invoice No.</th>
                            <th>Customer</th>
                            <th>Meter No.</th>
                            <th>Consumption</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th style="text-align:center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($i = $invoices->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo $i['invoice_no']; ?></strong></td>
                            <td><?php echo $i['first_name']." ".$i['last_name']; ?></td>
                            <td><small><?php echo $i['meter_number']; ?></small></td>
                            <td><?php echo $i['consumption']; ?> m³</td>
                            <td style="color:var(--success); font-weight:800;">₱<?php echo number_format($i['amount'], 2); ?></td>
                            <td><small><?php echo date('M d, Y', strtotime($i['due_date'])); ?></small></td>
                            <td>
                                <span style="font-size:0.7rem; font-weight:bold; color: <?php echo ($i['status'] == 'paid') ? 'var(--success)' : 'var(--danger)'; ?>;">
                                    <?php echo strtoupper($i['status']); ?>
                                </span>
                            </td>
                            <td style="text-align:center">
                                <a href="print_invoice.php?id=<?php echo $i['id']; ?>" target="_blank">
                                    <button class="btn-action btn-print">Print</button>
                                </a>
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
                <a href="invoices.php" class="nav-item active">INVOICES</a>
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
        </script>
</body>
</html>
