<?php
session_start();
include "db.php";
include "barangay.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: index.php");
    exit();
}

$admin_id = $_SESSION['user_id'];

if (isset($_POST['post_announcement'])) {
    $title = $_POST['title'];
    $message = $_POST['message'];
    $target_type = $_POST['target_type'];
    $barangay = $_POST['barangay'];
    $date = $_POST['announcement_date'];

    if ($target_type == "everyone") {
        $barangay = "";
    }

    $stmt = $conn->prepare("INSERT INTO announcements 
    (title, message, target_type, barangay, posted_by, announcement_date)
    VALUES (?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssssis", $title, $message, $target_type, $barangay, $admin_id, $date);
    $stmt->execute();

    echo "<script>alert('Announcement posted'); window.location='announcements.php';</script>";
    exit();
}

$announcements = $conn->query("
    SELECT a.*, u.first_name, u.last_name 
    FROM announcements a
    LEFT JOIN users u ON a.posted_by = u.id
    ORDER BY a.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Announcement Panel | H2O</title>
    <link rel="stylesheet" href="announcement.css">
</head>
<body id="mainBody">

    <div class="main-content">
        <div class="header-row">
            <h1>ANNOUNCEMENT PANEL</h1>
        </div>

        <!-- POST FORM PANEL -->
        <div class="glass-panel">
            <div class="panel-title-bar">Create New Announcement</div>
            <div class="content-area">
                <form method="POST" class="announcement-form">
                    <div class="form-group">
                        <label>Announcement Title</label>
                        <input type="text" name="title" placeholder="e.g. Scheduled Maintenance" required>
                    </div>

                    <div class="form-group">
                        <label>Effective Date</label>
                        <input type="date" name="announcement_date" required>
                    </div>

                    <div class="form-group">
                        <label>Target Audience</label>
                        <select name="target_type" id="targetType" required>
                            <option value="everyone">Everyone</option>
                            <option value="barangay">Specific Barangay</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Barangay (If applicable)</label>
                        <select name="barangay" id="barangaySelect">
                            <option value="">-- Select Barangay --</option>
                            <?php foreach($barangays as $b): ?>
                                <option value="<?php echo $b; ?>"><?php echo $b; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label>Message Content</label>
                        <textarea name="message" placeholder="Type your announcement details here..." required></textarea>
                    </div>

                    <button name="post_announcement" class="post-btn">Post Announcement</button>
                </form>
            </div>
        </div>

        <!-- HISTORY TABLE PANEL -->
        <div class="glass-panel">
            <div class="panel-title-bar">Posted Announcements History</div>
            <div class="table-area">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Target</th>
                            <th>Barangay</th>
                            <th>Date</th>
                            <th>Posted By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($a = $announcements->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo $a['title']; ?></strong></td>
                            <td><?php echo strtoupper($a['target_type']); ?></td>
                            <td><?php echo $a['barangay'] ?: '<span style="opacity:0.4">N/A</span>'; ?></td>
                            <td><?php echo date('M d, Y', strtotime($a['announcement_date'])); ?></td>
                            <td><small><?php echo $a['first_name']; ?></small></td>
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
            <a href="announcements.php" class="nav-item active">ANNOUNCEMENTS</a>
            <a href="user_management.php" class="nav-item">USER MANAGEMENT</a>
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
        // Entrance Transition
        window.onload = () => { document.body.classList.add('fade-in'); };

        const targetType = document.getElementById("targetType");
        const barangaySelect = document.getElementById("barangaySelect");

        function toggleBarangay(){
            if(targetType.value === "everyone"){
                barangaySelect.disabled = true;
                barangaySelect.style.opacity = "0.3";
                barangaySelect.value = "";
            } else {
                barangaySelect.disabled = false;
                barangaySelect.style.opacity = "1";
            }
        }

        toggleBarangay();
        targetType.addEventListener("change", toggleBarangay);
    </script>
</body>
</html>
