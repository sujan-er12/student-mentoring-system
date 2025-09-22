<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Page</title>
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
        .hero-section {
            background-color: #0c4b85;
            color: white;
            padding: 50px 0;
            text-align: center;
            animation: fadeIn 2s;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        .login-cards {
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .card {
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: scale(1.05);
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
    <a class="navbar-brand" href="#">Welcome</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" 
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="admin_login.php"><i class="fas fa-user-shield"></i> Admin Login</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="mentor_login.php"><i class="fas fa-chalkboard-teacher"></i> Mentor Login</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="student_login.php"><i class="fas fa-user-graduate"></i> Student Login</a>
            </li>
        </ul>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-section">
    <h1>Welcome to Our Platform</h1>
    <p>Manage, Monitor, and Collaborate Effectively</p>
</section>

<!-- Login Cards -->
<div class="container login-cards">
    <div class="row justify-content-center text-center">
        <div class="col-lg-4 col-md-6 mb-4 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-body">
                    <i class="fas fa-user-shield fa-3x mb-3"></i>
                    <h5 class="card-title">Admin</h5>
                    <p class="card-text">Manage system-wide configurations and users.</p>
                    <a href="admin_login.php" class="btn btn-primary">Login as Admin</a>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-body">
                    <i class="fas fa-chalkboard-teacher fa-3x mb-3"></i>
                    <h5 class="card-title">Mentor</h5>
                    <p class="card-text">Track student progress and provide guidance.</p>
                    <a href="mentor_login.php" class="btn btn-primary">Login as Mentor</a>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-body">
                    <i class="fas fa-user-graduate fa-3x mb-3"></i>
                    <h5 class="card-title">Student</h5>
                    <p class="card-text">Access your learning progress and updates.</p>
                    <a href="student_login.php" class="btn btn-primary">Login as Student</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
