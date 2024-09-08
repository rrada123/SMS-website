<?php
// Start session at the beginning
session_start();

// Include database connection
require '../db.php';

// Initialize $subjects as an empty array
$subjects = [];

// Get the student ID from session
$student_id = $_SESSION['student_id'] ?? 0;


if (!is_numeric($student_id) || $student_id <= 0) {
    die("Invalid student ID");
}

// Fetch subjects data from the classes table for the specific student
$query = "SELECT * FROM classes WHERE id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param('i', $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($conn->error) {
    die("Database query error: " . $conn->error);
}

// Store fetched data in an array
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row;
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subjects - Student Dashboard</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="css/student_grades.css">
    <link rel="stylesheet" href="../css/modal.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="sidebar">
        <div class="admin-section">
            <img src="../images/SCHOOL_LOGO.jpg" alt="Student">
            <h2><?php echo htmlspecialchars($_SESSION['student_name'] ?? 'N/A'); ?></h2>
        </div>
        <ul>
            <li><a href="../student_portal.php">Dashboard</a></li>
            <li><a href="student_enrollment.php">Personal Information</a></li>
            <li><a href="student_payment.php">Payment and Invoice</a></li>
            <li><a href="student_grades.php">Grades</a></li>
            <li><a href="student_Contact_admin.php">Contact Admin</a></li>
        </ul>
    </div>
    <div class="content">
        <div class="header">
            <span>Grades</span>
            <span><a href="../logout.php" class="logout">Logout</a></span>
        </div>
        <form id="searchForm">
            <div class="search-container">
                <h4>Student ID: <?php echo htmlspecialchars($student_id); ?></h4>
            </div>
        </form>

        <div class="table-container">
            <table id="subjectsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Teacher</th>
                        <th>Subject Name</th>
                        <th>Subject Code</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($subjects) > 0): ?>
                        <?php foreach ($subjects as $subject): ?>
                            <tr>
                                <td><?= htmlspecialchars($subject['id']) ?></td>
                                <td><?= htmlspecialchars($subject['teacher']) ?></td>
                                <td><?= htmlspecialchars($subject['name']) ?></td>
                                <td><?= htmlspecialchars($subject['code']) ?></td>
                                <td><?= htmlspecialchars($subject['Grades']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5">No subjects found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
