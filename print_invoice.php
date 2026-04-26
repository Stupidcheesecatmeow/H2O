<?php
session_start();
include "db.php";

if (!isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Invoice not found");
}

$invoice_id = $_GET['id'];

$stmt = $conn->prepare("
    SELECT 
        i.*,
        u.first_name,
        u.last_name,
        u.email,
        u.contact_no,
        u.barangay,
        u.street,
        u.meter_number,
        mr.previous_reading,
        mr.current_reading,
        mr.reading_date
    FROM invoices i
    JOIN users u ON i.user_id = u.id
    JOIN meter_readings mr ON i.reading_id = mr.id
    WHERE i.id = ?
");

$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();

if (!$invoice) {
    die("Invoice not found");
}

/* PREVIOUS UNPAID BILL */
$previous_balance = $conn->query("
    SELECT SUM(amount) AS total
    FROM invoices
    WHERE user_id='{$invoice['user_id']}'
    AND id!='{$invoice['id']}'
    AND status!='paid'
")->fetch_assoc()['total'] ?? 0;

$total_bill = $invoice['amount'] + $previous_balance;

/* BILLING PERIOD */
$period_from = date("m/d/Y", strtotime($invoice['reading_date']));
$period_to = date("m/d/Y", strtotime($invoice['due_date']));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Print Invoice</title>
    <link rel="stylesheet" href="invoice.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
</head>
<body>

<button class="print-btn" onclick="window.print()">Print Invoice</button>

<div class="invoice">

    <div class="watermark">
        <img src="assets/spongy.png" alt="Watermark">
    </div>

    <div class="header">
        <div class="brand">
            <img src="assets/spongy.png" alt="Logo">
            <div>
                <h1>H2O</h1>
                <p>Hydro Operations Hub</p>
                <p>Water Billing and Collection System</p>
            </div>
        </div>

        <div class="invoice-title">
            <h2>WATER BILL NOTICE</h2>
            <p><strong>Invoice No: <?php echo $invoice['invoice_no']; ?></strong></p>
            <p>Date Issued: <?php echo date("Y-m-d", strtotime($invoice['created_at'])); ?></p>
            <p>Due Date: <?php echo $invoice['due_date']; ?></p>

            <div id="qrcode"></div>
            <svg id="barcode"></svg>
        </div>
    </div>

    <div class="section info-grid">
        <div class="box">
            <h3>Account Details</h3>
            <p><strong>Name:</strong> <?php echo $invoice['first_name']." ".$invoice['last_name']; ?></p>
            <p><strong>Email:</strong> <?php echo $invoice['email']; ?></p>
            <p><strong>Contact:</strong> <?php echo $invoice['contact_no']; ?></p>
            <p><strong>Address:</strong> <?php echo $invoice['street'].", ".$invoice['barangay']; ?></p>
            <p><strong>Meter No:</strong> <?php echo $invoice['meter_number']; ?></p>
        </div>

        <div class="box">
            <h3>Period Covered</h3>
            <p><strong>From:</strong> <?php echo $period_from; ?></p>
            <p><strong>To:</strong> <?php echo $period_to; ?></p>
            <p><strong>Status:</strong> <?php echo strtoupper($invoice['status']); ?></p>
            <p><strong>Rate:</strong> Minimum ₱165 for 0–10 m³</p>
            <p><strong>Additional:</strong> ₱18.15/m³ after 10 m³, ₱22.28/m³ after 30 m³</p>
        </div>
    </div>

    <div class="section">
        <h3>Reading</h3>

        <table>
            <tr>
                <th>Present</th>
                <th>Previous</th>
                <th>Consumption</th>
                <th>Monthly Due</th>
            </tr>

            <tr>
                <td><?php echo $invoice['current_reading']; ?></td>
                <td><?php echo $invoice['previous_reading']; ?></td>
                <td><?php echo $invoice['consumption']; ?> m³</td>
                <td>₱<?php echo number_format($invoice['amount'], 2); ?></td>
            </tr>
        </table>
    </div>

    <div class="total">
        <div class="total-box">
            <p>
                <span>Previous Bill:</span>
                <strong>₱<?php echo number_format($previous_balance, 2); ?></strong>
            </p>

            <p>
                <span>Current Bill:</span>
                <strong>₱<?php echo number_format($invoice['amount'], 2); ?></strong>
            </p>

            <p>
                <span>Other Dues:</span>
                <strong>₱0.00</strong>
            </p>

            <h2>
                <span>Total Bill:</span>
                <span>₱<?php echo number_format($total_bill, 2); ?></span>
            </h2>
        </div>
    </div>

    <div class="section">
        <div class="box">
            <h3>Payment Reminder</h3>
            <p><strong>Payment Due Date:</strong> <?php echo date("F d, Y", strtotime($invoice['due_date'])); ?></p>
            <p>PLEASE DISREGARD THIS BILL IF PAYMENT HAS BEEN MADE.</p>
            <p><strong>Note:</strong> Two (2) months delay of payment may be subject for disconnection.</p>
        </div>
    </div>

    <div class="signature-section">
        <div>
            <p>Prepared by:</p>
            <span>________________________</span>
            <small>Administrator</small>
        </div>

        <div>
            <p>Received by:</p>
            <span>________________________</span>
            <small>Customer Signature</small>
        </div>
    </div>

    <div class="footer">
        <p>This is a system-generated water bill invoice.</p>
        <p>Please settle your bill on or before the due date.</p>
    </div>

</div>

<script>
new QRCode(document.getElementById("qrcode"), {
    text: "Invoice No: <?php echo $invoice['invoice_no']; ?> | Customer: <?php echo $invoice['first_name'].' '.$invoice['last_name']; ?> | Total Bill: <?php echo $total_bill; ?>",
    width: 80,
    height: 80
});

JsBarcode("#barcode", "<?php echo $invoice['invoice_no']; ?>", {
    format: "CODE128",
    width: 1.5,
    height: 40,
    displayValue: true,
    fontSize: 12
});
</script>

</body>
</html>