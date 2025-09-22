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

// Fetch student progress data
$stmt = $conn->prepare("SELECT students.name AS student_name, progress.progress_details, progress.updated_at 
                         FROM progress 
                         JOIN students ON progress.student_id = students.student_id 
                         WHERE students.mentor_id = ?
                         ORDER BY progress.updated_at DESC");
$stmt->bind_param('i', $mentor_id);
$stmt->execute();
$progress_data = $stmt->get_result();

// Fetch data for the chart (count by progress category)
$chart_data = [];
$chart_stmt = $conn->prepare("SELECT progress_details, COUNT(*) AS count FROM progress WHERE mentor_id = ? GROUP BY progress_details");
$chart_stmt->bind_param('i', $mentor_id);
$chart_stmt->execute();
$chart_result = $chart_stmt->get_result();
while ($row = $chart_result->fetch_assoc()) {
    $chart_data[] = $row;
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
                <a class="nav-link" href="student_progress.php"><i class="fas fa-chart-pie"></i> Progress</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="mb-4">Student Progress</h3>

    <!-- Progress Table -->
    <div class="card mb-4">
        <div class="card-header">Progress Details</div>
        <div class="card-body">
            <table class="table table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Student Name</th>
                        <th>Progress Details</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($progress = $progress_data->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($progress['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($progress['progress_details']); ?></td>
                            <td><?php echo date('d M Y, h:i A', strtotime($progress['updated_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Progress Chart -->
    <div class="card">
        <div class="card-header">Progress Overview</div>
        <div class="card-body">
            <canvas id="progressChart"></canvas>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('progressChart').getContext('2d');
    const progressChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_column($chart_data, 'progress_details')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($chart_data, 'count')); ?>,
                backgroundColor: [
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 99, 132, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(153, 102, 255, 0.6)'
                ],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
