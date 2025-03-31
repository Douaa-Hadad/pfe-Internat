<?php
session_start();
include '../db.php';

/*Redirect to login page if no session exists or user is not admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login/login.php");
    exit();
}
*/
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

// Fetch dorm occupancy data
$dorms_query = "
    SELECT d.name, COUNT(r.room_number) AS total_rooms, SUM(r.occupied_slots) AS occupied_slots
    FROM dorms d
    LEFT JOIN rooms r ON d.id = r.dorm_id
    GROUP BY d.id, d.name";
$dorms_result = $conn->query($dorms_query);
$dorms_data = [];
while ($row = $dorms_result->fetch_assoc()) {
    $dorms_data[] = $row;
}

// Fetch total students count
$total_students_count_query = "SELECT COUNT(*) AS total_students FROM students";
$total_students_count_result = $conn->query($total_students_count_query);
$total_students_count = $total_students_count_result->fetch_assoc()['total_students'];

// Fetch total male students count
$total_male_students_query = "SELECT COUNT(*) AS total_male_students FROM students WHERE gender = 'Male'";
$total_male_students_result = $conn->query($total_male_students_query);
$total_male_students = $total_male_students_result->fetch_assoc()['total_male_students'];

// Fetch total female students count
$total_female_students_query = "SELECT COUNT(*) AS total_female_students FROM students WHERE gender = 'Female'";
$total_female_students_result = $conn->query($total_female_students_query);
$total_female_students = $total_female_students_result->fetch_assoc()['total_female_students'];

// Fetch total male places (capacity of rooms assigned to males)
$total_male_places_query = "
    SELECT SUM(r.capacity) AS total_male_places 
    FROM rooms r 
    JOIN dorms d ON r.dorm_id = d.id 
    WHERE d.gender = 'Male'";
$total_male_places_result = $conn->query($total_male_places_query);
$total_male_places = $total_male_places_result->fetch_assoc()['total_male_places'];

// Fetch total female places (capacity of rooms assigned to females)
$total_female_places_query = "
    SELECT SUM(r.capacity) AS total_female_places 
    FROM rooms r 
    JOIN dorms d ON r.dorm_id = d.id 
    WHERE d.gender = 'Female'";
$total_female_places_result = $conn->query($total_female_places_query);
$total_female_places = $total_female_places_result->fetch_assoc()['total_female_places'];

// Calculate overall occupancy rate
$total_occupied_slots_query = "SELECT SUM(occupied_slots) AS total_occupied FROM rooms";
$total_occupied_slots_result = $conn->query($total_occupied_slots_query);
$total_occupied_slots = $total_occupied_slots_result->fetch_assoc()['total_occupied'];

$total_slots_query = "SELECT SUM(capacity) AS total_capacity FROM rooms";
$total_slots_result = $conn->query($total_slots_query);
$total_slots = $total_slots_result->fetch_assoc()['total_capacity'];

$occupancy_rate = $total_slots > 0 ? round(($total_occupied_slots / $total_slots) * 100, 2) : 0;

// Fetch recent activities (example: last 5 student registrations)
$recent_activities_query = "SELECT name, cin, created_at FROM students ORDER BY created_at DESC LIMIT 5";
$recent_activities_result = $conn->query($recent_activities_query);
$recent_activities = [];
while ($row = $recent_activities_result->fetch_assoc()) {
    $recent_activities[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Add this line -->
    <style>

        /* General Styles */

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        /* Overview Section */
        .overview {
            display: flex;
            justify-content: space-around;
            width: 100%;
            max-width: 800px;
            margin-bottom: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .overview div {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #555;
        }
        /* Recent Activities Section */
        .recent-activities {
            width: 100%;
            max-width: 800px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }

        .recent-activities ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .recent-activities li {
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
            font-size: 16px;
            color: #555;
        }

        .recent-activities li:last-child {
            border-bottom: none;
        }

        /* Chart Section */
        .chart-container {
            width: 100%;
            max-width: 800px;
            margin: auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        /* Recent Activities Table Styles */
        .recent-activities table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .recent-activities th, .recent-activities td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .recent-activities th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        .recent-activities tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .recent-activities tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <!-- Overview Section -->
        <h2>Dashboard Overview</h2>
        <div class="overview">
            <div>Total Male Students: <?php echo $total_male_students . " / " . $total_male_places; ?></div>
            <div>Total Female Students: <?php echo $total_female_students . " / " . $total_female_places; ?></div>
            <div>Occupancy Rate: <?php echo $occupancy_rate; ?>%</div>
        </div>

        <!-- Chart Section -->
        <h2>Dorm Occupancy</h2>
        <div class="chart-container">
            <canvas id="dormOccupancyChart" width="800" height="400"></canvas>
        </div>

        <!-- Recent Activities Section -->
        <h2>Recent Activities</h2>
        <div class="recent-activities">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>CIN</th>
                        <th>Registration Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_activities as $activity): ?>
                        <tr>
                            <td><?php echo $activity['name']; ?></td>
                            <td><?php echo $activity['cin']; ?></td>
                            <td><?php echo $activity['created_at']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                const ctx = document.getElementById('dormOccupancyChart').getContext('2d');
                const dormsData = <?php echo json_encode($dorms_data); ?>;
                const labels = dormsData.map(dorm => dorm.name);
                const totalRooms = dormsData.map(dorm => dorm.total_rooms);
                const occupiedSlots = dormsData.map(dorm => dorm.occupied_slots);

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Total Rooms',
                                data: totalRooms,
                                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Occupied Slots',
                                data: occupiedSlots,
                                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                                borderColor: 'rgba(255, 99, 132, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: false, 
                        maintainAspectRatio: false, 
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }, 100); // Small delay to ensure layout is fully applied
        });
    </script>
</body>
</html>
