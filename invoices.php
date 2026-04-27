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
<html>
<head>
    <title>Invoices</title>
    <link rel="stylesheet" href="dashboard.css">
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

            <h1>Billing Invoice Issuance</h1>

            <h2>Pending Meter Readings</h2>

            <table>
                <tr>
                    <th>Customer</th>
                    <th>Address</th>
                    <th>Meter No.</th>
                    <th>Previous</th>
                    <th>Current</th>
                    <th>Consumption</th>
                    <th>Reading Date</th>
                    <th>Action</th>
                </tr>

                <?php while($r = $readings->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $r['first_name']." ".$r['last_name']; ?></td>
                    <td><?php echo $r['barangay']." / ".$r['street']; ?></td>
                    <td><?php echo $r['meter_number']; ?></td>
                    <td><?php echo $r['previous_reading']; ?></td>
                    <td><?php echo $r['current_reading']; ?></td>
                    <td><?php echo $r['consumption']; ?> m³</td>
                    <td><?php echo $r['reading_date']; ?></td>
                    <td>
                        <a href="?release_invoice=<?php echo $r['id']; ?>" onclick="return confirm('Release billing invoice?')">
                            <button type="button">Release Invoice</button>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>

            <h2>Released Invoices</h2>

            <table>
                <tr>
                    <th>Invoice No.</th>
                    <th>Customer</th>
                    <th>Meter No.</th>
                    <th>Consumption</th>
                    <th>Amount</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Print</th>
                </tr>

                <?php while($i = $invoices->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $i['invoice_no']; ?></td>
                    <td><?php echo $i['first_name']." ".$i['last_name']; ?></td>
                    <td><?php echo $i['meter_number']; ?></td>
                    <td><?php echo $i['consumption']; ?> m³</td>
                    <td>₱<?php echo number_format($i['amount'], 2); ?></td>
                    <td><?php echo $i['due_date']; ?></td>
                    <td><?php echo strtoupper($i['status']); ?></td>
                    <td>
                        <a href="print_invoice.php?id=<?php echo $i['id']; ?>" target="_blank">
                            <button type="button">Print</button>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>

        </div>
    </div>

</body>
</html>