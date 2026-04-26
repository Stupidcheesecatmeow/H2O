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
<<<<<<< HEAD
        $conn->query("UPDATE invoices SET status='unpaid' WHERE id='{$payment['invoice_id']}'");

=======

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
            INSERT INTO notifications (user_id, role_target, title, message, type, status)
            VALUES (
            '{$payment['user_id']}',
            'user',
            'Payment Rejected',
            'Your payment was rejected. Please review and submit again.',
            'payment',
            'unread'
            )
        ");

>>>>>>> be3ddca4134d56b51216987ef9432112f9f42a32
        echo "<script>alert('Payment rejected'); window.location='payments.php';</script>";
        exit();
    }
}

/* BARANGAY FILTER */
$barangay_filter = $_GET['barangay'] ?? "";

$where = "";
if ($barangay_filter != "") {
    $safe_barangay = $conn->real_escape_string($barangay_filter);
    $where = "WHERE u.barangay = '$safe_barangay'";
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
    ORDER BY u.barangay ASC, p.paid_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payments</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

    <div class="layout">

        <div class="sidebar">
            <h2>Accountant</h2>
            <ul>
                <li><a href="accountant_dashboard.php">Dashboard</a></li>
                <li><a href="payments.php">Payments</a></li>
                <li><a href="receipts.php">Receipts</a></li>
                <li><a href="reports_accountant.php">Reports</a></li>
                <li><a href="balance.php">Balance Tracker</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <div class="main">

            <h1>Payment Verification</h1>

            <div class="table-box">
                <form method="GET">
                    <select name="barangay">
                        <option value="">All Barangays</option>

                        <?php while($b = $barangays->fetch_assoc()): ?>
                            <option value="<?php echo $b['barangay']; ?>"
                                <?php if($barangay_filter == $b['barangay']) echo "selected"; ?>>
                                <?php echo $b['barangay']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <button type="submit">Filter</button>
                    <a href="payments.php">
                        <button type="button">Reset</button>
                    </a>
                </form>
            </div>

            <table>
                <tr>
                    <th>Transaction ID</th>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Barangay</th>
                    <th>Invoice No.</th>
                    <th>Amount</th>
                    <th>MOP</th>
                    <th>Reference No.</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Invoice</th>
                    <th>Action</th>
                </tr>

                <?php while($p = $payments->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $p['transaction_no']; ?></td>
                    <td><?php echo $p['user_id_display']; ?></td>
                    <td><?php echo $p['first_name']." ".$p['last_name']; ?></td>
                    <td><?php echo $p['barangay']; ?></td>
                    <td><?php echo $p['invoice_no']; ?></td>
                    <td>₱<?php echo number_format($p['amount'], 2); ?></td>
                    <td><?php echo $p['payment_method']; ?></td>
                    <td><?php echo $p['reference_no']; ?></td>
                    <td><?php echo $p['status']; ?></td>
                    <td><?php echo $p['paid_at']; ?></td>
                    <td>
                        <a href="print_invoice.php?id=<?php echo $p['invoice_id']; ?>" target="_blank">
                            <button type="button">Print Invoice</button>
                        </a>
                    </td>
                    <td>
                        <?php if($p['status'] == "pending"): ?>
                            <a href="?verify=<?php echo $p['id']; ?>&barangay=<?php echo urlencode($barangay_filter); ?>" onclick="return confirm('Verify this payment?')">
                                <button type="button">Verify</button>
                            </a>

                            <a href="?reject=<?php echo $p['id']; ?>&barangay=<?php echo urlencode($barangay_filter); ?>" onclick="return confirm('Reject this payment?')">
                                <button type="button">Reject</button>
                            </a>
                        <?php else: ?>
                            Done
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>

        </div>
    </div>

</body>
</html>