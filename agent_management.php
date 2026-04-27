<?php
session_start();
include "db.php";
include "barangay.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: index.php");
    exit();
}

/* SAVE / UPDATE AGENT ASSIGNMENT */
if (isset($_POST['assign_area'])) {
    $agent_id = $_POST['agent_id'];
    $area = $_POST['area'];
    $total_households = $_POST['total_households'];

    $check = $conn->prepare("SELECT id FROM agent_assignments WHERE agent_id = ?");
    $check->bind_param("i", $agent_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE agent_assignments 
                                SET area = ?, total_households = ? 
                                WHERE agent_id = ?");
        $stmt->bind_param("sii", $area, $total_households, $agent_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO agent_assignments 
                                (agent_id, area, total_households) 
                                VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $agent_id, $area, $total_households);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Field agent assignment saved'); window.location='agent_management.php';</script>";
        exit();
    } else {
        echo "<script>alert('Failed to save assignment'); window.location='agent_management.php';</script>";
        exit();
    }
}

/* DELETE ASSIGNMENT */
if (isset($_GET['delete_assignment'])) {
    $id = $_GET['delete_assignment'];

    $stmt = $conn->prepare("DELETE FROM agent_assignments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo "<script>alert('Assignment deleted'); window.location='agent_management.php';</script>";
    exit();
}

/* ACTIVATE / DEACTIVATE AGENT */
if (isset($_GET['toggle_status'])) {
    $agent_id = $_GET['toggle_status'];

    $agent = $conn->query("SELECT status FROM users WHERE id='$agent_id' AND role='agent'")->fetch_assoc();

    if ($agent) {
        $new_status = ($agent['status'] == 'active') ? 'inactive' : 'active';

        $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'agent'");
        $stmt->bind_param("si", $new_status, $agent_id);
        $stmt->execute();
    }

    header("Location: agent_management.php");
    exit();
}

/* AGENT DROPDOWN */
$agent_options = $conn->query("
    SELECT * FROM users 
    WHERE role='agent'
    ORDER BY first_name ASC
");

/* AGENT LIST */
$agents = $conn->query("
    SELECT 
        u.id AS agent_id,
        u.user_code,
        u.first_name,
        u.last_name,
        u.email,
        u.contact_no,
        u.status,
        aa.id AS assignment_id,
        aa.area,
        aa.total_households
    FROM users u
    LEFT JOIN agent_assignments aa ON u.id = aa.agent_id
    WHERE u.role='agent'
    ORDER BY u.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Field Agent Management</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

    <div class="layout">

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

            <h1>Field Agent Management</h1>

            <div class="cards">
                <div class="card">
                    Total Field Agents<br>
                    <strong>
                        <?php echo $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='agent'")->fetch_assoc()['total']; ?>
                    </strong>
                </div>

                <div class="card">
                    Active Agents<br>
                    <strong>
                        <?php echo $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='agent' AND status='active'")->fetch_assoc()['total']; ?>
                    </strong>
                </div>

                <div class="card">
                    Assigned Areas<br>
                    <strong>
                        <?php echo $conn->query("SELECT COUNT(*) AS total FROM agent_assignments")->fetch_assoc()['total']; ?>
                    </strong>
                </div>
            </div>

            <div class="table-box">
                <h3>Assign / Update Area</h3>

                <form method="POST">
                    <select name="agent_id" required>
                        <option value="">Select Field Agent</option>

                        <?php while($a = $agent_options->fetch_assoc()): ?>
                            <option value="<?php echo $a['id']; ?>">
                                <?php echo ($a['user_code'] ?? $a['id'])." - ".$a['first_name']." ".$a['last_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <select name="area" required>
                        <option value="">Select Assigned Barangay</option>

                        <?php foreach($barangays as $b): ?>
                            <option value="<?php echo $b; ?>">
                                <?php echo $b; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" name="assign_area">Save Assignment</button>
                </form>
            </div>

            <h3>Field Agent List</h3>

            <table>
                <tr>
                    <th>Agent ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Assigned Area</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                <?php while($row = $agents->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['user_code'] ?? $row['agent_id']; ?></td>
                    <td><?php echo $row['first_name']." ".$row['last_name']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['contact_no']; ?></td>
                    <td><?php echo $row['area'] ?? "Not assigned"; ?></td>  
                    <td><?php echo $row['status']; ?></td>
                    <td>
                        <a href="?toggle_status=<?php echo $row['agent_id']; ?>">
                            <button type="button">
                                <?php echo ($row['status'] == 'active') ? 'Deactivate' : 'Activate'; ?>
                            </button>
                        </a>

                        <?php if($row['assignment_id']): ?>
                            <a href="?delete_assignment=<?php echo $row['assignment_id']; ?>" onclick="return confirm('Delete this assignment?')">
                                <button type="button">Delete Assignment</button>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>

        </div>
    </div>

</body>
</html>