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

// Fetch progress data for the student
$stmt = $conn->prepare("SELECT progress_details, updated_at FROM progress WHERE student_id = ? ORDER BY updated_at DESC");
$stmt->bind_param('i', $student_id);
$stmt->execute();
$progress_data = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Reports</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
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
                <a class="nav-link" href="progress_reports.php"><i class="fas fa-file-alt"></i> Progress Reports</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="mb-4">Progress Reports</h3>

    <!-- Progress Table -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Progress Details</span>
            <button class="btn btn-primary btn-sm" onclick="exportToExcel()">Export to Excel</button>
        </div>
        <div class="card-body">
            <table id="progressTable" class="table table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Progress Details</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($progress = $progress_data->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($progress['progress_details']); ?></td>
                            <td><?php echo date('d M Y, h:i A', strtotime($progress['updated_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($progress_data->num_rows === 0): ?>
                        <tr>
                            <td colspan="2">No progress data available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function exportToExcel() {
    var table = document.getElementById('progressTable');
    var workbook = XLSX.utils.table_to_book(table, { sheet: 'Progress Reports' });
    XLSX.writeFile(workbook, 'ProgressReports.xlsx');
}
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
