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

// Fetch feedback assigned to the mentor
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT feedback_id, feedback_text, sentiment FROM feedback WHERE mentor_id = ? ORDER BY submitted_at DESC");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$feedbacks = $stmt->get_result();

// Mock sentiment analysis function (Replace with NLP library or API integration)
function analyze_sentiment($text) {
    // Simple sentiment analysis logic for demo purposes
    $positive_words = ['good', 'happy', 'excellent', 'great', 'positive'];
    $negative_words = ['bad', 'sad', 'poor', 'negative', 'stress'];
    $score = 0;

    foreach ($positive_words as $word) {
        if (stripos($text, $word) !== false) {
            $score++;
        }
    }

    foreach ($negative_words as $word) {
        if (stripos($text, $word) !== false) {
            $score--;
        }
    }

    if ($score > 0) {
        return 'Positive';
    } elseif ($score < 0) {
        return 'Negative';
    } else {
        return 'Neutral';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyze Feedback</title>
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
                <a class="nav-link" href="manage_notifications.php"><i class="fas fa-bell"></i> Notifications</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="mb-4">Analyze Feedback</h3>
    <div class="card">
        <div class="card-body">
            <table class="table table-striped table-responsive">
                <thead class="thead-dark">
                    <tr>
                        <th>Feedback ID</th>
                        <th>Feedback Text</th>
                        <th>Sentiment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($feedback = $feedbacks->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $feedback['feedback_id']; ?></td>
                            <td><?php echo htmlspecialchars($feedback['feedback_text']); ?></td>
                            <td>
                                <?php
                                $sentiment = $feedback['sentiment'] ?: analyze_sentiment($feedback['feedback_text']);
                                echo $sentiment;
                                ?>
                            </td>
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
