<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<style>
    .header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: #141460;
        color: white;
        padding: 19px 20px; /* Increased padding to make the header 8px taller */
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        position: fixed; /* Makes the header fixed */
        top: 0; /* Pins it to the top */
        left: 0; /* Ensures it starts from the left edge */
        width: 100%; /* Ensures it spans the full width */
        z-index: 1000; /* Ensures it stays above other content */
    }

    .profile-link {
        display: flex;
        align-items: center;
        text-decoration: none;
        color: white;
        position: absolute;
        right: 20px; /* Moves the profile to the right */
        top: 15px; /* Adjusts the vertical position */
    }

    .profile {
        display: flex;
        align-items: center;
        margin-right: 28px;
    }

    .profile-picture {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-right: 10px;
        border: 2px solid white;
    }

    .username {
        font-size: 16px;
    }

    .header-title {
        flex-grow: 1;
        text-align: center;
        font-size: 20px;
        font-weight: bold;
    }

    /* Optional: Add padding to the body to prevent content from being hidden behind the fixed header */
    body {
        padding-top: 78px; /* Adjusted to match the new header height */
    }
</style>

<header class="header">
    <h1 class="header-title">Gestion d'internat</h1>
    <a href="../adminprofile.php" class="profile-link">
        <div class="profile">
            <img src="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png" 
                 alt="Profile Picture" class="profile-picture">
            <span class="username">
                <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
            </span>
        </div>
    </a>
</header>