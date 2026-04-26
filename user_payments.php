<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "user") {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id='$user_id'")->fetch_assoc();

/* SUBMIT PAYMENT */
if(isset($_POST['pay'])){

    $invoice_id = $_POST['invoice_id'];
    $amount = $_POST['amount'];
    $mop = $_POST['payment_method'];

    $trx = "TRX-" . date("Ymd") . "-" . rand(1000,9999);

    $stmt = $conn->prepare("INSERT INTO payments 
    (invoice_id, user_id, transaction_no, amount, payment_method, reference_no, status)
    VALUES (?, ?, ?, ?, ?, ?, 'pending')");

    $stmt->bind_param("iisdss", $invoice_id, $user_id, $trx, $amount, $mop, $trx);
    $stmt->execute();

    $conn->query("UPDATE invoices SET status='pending_verification' WHERE id='$invoice_id'");

    echo "<script>alert('Payment submitted!'); window.location='payment.php';</script>";
    exit();
}

/* UNPAID BILLS */
$bills = $conn->query("
SELECT * FROM invoices
WHERE user_id='$user_id' AND status='unpaid'
ORDER BY due_date ASC
");

/* LATEST PAYMENT */
$latest = $conn->query("
SELECT * FROM payments
WHERE user_id='$user_id'
ORDER BY paid_at DESC
LIMIT 1
")->fetch_assoc();
?>

<link rel="stylesheet" href="dashboard.css">

<div class="layout">

<div class="sidebar">
<h2><?php echo $user['first_name']; ?></h2>
<ul>
    <li><a href="user_dashboard.php">Dashboard</a></li>
    <li><a href="user_billing.php">Billing</a></li>
    <li><a href="user_payments.php">Payment</a></li>
    <li><a href="user_history.php">History</a></li>
    <li><a href="user_complaints.php">Complaints</a></li>
</ul>
</div>

<div class="main">

<h1>Payment</h1>

<form method="POST">

<select name="invoice_id" id="invoiceSelect" required>
<option value="">Select Bill</option>

<?php while($b = $bills->fetch_assoc()): ?>
<option value="<?php echo $b['id']; ?>" data-amount="<?php echo $b['amount']; ?>">
<?php echo $b['invoice_no']; ?> - ₱<?php echo number_format($b['amount'],2); ?>
</option>
<?php endwhile; ?>

</select>

<input type="number" name="amount" id="amountInput" placeholder="Amount" required>

<select name="payment_method" required>
<option value="">Select MOP</option>
<option value="Cash">Cash</option>
<option value="GCash">GCash</option>
<option value="Maya">Maya</option>
<option value="Bank">Bank</option>
</select>

<button name="pay">Submit Payment</button>

</form>

<h2>Latest Payment</h2>

<?php if($latest): ?>
<p>
Transaction: <?php echo $latest['transaction_no']; ?><br>
Amount: ₱<?php echo number_format($latest['amount'],2); ?><br>
Status: <?php echo $latest['status']; ?>
</p>
<?php else: ?>
<p>No payments yet</p>
<?php endif; ?>

</div>
</div>

<script>
const select = document.getElementById("invoiceSelect");
const amount = document.getElementById("amountInput");

select.addEventListener("change", function(){
    const val = this.options[this.selectedIndex].getAttribute("data-amount");
    amount.value = val || "";
});
</script>