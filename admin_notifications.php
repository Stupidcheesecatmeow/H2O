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
<html>
<head>
    <title>Admin Notifications</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="notifications.css">
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
                <li><a href="admin_notifications.php">Notifications</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <div class="main">

            <h1>Admin Notifications</h1>

            <?php
            function renderSection($title, $data){
                echo "<div class='notif-section'>";
                echo "<div class='notif-title'>$title</div>";

                if($data->num_rows == 0){
                    echo "<p>No notifications</p>";
                }

                while($n = $data->fetch_assoc()){
                    $class = ($n['status'] == "unread") ? "notif-box unread" : "notif-box";

                    echo "<div class='$class'>";

                    echo "<div class='notif-header'>";
                    echo "<span>".$n['title']."</span>";
                    echo "<span>".strtoupper($n['status'])."</span>";
                    echo "</div>";

                    echo "<div class='notif-msg'>".$n['message']."</div>";

                    echo "<div class='notif-footer'>";
                    if(!empty($n['first_name'])){
                        echo $n['first_name']." ".$n['last_name']." | ";
                    }
                    echo $n['created_at'];
                    echo "</div>";

                    if($n['status']=="unread"){
                        echo "<div class='notif-action'>
                                <a href='?read=".$n['id']."'>
                                    <button>Mark as Read</button>
                                </a>
                            </div>";
                    }

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
    </div>

</body>
</html>