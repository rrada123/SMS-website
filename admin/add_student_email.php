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
    <script src="https://cdn.jsdelivr.net/npm/emailjs-com@2.6.4/dist/email.min.js"></script>
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
            <h1>Add Subjects</h1>
            <a href="../logout.php" class="logout">Logout</a>
        </div>

        <!-- Email Form for Sending Google Forms Link -->
        <div class="email-form-container">
            <h2>Send Google Form Link to Email</h2>
            <form id="emailForm">
                <label for="email">Enter Email Address:</label><br><br>
                <input type="email" id="email" name="email" placeholder="Enter email" required>
                <br><br>
                <button type="submit">Send Form Link</button>
            </form>
        </div>
       
    </div>

    <script type="text/javascript">
        (function() {
            emailjs.init('YOUR_PUBLIC_KEY');  // Replace with your EmailJS public key
        })();

        document.getElementById('emailForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent form from submitting in the traditional way

            // Get the email from the form input
            var email = document.getElementById('email').value;

            // Define the template parameters for EmailJS
            var templateParams = {
                user_email: email, // EmailJS uses this to send the email to this address
                google_form_link: 'YOUR_GOOGLE_FORM_LINK' // Replace with your actual Google Form link
            };

            // Send the email using EmailJS
            emailjs.send('YOUR_SERVICE_ID', 'YOUR_TEMPLATE_ID', templateParams)
            .then(function(response) {
                console.log('SUCCESS!', response.status, response.text);
                alert('Google Form link sent successfully!');
            }, function(error) {
                console.log('FAILED...', error);
                alert('Failed to send email.');
            });
        });
    </script>
</body>
</html>
