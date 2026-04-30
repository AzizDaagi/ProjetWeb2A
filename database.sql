-- Nouvelle table utilisateur
CREATE TABLE IF NOT EXISTS utilisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    age INT NOT NULL,
    poids FLOAT NOT NULL,
    taille FLOAT NOT NULL,
    objectif_calories FLOAT NOT NULL
);

-- Mise a jour de la table repas_consomme pour lier chaque repas a un utilisateur
ALTER TABLE repas_consomme
    ADD COLUMN user_id INT NOT NULL AFTER aliment_id;

ALTER TABLE repas_consomme
    ADD COLUMN type ENUM('proteine', 'glucide', 'lipide') NULL AFTER calories_calculees;

ALTER TABLE repas_consomme
    ADD INDEX idx_repas_user_id (user_id);

ALTER TABLE aliments
    ADD COLUMN proteines FLOAT NULL DEFAULT 0 AFTER calories,
    ADD COLUMN glucides FLOAT NULL DEFAULT 0 AFTER proteines,
    ADD COLUMN lipides FLOAT NULL DEFAULT 0 AFTER glucides;

ALTER TABLE aliments
    ADD COLUMN unite ENUM('g', 'piece') NOT NULL DEFAULT 'g' AFTER type;

ALTER TABLE objectif
    ADD COLUMN proteines FLOAT NULL DEFAULT 0 AFTER calories_cible,
    ADD COLUMN glucides FLOAT NULL DEFAULT 0 AFTER proteines,
    ADD COLUMN lipides FLOAT NULL DEFAULT 0 AFTER glucides;

ALTER TABLE objectif
    ADD COLUMN objectif_type ENUM('maintien', 'prise_muscle') NOT NULL DEFAULT 'maintien' AFTER calories_cible;

ALTER TABLE objectif
    ADD COLUMN poids FLOAT NULL DEFAULT NULL AFTER objectif_type,
    ADD COLUMN taille FLOAT NULL DEFAULT NULL AFTER poids,
    ADD COLUMN age INT NULL DEFAULT NULL AFTER taille,
    ADD COLUMN sexe ENUM('homme', 'femme') NOT NULL DEFAULT 'homme' AFTER age,
    ADD COLUMN activite ENUM('sedentary', 'light', 'moderate', 'active', 'very_active', 'extra_active') NOT NULL DEFAULT 'moderate' AFTER sexe;

-- Optionnel : cle etrangere si vous souhaitez renforcer l'integrite
-- ALTER TABLE repas_consomme
--     ADD CONSTRAINT fk_repas_utilisateur FOREIGN KEY (user_id) REFERENCES utilisateur(id);

-- Utilisateur de test pour verifier l'application rapidement
INSERT INTO utilisateur (nom, age, poids, taille, objectif_calories)
VALUES ('Utilisateur Test', 30, 70, 175, 2000);
