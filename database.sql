-- ============================================
-- Base de donnees : gestion_scolarite
-- ============================================

CREATE DATABASE IF NOT EXISTS gestion_scolarite;
USE gestion_scolarite;

-- Table administrateurs
CREATE TABLE IF NOT EXISTS administrateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL
);

-- Table enseignants
CREATE TABLE IF NOT EXISTS enseignants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricule VARCHAR(20) UNIQUE NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    specialite VARCHAR(150)
);

-- Table etudiants
CREATE TABLE IF NOT EXISTS etudiants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricule VARCHAR(20) UNIQUE NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    date_naissance DATE,
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    niveau VARCHAR(50) DEFAULT '2eme Annee'
);

-- Table modules
CREATE TABLE IF NOT EXISTS modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code_module VARCHAR(20) UNIQUE NOT NULL,
    intitule VARCHAR(200) NOT NULL,
    coefficient INT NOT NULL DEFAULT 1,
    enseignant_id INT,
    FOREIGN KEY (enseignant_id) REFERENCES enseignants(id) ON DELETE SET NULL
);

-- Table notes
CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id INT NOT NULL,
    module_id INT NOT NULL,
    note DECIMAL(5,2) NOT NULL,
    UNIQUE KEY unique_note (etudiant_id, module_id),
    FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
);

-- ============================================
-- Donnees de test
-- ============================================

-- Admin (mot de passe : password)
INSERT INTO administrateurs (nom, prenom, email, mot_de_passe) VALUES
('Admin', 'USTHB', 'admin@usthb.dz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Enseignants (mot de passe : password)
INSERT INTO enseignants (matricule, nom, prenom, email, mot_de_passe, specialite) VALUES
('ENS001', 'LAACHEMI', 'Ahmed', 'laachemi@usthb.dz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Programmation Web'),
('ENS002', 'BOUZIDI', 'Sara', 'bouzidi@usthb.dz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Algorithmes'),
('ENS003', 'HAMDI', 'Karim', 'hamdi@usthb.dz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Base de Donnees');

-- Etudiants (mot de passe : password)
INSERT INTO etudiants (matricule, nom, prenom, date_naissance, email, mot_de_passe, niveau) VALUES
('20230001', 'KARIM', 'Ali', '2003-05-15', 'ali.karim@etud.usthb.dz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2eme Annee'),
('20230002', 'HOUDA', 'Leila', '2003-08-22', 'leila.houda@etud.usthb.dz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2eme Annee'),
('20230003', 'BENALI', 'Youcef', '2002-11-30', 'youcef.benali@etud.usthb.dz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2eme Annee'),
('20230004', 'SELLAMI', 'Amina', '2003-03-12', 'amina.sellami@etud.usthb.dz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2eme Annee'),
('20230005', 'MEZIANE', 'Riad', '2002-07-19', 'riad.meziane@etud.usthb.dz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2eme Annee');

-- Modules
INSERT INTO modules (code_module, intitule, coefficient, enseignant_id) VALUES
('PWEB', 'Programmation Web', 3, 1),
('ALGO', 'Algorithmique', 4, 2),
('BD', 'Base de Donnees', 3, 3),
('MATH', 'Mathematiques', 4, NULL),
('SYS', 'Systemes d\'exploitation', 2, NULL);

-- Notes
INSERT INTO notes (etudiant_id, module_id, note) VALUES
(1, 1, 14.50), (1, 2, 12.00), (1, 3, 16.00), (1, 4, 11.50), (1, 5, 13.00),
(2, 1, 18.00), (2, 2, 15.50), (2, 3, 17.00), (2, 4, 16.00), (2, 5, 14.50),
(3, 1, 10.00), (3, 2, 08.50), (3, 3, 11.00), (3, 4, 09.50), (3, 5, 12.00),
(4, 1, 16.50), (4, 2, 14.00), (4, 3, 15.50), (4, 4, 13.00), (4, 5, 17.00),
(5, 1, 13.00), (5, 2, 11.50), (5, 3, 12.50), (5, 4, 10.00), (5, 5, 14.00);
