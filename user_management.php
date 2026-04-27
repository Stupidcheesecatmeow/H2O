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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>H.O.H User Management</title>
    <link rel="stylesheet" href="styles/user_management.css">
</head>
<body id="mainBody">

    <div class="main-content">
        <div class="header-row">
            <h1>USER MANAGEMENT</h1>
        </div>

        <!-- ADD USER FORM PANEL -->
        <div class="glass-panel">
            <div class="panel-title-bar">Register New Account</div>
            <div class="table-area" style="padding: 25px;">
                <form method="POST" class="mgmt-form-grid">
                    <div class="input-group">
                        <label>First Name</label>
                        <input name="first_name" placeholder="Enter first name" required>
                    </div>
                    <div class="input-group">
                        <label>Last Name</label>
                        <input name="last_name" placeholder="Enter last name" required>
                    </div>
                    <div class="input-group">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="email@example.com" required>
                    </div>
                    <div class="input-group">
                        <label>Contact Number</label>
                        <input name="contact_no" placeholder="09XXXXXXXXX" required>
                    </div>
                    <div class="input-group">
                        <label>System Role</label>
                        <select name="role">
                            <option value="user">User / Consumer</option>
                            <option value="admin">Administrator</option>
                            <option value="accountant">Accountant</option>
                            <option value="agent">Field Agent</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Barangay</label>
                        <input name="barangay" placeholder="Assigned Area">
                    </div>
                    <div class="input-group">
                        <label>Street / Address</label>
                        <input name="street" placeholder="House No / Street">
                    </div>
                    <div class="input-group">
                        <label>Meter Number</label>
                        <input name="meter_number" placeholder="Enter meter ID">
                    </div>
                    <div class="input-group">
                        <label>Account Status</label>
                        <select name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Set default password" required>
                    </div>

                    <button name="add_user" class="btn-add">REGISTER ACCOUNT</button>
                </form>
            </div>
        </div>

        <!-- USER LIST PANEL -->
        <div class="glass-panel">
            <div class="panel-title-bar">All Registered Users</div>
            <div class="table-area">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th style="text-align:center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($u=$users->fetch_assoc()): ?>
                        <tr>
                            <td><span class="id-badge"><?php echo $u['user_code']; ?></span></td>
                            <td><strong><?php echo strtoupper($u['first_name']." ".$u['last_name']); ?></strong></td>
                            <td><?php echo $u['email']; ?></td>
                            <td><span class="role-tag"><?php echo strtoupper($u['role']); ?></span></td>
                            <td>
                                <span class="status-pill <?php echo ($u['status']=='active') ? 'paid' : 'unpaid'; ?>">
                                    <?php echo strtoupper($u['status']); ?>
                                </span>
                            </td>
                            <td style="text-align:center">
                                <a href="?delete=<?php echo $u['id']; ?>" onclick="return confirm('Permanently delete this user?')">
                                    <button class="btn-delete">DELETE</button>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SIDEBAR RIGHT -->
    <div class="sidebar-right">
        <img src="assets/logo_name.png" class="side-logo">
        <div class="agent-info" style="color: white; text-align: center; margin-bottom: 30px;">
            <h3>ADMIN</h3>
            <p style="font-size: 0.7rem; opacity: 0.6;">ADMIN DEPT</p>
        </div>
        <nav class="nav-menu">
            <a href="admin_dashboard.php" class="nav-item">DASHBOARD</a>
            <a href="admin_notifications.php" class="nav-item">NOTIFICATIONS</a>
            <a href="announcements.php" class="nav-item">ANNOUNCEMENTS</a>
            <a href="user_management.php" class="nav-item active">USER MANAGEMENT</a>
            <a href="agent_management.php" class="nav-item">FIELD AGENTS</a>
            <a href="invoices.php" class="nav-item">INVOICES</a>
            <a href="transactions.php" class="nav-item">TRANSACTIONS</a>
            <a href="complaints_admin.php" class="nav-item">COMPLAINTS</a>
            <a href="reports.php" class="nav-item">REPORTS</a>
            <a href="profile.php" class="nav-item">PROFILE</a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn-container">LOG OUT</a>
        </div>
    </div>

    <script>
        window.onload = () => { document.body.classList.add('fade-in'); };
    </script>
</body>
</html>
