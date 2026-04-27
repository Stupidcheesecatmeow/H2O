<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "accountant") {
    header("Location: index.php");
    exit();
}

$unpaid = $conn->query("
    SELECT 
        i.*,
        u.first_name,
        u.last_name,
        u.meter_number,
        DATEDIFF(CURDATE(), i.due_date) AS overdue_days
    FROM invoices i
    JOIN users u ON i.user_id = u.id
    WHERE i.status!='paid'
    ORDER BY i.due_date ASC
");

$total_unpaid = $conn->query("
    SELECT SUM(amount) AS total
    FROM invoices
    WHERE status!='paid'
")->fetch_assoc()['total'] ?? 0;

$total_overdue = $conn->query("
    SELECT COUNT(*) AS total
    FROM invoices
    WHERE status!='paid' AND due_date < CURDATE()
")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Balance Tracker | H2O</title>
    <link rel="stylesheet" href="balance.css">
</head>
<body id="mainBody">

    <div class="main-content">
        <div class="header-row">
            <h1>Balance Tracker</h1>
        </div>

        <!-- STAT CARDS -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-label">Total Unpaid Balance</span>
                <span class="stat-value" style="color: var(--danger);">₱<?php echo number_format($total_unpaid, 2); ?></span>
            </div>

            <div class="stat-card">
                <span class="stat-label">Overdue Accounts</span>
                <span class="stat-value" style="color: var(--warning);"><?php echo $total_overdue; ?></span>
            </div>
        </div>

        <!-- TABLE SECTION -->
        <div class="glass-panel">
            <div class="panel-title-bar">Delinquent Account List</div>
            <div class="table-area">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Meter No.</th>
                            <th>Invoice</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Overdue</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($u = $unpaid->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo strtoupper($u['first_name']." ".$u['last_name']); ?></strong></td>
                            <td><small style="font-family: monospace;"><?php echo $u['meter_number']; ?></small></td>
                            <td><?php echo $u['invoice_no']; ?></td>
                            <td style="color: var(--white); font-weight:bold;">₱<?php echo number_format($u['amount'], 2); ?></td>
                            <td><?php echo date('M d, Y', strtotime($u['due_date'])); ?></td>
                            <td>
                                <span class="overdue-badge">
                                    <?php echo ($u['overdue_days'] > 0) ? $u['overdue_days'] : 0; ?> Days
                                </span>
                            </td>
                            <td style="color: var(--warning); font-weight: 800; font-size: 0.7rem;">
                                <?php echo strtoupper($u['status']); ?>
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
            <h3>ACCOUNTANT</h3>
            <p style="font-size: 0.7rem; opacity: 0.6;">FINANCE DEPT</p>
        </div>
        <nav class="nav-menu" style="width: 100%;">
            <a href="accountant_dashboard.php" class="nav-item">DASHBOARD</a>
            <a href="payments.php" class="nav-item">PAYMENTS</a>
            <a href="receipts.php" class="nav-item">RECEIPTS</a>
            <a href="reports_accountant.php" class="nav-item">REPORTS</a>
            <a href="balance.php" class="nav-item active">BALANCE TRACKER</a>
            <a href="profile.php" class="nav-item">PROFILE</a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn-container">LOG OUT</a>
        </div>
    </div>

    <script>
        // Turbo Fade-In
        window.addEventListener('DOMContentLoaded', () => {
            document.body.classList.add('fade-in');
        });

        // Fast Fade-Out
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
