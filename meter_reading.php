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
<html>
<head>
    <title>Meter Reading</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<div class="layout">

<div class="sidebar">
<h2>Field Agent</h2>
<ul>
    <li><a href="agent_dashboard.php">Dashboard</a></li>
    <li><a href="meter_reading.php">Meter Reading</a></li>
    <li><a href="profile.php">Profile</a></li>
    <li><a href="logout.php">Logout</a></li>
</ul>
</div>

<div class="main">

<h1>Meter Reading Input</h1>

<div class="card">
    Assigned Barangay<br>
    <strong><?php echo $assigned_barangay ?: "Not Assigned"; ?></strong>
</div>

<?php if($assigned_barangay == ""): ?>

<p>No barangay assigned yet. Please contact admin.</p>

<?php else: ?>

<form method="POST">

    <input type="text" value="<?php echo $assigned_barangay; ?>" readonly>

    <select id="streetSelect" required>
        <option value="">Select Street</option>
    </select>

    <select name="user_id" id="meterSelect" required>
        <option value="">Select Meter Number</option>
    </select>

    <input type="text" id="customerName" placeholder="Customer Name" readonly>

    <input type="number" name="current" placeholder="Current Reading" required>

    <button type="submit" name="save">Submit Reading</button>

</form>

<h2>Reading History</h2>

<table>
<tr>
    <th>Customer</th>
    <th>Street</th>
    <th>Meter No.</th>
    <th>Previous</th>
    <th>Current</th>
    <th>Consumption</th>
    <th>Date</th>
</tr>

<?php while($h = $history->fetch_assoc()): ?>
<tr>
    <td><?php echo $h['first_name']." ".$h['last_name']; ?></td>
    <td><?php echo $h['street']; ?></td>
    <td><?php echo $h['meter_number']; ?></td>
    <td><?php echo $h['previous_reading']; ?></td>
    <td><?php echo $h['current_reading']; ?></td>
    <td><?php echo $h['consumption']; ?></td>
    <td><?php echo $h['reading_date']; ?></td>
</tr>
<?php endwhile; ?>
</table>

<?php endif; ?>

</div>
</div>

<script>
const customers = [
<?php while($c = $customers->fetch_assoc()): ?>
{
    id: "<?php echo $c['id']; ?>",
    code: "<?php echo $c['user_code']; ?>",
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
    streetSelect.innerHTML += `<option value="${street}">${street}</option>`;
});

streetSelect.addEventListener("change", function(){
    const selectedStreet = this.value;

    meterSelect.innerHTML = '<option value="">Select Meter Number</option>';
    customerName.value = "";

    customers
        .filter(c => c.street === selectedStreet)
        .forEach(c => {
            meterSelect.innerHTML += `
                <option value="${c.id}" data-name="${c.name}">
                    ${c.meter}
                </option>
            `;
        });
});

meterSelect.addEventListener("change", function(){
    const selectedOption = this.options[this.selectedIndex];
    customerName.value = selectedOption.getAttribute("data-name") || "";
});
</script>

</body>
</html>