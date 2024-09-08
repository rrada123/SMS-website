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

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Initialize variables
$students = [];
$subjects = [];
$tuition = [];

// Fetch student ID from query parameter
$student_id = $_GET['id'] ?? 0;
$edit_mode = isset($_GET['edit']) && $_GET['edit'] === 'true';

// Fetch student data
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
    $subjectStmt->bind_param('i', $student_id);
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

// Handle delete request via AJAX
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['subject_code']) && isset($_POST['student_id'])) {
    $subject_code = $_POST['subject_code'];
    $student_id = $_POST['student_id'];

    // Prepare and execute delete query
    $deleteQuery = "DELETE FROM classes WHERE code = ? AND id = ?";
    $stmt = $conn->prepare($deleteQuery);
    if ($stmt) {
        $stmt->bind_param('si', $subject_code, $student_id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete subject or subject not found.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Error preparing the SQL statement.']);
    }
    $conn->close();
    exit();
}

// Handle student update request
// Handle student update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $student_id = $_POST['id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $gender = $_POST['gender'];
    $birthday = $_POST['birthday'];
    $place_of_birth = $_POST['place_of_birth'];
    $present_address = $_POST['present_address'];
    $fathers_name = $_POST['fathers_name'];
    $fathers_occupation = $_POST['fathers_occupation'];
    $fathers_contact = $_POST['fathers_contact'];
    $mothers_name = $_POST['mothers_name'];
    $mothers_occupation = $_POST['mothers_occupation'];
    $mothers_contact = $_POST['mothers_contact'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];
    $section = $_POST['section'];
    $status = $_POST['status'];

    $updateQuery = "UPDATE students SET first_name = ?, last_name = ?, gender = ?, birthday = ?, place_of_birth = ?, present_address = ?, fathers_name = ?, fathers_occupation = ?, fathers_contact = ?, mothers_name = ?, mothers_occupation = ?, mothers_contact = ?, course = ?, year_level = ?, section = ?, status = ? WHERE id = ?";

    $stmt = $conn->prepare($updateQuery);
    if ($stmt) {
			$stmt->bind_param('ssssssssssssssssi', $first_name, $last_name, $gender, $birthday, $place_of_birth, $present_address, $fathers_name, $fathers_occupation, $fathers_contact, $mothers_name, $mothers_occupation, $mothers_contact, $course, $year_level, $section, $status, $student_id);
        if ($stmt->execute()) {
            // Redirect to students.php after successful update
            header("Location: students.php");
            exit();
            echo "<script>alert('Student information updated successfully.'); location.reload();</script>";
        } else {
            echo "<script>alert('Failed to update student information.');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Error preparing the SQL statement.');</script>";
    }
    $conn->close();
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information Sheet</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/class.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4); /* Darker overlay for better focus */
}

.modal-content {
    background-color: #ffffff; /* White background for contrast */
    margin: 5% auto;
    padding: 25px;
    border: 1px solid #ddd;
    width: 50%;
    max-width: 600px;
    border-radius: 10px; /* More pronounced rounding */
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3); /* Stronger shadow for depth */
}

.close {
    color: #e74c3c; /* Red color for the close button */
    float: right;
    font-size: 28px;
    font-weight: bold;
    transition: color 0.3s;
}

.close:hover,
.close:focus {
    color: #c0392b; /* Darker red on hover */
    text-decoration: none;
    cursor: pointer;
}

.modal-content h2 {
    color: #3498db;
    margin-top: 0;
    font-size: 22px;
}

.modal-content label {
    display: block;
    font-weight: bold;
    margin-bottom: 6px;
    color: #2c3e50; /* Darker label color */
}

.modal-content input[type="text"],
.modal-content input[type="date"],
.modal-content select {
    width: calc(50% - 10px); /* Two inputs side by side */
    padding: 10px;
    margin: 5px 0 15px 0;
    display: inline-block;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 14px;
    color: #2c3e50;
}

