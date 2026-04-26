<?php
session_start();
include "db.php";

if ($_SESSION['role'] != "agent") {
    header("Location: index.php");
    exit();
}

$agent_id = $_SESSION['user_id'];

$assignment = $conn->query("
    SELECT area 
    FROM agent_assignments 
    WHERE agent_id='$agent_id'
")->fetch_assoc();

$assigned_barangay = $assignment['area'] ?? "";

$customers = $conn->query("
    SELECT id, first_name, last_name, barangay, street, meter_number 
    FROM users 
    WHERE role='user' 
    AND status='active'
    AND barangay='$assigned_barangay'
");
?>

<link rel="stylesheet" href="dashboard.css">

<div class="layout">

<div class="sidebar">
<h2>Field Agent</h2>
<ul>
    <li><a href="agent_dashboard.php">Dashboard</a></li>
    <li><a href="meter_reading.php">Meter Reading</a></li>
</ul>
</div>

<div class="main">

<h1>Meter Reading</h1>

<form method="POST" action="agent_dashboard.php">

<input type="text" value="<?php echo $assigned_barangay; ?>" readonly>

<select id="streetSelect">
<option>Select Street</option>
</select>

<select name="user_id" id="meterSelect" required>
<option>Select Meter</option>
</select>

<input type="text" id="customerName" readonly>

<input type="number" name="current" placeholder="Current Reading" required>

<button name="save">Submit</button>

</form>

</div>
</div>

<script>
const customers = [
<?php while($c = $customers->fetch_assoc()): ?>
{
    id: "<?php echo $c['id']; ?>",
    name: "<?php echo $c['first_name']." ".$c['last_name']; ?>",
    barangay: "<?php echo $c['barangay']; ?>",
    street: "<?php echo $c['street']; ?>",
    meter: "<?php echo $c['meter_number']; ?>"
},
<?php endwhile; ?>
];
</script>