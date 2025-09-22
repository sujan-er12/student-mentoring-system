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

$mentor_user_id = $_SESSION['user_id'];
$message = "";

// Fetch the actual mentor_id from the mentors table
$mentor_stmt = $conn->prepare("SELECT mentor_id FROM mentors WHERE user_id = ?");
$mentor_stmt->bind_param("i", $mentor_user_id);
$mentor_stmt->execute();
$mentor_data = $mentor_stmt->get_result()->fetch_assoc();
$mentor_id = $mentor_data['mentor_id']; // Ensure correct mentor_id is used

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_meeting'])) {
        // Handle meeting edit
        $meeting_id = $_POST['meeting_id'];
        $meeting_date = $_POST['meeting_date'];
        $meeting_purpose = $_POST['meeting_purpose'];

        $stmt = $conn->prepare("UPDATE meetings SET meeting_date = ?, meeting_purpose = ? WHERE meeting_id = ? AND mentor_id = ?");
        $stmt->bind_param("ssii", $meeting_date, $meeting_purpose, $meeting_id, $mentor_id);
        if ($stmt->execute()) {
            echo "<script>alert('Meeting updated successfully!'); window.location.href='view_meetings.php';</script>";
        } else {
            echo "<script>alert('Error updating meeting. Please try again.');</script>";
        }
    } elseif (isset($_POST['delete_meeting'])) {
        // Handle meeting deletion
        $meeting_id = $_POST['meeting_id'];

        $stmt = $conn->prepare("DELETE FROM meetings WHERE meeting_id = ? AND mentor_id = ?");
        $stmt->bind_param("ii", $meeting_id, $mentor_id);
        if ($stmt->execute()) {
            echo "<script>alert('Meeting deleted successfully!'); window.location.href='view_meetings.php';</script>";
        } else {
            echo "<script>alert('Error deleting meeting. Please try again.');</script>";
        }
    }
}

// Fetch scheduled meetings for the mentor using correct mentor_id
$stmt = $conn->prepare("
    SELECT m.meeting_id, m.meeting_date, m.meeting_purpose, s.name AS student_name 
    FROM meetings m
    JOIN students s ON m.student_id = s.student_id
    WHERE m.mentor_id = ?
    ORDER BY m.meeting_date ASC
");
$stmt->bind_param("i", $mentor_id);
$stmt->execute();
$meetings = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Meetings</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
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

<div class="container mt-4">
    <h3>Manage Scheduled Meetings</h3>

    <table class="table table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Student</th>
                <th>Meeting Date</th>
                <th>Purpose</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($meeting = $meetings->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($meeting['student_name']); ?></td>
                    <td><?php echo date('d M Y, h:i A', strtotime($meeting['meeting_date'])); ?></td>
                    <td><?php echo htmlspecialchars($meeting['meeting_purpose']); ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="editMeeting(
                            <?php echo $meeting['meeting_id']; ?>,
                            '<?php echo $meeting['meeting_date']; ?>',
                            '<?php echo addslashes($meeting['meeting_purpose']); ?>'
                        )">Edit</button>

                        <form method="POST" action="" class="d-inline">
                            <input type="hidden" name="meeting_id" value="<?php echo $meeting['meeting_id']; ?>">
                            <button type="submit" name="delete_meeting" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Edit Meeting Modal -->
<div class="modal fade" id="editMeetingModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <form id="editMeetingForm" method="POST">
                    <input type="hidden" name="meeting_id" id="edit_meeting_id">
                    <label>Meeting Date & Time:</label>
                    <input type="datetime-local" name="meeting_date" id="edit_meeting_date" class="form-control">
                    <label>Meeting Purpose:</label>
                    <textarea name="meeting_purpose" id="edit_meeting_purpose" class="form-control"></textarea>
                    <button type="submit" name="edit_meeting" class="btn btn-primary mt-2">Update Meeting</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editMeeting(id, date, purpose) {
    document.getElementById('edit_meeting_id').value = id;
    document.getElementById('edit_meeting_date').value = date;
    document.getElementById('edit_meeting_purpose').value = purpose;
    $('#editMeetingModal').modal('show');
}
</script>

</body>
</html>
