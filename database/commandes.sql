-- Table des types de repas
CREATE TABLE IF NOT EXISTS repas (
    id_repas INT AUTO_INCREMENT PRIMARY KEY,
    type_repas VARCHAR(50) NOT NULL
);

-- Table des tickets de repas
CREATE TABLE IF NOT EXISTS tickets (
    id_ticket INT AUTO_INCREMENT PRIMARY KEY,  -- Clé primaire
    cin VARCHAR(20),                           -- Référence à l'étudiant par son CIN
    id_repas INT,                              -- Référence au type de repas
    date DATE NOT NULL,                        -- Date du repas
    code_qr VARCHAR(255) NOT NULL UNIQUE,      -- Code QR unique pour le ticket
    FOREIGN KEY (cin) REFERENCES students(cin) ON DELETE CASCADE,  -- Clé étrangère pour les étudiants par CIN
    FOREIGN KEY (id_repas) REFERENCES repas(id_repas) ON DELETE CASCADE            -- Clé étrangère pour les repas
);

-- Table des commandes (enregistrement des repas consommés)
CREATE TABLE IF NOT EXISTS commandes (
    id_commande INT AUTO_INCREMENT PRIMARY KEY,
    cin VARCHAR(20),                           -- Référence à l'étudiant par son CIN
    id_repas INT,                              -- Référence au type de repas
    date DATE NOT NULL,                        -- Date du repas
    etat ENUM('validé', 'non validé') DEFAULT 'non validé', -- Statut de la commande
    FOREIGN KEY (cin) REFERENCES students(cin) ON DELETE CASCADE,  -- Clé étrangère pour les étudiants par CIN
    FOREIGN KEY (id_repas) REFERENCES repas(id_repas) ON DELETE CASCADE            -- Clé étrangère pour les repas
);

-- Insertion des types de repas
INSERT INTO repas (type_repas) VALUES 
('Petit Déjeuner'), 
('Déjeuner'), 
('Dîner');
