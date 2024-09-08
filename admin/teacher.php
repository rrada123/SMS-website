<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

require '../db.php'; // Include database connection

// Function to generate a random time between 07:00:00 and 16:00:00
function generateRandomTime() {
    $start = new DateTime('07:00:00');
    $end = new DateTime('16:00:00');
    $interval = $start->diff($end);
    $randomInterval = mt_rand(0, $interval->h * 3600 + $interval->i * 60);
    $randomTime = clone $start;
    $randomTime->add(new DateInterval('PT' . $randomInterval . 'S'));
    return $randomTime->format('H:i:s');
}

// Function to generate a random time frame of 1-2 hours
function generateRandomSchedule() {
    $startTime = generateRandomTime();
    $startDateTime = new DateTime($startTime);
    $duration = mt_rand(1, 2); // Duration in hours
    $endDateTime = clone $startDateTime;
    $endDateTime->add(new DateInterval('PT' . $duration . 'H'));
    return $startDateTime->format('h:i A') . ' - ' . $endDateTime->format('h:i A');
}

// Update the schedule for all subjects
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_schedule'])) {
    $schedule = generateRandomSchedule();
    $stmt = $conn->prepare("UPDATE subjects SET schedule=?");
    $stmt->bind_param('s', $schedule);
    if ($stmt->execute()) {
        echo "Schedules updated successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
    exit;
}

// Handle edit action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'];
    $teacher = $_POST['teacher'];
    $name = $_POST['name'];
    $schedule = $_POST['schedule'];
    
    $stmt = $conn->prepare("UPDATE subjects SET teacher=?, name=?, schedule=? WHERE id=?");
    $stmt->bind_param('sssi', $teacher, $name, $schedule, $id);
    
    if ($stmt->execute()) {
        $response = [
            "status" => "success",
            "id" => $id,
            "teacher" => $teacher,
            "name" => $name,
            "schedule" => $schedule
        ];
        echo json_encode($response);
    } else {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }
    exit;
}

// Handle add action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $teacher = $_POST['teacher'];
    $name = $_POST['name'];
    $schedule = $_POST['schedule'];
    
    $stmt = $conn->prepare("INSERT INTO subjects (teacher, name, schedule) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $teacher, $name, $schedule);
    
    if ($stmt->execute()) {
        $id = $stmt->insert_id;
        $response = [
            "status" => "success",
            "id" => $id,
            "teacher" => $teacher,
            "name" => $name,
            "schedule" => $schedule
        ];
        echo json_encode($response);
    } else {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }
    exit;
}

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = $_POST['delete_id'];
    
    $stmt = $conn->prepare("DELETE FROM subjects WHERE id=?");
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        echo "Teacher deleted successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
    exit;
}

