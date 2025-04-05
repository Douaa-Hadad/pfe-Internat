<?php
if (!isset($student_name)) {
    $student_name = isset($_SESSION['student_name']) ? $_SESSION['student_name'] : 'Student';
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['student_cin'])) {
    header("Location: ../login/login.php"); // ✅ Fixed path to login
    exit();
}
?>
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            background: #f0f2f5;
        }

        /* ================================
           Sidebar Styling
        ================================ */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            height: 100%;
            background-color: #1b6ca8;
            padding: 20px;
            transition: width 0.3s ease;
            z-index: 1000;
            color: white;
        }

        .sidebar .profile {
            display: flex;
            align-items: center;
            margin-top: 40px;
            margin-bottom: 30px;
            flex-direction: column;
        }

        .profile .profile-picture {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
            max-width: 100%;
            max-height: 100%;
        }

        .profile .username {
            font-size: 18px;
            font-weight: bold;
        }

        /* ================================
           Sidebar Buttons
        ================================ */
        .sidebar-buttons {
            margin-top: 20px;
        }

        .sidebar-button {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            margin: 10px 10px;
            color: white;
            cursor: pointer;
            font-size: 17px;
            white-space: nowrap;
            text-decoration: none;
            transition: color 0.3s;
        }

        .sidebar-button i {
            font-size: 20px;
            margin-right: 15px;
            margin-top: 9px;
        }

        .sidebar-button:hover,
        .sidebar-button.active {
            background-color: #155a8a;
        }

        /* ================================
           Logout Button
        ================================ */
        .logout-button {
            display: flex;
            align-items: center;
            width: 100%;
            color: white;
            cursor: pointer;
            font-size: 17px;
            white-space: nowrap;
            text-decoration: none;
            transition: color 0.3s;
            position: absolute;
            bottom: 90px;
            left: 0;
            justify-content: center;
        }

        .logout-button i {
            margin-right: 10px;
        }

        .logout-button:hover {
            background-color: #c9302c;
        }

        /* ================================
           Toggle Button
        ================================ */
        .toggle-btn {
            font-size: 22px;
            position: fixed;
            left: 10px;
            top: 10px;
            background-color: #1b6ca8;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1001;
            margin-left: 15px;
            margin-bottom: 5px;
        }

        .toggle-btn:hover {
            background-color: #155a8a;
        }

        /* ================================
           Header Bar Styling
        ================================ */
        .header-bar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            background-color: #fff;
            padding: 5px 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 999;
        }

        .header-bar .notification-bell {
            position: relative;
            margin-right: 20px;
            cursor: pointer;
        }

        .header-bar .notification-bell .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: red;
            color: white;
            font-size: 10px;
            padding: 3px 6px;
            border-radius: 50%;
        }

        .header-bar .profile-photo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
        }

        /* ================================
>>>>>>> 921f3a6ffbbe4b1e5517401fdb074f65c08b4b2b
           Responsive Behavior
        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
                overflow: hidden;
            }

            .sidebar-button span,
            .logout-button span {
                display: none;
            }

            .sidebar .profile .username {
                display: none;
            }
        }

        @media (min-width: 769px) {
            .sidebar {
                width: 240px;
            }
        }

        /* ================================
           Closed Sidebar Styling
        ================================ */
        .sidebar.closed {
            width: 60px;
            transition: width 0.3s ease;
        }

        .sidebar.closed .profile .username {
            display: none;
        }

        .sidebar.closed .profile .profile-picture {
            display: none;
        }

        .sidebar.closed .sidebar-button span {
            display: none;
        }

        .sidebar.closed .sidebar-button {
            justify-content: center;
        }

        .sidebar.closed .logout-button span {
            display: none;
        }

        .sidebar.closed .logout-button {
            justify-content: center;
        }
    </style>
</head>

<div class="sidebar closed" id="sidebar">
    <!-- Profile Section -->
    <a href="userprofile.php" class="profile-link">
        <div class="profile">
            <img src="<?php echo isset($_SESSION['profile_picture']) && !empty($_SESSION['profile_picture']) 
                ? '../uploads/' . htmlspecialchars($_SESSION['profile_picture']) 
                : 'https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png'; ?>" 
                alt="Profile Picture" class="profile-picture">
            <div class="username"><?php echo htmlspecialchars($student_name); ?></div>
        </div>
    </a>
    <hr>

    <!-- Navigation Section -->
    <nav id="nav-bar">
        <div class="sidebar-buttons">
            <a href="dashboard.php" class="sidebar-button"><i class="fa-solid fa-home"></i><span> Home</span></a>
            <a href="apply_dorm.php" class="sidebar-button"><i class="fa-solid fa-building"></i><span>demande d'internat</span></a>
            <a href="reserve_room.php" class="sidebar-button"><i class="fa-solid fa-door-open"></i><span>choix de chambre</span></a>
            <a href="etudiant_repas.php" class="sidebar-button"><i class="fa-solid fa-utensils"></i><span>service de restauration</span></a>
            <a href="complains.php" class="sidebar-button"><i class="fa-solid fa-face-angry"></i><span> Réclamations</span></a>
        </div>
    </nav>

    <!-- Logout Button -->
    <a href="../login/logout.php" class="logout-button"><i class="fa-solid fa-right-from-bracket"></i><span> Logout</span></a>
</div>

<script src="js/sidebar.js"></script>

<!-- Sidebar Toggle Button -->
<button class="toggle-btn" onclick="toggleSidebar()">☰</button>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('closed');
    }
</script>
