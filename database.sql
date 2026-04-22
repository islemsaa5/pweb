-- ============================================
-- Projet: Gestion de Scolarité USTHB
-- Équipe:
-- - SAADI Islem (232331698506)
-- - KHELLAS Maria (242431486807)
-- - ABDELLATIF Sara (242431676416)
-- - DAHMANI Anais (242431679715)
-- ============================================

USE `gestion_scolarite`;

CREATE TABLE `administrateurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `administrateurs` VALUES (1,'Admin','USTHB','admin@usthb.dz','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

UNLOCK TABLES;

CREATE TABLE `enseignants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `matricule` varchar(20) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `specialite` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `matricule` (`matricule`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `enseignants` VALUES (1,'ENS001','LAACHEMI','Ahmed','laachemi@usthb.dz','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Programmation Web'),(2,'ENS002','BOUZIDI','Sara','bouzidi@usthb.dz','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Algorithmes'),(3,'ENS003','HAMDI','Karim','hamdi@usthb.dz','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Base de Donnees');

UNLOCK TABLES;

CREATE TABLE `etudiants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `matricule` varchar(20) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `date_naissance` date DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `niveau` varchar(50) DEFAULT '2eme Annee',
  `specialite` varchar(50) DEFAULT 'ISIL',
  `section` varchar(10) DEFAULT 'C',
  `groupe_td` tinyint(4) DEFAULT 1,
  `groupe_tp` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `matricule` (`matricule`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `etudiants` VALUES (6,'242431679715','DAHMANI','Anais',NULL,'anais.dahmani@etud.usthb.dz','\\.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','2eme Annee','ISIL','C',1,1),(7,'242431676416','ABDELLATIF','Sara',NULL,'sara.abdellatif@etud.usthb.dz','\\.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','2eme Annee','ISIL','C',1,1),(8,'242431486807','KHELLAS','Maria',NULL,'maria.khellas@etud.usthb.dz','\\.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','2eme Annee','ISIL','C',1,1),(9,'232331698506','SAADI','Islem\r\n',NULL,'islam.saadi@etud.usthb.dz','\\.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','2eme Annee','ISIL','C',1,1);

UNLOCK TABLES;

CREATE TABLE `modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code_module` varchar(20) NOT NULL,
  `intitule` varchar(200) NOT NULL,
  `coefficient` int(11) NOT NULL DEFAULT 1,
  `enseignant_id` int(11) DEFAULT NULL,
  `semestre` int(11) DEFAULT 1,
  `credits` int(11) DEFAULT 3,
  `section` varchar(10) DEFAULT 'C',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_module` (`code_module`),
  KEY `enseignant_id` (`enseignant_id`),
  CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`enseignant_id`) REFERENCES `enseignants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `modules` VALUES (6,'IS1','Introduction aux Systèmes d\'Information',3,NULL,1,4,'C'),(7,'ARCHI1','Architecture des Ordinateurs',3,NULL,1,4,'C'),(8,'ALGO3','Algorithmique et structures de données',3,NULL,1,5,'C'),(9,'PROBA','Probabilités et Statistiques',2,NULL,1,4,'C'),(10,'ANUM','Analyse Numérique',2,NULL,1,4,'C'),(11,'LOGIQUE','Logique Mathématique',2,NULL,1,3,'C'),(12,'POO','Programmation Orientée Objet',2,NULL,1,3,'C'),(13,'ANG1','Anglais 1',1,NULL,1,2,'C'),(14,'GL1','Génie Logiciel 1',3,NULL,2,5,'C'),(15,'BD1','Bases de Données : Conception et Langage',3,NULL,2,6,'C'),(16,'ARCHI2','Architecture des ordinateurs 2',3,NULL,2,5,'C'),(17,'SYS1','Système d\'Exploitation 1',3,NULL,2,4,'C'),(18,'THG','Théorie des Graphes',2,NULL,2,4,'C'),(19,'PWEB','Programmation Web',2,NULL,2,4,'C'),(20,'ANG2','Anglais 2',1,NULL,2,2,'C');

UNLOCK TABLES;

CREATE TABLE `notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `etudiant_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `note` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_note` (`etudiant_id`,`module_id`),
  KEY `module_id` (`module_id`),
  CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`etudiant_id`) REFERENCES `etudiants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notes_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

UNLOCK TABLES;

