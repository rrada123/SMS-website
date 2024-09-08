<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Database connection
$servername = "localhost"; // Update with your database server
$username = "root";        // Update with your database username
$password = "";            // Update with your database password
$dbname = "school_db";     // Updated database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the number of students
$students_sql = "SELECT COUNT(id) as student_count FROM students";
$students_result = $conn->query($students_sql);
$students_count = $students_result->fetch_assoc()['student_count'];

// Get the number of unique teachers
$teachers_sql = "SELECT COUNT(DISTINCT teacher) as teacher_count FROM subjects";
$teachers_result = $conn->query($teachers_sql);
$teachers_count = $teachers_result->fetch_assoc()['teacher_count'];

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Login Portal - Admin Dashboard</title>
    <link rel="stylesheet" href="../css/sidebar.css"> <!-- Link to sidebar CSS -->
    <link rel="stylesheet" href="../css/admin.css"> <!-- Link to dashboard CSS -->
</head>
<body>
    <div class="sidebar">
        <div class="admin-section">
            <img src="../images/SCHOOL_LOGO.jpg" alt="Admin">
            <h2>Admin</h2>
        </div>
        <ul>
            <li><a href="admin.php">Dashboard</a></li>
            <li><a href="students.php">Student Section</a></li> 
            <li><a href="subject_section.php">Subject</a></li>
			<li><a href="Grades.php">Grades</a></li>			
            <li><a href="payment.php">Payment and Invoice</a></li>
			<li><a href="Tuition.php">Tuition</a></li>
            <li><a href="add_student.php">Add Student</a></li>
			
        </ul>
    </div>
    <div class="content">
        <div class="header">
            <h3>School Login Portal</h3>
            <a href="../logout.php" class="logout">Logout</a>
        </div>
        <h1>Admin Dashboard</h1>
        <div class="dashboard">
            <a href="students.php" class="card card-2">
                <?php echo $students_count; ?><br>Students
            </a>
            <a href="subject_section.php" class="card card-3">
                <?php echo $teachers_count; ?><br>Teachers
            </a>
        </div>
        <h1>Events</h1>
        <div class="dashboard">
            <div class="card card-2">14<br>No Events today</div>
        </div>
    </div>
</body>
</html>