.modal-content input[type="text"]:focus,
.modal-content input[type="date"]:focus,
.modal-content select:focus {
    border-color: #3498db; /* Blue border on focus */
    outline: none;
    box-shadow: 0 0 5px rgba(52, 152, 219, 0.5); /* Subtle shadow on focus */
}

.modal-content .form-group {
    display: flex;
    justify-content: space-between;
}

.modal-content .form-group label {
    width: 48%;
}

.modal-content .form-group input[type="text"],
.modal-content .form-group input[type="date"],
.modal-content .form-group select {
    width: 100%;
}

.modal-content button[type="submit"] {
    background-color: #3498db;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
}

.modal-content button[type="submit"]:hover {
    background-color: #2980b9; /* Darker blue on hover */
}

.modal-content .form-group .half-width {
    width: calc(50% - 10px); /* Ensure even spacing */
}

.modal-content .form-group input.full-width {
    width: 100%;
    margin-bottom: 20px; /* Margin for full-width fields */
}
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="admin-section">
            <img src="../images/SCHOOL_LOGO.jpg" alt="School Logo">
            <h2><?php echo htmlspecialchars(($students['first_name'] ?? '') . ' ' . ($students['last_name'] ?? '')); ?></h2>
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
    <div class="container">
        <div class="header">
            <h1>STUDENT INFORMATION SHEET</h1>
            <a href="../logout.php" class="logout">Logout</a>
        </div>

        <!-- Display student information -->
        <div class="section">
            <h2>STUDENT INFORMATION <button onclick="openEditModal()">EDIT</button></h2>
            <h4>Student ID: <?php echo htmlspecialchars($students['id'] ?? 'N/A'); ?> NAME: <?php echo htmlspecialchars(($students['first_name'] ?? '') . ' ' . ($students['last_name'] ?? '')); ?></h4>
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
            <h2>PARENT'S INFORMATION</h2>
            <table>
                <tr>
                    <th>Father's Name:</th>
                    <td><?php echo htmlspecialchars($students['fathers_name'] ?? 'N/A'); ?></td>
                    <th>Occupation:</th>
                    <td><?php echo htmlspecialchars($students['fathers_occupation'] ?? 'N/A'); ?></td>
                    <th>Contact:</th>
                    <td><?php echo htmlspecialchars($students['fathers_contact'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <th>Mother's Name:</th>
                    <td><?php echo htmlspecialchars($students['mothers_name'] ?? 'N/A'); ?></td>
                    <th>Occupation:</th>
                    <td><?php echo htmlspecialchars($students['mothers_occupation'] ?? 'N/A'); ?></td>
                    <th>Contact:</th>
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
                    
                </tr>
                <tr>
                    <th>Status:</th>
                    <td><?php echo htmlspecialchars($students['status'] ?? 'N/A'); ?></td>
                </tr>
            </table>
        </div>

        <!-- Display class subjects -->
        <div class="section">
            <h2>SUBJECTS ENROLLED    <button onclick="window.location.href='add_subject.php?id=<?php echo htmlspecialchars($students['id']); ?>'">Add</button>
</h2>
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Teacher's name</th>
                        <th>Subject</th>
                        <th>Schedule</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjects as $subject): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($subject['code']); ?></td>
                        <td><?php echo htmlspecialchars($subject['teacher']); ?></td>
                        <td><?php echo htmlspecialchars($subject['name']); ?></td>
                        <td><?php echo htmlspecialchars($subject['schedule']); ?></td>
                        
                        <td><button class="delete-button" data-code="<?php echo htmlspecialchars($subject['code']); ?>">Delete</button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Display tuition fees -->
        <?php if ($tableExists && !empty($tuition)): ?>
			<div class="section">
				<h2>TUITION FEES</h2>
				<table>
					<thead>
						<tr>
							<th>Amount</th>
							<th>Description</th>
							<th>Date</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($tuition as $item): ?>
						<tr>
							<td><?php echo htmlspecialchars($item['amount']); ?></td>
							<td><?php echo htmlspecialchars($item['description']); ?></td>
							<td><?php echo htmlspecialchars($item['date']); ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php else: ?>
			<div class="section">
				<h2>TUITION FEES</h2>
				<p>Billing under maintenance</p>
			</div>
		<?php endif; ?>
			
    </div>

    <!-- Edit Student Modal -->
    <div id="editStudentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Student Information</h2>
            <form method="post" action="class.php">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($students['id'] ?? ''); ?>">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($students['first_name'] ?? ''); ?>" required>
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($students['last_name'] ?? ''); ?>" required>
                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="Male" <?php echo ($students['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo ($students['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                </select>
                <label for="birthday">Birthday:</label>
                <input type="date" id="birthday" name="birthday" value="<?php echo htmlspecialchars($students['birthday'] ?? ''); ?>" required>
                <label for="place_of_birth">Place of Birth:</label>
                <input type="text" id="place_of_birth" name="place_of_birth" value="<?php echo htmlspecialchars($students['place_of_birth'] ?? ''); ?>" required>
                <label for="present_address">Present Address:</label>
                <input type="text" id="present_address" name="present_address" value="<?php echo htmlspecialchars($students['present_address'] ?? ''); ?>" required>
                <label for="fathers_name">Father's Name:</label>
                <input type="text" id="fathers_name" name="fathers_name" value="<?php echo htmlspecialchars($students['fathers_name'] ?? ''); ?>" required>
                <label for="fathers_occupation">Father's Occupation:</label>
                <input type="text" id="fathers_occupation" name="fathers_occupation" value="<?php echo htmlspecialchars($students['fathers_occupation'] ?? ''); ?>" required>
                <label for="fathers_contact">Father's Contact:</label>
                <input type="text" id="fathers_contact" name="fathers_contact" value="<?php echo htmlspecialchars($students['fathers_contact'] ?? ''); ?>" required>
                <label for="mothers_name">Mother's Name:</label>
                <input type="text" id="mothers_name" name="mothers_name" value="<?php echo htmlspecialchars($students['mothers_name'] ?? ''); ?>" required>
                <label for="mothers_occupation">Mother's Occupation:</label>
                <input type="text" id="mothers_occupation" name="mothers_occupation" value="<?php echo htmlspecialchars($students['mothers_occupation'] ?? ''); ?>" required>
                <label for="mothers_contact">Mother's Contact:</label>
                <input type="text" id="mothers_contact" name="mothers_contact" value="<?php echo htmlspecialchars($students['mothers_contact'] ?? ''); ?>" required>
                <label for="course">Course:</label>
                <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($students['course'] ?? ''); ?>" required>
                <label for="year_level">Year Level:</label>
                <input type="number" id="year_level" name="year_level" value="<?php echo htmlspecialchars($students['year_level'] ?? ''); ?>" required>
                <label for="section">Section:</label>
                <input type="text" id="section" name="section" value="<?php echo htmlspecialchars($students['section'] ?? ''); ?>" required>           
                <label for="status">Status:</label>
                <input type="text" id="status" name="status" value="<?php echo htmlspecialchars($students['status'] ?? ''); ?>" required>
                <button type="submit" name="update">Update</button>
            </form>
        </div>
    </div>

    <script>
       // Function to open the modal
function openEditModal() {
    document.getElementById('editStudentModal').style.display = 'block';
}

// Function to close the modal
function closeEditModal() {
    document.getElementById('editStudentModal').style.display = 'none';
}

// jQuery for handling the delete button
$(document).ready(function() {
    $('.delete-button').click(function() {
        var code = $(this).data('code');
        var studentId = <?php echo $student_id; ?>;

        $.post('class.php', {
            action: 'delete',
            subject_code: code,
            student_id: studentId
        }, function(response) {
            var data = JSON.parse(response);
            if (data.success) {
                alert('Subject deleted successfully.');
                location.reload();
            } else {
                alert(data.message);
            }
        });
    });
});

    </script>
</body>
</html>
