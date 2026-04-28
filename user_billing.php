<?php
session_start();
include "db.php";

if ($_SESSION['role'] != "user") {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id='$user_id'")->fetch_assoc();

$bills = $conn->query("
SELECT i.*, mr.previous_reading, mr.current_reading
FROM invoices i
JOIN meter_readings mr ON i.reading_id = mr.id
WHERE i.user_id='$user_id'
ORDER BY i.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>H.O.H My Billing</title>
    
    <link rel="stylesheet" href="styles/user_design.css">
</head>
<body id="mainBody">

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="header-row">
            <h1>BILLING HISTORY</h1>
        </div>

        <!-- GLASS TABLE -->
        <div class="glass-panel">
            <div class="panel-title-bar">Invoice Records</div>
            
            <div class="table-area">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Invoice No</th>
                            <th>Reading (m³)</th>
                            <th>Amount Due</th>
                            <th>Status</th>
                            <th style="text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($bills && $bills->num_rows > 0): ?>
                            <?php while($b = $bills->fetch_assoc()): 
                                // Determine the pill color based on status
                                $status_class = (strtolower($b['status']) == 'paid') ? 'paid' : 'unpaid';
                            ?>
                            <tr>
                                <td><strong><?php echo $b['invoice_no']; ?></strong></td>
                                <td>
                                    <span style="opacity: 0.6;"><?php echo $b['previous_reading'] ?? '0'; ?></span> 
                                    <span style="color: var(--accent-blue);">→</span> 
                                    <strong><?php echo $b['current_reading'] ?? '0'; ?></strong>
                                </td>
                                <td style="font-weight: 800; color: #2ecc71;">₱<?php echo number_format($b['amount'], 2); ?></td>
                                <td>
                                    
                                    <span class="status-pill <?php echo $status_class; ?>">
                                        <?php echo strtoupper($b['status']); ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <a href="print_invoice.php?id=<?php echo $b['id']; ?>" target="_blank">
                                        <button class="print-btn">PRINT</button>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px; opacity: 0.5;">
                                    No billing records found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SIDEBAR -->
    <div class="sidebar-right">
        <img src="assets/logo_name.png" class="side-logo">
        <div class="agent-info">
            <h3><?php echo strtoupper($user['first_name'] . " " . $user['last_name']); ?></h3>
            <p>CONSUMER ACCOUNT</p>
        </div>
        
        <nav class="nav-menu">
            <a href="user_dashboard.php" class="nav-item">DASHBOARD</a>
            <a href="user_notifications.php" class="nav-item">NOTIFICATIONS</a>
            <a href="user_billing.php" class="nav-item active">BILLING</a>
            <a href="user_payments.php" class="nav-item">PAYMENTS</a>
            <a href="user_history.php" class="nav-item">HISTORY</a>
            <a href="user_complaints.php" class="nav-item">COMPLAINTS</a>
            <a href="profile.php" class="nav-item">PROFILE</a>
        </nav>

        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn-container">LOG OUT</a>
        </div>
    </div>

    <script>
        // Trigger fade-in transition on load
        window.onload = () => {
            document.body.style.opacity = "1";
        };
    </script>
</body>
</html>
