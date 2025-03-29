<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['student_cin'])) {
    header("Location: ../auth/login.php"); // ✅ Fixed path to login
    exit();
}

$student_name = $_SESSION['student_name'];
?>

<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="css/sidebar.css"> <!-- ✅ Fixed CSS path -->
</head>

<div class="sidebar" id="sidebar">
    <!-- ✅ Profile Section -->
    <a href="profile.php" class="profile-link">
        <div class="profile">
            <img src="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png" 
                 alt="Profile Picture" class="profile-picture">
            <span class="username"><?php echo htmlspecialchars($student_name); ?></span>
        </div>
    </a>
    <hr>

    <!-- ✅ Navigation Section -->
    <nav id="nav-bar">
        <div class="sidebar-buttons">
            <a href="dashboard.php" class="sidebar-button">
                <i class="fa-solid fa-home"></i><span> Home</span>
            </a>
            <a href="apply-dorm.php" class="sidebar-button"> <!-- ✅ Changed from choose-room.php -->
                <i class="fa-solid fa-building"></i><span> Gestion des internats</span>
            </a>
            <a href="reserve-room.php" class="sidebar-button">
                <i class="fa-solid fa-door-open"></i><span> Gestion des chambres</span>
            </a>
            <a href="internat-requests.php" class="sidebar-button">
                <i class="fa-solid fa-file"></i><span> Demandes d'internat</span>
            </a>
            <a href="complaints.php" class="sidebar-button">
                <i class="fa-solid fa-exclamation-circle"></i><span> Réclamations</span>
            </a>
        </div>
    </nav>

    <!-- ✅ Logout Button -->
    <a href="../auth/logout.php" class="logout-button"> <!-- ✅ Fixed logout path -->
        <i class="fa-solid fa-right-from-bracket"></i><span> Logout</span>
    </a>
</div>

<!-- ✅ JS Link -->
<script src="js/sidebar.js"></script> <!-- ✅ Fixed JS path -->

<!-- ✅ Sidebar Toggle Button -->
<button class="toggle-btn" onclick="toggleSidebar()">☰</button>
