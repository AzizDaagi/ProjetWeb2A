<?php

class ChronoNutritionModel {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getPdo() {
        return $this->pdo;
    }

    public static function createTableIfNotExists(PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS chrono_profiles (
                id            INT AUTO_INCREMENT PRIMARY KEY,
                user_id       INT NOT NULL,
                chronotype    ENUM('leve_tot','standard','couche_tard')
                              NOT NULL DEFAULT 'standard',
                wake_time     TIME NOT NULL DEFAULT '07:00:00',
                sleep_time    TIME NOT NULL DEFAULT '23:00:00',
                sleep_quality ENUM('bonne','moyenne','mauvaise')
                              NOT NULL DEFAULT 'moyenne',
                created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                              ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    public function saveProfile($userId, $data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO chrono_profiles (user_id, chronotype, wake_time, sleep_time, sleep_quality)
            VALUES (:user_id, :chronotype, :wake_time, :sleep_time, :sleep_quality)
            ON DUPLICATE KEY UPDATE
                chronotype = :chronotype,
                wake_time = :wake_time,
                sleep_time = :sleep_time,
                sleep_quality = :sleep_quality
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':chronotype' => $data['chronotype'],
            ':wake_time' => $data['wake_time'],
            ':sleep_time' => $data['sleep_time'],
            ':sleep_quality' => $data['sleep_quality']
        ]);
        return ["data" => "Profile saved successfully.", "error" => null, "cached" => false];
    }

    public function getProfile($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM chrono_profiles WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
        return ["data" => $profile, "error" => null, "cached" => false];
    }
}