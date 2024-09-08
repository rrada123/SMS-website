<?php
// Start session and check user
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Connect to the database
$conn = mysqli_connect("localhost", "root", "", "school_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle delete request via AJAX
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    $student_id = intval($_POST['id']); // Sanitize input

    // Prepare and execute delete query
    $deleteQuery = "DELETE FROM students WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    if ($stmt) {
        $stmt->bind_param('i', $student_id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete student or student not found.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Error preparing the SQL statement.']);
    }
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Admin Dashboard</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/students.css">
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
			<li><a href="Grades.php">Grades</a></li>
            <li><a href="payment.php">Payment and Invoice</a></li>
			<li><a href="Tuition.php">Tuition</a></li>
            <li><a href="add_student.php">Add Student</a></li>
        </ul>
    </div>
    <div class="content">
        <div class="header">
            <span><h1>Students</h1></span>
            <span><a href="../logout.php" class="logout">Logout</a></span>
        </div>
        <input type="text" id="searchInput" placeholder="Filter by student name or ID..." />
        <table id="studentsTable">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Course</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
    <?php
    // Fetch student data with status ordering
    $sql = "SELECT * FROM students
            ORDER BY CASE 
                WHEN status =  '   ' THEN 1
                WHEN status = 'Pending' THEN 2
                WHEN status = 'Enrolled' THEN 3
                ELSE 4
            END";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['course']) . "</td>";
            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
            echo '<td>
                    <button onclick="viewStudent(' . $row['id'] . ')">View</button>                    
                    <button onclick="showDeleteConfirmation(' . $row['id'] . ')">Delete</button>
                  </td>';
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No students found</td></tr>";
    }

    // Close the connection
    mysqli_close($conn);
    ?>
</tbody>
        </table>
    </div>

    <script>
    function viewStudent(id) {
        // Redirect to class.php with student ID
        window.location.href = 'class.php?id=' + id;
    }

    function closeModal(modalId) {
        $('#' + modalId).hide();
    }

    function showDeleteConfirmation(id) {
        if (confirm('Are you sure you want to delete this student?')) {
            $.post('students.php', { action: 'delete', id: id }, function(response) {
                // Handle the response from PHP
                if (response.success) {
                    alert('Student deleted successfully.');
                    location.reload();
                } else {
                    alert(response.message);
                }
            }, 'json');
        }
    }
	
	    // Function to filter table rows based on search input
    $(document).ready(function() {
        $('#searchInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#studentsTable tbody tr').each(function() {
                var id = $(this).find('td').eq(0).text().toLowerCase();
                var name = $(this).find('td').eq(1).text().toLowerCase();
                if (id.includes(value) || name.includes(value)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });
</script>

</body>
</html>
