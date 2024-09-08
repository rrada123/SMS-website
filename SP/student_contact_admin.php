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

// Fetch admin email
$adminEmail = '';
$adminQuery = "SELECT email FROM admins LIMIT 1";
$adminResult = $conn->query($adminQuery);

if ($adminResult && $adminResult->num_rows > 0) {
    $adminEmail = $adminResult->fetch_assoc()['email'];
}

if (!$adminEmail) {
    die("Admin email not found.");
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Admin</title>
    
    <!-- Link to additional stylesheets -->
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../SP/css/student_contact_admin.css">
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
            <li><a href="student_contact_admin.php">Contact Admin</a></li>
        </ul>
    </div>
    <div class="container">
        <div class="header">
            <h1>Contact Admin</h1>
            <a href="../logout.php" class="logout">Logout</a>
        </div>
        <div class="section">
            <h2>Please select an option</h2>
            <form id="contact-form" method="POST" action="process_contact.php">
                <input type="hidden" name="student_name" value="<?php echo htmlspecialchars($_SESSION['student_name'] ?? 'Student'); ?>">
                
                <label>
                    <input type="radio" name="contact_reason" value="Request update information" required>
                    Request update information
                </label>
                <label>
                    <input type="radio" name="contact_reason" value="Other Concerns">
                    Other Concerns
                </label>
                <div id="update-information-form" class="form-container">
                    <h2>Request Information Change</h2>
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                    <label for="birthday">Birthday:</label>
                    <input type="date" id="birthday" name="birthday">
                    <label for="place_of_birth">Place of Birth:</label>
                    <input type="text" id="place_of_birth" name="place_of_birth">
                    <label for="present_address">Present Address:</label>
                    <input type="text" id="present_address" name="present_address">
                    <label for="fathers_name">Father's Name:</label>
                    <input type="text" id="fathers_name" name="fathers_name">
                    <label for="fathers_occupation">Father's Occupation:</label>
                    <input type="text" id="fathers_occupation" name="fathers_occupation">
                    <label for="fathers_contact">Father's Contact:</label>
                    <input type="text" id="fathers_contact" name="fathers_contact">
                    <label for="mothers_name">Mother's Name:</label>
                    <input type="text" id="mothers_name" name="mothers_name">
                    <label for="mothers_occupation">Mother's Occupation:</label>
                    <input type="text" id="mothers_occupation" name="mothers_occupation">
                    <label for="mothers_contact">Mother's Contact:</label>
                    <input type="text" id="mothers_contact" name="mothers_contact">
                    <label for="course">Course:</label>
                    <input type="text" id="course" name="course">
                    <label for="year_level">Year Level:</label>
                    <input type="number" id="year_level" name="year_level">
                    <label for="section">Section:</label>
                    <input type="text" id="section" name="section">           
                    <label for="status">Status:</label>
                    <input type="text" id="status" name="status">
                    <button type="submit" name="update">Update</button>
                </div>
                <div id="other-concerns-box" class="form-container">
                    <label for="other_concerns_text">Please describe your concern:</label>
                    <textarea name="other_concerns_text" id="other_concerns_text" maxlength="1000"></textarea>
					<input type="submit" value="Submit">
                </div>
                
            </form>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('input[name="contact_reason"]').on('change', function() {
                if ($(this).val() === 'Request update information') {
                    $('#update-information-form').show();
                    $('#other-concerns-box').hide();
                } else if ($(this).val() === 'Other Concerns') {
                    $('#update-information-form').hide();
                    $('#other-concerns-box').show();
                }
            });
        });
    </script>
</body>
</html>
