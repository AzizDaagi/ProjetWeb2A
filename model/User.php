<?php

class User
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function emailExists($email)
    {
        $sql = 'SELECT id FROM users WHERE email = :email LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);

        return (bool) $stmt->fetch();
    }

    public function emailExistsForAnother($email, $excludeId)
    {
        $sql = 'SELECT id FROM users WHERE email = :email AND id <> :exclude_id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'email' => $email,
            'exclude_id' => (int) $excludeId,
        ]);

        return (bool) $stmt->fetch();
    }

    public function create($data)
    {
        $roleSlug = trim((string) ($data['role'] ?? 'user'));
        if ($roleSlug === '') {
            $roleSlug = 'user';
        }

        $sql = 'INSERT INTO users (nom, prenom, date_naissance, sexe, age, poids, taille, objectif, email, password, `role`, face_descriptor, face_updated_at)
            VALUES (:nom, :prenom, :date_naissance, :sexe, :age, :poids, :taille, :objectif, :email, :password, :role, :face_descriptor, :face_updated_at)';

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'date_naissance' => $data['date_naissance'] ?? null,
            'sexe' => $data['sexe'] ?? null,
            'age' => $data['age'] ?? null,
            'poids' => $data['poids'] ?? null,
            'taille' => $data['taille'] ?? null,
            'objectif' => $data['objectif'] ?? null,
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $roleSlug,
            'face_descriptor' => $data['face_descriptor'] ?? null,
            'face_updated_at' => $data['face_updated_at'] ?? null,
        ]);
    }

    public function findByEmail($email)
    {
        $sql = 'SELECT u.*, COALESCE(NULLIF(u.role, ""), "user") AS role
                FROM users u
                WHERE u.email = :email
                LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);

        return $stmt->fetch();
    }

    public function findById($id)
    {
        $sql = 'SELECT
                    u.id,
                    u.nom,
                    u.prenom,
                    u.date_naissance,
                    u.sexe,
                    u.age,
                    u.poids,
                    u.taille,
                    u.objectif,
                    u.email,
                    u.face_descriptor,
                    u.face_updated_at,
                    COALESCE(NULLIF(u.role, ""), "user") AS role
                FROM users u
                WHERE u.id = :id
                LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    public function getAllWithRole()
    {
        $sql = 'SELECT
                    u.id,
                    u.nom,
                    u.prenom,
                    u.date_naissance,
                    u.sexe,
                    u.age,
                    u.poids,
                    u.taille,
                    u.objectif,
                    u.email,
                    u.face_descriptor,
                    u.face_updated_at,
                    COALESCE(NULLIF(u.role, ""), "user") AS role
                FROM users u
                WHERE COALESCE(NULLIF(u.role, ""), "user") <> "admin"
                ORDER BY u.id DESC';

        return $this->pdo->query($sql)->fetchAll();
    }

    public function getAllWithFaceDescriptors()
    {
        $sql = 'SELECT
                    u.id,
                    u.nom,
                    u.prenom,
                    u.email,
                    u.face_descriptor,
                    u.face_updated_at,
                    COALESCE(NULLIF(u.role, ""), "user") AS role
                FROM users u
                WHERE u.face_descriptor IS NOT NULL AND TRIM(u.face_descriptor) <> ""';

        return $this->pdo->query($sql)->fetchAll();
    }

    public function updateById($id, $data)
    {
        $sql = 'UPDATE users
                SET nom = :nom,
                    prenom = :prenom,
                    date_naissance = :date_naissance,
                    sexe = :sexe,
                    age = :age,
                    poids = :poids,
                    taille = :taille,
                    objectif = :objectif,
                    email = :email
                WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'id' => (int) $id,
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'date_naissance' => $data['date_naissance'] ?? null,
            'sexe' => $data['sexe'] ?? null,
            'age' => $data['age'] ?? null,
            'poids' => $data['poids'] ?? null,
            'taille' => $data['taille'] ?? null,
            'objectif' => $data['objectif'] ?? null,
            'email' => $data['email'],
        ]);
    }

    public function deleteById($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => (int) $id]);
    }

    public function updateFaceDescriptorById($id, $faceDescriptorJson)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users
             SET face_descriptor = :face_descriptor,
                 face_updated_at = NOW()
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => (int) $id,
            'face_descriptor' => $faceDescriptorJson,
        ]);
    }

    public function clearFaceDescriptorById($id)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users
             SET face_descriptor = NULL,
                 face_updated_at = NULL
             WHERE id = :id'
        );

        return $stmt->execute(['id' => (int) $id]);
    }

    public function setPasswordResetTokenByEmail($email, $token, $expiresAt)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users
             SET password_reset_token = :token,
                 password_reset_expires = :expires
             WHERE email = :email'
        );

        return $stmt->execute([
            'token' => $token,
            'expires' => $expiresAt,
            'email' => $email,
        ]);
    }

    public function findByResetToken($token)
    {
        $sql = 'SELECT * FROM users WHERE password_reset_token = :token LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['token' => $token]);

        return $stmt->fetch();
    }

    public function updatePasswordById($id, $hashedPassword)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users
             SET password = :password,
                 password_reset_token = NULL,
                 password_reset_expires = NULL
             WHERE id = :id'
        );

        return $stmt->execute([
            'password' => $hashedPassword,
            'id' => (int) $id,
        ]);
    }

    public function clearResetTokenById($id)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users
             SET password_reset_token = NULL,
                 password_reset_expires = NULL
             WHERE id = :id'
        );

        return $stmt->execute(['id' => (int) $id]);
    }

}
