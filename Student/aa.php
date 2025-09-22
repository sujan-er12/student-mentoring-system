<?php
// Start the session
session_start();

// Check if the mentor is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mentor') {
    header("Location: mentor_login.php");
    exit();
}

// Include database connection
require_once 'db_connection.php';

$mentor_id = $_SESSION['user_id'];
$message = "";

// Handle form submissions for Create, Update, and Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        // Create a new notification
        $content = $_POST['content'];

        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, notification_type) VALUES (?, ?, 'general')");
        $stmt->bind_param('is', $mentor_id, $content);
        if ($stmt->execute()) {
            $message = "Notification created successfully!";
        } else {
            $message = "Error creating notification: " . $stmt->error;
        }
    } elseif (isset($_POST['update'])) {
        // Update an existing notification
        $notification_id = $_POST['notification_id'];
        $content = $_POST['content'];

        $stmt = $conn->prepare("UPDATE notifications SET message = ? WHERE notification_id = ? AND user_id = ?");
        $stmt->bind_param('sii', $content, $notification_id, $mentor_id);
        if ($stmt->execute()) {
            $message = "Notification updated successfully!";
        } else {
            $message = "Error updating notification: " . $stmt->error;
        }
    } elseif (isset($_POST['delete'])) {
        // Delete a notification
        $notification_id = $_POST['notification_id'];

        $stmt = $conn->prepare("DELETE FROM notifications WHERE notification_id = ? AND user_id = ?");
        $stmt->bind_param('ii', $notification_id, $mentor_id);
        if ($stmt->execute()) {
            $message = "Notification deleted successfully!";
        } else {
            $message = "Error deleting notification: " . $stmt->error;
        }
    }
}

// Fetch existing notifications
$stmt = $conn->prepare("SELECT notification_id, message, sent_at FROM notifications WHERE user_id = ? ORDER BY sent_at DESC");
$stmt->bind_param('i', $mentor_id);
$stmt->execute();
$notifications = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Notifications</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f4f4f9;
        }
        .navbar {
            background-color: navy;
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        .btn-primary {
            background-color: navy;
            border-color: navy;
        }
        .form-control, .btn {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <a class="navbar-brand" href="#">Mentor Dashboard</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="mentor_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_students.php"><i class="fas fa-users"></i> Students</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="schedule_meeting.php"><i class="fas fa-calendar-alt"></i> Schedule Meeting</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="analyze_feedback.php"><i class="fas fa-chart-line"></i> Analyze Feedback</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_notifications.php"><i class="fas fa-bell"></i> Notifications</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="mb-4">Manage Notifications</h3>
    <?php if (!empty($message)): ?>
        <div class="alert alert-info" role="alert">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- Create Notification Form -->
    <div class="card mb-4">
        <div class="card-header">Create a New Notification</div>
        <div class="card-body">
            <form method="POST" action="">
                <textarea name="content" class="form-control" placeholder="Notification Content" required></textarea>
                <button type="submit" name="create" class="btn btn-primary">Create Notification</button>
            </form>
        </div>
    </div>

    <!-- Previous Notifications -->
    <div class="card">
        <div class="card-header">Previous Notifications</div>
        <div class="card-body">
            <table class="table table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Notification</th>
                        <th>Sent At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($notification = $notifications->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($notification['message']); ?></td>
                            <td><?php echo date('d M Y, h:i A', strtotime($notification['sent_at'])); ?></td>
                            <td>
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                                    <button type="submit" name="delete" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                                <button class="btn btn-warning btn-sm" onclick="editNotification(<?php echo $notification['notification_id']; ?>, '<?php echo addslashes($notification['message']); ?>')">Edit</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function editNotification(notificationId, message) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '';

    form.innerHTML = `
        <input type="hidden" name="notification_id" value="${notificationId}">
        <textarea name="content" class="form-control" required>${message}</textarea>
        <button type="submit" name="update" class="btn btn-primary">Update Notification</button>
    `;

    document.body.appendChild(form);
    form.submit();
}
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
