<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once 'db_connection.php';

// Check admin session
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// flash message
if (!empty($_SESSION['flash_message'])) {
    $flash = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
} else {
    $flash = "";
}

// Fetch mentors
$mentors_list = $conn->query("SELECT mentor_id, name FROM mentors");

// Fetch students
$students = $conn->query("
    SELECT students.student_id, students.name, students.course, users.username, students.mentor_id, mentors.name AS mentor_name
    FROM students
    JOIN users ON students.user_id = users.user_id
    LEFT JOIN mentors ON students.mentor_id = mentors.mentor_id
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ---------- ADD STUDENT ----------
    if (isset($_POST['add_student'])) {
        $name = trim($_POST['name']);
        $username = trim($_POST['username']);
        $password = trim($_POST['password']); // Use password_hash() in production
        $course = trim($_POST['course']);
        $mentor_id = (int)$_POST['mentor_id'];

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'student')");
            $stmt->bind_param('ss', $username, $password);
            $stmt->execute();
            $user_id = $conn->insert_id;
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO students (user_id, name, course, mentor_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('issi', $user_id, $name, $course, $mentor_id);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            $_SESSION['flash_message'] = "✅ Student added successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['flash_message'] = "❌ Error: " . $e->getMessage();
        }

        header("Location: manage_students.php");
        exit();
    }

    // ---------- UPDATE STUDENT ----------
    if (isset($_POST['update_student'])) {
        $student_id = (int)$_POST['student_id'];
        $name = trim($_POST['name']);
        $username = trim($_POST['username']);
        $course = trim($_POST['course']);
        $mentor_id = (int)$_POST['mentor_id'];

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("UPDATE students SET name = ?, course = ?, mentor_id = ? WHERE student_id = ?");
            $stmt->bind_param('ssii', $name, $course, $mentor_id, $student_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("SELECT user_id FROM students WHERE student_id = ?");
            $stmt->bind_param('i', $student_id);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            $stmt->close();

            if ($row) {
                $user_id = (int)$row['user_id'];
                $stmt = $conn->prepare("UPDATE users SET username = ? WHERE user_id = ?");
                $stmt->bind_param('si', $username, $user_id);
                $stmt->execute();
                $stmt->close();
            }

            $conn->commit();
            $_SESSION['flash_message'] = "✅ Student updated successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['flash_message'] = "❌ Error: " . $e->getMessage();
        }

        header("Location: manage_students.php");
        exit();
    }

    // ---------- DELETE STUDENT ----------
    if (isset($_POST['delete_student'])) {
        $student_id = (int)$_POST['student_id'];

        $conn->begin_transaction();
        try {
            // Get user_id
            $stmt = $conn->prepare("SELECT user_id FROM students WHERE student_id = ?");
            $stmt->bind_param('i', $student_id);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            $stmt->close();

            if (!$row) {
                throw new Exception("Student not found.");
            }

            $user_id = (int)$row['user_id'];

            // Delete student (ON DELETE CASCADE removes feedback, meetings, weekly_questions, attendance, grades)
            $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
            $stmt->bind_param('i', $student_id);
            $stmt->execute();
            $stmt->close();

            // Delete user (ON DELETE CASCADE removes notifications)
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            $_SESSION['flash_message'] = "✅ Student and related records deleted successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['flash_message'] = "❌ Error: " . $e->getMessage();
        }

        header("Location: manage_students.php");
        exit();
    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background-color: #f4f4f9; }
        .navbar { background-color: navy; }
        .navbar-brand, .nav-link { color: white !important; }
        .card { box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .btn-primary { background-color: navy; border-color: navy; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <a class="navbar-brand" href="#">Admin Dashboard</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a class="nav-link" href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="manage_mentors.php"><i class="fas fa-chalkboard-teacher"></i> Manage Mentors</a></li>
            <li class="nav-item"><a class="nav-link" href="manage_students.php"><i class="fas fa-user-graduate"></i> Manage Students</a></li>
            <li class="nav-item"><a class="nav-link" href="view_feedback.php"><i class="fas fa-comments"></i> View Feedback</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="mb-4">Manage Students</h3>

    <?php if (!empty($flash)): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($flash); ?></div>
    <?php endif; ?>

    <!-- Add Student Form -->
    <div class="card mb-4">
        <div class="card-header">Add New Student</div>
        <div class="card-body">
            <form method="POST">
                <input type="text" name="name" class="form-control mb-2" placeholder="Name" required>
                <input type="text" name="username" class="form-control mb-2" placeholder="Username" required>
                <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
                <input type="text" name="course" class="form-control mb-2" placeholder="Course" required>
                <select name="mentor_id" class="form-control mb-2" required>
                    <option value="">Select Mentor</option>
                    <?php while ($mentor = $mentors_list->fetch_assoc()): ?>
                        <option value="<?php echo $mentor['mentor_id']; ?>"><?php echo htmlspecialchars($mentor['name']); ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" name="add_student" class="btn btn-primary">Add Student</button>
            </form>
        </div>
    </div>

    <!-- Students List -->
    <table class="table table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Name</th>
                <th>Username</th>
                <th>Course</th>
                <th>Mentor</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($student = $students->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($student['name']); ?></td>
                <td><?php echo htmlspecialchars($student['username']); ?></td>
                <td><?php echo htmlspecialchars($student['course']); ?></td>
                <td><?php echo htmlspecialchars($student['mentor_name'] ?? 'Not Assigned'); ?></td>
                <td>
                    <button class="btn btn-warning btn-sm edit-btn"
                        data-id="<?php echo $student['student_id']; ?>"
                        data-name="<?php echo htmlspecialchars($student['name']); ?>"
                        data-username="<?php echo htmlspecialchars($student['username']); ?>"
                        data-course="<?php echo htmlspecialchars($student['course']); ?>"
                        data-mentor-id="<?php echo $student['mentor_id']; ?>">
                        Edit
                    </button>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this student? All related records will be removed.');">
                        <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                        <button type="submit" name="delete_student" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Student</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="student_id" id="edit_student_id">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" id="edit_username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Course</label>
                    <input type="text" name="course" id="edit_course" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Mentor</label>
                    <select name="mentor_id" id="edit_mentor" class="form-control" required>
                        <option value="">Select Mentor</option>
                        <?php
                        $mentors_dropdown = $conn->query("SELECT mentor_id, name FROM mentors");
                        while ($mentor = $mentors_dropdown->fetch_assoc()):
                        ?>
                            <option value="<?php echo $mentor['mentor_id']; ?>"><?php echo htmlspecialchars($mentor['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="update_student" class="btn btn-primary">Update Student</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
$('.edit-btn').click(function() {
    $('#edit_student_id').val($(this).data('id'));
    $('#edit_name').val($(this).data('name'));
    $('#edit_username').val($(this).data('username'));
    $('#edit_course').val($(this).data('course'));
    $('#edit_mentor').val($(this).data('mentor-id'));
    $('#editStudentModal').modal('show');
});
</script>

</body>
</html>
