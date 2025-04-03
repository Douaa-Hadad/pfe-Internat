<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "estcasa");

// Rediriger vers la page de connexion si l'utilisateur n'est pas administrateur
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login/login.php");
    exit();
}

// Récupérer tous les étudiants
$students = $mysqli->query("SELECT * FROM students");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">
    <style>
        
        .students-table {
            width: 95%; /* Augmenter la largeur de la table */
            max-width: 1100px; /* Ajuster la largeur maximale */
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            overflow-x: auto;
        }

        .students-table h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #141460;
        }

        .add-student-btn {
            display: block;
            width: fit-content;
            margin: 0 auto 20px auto;
            padding: 10px 20px;
            background-color: #141460;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }

        .add-student-btn:hover {
            background-color: #0f0f5c;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 16px;
        }

        thead {
            background: #141460;
            color: white;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
        }

        .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .actions a, .actions button {
            text-decoration: none;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }

        .actions .edit {
            background-color: #4CAF50;
        }

        .actions .profile {
            background-color: #FF9800;
        }

        .actions .delete {
            background-color: #f44336;
        }

        .rounded-circle {
            border-radius: 50%;
        }

        .edit-profile-picture {
            width: 100px;
            height: 100px;
        }

        #profilePicture {
            width: 150px; 
            height: 150px; 
            border-radius: 50%; /* Le rend parfaitement rond */
            object-fit: cover; /* Assure que l'image remplit la zone sans distorsion */
        }

        .main-content {
            margin-left: 0; /* Supprimer la marge de la barre latérale si nécessaire */
            padding: 20px;
            width: 100%; /* Faire en sorte qu'il occupe toute la largeur */
        }

        body {
            margin-top: 0; /* Supprimer l'espace occupé par l'en-tête */
        }

        /* Assurer un espacement et une taille cohérents pour les boutons d'action */
        .actions .edit, .actions .profile, .actions .delete {
            padding: 5px 10px; /* Rembourrage uniforme */
            margin: 0; /* Supprimer les marges supplémentaires */
            width: auto; /* Assurer que les boutons s'ajustent au contenu */
            display: inline-block; /* Assurer que les boutons sont en ligne */
        }

        .actions {
            display: flex;
            justify-content: center;
            gap: 10px; /* Espace uniforme entre les boutons */
        }

    
    </style>
