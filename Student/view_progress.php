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

// Get the correct student_id from students table
$stmt = $conn->prepare("SELECT student_id, name, course FROM students WHERE user_id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$student_id = $student['student_id'];

// Fetch latest overall sentiment result
$stmt = $conn->prepare("
    SELECT sentiment, created_at 
    FROM weekly_questions 
    WHERE student_id = ? AND question = 'Overall Sentiment Result'
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->bind_param('i', $student_id);
$stmt->execute();
$sentiment_result = $stmt->get_result()->fetch_assoc();

$latest_sentiment = $sentiment_result ? ucfirst($sentiment_result['sentiment']) : "No sentiment data available";
$sentiment_date = $sentiment_result ? date('d M Y, h:i A', strtotime($sentiment_result['created_at'])) : "-";

// Fetch sentiment history for chart
$stmt = $conn->prepare("
    SELECT sentiment, created_at 
    FROM weekly_questions 
    WHERE student_id = ? AND question = 'Overall Sentiment Result'
    ORDER BY created_at ASC
");
$stmt->bind_param('i', $student_id);
$stmt->execute();
$sentiment_history = $stmt->get_result();

// Prepare chart data
$chart_data = [];
while ($row = $sentiment_history->fetch_assoc()) {
    $chart_data[] = [
        "date" => date('d M', strtotime($row['created_at'])),
        "sentiment" => $row['sentiment']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Progress</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    <!-- Latest Overall Sentiment -->
    <div class="card mb-4">
        <div class="card-header">Latest Overall Sentiment</div>
        <div class="card-body">
            <p><strong>Last Updated:</strong> <?php echo $sentiment_date; ?></p>
            <p><strong>Overall Sentiment:</strong> <?php echo $latest_sentiment; ?></p>
        </div>
    </div>

    <!-- Sentiment Progress Chart -->
    <div class="card">
        <div class="card-header">Sentiment Progress Over Time</div>
        <div class="card-body">
            <canvas id="sentimentChart"></canvas>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('sentimentChart').getContext('2d');
const chartData = <?php echo json_encode($chart_data); ?>;

// Convert sentiment to numeric values for chart
const sentimentMap = { "Positive": 1, "Neutral": 0, "Negative": -1 };

new Chart(ctx, {
    type: 'line',
    data: {
        labels: chartData.map(item => item.date),
        datasets: [{
            label: 'Sentiment Trend',
            data: chartData.map(item => sentimentMap[item.sentiment] ?? 0),
            borderColor: 'navy',
            backgroundColor: 'rgba(0,0,128,0.1)',
            fill: true,
            tension: 0.3,
            pointRadius: 5
        }]
    },
    options: {
        scales: {
            y: {
                ticks: {
                    callback: function(value) {
                        if (value === 1) return 'Positive';
                        if (value === 0) return 'Neutral';
                        if (value === -1) return 'Negative';
                        return value;
                    }
                },
                min: -1,
                max: 1,
                stepSize: 1
            }
        }
    }
});
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
