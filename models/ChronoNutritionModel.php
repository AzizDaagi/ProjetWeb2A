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
                energy_peak   ENUM('matin','apres_midi','soir') DEFAULT NULL,
                energy_dip    ENUM('aucun','fin_matin','apres_midi','soir')
                              NOT NULL DEFAULT 'aucun',
                workout_time  ENUM('aucun','matin','midi','apres_midi','soir')
                              NOT NULL DEFAULT 'aucun',
                last_caffeine_time ENUM('aucun','avant_12h','12_14h','14_17h','apres_17h')
                              NOT NULL DEFAULT 'aucun',
                preferred_meals_count TINYINT NOT NULL DEFAULT 3,
                created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                              ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        self::ensureColumnExists($pdo, 'energy_peak', "ALTER TABLE chrono_profiles ADD COLUMN energy_peak ENUM('matin','apres_midi','soir') DEFAULT NULL AFTER sleep_quality");
        self::ensureColumnExists($pdo, 'energy_dip', "ALTER TABLE chrono_profiles ADD COLUMN energy_dip ENUM('aucun','fin_matin','apres_midi','soir') NOT NULL DEFAULT 'aucun' AFTER energy_peak");
        self::ensureColumnExists($pdo, 'workout_time', "ALTER TABLE chrono_profiles ADD COLUMN workout_time ENUM('aucun','matin','midi','apres_midi','soir') NOT NULL DEFAULT 'aucun' AFTER energy_dip");
        self::ensureColumnExists($pdo, 'last_caffeine_time', "ALTER TABLE chrono_profiles ADD COLUMN last_caffeine_time ENUM('aucun','avant_12h','12_14h','14_17h','apres_17h') NOT NULL DEFAULT 'aucun' AFTER workout_time");
        self::ensureColumnExists($pdo, 'preferred_meals_count', "ALTER TABLE chrono_profiles ADD COLUMN preferred_meals_count TINYINT NOT NULL DEFAULT 3 AFTER last_caffeine_time");
    }

    public function saveProfile($userId, $data) {
        $profileData = $this->normalizeProfileData($data);
        $existingProfileId = $this->getLatestProfileId($userId);

        if ($existingProfileId !== null) {
            $stmt = $this->pdo->prepare("
                UPDATE chrono_profiles
                SET chronotype = :chronotype,
                    wake_time = :wake_time,
                    sleep_time = :sleep_time,
                    sleep_quality = :sleep_quality,
                    energy_peak = :energy_peak,
                    energy_dip = :energy_dip,
                    workout_time = :workout_time,
                    last_caffeine_time = :last_caffeine_time,
                    preferred_meals_count = :preferred_meals_count
                WHERE id = :id
            ");
            $stmt->execute([
                ':id' => $existingProfileId,
                ':chronotype' => $profileData['chronotype'],
                ':wake_time' => $profileData['wake_time'],
                ':sleep_time' => $profileData['sleep_time'],
                ':sleep_quality' => $profileData['sleep_quality'],
                ':energy_peak' => $profileData['energy_peak'],
                ':energy_dip' => $profileData['energy_dip'],
                ':workout_time' => $profileData['workout_time'],
                ':last_caffeine_time' => $profileData['last_caffeine_time'],
                ':preferred_meals_count' => $profileData['preferred_meals_count'],
            ]);
        } else {
            $stmt = $this->pdo->prepare("
                INSERT INTO chrono_profiles (
                    user_id,
                    chronotype,
                    wake_time,
                    sleep_time,
                    sleep_quality,
                    energy_peak,
                    energy_dip,
                    workout_time,
                    last_caffeine_time,
                    preferred_meals_count
                )
                VALUES (
                    :user_id,
                    :chronotype,
                    :wake_time,
                    :sleep_time,
                    :sleep_quality,
                    :energy_peak,
                    :energy_dip,
                    :workout_time,
                    :last_caffeine_time,
                    :preferred_meals_count
                )
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':chronotype' => $profileData['chronotype'],
                ':wake_time' => $profileData['wake_time'],
                ':sleep_time' => $profileData['sleep_time'],
                ':sleep_quality' => $profileData['sleep_quality'],
                ':energy_peak' => $profileData['energy_peak'],
                ':energy_dip' => $profileData['energy_dip'],
                ':workout_time' => $profileData['workout_time'],
                ':last_caffeine_time' => $profileData['last_caffeine_time'],
                ':preferred_meals_count' => $profileData['preferred_meals_count'],
            ]);
        }

        return [
            "data" => [
                "message" => "Profil chrono enregistre avec succes.",
                "profile" => $this->getProfile($userId)['data'],
            ],
            "error" => null,
            "cached" => false,
        ];
    }

    public function getProfile($userId) {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM chrono_profiles
            WHERE user_id = :user_id
            ORDER BY updated_at DESC, id DESC
            LIMIT 1
        ");
        $stmt->execute([':user_id' => $userId]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        if (is_array($profile)) {
            $profile['wake_time'] = $this->formatTimeForResponse($profile['wake_time'] ?? '07:00:00');
            $profile['sleep_time'] = $this->formatTimeForResponse($profile['sleep_time'] ?? '23:00:00');
            $profile['energy_peak'] = $profile['energy_peak'] ?? null;
            $profile['energy_dip'] = $profile['energy_dip'] ?? 'aucun';
            $profile['workout_time'] = $profile['workout_time'] ?? 'aucun';
            $profile['last_caffeine_time'] = $profile['last_caffeine_time'] ?? 'aucun';
            $profile['preferred_meals_count'] = (int) ($profile['preferred_meals_count'] ?? 3);
        }

        return ["data" => $profile, "error" => null, "cached" => false];
    }

    private function getLatestProfileId($userId) {
        $stmt = $this->pdo->prepare("
            SELECT id
            FROM chrono_profiles
            WHERE user_id = :user_id
            ORDER BY updated_at DESC, id DESC
            LIMIT 1
        ");
        $stmt->execute([':user_id' => $userId]);

        $profileId = $stmt->fetchColumn();

        return $profileId !== false ? (int) $profileId : null;
    }

    private function normalizeProfileData($data) {
        $chronotype = (string) ($data['chronotype'] ?? 'standard');
        $sleepQuality = (string) ($data['sleep_quality'] ?? 'moyenne');
        $energyPeak = trim((string) ($data['energy_peak'] ?? ''));
        $energyDip = (string) ($data['energy_dip'] ?? 'aucun');
        $workoutTime = (string) ($data['workout_time'] ?? 'aucun');
        $lastCaffeineTime = (string) ($data['last_caffeine_time'] ?? 'aucun');
        $preferredMealsCount = (int) ($data['preferred_meals_count'] ?? 3);
        $allowedChronotypes = ['leve_tot', 'standard', 'couche_tard'];
        $allowedSleepQualities = ['bonne', 'moyenne', 'mauvaise'];
        $allowedEnergyPeaks = ['matin', 'apres_midi', 'soir'];
        $allowedEnergyDips = ['aucun', 'fin_matin', 'apres_midi', 'soir'];
        $allowedWorkoutTimes = ['aucun', 'matin', 'midi', 'apres_midi', 'soir'];
        $allowedLastCaffeineTimes = ['aucun', 'avant_12h', '12_14h', '14_17h', 'apres_17h'];
        $allowedMealCounts = [2, 3, 4];

        if (!in_array($chronotype, $allowedChronotypes, true)) {
            $chronotype = 'standard';
        }

        if (!in_array($sleepQuality, $allowedSleepQualities, true)) {
            $sleepQuality = 'moyenne';
        }

        if (!in_array($energyPeak, $allowedEnergyPeaks, true)) {
            $energyPeak = null;
        }

        if (!in_array($energyDip, $allowedEnergyDips, true)) {
            $energyDip = 'aucun';
        }

        if (!in_array($workoutTime, $allowedWorkoutTimes, true)) {
            $workoutTime = 'aucun';
        }

        if (!in_array($lastCaffeineTime, $allowedLastCaffeineTimes, true)) {
            $lastCaffeineTime = 'aucun';
        }

        if (!in_array($preferredMealsCount, $allowedMealCounts, true)) {
            $preferredMealsCount = 3;
        }

        return [
            'chronotype' => $chronotype,
            'wake_time' => $this->normalizeTimeValue($data['wake_time'] ?? '07:00', '07:00:00'),
            'sleep_time' => $this->normalizeTimeValue($data['sleep_time'] ?? '23:00', '23:00:00'),
            'sleep_quality' => $sleepQuality,
            'energy_peak' => $energyPeak,
            'energy_dip' => $energyDip,
            'workout_time' => $workoutTime,
            'last_caffeine_time' => $lastCaffeineTime,
            'preferred_meals_count' => $preferredMealsCount,
        ];
    }

    private function normalizeTimeValue($time, $fallbackTime) {
        $time = trim((string) $time);

        if ($time === '') {
            return $fallbackTime;
        }

        $timestamp = strtotime($time);

        if ($timestamp === false) {
            return $fallbackTime;
        }

        return date('H:i:s', $timestamp);
    }

    private function formatTimeForResponse($time) {
        $timestamp = strtotime((string) $time);

        if ($timestamp === false) {
            return '07:00';
        }

        return date('H:i', $timestamp);
    }

    private static function ensureColumnExists(PDO $pdo, $columnName, $alterSql): void {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM chrono_profiles LIKE ?");
        $stmt->execute([$columnName]);

        if ($stmt->fetchColumn() === false) {
            $pdo->exec($alterSql);
        }
    }
}
