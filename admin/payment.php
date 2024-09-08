<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

require '../db.php'; // Include database connection

$hidden_images_file = 'hidden_images.json';

// Create hidden_images.json if it doesn't exist
if (!file_exists($hidden_images_file)) {
    file_put_contents($hidden_images_file, json_encode([]));
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_image') {
    $target_dir = "../images/"; // Corrected path
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        echo "Image uploaded successfully.";
    } else {
        echo "Error uploading image.";
    }
    exit;
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
    <title>Payment and Invoice - Admin Dashboard</title>
    <link rel="stylesheet" href="../css/sidebar.css"> <!-- Link to sidebar CSS -->
    <link rel="stylesheet" href="../css/payment.css"> <!-- Link to payment CSS -->
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
			<li><a href="Grades.php">Grades</a></li>
			<li><a href="Tuition.php">Tuition</a></li>
			<li><a href="add_student.php">Add Student</a></li>
        </ul>
    </div>
    <div class="content">
        <div class="header">
            <h3>Payment and Invoice</h3>
            <a href="../logout.php" class="logout">Logout</a>
        </div>
        <div class="image-section">
            <h2>Images</h2>
            <button class="edit-btn" id="editPage">Edit</button>
            <div class="images-container">
                <?php foreach ($images as $image): ?>
                    <?php if (!in_array($image, $hidden_images)): ?>
                        <div class="image-wrapper">
                            <img src="../images/<?php echo $image; ?>" alt="<?php echo $image; ?>" class="displayed-image">
                            <button class="btn-hide" onclick="hideImage('<?php echo $image; ?>')">Hide</button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Edit Form Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Payment and Invoice Page</h2>
                <span class="close" id="closeEditModal">&times;</span>
            </div>
            <div class="modal-body">
                <form id="uploadImageForm" enctype="multipart/form-data" class="edit-modal">
                    <label for="image">Upload Image:</label>
                    <input type="file" id="image" name="image" required />
                    <button type="submit" class="btn btn-upload">Upload</button>
                </form>
                <div class="image-gallery edit-modal">
                    <h3>Existing Images</h3>
                    <div class="gallery-container">
                        <?php foreach ($images as $image): ?>
                            <div class="image-wrapper">
                                <img src="../images/<?php echo $image; ?>" alt="<?php echo $image; ?>" class="gallery-image">
                                <button class="btn-hide" onclick="hideImage('<?php echo $image; ?>')">Hide</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="hidden-images-gallery edit-modal">
                    <h3>Hidden Images</h3>
                    <div class="gallery-container">
                        <?php foreach ($hidden_images as $image): ?>
                            <div class="image-wrapper">
                                <img src="../images/<?php echo $image; ?>" alt="<?php echo $image; ?>" class="gallery-image">
                                <button class="btn-unhide" onclick="unhideImage('<?php echo $image; ?>')">Unhide</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Show edit modal
            $('#editPage').click(function() {
                $('#editModal').show();
            });

            // Close edit modal
            $('#closeEditModal').click(function() {
                $('#editModal').hide();
            });

            // Handle image upload form submission
            $('#uploadImageForm').submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                formData.append('action', 'upload_image');
                $.ajax({
                    url: 'payment.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        alert(response);
                        location.reload();
                    }
                });
            });
        });

        // Handle image hiding
        function hideImage(image) {
            if (confirm("Are you sure you want to hide this image?")) {
                $.post('payment.php', { action: 'hide_image', image: image }, function(response) {
                    alert(response);
                    location.reload();
                });
            }
        }

        // Handle image unhiding
        function unhideImage(image) {
            if (confirm("Are you sure you want to unhide this image?")) {
                $.post('payment.php', { action: 'unhide_image', image: image }, function(response) {
                    alert(response);
                    location.reload();
                });
            }
        }
    </script>
</body>
</html>
