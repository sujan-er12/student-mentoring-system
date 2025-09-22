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

// Fetch student information
$stmt = $conn->prepare("SELECT name, course FROM students WHERE user_id = ?");
$stmt->bind_param('i', $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// Fetch latest overall sentiment result
$stmt = $conn->prepare("
    SELECT sentiment, created_at 
    FROM question 
    WHERE student_id = ? AND question LIKE '%Overall Sentiment%'
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->bind_param('i', $student_id);
$stmt->execute();
$sentiment_result = $stmt->get_result()->fetch_assoc();

$latest_sentiment = $sentiment_result ? ucfirst($sentiment_result['sentiment']) : "No sentiment data available";
$sentiment_date = $sentiment_result ? date('d M Y, h:i A', strtotime($sentiment_result['created_at'])) : "-";

// Fetch scheduled meetings
$stmt = $conn->prepare("
    SELECT m.meeting_date, m.meeting_purpose, mn.name AS mentor_name
    FROM meetings m
    JOIN students s ON m.student_id = s.student_id
    JOIN mentors mn ON m.mentor_id = mn.mentor_id
    WHERE s.user_id = ?
    ORDER BY m.meeting_date ASC
");
$stmt->bind_param('i', $student_id);
$stmt->execute();
$meetings = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background-color: #f4f4f9; }
        .navbar { background-color: navy; }
        .navbar-brand, .nav-link { color: white !important; }
        .btn-primary { background-color: navy; border-color: navy; }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <a class="navbar-brand" href="#">Student Dashboard</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a class="nav-link" href="student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="view_progress.php"><i class="fas fa-chart-line"></i> Progress</a></li>
            <li class="nav-item"><a class="nav-link" href="view_notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
            <li class="nav-item"><a class="nav-link" href="feedback.php"><i class="fas fa-comment"></i> Feedback</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="mb-4">Welcome, <?php echo htmlspecialchars($student['name']); ?>!</h3>

    <!-- Student Info -->
    <div class="card mb-4">
        <div class="card-header">Your Information</div>
        <div class="card-body">
            <p><strong>Course:</strong> <?php echo htmlspecialchars($student['course']); ?></p>
        </div>
    </div>

    <!-- Scheduled Meetings -->
    <div class="card mb-4">
        <div class="card-header">Scheduled Meetings</div>
        <div class="card-body">
            <table class="table table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Meeting Date & Time</th>
                        <th>Purpose</th>
                        <th>Mentor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($meeting = $meetings->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('d M Y, h:i A', strtotime($meeting['meeting_date'])); ?></td>
                            <td><?php echo htmlspecialchars($meeting['meeting_purpose']); ?></td>
                            <td><?php echo htmlspecialchars($meeting['mentor_name']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($meetings->num_rows === 0): ?>
                        <tr><td colspan="3">No scheduled meetings.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Overall Sentiment Result -->
    <div class="card">
        <div class="card-header">Overall Sentiment Result</div>
        <div class="card-body">
            <p><strong>Last Updated:</strong> <?php echo $sentiment_date; ?></p>
            <p><strong>Overall Sentiment:</strong> <?php echo $latest_sentiment; ?></p>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>