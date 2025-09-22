<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}
require_once 'db_connection.php';

$message = "";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_feedback'])) {
        $student_id = $_POST['student_id'];
        $mentor_id = $_POST['mentor_id'];
        $comments = $_POST['comments'];
        $stmt = $conn->prepare("INSERT INTO feedback (student_id, mentor_id, comments, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param('iis', $student_id, $mentor_id, $comments);
        $message = $stmt->execute() ? "✅ Feedback added successfully!" : "❌ Error: " . $stmt->error;
    } elseif (isset($_POST['update_feedback'])) {
        $feedback_id = $_POST['feedback_id'];
        $comments = $_POST['comments'];
        $stmt = $conn->prepare("UPDATE feedback SET comments = ? WHERE feedback_id = ?");
        $stmt->bind_param('si', $comments, $feedback_id);
        $message = $stmt->execute() ? "✅ Feedback updated successfully!" : "❌ Error: " . $stmt->error;
    } elseif (isset($_POST['delete_feedback'])) {
        $feedback_id = $_POST['feedback_id'];
        $stmt = $conn->prepare("DELETE FROM feedback WHERE feedback_id = ?");
        $stmt->bind_param('i', $feedback_id);
        $message = $stmt->execute() ? "✅ Feedback deleted successfully!" : "❌ Error: " . $stmt->error;
    }
}

function analyzeSentiment($text) {
    $positive = ['good', 'great', 'excellent', 'happy', 'love', 'awesome', 'nice', 'helpful'];
    $negative = ['bad', 'poor', 'sad', 'terrible', 'hate', 'worst', 'issue', 'problem'];
    $text = strtolower($text);
    $pos = $neg = 0;
    foreach ($positive as $word) $pos += substr_count($text, $word);
    foreach ($negative as $word) $neg += substr_count($text, $word);
    return $pos > $neg ? "Positive" : ($neg > $pos ? "Negative" : "Neutral");
}

$feedbacks = $conn->query("
    SELECT f.feedback_id, f.comments, f.created_at, 
           s.name AS student_name, m.name AS mentor_name
    FROM feedback f
    JOIN students s ON f.student_id = s.student_id
    JOIN mentors m ON f.mentor_id = m.mentor_id
    ORDER BY f.created_at DESC
");

$sentimentCounts = ['Positive' => 0, 'Negative' => 0, 'Neutral' => 0];
$feedback_data = [];
while ($row = $feedbacks->fetch_assoc()) {
    $sentiment = analyzeSentiment($row['comments']);
    $sentimentCounts[$sentiment]++;
    $row['sentiment'] = $sentiment;
    $feedback_data[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Feedback</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f4f4f9; }
        .navbar { background-color: navy; }
        .navbar-brand, .nav-link { color: white !important; }
        .btn-primary { background-color: navy; border-color: navy; }
        canvas { display: block; margin: 0 auto; }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <a class="navbar-brand" href="#">Admin Dashboard</a>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="manage_mentors.php">Manage Mentors</a></li>
            <li class="nav-item"><a class="nav-link" href="manage_students.php">Manage Students</a></li>
            <li class="nav-item"><a class="nav-link" href="view_feedback.php">View Feedback</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="mb-4">View Feedback with Sentiment Analysis</h3>
    <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>

    <!-- Feedback Table -->
    <div class="card mb-4">
        <div class="card-header">Feedback List</div>
        <div class="card-body">
            <table class="table table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Student Name</th>
                        <th>Mentor Name</th>
                        <th>Feedback</th>
                        <th>Sentiment</th>
                        <th>Submitted At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feedback_data as $f): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($f['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($f['mentor_name']); ?></td>
                            <td><?php echo htmlspecialchars($f['comments']); ?></td>
                            <td><strong><?php echo $f['sentiment']; ?></strong></td>
                            <td><?php echo date('d M Y, h:i A', strtotime($f['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (count($feedback_data) === 0): ?>
                        <tr><td colspan="5">No feedback available.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sentiment Pie Chart (Compact View) -->
    <div class="card mt-4" style="max-width: 400px; margin: 0 auto;">
        <div class="card-header text-center">Sentiment Distribution</div>
        <div class="card-body text-center">
            <canvas id="sentimentChart" width="300" height="300"></canvas>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
    const ctx = document.getElementById('sentimentChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Positive', 'Negative', 'Neutral'],
            datasets: [{
                label: 'Sentiment',
                data: [
                    <?php echo $sentimentCounts['Positive']; ?>,
                    <?php echo $sentimentCounts['Negative']; ?>,
                    <?php echo $sentimentCounts['Neutral']; ?>
                ],
                backgroundColor: ['#28a745', '#dc3545', '#ffc107']
            }]
        },
        options: {
            responsive: false,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            const total = <?php echo array_sum($sentimentCounts); ?>;
                            const percent = ((value / total) * 100).toFixed(1);
                            return `${context.label}: ${value} (${percent}%)`;
                        }
                    }
                }
            }
        }
    });
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
