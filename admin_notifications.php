<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: index.php");
    exit();
}

/* MARK READ */
if (isset($_GET['read'])) {
    $id = $_GET['read'];
    $conn->query("UPDATE notifications SET status='read' WHERE id='$id'");
    header("Location: admin_notifications.php");
    exit();
}

/* FUNCTION */
function getNotifs($conn, $type){
    return $conn->query("
        SELECT n.*, u.first_name, u.last_name, u.email
        FROM notifications n
        LEFT JOIN users u ON n.user_id = u.id
        WHERE n.role_target='admin' AND n.type='$type'
        ORDER BY n.created_at DESC
    ");
}

$complaints = getNotifs($conn, "complaint");
$readings   = getNotifs($conn, "reading");
$profiles   = getNotifs($conn, "profile");
$others     = getNotifs($conn, "general");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>H.O.H Admin Notifications</title>
    <link rel="stylesheet" href="styles/admin_notification.css">
</head>
<body id="mainBody">

<div class="main-content">
    <div class="header-row">
        <h1>ADMIN NOTIFICATIONS</h1>
    </div>
        <?php
        function renderSection($title, $data){
            echo "<div class='notif-section'>";
            echo "<div class='notif-title'>$title</div>";

            if($data->num_rows == 0){
                echo "<p class='no-notif'>No notifications in this category.</p>";
            }

            while($n = $data->fetch_assoc()){
                $isUnread = ($n['status'] == "unread");
                $class = $isUnread ? "notif-box unread" : "notif-box";
                $dateFormatted = date('M d, Y | h:i A', strtotime($n['created_at']));
                $userName = !empty($n['first_name']) ? strtoupper($n['first_name']." ".$n['last_name']) : "SYSTEM";

                echo "<div class='$class'>";
                    // EXIT / DISMISS
                    echo "<a href='?delete=".$n['id']."' class='dismiss-btn' title='Dismiss' onclick='event.stopPropagation(); return confirm(\"Dismiss this notification?\")'>&times;</a>";
                    
                    // CLICKABLE AREA FOR POP-IN
                    echo "<div class='notif-click-area' onclick=\"openNotif('".addslashes($n['title'])."', '".addslashes($n['message'])."', '$userName', '$dateFormatted', '".$n['id']."', '$isUnread')\">";
                        echo "<div class='notif-header'>";
                            echo "<span>".strtoupper($n['title'])."</span>";
                            echo "<span class='status-label'>".strtoupper($n['status'])."</span>";
                        echo "</div>";
                        echo "<div class='notif-footer'>$userName | $dateFormatted</div>";
                    echo "</div>";
                echo "</div>";
            }
            echo "</div>";
        } 

        renderSection("Complaints", $complaints);
        renderSection("Meter Reading", $readings);
        renderSection("Profile Updates", $profiles);
        renderSection("Others", $others);
        ?>
    </div>

        <!-- SIDEBAR -->
        <div class="sidebar-right">
            <img src="assets/logo_name.png" class="side-logo">
            <div class="agent-info" style="color: white; text-align: center; margin-bottom: 30px;">
                <h3>ADMIN</h3>
                <p style="font-size: 0.7rem; opacity: 0.6;">ADMIN DEPT</p>
            </div>
            <nav class="nav-menu">
                <a href="admin_dashboard.php" class="nav-item">DASHBOARD</a>
                <a href="admin_notifications.php" class="nav-item active">NOTIFICATIONS</a>
                <a href="announcements.php" class="nav-item">ANNOUNCEMENTS</a>
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

        <!-- MODAL -->
        <div id="notifModal" class="modal-overlay">
            <div class="modal-content glass-panel">
                <button class="close-btn" onclick="closeNotif()">&times;</button>
                <div class="modal-body">
                    <h2 id="modalTitle">Title</h2>
                    <div class="modal-meta">
                        <span id="modalUser">User</span> • <span id="modalDate">Date</span>
                    </div>
                    <hr style="opacity:0.1; margin: 20px 0;">
                    <p id="modalMsg">Message content goes here...</p>
                    <div id="modalActionArea">
                    </div>
                </div>
            </div>
        </div>

        <script>
        window.onload = () => { document.body.classList.add('fade-in'); };

        function openNotif(title, msg, user, date, id, isUnread) {
            document.getElementById('modalTitle').innerText = title.toUpperCase();
            document.getElementById('modalMsg').innerText = msg;
            document.getElementById('modalUser').innerText = user;
            document.getElementById('modalDate').innerText = date;
            
            const actionArea = document.getElementById('modalActionArea');
            actionArea.innerHTML = '';
            
            if(isUnread === '1') {
                actionArea.innerHTML = `<a href="?read=${id}" class="read-btn-link">MARK AS READ</a>`;
            }

            document.getElementById('notifModal').classList.add('active');
        }

        function closeNotif() {
            document.getElementById('notifModal').classList.remove('active');
        }

        // Close on clicking outside the box
        window.onclick = function(event) {
            let modal = document.getElementById('notifModal');
            if (event.target == modal) { closeNotif(); }
        }
        </script>
</body>
</html>