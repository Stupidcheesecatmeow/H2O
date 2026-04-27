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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>H.O.H Make Payment</title>
    <link rel="stylesheet" href="styles/user_design.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body id="mainBody">

    <!-- MAIN CONTENT AREA -->
    <div class="main-content">
        <div class="header-row">
            <h1>BILL PAYMENT</h1>
        </div>

        <div class="glass-panel" style="padding: 25px;">
            <div class="panel-title-bar" style="margin: -25px -25px 25px -25px;">Submit Transaction</div>
            
            <form method="POST" enctype="multipart/form-data" onsubmit="return validatePaymentProof()">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    
                    <!-- Form Controls -->
                    <div class="form-section">
                        <label class="stat-label">Select Outstanding Bill</label>
                        <select name="invoice_id" id="invoiceSelect" required>
                            <option value="">-- Choose Bill --</option>
                            <?php while($b = $bills->fetch_assoc()): ?>
                            <option value="<?php echo $b['id']; ?>" data-amount="<?php echo $b['amount']; ?>" data-invoice="<?php echo $b['invoice_no']; ?>">
                                <?php echo $b['invoice_no']; ?> - ₱<?php echo number_format($b['amount'],2); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>

                        <label class="stat-label" style="margin-top: 15px;">Total Amount</label>
                        <input type="number" step="0.01" name="amount" id="amountInput" placeholder="0.00" required readonly>

                        <label class="stat-label" style="margin-top: 15px;">Payment Method</label>
                        <select name="payment_method" id="mopSelect" required>
                            <option value="">-- Choose Mode of Payment --</option>
                            <option value="Cash">Cash (At Office)</option>
                            <option value="GCash">GCash</option>
                            <option value="Maya">Maya</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                        </select>
                    </div>

                    <!-- Dynamic Details (QR/Instructions) -->
                    <div class="details-section">
                        <div id="paymentDetails">
                            <div class="card" style="text-align: center; padding: 40px; opacity: 0.5; border: 1px dashed rgba(255,255,255,0.2);">
                                Please select a bill and payment method to proceed.
                            </div>
                        </div>

                        <div id="proofBox" style="display:none; margin-top: 20px;">
                            <label class="stat-label">Upload Transaction Proof (Screenshot)</label>
                            <input type="file" name="proof_image" id="proofImage" accept="image/*">
                        </div>
                    </div>
                </div>

                <button type="submit" name="pay" class="pay-submit-btn">SUBMIT PAYMENT</button>
            </form>
        </div>

        <!-- LATEST TRANSACTION STATUS -->
        <div class="glass-panel">
            <div class="panel-title-bar">Latest Transaction Activity</div>
            <div class="table-area" style="padding: 20px;">
                <?php if($latest): ?>
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); text-align: center;">
                        <div><span class="stat-label">Reference</span><br><strong><?php echo $latest['transaction_no']; ?></strong></div>
                        <div><span class="stat-label">Amount</span><br><strong>₱<?php echo number_format($latest['amount'],2); ?></strong></div>
                        <div><span class="stat-label">Method</span><br><strong><?php echo $latest['payment_method']; ?></strong></div>
                        <div><span class="stat-label">Status</span><br>
                            <span class="status-pill <?php echo ($latest['status'] == 'pending') ? 'unpaid' : 'paid'; ?>">
                                <?php echo strtoupper($latest['status']); ?>
                            </span>
                        </div>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; opacity: 0.5;">No recent payment activity found.</p>
                <?php endif; ?>
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
            <a href="user_payments.php" class="nav-item active">PAYMENT</a>
            <a href="user_history.php" class="nav-item">HISTORY</a>
            <a href="profile.php" class="nav-item">PROFILE</a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn-container">LOG OUT</a>
        </div>
    </div>

    <script>
        window.onload = () => { document.body.style.opacity = "1"; };

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
            clearInterval(countdownInterval);

            if(val === ""){
                proofBox.style.display = "none";
                box.innerHTML = `<div class="card" style="text-align: center; padding: 40px; opacity: 0.5; border: 1px dashed rgba(255,255,255,0.2);">Please select a bill and payment method to proceed.</div>`;
                return;
            }

            if(val === "Cash"){
                proofBox.style.display = "none";
                proofImage.required = false;
                box.innerHTML = `
                    <div class="card">
                        <h3 style="color: var(--accent-blue); margin-top:0;">Cash Payment Instructions</h3>
                        <p>Please visit the H2O Main Office to settle this bill. Bring your Invoice Number for faster processing.</p>
                        <p><strong>Office Hours:</strong> 8:00 AM - 5:00 PM (Mon-Fri)</p>
                    </div>`;
                return;
            }

            if(select.value === ""){
                box.innerHTML = `<div class="card"><p>Please select a specific bill above to generate your unique ${val} QR code.</p></div>`;
                return;
            }

            // Electronic Payment Generation
            proofBox.style.display = "block";
            proofImage.required = true;
            const invoiceNo = selected.getAttribute("data-invoice");
            const amountVal = amount.value;
            const tempToken = "QR-" + Date.now();
            qrExpireTime = new Date().getTime() + (15 * 60 * 1000);

            box.innerHTML = `
                <div class="card" style="text-align: center;">
                    <h3 style="color: var(--accent-blue); margin-top:0;">${val} Scan-to-Pay</h3>
                    <div id="qrcode" style="display:inline-block; padding:10px; background:white; border-radius:8px; margin:10px 0;"></div>
                    <p style="font-size:0.8rem;">Invoice: <strong>${invoiceNo}</strong> | Amount: <strong>₱${amountVal}</strong></p>
                    <p style="font-size:0.7rem; color: #e74c3c; font-weight:bold;">QR Expires in: <span id="qrTimer">15:00</span></p>
                </div>`;

            new QRCode(document.getElementById("qrcode"), { text: tempToken, width: 150, height: 150 });
            startCountdown();
        }

        function startCountdown(){
            countdownInterval = setInterval(() => {
                const now = new Date().getTime();
                const distance = qrExpireTime - now;
                if(distance <= 0){
                    clearInterval(countdownInterval);
                    document.getElementById("qrTimer").innerHTML = "Expired";
                    box.innerHTML = `<div class="card" style="text-align:center; color:#e74c3c;">QR has expired. Please refresh the payment method.</div>`;
                    return;
                }
                const m = Math.floor(distance / 60000);
                const s = Math.floor((distance % 60000) / 1000);
                document.getElementById("qrTimer").innerHTML = `${m}:${s < 10 ? '0' : ''}${s}`;
            }, 1000);
        }

        function validatePaymentProof(){
            if(mop.value !== "Cash" && !proofImage.value){
                alert("Please upload the payment transaction screenshot before submitting.");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>