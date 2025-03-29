<div class="sidebar" id="sidebar">
    <hr>
    <nav id="nav-bar">
        <div class="sidebar-buttons">
            <a href="index.php" class="sidebar-button"><i class="fa-solid fa-chart-line"></i><span> Dashboard</span></a>
            <a href="manage_payments.php" class="sidebar-button"><i class="fa-solid fa-money-check-alt"></i><span> Manage Payments</span></a>
            <a href="payment_reports.php" class="sidebar-button"><i class="fa-solid fa-file-invoice"></i><span> Payment Reports</span></a>
        </div>
    </nav>
    <a href="../login/logout.php" class="logout-button"><i class="fa-solid fa-sign-out-alt"></i><span> Logout</span></a>
</div>
<button class="toggle-btn" onclick="toggleSidebar()">☰</button>

<style>
    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        margin-top: 74px;
        height: 100vh;
        width: 60px;
        z-index: 1000;
        background-color: #1e1e6d;
        transition: width 0.3s ease;
        overflow: hidden; /* Prevent content overflow */
    }

    .sidebar.open {
        width: 250px;
    }

    .sidebar a {
        display: flex;
        align-items: center;
        padding: 10px;
        margin: 17px 0;
        color: white;
        cursor: pointer;
        font-size: 15px;
        white-space: nowrap;
        text-decoration: none;
        transition: color 0.3s, padding 0.3s;
    }

    .sidebar a i {
        font-size: 20px;
        margin-right: 16px;
        transition: margin-right 0.3s;
    }

    .sidebar:not(.open) a {
        justify-content: center;
        padding: 10px 0; /* Adjust padding for collapsed state */
    }

    .sidebar:not(.open) a span {
        display: none; /* Hide text when collapsed */
    }

    .sidebar a:hover {
        color: #1e6db0;
    }

    .toggle-btn {
        position: fixed;
        left: 10px;
        top: 10px;
        background: #14146050;
        color: white;
        padding: 10px;
        border: none;
        cursor: pointer;
        font-size: 20px;
        border-radius: 5px;
        transition: background 0.3s;
        z-index: 1001;
    }

    .toggle-btn:hover {
        background: #1e1e6d;
    }

    .logout-button {
        display: flex;
        align-items: center;
        width: 100%;
        color: white;
        cursor: pointer;
        font-size: 15px;
        white-space: nowrap;
        text-decoration: none;
        transition: color 0.3s;
        position: absolute;
        bottom: 80px;
        left: 0;
        justify-content: center;
    }

    .logout-button i {
        font-size: 20px;
        margin-right: 10px;
    }

    .sidebar:not(.open) .logout-button span {
        display: none;
    }

    .sidebar:not(.open) .logout-button {
        justify-content: center;
    }
</style>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
    }
</script>
