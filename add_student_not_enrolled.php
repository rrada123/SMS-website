<?php
session_start();

// Database connection
$conn = mysqli_connect("localhost", "root", "", "school_db");

// Check connection
if ($conn === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Initialize success flag
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $middle_name = mysqli_real_escape_string($conn, $_POST['middle_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $age = (int) $_POST['age'];
    $birthday = mysqli_real_escape_string($conn, $_POST['birthday']);
    $place_of_birth = mysqli_real_escape_string($conn, $_POST['place_of_birth']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $citizenship = mysqli_real_escape_string($conn, $_POST['citizenship']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $email_address = mysqli_real_escape_string($conn, $_POST['email_address']);
    $present_address = mysqli_real_escape_string($conn, $_POST['present_address']);
    $permanent_address = mysqli_real_escape_string($conn, $_POST['permanent_address']);
    $mothers_name = mysqli_real_escape_string($conn, $_POST['mothers_name']);
    $fathers_name = mysqli_real_escape_string($conn, $_POST['fathers_name']);
    $fathers_contact = mysqli_real_escape_string($conn, $_POST['fathers_contact']);
    $mothers_contact = mysqli_real_escape_string($conn, $_POST['mothers_contact']);
    $fathers_occupation = mysqli_real_escape_string($conn, $_POST['fathers_occupation']);
    $mothers_occupation = mysqli_real_escape_string($conn, $_POST['mothers_occupation']);
    $year_level = (int) $_POST['year_level'];

    // Prepare the SQL statement
    $sql = "INSERT INTO students (first_name, middle_name, last_name, age, birthday, place_of_birth, gender, citizenship, contact_number, email_address, present_address, permanent_address, mothers_name, fathers_name, fathers_contact, mothers_contact, fathers_occupation, mothers_occupation, year_level) 
            VALUES ('$first_name', '$middle_name', '$last_name', $age, '$birthday', '$place_of_birth', '$gender', '$citizenship', '$contact_number', '$email_address', '$present_address', '$permanent_address', '$mothers_name', '$fathers_name', '$fathers_contact', '$mothers_contact', '$fathers_occupation', '$mothers_occupation', $year_level)";

    // Execute the SQL statement
    if (mysqli_query($conn, $sql)) {
        $success = true; // Set success flag to true

        // Set a session variable to hold the success message
        $_SESSION['message'] = 'Student added successfully. We will send an email for any additional details required.';

        // Close the connection
        mysqli_close($conn);

        // Redirect to index.php after a short delay to display the message
        header("refresh:3;url=index.php");
        exit();
    } else {
        echo "ERROR: Could not execute $sql. " . mysqli_error($conn);
    }

    // Close the connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student - Admin Dashboard</title>
    <link rel="stylesheet" href="css/add_student_not_enrolled.css"> 
    <script>
        // Function to show alert if student was added successfully
        function showAlert(success) {
            if (success) {
                alert('Data stored in the database successfully.');
            }
        }
    </script>
</head>
<body onload="showAlert(<?php echo json_encode($success); ?>)">
   
    <div class="content">
        <div class="header">
            <h1>Enrollment</h1>
        </div>
        <form action="add_student_not_enrolled.php" method="post">
 
            <label for="first_name">First Name *</label>
            <input type="text" id="first_name" name="first_name" required>

            <label for="middle_name">Middle Name</label>
            <input type="text" id="middle_name" name="middle_name">

            <label for="last_name">Last Name *</label>
            <input type="text" id="last_name" name="last_name" required>

            <label for="age">Age *</label>
            <input type="number" id="age" name="age" required>

            <label for="birthday">Birthday *</label>
            <input type="date" id="birthday" name="birthday" required>

            <label for="place_of_birth">Place of Birth *</label>
            <input type="text" id="place_of_birth" name="place_of_birth" required>

            <label for="gender">Gender *</label>
            <select id="gender" name="gender" required>
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>

            <label for="citizenship">Citizenship *</label>
            <input type="text" id="citizenship" name="citizenship" required>

            <label for="contact_number">Contact Number *</label>
            <input type="text" id="contact_number" name="contact_number" required>

            <label for="email_address">Email Address *</label>
            <input type="email" id="email_address" name="email_address" required>

            <label for="present_address">Present Address *</label>
            <input type="text" id="present_address" name="present_address" required>

            <label for="permanent_address">Permanent Address *</label>
            <input type="text" id="permanent_address" name="permanent_address" required>

            <label for="mothers_name">Mother's Name *</label>
            <input type="text" id="mothers_name" name="mothers_name" required>

            <label for="mothers_occupation">Mother's Occupation *</label>
            <input type="text" id="mothers_occupation" name="mothers_occupation" required>
            
            <label for="mothers_contact">Mother's Contact Number *</label>
            <input type="text" id="mothers_contact" name="mothers_contact" required>
            
            <label for="fathers_name">Father's Name *</label>
            <input type="text" id="fathers_name" name="fathers_name" required>
            
            <label for="fathers_contact">Father's Contact Number *</label>
            <input type="text" id="fathers_contact" name="fathers_contact" required>

            <label for="fathers_occupation">Father's Occupation *</label>
            <input type="text" id="fathers_occupation" name="fathers_occupation" required>

            <label for="year_level">Year Level *</label>
            
            <select id="year_level" name="year_level" required>
                <option value="">Select Year</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
            </select>

            <button type="submit">Send</button>
        </form>
    </div>
</body>
</html>
