<?php
session_start();

// Redirect if not logged in as a student
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'student') {
    header("Location: index.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = ""; // Add your database password here
$dbname = "school_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Initialize variables
$students = [];
$subjects = [];
$tuition = [];

// Fetch student data
$student_id = $_SESSION['student_id'] ?? 0; // Ensure this is set properly
$studentQuery = "SELECT * FROM students WHERE id = ?";
$studentStmt = $conn->prepare($studentQuery);
if ($studentStmt) {
    $studentStmt->bind_param('i', $student_id);
    $studentStmt->execute();
    $studentsResult = $studentStmt->get_result();

    if ($studentsResult->num_rows > 0) {
        $students = $studentsResult->fetch_assoc();
    }
}

// Fetch subjects data
$subjectQuery = "SELECT * FROM classes WHERE id = ?";
$subjectStmt = $conn->prepare($subjectQuery);
if ($subjectStmt) {
    $subjectStmt->bind_param('i', $student_id); // Adjust based on your schema
    $subjectStmt->execute();
    $subjectResult = $subjectStmt->get_result();

    if ($subjectResult->num_rows > 0) {
        while ($subject = $subjectResult->fetch_assoc()) {
            $subjects[] = $subject;
        }
    }
}

// Check if Billing table exists
$tableExists = $conn->query("SHOW TABLES LIKE 'Billing'")->num_rows > 0;

// Fetch tuition data
if ($tableExists) {
    $tuitionQuery = "SELECT * FROM Billing WHERE id = ?";
    $tuitionStmt = $conn->prepare($tuitionQuery);
    if ($tuitionStmt) {
        $tuitionStmt->bind_param('i', $student_id);
        $tuitionStmt->execute();
        $tuitionResult = $tuitionStmt->get_result();

        if ($tuitionResult->num_rows > 0) {
            while ($tuition_item = $tuitionResult->fetch_assoc()) {
                $tuition[] = $tuition_item;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information Sheet</title>
    
    <!-- Link to additional stylesheets -->
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../SP/css/student_enrollment.css">
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
    <div class="container">
        <div class="header">
            <h1>STUDENT INFORMATION SHEET</h1>
            <a href="../logout.php" class="logout">Logout</a>
        </div>
        <div class="section">
            <h2>PERSONAL INFORMATION</h2>
            <h4>Student ID:   <?php echo htmlspecialchars($students['id'] ?? 'N/A'); ?></h4>
            <table>
                <tr>
                    <th>Complete Name:</th>
                    <td><?php echo htmlspecialchars(($students['first_name'] ?? '') . ' ' . ($students['last_name'] ?? '')); ?></td>
                    <th>Gender:</th>
                    <td><?php echo htmlspecialchars($students['gender'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <th>Birth Date:</th>
                    <td><?php echo htmlspecialchars($students['birthday'] ?? 'N/A'); ?></td>
                    <th>Place of Birth:</th>
                    <td><?php echo htmlspecialchars($students['place_of_birth'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <th>Address:</th>
                    <td><?php echo htmlspecialchars($students['present_address'] ?? 'N/A'); ?></td>
                </tr>
            </table>
        </div>
        <div class="section">
            <h2>PARENT's INFORMATION</h2>
            <table>
                <tr>
                    <th>Father's Name:</th>
                    <td><?php echo htmlspecialchars($students['fathers_name'] ?? 'N/A'); ?></td>
                    <th>Father's Occupation:</th>
                    <td><?php echo htmlspecialchars($students['fathers_occupation'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <th>Father's Contact Number:</th>
                    <td><?php echo htmlspecialchars($students['fathers_contact'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <th>Mother's Name:</th>
                    <td><?php echo htmlspecialchars($students['mothers_name'] ?? 'N/A'); ?></td>
                    <th>Mother's Occupation:</th>
                    <td><?php echo htmlspecialchars($students['mothers_occupation'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <th>Mother's Contact Number:</th>
                    <td><?php echo htmlspecialchars($students['mothers_contact'] ?? 'N/A'); ?></td>
                </tr>
             
            </table>
        </div>
        <div class="section">
            <h2>CLASS INFORMATION</h2>
            <table>
                <tr>
                    <th>Course:</th>
                    <td><?php echo htmlspecialchars($students['course'] ?? 'N/A'); ?></td>
                    <th>Year Level:</th>
                    <td><?php echo htmlspecialchars($students['year_level'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <th>Section:</th>
                    <td><?php echo htmlspecialchars($students['section'] ?? 'N/A'); ?></td>
                    <th>Advisor:</th>
                    <td><?php echo htmlspecialchars($students['advisor'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <th>Status:</th>
                    <td><?php echo htmlspecialchars($students['status'] ?? 'N/A'); ?></td>
                </tr>
            </table>
        </div>
        <div class="section">
    <h2>SUBJECTS</h2>
    <table>
        <thead>
            <tr>
                <th>Teacher's Name</th>
                <th>Subject Name</th>
                <th>Subject Code</th>
                <th>Schedule</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!empty($subjects)) {
                foreach ($subjects as $subject) {
                    echo "<tr>
                        <td>" . htmlspecialchars($subject['teacher'] ?? 'N/A') . "</td>
                        <td>" . htmlspecialchars($subject['name'] ?? 'N/A') . "</td>
                        <td>" . htmlspecialchars($subject['code'] ?? 'N/A') . "</td>
                        <td>" . htmlspecialchars($subject['schedule'] ?? 'N/A') . "</td>
                    </tr>";
                }
            } else {
                echo "<tr>
                    <td colspan='4'>No subjects found.</td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

        <div class="section">
            <h2>TUITION</h2>
            <table>
                <?php
                if ($tableExists) {
                    if (!empty($tuition)) {
                        foreach ($tuition as $tuition_item) {
                            echo "<tr>
                                <th>Item Code:</th>
                                <td>" . htmlspecialchars($tuition_item['item_code'] ?? 'N/A') . "</td>
                                <th>Amount:</th>
                                <td>" . htmlspecialchars($tuition_item['amount'] ?? 'N/A') . "</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr>
                            <td colspan='4'>No tuition data found.</td>
                        </tr>";
                    }
                } else {
                    echo "<tr>
                        <td colspan='4'>Billing table is under maintenance.</td>
                    </tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>
