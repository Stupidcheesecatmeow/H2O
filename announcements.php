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
<html>
<head>
    <title>Announcements</title>
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

            <h1>Announcement Panel</h1>

            <div class="table-box">
                <form method="POST">
                    <input type="text" name="title" placeholder="Title" required>
                    <input type="date" name="announcement_date" required>

                    <select name="target_type" id="targetType" required>
                        <option value="everyone">Everyone</option>
                        <option value="barangay">Per Barangay</option>
                    </select>

                    <select name="barangay" id="barangaySelect">
                        <option value="">Select Barangay</option>

                        <?php foreach($barangays as $b): ?>
                            <option value="<?php echo $b; ?>">
                                <?php echo $b; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <textarea name="message" placeholder="Message" required></textarea>

                    <button name="post_announcement">Post Announcement</button>
                </form>
            </div>

            <h2>Posted Announcements</h2>

            <table>
                <tr>
                    <th>Title</th>
                    <th>Message</th>
                    <th>Target</th>
                    <th>Barangay</th>
                    <th>Date</th>
                    <th>Posted By</th>
                </tr>

                <?php while($a = $announcements->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $a['title']; ?></td>
                    <td><?php echo $a['message']; ?></td>
                    <td><?php echo $a['target_type']; ?></td>
                    <td><?php echo $a['barangay']; ?></td>
                    <td><?php echo $a['announcement_date']; ?></td>
                    <td><?php echo $a['first_name']." ".$a['last_name']; ?></td>
                </tr>
                <?php endwhile; ?>
            </table>

            <script>
                const targetType = document.getElementById("targetType");
                const barangaySelect = document.getElementById("barangaySelect");

                function toggleBarangay(){
                    if(targetType.value === "everyone"){
                        barangaySelect.disabled = true;
                        barangaySelect.value = "";
                    }else{
                        barangaySelect.disabled = false;
                    }
                }

                // run on load
                toggleBarangay();

                // run on change
                targetType.addEventListener("change", toggleBarangay);
            </script>

        </div>
    </div>

</body>
</html>