-- Création de la base de données
CREATE DATABASE IF NOT EXISTS categorie7_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE categorie7_db;

-- Table des utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM(
    'agent_edm',
    'retraite_edm',
    'ayant_droit_conjoint',
    'ayant_droit_enfant',
    'ayant_droit_tuteur',
    'personnel_somagep',
    'personnel_somapep',
    'drh',
    'audit',
    'dcc',
    'scgc',
    'admin'
) DEFAULT 'agent_edm',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des demandes d’abonnement
CREATE TABLE requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    request_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('en_attente', 'valide', 'rejete', 'complement') DEFAULT 'en_attente',
    justification TEXT,
    response_date DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des documents justificatifs
CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    type ENUM('CNI', 'contrat_location', 'certificat_astreinte', 'autre') DEFAULT 'autre',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE
);

-- Table des certificats de vie (pour les retraités)
CREATE TABLE life_certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    year INT NOT NULL,
    file_path VARCHAR(255),
    status ENUM('valide', 'manquant', 'bloque') DEFAULT 'manquant',
    uploaded_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Journal des activités (audit)
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE retraites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100),
  matricule VARCHAR(50),
  adresse VARCHAR(150),
  telephone VARCHAR(20),
  email VARCHAR(100),
  conjoint VARCHAR(100),
  enfants TEXT,
  tuteur VARCHAR(100),
  carte_identite VARCHAR(255),
  certificat_vie VARCHAR(255),
  justificatif_lien VARCHAR(255),
  date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
