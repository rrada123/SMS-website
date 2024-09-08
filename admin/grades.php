<?php
// Include database connection
require '../db.php';

// Initialize $subjects as an empty array
$subjects = [];

// Fetch subjects data from the classes table
$query = "SELECT * FROM classes";
$result = $conn->query($query);

if ($conn->error) {
    die("Database query error: " . $conn->error);
}

// Store fetched data in an array
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row;
}

// Handle POST requests to update grades
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $subjectCode = $_POST['code'] ?? '';
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

    // Prepare and execute SQL statement
    $stmt = $conn->prepare("UPDATE classes SET Grades = ? WHERE id = ? AND code = ?");
    
    if (!$stmt) {
        echo 'Error preparing statement: ' . $conn->error;
        exit;
    }

    $stmt->bind_param('sis', $grade, $id, $subjectCode);

    if ($stmt->execute()) {
        echo 'Grade updated successfully!';
    } else {
        echo 'Error updating grade: ' . $stmt->error;
    }

    $stmt->close();
	exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subjects - Admin Dashboard</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/grades.css">
    <link rel="stylesheet" href="../css/modal.css">
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
            <span>Subjects</span>
            <span><a href="../index.php" class="logout">Logout</a></span>
        </div>
        <form id="searchForm">
            <div class="search-container">
                <label for="searchInput">Search Subjects:</label>
                <input type="text" id="searchInput" placeholder="Search by Subject Name or Code">
            </div>
        </form>

        <div class="table-container hidden" id="tableContainer">
    <table id="subjectsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Teacher</th>
                        <th>Subject Name</th>
                        <th>Subject Code</th>
                        <th>Grade</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($subjects) > 0): ?>
                        <?php foreach ($subjects as $subject): ?>
                            <tr data-subject-id="<?= htmlspecialchars($subject['id']) ?>" 
                                data-subject-code="<?= htmlspecialchars($subject['code']) ?>">
                                <td><?= htmlspecialchars($subject['id']) ?></td>
                                <td><?= htmlspecialchars($subject['teacher']) ?></td>
                                <td><?= htmlspecialchars($subject['name']) ?></td>
                                <td><?= htmlspecialchars($subject['code']) ?></td>
                                <td>
                                    <span class="gradeDisplay"><?= htmlspecialchars($subject['Grades']) ?></span>
                                    <input type="text" class="gradeInput" value="<?= htmlspecialchars($subject['Grades']) ?>" style="display:none;" />
                                </td>
                                <td>
                                    <button class="editButton">Edit</button>
                                    <button class="saveButton" style="display:none;">Save</button>
                                    <button class="cancelButton" style="display:none;">Cancel</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6">No subjects found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Grade</h2>
            <form id="editForm">
                <input type="hidden" id="modalSubjectId" name="id" />
                <input type="hidden" id="modalSubjectCode" name="subject_code" />
                <label for="modalGrade">Grade:</label>
                <input type="text" id="modalGrade" name="grade" required />
                <button type="submit">Save</button>
            </form>
        </div>
    </div>

 <script>
    $(document).ready(function() {
    // Initially hide the table
    $('#tableContainer').hide();

    // Show the table and perform search filter
    $('#searchInput').on('input', function() {
        const searchValue = $(this).val().toLowerCase();

        // Show the table container if there's any input
        if (searchValue) {
            $('#tableContainer').show();
        }

        // Filter table rows based on search input
        $('#subjectsTable tbody tr').each(function() {
            const id = $(this).find('td').eq(0).text().toLowerCase();
            const subjectName = $(this).find('td').eq(2).text().toLowerCase();
            $(this).toggle(id.indexOf(searchValue) > -1 || subjectName.indexOf(searchValue) > -1);
        });
    });

    // Show modal for editing
    $('.editButton').click(function() {
        const row = $(this).closest('tr');
        const subjectId = row.data('subject-id');
        const subjectCode = row.data('subject-code');
        const grade = row.find('.gradeDisplay').text();
        
        $('#modalSubjectId').val(subjectId);
        $('#modalSubjectCode').val(subjectCode);
        $('#modalGrade').val(grade);
        $('#editModal').show();
    });

    // Handle modal form submission
    $('#editForm').submit(function(event) {
        event.preventDefault();
        
        const subjectId = $('#modalSubjectId').val();
        const subjectCode = $('#modalSubjectCode').val();
        const grade = $('#modalGrade').val();
        
        $.ajax({
            url: 'grades.php',
            type: 'POST',
            data: {
                id: subjectId,
                code: subjectCode,
                grades: grade
            },
            success: function(response) {
                if (response.trim() === 'Grade updated successfully!') {
                    $('#editModal').hide();
                    // Reload the page to check results
                    location.reload();
                } else {
                    alert('Failed to update grade: ' + response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                alert('Failed to update grade.');
            }
        });
    });

    // Close modal
    $('.close').click(function() {
        $('#editModal').hide();
    });

    $(window).click(function(event) {
        if ($(event.target).is('#editModal')) {
            $('#editModal').hide();
        }
    });
});

</script>

</body>
</html>