</head>
<body>
    <?php include '../header.php'; ?>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="students-table">
            <h2>Liste des Étudiants</h2>
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Rechercher des étudiants par nom ou CIN...">
                <button type="button" id="searchButton"><i class="fa fa-search"></i></button>
            </div>
            <button type="button" class="add-student-btn" data-bs-toggle="modal" data-bs-target="#addStudentModal">Ajouter un Nouvel Étudiant</button>
            <table id="studentsTable">
                <thead>
                    <tr>
                        <th>CIN</th>
                        <th>Nom</th>
                        <th>Téléphone</th>
                        <th>Genre</th>
                        <th>ID Chambre</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($student = $students->fetch_assoc()): ?>
                    <tr>
                        <td><?= $student['cin'] ?></td>
                        <td><?= $student['name'] ?></td>
                        <td><?= $student['phone'] ?></td>
                        <td><?= ucfirst($student['gender']) ?></td>
                        <td><?= $student['room_id'] ?></td>
                        <td class="actions">
                            <button type="button" class="edit" data-bs-toggle="modal" data-bs-target="#editStudentModal" data-cin="<?= $student['cin'] ?>">Modifier</button>
                            <button type="button" class="profile" data-bs-toggle="modal" data-bs-target="#profileStudentModal" data-cin="<?= $student['cin'] ?>">Profil</button>
                            <button type="button" class="delete" data-cin="<?= $student['cin'] ?>">Supprimer</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modale Modifier Étudiant -->
    <div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStudentModalLabel">Modifier les Informations de l'Étudiant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm" method="POST" action="update_student.php" enctype="multipart/form-data">
                        <input type="hidden" name="cin" id="studentCin">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nom:</label>
                                <input type="text" class="form-control" name="name" id="studentName">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">sexe:</label>
                                <input type="text" class="form-control" name="gender" id="studentGender">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Téléphone:</label>
                                <input type="text" class="form-control" name="phone" id="studentPhone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email:</label>
                                <input type="email" class="form-control" name="email" id="studentEmail">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Filière:</label>
                                <select class="form-select" name="major" id="studentMajor" required>
                                    <option value="" disabled selected>Sélectionner la Filière</option>
                                    <option value="Genie Mecanique">Genie Mecanique</option>
                                    <option value="Genie Informatique">Genie Informatique</option>
                                    <option value="Genie Electrique">Genie Electrique</option>
                                    <option value="Genie des Procedes">Genie des Procedes</option>
                                    <option value="Finance et Commerce">Finance et Commerce</option>
                                    <option value="Business et Marketing">Business et Marketing</option>
                                    <option value="INED">INED</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Année d'Étude:</label>
                                <input type="text" class="form-control" name="year_of_study" id="studentYearOfStudy">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ID Chambre:</label>
                                <input type="text" class="form-control" name="room_id" id="studentRoomId">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Photo de Profil:</label><br>
                                <img id="studentProfilePicture" alt="Photo de Profil" class="rounded-circle edit-profile-picture"><br>
                                <input type="file" class="form-control" name="profile_picture">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Reçu de Paiement:</label><br>
                                <img id="studentPaymentReceipt" alt="Reçu de Paiement" width="150"><br>
                                <input type="file" class="form-control" name="payment_receipt">
                            </div>
                        </div>

                        <div class="d-flex justify-content-start" style="gap: 20px;">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Retour</button>
                            <button type="submit" id="saveButton" class="btn btn-success">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modale Profil Étudiant -->
    <div class="modal fade" id="profileStudentModal" tabindex="-1" aria-labelledby="profileStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="profileStudentModalLabel">Profil de l'Étudiant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <img id="profilePicture" alt="Photo de Profil" width="150" class="rounded-circle">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom:</label>
                            <p id="profileName"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Genre:</label>
                            <p id="profileGender"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Téléphone:</label>
                            <p id="profilePhone"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email:</label>
                            <p id="profileEmail"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Filière:</label>
                            <p id="profileMajor"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Année d'Étude:</label>
                            <p id="profileYearOfStudy"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ID Chambre:</label>
                            <p id="profileRoomId"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modale Ajouter Étudiant -->
    <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStudentModalLabel">Ajouter un Nouvel Étudiant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <form id="addForm" method="POST" action="add_student.php" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CIN:</label>
                                <input type="text" class="form-control" name="cin" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nom:</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Genre:</label>
                                <select class="form-select" name="gender" required>
                                    <option value="" disabled selected>Sélectionner le Genre</option>
                                    <option value="Male">Homme</option>
                                    <option value="Female">Femme</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Téléphone:</label>
                                <input type="text" class="form-control" name="phone" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email:</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Filière:</label>
                                <select class="form-select" name="major" required>
                                    <option value="" disabled selected>Sélectionner la Filière</option>
                                    <option value="Genie Mecanique">Genie Mecanique</option>
                                    <option value="Genie Informatique">Genie Informatique</option>
                                    <option value="Genie Electrique">Genie Electrique</option>
                                    <option value="Genie des Procedes">Genie des Procedes</option>
                                    <option value="Finance et Commerce">Finance et Commerce</option>
                                    <option value="Business et Marketing">Business et Marketing</option>
                                    <option value="INED">INED</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Année d'Étude:</label>
                                <select class="form-select" name="year_of_study" required>
                                    <option value="" disabled selected>Sélectionner l'Année d'Étude</option>
                                    <option value="1st">1ère</option>
                                    <option value="2nd">2ème</option>
                                    <option value="Licence">Licence</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ID Chambre:</label>
                                <input type="text" class="form-control" name="room_id" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Photo de Profil:</label>
                                <input type="file" class="form-control" name="profile_picture" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Reçu de Paiement:</label>
                                <input type="file" class="form-control" name="payment_receipt" required>
                            </div>
                        </div>
                        <div class="d-flex justify-content-start" style="gap: 20px;">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Retour</button>
                            <button type="submit" class="btn btn-success">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modale Succès/Erreur -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel">Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body" id="messageModalBody">
                    <!-- Le message sera inséré ici -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('hidden.bs.modal', function () {
        document.body.style.paddingTop = '0px';
        });

        document.addEventListener('DOMContentLoaded', function() {
            var editStudentModal = document.getElementById('editStudentModal');
            editStudentModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var cin = button.getAttribute('data-cin');

                fetch('get_student.php?cin=' + cin)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('studentCin').value = data.cin;
                        document.getElementById('studentName').value = data.name;
                        document.getElementById('studentGender').value = data.gender;
                        document.getElementById('studentPhone').value = data.phone;
                        document.getElementById('studentEmail').value = data.email;
                        document.getElementById('studentMajor').value = data.major;
                        document.getElementById('studentYearOfStudy').value = data.year_of_study;
                        document.getElementById('studentRoomId').value = data.room_id;
                        document.getElementById('studentProfilePicture').src = '../../uploads/' + data.profile_picture; // Chemin corrigé
                        document.getElementById('studentPaymentReceipt').src = '../../uploads/' + data.payment_receipt; // Chemin corrigé
                    })
                    .catch(error => console.error('Erreur lors de la récupération des données de l\'étudiant:', error));
            });

            var profileStudentModal = document.getElementById('profileStudentModal');
            profileStudentModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var cin = button.getAttribute('data-cin');

                fetch('get_student.php?cin=' + cin)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('profilePicture').src = '../../uploads/' + data.profile_picture; // Chemin corrigé
                        document.getElementById('profileName').innerText = data.name;
                        document.getElementById('profileGender').innerText = data.gender;
                        document.getElementById('profilePhone').innerText = data.phone;
                        document.getElementById('profileEmail').innerText = data.email;
                        document.getElementById('profileMajor').innerText = data.major;
                        document.getElementById('profileYearOfStudy').innerText = data.year_of_study;
                        document.getElementById('profileRoomId').innerText = data.room_id;
                    })
                    .catch(error => console.error('Erreur lors de la récupération des données de l\'étudiant:', error));
            });

            var editForm = document.getElementById('editForm');
            editForm.addEventListener('submit', function(event) {
                event.preventDefault();
                var formData = new FormData(editForm);

                fetch('update_student.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    document.getElementById('messageModalBody').innerText = data;
                    var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                    messageModal.show();
                })
                .catch(error => {
                    document.getElementById('messageModalBody').innerText = 'Erreur lors de la mise à jour de l\'enregistrement: ' + error;
                    var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                    messageModal.show();
                });
            });

            // Gérer la soumission du formulaire Ajouter Étudiant
            var addForm = document.getElementById('addForm');
            addForm.addEventListener('submit', function(event) {
                event.preventDefault();
                var formData = new FormData(addForm);

                fetch('add_student.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('messageModalBody').innerText = 'Étudiant ajouté avec succès!';
                        addForm.reset(); // Réinitialiser le formulaire après une soumission réussie
                        var addStudentModal = bootstrap.Modal.getInstance(document.getElementById('addStudentModal'));
                        addStudentModal.hide(); // Fermer la modale Ajouter Étudiant
                        location.reload(); // Recharger la page pour mettre à jour la liste des étudiants
                    } else {
                        document.getElementById('messageModalBody').innerText = 'Erreur: ' + data.message;
                    }
                    var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                    messageModal.show(); // Afficher la modale de message
                })
                .catch(error => {
                    document.getElementById('messageModalBody').innerText = 'Erreur lors de l\'ajout de l\'étudiant: ' + error;
                    var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                    messageModal.show(); // Afficher la modale de message
                });
            });

            // Gérer le clic sur le bouton Supprimer
            document.querySelectorAll('.delete').forEach(button => {
                button.addEventListener('click', function() {
                    const cin = this.getAttribute('data-cin');
                    if (confirm('Êtes-vous sûr de vouloir supprimer cet étudiant?')) {
                        fetch('delete_student.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ cin: cin })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Étudiant supprimé avec succès!');
                                location.reload(); // Recharger la page pour mettre à jour la liste des étudiants
                            } else {
                                alert('Erreur: ' + data.message);
                            }
                        })
                        .catch(error => alert('Erreur lors de la suppression de l\'étudiant: ' + error));
                    }
                });
            });

            const searchInput = document.getElementById('searchInput');
            const searchButton = document.getElementById('searchButton');
            const studentsTable = document.getElementById('studentsTable').getElementsByTagName('tbody')[0];

            searchButton.addEventListener('click', function () {
                const query = searchInput.value.toLowerCase();
                const rows = studentsTable.getElementsByTagName('tr');

                for (let i = 0; i < rows.length; i++) {
                    const cin = rows[i].getElementsByTagName('td')[0].textContent.toLowerCase();
                    const name = rows[i].getElementsByTagName('td')[1].textContent.toLowerCase();

                    if (cin.includes(query) || name.includes(query)) {
                        rows[i].style.display = '';
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
            });

            searchInput.addEventListener('keyup', function (event) {
                if (event.key === 'Enter') {
                    searchButton.click();
                }
            });
        });
    </script>
    
    <script src="script.js"></script>
</body>
</html>