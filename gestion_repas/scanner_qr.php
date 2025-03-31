<?php

session_start();
include '../connection.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_role'] !== 'Responsable_Scan') {
    header("Location: ../login/login.php");
    exit();
}

// Fetch available meal types
$query_repas = "SELECT * FROM repas";
$result_repas = mysqli_query($conn, $query_repas);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Scanner QR Code</title>
    <!-- Inclure la bibliothèque jsQR depuis le dossier local -->
    <script src="lib/jsQR.js"></script>
</head>
<body>
    <h1>Scanner QR Code</h1>
    <label for="mealType">Sélectionnez le type de repas :</label>
    <select id="mealType">
        <option value="">-- Sélectionnez un repas --</option>
        <?php while ($row_repas = mysqli_fetch_assoc($result_repas)) : ?>
            <option value="<?php echo $row_repas['id_repas']; ?>"><?php echo $row_repas['type_repas']; ?></option>
        <?php endwhile; ?>
    </select>
    <br><br>
    <input type="file" id="qrInput" accept="image/*">
    <button onclick="scanQRCode()">Scanner le code QR</button>
    <div id="result"></div>

    <script>
        function scanQRCode() {
            const input = document.getElementById('qrInput');
            const resultDiv = document.getElementById('result');
            const mealType = document.getElementById('mealType').value;
            const currentDate = new Date().toISOString().split('T')[0]; // Get current date in YYYY-MM-DD format

            if (!mealType) {
                alert('Veuillez sélectionner un type de repas.');
                return;
            }

            if (input.files.length === 0) {
                alert('Veuillez sélectionner une image QR.');
                return;
            }

            const file = input.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                const img = new Image();
                img.src = e.target.result;

                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    canvas.width = img.width;
                    canvas.height = img.height;
                    const context = canvas.getContext('2d');
                    context.drawImage(img, 0, 0);
                    const imageData = context.getImageData(0, 0, canvas.width, canvas.height);

                    const qrCode = jsQR(imageData.data, canvas.width, canvas.height);

                    if (qrCode && qrCode.data) { // Ensure qrCode.data is defined
                        try {
                            const data = qrCode.data;
                            const parts = data.split('|');
                            const cin = parts[0]?.split(':')[1]?.trim();
                            const repas = parts[1]?.split(':')[1]?.trim();
                            const date = parts[2]?.split(':')[1]?.trim();

                            if (!cin || !repas || !date) {
                                throw new Error('Format de QR code invalide.');
                            }

                            if (repas !== mealType) {
                                resultDiv.innerHTML = `<p style="color: red;">Erreur : Le type de repas scanné ne correspond pas au type sélectionné.</p>`;
                                return;
                            }

                            if (date !== currentDate) {
                                resultDiv.innerHTML = `<p style="color: red;">Erreur : La date du QR code (${date}) ne correspond pas à la date actuelle (${currentDate}).</p>`;
                                return;
                            }

                            fetch(`fetch_student.php?cin=${cin}&repas=${repas}&date=${date}`)
                                .then(response => response.json())
                                .then(student => {
                                    if (student.success) {
                                        // Check if the ticket is valid
                                        fetch(`update_ticket_status.php?cin=${cin}&repas=${repas}&date=${date}`)
                                            .then(response => response.json())
                                            .then(updateResponse => {
                                                if (updateResponse.success) {
                                                    resultDiv.innerHTML = `
                                                        <p>CIN: ${student.cin}</p>
                                                        <p>Nom: ${student.nom}</p>
                                                        <p>Repas: ${student.repas}</p>
                                                        <p>Date: ${student.date}</p>
                                                        <p><img src="${student.profile_picture}" alt="Image de l'étudiant" width="100"></p>
                                                        <p style="color: green;">Statut: ${updateResponse.message}</p>
                                                    `;
                                                } else {
                                                    resultDiv.innerHTML = `<p style="color: red;">${updateResponse.message}</p>`;
                                                }
                                            })
                                            .catch(err => {
                                                resultDiv.innerHTML = `<p>Erreur lors de la mise à jour du statut : ${err.message}</p>`;
                                            });
                                    } else {
                                        resultDiv.innerHTML = `<p>${student.message}</p>`;
                                    }
                                })
                                .catch(err => {
                                    resultDiv.innerHTML = `<p>Erreur lors de la récupération des données : ${err.message}</p>`;
                                });
                        } catch (err) {
                            resultDiv.innerHTML = `<p>Erreur : ${err.message}</p>`;
                        }
                    } else {
                        resultDiv.innerHTML = '<p>Aucun QR code détecté ou données invalides.</p>';
                    }
                };
            };

            reader.readAsDataURL(file);
        }
    </script>
</body>
</html>



