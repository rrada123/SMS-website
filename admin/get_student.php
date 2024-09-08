<?php
// Connect to the database
$conn = mysqli_connect("localhost", "root", "", "school_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get student ID from POST request
$id = $_POST['id'] ?? 0;

// Fetch student details
$sql = "SELECT * FROM students WHERE id = '$id'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $age = (int) $row['age'];
    $birthday = htmlspecialchars($row['birthday']);
    $place_of_birth = htmlspecialchars($row['place_of_birth']);
    $gender = htmlspecialchars($row['gender']);
    $citizenship = htmlspecialchars($row['citizenship']);
    $contact_number = htmlspecialchars($row['contact_number']);
    $email_address = htmlspecialchars($row['email_address']);
    $present_address = htmlspecialchars($row['present_address']);
    $permanent_address = htmlspecialchars($row['permanent_address']);
    $mothers_name = htmlspecialchars($row['mothers_name']);
    $mothers_occupation = htmlspecialchars($row['mothers_occupation']);
    $mothers_contact = htmlspecialchars($row['mothers_contact']);
    $fathers_name = htmlspecialchars($row['fathers_name']);
    $fathers_contact = htmlspecialchars($row['fathers_contact']);
    $fathers_occupation = htmlspecialchars($row['fathers_occupation']);
    $first_name = htmlspecialchars($row['first_name']);
    $last_name = htmlspecialchars($row['last_name']);
    $course = htmlspecialchars($row['course']);
    $section = htmlspecialchars($row['section']);
    $status = htmlspecialchars($row['status']);
    $year_level = htmlspecialchars($row['year_level']);

    echo "<h3>Student Information</h3>";
    echo "<p><strong>Name:</strong> $first_name $last_name</p>";
    echo "<p><strong>Age:</strong> $age</p>";
    echo "<p><strong>Birthday:</strong> $birthday</p>";
    echo "<p><strong>Place of Birth:</strong> $place_of_birth</p>";
    echo "<p><strong>Gender:</strong> $gender</p>";
    echo "<p><strong>Citizenship:</strong> $citizenship</p>";
    echo "<p><strong>Contact Number:</strong> $contact_number</p>";
    echo "<p><strong>Email Address:</strong> $email_address</p>";
    echo "<p><strong>Present Address:</strong> $present_address</p>";
    echo "<p><strong>Permanent Address:</strong> $permanent_address</p>";
    echo "<p><strong>Mother's Name:</strong> $mothers_name</p>";
    echo "<p><strong>Mother's Occupation:</strong> $mothers_occupation</p>";
    echo "<p><strong>Mother's Contact Number:</strong> $mothers_contact</p>";
    echo "<p><strong>Father's Name:</strong> $fathers_name</p>";
    echo "<p><strong>Father's Occupation:</strong> $fathers_occupation</p>";
    echo "<p><strong>Father's Contact Number:</strong> $fathers_contact</p>";
    echo "<p><strong>Course:</strong> $course</p>";
    echo "<p><strong>Section:</strong> $section</p>";
    echo "<p><strong>Status:</strong> $status</p>";
    echo "<p><strong>Year Level:</strong> $year_level</p>";
} else {
    echo "No student found with ID: $id";
}

// Close the connection
mysqli_close($conn);
?>
