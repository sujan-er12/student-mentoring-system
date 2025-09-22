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

// Fetch mentor information
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT mentor_id, name, department, email FROM mentors WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$mentor = $stmt->get_result()->fetch_assoc();
$mentor_id = $mentor['mentor_id'];  // Get mentor_id for fetching assigned students

// Fetch students assigned to the mentor
$stmt = $conn->prepare("SELECT name, course FROM students WHERE mentor_id = ?");
$stmt->bind_param('i', $mentor_id);
$stmt->execute();
$students = $stmt->get_result();

// Fetch notifications for the mentor
$stmt = $conn->prepare("SELECT message, sent_at FROM notifications WHERE user_id = ? ORDER BY sent_at DESC");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$notifications = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentor Dashboard</title>
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
        .card {
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: navy;
            border-color: navy;
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
            
            <li class="nav-item active">
                <a class="nav-link" href="schedule_meeting.php"><i class="fas fa-calendar-alt"></i> Schedule Meeting</a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="view_meetings.php"><i class="fas fa-calendar-alt"></i> view all Meeting</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_notifications.php"><i class="fas fa-bell"></i> Notifications</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_feedbacks.php"><i class="fas fa-comments"></i> View Feedback</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <!-- Mentor Info Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Welcome, <?php echo htmlspecialchars($mentor['name']); ?>!</h5>
                    <p class="card-text"><strong>Department:</strong> <?php echo htmlspecialchars($mentor['department']); ?></p>
                    <p class="card-text"><strong>Email:</strong> <?php echo htmlspecialchars($mentor['email']); ?></p>
                </div>
            </div>
        </div>

        <!-- Notifications Card -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h5>Recent Notifications</h5></div>
                <ul class="list-group list-group-flush">
                    <?php while ($notification = $notifications->fetch_assoc()): ?>
                        <li class="list-group-item">
                            <?php echo htmlspecialchars($notification['message']); ?>
                            <span class="text-muted float-right">(<?php echo date('d M Y, h:i A', strtotime($notification['sent_at'])); ?>)</span>
                        </li>
                    <?php endwhile; ?>
                    <?php if ($notifications->num_rows === 0): ?>
                        <li class="list-group-item">No new notifications.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Assigned Students Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h5>Assigned Students</h5></div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>Name</th>
                                <th>Course</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($student = $students->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['course']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if ($students->num_rows === 0): ?>
                                <tr><td colspan="3">No students assigned yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
