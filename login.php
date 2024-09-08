<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);
    $userType = $conn->real_escape_string($_POST['user_type']);

    if ($userType === 'admin') {
        $sql = "SELECT * FROM admins WHERE email = '$email' AND password = '$password'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $_SESSION['user'] = 'admin';
            header("Location: admin/admin.php");
            exit();
        }
    } else if ($userType === 'student') {
        // Check if login using ID and last name + birthday
        $id = $conn->real_escape_string($_POST['email']); // Using 'email' field to get the ID
        $birthday = $conn->real_escape_string($_POST['password']);
        
        $sql = "SELECT * FROM students WHERE id = '$id'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $student = $result->fetch_assoc();
            $expectedPassword = $student['last_name'] . $student['birthday'];

            if ($expectedPassword === $birthday) {
                $_SESSION['user'] = 'student';
                $_SESSION['student_id'] = $student['id'];
                $_SESSION['student_name'] = $student['first_name'] . ' ' . $student['last_name'];
                header("Location: student_portal.php");
                exit();
            }
        }
    }

    $_SESSION['error'] = "Invalid login credentials or user type.";
    header("Location: index.php");
    exit();
}
?>
