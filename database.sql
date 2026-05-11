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
    muscle_principal VARCHAR(100) NOT NULL,
    muscle_secondaire VARCHAR(100) DEFAULT NULL,
    niveau_difficulte VARCHAR(50) NOT NULL,
    calories_estimees INT NOT NULL,
    id_activite INT NOT NULL,
    FOREIGN KEY (id_activite) REFERENCES activite(id_activite) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS nutrition_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    current_weight FLOAT NOT NULL,
    current_goal ENUM('lose weight', 'gain muscle', 'maintain weight') NOT NULL,
    height FLOAT DEFAULT NULL,
    message TEXT,
    generated_activities TEXT,
    generated_exercises TEXT,
    selected_exercises TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
