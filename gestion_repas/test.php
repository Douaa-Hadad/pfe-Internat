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
    <input type="file" id="qrInput" accept="image/*">
    <button onclick="scanQRCode()">Scanner le code QR</button>
    <div id="result"></div>

    <script>
        function scanQRCode() {
            const input = document.getElementById('qrInput');
            const resultDiv = document.getElementById('result');

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

                    if (qrCode) {
                        // Extraire les informations du QR code
                        const data = qrCode.data;
                        const parts = data.split('|');
                        const cin = parts[0].split(':')[1];
                        const repas = parts[1].split(':')[1];
                        const date = parts[2].split(':')[1];
                        const random = parts[3].split(':')[1];  // Ajout de la partie random

                        // Affichage pour tester
                        resultDiv.innerHTML = `
                            <h3>Informations extraites du QR Code :</h3>
                            <p>CIN: ${cin}</p>
                            <p>Repas: ${repas}</p>
                            <p>Date: ${date}</p>
                            <p>Random: ${random}</p>
                        `;

                        // Rediriger vers fetch_student.php avec les paramètres extraits du QR code
                        window.location.href = `fetch_student.php?cin=${cin}&repas=${repas}&date=${date}&random=${random}`;
                    } else {
                        resultDiv.innerHTML = '<p>Aucun QR code détecté.</p>';
                    }
                };
            };

            reader.readAsDataURL(file);
        }
    </script>
</body>
</html>
