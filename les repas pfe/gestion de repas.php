<?php
session_start();
include_once('../db_connect.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Traitement de la réservation
if (isset($_POST['reserve_meal'])) {
    $meal_date = htmlspecialchars($_POST['meal_date']);
    $breakfast = isset($_POST['breakfast']) ? 1 : 0;
    $lunch = isset($_POST['lunch']) ? 1 : 0;
    $dinner = isset($_POST['dinner']) ? 1 : 0;

    $sql = "INSERT INTO meal_reservations (user_id, meal_date, breakfast, lunch, dinner) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isiii", $user_id, $meal_date, $breakfast, $lunch, $dinner);

    if ($stmt->execute()) {
        $success = "Réservation réussie.";
    } else {
        $error = "Erreur lors de la réservation.";
    }
}
?>

<?php include('../includes/header.php'); ?>

<div class="container">
    <h1>Réserver vos repas</h1>
    <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <label>Date :</label>
        <input type="date" name="meal_date" required><br>

        <label><input type="checkbox" name="breakfast"> Petit-déjeuner</label><br>
        <label><input type="checkbox" name="lunch"> Déjeuner</label><br>
        <label><input type="checkbox" name="dinner"> Dîner</label><br>

        <button type="submit" name="reserve_meal">Réserver</button>
    </form>

    <h2>Mes réservations</h2>
    <table>
        <tr>
            <th>Date</th>
            <th>Petit-déjeuner</th>
            <th>Déjeuner</th>
            <th>Dîner</th>
        </tr>
        <?php
        $sql = "SELECT * FROM meal_reservations WHERE user_id = ? ORDER BY meal_date DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['meal_date']}</td>";
            echo "<td>" . ($row['breakfast'] ? 'Oui' : 'Non') . "</td>";
            echo "<td>" . ($row['lunch'] ? 'Oui' : 'Non') . "</td>";
            echo "<td>" . ($row['dinner'] ? 'Oui' : 'Non') . "</td>";
            echo "</tr>";
        }
        ?>
    </table>
</div>

<?php include('../includes/footer.php'); ?>