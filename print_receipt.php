<?php
session_start();
include "db.php";

if (!isset($_GET['id'])) {
    die("Receipt not found");
}

$id = $_GET['id'];

$r = $conn->query("
    SELECT 
        r.*, 
        p.amount AS amount_paid,
        p.payment_method,
        p.reference_no,
        p.paid_at,
        i.id AS invoice_id,
        i.user_id,
        i.invoice_no,
        i.amount AS invoice_amount,
        i.due_date,
        i.status AS invoice_status,
        i.consumption,
        i.rate,
        i.created_at AS invoice_created,
        u.first_name,
        u.last_name,
        u.barangay,
        u.street,
        u.meter_number,
        mr.previous_reading,
        mr.current_reading,
        mr.reading_date
    FROM receipts r
    JOIN payments p ON r.payment_id = p.id
    JOIN invoices i ON p.invoice_id = i.id
    JOIN users u ON p.user_id = u.id
    JOIN meter_readings mr ON i.reading_id = mr.id
    WHERE r.id='$id'
")->fetch_assoc();

if (!$r) {
    die("Receipt not found");
}

/* PREVIOUS UNPAID BILL EXCEPT THIS INVOICE */
$previous_balance = $conn->query("
    SELECT SUM(amount) AS total
    FROM invoices
    WHERE user_id='{$r['user_id']}'
    AND id!='{$r['invoice_id']}'
    AND status!='paid'
")->fetch_assoc()['total'] ?? 0;

$current_bill = $r['invoice_amount'];
$total_bill = $previous_balance + $current_bill;
$remaining_balance = $total_bill - $r['amount_paid'];

if ($remaining_balance < 0) {
    $remaining_balance = 0;
}

$period_from = date("m/d/Y", strtotime($r['reading_date']));
$period_to = date("m/d/Y", strtotime($r['due_date']));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Print Receipt</title>
    <link rel="stylesheet" href="invoice.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
</head>
<body>

<button class="print-btn" onclick="window.print()">Print Receipt</button>

<div class="invoice">

    <div class="watermark">
        <img src="assets/logo_blk_name.png" alt="Watermark">
    </div>

    <div class="header">
        <div class="brand">
            <img src="assets/logo_blk_name.png" alt="Logo">
            <div>
                <h1>H2O</h1>
                <p>Hydro Operations Hub</p>
                <p>Official Payment Receipt</p>
            </div>
        </div>

        <div class="invoice-title">
            <h2>OFFICIAL RECEIPT</h2>
            <p><strong>Receipt No: <?php echo $r['receipt_no']; ?></strong></p>
            <p>Invoice No: <?php echo $r['invoice_no']; ?></p>
            <p>Issued At: <?php echo $r['issued_at']; ?></p>

            <div id="qrcode"></div>
            <svg id="barcode"></svg>
        </div>
    </div>

    <div class="section info-grid">
        <div class="box">
            <h3>Customer Information</h3>
            <p><strong>Name:</strong> <?php echo $r['first_name']." ".$r['last_name']; ?></p>
            <p><strong>Address:</strong> <?php echo $r['street'].", ".$r['barangay']; ?></p>
            <p><strong>Meter No:</strong> <?php echo $r['meter_number']; ?></p>
            <p><strong>Period Covered:</strong> <?php echo $period_from." to ".$period_to; ?></p>
        </div>

        <div class="box">
            <h3>Payment Details</h3>
            <p><strong>Payment Method:</strong> <?php echo $r['payment_method']; ?></p>
            <p><strong>Reference No:</strong> <?php echo $r['reference_no']; ?></p>
            <p><strong>Date Paid:</strong> <?php echo $r['paid_at']; ?></p>
            <p><strong>Invoice Status:</strong> <?php echo strtoupper($r['invoice_status']); ?></p>
        </div>
    </div>

    <div class="section">
        <h3>Reading Summary</h3>

        <table>
            <tr>
                <th>Present</th>
                <th>Previous</th>
                <th>Consumption</th>
                <th>Monthly Due</th>
            </tr>

            <tr>
                <td><?php echo $r['current_reading']; ?></td>
                <td><?php echo $r['previous_reading']; ?></td>
                <td><?php echo $r['consumption']; ?> m³</td>
                <td>₱<?php echo number_format($current_bill, 2); ?></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Receipt Summary</h3>

        <table>
            <tr>
                <th>Receipt No.</th>
                <th>Invoice No.</th>
                <th>Amount Paid</th>
                <th>Payment Method</th>
            </tr>

            <tr>
                <td><?php echo $r['receipt_no']; ?></td>
                <td><?php echo $r['invoice_no']; ?></td>
                <td>₱<?php echo number_format($r['amount_paid'], 2); ?></td>
                <td><?php echo $r['payment_method']; ?></td>
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
                <strong>₱<?php echo number_format($current_bill, 2); ?></strong>
            </p>

            <p>
                <span>Total Bill:</span>
                <strong>₱<?php echo number_format($total_bill, 2); ?></strong>
            </p>

            <p>
                <span>Amount Paid:</span>
                <strong>₱<?php echo number_format($r['amount_paid'], 2); ?></strong>
            </p>

            <h2>
                <span>Balance:</span>
                <span>₱<?php echo number_format($remaining_balance, 2); ?></span>
            </h2>
        </div>
    </div>

    <div class="signature-section">
        <div>
            <p>Verified by:</p>
            <span>________________________</span>
            <small>Accountant</small>
        </div>

        <div>
            <p>Received by:</p>
            <span>________________________</span>
            <small>Customer Signature</small>
        </div>
    </div>

    <div class="footer">
        <p>This is a system-generated official receipt.</p>
        <p>Thank you for your payment.</p>
    </div>

</div>

<script>
new QRCode(document.getElementById("qrcode"), {
    text: "Receipt No: <?php echo $r['receipt_no']; ?> | Invoice: <?php echo $r['invoice_no']; ?> | Customer: <?php echo $r['first_name'].' '.$r['last_name']; ?> | Amount Paid: <?php echo $r['amount_paid']; ?> | Balance: <?php echo $remaining_balance; ?>",
    width: 80,
    height: 80
});

JsBarcode("#barcode", "<?php echo $r['receipt_no']; ?>", {
    format: "CODE128",
    width: 1.5,
    height: 40,
    displayValue: true,
    fontSize: 12
});
</script>

</body>
</html>