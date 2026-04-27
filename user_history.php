<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "user") {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id='$user_id'")->fetch_assoc();

$payment_history = $conn->query("
    SELECT p.*, i.invoice_no, i.consumption
    FROM payments p
    JOIN invoices i ON p.invoice_id = i.id
    WHERE p.user_id='$user_id'
    ORDER BY p.paid_at DESC
");

$receipts = $conn->query("
    SELECT r.*, p.amount, p.payment_method, i.invoice_no
    FROM receipts r
    JOIN payments p ON r.payment_id = p.id
    JOIN invoices i ON p.invoice_id = i.id
    WHERE p.user_id='$user_id'
    ORDER BY r.issued_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History | H2O</title>
    <link rel="stylesheet" href="user_design.css">
</head>
<body id="mainBody">

    <!-- MAIN CONTENT AREA -->
    <div class="main-content">
        <div class="header-row">
            <h1>PAYMENT HISTORY</h1>
        </div>

        <div class="glass-panel">
            <div class="panel-title-bar">Recent Transactions</div>
            <div class="table-area">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Invoice No.</th>
                            <th>Amount Paid</th>
                            <th>Method</th>
                            <th>Consumption</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($ph = $payment_history->fetch_assoc()): 
                            $statClass = (strtolower($ph['status']) == 'verified') ? 'status-verified' : 'status-pending';
                        ?>
                        <tr>
                            <td style="font-size: 0.75rem; opacity: 0.7;"><?php echo date('M d, Y', strtotime($ph['paid_at'])); ?></td>
                            <td><span class="invoice-tag"><?php echo $ph['invoice_no']; ?></span></td>
                            <td style="font-weight: bold; color: #2ecc71;">₱<?php echo number_format($ph['amount'], 2); ?></td>
                            <td><?php echo $ph['payment_method']; ?></td>
                            <td><?php echo $ph['consumption']; ?> m³</td>
                            <td><span class="<?php echo $statClass; ?>"><?php echo strtoupper($ph['status']); ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="header-row">
            <h1>OFFICIAL RECEIPTS</h1>
        </div>

        <div class="glass-panel">
            <div class="panel-title-bar">Issued Receipts</div>
            <div class="table-area">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Receipt No.</th>
                            <th>Invoice No.</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Date Issued</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($r = $receipts->fetch_assoc()): ?>
                        <tr>
                            <td style="font-weight: bold; color: var(--accent-blue);"><?php echo $r['receipt_no']; ?></td>
                            <td><?php echo $r['invoice_no']; ?></td>
                            <td>₱<?php echo number_format($r['amount'], 2); ?></td>
                            <td><?php echo $r['payment_method']; ?></td>
                            <td style="font-size: 0.75rem; opacity: 0.7;"><?php echo date('M d, Y', strtotime($r['issued_at'])); ?></td>
                            <td style="text-align: right;">
                                <a href="print_receipt.php?id=<?php echo $r['id']; ?>" target="_blank">
                                    <button class="print-btn">PRINT RECEIPT</button>
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
        <div class="agent-info">
            <h3><?php echo strtoupper($user['first_name'] . " " . $user['last_name']); ?></h3>
            <p>CONSUMER ACCOUNT</p>
        </div>
        <nav class="nav-menu">
            <a href="user_dashboard.php" class="nav-item">DASHBOARD</a>
            <a href="user_notifications.php" class="nav-item">NOTIFICATIONS</a>
            <a href="user_billing.php" class="nav-item">BILLING</a>
            <a href="user_payments.php" class="nav-item">PAYMENT</a>
            <a href="user_history.php" class="nav-item active">HISTORY</a>
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
