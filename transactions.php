<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: index.php");
    exit();
}

$transactions = $conn->query("
    SELECT 
        p.*,
        i.id AS invoice_id,
        i.invoice_no,
        u.first_name,
        u.last_name,
        u.meter_number
    FROM payments p
    JOIN invoices i ON p.invoice_id = i.id
    JOIN users u ON p.user_id = u.id
    ORDER BY p.paid_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction Monitoring | H2O</title>
    <link rel="stylesheet" href="styles/transaction.css">
</head>
<body id="mainBody">

    <div class="main-content">
        <div class="header-row">
            <h1>TRANSACTION MONITORING</h1>
        </div>

        <!-- TRANSACTIONS TABLE PANEL -->
        <div class="glass-panel">
            <div class="panel-title-bar">System-Wide Payment Records</div>
            <div class="table-area">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>TRX ID</th>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Meter No.</th>
                            <th>Amount</th>
                            <th>MOP</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th style="text-align:center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($t = $transactions->fetch_assoc()): 
                            $statusClass = ($t['status'] == 'verified') ? 'paid' : 'unpaid';
                        ?>
                        <tr>
                            <td><span class="id-badge"><?php echo $t['transaction_no']; ?></span></td>
                            <td><small><?php echo $t['invoice_no']; ?></small></td>
                            <td><strong><?php echo strtoupper($t['first_name']." ".$t['last_name']); ?></strong></td>
                            <td><small style="font-family: monospace;"><?php echo $t['meter_number']; ?></small></td>
                            <td style="color: var(--success); font-weight: bold;">₱<?php echo number_format($t['amount'], 2); ?></td>
                            <td><?php echo $t['payment_method']; ?></td>
                            <td><span class="status-pill <?php echo $statusClass; ?>"><?php echo strtoupper($t['status']); ?></span></td>
                            <td><small style="opacity: 0.7;"><?php echo date('M d, Y', strtotime($t['paid_at'])); ?></small></td>
                            <td style="text-align:center">
                                <a href="print_invoice.php?id=<?php echo $t['invoice_id']; ?>" target="_blank">
                                    <button class="btn-print">PRINT</button>
                                </a>
                            </td>
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
            <a href="transactions.php" class="nav-item active">TRANSACTIONS</a>
            <a href="complaints_admin.php" class="nav-item">COMPLAINTS</a>
            <a href="reports.php" class="nav-item">REPORTS</a>
            <a href="profile.php" class="nav-item">PROFILE</a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn-container">LOG OUT</a>
        </div>
    </div>

    <script>
        window.onload = () => { document.body.style.opacity = "1"; };
        
        // Turbo Transitions
        document.querySelectorAll('.nav-item, .logout-btn-container').forEach(link => {
            link.addEventListener('click', function(e) {
                const target = this.getAttribute('href');
                if (target && target !== '#') {
                    e.preventDefault();
                    document.body.style.opacity = "0";
                    setTimeout(() => { window.location.href = target; }, 200);
                }
            });
        });
    </script>
</body>
</html>
