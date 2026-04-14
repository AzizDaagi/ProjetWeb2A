CREATE DATABASE IF NOT EXISTS smart_nutrition_db;
USE smart_nutrition_db;

CREATE TABLE IF NOT EXISTS activite (
    id_activite INT AUTO_INCREMENT PRIMARY KEY,
    nom_activite VARCHAR(255) NOT NULL,
    description TEXT,
    duree_minutes INT NOT NULL,
    calories_brulees INT NOT NULL
);

CREATE TABLE IF NOT EXISTS exercice (
    id_exercice INT AUTO_INCREMENT PRIMARY KEY,
    nom_exercice VARCHAR(255) NOT NULL,
    series INT NOT NULL,
    repetitions INT NOT NULL,
    id_activite INT NOT NULL,
    FOREIGN KEY (id_activite) REFERENCES activite(id_activite) ON DELETE CASCADE
);
