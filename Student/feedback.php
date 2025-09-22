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

$student_user_id = $_SESSION['user_id'];
$message = "";

// ✅ Fetch the correct `student_id` and mentor details
$stmt = $conn->prepare("SELECT students.student_id, mentors.mentor_id, mentors.name AS mentor_name 
                        FROM students 
                        JOIN mentors ON students.mentor_id = mentors.mentor_id 
                        WHERE students.user_id = ?");
$stmt->bind_param('i', $student_user_id);
$stmt->execute();
$student_data = $stmt->get_result()->fetch_assoc();

if (!$student_data) {
    die("⚠️ Error: Student not found. Please contact the administrator.");
}

$student_id = $student_data['student_id']; 
$mentor_id = $student_data['mentor_id']; 
$mentor_name = $student_data['mentor_name'];

if (!$mentor_id) {
    die("⚠️ Error: No mentor assigned. Please contact the administrator.");
}

// ✅ Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $feedback_text = trim($_POST['feedback_text']);

    if (!empty($feedback_text)) {
        $stmt = $conn->prepare("INSERT INTO feedback (student_id, mentor_id, comments, created_at) VALUES (?, ?, ?, NOW())");
        if (!$stmt) {
            die("❌ Prepare failed: " . $conn->error);
        }

        $stmt->bind_param('iis', $student_id, $mentor_id, $feedback_text);
        if ($stmt->execute()) {
            $message = "✅ Feedback submitted successfully!";
        } else {
            $message = "❌ Error submitting feedback: " . $stmt->error;
        }
    } else {
        $message = "❌ Feedback cannot be empty!";
    }
}

// ✅ Fetch previous feedback
$stmt = $conn->prepare("SELECT comments, created_at FROM feedback WHERE student_id = ? ORDER BY created_at DESC");
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
    <h3 class="mb-4">Submit Feedback to Your Mentor</h3>

    <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>

    <!-- Feedback Submission Form -->
    <div class="card mb-4">
        <div class="card-header">Provide Feedback</div>
        <div class="card-body">
            <p><strong>Mentor:</strong> <?php echo htmlspecialchars($mentor_name); ?></p>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="feedback_text">Your Feedback:</label>
                    <textarea name="feedback_text" id="feedback_text" class="form-control" rows="4" required></textarea>
                </div>
                <button type="submit" name="submit_feedback" class="btn btn-primary">Submit Feedback</button>
            </form>
        </div>
    </div>

    <!-- Previous Feedback -->
    <div class="card">
        <div class="card-header">Your Previous Feedback</div>
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
                            <td><?php echo htmlspecialchars($feedback['comments']); ?></td>
                            <td><?php echo date('d M Y, h:i A', strtotime($feedback['created_at'])); ?></td>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
