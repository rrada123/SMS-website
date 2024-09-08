<?php
// Include database connection
require '../db.php'; 

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data
    $id = $_POST['id'] ?? '';
    $subjectCode = $_POST['subject_code'] ?? '';
    $grade = $_POST['grades'] ?? '';

    // Validate input
    if (!is_numeric($id) || empty($subjectCode) || empty($grade) || !is_numeric($grade)) {
        echo 'Invalid input';
        exit;
    }

    // Sanitize inputs
    $id = intval($id);
    $subjectCode = $conn->real_escape_string($subjectCode);
    $grade = $conn->real_escape_string($grade);

    // Prepare SQL statement
    $stmt = $conn->prepare("UPDATE classes SET Grades = ? WHERE id = ? AND subject_code = ?");
    
    if (!$stmt) {
        echo 'Error preparing statement: ' . $conn->error;
        exit;
    }

    // Bind parameters and execute the statement
    $stmt->bind_param('sis', $grade, $id, $subjectCode);

    if ($stmt->execute()) {
        echo 'Grade updated successfully!';
    } else {
        echo 'Error updating grade: ' . $stmt->error;
    }

    // Close statement
    $stmt->close();
}

// Close database connection
$conn->close();
?>
