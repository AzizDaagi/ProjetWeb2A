CREATE DATABASE IF NOT EXISTS projetwebmalek_db;
USE projetwebmalek_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS recette_aliment;
DROP TABLE IF EXISTS recettes;
DROP TABLE IF EXISTS aliments;
DROP TABLE IF EXISTS utilisateurs;
DROP TABLE IF EXISTS recommandations;
SET FOREIGN_KEY_CHECKS = 1;

-- Table `utilisateurs`
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table `recettes`
CREATE TABLE IF NOT EXISTS recettes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    temps_preparation VARCHAR(100) NOT NULL,
    niveau_difficulte VARCHAR(100) NOT NULL,
    image_url VARCHAR(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table `aliments`
CREATE TABLE IF NOT EXISTS aliments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    calories FLOAT NOT NULL COMMENT 'Calories pour 100g',
    proteines FLOAT NOT NULL DEFAULT 0.0,
    glucides FLOAT NOT NULL DEFAULT 0.0,
    lipides FLOAT NOT NULL DEFAULT 0.0,
    type VARCHAR(100) NOT NULL,
    image_url VARCHAR(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table Pivot `recette_aliment`
CREATE TABLE IF NOT EXISTS recette_aliment (
    id_recette INT NOT NULL,
    id_aliment INT NOT NULL,
    quantite VARCHAR(100) DEFAULT NULL,
    PRIMARY KEY (id_recette, id_aliment),
    FOREIGN KEY (id_recette) REFERENCES recettes(id) ON DELETE CASCADE,
    FOREIGN KEY (id_aliment) REFERENCES aliments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table `recommandations`
CREATE TABLE IF NOT EXISTS recommandations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    type_objectif VARCHAR(100) NOT NULL,
    contenu_regle TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertion d'un admin par defaut
INSERT INTO utilisateurs (nom, email, password, role) VALUES ('Admin', 'admin@smartnutrition.com', 'admin123', 'admin');
