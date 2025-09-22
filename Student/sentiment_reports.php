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

// Fetch feedback and sentiment analysis reports
$stmt = $conn->prepare(
    "SELECT feedback.feedback_id, feedback.feedback_text, feedback.sentiment, students.name AS student_name, feedback.submitted_at 
     FROM feedback 
     JOIN students ON feedback.student_id = students.student_id 
     WHERE feedback.mentor_id = ? 
     ORDER BY feedback.submitted_at DESC"
);
$stmt->bind_param('i', $mentor_id);
$stmt->execute();
$feedbacks = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sentiment Analysis Reports</title>
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
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #e9ecef;
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
                <a class="nav-link" href="sentiment_reports.php"><i class="fas fa-file-alt"></i> Sentiment Reports</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="mb-4">Sentiment Analysis Reports</h3>
    <div class="card">
        <div class="card-body">
            <table class="table table-striped table-responsive">
                <thead class="thead-dark">
                    <tr>
                        <th>Feedback ID</th>
                        <th>Student Name</th>
                        <th>Feedback</th>
                        <th>Sentiment</th>
                        <th>Submitted At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($feedback = $feedbacks->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $feedback['feedback_id']; ?></td>
                            <td><?php echo htmlspecialchars($feedback['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($feedback['feedback_text']); ?></td>
                            <td>
                                <span class="badge 
                                    <?php echo $feedback['sentiment'] === 'Positive' ? 'badge-success' : ($feedback['sentiment'] === 'Negative' ? 'badge-danger' : 'badge-warning'); ?>">
                                    <?php echo $feedback['sentiment']; ?>
                                </span>
                            </td>
                            <td><?php echo date('d M Y, h:i A', strtotime($feedback['submitted_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
