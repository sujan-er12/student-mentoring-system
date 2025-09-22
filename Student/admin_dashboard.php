<?php
// Start the session
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Include database connection
require_once 'db_connection.php';

// Fetch dashboard data
$mentors_count = $conn->query("SELECT COUNT(*) AS total FROM mentors")->fetch_assoc()['total'];
$students_count = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'];
$feedback_count = $conn->query("SELECT COUNT(*) AS total FROM feedback")->fetch_assoc()['total'];
$meetings_count = $conn->query("SELECT COUNT(*) AS total FROM meetings")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        .card {
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
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
    <a class="navbar-brand" href="#">Admin Dashboard</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_mentors.php"><i class="fas fa-chalkboard-teacher"></i> Manage Mentors</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_students.php"><i class="fas fa-user-graduate"></i> Manage Students</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_feedback.php"><i class="fas fa-comments"></i> View Feedback</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="mb-4">Welcome to the Admin Dashboard</h3>

    <div class="row">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Mentors</h5>
                    <p class="card-text"><i class="fas fa-chalkboard-teacher fa-3x"></i></p>
                    <h2><?php echo $mentors_count; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Students</h5>
                    <p class="card-text"><i class="fas fa-user-graduate fa-3x"></i></p>
                    <h2><?php echo $students_count; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Feedback</h5>
                    <p class="card-text"><i class="fas fa-comments fa-3x"></i></p>
                    <h2><?php echo $feedback_count; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Meetings</h5>
                    <p class="card-text"><i class="fas fa-calendar-alt fa-3x"></i></p>
                    <h2><?php echo $meetings_count; ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
