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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Field Agent Management | H2O</title>
    <link rel="stylesheet" href="agent_management.css">
</head>
<body id="mainBody">

    <div class="main-content">
        <div class="header-row">
            <h1>FIELD AGENT MANAGEMENT</h1>
        </div>

        <!-- STATS CARDS -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-label">Total Field Agents</span>
                <span class="stat-value">
                    <?php echo $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='agent'")->fetch_assoc()['total']; ?>
                </span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Active Agents</span>
                <span class="stat-value" style="color: var(--success);">
                    <?php echo $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='agent' AND status='active'")->fetch_assoc()['total']; ?>
                </span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Assigned Areas</span>
                <span class="stat-value" style="color: var(--accent-blue);">
                    <?php echo $conn->query("SELECT COUNT(*) AS total FROM agent_assignments")->fetch_assoc()['total']; ?>
                </span>
            </div>
        </div>

        <!-- ASSIGNMENT FORM PANEL -->
        <div class="glass-panel">
            <div class="panel-title-bar">Assign / Update Field Area</div>
            <div class="content-area">
                <form method="POST" class="assignment-form-grid">
                    <div class="form-group">
                        <label>Select Field Agent</label>
                        <select name="agent_id" required>
                            <option value="">-- Choose Agent --</option>
                            <?php while($a = $agent_options->fetch_assoc()): ?>
                                <option value="<?php echo $a['id']; ?>">
                                    <?php echo ($a['user_code'] ?? $a['id'])." - ".$a['first_name']." ".$a['last_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Assigned Barangay</label>
                        <select name="area" required>
                            <option value="">-- Select Barangay --</option>
                            <?php foreach($barangays as $b): ?>
                                <option value="<?php echo $b; ?>"><?php echo $b; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" name="assign_area" class="save-btn">SAVE ASSIGNMENT</button>
                </form>
            </div>
        </div>

        <!-- AGENT LIST PANEL -->
        <div class="glass-panel">
            <div class="panel-title-bar">Active Field Personnel</div>
            <div class="table-area">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Agent ID</th>
                            <th>Full Name</th>
                            <th>Contact</th>
                            <th>Assigned Area</th>
                            <th>Status</th>
                            <th style="text-align:center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $agents->fetch_assoc()): 
                            $statusClass = ($row['status'] == 'active') ? 'paid' : 'unpaid';
                        ?>
                        <tr>
                            <td><span class="id-badge"><?php echo $row['user_code'] ?? $row['agent_id']; ?></span></td>
                            <td><strong><?php echo strtoupper($row['first_name']." ".$row['last_name']); ?></strong><br><small style="opacity:0.6"><?php echo $row['email']; ?></small></td>
                            <td><?php echo $row['contact_no']; ?></td>
                            <td><span style="color: var(--accent-blue); font-weight:bold;"><?php echo $row['area'] ?? "NOT ASSIGNED"; ?></span></td>
                            <td><span class="status-pill <?php echo $statusClass; ?>"><?php echo strtoupper($row['status']); ?></span></td>
                            <td style="text-align:center">
                                <a href="?toggle_status=<?php echo $row['agent_id']; ?>">
                                    <button class="action-btn"><?php echo ($row['status'] == 'active') ? 'Deactivate' : 'Activate'; ?></button>
                                </a>
                                <?php if($row['assignment_id']): ?>
                                    <a href="?delete_assignment=<?php echo $row['assignment_id']; ?>" onclick="return confirm('Delete this assignment?')">
                                        <button class="action-btn delete-btn">Unassign</button>
                                    </a>
                                <?php endif; ?>
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
            <h3>ACCOUNTANT</h3>
            <p style="font-size: 0.7rem; opacity: 0.6;">FINANCE DEPT</p>
        </div>
        <nav class="nav-menu">
            <a href="admin_dashboard.php" class="nav-item">DASHBOARD</a>
            <a href="admin_notifications.php" class="nav-item">NOTIFICATIONS</a>
            <a href="announcements.php" class="nav-item">ANNOUNCEMENTS</a>
            <a href="user_management.php" class="nav-item">USER MANAGEMENT</a>
            <a href="agent_management.php" class="nav-item active">FIELD AGENTS</a>
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
        window.onload = () => { document.body.style.opacity = "1"; };
        
        // Quick Fade transitions
        document.querySelectorAll('.nav-item, .logout-btn-container').forEach(link => {
            link.addEventListener('click', function(e) {
                const target = this.getAttribute('href');
                if (target && target !== '#') {
                    e.preventDefault();
                    document.body.style.opacity = "0";
                    setTimeout(() => { window.location.href = target; }, 200);
                }
            });
        });
    </script>
</body>
</html>
