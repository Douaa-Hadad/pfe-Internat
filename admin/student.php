<?php
session_start();
include '../db.php'; 
/*
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}
*/
// Fetch students from the database
$sql = "SELECT * FROM students";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . htmlspecialchars($conn->error));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .students-table {
            width: 90%;
            max-width: 1000px; /* Adjusted max-width */
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

        .search-bar {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
        }

        .search-bar input {
            width: 70%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .search-bar button {
            padding: 10px 15px;
            background-color: #141460;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 5px;
        }

        .search-bar button:hover {
            background-color: #0f0f5c;
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
            vertical-align: middle; /* Add this line */
        }

        .actions {
            display: flex;
            justify-content: center; /* Add this line */
            gap: 10px;
        }

        .actions a {
            text-decoration: none;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .actions .edit {
            background-color: #4CAF50;
        }

        .actions .assign {
            background-color: #2196F3;
        }

        .actions .profile {
            background-color: #FF9800;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="students-table">
            <h2>Student Management</h2>
            <div class="search-bar">
                <input type="text" placeholder="Search students...">
                <button type="button"><i class="fa fa-search"></i></button>
            </div>
            <a href="add_student.php" class="add-student-btn">Add New Student</a>
            <table>
                <thead>
                    <tr>
                        <th>CIN</th>
                        <th>Name</th>
                        <th>Gender</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['cin']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['gender']); ?></td>
                            <td class="actions">
                                <a href="edit_student.php?cin=<?php echo htmlspecialchars($row['cin']); ?>" class="edit">Edit</a>
                                <a href="assign_room.php?cin=<?php echo htmlspecialchars($row['cin']); ?>" class="assign">Assign Room</a>
                                <a href="student_profile.php?cin=<?php echo htmlspecialchars($row['cin']); ?>" class="profile">Profile</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>