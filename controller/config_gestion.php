<?php
// Fichier de configuration de secours pour la compatibilité
require_once __DIR__ . '/../model/Database.php';

class Config {
    public static function getConnexion() {
        return Database::getConnection();
    }
}
