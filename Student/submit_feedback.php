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
$message = "";

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $feedback_text = $_POST['feedback_text'];

    if (!empty($feedback_text)) {
        $stmt = $conn->prepare("INSERT INTO feedback (student_id, feedback_text, submitted_at) VALUES (?, ?, NOW())");
        $stmt->bind_param('is', $student_id, $feedback_text);
        if ($stmt->execute()) {
            $message = "Feedback submitted successfully!";
        } else {
            $message = "Error submitting feedback: " . $stmt->error;
        }
    } else {
        $message = "Please enter your feedback.";
    }
}

// Fetch submitted feedback
$stmt = $conn->prepare("SELECT feedback_text, submitted_at FROM feedback WHERE student_id = ? ORDER BY submitted_at DESC");
$stmt->bind_param('i', $student_id);
$stmt->execute();
$feedbacks = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Feedback</title>
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
                <a class="nav-link" href="submit_feedback.php"><i class="fas fa-comments"></i> Submit Feedback</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_notifications.php"><i class="fas fa-bell"></i> Notifications</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="mb-4">Submit Feedback</h3>

    <?php if (!empty($message)): ?>
        <div class="alert alert-info" role="alert">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- Feedback Form -->
    <div class="card mb-4">
        <div class="card-header">Add New Feedback</div>
        <div class="card-body">
            <form method="POST" action="">
                <textarea name="feedback_text" class="form-control" placeholder="Write your feedback here..." required></textarea>
                <button type="submit" name="submit_feedback" class="btn btn-primary">Submit Feedback</button>
            </form>
        </div>
    </div>

    <!-- Submitted Feedbacks -->
    <div class="card">
        <div class="card-header">Your Submitted Feedback</div>
        <div class="card-body">
            <table class="table table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Feedback</th>
                        <th>Submitted At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($feedback = $feedbacks->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($feedback['feedback_text']); ?></td>
                            <td><?php echo date('d M Y, h:i A', strtotime($feedback['submitted_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($feedbacks->num_rows === 0): ?>
                        <tr>
                            <td colspan="2">No feedback submitted yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
