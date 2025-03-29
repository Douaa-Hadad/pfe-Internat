<?php
<<<<<<< HEAD
if (!isset($student_name)) {
    $student_name = isset($_SESSION['student_name']) ? $_SESSION['student_name'] : 'Student';
=======
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['student_cin'])) {
    header("Location: ../auth/login.php"); // ✅ Fixed path to login
    exit();
>>>>>>> 882eca008fec5519a5920fa8025c89817c86b57f
}
?>
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="css/sidebar.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>

<div class="sidebar" id="sidebar">
    <!-- Profile Section -->
    <a href="profile.php" class="profile-link">
        <div class="profile">
            <img src="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png" 
                 alt="Profile Picture" class="profile-picture">
            <span class="username"><?php echo htmlspecialchars($student_name); ?></span>
        </div>
    </a>
    <hr>

    <!-- Navigation Section -->
    <nav id="nav-bar">
        <div class="sidebar-buttons">
            <a href="dashboard.php" class="sidebar-button"><i class="fa-solid fa-home"></i><span> Home</span></a>
            <a href="edit_info.php" class="sidebar-button"><i class="fa-solid fa-user-pen"></i><span>editer mes info</span></a>
            <a href="apply_dorm.php" class="sidebar-button"><i class="fa-solid fa-building"></i><span>demande d'internat</span></a>
            <a href="reserve_room.php" class="sidebar-button"><i class="fa-solid fa-door-open"></i><span>choix de chambre</span></a>
            <a href="complaints.php" class="sidebar-button"><i class="fa-solid fa-face-angry"></i><span> Réclamations</span></a>
        </div>
    </nav>

    <!-- Logout Button -->
    <a href="../auth/logout.php" class="logout-button"><i class="fa-solid fa-right-from-bracket"></i><span> Logout</span></a>
</div>

<script src="js/sidebar.js"></script> <

<!-- Sidebar Toggle Button -->
<button class="toggle-btn" onclick="toggleSidebar()">☰</button>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('closed');
    }
</script>
