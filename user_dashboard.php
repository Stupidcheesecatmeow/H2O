<?php
session_start();
include "db.php";
include "barangay.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "user") {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$user = $conn->query("SELECT * FROM users WHERE id='$user_id'")->fetch_assoc();

/* SUBMIT PAYMENT */
if (isset($_POST['pay'])) {
    $invoice_id = $_POST['invoice_id'];
    $amount = $_POST['amount'];
    $mop = $_POST['payment_method'];
    $ref = "TRX-" . date("Ymd") . "-" . rand(1000, 9999);

    $stmt = $conn->prepare("INSERT INTO payments 
    (invoice_id, user_id, transaction_no, amount, payment_method, reference_no, status)
    VALUES (?, ?, ?, ?, ?, ?, 'pending')");

    $stmt->bind_param("iisdss", $invoice_id, $user_id, $ref, $amount, $mop, $ref);
    $stmt->execute();

    $conn->query("UPDATE invoices SET status='pending_verification' WHERE id='$invoice_id'");

    echo "<script>alert('Payment submitted. Waiting for confirmation. Transaction ID: $ref'); window.location='user_dashboard.php';</script>";
    exit();
}

/* SUBMIT COMPLAINT */
if (isset($_POST['submit_complaint'])) {
    $subject = $_POST['complaint_type'];
    $message = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO complaints (user_id, subject, message, status)
    VALUES (?, ?, ?, 'open')");

    $stmt->bind_param("iss", $user_id, $subject, $message);
    $stmt->execute();

    echo "<script>alert('Complaint submitted'); window.location='user_dashboard.php';</script>";
    exit();
}

/* ANNOUNCEMENTS */
$announcements = $conn->query("
    SELECT * FROM announcements 
    WHERE target_type='everyone' 
    OR (target_type='barangay' AND barangay='{$user['barangay']}')
    ORDER BY created_at DESC
");

/* CURRENT BILL */
$current_bill = $conn->query("
    SELECT * FROM invoices 
    WHERE user_id='$user_id' AND status!='paid'
    ORDER BY due_date ASC 
    LIMIT 1
")->fetch_assoc();

/* ALL BILLS */
$bills = $conn->query("
    SELECT i.*, mr.previous_reading, mr.current_reading, mr.reading_date
    FROM invoices i
    JOIN meter_readings mr ON i.reading_id = mr.id
    WHERE i.user_id='$user_id'
    ORDER BY i.created_at DESC
");

/* PAYABLE BILLS */
$payable = $conn->query("
    SELECT * FROM invoices
    WHERE user_id='$user_id' AND status='unpaid'
    ORDER BY due_date ASC
");

/* PAYMENT HISTORY */
$payment_history = $conn->query("
    SELECT p.*, i.invoice_no, i.consumption
    FROM payments p
    JOIN invoices i ON p.invoice_id = i.id
    WHERE p.user_id='$user_id'
    ORDER BY p.paid_at DESC
");

/* PAYMENT CONFIRMATION */
$latest_payment = $conn->query("
    SELECT p.*, u.first_name, u.last_name
    FROM payments p
    JOIN users u ON p.user_id = u.id
    WHERE p.user_id='$user_id'
    ORDER BY p.paid_at DESC
    LIMIT 1
")->fetch_assoc();

/* RECEIPTS */
$receipts = $conn->query("
    SELECT r.*, p.amount, p.payment_method, i.invoice_no
    FROM receipts r
    JOIN payments p ON r.payment_id = p.id
    JOIN invoices i ON p.invoice_id = i.id
    WHERE p.user_id='$user_id'
    ORDER BY r.issued_at DESC
");

/* COMPLAINTS */
$conn->query("
INSERT INTO notifications (user_id, role_target, title, message, type, status)
VALUES (
    '$user_id',
    'admin',
    'New Complaint',
    'A new complaint has been submitted.',
    'complaint',
    'unread'
)
");

/* GRAPH */
$usage = $conn->query("
    SELECT consumption, created_at
    FROM invoices
    WHERE user_id='$user_id'
    ORDER BY created_at ASC
");

$months = [];
$cons = [];

while ($u = $usage->fetch_assoc()) {
    $months[] = date("M", strtotime($u['created_at']));
    $cons[] = $u['consumption'];
}

/* STATUS */
$overdue_count = $conn->query("
    SELECT COUNT(*) AS total
    FROM invoices
    WHERE user_id='$user_id' AND status!='paid' AND due_date < CURDATE()
")->fetch_assoc()['total'];

$account_status = ($overdue_count > 0) ? "Overdue" : $user['status'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="layout">

<div class="sidebar collapsed" id="sidebar">
    <div class="top-bar">
        <button type="button" id="toggleBtn">
            <img src="assets/spongy.png" alt="logo" class="balew">
        </button>
    </div>

    <div class="profile">
        <h3><?php echo $user['first_name']; ?></h3>
        <p><?php echo $user['meter_number']; ?></p>
    </div>

    <ul>
        
    <li><a href="user_dashboard.php">Dashboard</a></li>
    <li><a href="user_billing.php">Billing</a></li>
    <li><a href="user_payments.php">Payment</a></li>
    <li><a href="user_history.php">History</a></li>
    <li><a href="user_complaints.php">Complaints</a></li>
    <li><a href="profile.php">Profile</a></li>
    <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="main">

<h1>Customer Dashboard</h1>

<div class="cards">
    <div class="card">
        Current Bill<br>
        <strong>
            ₱<?php echo number_format($current_bill['amount'] ?? 0, 2); ?>
        </strong>
        <br>
        Due: <?php echo $current_bill['due_date'] ?? "No bill"; ?>
    </div>

    <div class="card">
        Account Status<br>
        <strong><?php echo $account_status; ?></strong>
    </div>

    <div class="card">
        Meter Number<br>
        <strong><?php echo $user['meter_number']; ?></strong>
    </div>
</div>

<h2>Notification Panel</h2>

<div class="table-box">
    <h3>Announcements</h3>
    <?php while($a = $announcements->fetch_assoc()): ?>
        <p>
            <strong><?php echo $a['title']; ?></strong><br>
            <?php echo $a['message']; ?><br>
            <small><?php echo $a['announcement_date']; ?></small>
        </p>
        <hr>
    <?php endwhile; ?>

    <h3>Overdue Notice</h3>
    <?php if($overdue_count > 0): ?>
        <p style="color:red;">You have overdue bill/s. Please settle your payment.</p>
    <?php else: ?>
        <p>No overdue bills.</p>
    <?php endif; ?>

    <h3>Payment Confirmation</h3>
    <?php if($latest_payment): ?>
        <p>
            Transaction ID: <strong><?php echo $latest_payment['transaction_no']; ?></strong><br>
            Status: <strong><?php echo $latest_payment['status']; ?></strong>
        </p>
    <?php else: ?>
        <p>No payment submitted yet.</p>
    <?php endif; ?>
</div>

<h2>Water Usage Graph</h2>
<div class="box big">
    <canvas id="usageChart"></canvas>
</div>

<h2>Billing Page</h2>

<table>
<tr>
    <th>Billing Period</th>
    <th>Invoice No.</th>
    <th>Previous</th>
    <th>Current</th>
    <th>Consumption</th>
    <th>Amount Due</th>
    <th>Status</th>
    <th>Invoice</th>
</tr>

<?php while($b = $bills->fetch_assoc()): ?>
<tr>
    <td><?php echo date("M Y", strtotime($b['created_at'])); ?></td>
    <td><?php echo $b['invoice_no']; ?></td>
    <td><?php echo $b['previous_reading']; ?></td>
    <td><?php echo $b['current_reading']; ?></td>
    <td><?php echo $b['consumption']; ?></td>
    <td>₱<?php echo number_format($b['amount'], 2); ?></td>
    <td><?php echo $b['status']; ?></td>
    <td>
        <a href="print_invoice.php?id=<?php echo $b['id']; ?>" target="_blank">
            <button type="button">View / Print</button>
        </a>
    </td>
</tr>
<?php endwhile; ?>
</table>

<h2>Payment Page</h2>

<div class="table-box">
<form method="POST">
    <label>Select Bill</label>
    <select name="invoice_id" required>
        <option value="">Select unpaid bill</option>
        <?php while($p = $payable->fetch_assoc()): ?>
            <option value="<?php echo $p['id']; ?>" data-amount="<?php echo $p['amount']; ?>">
                <?php echo $p['invoice_no']; ?> - ₱<?php echo number_format($p['amount'], 2); ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Amount to Pay</label>
    <input type="number" step="0.01" name="amount" placeholder="Amount" required>

    <label>Mode of Payment</label>
    <select name="payment_method" required>
        <option value="">Select MOP</option>
        <option value="Cash">Cash</option>
        <option value="GCash">GCash</option>
        <option value="Maya">Maya</option>
        <option value="Bank Transfer">Bank Transfer</option>
    </select>

    <button type="submit" name="pay">Submit Payment</button>
</form>
</div>

<h2>Payment Confirmation</h2>

<table>
<tr>
    <th>Transaction ID</th>
    <th>Name</th>
    <th>Amount</th>
    <th>MOP</th>
    <th>Date</th>
    <th>Payment Status</th>
</tr>

<?php if($latest_payment): ?>
<tr>
    <td><?php echo $latest_payment['transaction_no']; ?></td>
    <td><?php echo $latest_payment['first_name']." ".$latest_payment['last_name']; ?></td>
    <td>₱<?php echo number_format($latest_payment['amount'], 2); ?></td>
    <td><?php echo $latest_payment['payment_method']; ?></td>
    <td><?php echo $latest_payment['paid_at']; ?></td>
    <td><?php echo $latest_payment['status']; ?></td>
</tr>
<?php endif; ?>
</table>

<h2>Payment History</h2>

<table>
<tr>
    <th>Date</th>
    <th>Amount Paid</th>
    <th>MOP</th>
    <th>Reading</th>
    <th>Status</th>
</tr>

<?php while($ph = $payment_history->fetch_assoc()): ?>
<tr>
    <td><?php echo $ph['paid_at']; ?></td>
    <td>₱<?php echo number_format($ph['amount'], 2); ?></td>
    <td><?php echo $ph['payment_method']; ?></td>
    <td><?php echo $ph['consumption']; ?></td>
    <td><?php echo $ph['status']; ?></td>
</tr>
<?php endwhile; ?>
</table>

<h2>Receipts</h2>

<table>
<tr>
    <th>Receipt No.</th>
    <th>Invoice No.</th>
    <th>Amount</th>
    <th>MOP</th>
    <th>Date Issued</th>
</tr>

<?php while($r = $receipts->fetch_assoc()): ?>
<tr>
    <td><?php echo $r['receipt_no']; ?></td>
    <td><?php echo $r['invoice_no']; ?></td>
    <td>₱<?php echo number_format($r['amount'], 2); ?></td>
    <td><?php echo $r['payment_method']; ?></td>
    <td><?php echo $r['issued_at']; ?></td>
</tr>
<?php endwhile; ?>
</table>

<h2>Complaints</h2>

<div class="table-box">
<form method="POST">
    <label>Complaint Type</label>
    <select name="complaint_type" required>
        <option value="">Select type</option>
        <option value="Billing Concern">Billing Concern</option>
        <option value="Water Interruption">Water Interruption</option>
        <option value="Meter Problem">Meter Problem</option>
        <option value="Payment Concern">Payment Concern</option>
        <option value="Others">Others</option>
    </select>

    <label>Description</label>
    <textarea name="description" placeholder="Describe your complaint" required></textarea>

    <button name="submit_complaint">Submit Complaint</button>
</form>
</div>

<table>
<tr>
    <th>Complaint Type</th>
    <th>Description</th>
    <th>Reply</th>
    <th>Status</th>
</tr>

<?php while($c = $complaints->fetch_assoc()): ?>
<tr>
    <td><?php echo $c['subject']; ?></td>
    <td><?php echo $c['message']; ?></td>
    <td><?php echo $c['reply']; ?></td>
    <td><?php echo $c['status']; ?></td>
</tr>
<?php endwhile; ?>
</table>

</div>
</div>

<script>
new Chart(document.getElementById("usageChart"), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
            label: 'Water Usage',
            data: <?php echo json_encode($cons); ?>,
            borderWidth: 2
        }]
    }
});
</script>

<script src="script.js"></script>
</body>
</html>