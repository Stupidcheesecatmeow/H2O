<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: index.php");
    exit();
}

/* ADD USER */
if(isset($_POST['add_user'])){
    $stmt = $conn->prepare("INSERT INTO users 
    (first_name,last_name,email,contact_no,role,barangay,street,meter_number,status,password)
    VALUES (?,?,?,?,?,?,?,?,?,?)");

    $stmt->bind_param("ssssssssss",
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['email'],
        $_POST['contact_no'],
        $_POST['role'],
        $_POST['barangay'],
        $_POST['street'],
        $_POST['meter_number'],
        $_POST['status'],
        $_POST['password']
    );

    $stmt->execute();
    echo "<script>alert('User added'); window.location='user_management.php';</script>";
}

/* DELETE USER */
if(isset($_GET['delete'])){
    $conn->query("DELETE FROM users WHERE id=".$_GET['delete']);
    echo "<script>alert('Deleted'); window.location='user_management.php';</script>";
}

/* UPDATE USER */
if(isset($_POST['update_user'])){
    $stmt = $conn->prepare("UPDATE users SET 
        first_name=?, last_name=?, email=?, contact_no=?, role=?, barangay=?, street=?, meter_number=?, status=? 
        WHERE id=?");

    $stmt->bind_param("sssssssssi",
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['email'],
        $_POST['contact_no'],
        $_POST['role'],
        $_POST['barangay'],
        $_POST['street'],
        $_POST['meter_number'],
        $_POST['status'],
        $_POST['id']
    );

    $stmt->execute();
    echo "<script>alert('Updated'); window.location='user_management.php';</script>";
}

$users = $conn->query("SELECT * FROM users");
?>

<link rel="stylesheet" href="dashboard.css">

<div class="layout">

<!-- SIDEBAR -->
<div class="sidebar">
<h2>Admin</h2>
<ul>
    <li><a href="admin_dashboard.php">Dashboard</a></li>
    <li><a href="announcements.php">Announcements</a></li>
    <li><a href="user_management.php">User Management</a></li>
    <li><a href="agent_management.php">Field Agents</a></li>
    <li><a href="invoices.php">Invoices</a></li>
    <li><a href="transactions.php">Transactions</a></li>
    <li><a href="complaints_admin.php">Complaints</a></li>
    <li><a href="reports.php">Reports</a></li>
    <li><a href="profile.php">Profile</a></li>
    <li><a href="logout.php">Logout</a></li>
</ul>
</div>

<div class="main">

<h1>User Management</h1>

<!-- ADD USER -->
<h3>Add User</h3>
<form method="POST">
<input name="first_name" placeholder="First Name" required>
<input name="last_name" placeholder="Last Name" required>
<input name="email" placeholder="Email" required>
<input name="contact_no" placeholder="Contact No" required>

<select name="role">
<option value="user">User</option>
<option value="admin">Admin</option>
<option value="accountant">Accountant</option>
<option value="agent">Agent</option>
</select>

<input name="barangay" placeholder="Barangay">
<input name="street" placeholder="Street">
<input name="meter_number" placeholder="Meter No">

<select name="status">
<option value="active">Active</option>
<option value="inactive">Inactive</option>
</select>

<input name="password" placeholder="Password" required>

<button name="add_user">Add</button>
</form>

<!-- USER TABLE -->
<h3>All Users</h3>
<table>
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Role</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php while($u=$users->fetch_assoc()): ?>
<tr>
<td><?php echo $u['user_code']; ?></td>
<td><?php echo $u['first_name']." ".$u['last_name']; ?></td>
<td><?php echo $u['email']; ?></td>
<td><?php echo $u['role']; ?></td>
<td><?php echo $u['status']; ?></td>
<td>
    <a href="?delete=<?php echo $u['id']; ?>">
        <button>Delete</button>
    </a>
</td>
</tr>
<?php endwhile; ?>
</table>

</div>
</div>