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

// Get the actual mentor_id from the mentors table
$stmt = $conn->prepare("SELECT mentor_id FROM mentors WHERE user_id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$mentor_data = $result->fetch_assoc();

if (!$mentor_data) {
    die("⚠️ Error: Mentor not found. Please contact the administrator.");
}

$mentor_id = $mentor_data['mentor_id'];

// Fetch feedback for this mentor along with student names
$stmt = $conn->prepare("
    SELECT feedback.feedback_id, feedback.comments AS feedback_text, feedback.created_at, 
           students.name AS student_name
    FROM feedback 
    JOIN students ON feedback.student_id = students.student_id
    WHERE feedback.mentor_id = ?
    ORDER BY feedback.created_at DESC
");

$stmt->bind_param('i', $mentor_id);
$stmt->execute();
$feedbacks = $stmt->get_result();

// Simple sentiment analysis function
function getSentiment($text) {
    $positive_words = ['good', 'great', 'excellent', 'awesome', 'fantastic', 'love', 'positive', 'satisfied'];
    $negative_words = ['bad', 'poor', 'terrible', 'worst', 'hate', 'negative', 'unsatisfied'];

    $text = strtolower($text);
    $score = 0;

    foreach ($positive_words as $word) {
        if (strpos($text, $word) !== false) $score++;
    }
    foreach ($negative_words as $word) {
        if (strpos($text, $word) !== false) $score--;
    }

    if ($score > 0) return 'Positive';
    elseif ($score < 0) return 'Negative';
    else return 'Neutral';
}

// Analyze sentiments
$sentiment_counts = ['Positive' => 0, 'Negative' => 0, 'Neutral' => 0];
$feedback_data = [];

while ($feedback = $feedbacks->fetch_assoc()) {
    $sentiment = getSentiment($feedback['feedback_text']);
    $sentiment_counts[$sentiment]++;
    $feedback['sentiment'] = $sentiment;
    $feedback_data[] = $feedback;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Feedback</title>
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
    <a class="navbar-brand" href="#">Mentor Dashboard</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a class="nav-link" href="mentor_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="schedule_meeting.php"><i class="fas fa-calendar-alt"></i> Schedule Meeting</a></li>
            <li class="nav-item"><a class="nav-link" href="view_meetings.php"><i class="fas fa-calendar-alt"></i> View All Meetings</a></li>
            <li class="nav-item"><a class="nav-link" href="manage_notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
            <li class="nav-item"><a class="nav-link" href="view_feedbacks.php"><i class="fas fa-comments"></i> View Feedback</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="mb-4">View Feedback</h3>

    <!-- Feedback List -->
    <div class="card">
        <div class="card-header">Feedback from Assigned Students</div>
        <div class="card-body">
            <table class="table table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Student Name</th>
                        <th>Feedback</th>
                        <th>Submitted At</th>
                        <th>Sentiment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feedback_data as $feedback): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($feedback['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($feedback['feedback_text']); ?></td>
                            <td><?php echo date('d M Y, h:i A', strtotime($feedback['submitted_at'])); ?></td>
                            <td><span class="badge 
                                <?php 
                                    echo $feedback['sentiment'] === 'Positive' ? 'badge-success' : 
                                         ($feedback['sentiment'] === 'Negative' ? 'badge-danger' : 'badge-secondary');
                                ?>">
                                <?php echo $feedback['sentiment']; ?>
                            </span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (count($feedback_data) === 0): ?>
                        <tr><td colspan="4">No feedback available.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sentiment Chart -->
    <div class="mt-5">
        <h4>Sentiment Analysis Summary</h4>
        <canvas id="sentimentChart" height="100"></canvas>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('sentimentChart').getContext('2d');
    const sentimentChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Positive', 'Neutral', 'Negative'],
            datasets: [{
                label: 'Number of Feedbacks',
                data: [
                    <?= $sentiment_counts['Positive'] ?>,
                    <?= $sentiment_counts['Neutral'] ?>,
                    <?= $sentiment_counts['Negative'] ?>
                ],
                backgroundColor: ['green', 'gray', 'red']
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
