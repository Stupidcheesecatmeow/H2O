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

/* DELETE LOGIC */
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM notifications WHERE id='$id'");
    header("Location: admin_notifications.php");
    exit();
}

/* DATA FETCHING FUNCTION */
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

/* RENDER FUNCTION */
function renderSection($title, $data){
    echo "<div class='glass-panel'>"; 
        echo "<div class='panel-title-bar'>$title</div>";
        
        echo "<div class='table-area'>"; 
            if($data->num_rows == 0){
                echo "<p class='no-notif' style='padding: 20px; opacity: 0.6;'>No notifications in this category.</p>";
            } else {
                echo "<table class='data-table'>";
                echo "<thead>
                        <tr>
                            <th>Title</th>
                            <th>User</th>
                            <th>Date & Time</th>
                            <th style='text-align:center'>Status</th>
                            <th style='text-align:center'>Action</th>
                        </tr>
                    </thead>";
                echo "<tbody>";

                while($n = $data->fetch_assoc()){
                    $isUnread = ($n['status'] == "unread");
                    $rowClass = $isUnread ? "unread-row" : "";
                    $dateFormatted = date('M d, Y | h:i A', strtotime($n['created_at']));
                    $userName = !empty($n['first_name']) ? strtoupper($n['first_name']." ".$n['last_name']) : "SYSTEM";
                    $jsIsUnread = $isUnread ? '1' : '0';
                    
                    echo "<tr class='$rowClass' onclick=\"openNotif('".addslashes($n['title'])."', '".addslashes($n['message'])."', '$userName', '$dateFormatted', '".$n['id']."', '$jsIsUnread')\">";
                        echo "<td><strong>".strtoupper($n['title'])."</strong></td>";
                        echo "<td>$userName</td>";
                        echo "<td><small>$dateFormatted</small></td>";
                        echo "<td style='text-align:center'>
                                <span class='status-pill ".($isUnread ? 'unpaid' : 'paid')."'>".strtoupper($n['status'])."</span>
                            </td>";
                        echo "<td style='text-align:center'>
                                <a href='?delete=".$n['id']."' class='dismiss-btn' onclick='event.stopPropagation(); return confirm(\"Dismiss this notification?\")'>&times;</a>
                            </td>";
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
            }
        echo "</div>"; 
    echo "</div>"; 
}
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
    // --- KEY FIX: CALLING THE FUNCTIONS TO DISPLAY DATA ---
    renderSection("Complaints", $complaints);
    renderSection("Meter Readings", $readings);
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
                <a href="admin_notifications.php" class="nav-item  active">NOTIFICATIONS</a>
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
            <h2 id="modalTitle" style="color: white;">Title</h2>
            <div class="modal-meta" style="color: rgba(255,255,255,0.6);">
                <span id="modalUser">User</span> • <span id="modalDate">Date</span>
            </div>
            <hr style="opacity:0.1; margin: 20px 0;">
            <p id="modalMsg" style="color: white; line-height: 1.6;">Message content goes here...</p>
            <div id="modalActionArea" style="margin-top: 20px; text-align: right;"></div>
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
        actionArea.innerHTML = `<a href="?read=${id}" class="read-btn-link" style="background: #3498db; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-weight: bold;">MARK AS READ</a>`;
    }

    document.getElementById('notifModal').classList.add('active');
}

function closeNotif() {
    document.getElementById('notifModal').classList.remove('active');
}

window.onclick = function(event) {
    let modal = document.getElementById('notifModal');
    if (event.target == modal) { closeNotif(); }
}
</script>
</body>
</html>
