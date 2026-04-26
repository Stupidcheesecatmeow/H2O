<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: index.php");
    exit();
}

if (isset($_POST['reply_complaint'])) {

    $complaint_id = $_POST['complaint_id'];
    $reply = $_POST['reply'];
    $status = $_POST['status'];

    /* KUHANIN USER NG COMPLAINT */
    $c = $conn->query("
        SELECT user_id, complaint_type 
        FROM complaints 
        WHERE id='$complaint_id'
    ")->fetch_assoc();

    /* UPDATE COMPLAINT */
    $stmt = $conn->prepare("UPDATE complaints 
    SET reply=?, status=?, replied_at=NOW() 
    WHERE id=?");

    $stmt->bind_param("ssi", $reply, $status, $complaint_id);
    $stmt->execute();

    /* NOTIFY USER */
    $title = "Complaint Update";

    $message = "Your complaint '{$c['complaint_type']}' has been reviewed. 
    Status: $status. Click to view reply.";

    $notif = $conn->prepare("INSERT INTO notifications
    (user_id, role_target, title, message, type, status, link)
    VALUES (?, 'user', ?, ?, 'complaint', 'unread', 'user_complaints.php')");

    $notif->bind_param("iss", $c['user_id'], $title, $message);
    $notif->execute();

    echo "<script>alert('Complaint updated'); window.location='complaints_admin.php';</script>";
    exit();
}

$complaints = $conn->query("
    SELECT c.*, u.first_name, u.last_name
    FROM complaints c
    JOIN users u ON c.user_id = u.id
    ORDER BY c.created_at DESC
");
?>

<link rel="stylesheet" href="dashboard.css">

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

        <h1>Complaint Panel</h1>

        <table>
            <tr>
                <th>Customer</th>
                <th>Case</th>
                <th>Message</th>
                <th>Reply</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php while($c = $complaints->fetch_assoc()): ?>
            <tr>
                <td><?php echo $c['first_name']." ".$c['last_name']; ?></td>
                <td><?php echo $c['subject']; ?></td>
                <td><?php echo $c['message']; ?></td>
                <td><?php echo $c['reply']; ?></td>
                <td><?php echo $c['status']; ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="complaint_id" value="<?php echo $c['id']; ?>">
                        <textarea name="reply" placeholder="Reply" required></textarea>

                        <select name="status">
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                        </select>

                        <button name="reply_complaint">Reply</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>

    </div>
</div>