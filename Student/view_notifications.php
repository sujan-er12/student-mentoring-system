<?php
// Start the session
session_start();

// Check if the student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: student_login.php");
    exit();
}

// Include database connection
require_once 'db_connection.php';

$student_id = $_SESSION['user_id'];

// Pagination setup
$limit = 10; // Number of notifications per page
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch total notification count
$total_stmt = $conn->prepare("SELECT COUNT(*) as total FROM notifications WHERE user_id = ?");
$total_stmt->bind_param('i', $student_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_notifications = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_notifications / $limit);

// Fetch notifications for the student with pagination
$stmt = $conn->prepare("
    SELECT message, sent_at, notification_type 
    FROM notifications 
    WHERE user_id = ? 
    ORDER BY sent_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->bind_param('iii', $student_id, $limit, $offset);
$stmt->execute();
$notifications = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Notifications</title>
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
        .badge-new {
            background-color: red;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <a class="navbar-brand" href="#">Student Dashboard</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
        <li class="nav-item">
                <a class="nav-link" href="student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_progress.php"><i class="fas fa-chart-line"></i> Progress</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_notifications.php"><i class="fas fa-bell"></i> Notifications</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="feedback.php"><i class="fas fa-comment"></i> Feedback</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="mb-4">Your Notifications</h3>

    <!-- Notifications List -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>All Notifications</h5>
        </div>
        <div class="card-body">
            <ul class="list-group">
                <?php while ($notification = $notifications->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <?php if ($notification['notification_type'] === 'meeting'): ?>
                            <i class="fas fa-calendar-alt text-primary"></i>
                        <?php else: ?>
                            <i class="fas fa-bell text-warning"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($notification['message']); ?>
                        <span class="text-muted float-right">
                            (<?php echo date('d M Y, h:i A', strtotime($notification['sent_at'])); ?>)
                        </span>
                    </li>
                <?php endwhile; ?>
                <?php if ($notifications->num_rows === 0): ?>
                    <li class="list-group-item text-center">No notifications found.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="view_notifications.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
