<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "accountant") {
    header("Location: index.php");
    exit();
}

$receipts = $conn->query("
    SELECT 
        r.*, 
        p.id AS payment_id,
        p.amount,
        p.payment_method,
        p.reference_no,
        p.paid_at,
        i.id AS invoice_id,
        i.invoice_no,
        u.first_name,
        u.last_name
    FROM receipts r
    JOIN payments p ON r.payment_id = p.id
    JOIN invoices i ON p.invoice_id = i.id
    JOIN users u ON p.user_id = u.id
    ORDER BY r.issued_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>H.O.H Receipts</title>
    <link rel="stylesheet" href="styles/receipt.css">
</head>
<body id="mainBody">

    <div class="main-content">
        <div class="header-row">
            <h1>Receipt History</h1>
        </div>

        <div class="glass-panel">
            <div class="panel-title-bar">Generated Official Receipts</div>
            <div class="table-area">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Receipt No.</th>
                            <th>Inv No.</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>MOP</th>
                            <th>Issued At</th>
                            <th style="text-align:center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($r = $receipts->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo $r['receipt_no']; ?></strong></td>
                            <td><?php echo $r['invoice_no']; ?></td>
                            <td><?php echo strtoupper($r['first_name']." ".$r['last_name']); ?></td>
                            <td style="color: var(--success); font-weight:bold;">₱<?php echo number_format($r['amount'], 2); ?></td>
                            <td><?php echo $r['payment_method']; ?></td>
                            <td><small style="opacity:0.7"><?php echo date('M d, Y', strtotime($r['issued_at'])); ?></small></td>
                            <td style="text-align:center">
                                <a href="print_receipt.php?id=<?php echo $r['id']; ?>" target="_blank" class="btn-print">Receipt</a>
                                <a href="print_invoice.php?id=<?php echo $r['invoice_id']; ?>" target="_blank" class="btn-print" style="margin-left:5px;">Invoice</a>
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
            <h3>ACCOUNTANT</h3>
            <p style="font-size: 0.7rem; opacity: 0.6;">FINANCE DEPT</p>
        </div>
        <nav class="nav-menu" style="width: 100%;">
            <a href="accountant_dashboard.php" class="nav-item">DASHBOARD</a>
            <a href="payments.php" class="nav-item">PAYMENTS</a>
            <a href="receipts.php" class="nav-item active">RECEIPTS</a>
            <a href="reports_accountant.php" class="nav-item">REPORTS</a>
            <a href="balance.php" class="nav-item">BALANCE TRACKER</a>
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

        // Fast Fade-Out on click
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
