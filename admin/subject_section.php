<?php
// Start the session and check if the user is an admin
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Include database connection
require '../db.php'; 

// Handle form submissions (Edit, Delete, Add)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'edit') {
        // Edit subject
        $stmt = $conn->prepare("UPDATE subjects SET code=?, name=?, description=?, teacher=?, schedule=? WHERE id=?");
        $stmt->bind_param('sssssi', $_POST['code'], $_POST['name'], $_POST['description'], $_POST['teacher'], $_POST['schedule'], $_POST['id']);
        $response = $stmt->execute() ? "Subject updated successfully." : "Error: " . $stmt->error;

    } elseif ($action === 'delete') {
        // Delete subject
        if (isset($_POST['id']) && is_numeric($_POST['id'])) { // Ensure id is set and is a number
            $stmt = $conn->prepare("DELETE FROM subjects WHERE id=?");
            $stmt->bind_param('i', $_POST['id']);
            $response = $stmt->execute() ? "Subject deleted successfully." : "Error: " . $stmt->error;
        } else {
            $response = "Invalid ID.";
        }

    } elseif ($action === 'add') {
        // Add new subject
        $stmt = $conn->prepare("INSERT INTO subjects (code, name, description, teacher, schedule) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $_POST['code'], $_POST['name'], $_POST['description'], $_POST['teacher'], $_POST['schedule']);
        $response = $stmt->execute() ? "New subject added successfully." : "Error: " . $stmt->error;
    }

    echo $response;
    exit;
}

// Fetch subjects from the database
$sql = "SELECT * FROM subjects";
$result = $conn->query($sql);
if ($conn->error) {
    die("Error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Section - Admin Dashboard</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/modal.css">
    <link rel="stylesheet" href="../css/subject_section.css">
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
            <span>Subject Section</span>
            <span><a href="../index.php" class="logout">Logout</a></span>
        </div>
       
        <button id="addNewSubject" class="btn-add">Add New Subject</button>
        <input type="text" id="searchInput" placeholder="Filter by subject name..." />
        <div class="table-container">
            <table id="dataTable">
                <thead>
                    <tr>
						<th>Teacher</th>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th>Description</th>                 
                        <th>Schedule</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr data-id="<?= htmlspecialchars($row['id']) ?>">
								<td><?= htmlspecialchars($row['teacher']) ?></td>
                                <td><?= htmlspecialchars($row['code']) ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td><?= htmlspecialchars($row['schedule']) ?></td>
                                <td>
                                    <button class="btn btn-edit" onclick="editSubject(<?= $row['id'] ?>, '<?= htmlspecialchars($row['code']) ?>', '<?= htmlspecialchars($row['name']) ?>', '<?= htmlspecialchars($row['description']) ?>', '<?= htmlspecialchars($row['teacher']) ?>', '<?= htmlspecialchars($row['schedule']) ?>')">Edit</button>
                                    <button class="btn-delete" onclick="showDeleteConfirmation(<?= $row['id'] ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6">No subjects found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Edit Form Modal -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Edit Subject</h2>
                    <span class="close" id="closeEditModal">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="editSubjectForm">
                        <input type="hidden" id="editSubjectId" name="id" />
                        <label for="editCode">Code:</label>
                        <input type="text" id="editCode" name="code" required />
                        <label for="editName">Name:</label>
                        <input type="text" id="editName" name="name" required />
                        <label for="editDescription">Description:</label>
                        <input type="text" id="editDescription" name="description" required />
                        <label for="editTeacher">Teacher:</label>
                        <input type="text" id="editTeacher" name="teacher" required />
                        <label for="editSchedule">Schedule:</label>
                        <input type="text" id="editSchedule" name="schedule" required />
                        <button type="submit" class="btn btn-save">Save</button>
                        <button type="button" id="cancelEdit" class="btn btn-cancel">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Add Form Modal -->
        <div id="addForm" class="modal" style="display:none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Add New Subject</h2>
                    <span class="close" id="closeAddModal">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="addSubjectForm">
                        <label for="addCode">Code:</label>
                        <input type="text" id="addCode" name="code" required />
                        <label for="addName">Name:</label>
                        <input type="text" id="addName" name="name" required />
                        <label for="addDescription">Description:</label>
                        <input type="text" id="addDescription" name="description" required />
                        <label for="addTeacher">Teacher:</label>
                        <input type="text" id="addTeacher" name="teacher" required />
                        <label for="addSchedule">Schedule:</label>
                        <input type="text" id="addSchedule" name="schedule" required />
                        <button type="submit" class="btn btn-add">Add</button>
                        <button type="button" id="cancelAdd" class="btn btn-cancel">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        $(document).ready(function() {
            // Show Add Form
            $('#addNewSubject').click(function() {
                $('#addForm').show();
                $('#editModal').hide();
            });

            // Show Edit Modal
            $('#dataTable').on('click', '.btn-edit', function() {
                var row = $(this).closest('tr');
                var id = row.data('id');
                $('#editSubjectId').val(id);
                $('#editCode').val(row.find('td:eq(0)').text());
                $('#editName').val(row.find('td:eq(1)').text());
                $('#editDescription').val(row.find('td:eq(2)').text());
                $('#editTeacher').val(row.find('td:eq(3)').text());
                $('#editSchedule').val(row.find('td:eq(4)').text());
                $('#editModal').show();
                $('#addForm').hide();
            });

            // Close Modals
            $('.close, #cancelEdit, #cancelAdd').click(function() {
                $('#editModal, #addForm').hide();
            });

            // Handle Add Form Submission
            $('#addSubjectForm').submit(function(e) {
                e.preventDefault();
                $.post('subject_section.php', $(this).serialize() + '&action=add', function(response) {
                    alert(response);
                    location.reload();
                }).fail(function() {
                    alert('An error occurred.');
                });
            });

            // Handle Edit Form Submission
            $('#editSubjectForm').submit(function(e) {
                e.preventDefault();
                $.post('subject_section.php', $(this).serialize() + '&action=edit', function(response) {
                    alert(response);
                    location.reload();
                }).fail(function() {
                    alert('An error occurred.');
                });
            });

            // Handle Delete Action
            $('#dataTable').on('click', '.btn-delete', function() {
                if (confirm('Are you sure you want to delete this subject?')) {
                    var id = $(this).closest('tr').data('id');
                    console.log("ID to delete: " + id); // Debugging line
                    $.post('subject_section.php', { id: id, action: 'delete' }, function(response) {
                        alert(response);
                        location.reload();
                    }).fail(function() {
                        alert('An error occurred.');
                    });
                }
            });

            // Filter by subject name
            $('#searchInput').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('#dataTable tbody tr').filter(function() {
                    $(this).toggle($(this).find('td:eq(1)').text().toLowerCase().indexOf(value) > -1);
                });
            });
        });
    </script>
</body>
</html>
