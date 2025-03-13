<?php
session_start();
include '../db.php'; 
/*
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}
*/
// Fetch search query if available
$search_query = isset($_GET['search']) ? trim($_GET['search']) : "";

// Prepare SQL query for student search
if ($search_query) {
    $total_students_query = "SELECT * FROM students WHERE cin LIKE ? OR name LIKE ?";
    $stmt = $conn->prepare($total_students_query);
    $search_term = "%$search_query%";
    $stmt->bind_param("ss", $search_term, $search_term);
    $stmt->execute();
    $total_students = $stmt->get_result();
} else {
    $total_students_query = "SELECT * FROM students";
    $total_students = $conn->query($total_students_query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion d'internat</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .students-table {
            width: 90%;
            max-width: 800px;
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
        }

        td img {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid #141460;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="students-table">
            <h2>Students List</h2>

            <!-- Search Bar -->
            <form method="GET" action="" class="search-bar">
                <input type="text" name="search" placeholder="Search by CIN or Name" 
                       value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit"><i class="fa fa-search"></i></button>
            </form>

            <table border="1">
                <thead>
                    <tr>
                        <th>CIN</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Photo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($student = $total_students->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['cin']); ?></td>
                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td><?php echo htmlspecialchars($student['phone']); ?></td>
                        <td><?php echo htmlspecialchars($student['gender']); ?></td>
                        <td>
                            <img src="<?php echo htmlspecialchars($student['photo']); ?>" 
                                 alt="Student Photo">
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