// Fetch teachers from the database
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
    <title>Teacher Section - Admin Dashboard</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/modal.css">
    <link rel="stylesheet" href="../css/teacher.css">
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
			<li><a href="Tuition.php">Tuition</a></li>
			<li><a href="add_student.php">Add Student</a></li>
        </ul>
    </div>
    <div class="content">
        <div class="header">
            <span>Teacher Section</span>
            <span><a href="../index.php" class="logout">Logout</a></span>
        </div>

        <button id="addNewTeacher" class="btn-add">Add New Teacher</button>
        <input type="text" id="searchInput" placeholder="Filter by teacher name..." />
        
        <div class="table-container">
            <table id="dataTable">
                <thead>
                    <tr>
                        <th>Teacher</th>
                        <th>Name</th>
                        <th>Schedule</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr data-id='" . htmlspecialchars($row['id']) . "'>";
                            echo "<td>" . htmlspecialchars($row['teacher'] ?? '') . "</td>";
                            echo "<td>" . htmlspecialchars($row['name'] ?? '') . "</td>";
                            echo "<td>" . htmlspecialchars($row['schedule'] ?? '') . "</td>";
                            echo '<td>
                                    <button class="btn btn-edit" onclick="editTeacher(' . $row['id'] . ', \'' . htmlspecialchars($row['teacher'] ?? '') . '\', \'' . htmlspecialchars($row['name'] ?? '') . '\', \'' . htmlspecialchars($row['schedule'] ?? '') . '\')">Edit</button>
                                    <button class="btn-delete" onclick="showDeleteConfirmation(' . $row['id'] . ')">Delete</button>
                                  </td>';
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No teachers found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <!-- Edit Form Modal -->
        <div id="editModal" class="modal" style="display:none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Edit Teacher</h2>
                    <span class="close" id="closeEditModal">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="editTeacherForm">
                        <input type="hidden" id="editTeacherId" name="id" />
                        <label for="editTeacher">Teacher:</label>
                        <input type="text" id="editTeacher" name="teacher" required />
                        <label for="editName">Name:</label>
                        <input type="text" id="editName" name="name" required />
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
                    <h2>Add New Teacher</h2>
                    <span class="close" id="closeAddModal">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="addTeacherForm">
                        <label for="addTeacher">Teacher:</label>
                        <input type="text" id="addTeacher" name="teacher" required />
                        <label for="addName">Name:</label>
                        <input type="text" id="addName" name="name" required />
                        <label for="addSchedule">Schedule:</label>
                        <input type="text" id="addSchedule" name="schedule" required />
                        <button type="submit" class="btn btn-save">Add</button>
                        <button type="button" id="cancelAdd" class="btn btn-cancel">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#addNewTeacher').click(function() {
                $('#addForm').show();
                $('#editModal').hide();
            });

            // Show edit modal
            window.editTeacher = function(id, teacher, name, schedule) {
                $('#editTeacherId').val(id);
                $('#editTeacher').val(teacher);
                $('#editName').val(name);
                $('#editSchedule').val(schedule);
                
                $('#editModal').show();
            };

            // Close edit modal
            $('#closeEditModal, #cancelEdit').click(function() {
                $('#editModal').hide();
            });

            // Handle edit form submission
            $('#editTeacherForm').submit(function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                $.post('teacher.php', formData + '&action=edit', function(response) {
                    var data = JSON.parse(response);
                    if (data.status === 'success') {
                        var row = $('tr[data-id="' + data.id + '"]');
                        row.find('td:eq(0)').text(data.teacher);
                        row.find('td:eq(1)').text(data.name);
                        row.find('td:eq(2)').text(data.schedule);
                        $('#editModal').hide();
                    } else {
                        alert(data.message);
                    }
                });
            });

            // Show delete confirmation
            window.showDeleteConfirmation = function(id) {
                if (confirm("Are you sure you want to delete this teacher?")) {
                    $.post('teacher.php', { delete_id: id }, function(response) {
                        alert(response);
                        location.reload();
                    });
                }
            };

            // Handle add form submission
            $('#addTeacherForm').submit(function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                $.post('teacher.php', formData + '&action=add', function(response) {
                    var data = JSON.parse(response);
                    if (data.status === 'success') {
                        var newRow = '<tr data-id="' + data.id + '">';
                        newRow += '<td>' + data.teacher + '</td>';
                        newRow += '<td>' + data.name + '</td>';
                        newRow += '<td>' + data.schedule + '</td>';
                        newRow += '<td>';
                        newRow += '<button class="btn btn-edit" onclick="editTeacher(' + data.id + ', \'' + data.teacher + '\', \'' + data.name + '\', \'' + data.schedule + '\')">Edit</button>';
                        newRow += '<button class="btn-delete" onclick="showDeleteConfirmation(' + data.id + ')">Delete</button>';
                        newRow += '</td>';
                        newRow += '</tr>';
                        $('#dataTable tbody').append(newRow);
                        $('#addForm').hide();
                    } else {
                        alert(data.message);
                    }
                });
            });

            // Close add form
            $('#closeAddModal, #cancelAdd').click(function() {
                $('#addForm').hide();
            });

            // Search filter
            $('#searchInput').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('#dataTable tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
        });
    </script>
</body>
</html>
