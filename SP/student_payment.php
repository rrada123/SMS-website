<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'student') {
    header("Location: index.php");
    exit();
}

require '../db.php'; // Adjusted path to db.php

$hidden_images_file = '../hidden_images.json'; // Adjusted path to hidden_images.json

// Create hidden_images.json if it doesn't exist
if (!file_exists($hidden_images_file)) {
    file_put_contents($hidden_images_file, json_encode([]));
}

// Handle image hiding
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'hide_image') {
    $image = $_POST['image'];
    $hidden_images = json_decode(file_get_contents($hidden_images_file), true);
    $hidden_images[] = $image;
    file_put_contents($hidden_images_file, json_encode(array_unique($hidden_images)));
    echo "Image hidden successfully.";
    exit;
}

// Handle image unhiding
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'unhide_image') {
    $image = $_POST['image'];
    $hidden_images = json_decode(file_get_contents($hidden_images_file), true);
    if (($key = array_search($image, $hidden_images)) !== false) {
        unset($hidden_images[$key]);
    }
    file_put_contents($hidden_images_file, json_encode(array_values($hidden_images)));
    echo "Image unhidden successfully.";
    exit;
}

// Fetch images from the images folder
$images = array_diff(scandir('../images'), array('..', '.'));
$hidden_images = json_decode(file_get_contents($hidden_images_file), true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment and Invoice - Student Dashboard</title>
    <link rel="stylesheet" href="../css/sidebar.css"> <!-- Link to sidebar CSS -->
    <link rel="stylesheet" href="../sp/css/student_payment.css"> <!-- Link to payment CSS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="sidebar">
        <div class="admin-section">
            <img src="../images/SCHOOL_LOGO.jpg" alt="Student">
            <h2><?php echo $_SESSION['student_name']; ?></h2>
        </div>
        <ul>
            <li><a href="../student_portal.php">Dashboard</a></li>
            <li><a href="student_enrollment.php">Personal Information</a></li>
            <li><a href="student_payment.php">Payment and Invoice</a></li>
			<li><a href="student_grades.php">Grades</a></li>
			<li><a href="student_Contact_admin.php">Contact Admin</a></li>
            
        </ul>
    </div>
    <div class="content">
        <div class="header">
            <h3>Payment and Invoice</h3>
            <a href="../logout.php" class="logout">Logout</a>
        </div>
        <div class="image-section">
            <h2>Payment Methods</h2>
            <div class="images-container">
                <?php foreach ($images as $image): ?>
                    <?php if (!in_array($image, $hidden_images)): ?>
                        <div class="image-wrapper">
                            <img src="../images/<?php echo $image; ?>" alt="<?php echo $image; ?>" class="displayed-image" style="width: 350px; height: 500px;">
                            
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
</body>
</html>
