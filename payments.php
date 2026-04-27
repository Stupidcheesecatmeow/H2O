<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "accountant") {
    header("Location: index.php");
    exit();
}

$accountant_id = $_SESSION['user_id'];

/* VERIFY PAYMENT */
if (isset($_GET['verify'])) {
    $payment_id = $_GET['verify'];

    $payment = $conn->query("SELECT * FROM payments WHERE id='$payment_id'")->fetch_assoc();

    if ($payment) {
        $conn->query("UPDATE payments SET status='verified', verified_by='$accountant_id' WHERE id='$payment_id'");
        $conn->query("UPDATE invoices SET status='paid' WHERE id='{$payment['invoice_id']}'");

        $receipt_no = "REC-" . date("Ymd") . "-" . rand(1000,9999);

        $check = $conn->query("SELECT id FROM receipts WHERE payment_id='$payment_id'");

        if ($check->num_rows == 0) {
            $conn->query("INSERT INTO receipts (payment_id, receipt_no) VALUES ('$payment_id', '$receipt_no')");
        }

        $conn->query("
            INSERT INTO notifications (user_id, role_target, title, message, type, status, link)
            VALUES (
                '{$payment['user_id']}',
                'user',
                'Payment Verified',
                'Your payment has been verified. Receipt is now available.',
                'payment',
                'unread',
                'user_history.php'
            )
        ");

        echo "<script>alert('Payment verified and receipt generated'); window.location='payments.php';</script>";
        exit();
    }
}

/* REJECT PAYMENT */
if (isset($_GET['reject'])) {
    $payment_id = $_GET['reject'];

    $payment = $conn->query("SELECT * FROM payments WHERE id='$payment_id'")->fetch_assoc();

    if ($payment) {
        $conn->query("UPDATE payments SET status='rejected', verified_by='$accountant_id' WHERE id='$payment_id'");

        $conn->query("
            INSERT INTO notifications (user_id, role_target, title, message, type, status)
            VALUES (
            '{$payment['user_id']}',
            'user',
            'Payment Verified',
            'Your payment has been verified. Receipt is now available.',
            'payment',
            'unread'
            )
        ");
        $conn->query("UPDATE invoices SET status='unpaid' WHERE id='{$payment['invoice_id']}'");

        $conn->query("
            INSERT INTO notifications (user_id, role_target, title, message, type, status, link)
            VALUES (
                '{$payment['user_id']}',
                'user',
                'Payment Rejected',
                'Your payment was rejected. Please review and submit again.',
                'payment',
                'unread',
                'user_payments.php'
            )
        ");

        echo "<script>alert('Payment rejected'); window.location='payments.php';</script>";
        exit();
    }
}

/* BARANGAY FILTER */
$barangay_filter = $_GET['barangay'] ?? "";

$where = "";
$order_by = "ORDER BY p.paid_at DESC";

if ($barangay_filter != "") {
    $safe_barangay = $conn->real_escape_string($barangay_filter);
    $where = "WHERE u.barangay = '$safe_barangay'";
    $order_by = "ORDER BY p.paid_at DESC";
}

/* BARANGAY LIST */
$barangays = $conn->query("
    SELECT DISTINCT barangay 
    FROM users 
    WHERE role='user' 
    AND barangay IS NOT NULL 
    AND barangay!=''
    ORDER BY barangay ASC
");

/* PAYMENT TABLE */
$payments = $conn->query("
    SELECT 
        p.*, 
        i.id AS invoice_id,
        i.invoice_no,
        u.id AS user_id_display,
        u.first_name,
        u.last_name,
        u.barangay
    FROM payments p
    JOIN invoices i ON p.invoice_id = i.id
    JOIN users u ON p.user_id = u.id
    $where
    $order_by
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Verification | H2O</title>
    <link rel="stylesheet" href="payment.css">
</head>
<body>

    <div class="main-content">
        <div class="header-row">
            <h1>Payment Verification</h1>
        </div>

        <!-- FILTER PANEL -->
        <div class="glass-panel" style="margin-bottom: 15px;">
            <div class="panel-title-bar" style="padding: 8px 20px;">FILTER BY BARANGAY</div>
            <div class="filter-panel-body">
                <form method="GET" style="display: flex; gap: 10px; align-items: center;">
                    <select name="barangay">
                        <option value="">All Barangays</option>
                        <?php while($b = $barangays->fetch_assoc()): ?>
                            <option value="<?= $b['barangay'] ?>" <?= ($barangay_filter == $b['barangay']) ? "selected" : "" ?>>
                                <?= $b['barangay'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    
                    <button type="submit" class="btn-action btn-verify btn-filter">FILTER</button>
                    
                    <a href="payments.php" style="text-decoration:none;">
                        <button type="button" class="btn-action btn-filter" style="background:rgba(255,255,255,0.1); color:white;">
                            RESET
                        </button>
                    </a>
                </form>
            </div>
        </div>

        <!-- TABLE PANEL -->
        <div class="glass-panel">
            <div class="panel-title-bar">Transaction Queue</div>
            <div class="table-area">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Transaction</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>MOP</th>
                            <th>Proof</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($p = $payments->fetch_assoc()): ?>
                        <tr>
                            <td><small><?= $p['transaction_no'] ?></small></td>
                            <td><strong><?= strtoupper($p['first_name'] . " " . $p['last_name']) ?></strong><br><small><?= $p['barangay'] ?></small></td>
                            <td style="color: var(--success); font-weight: 800;">₱<?= number_format($p['amount'], 2) ?></td>
                            <td><?= $p['payment_method'] ?></td>
                            <td>
                                <?php if($p['payment_method'] != "Cash" && !empty($p['proof_image'])): ?>
                                    <img src="payment_proofs/<?= $p['proof_image'] ?>" style="width:35px; height:35px; border-radius:4px; cursor:pointer; border:1px solid var(--accent-blue);" onclick="window.open(this.src)">
                                <?php else: ?>
                                    <span style="opacity:0.4">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="status-<?= $p['status'] ?>"><?= strtoupper($p['status']) ?></span></td>
                            <td>
                                <?php if($p['status'] == "pending"): ?>
                                    <a href="?verify=<?= $p['id'] ?>" onclick="return confirm('Verify?')"><button class="btn-action btn-verify">VERIFY</button></a>
                                    <a href="?reject=<?= $p['id'] ?>" onclick="return confirm('Reject?')"><button class="btn-action btn-reject">REJECT</button></a>
                                <?php else: ?>
                                    <span style="opacity:0.5; font-size:0.7rem;">PROCESSED</span>
                                <?php endif; ?>
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
            <a href="payments.php" class="nav-item active">PAYMENTS</a>
            <a href="receipts.php" class="nav-item">RECEIPTS</a>
            <a href="reports_accountant.php" class="nav-item">REPORTS</a>
            <a href="balance.php" class="nav-item">BALANCE TRACKER</a>
            <a href="profile.php" class="nav-item">PROFILE</a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn-container">LOG OUT</a>
        </div>
    </div>

    <script>
        // Fade in on load
        window.addEventListener('DOMContentLoaded', () => {
            document.body.classList.add('fade-in');
        });

        // Fade out on link click
        document.querySelectorAll('.nav-item, .logout-btn-container').forEach(link => {
            link.addEventListener('click', function(e) {
                const target = this.getAttribute('href');
                if (target && target !== '#') {
                    e.preventDefault();
                    document.body.classList.add('fade-out');
                    setTimeout(() => { window.location.href = target; }, 200);
                }
            });
        });
    </script>



</body>
</html>
