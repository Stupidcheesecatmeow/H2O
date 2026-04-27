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
    $amount = floatval($_POST['amount']);
    $mop = $_POST['payment_method'];

    $proof_name = null;

    if($mop != "Cash"){
        if(!isset($_FILES['proof_image']) || $_FILES['proof_image']['name'] == ""){
            echo "<script>alert('Please upload payment proof'); window.location='user_payments.php';</script>";
            exit();
        }

        if(!is_dir("payment_proofs")){
            mkdir("payment_proofs", 0777, true);
        }

        $ext = pathinfo($_FILES['proof_image']['name'], PATHINFO_EXTENSION);
        $proof_name = "proof_" . $user_id . "_" . time() . "." . $ext;
        $target = "payment_proofs/" . $proof_name;

        move_uploaded_file($_FILES['proof_image']['tmp_name'], $target);
    }

    $trx = "TRX-" . date("Ymd") . "-" . rand(1000,9999);
    $qr_token = "QR-" . bin2hex(random_bytes(8));
    $qr_expires_at = date("Y-m-d H:i:s", strtotime("+15 minutes"));

    $stmt = $conn->prepare("INSERT INTO payments 
    (invoice_id, user_id, transaction_no, amount, payment_method, reference_no, proof_image, qr_token, qr_expires_at, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");

    $stmt->bind_param(
        "iisdsssss", 
        $invoice_id, 
        $user_id, 
        $trx, 
        $amount, 
        $mop, 
        $trx, 
        $proof_name,
        $qr_token,
        $qr_expires_at
    );

    $stmt->execute();

    $conn->query("UPDATE invoices SET status='pending_verification' WHERE id='$invoice_id'");

    echo "<script>alert('Payment submitted! Waiting for verification.'); window.location='user_payments.php';</script>";
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

<!DOCTYPE html>
<html>
<head>
    <title>Payment</title>
    <link rel="stylesheet" href="dashboard.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body>

    <div class="layout">

        <div class="sidebar">
            <h2><?php echo $user['first_name']; ?></h2>
            <ul>
                <li><a href="user_dashboard.php">Dashboard</a></li>
                <li><a href="user_notifications.php">Notifications</a></li>
                <li><a href="user_billing.php">Billing</a></li>
                <li><a href="user_payments.php">Payment</a></li>
                <li><a href="user_history.php">History</a></li>
                <li><a href="user_complaints.php">Complaints</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <div class="main">

            <h1>Payment</h1>

            <form method="POST" enctype="multipart/form-data" onsubmit="return validatePaymentProof()">

                <select name="invoice_id" id="invoiceSelect" required>
                    <option value="">Select Bill</option>

                    <?php while($b = $bills->fetch_assoc()): ?>
                    <option 
                        value="<?php echo $b['id']; ?>" 
                        data-amount="<?php echo $b['amount']; ?>"
                        data-invoice="<?php echo $b['invoice_no']; ?>">
                        <?php echo $b['invoice_no']; ?> - ₱<?php echo number_format($b['amount'],2); ?>
                    </option>
                    <?php endwhile; ?>

                </select>

                <input type="number" step="0.01" name="amount" id="amountInput" placeholder="Amount" required readonly>

                <select name="payment_method" id="mopSelect" required>
                    <option value="">Select MOP</option>
                    <option value="Cash">Cash</option>
                    <option value="GCash">GCash</option>
                    <option value="Maya">Maya</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                </select>

                <div id="paymentDetails"></div>

                <div id="proofBox" style="display:none;">
                    <label>Upload Payment Proof</label>
                    <input type="file" name="proof_image" id="proofImage" accept="image/*">
                </div>

                <button name="pay">Submit Payment</button>

            </form>

            <h2>Latest Payment</h2>

            <?php if($latest): ?>
            <p>
            Transaction: <?php echo $latest['transaction_no']; ?><br>
            Amount: ₱<?php echo number_format($latest['amount'],2); ?><br>
            MOP: <?php echo $latest['payment_method']; ?><br>
            QR Token: <?php echo $latest['qr_token'] ?? "N/A"; ?><br>
            QR Expires: <?php echo $latest['qr_expires_at'] ?? "N/A"; ?><br>
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
        const mop = document.getElementById("mopSelect");
        const box = document.getElementById("paymentDetails");
        const proofBox = document.getElementById("proofBox");
        const proofImage = document.getElementById("proofImage");

        let qrExpireTime = null;
        let countdownInterval = null;

        select.addEventListener("change", function(){
            const selected = this.options[this.selectedIndex];
            amount.value = selected.getAttribute("data-amount") || "";
            renderPaymentBox();
        });

        mop.addEventListener("change", renderPaymentBox);

        function renderPaymentBox(){
            const val = mop.value;
            const selected = select.options[select.selectedIndex];

            if(val === ""){
                proofBox.style.display = "none";
                proofImage.required = false;
                proofImage.value = "";
                box.innerHTML = "";
                return;
            }

            if(val === "Cash"){
                proofBox.style.display = "none";
                proofImage.required = false;
                proofImage.value = "";

                box.innerHTML = `
                    <div class="card">
                        <h3>Cash Payment</h3>
                        <p>Please proceed to the H2O Office to complete your payment.</p>
                        <p><strong>Office Hours:</strong> 8:00 AM - 5:00 PM</p>
                    </div>
                `;
                return;
            }

            if(select.value === ""){
                box.innerHTML = `
                    <div class="card">
                        <p>Please select a bill first before generating QR.</p>
                    </div>
                `;
                return;
            }

            proofBox.style.display = "block";
            proofImage.required = true;

            const invoiceNo = selected.getAttribute("data-invoice");
            const amountVal = amount.value;
            const tempToken = "QR-" + Date.now() + "-" + Math.floor(Math.random() * 9999);

            qrExpireTime = new Date().getTime() + (15 * 60 * 1000);

            box.innerHTML = `
                <div class="card">
                    <h3>${val} Payment QR</h3>
                    <div id="qrcode"></div>
                    <p><strong>Invoice:</strong> ${invoiceNo}</p>
                    <p><strong>Amount:</strong> ₱${amountVal}</p>
                    <p><strong>Temp QR Token:</strong> ${tempToken}</p>
                    <p><strong>Expires in:</strong> <span id="qrTimer">15:00</span></p>
                    <p>Scan the QR, then upload your payment screenshot proof.</p>
                </div>
            `;

            const qrData = `
            H2O PAYMENT
            Customer: <?php echo $user['first_name']." ".$user['last_name']; ?>
            Invoice: ${invoiceNo}
            Amount: ${amountVal}
            MOP: ${val}
            Token: ${tempToken}
            Expires: 15 minutes
            `;

            new QRCode(document.getElementById("qrcode"), {
                text: qrData,
                width: 180,
                height: 180
            });

            startCountdown();
        }

        function startCountdown(){
            clearInterval(countdownInterval);

            countdownInterval = setInterval(() => {
                const now = new Date().getTime();
                const distance = qrExpireTime - now;

                if(distance <= 0){
                    clearInterval(countdownInterval);
                    document.getElementById("qrTimer").innerHTML = "Expired";
                    document.querySelector("button[name='pay']").disabled = true;
                    alert("QR expired. Please reselect payment method to generate a new QR.");
                    return;
                }

                const minutes = Math.floor(distance / 1000 / 60);
                const seconds = Math.floor((distance / 1000) % 60);

                document.getElementById("qrTimer").innerHTML =
                    String(minutes).padStart(2, '0') + ":" + String(seconds).padStart(2, '0');

                document.querySelector("button[name='pay']").disabled = false;
            }, 1000);
        }

        function validatePaymentProof(){
            if(select.value === ""){
                alert("Please select a bill.");
                return false;
            }

            if(mop.value === ""){
                alert("Please select mode of payment.");
                return false;
            }

            if(mop.value !== "Cash"){
                if(qrExpireTime && new Date().getTime() > qrExpireTime){
                    alert("QR expired. Please generate a new QR.");
                    return false;
                }

                if(proofImage.files.length === 0){
                    alert("Please upload payment proof.");
                    return false;
                }
            }

            return true;
        }
    </script>

</body>
</html>