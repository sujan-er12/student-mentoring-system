<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}
require_once 'db_connection.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_mentor'])) {
        $name = $_POST['name'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $department = $_POST['department'];
        $contact_number = $_POST['contact_number'];
        $specialization = $_POST['specialization'];

        $stmt = $conn->prepare("INSERT INTO users (username, password, role, email) VALUES (?, ?, 'mentor', ?)");
        $stmt->bind_param('sss', $username, $password, $email);
        if ($stmt->execute()) {
            $user_id = $conn->insert_id;

            $stmt = $conn->prepare("INSERT INTO mentors (user_id, name, department, contact_number, email, specialization) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('isssss', $user_id, $name, $department, $contact_number, $email, $specialization);
            $message = $stmt->execute() ? "✅ Mentor added successfully!" : "❌ Error adding mentor: " . $stmt->error;
        } else {
            $message = "❌ Error adding user: " . $stmt->error;
        }

    } elseif (isset($_POST['update_mentor'])) {
        $mentor_id = $_POST['mentor_id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $department = $_POST['department'];
        $contact_number = $_POST['contact_number'];
        $specialization = $_POST['specialization'];

        $stmt = $conn->prepare("UPDATE mentors SET name=?, email=?, department=?, contact_number=?, specialization=? WHERE mentor_id=?");
        $stmt->bind_param('sssssi', $name, $email, $department, $contact_number, $specialization, $mentor_id);
        $message = $stmt->execute() ? "✅ Mentor updated successfully!" : "❌ Error updating mentor: " . $stmt->error;
    }

    elseif (isset($_POST['delete_mentor'])) {
        $mentor_id = $_POST['mentor_id'];

        $stmt = $conn->prepare("SELECT user_id FROM mentors WHERE mentor_id = ?");
        $stmt->bind_param('i', $mentor_id);
        $stmt->execute();
        $stmt->bind_result($user_id);
        $stmt->fetch();
        $stmt->close();

        if ($user_id) {
            $stmt = $conn->prepare("DELETE FROM mentors WHERE mentor_id = ?");
            $stmt->bind_param('i', $mentor_id);
            if ($stmt->execute()) {
                $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
                $stmt->bind_param('i', $user_id);
                $stmt->execute();
                $message = "✅ Mentor deleted successfully!";
            } else {
                $message = "❌ Error deleting mentor: " . $stmt->error;
            }
        }
    }
}

$mentors = $conn->query("SELECT * FROM mentors");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Mentors</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { background-color: #f4f4f9; }
        .navbar { background-color: navy; }
        .navbar-brand, .nav-link { color: white !important; }
        .card { box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        .btn-primary { background-color: navy; border-color: navy; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <a class="navbar-brand" href="#">Admin Dashboard</a>
    <div class="collapse navbar-collapse">
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
    <h3>Manage Mentors</h3>

    <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>

    <!-- Add Mentor Form -->
    <div class="card mb-4">
        <div class="card-header">Add New Mentor</div>
        <div class="card-body">
            <form method="POST" action="">
                <input type="text" name="name" class="form-control" placeholder="Name" required>
                <input type="text" name="username" class="form-control" placeholder="Username" required>
                <input type="email" name="email" class="form-control" placeholder="Email" required>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
                <input type="text" name="department" class="form-control" placeholder="Department" required>
                <input type="text" name="contact_number" class="form-control" placeholder="Contact Number" required>
                <input type="text" name="specialization" class="form-control" placeholder="Specialization" required>
                <button type="submit" name="add_mentor" class="btn btn-primary mt-2">Add Mentor</button>
            </form>
        </div>
    </div>

    <!-- Mentors List -->
    <div class="card">
        <div class="card-header">Mentors List</div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Specialization</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($mentor = $mentors->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($mentor['name']) ?></td>
                            <td><?= htmlspecialchars($mentor['department']) ?></td>
                            <td><?= htmlspecialchars($mentor['contact_number']) ?></td>
                            <td><?= htmlspecialchars($mentor['email']) ?></td>
                            <td><?= htmlspecialchars($mentor['specialization']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editMentor(
                                    <?= $mentor['mentor_id'] ?>,
                                    '<?= addslashes($mentor['name']) ?>',
                                    '<?= addslashes($mentor['email']) ?>',
                                    '<?= addslashes($mentor['department']) ?>',
                                    '<?= addslashes($mentor['contact_number']) ?>',
                                    '<?= addslashes($mentor['specialization']) ?>'
                                )">Edit</button>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="mentor_id" value="<?= $mentor['mentor_id'] ?>">
                                    <button type="submit" name="delete_mentor" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($mentors->num_rows === 0): ?>
                        <tr><td colspan="6" class="text-center">No mentors found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editMentorModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Mentor</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="mentor_id" id="edit_mentor_id">
        <input type="text" name="name" id="edit_name" class="form-control mb-2" placeholder="Name" required>
        <input type="email" name="email" id="edit_email" class="form-control mb-2" placeholder="Email" required>
        <input type="text" name="department" id="edit_department" class="form-control mb-2" placeholder="Department" required>
        <input type="text" name="contact_number" id="edit_contact" class="form-control mb-2" placeholder="Contact Number" required>
        <input type="text" name="specialization" id="edit_specialization" class="form-control mb-2" placeholder="Specialization" required>
      </div>
      <div class="modal-footer">
        <button type="submit" name="update_mentor" class="btn btn-primary">Update Mentor</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function editMentor(id, name, email, dept, contact, specialization) {
    $('#edit_mentor_id').val(id);
    $('#edit_name').val(name);
    $('#edit_email').val(email);
    $('#edit_department').val(dept);
    $('#edit_contact').val(contact);
    $('#edit_specialization').val(specialization);
    $('#editMentorModal').modal('show');
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
