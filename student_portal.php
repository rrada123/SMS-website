<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'student') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Login Portal - Student Dashboard</title>
    <link rel="stylesheet" href="css/sidebar.css"> <!-- Link to sidebar CSS -->
    <link rel="stylesheet" href="css/student_portal.css"> <!-- Link to student dashboard CSS -->
</head>
<body>
    <div class="sidebar">
        <div class="admin-section">
            <img src="images/SCHOOL_LOGO.jpg" alt="Student">
            <h2><?php echo $_SESSION['student_name']; ?></h2>
        </div>
        <ul>
            <li><a href="student_portal.php">Dashboard</a></li>
            <li><a href="SP/student_enrollment.php">Personal Information</a></li>
            <li><a href="SP/student_payment.php">Payment and Invoice</a></li>
			<li><a href="SP/student_grades.php">Grades</a></li>
            <li><a href="SP/student_Contact_admin.php">Contact Admin</a></li>
        </ul>
    </div>
    <div class="content">
        <div class="header">
            <h3>Welcome, <?php echo $_SESSION['student_name']; ?></h3>
            <a href="logout.php" class="logout">Logout</a>
        </div>
        <h1>Student Dashboard</h1>
        <!-- Extra message for no events -->
        <div class="no-events-message">
            <p>There are currently no upcoming events.</p>
        </div>
    </div>
</body>
</html>
