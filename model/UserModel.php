<?php

class UserModel
{
    private $pdo;
    private $tableExistsCache = [];
    private $columnExistsCache = [];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getUserProfile(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $tableName = $this->resolveUserTable();

        if ($tableName === null) {
            return [];
        }

        $selectColumns = $this->buildSelectableColumns($tableName, [
            'id',
            'nom',
            'prenom',
            'age',
            'poids',
            'taille',
            'sexe',
            'niveau_activite',
            'objectif',
            'email',
        ]);

        if (empty($selectColumns)) {
            return [];
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT " . implode(",\n                    ", $selectColumns) . "
                FROM {$tableName}
                WHERE id = :userId
                LIMIT 1
            ");
            $stmt->execute([
                'userId' => $userId,
            ]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return [];
            }

            return $this->normalizeUserProfile($row);
        } catch (Exception $exception) {
            error_log($exception->getMessage());
            return [];
        }
    }

    public function getRemindableUsers(): array
    {
        $tableName = $this->resolveUserTable();

        if ($tableName === null) {
            return [];
        }

        $selectColumns = $this->buildSelectableColumns($tableName, [
            'id',
            'email',
            'nom',
            'prenom',
        ]);

        if (empty($selectColumns) || !$this->columnExists($tableName, 'email')) {
            return [];
        }

        try {
            $stmt = $this->pdo->query("
                SELECT " . implode(",\n                    ", $selectColumns) . "
                FROM {$tableName}
                WHERE email IS NOT NULL
                  AND email <> ''
                ORDER BY id DESC
            ");

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            return array_map([$this, 'normalizeReminderUser'], $rows);
        } catch (Exception $exception) {
            error_log($exception->getMessage());
            return [];
        }
    }

    private function normalizeUserProfile(array $row): array
    {
        return [
            'id' => isset($row['id']) ? (int) $row['id'] : 0,
            'nom' => trim((string) ($row['nom'] ?? '')),
            'prenom' => trim((string) ($row['prenom'] ?? '')),
            'age' => isset($row['age']) && is_numeric($row['age']) ? (int) $row['age'] : null,
            'poids' => isset($row['poids']) && is_numeric($row['poids']) ? (float) $row['poids'] : null,
            'taille' => isset($row['taille']) && is_numeric($row['taille']) ? (float) $row['taille'] : null,
            'sexe' => trim((string) ($row['sexe'] ?? '')),
            'niveau_activite' => trim((string) ($row['niveau_activite'] ?? '')),
            'objectif' => trim((string) ($row['objectif'] ?? '')),
            'email' => trim((string) ($row['email'] ?? '')),
        ];
    }

    private function normalizeReminderUser(array $row): array
    {
        return [
            'id' => isset($row['id']) ? (int) $row['id'] : 0,
            'email' => trim((string) ($row['email'] ?? '')),
            'nom' => trim((string) ($row['nom'] ?? '')),
            'prenom' => trim((string) ($row['prenom'] ?? '')),
        ];
    }

    private function resolveUserTable(): ?string
    {
        if ($this->tableExists('users')) {
            return 'users';
        }

        if ($this->tableExists('utilisateur')) {
            return 'utilisateur';
        }

        return null;
    }

    private function buildSelectableColumns(string $tableName, array $candidates): array
    {
        $columns = [];

        foreach ($candidates as $column) {
            if ($this->columnExists($tableName, $column)) {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    private function tableExists(string $tableName): bool
    {
        if (array_key_exists($tableName, $this->tableExistsCache)) {
            return $this->tableExistsCache[$tableName];
        }

        try {
            $stmt = $this->pdo->prepare('SHOW TABLES LIKE ?');
            $stmt->execute([$tableName]);
            $exists = (bool) $stmt->fetchColumn();
        } catch (Exception $exception) {
            error_log($exception->getMessage());
            $exists = false;
        }

        $this->tableExistsCache[$tableName] = $exists;

        return $exists;
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        $cacheKey = $tableName . '.' . $columnName;

        if (array_key_exists($cacheKey, $this->columnExistsCache)) {
            return $this->columnExistsCache[$cacheKey];
        }

        if (!$this->tableExists($tableName)) {
            $this->columnExistsCache[$cacheKey] = false;
            return false;
        }

        try {
            $stmt = $this->pdo->prepare("SHOW COLUMNS FROM `$tableName` LIKE ?");
            $stmt->execute([$columnName]);
            $exists = (bool) $stmt->fetchColumn();
        } catch (Exception $exception) {
            error_log($exception->getMessage());
            $exists = false;
        }

        $this->columnExistsCache[$cacheKey] = $exists;

        return $exists;
    }
}
