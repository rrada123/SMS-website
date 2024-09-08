<?php
session_start();

// Redirect if not logged in as an admin
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = ""; // Add your database password here
$dbname = "school_db";

$conn = new mysqli($servername, $username, $password, $dbname);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Initialize variables
$subjects = [];
$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$student_name = '';

// Fetch student details
if ($student_id > 0) {
    $studentQuery = "SELECT first_name, last_name FROM students WHERE id = ?";
    if ($studentStmt = $conn->prepare($studentQuery)) {
        $studentStmt->bind_param('i', $student_id);
        $studentStmt->execute();
        $studentResult = $studentStmt->get_result();
        
        if ($studentResult->num_rows > 0) {
            $student = $studentResult->fetch_assoc();
            $student_name = htmlspecialchars($student['first_name'] . ' ' . $student['last_name']);
        } else {
            $student_name = 'Student not found';
        }
    } else {
        echo "Error preparing student query: " . $conn->error;
    }
}

// Fetch subjects data
$subjectQuery = "SELECT * FROM subjects";
if ($subjectStmt = $conn->prepare($subjectQuery)) {
    $subjectStmt->execute();
    $subjectResult = $subjectStmt->get_result();
    
    while ($subject = $subjectResult->fetch_assoc()) {
        $subjects[] = $subject;
    }
} else {
    echo "Error preparing subject query: " . $conn->error;
}

// Handle form submission to add subjects
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_subjects = $_POST['subjects'] ?? [];

    if (!empty($selected_subjects)) {
        foreach ($selected_subjects as $subject_code) {
            // Fetch details for the selected subject
            $subjectDetailsQuery = "SELECT name, teacher, schedule FROM subjects WHERE code = ?";
            if ($subjectDetailsStmt = $conn->prepare($subjectDetailsQuery)) {
                $subjectDetailsStmt->bind_param('s', $subject_code);
                $subjectDetailsStmt->execute();
                $subjectDetailsResult = $subjectDetailsStmt->get_result();
                
                if ($subjectDetailsResult->num_rows > 0) {
                    $subjectDetails = $subjectDetailsResult->fetch_assoc();
                    $subject_name = $subjectDetails['name'];
                    $teacher_name = $subjectDetails['teacher'];
                    $schedule = $subjectDetails['schedule'];

                    // Insert into classes table
                    $insertQuery = "INSERT INTO classes (id, student_name, teacher, name, schedule, code) 
                                    VALUES (?, ?, ?, ?, ?, ?)";
                    if ($insertStmt = $conn->prepare($insertQuery)) {
                        $insertStmt->bind_param('isssss', $student_id, $student_name, $teacher_name, $subject_name, $schedule, $subject_code);
                        try {
                            $insertStmt->execute();
                        } catch (mysqli_sql_exception $e) {
                            // Check for duplicate entry error
                            if ($e->getCode() === 1062) { // 1062 is the error code for duplicate entry
                                header("Location: students.php");
                                exit();
                            } else {
                                echo "Error executing insert statement: " . $e->getMessage();
                            }
                        }
						
                    } else {
                        echo "Error preparing insert statement: " . $conn->error;
                    }
                } else {
                    echo "No details found for subject code: $subject_code";
                }
            } else {
                echo "Error preparing subject details statement: " . $conn->error;
            }
        }
    } else {
        echo "<script>alert('No subjects selected.');</script>";
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Subjects</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/add_subject.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            <li><a href="payment.php">Payment and Invoice</a></li>
			<li><a href="Tuition.php">Tuition</a></li>
            <li><a href="add_student.php">Add Student</a></li>
        </ul>
    </div>
    <div class="content">
        <div class="header">
            <h1>Add Subjects to Student ID: <?php echo htmlspecialchars($student_id); ?></h1>
            <p>Student Name: <?php echo htmlspecialchars($student_name); ?></p>
            <a href="../logout.php" class="logout">Logout</a>
        </div>
        <form action="add_subject.php?id=<?php echo htmlspecialchars($student_id); ?>" method="POST">
            <div class="form-container">
                <h2>Select Subjects</h2>
                <?php if (!empty($subjects)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Subject Name</th>
                                <th>Subject Code</th>
                                <th>Schedule</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subjects as $subject): ?>
                                <tr>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="subjects[]" value="<?php echo htmlspecialchars($subject['code']); ?>">
                                        </label>
                                    </td>
                                    <td><?php echo htmlspecialchars($subject['name']); ?></td>
                                    <td><?php echo htmlspecialchars($subject['code']); ?></td>
                                    <td><?php echo htmlspecialchars($subject['schedule']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No subjects available.</p>
                <?php endif; ?>
            </div>
            <div class="btn-toolbar">
                <button type="submit" class="btn-save">Add Subjects</button>
                <a href="students.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
