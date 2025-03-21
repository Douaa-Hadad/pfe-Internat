<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "estcasa");

/* Redirect to login page if no session exists
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login/login.php");
    exit();
}*/

$students = $mysqli->query("SELECT * FROM students");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        
        .students-table {
            width: 95%; /* Increase table width */
            max-width: 1100px; /* Adjust max-width */
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            overflow-x: auto;
        }

        .students-table h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #141460;
        }

        .add-student-btn {
            display: block;
            width: fit-content;
            margin: 0 auto 20px auto;
            padding: 10px 20px;
            background-color: #141460;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }

        .add-student-btn:hover {
            background-color: #0f0f5c;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 16px;
        }

        thead {
            background: #141460;
            color: white;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
        }

        .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .actions a, .actions button {
            text-decoration: none;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }

        .actions .edit {
            background-color: #4CAF50;
        }

        .actions .profile {
            background-color: #FF9800;
        }

        .actions .delete {
            background-color: #f44336;
        }

        .rounded-circle {
            border-radius: 50%;
        }

        .edit-profile-picture {
            width: 100px;
            height: 100px;
        }

        #profilePicture {
            width: 150px; 
            height: 150px; 
            border-radius: 50%; /* Makes it perfectly round */
            object-fit: cover; /* Ensures the image fills the area without distortion */
        }

        .main-content {
            margin-left: 0; /* Remove sidebar margin if needed */
            padding: 20px;
            width: 100%; /* Make it occupy full width */
        }

        body {
            margin-top: 0; /* Remove header's occupied space */
        }

        /* Ensure consistent spacing and size for action buttons */
        .actions .edit, .actions .profile, .actions .delete {
            padding: 5px 10px; /* Uniform padding */
            margin: 0; /* Remove any extra margins */
            width: auto; /* Ensure buttons adjust to content */
            display: inline-block; /* Ensure buttons are inline */
        }

        .actions {
            display: flex;
            justify-content: center;
            gap: 10px; /* Uniform gap between buttons */
        }

        .main-content {
    margin-top: 0 !important;
    padding-top: 0 !important;
}
    
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="students-table">
            <h2>Students List</h2>
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Search students by name or CIN...">
                <button type="button" id="searchButton"><i class="fa fa-search"></i></button>
            </div>
            <button type="button" class="add-student-btn" data-bs-toggle="modal" data-bs-target="#addStudentModal">Add New Student</button>
            <table id="studentsTable">
                <thead>
                    <tr>
                        <th>CIN</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Room ID</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($student = $students->fetch_assoc()): ?>
                    <tr>
                        <td><?= $student['cin'] ?></td>
                        <td><?= $student['name'] ?></td>
                        <td><?= $student['phone'] ?></td>
                        <td><?= ucfirst($student['gender']) ?></td>
                        <td><?= $student['room_id'] ?></td>
                        <td class="actions">
                            <button type="button" class="edit" data-bs-toggle="modal" data-bs-target="#editStudentModal" data-cin="<?= $student['cin'] ?>">Edit</button>
                            <button type="button" class="profile" data-bs-toggle="modal" data-bs-target="#profileStudentModal" data-cin="<?= $student['cin'] ?>">Profile</button>
                            <button type="button" class="delete" data-cin="<?= $student['cin'] ?>">Delete</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStudentModalLabel">Edit Student Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm" method="POST" action="update_student.php" enctype="multipart/form-data">
                        <input type="hidden" name="cin" id="studentCin">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name:</label>
                                <input type="text" class="form-control" name="name" id="studentName">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gender:</label>
                                <input type="text" class="form-control" name="gender" id="studentGender">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone:</label>
                                <input type="text" class="form-control" name="phone" id="studentPhone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email:</label>
                                <input type="email" class="form-control" name="email" id="studentEmail">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Major:</label>
                                <input type="text" class="form-control" name="major" id="studentMajor">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Year of Study:</label>
                                <input type="text" class="form-control" name="year_of_study" id="studentYearOfStudy">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Room ID:</label>
                                <input type="text" class="form-control" name="room_id" id="studentRoomId">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Profile Picture:</label><br>
                                <img id="studentProfilePicture" alt="Profile Picture" class="rounded-circle edit-profile-picture"><br>
                                <input type="file" class="form-control" name="profile_picture">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Receipt:</label><br>
                                <img id="studentPaymentReceipt" alt="Payment Receipt" width="150"><br>
                                <input type="file" class="form-control" name="payment_receipt">
                            </div>
                        </div>

                        <div class="d-flex justify-content-start" style="gap: 20px;">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
                            <button type="submit" id="saveButton" class="btn btn-success">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Student Modal -->
    <div class="modal fade" id="profileStudentModal" tabindex="-1" aria-labelledby="profileStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="profileStudentModalLabel">Student Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <img id="profilePicture" alt="Profile Picture" width="150" class="rounded-circle">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name:</label>
                            <p id="profileName"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gender:</label>
                            <p id="profileGender"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone:</label>
                            <p id="profilePhone"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email:</label>
                            <p id="profileEmail"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Major:</label>
                            <p id="profileMajor"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Year of Study:</label>
                            <p id="profileYearOfStudy"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Room ID:</label>
                            <p id="profileRoomId"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStudentModalLabel">Add New Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addForm" method="POST" action="add_student.php" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CIN:</label>
                                <input type="text" class="form-control" name="cin" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name:</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gender:</label>
                                <select class="form-select" name="gender" required>
                                    <option value="" disabled selected>Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone:</label>
                                <input type="text" class="form-control" name="phone" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email:</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Major:</label>
                                <select class="form-select" name="major" required>
                                    <option value="" disabled selected>Select Major</option>
                                    <option value="Genie Mecanique">Genie Mecanique</option>
                                    <option value="Genie Informatique">Genie Informatique</option>
                                    <option value="Genie Electrique">Genie Electrique</option>
                                    <option value="Genie des Procedes">Genie des Procedes</option>
                                    <option value="Finance et Comptabilité">Finance et Comptabilité</option>
                                    <option value="Business et Marketing">Business et Marketing</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Year of Study:</label>
                                <select class="form-select" name="year_of_study" required>
                                    <option value="" disabled selected>Select Year of Study</option>
                                    <option value="1st">1st</option>
                                    <option value="2nd">2nd</option>
                                    <option value="Licence">Licence</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Room ID:</label>
                                <input type="text" class="form-control" name="room_id" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Profile Picture:</label>
                                <input type="file" class="form-control" name="profile_picture" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Receipt:</label>
                                <input type="file" class="form-control" name="payment_receipt" required>
                            </div>
                        </div>
                        <div class="d-flex justify-content-start" style="gap: 20px;">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
                            <button type="submit" class="btn btn-success">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel">Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="messageModalBody">
                    <!-- Message will be inserted here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('hidden.bs.modal', function () {
        document.body.style.paddingTop = '0px';
        });

        document.addEventListener('DOMContentLoaded', function() {
            var editStudentModal = document.getElementById('editStudentModal');
            editStudentModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var cin = button.getAttribute('data-cin');

                fetch('get_student.php?cin=' + cin)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('studentCin').value = data.cin;
                        document.getElementById('studentName').value = data.name;
                        document.getElementById('studentGender').value = data.gender;
                        document.getElementById('studentPhone').value = data.phone;
                        document.getElementById('studentEmail').value = data.email;
                        document.getElementById('studentMajor').value = data.major;
                        document.getElementById('studentYearOfStudy').value = data.year_of_study;
                        document.getElementById('studentRoomId').value = data.room_id;
                        document.getElementById('studentProfilePicture').src = 'uploads/' + data.profile_picture;
                        document.getElementById('studentPaymentReceipt').src = 'uploads/' + data.payment_receipt;
                    })
                    .catch(error => console.error('Error fetching student data:', error));
            });

            var profileStudentModal = document.getElementById('profileStudentModal');
            profileStudentModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var cin = button.getAttribute('data-cin');

                fetch('get_student.php?cin=' + cin)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('profilePicture').src = 'uploads/' + data.profile_picture;
                        document.getElementById('profileName').innerText = data.name;
                        document.getElementById('profileGender').innerText = data.gender;
                        document.getElementById('profilePhone').innerText = data.phone;
                        document.getElementById('profileEmail').innerText = data.email;
                        document.getElementById('profileMajor').innerText = data.major;
                        document.getElementById('profileYearOfStudy').innerText = data.year_of_study;
                        document.getElementById('profileRoomId').innerText = data.room_id;
                    })
                    .catch(error => console.error('Error fetching student data:', error));
            });

            var editForm = document.getElementById('editForm');
            editForm.addEventListener('submit', function(event) {
                event.preventDefault();
                var formData = new FormData(editForm);

                fetch('update_student.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    document.getElementById('messageModalBody').innerText = data;
                    var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                    messageModal.show();
                })
                .catch(error => {
                    document.getElementById('messageModalBody').innerText = 'Error updating record: ' + error;
                    var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                    messageModal.show();
                });
            });

            // Handle Add Student form submission
            var addForm = document.getElementById('addForm');
            addForm.addEventListener('submit', function(event) {
                event.preventDefault();
                var formData = new FormData(addForm);

                fetch('add_student.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('messageModalBody').innerText = 'Student added successfully!';
                        addForm.reset(); // Reset the form after successful submission
                        var addStudentModal = bootstrap.Modal.getInstance(document.getElementById('addStudentModal'));
                        addStudentModal.hide(); // Close the Add Student modal
                        location.reload(); // Reload the page to update the students list
                    } else {
                        document.getElementById('messageModalBody').innerText = 'Error: ' + data.message;
                    }
                    var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                    messageModal.show(); // Display the message modal
                })
                .catch(error => {
                    document.getElementById('messageModalBody').innerText = 'Error adding student: ' + error;
                    var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                    messageModal.show(); // Display the message modal
                });
            });

            // Handle Delete button click
            document.querySelectorAll('.delete').forEach(button => {
                button.addEventListener('click', function() {
                    const cin = this.getAttribute('data-cin');
                    if (confirm('Are you sure you want to delete this student?')) {
                        fetch('delete_student.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ cin: cin })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Student deleted successfully!');
                                location.reload(); // Reload the page to update the students list
                            } else {
                                alert('Error: ' + data.message);
                            }
                        })
                        .catch(error => alert('Error deleting student: ' + error));
                    }
                });
            });

            const searchInput = document.getElementById('searchInput');
            const searchButton = document.getElementById('searchButton');
            const studentsTable = document.getElementById('studentsTable').getElementsByTagName('tbody')[0];

            searchButton.addEventListener('click', function () {
                const query = searchInput.value.toLowerCase();
                const rows = studentsTable.getElementsByTagName('tr');

                for (let i = 0; i < rows.length; i++) {
                    const cin = rows[i].getElementsByTagName('td')[0].textContent.toLowerCase();
                    const name = rows[i].getElementsByTagName('td')[1].textContent.toLowerCase();

                    if (cin.includes(query) || name.includes(query)) {
                        rows[i].style.display = '';
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
            });

            searchInput.addEventListener('keyup', function (event) {
                if (event.key === 'Enter') {
                    searchButton.click();
                }
            });
        });
    </script>
    
    <script src="script.js"></script>
</body>
</html>