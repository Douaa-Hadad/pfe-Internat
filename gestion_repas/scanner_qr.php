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

                        // Envoyer la requête AJAX pour récupérer les informations de l'étudiant
                        fetch(`fetch_student.php?cin=${cin}&repas=${repas}&date=${date}`)
                            .then(response => response.json())
                            .then(student => {
                                if (student.success) {
                                    resultDiv.innerHTML = `
                                        <p>CIN: ${cin}</p>
                                        <p>Nom & Prénom: ${student.nom} ${student.prenom}</p>
                                        <p>Repas: ${student.repas}</p>
                                        <p><img src="${student.image}" alt="Image de l'étudiant" width="100"></p>
                                        // <p>QR Code: ${data}</p>
                                    `;
                                } else {
                                    resultDiv.innerHTML = `<p>${student.message}</p>`;
                                }
                            });
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



