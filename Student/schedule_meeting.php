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

// Fetch the mentor's mentor_id from the mentors table using the logged-in user's id
$mentor_stmt = $conn->prepare("SELECT mentor_id FROM mentors WHERE user_id = ?");
$mentor_stmt->bind_param("i", $_SESSION['user_id']);
$mentor_stmt->execute();
$mentor_data = $mentor_stmt->get_result()->fetch_assoc();
$mentor_id = $mentor_data['mentor_id'];

// Fetch students assigned to the mentor using the correct mentor_id
$stmt = $conn->prepare("SELECT student_id, user_id, name FROM students WHERE mentor_id = ?");
$stmt->bind_param('i', $mentor_id);
$stmt->execute();
$students = $stmt->get_result();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $meeting_date = $_POST['meeting_date'];
    $meeting_purpose = $_POST['meeting_purpose'];

    if (!empty($student_id) && !empty($meeting_date) && !empty($meeting_purpose)) {
        
        // Get the student’s `user_id` from `students` table
        $stmt = $conn->prepare("SELECT user_id FROM students WHERE student_id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student_data = $result->fetch_assoc();
        
        if (!$student_data) {
            echo "<script>alert('Invalid student selected. Please try again.');</script>";
        } else {
            $student_user_id = $student_data['user_id'];

            // Insert meeting into `meetings` table
            $stmt = $conn->prepare("INSERT INTO meetings (mentor_id, student_id, meeting_date, meeting_purpose, notification_status) VALUES (?, ?, ?, ?, 0)");
            $stmt->bind_param("iiss", $mentor_id, $student_id, $meeting_date, $meeting_purpose);
            
            if ($stmt->execute()) {
                // Insert a notification for the student using `user_id`
                $notification_msg = "Your mentor has scheduled a meeting on " . date('d M Y, h:i A', strtotime($meeting_date)) . ". Purpose: " . $meeting_purpose;
                $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, notification_type) VALUES (?, ?, 'meeting')");
                $stmt->bind_param("is", $student_user_id, $notification_msg);
                $stmt->execute();

                echo "<script>alert('Meeting scheduled successfully!'); window.location.href='mentor_dashboard.php';</script>";
            } else {
                echo "<script>alert('Failed to schedule meeting. Please try again.');</script>";
            }
        }
    } else {
        echo "<script>alert('All fields are required!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Meeting</title>
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
        .container {
            margin-top: 50px;
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
            
            <li class="nav-item active">
                <a class="nav-link" href="schedule_meeting.php"><i class="fas fa-calendar-alt"></i> Schedule Meeting</a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="view_meetings.php"><i class="fas fa-calendar-alt"></i> view all Meeting</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_notifications.php"><i class="fas fa-bell"></i> Notifications</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_feedbacks.php"><i class="fas fa-comments"></i> View Feedback</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>
</nav>

<!-- Meeting Scheduling Form -->
<div class="container">
    <div class="card">
        <div class="card-header">
            <h4>Schedule a Meeting</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="student_id">Select Student:</label>
                    <select name="student_id" id="student_id" class="form-control" required>
                        <option value="">-- Select Student --</option>
                        <?php while ($student = $students->fetch_assoc()): ?>
                            <option value="<?php echo $student['student_id']; ?>">
                                <?php echo htmlspecialchars($student['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="meeting_date">Meeting Date & Time:</label>
                    <input type="datetime-local" name="meeting_date" id="meeting_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="meeting_purpose">Meeting Purpose:</label>
                    <textarea name="meeting_purpose" id="meeting_purpose" class="form-control" rows="3" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-calendar-check"></i> Schedule Meeting</button>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
