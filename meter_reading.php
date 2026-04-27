<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "agent") {
    header("Location: index.php");
    exit();
}

$agent_id = $_SESSION['user_id'];

/* GET ASSIGNED BARANGAY */
$assignment = $conn->query("
    SELECT area 
    FROM agent_assignments 
    WHERE agent_id='$agent_id'
")->fetch_assoc();

$assigned_barangay = $assignment['area'] ?? "";

/* SAVE READING */
if(isset($_POST['save'])){
    $user_id = $_POST['user_id'];
    $curr = $_POST['current'];

    $checkUser = $conn->query("
        SELECT id 
        FROM users 
        WHERE id='$user_id' 
        AND barangay='$assigned_barangay'
        AND role='user'
    ")->fetch_assoc();

    if(!$checkUser){
        echo "<script>alert('Invalid customer for your assigned barangay'); window.location='meter_reading.php';</script>";
        exit();
    }

    $last = $conn->query("
        SELECT current_reading 
        FROM meter_readings 
        WHERE user_id='$user_id'
        ORDER BY reading_date DESC, id DESC
        LIMIT 1
    ")->fetch_assoc();

    $prev = $last['current_reading'] ?? 0;
    $consumption = $curr - $prev;
    $date = date("Y-m-d");

    if($consumption < 0){
        echo "<script>alert('Current reading cannot be lower than previous reading'); window.location='meter_reading.php';</script>";
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO meter_readings 
    (user_id, agent_id, previous_reading, current_reading, consumption, reading_date, status)
    VALUES (?, ?, ?, ?, ?, ?, 'completed')");

    $stmt->bind_param("iiiiis", $user_id, $agent_id, $prev, $curr, $consumption, $date);
    $stmt->execute();

    $conn->query("
        INSERT INTO notifications (user_id, role_target, title, message, type, status)
        VALUES ('$agent_id','admin','Meter Reading Completed','Field agent submitted meter reading.','reading','unread')
    ");

    echo "<script>alert('Reading saved'); window.location='meter_reading.php';</script>";
    exit();
}

/* CUSTOMERS ONLY FROM ASSIGNED BARANGAY */
$customers = $conn->query("
    SELECT id, user_code, first_name, last_name, barangay, street, meter_number
    FROM users 
    WHERE role='user' 
    AND status='active'
    AND barangay='$assigned_barangay'
    ORDER BY street ASC, meter_number ASC
");

/* HISTORY */
$history = $conn->query("
    SELECT mr.*, u.first_name, u.last_name, u.street, u.meter_number
    FROM meter_readings mr
    JOIN users u ON mr.user_id = u.id
    WHERE mr.agent_id='$agent_id'
    AND u.barangay='$assigned_barangay'
    ORDER BY mr.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meter Reading | H2O</title>
    <link rel="stylesheet" href="meter_reading.css">
</head>
<body id="mainBody">

    <div class="main-content">
        <div class="header-row">
            <h1>METER READING INPUT</h1>
        </div>

        <!-- TOP BARANGAY INFO -->
        <div class="glass-panel" style="padding: 20px; margin-bottom: 20px;">
            <span style="font-size: 0.7rem; opacity: 0.7; text-transform: uppercase;">Current Assignment</span><br>
            <strong style="font-size: 1.5rem; color: var(--accent-blue);">
                <?php echo $assigned_barangay ?: "Not Assigned"; ?>
            </strong>
        </div>

        <?php if($assigned_barangay != ""): ?>
        
        <!-- INPUT FORM PANEL -->
        <div class="glass-panel">
            <div class="panel-title-bar">New Reading Entry</div>
            <div class="table-area">
                <form method="POST" class="reading-form-container">
                    
                    <div class="input-group">
                        <label>Barangay</label>
                        <input type="text" value="<?php echo $assigned_barangay; ?>" readonly>
                    </div>

                    <div class="input-group">
                        <label>Select Street</label>
                        <select id="streetSelect" required>
                            <option value="">-- Choose Street --</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Meter Number</label>
                        <select name="user_id" id="meterSelect" required>
                            <option value="">-- Select Meter --</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Customer Name</label>
                        <input type="text" id="customerName" placeholder="Auto-filled" readonly>
                    </div>

                    <div class="input-group" style="grid-column: span 2;">
                        <label>Current Reading (m³)</label>
                        <input type="number" name="current" placeholder="Enter numerical value" required>
                    </div>

                    <button type="submit" name="save" class="submit-btn">SUBMIT READING</button>
                </form>
            </div>
        </div>

        <!-- HISTORY TABLE -->
        <div class="glass-panel">
            <div class="panel-title-bar">Your Recent Submissions</div>
            <div class="table-area">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Street</th>
                            <th>Meter No.</th>
                            <th>Prev</th>
                            <th>Curr</th>
                            <th>Cons.</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($h = $history->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo strtoupper($h['first_name']." ".$h['last_name']); ?></strong></td>
                            <td><?php echo $h['street']; ?></td>
                            <td><span style="font-family: monospace;"><?php echo $h['meter_number']; ?></span></td>
                            <td><?php echo $h['previous_reading']; ?></td>
                            <td><?php echo $h['current_reading']; ?></td>
                            <td style="color: var(--accent-blue); font-weight: bold;"><?php echo $h['consumption']; ?> m³</td>
                            <td style="font-size: 0.75rem; opacity: 0.7;"><?php echo date('M d, Y', strtotime($h['reading_date'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php else: ?>
            <div class="glass-panel" style="padding: 40px; text-align: center; opacity: 0.6;">
                <p>No barangay assigned. Please contact the administrator to receive your field assignment.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- SIDEBAR RIGHT -->
    <div class="sidebar-right">
        <img src="assets/logo_name.png" class="side-logo">
        <div class="agent-info">
            <h3>FIELD AGENT</h3>
            <p>METER DEPARTMENT</p>
        </div>
        <nav class="nav-menu">
            <a href="agent_dashboard.php" class="nav-item">DASHBOARD</a>
            <a href="meter_reading.php" class="nav-item active">METER READING</a>
            <a href="profile.php" class="nav-item">PROFILE</a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn-container">LOG OUT</a>
        </div>
    </div>

    <script>
        window.onload = () => { document.body.style.opacity = "1"; };

        const customers = [
            <?php 
            // Reset pointer if loop was used before
            $customers->data_seek(0); 
            while($c = $customers->fetch_assoc()): 
            ?>
            {
                id: "<?php echo $c['id']; ?>",
                name: "<?php echo $c['first_name'].' '.$c['last_name']; ?>",
                street: "<?php echo $c['street']; ?>",
                meter: "<?php echo $c['meter_number']; ?>"
            },
            <?php endwhile; ?>
        ];

        const streetSelect = document.getElementById("streetSelect");
        const meterSelect = document.getElementById("meterSelect");
        const customerName = document.getElementById("customerName");

        const streets = [...new Set(customers.map(c => c.street))];
        streets.forEach(street => {
            const opt = document.createElement("option");
            opt.value = street;
            opt.textContent = street;
            streetSelect.appendChild(opt);
        });

        streetSelect.addEventListener("change", function(){
            const selectedStreet = this.value;
            meterSelect.innerHTML = '<option value="">-- Select Meter --</option>';
            customerName.value = "";

            customers
                .filter(c => c.street === selectedStreet)
                .forEach(c => {
                    const opt = document.createElement("option");
                    opt.value = c.id;
                    opt.setAttribute("data-name", c.name);
                    opt.textContent = c.meter;
                    meterSelect.appendChild(opt);
                });
        });

        meterSelect.addEventListener("change", function(){
            const selectedOption = this.options[this.selectedIndex];
            customerName.value = selectedOption.getAttribute("data-name") || "";
        });
    </script>
</body>
</html>