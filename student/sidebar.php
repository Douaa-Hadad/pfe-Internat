<?php
session_start();
if (!isset($_SESSION['student_cin'])) {
    header("Location: ../login.php");
    exit();
}

$student_name = $_SESSION['student_name'];
?>

<div class="sidebar" id="sidebar">
    <a href="profile.php" class="profile-link">
        <div class="profile">
            <img src="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png" alt="Profile Picture" class="profile-picture">
            <span class="username"><?php echo $student_name; ?></span>
        </div>
    </a>
    <hr>
    <nav id="nav-bar">
        <div class="sidebar-buttons">
            <a href="dashboard.php" class="sidebar-button"><i class="fa-solid fa-home"></i><span> Home</span></a>
            <a href="choose-room.php" class="sidebar-button"><i class="fa-solid fa-building"></i><span> Gestion des internats</span></a>
            <a href="reserve_room.php" class="sidebar-button"><i class="fa-solid fa-door-open"></i><span> Gestion des chambres</span></a>
            <a href="internat-requests.php" class="sidebar-button"><i class="fa-solid fa-file"></i><span> Demandes d'internat</span></a>
            <a href="complaints.php" class="sidebar-button"><i class="fa-solid fa-exclamation-circle"></i><span> Réclamations</span></a>
         </div>
    </nav>
    <a href="../logout.php" class="logout-button"><i class="fa-solid fa-right-from-bracket"></i><span> Logout</span></a>
</div>
<button class="toggle-btn" onclick="toggleSidebar()">☰</button>
