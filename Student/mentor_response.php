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
$message = "";

// Handle form submissions for Create, Update, and Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        // Create a new response
        $student_id = $_POST['student_id'];
        $response_content = $_POST['response_content'];

        $stmt = $conn->prepare("INSERT INTO responses (mentor_id, student_id, response_content, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param('iis', $mentor_id, $student_id, $response_content);
        if ($stmt->execute()) {
            $message = "Response created successfully!";
        } else {
            $message = "Error creating response: " . $stmt->error;
        }
    } elseif (isset($_POST['update'])) {
        // Update an existing response
        $response_id = $_POST['response_id'];
        $response_content = $_POST['response_content'];

        $stmt = $conn->prepare("UPDATE responses SET response_content = ? WHERE response_id = ? AND mentor_id = ?");
        $stmt->bind_param('sii', $response_content, $response_id, $mentor_id);
        if ($stmt->execute()) {
            $message = "Response updated successfully!";
        } else {
            $message = "Error updating response: " . $stmt->error;
        }
    } elseif (isset($_POST['delete'])) {
        // Delete a response
        $response_id = $_POST['response_id'];

        $stmt = $conn->prepare("DELETE FROM responses WHERE response_id = ? AND mentor_id = ?");
        $stmt->bind_param('ii', $response_id, $mentor_id);
        if ($stmt->execute()) {
            $message = "Response deleted successfully!";
        } else {
            $message = "Error deleting response: " . $stmt->error;
        }
    }
}

// Fetch previous responses
$stmt = $conn->prepare("SELECT responses.response_id, students.name AS student_name, responses.response_content, responses.created_at 
                         FROM responses 
                         JOIN students ON responses.student_id = students.student_id 
                         WHERE responses.mentor_id = ? 
                         ORDER BY responses.created_at DESC");
$stmt->bind_param('i', $mentor_id);
$stmt->execute();
$responses = $stmt->get_result();

// Fetch students assigned to the mentor
$stmt = $conn->prepare("SELECT student_id, name FROM students WHERE mentor_id = ?");
$stmt->bind_param('i', $mentor_id);
$stmt->execute();
$students = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentor Responses</title>
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
        .btn-primary {
            background-color: navy;
            border-color: navy;
        }
        .form-control, .btn {
            margin-bottom: 10px;
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
                <a class="nav-link" href="mentor_response.php"><i class="fas fa-comment"></i> Responses</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="mb-4">Manage Mentor Responses</h3>
    <?php if (!empty($message)): ?>
        <div class="alert alert-info" role="alert">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- Create Response Form -->
    <div class="card mb-4">
        <div class="card-header">Create a New Response</div>
        <div class="card-body">
            <form method="POST" action="">
                <select name="student_id" class="form-control" required>
                    <option value="" disabled selected>Select Student</option>
                    <?php while ($student = $students->fetch_assoc()): ?>
                        <option value="<?php echo $student['student_id']; ?>">
                            <?php echo $student['name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <textarea name="response_content" class="form-control" placeholder="Write your response here..." required></textarea>
                <button type="submit" name="create" class="btn btn-primary">Create Response</button>
            </form>
        </div>
    </div>

    <!-- Previous Responses -->
    <div class="card">
        <div class="card-header">Previous Responses</div>
        <div class="card-body">
            <table class="table table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Student Name</th>
                        <th>Response Content</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($response = $responses->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($response['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($response['response_content']); ?></td>
                            <td><?php echo date('d M Y, h:i A', strtotime($response['created_at'])); ?></td>
                            <td>
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="response_id" value="<?php echo $response['response_id']; ?>">
                                    <button type="submit" name="delete" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                                <button class="btn btn-warning btn-sm" onclick="editResponse(<?php echo $response['response_id']; ?>, '<?php echo addslashes($response['response_content']); ?>')">Edit</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function editResponse(responseId, content) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '';

    form.innerHTML = `
        <input type="hidden" name="response_id" value="${responseId}">
        <textarea name="response_content" class="form-control" required>${content}</textarea>
        <button type="submit" name="update" class="btn btn-primary">Update Response</button>
    `;

    document.body.appendChild(form);
    form.submit();
}
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
