<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Login Portal</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="login-container">
        <img src="images/SCHOOL_LOGO.jpg" alt="School Logo">
        <h1>School Login Portal</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p class="error-message">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form method="POST" action="login.php">
            <input type="text" placeholder="Email" id="email" name="email" required>
            <input type="password" placeholder="Password" id="password" name="password" required>
            <select id="user-type" name="user_type" required>
                <option value="">Select User Type</option>
                <option value="admin">Admin</option>
                <option value="student">Student</option>
            </select>
            <button type="submit">Login</button>
        </form>
        <div class="enrollment-info">
            <p>Not yet a student?</p>
            <a href="add_student_not_enrolled.php?source=login" class="enrollment-link">Click here to enroll</a>
        </div>
    </div>
</body>
</html>
