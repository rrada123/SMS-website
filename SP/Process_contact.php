<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'student') {
    header("Location: index.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = ""; // Add your database password here
$dbname = "school_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$studentName = $conn->real_escape_string($_POST['student_name']);
$contactReason = $conn->real_escape_string($_POST['contact_reason']);
$otherConcernsText = isset($_POST['other_concerns_text']) ? $conn->real_escape_string($_POST['other_concerns_text']) : '';

if ($contactReason === 'Request update information') {
    $studentId = $conn->real_escape_string($_POST['student_id']);
    $firstName = $conn->real_escape_string($_POST['first_name']);
    $lastName = $conn->real_escape_string($_POST['last_name']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $birthday = $conn->real_escape_string($_POST['birthday']);
    $placeOfBirth = $conn->real_escape_string($_POST['place_of_birth']);
    $presentAddress = $conn->real_escape_string($_POST['present_address']);
    $fathersName = $conn->real_escape_string($_POST['fathers_name']);
    $fathersOccupation = $conn->real_escape_string($_POST['fathers_occupation']);
    $fathersContact = $conn->real_escape_string($_POST['fathers_contact']);
    $mothersName = $conn->real_escape_string($_POST['mothers_name']);
    $mothersOccupation = $conn->real_escape_string($_POST['mothers_occupation']);
    $mothersContact = $conn->real_escape_string($_POST['mothers_contact']);
    $course = $conn->real_escape_string($_POST['course']);
    $yearLevel = $conn->real_escape_string($_POST['year_level']);
    $section = $conn->real_escape_string($_POST['section']);
    $status = $conn->real_escape_string($_POST['status']);

    $updateQuery = "UPDATE students SET 
                    first_name = '$firstName', 
                    last_name = '$lastName', 
                    gender = '$gender', 
                    birthday = '$birthday', 
                    place_of_birth = '$placeOfBirth', 
                    present_address = '$presentAddress', 
                    fathers_name = '$fathersName', 
                    fathers_occupation = '$fathersOccupation', 
                    fathers_contact = '$fathersContact', 
                    mothers_name = '$mothersName', 
                    mothers_occupation = '$mothersOccupation', 
                    mothers_contact = '$mothersContact', 
                    course = '$course', 
                    year_level = '$yearLevel', 
                    section = '$section', 
                    status = '$status' 
                    WHERE id = '$studentId'";

    if ($conn->query($updateQuery) === TRUE) {
        $message = "Your information has been updated.";
    } else {
        $message = "Error updating record: " . $conn->error;
    }
} else {
    $message = "Thank you for your Request. We will get back to you soon.";
}

$to = 'sample@gmail.com'; // Replace with admin email
$subject = "Contact Form Submission";
$headers = "From: no-reply@yourdomain.com\r\n";
$headers .= "Reply-To: no-reply@yourdomain.com\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$body = "Student Name: $studentName<br>Contact Reason: $contactReason<br>Other Concerns: $otherConcernsText";

// Send email
mail($to, $subject, $body, $headers);

// Redirect with a thank you message
echo "<script>alert('$message'); window.location.href = 'student_contact_admin.php';</script>";

$conn->close();
?>
