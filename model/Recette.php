<?php

class Recette
{
    private $pdo;
    private $id;
    private $nom;
    private $description;
    private $temps_preparation;
    private $niveau_difficulte;
    private $image_url;
    private $columnExistsCache = [];
    private $liaisonColumns;

    public function __construct($pdoOrId = null, $nom = null, $description = null, $temps_preparation = null, $niveau_difficulte = null, $image_url = null)
    {
        if ($pdoOrId instanceof PDO) {
            $this->pdo = $pdoOrId;
            return;
        }

        $this->id = $pdoOrId;
        $this->nom = $nom;
        $this->description = $description;
        $this->temps_preparation = $temps_preparation;
        $this->niveau_difficulte = $niveau_difficulte;
        $this->image_url = $image_url;
    }

    public function getAll()
    {
        if (!$this->pdo) {
            return [];
        }

        try {
            $query = $this->pdo->query('SELECT * FROM recettes ORDER BY id DESC');
            return $this->normalizeRecetteRows($query->fetchAll(PDO::FETCH_ASSOC));
        } catch (Throwable $exception) {
            return [];
        }
    }

    public function countAll()
    {
        if (!$this->pdo) {
            return 0;
        }

        try {
            return (int) $this->pdo->query('SELECT COUNT(*) FROM recettes')->fetchColumn();
        } catch (Throwable $exception) {
            return 0;
        }
    }

    public function getLatest($limit = 5)
    {
        if (!$this->pdo) {
            return [];
        }

        $limit = max(1, (int) $limit);

        try {
            $query = $this->pdo->query("SELECT * FROM recettes ORDER BY id DESC LIMIT {$limit}");
            return $this->normalizeRecetteRows($query->fetchAll(PDO::FETCH_ASSOC));
        } catch (Throwable $exception) {
            return [];
        }
    }

    public function findById($id)
    {
        if (!$this->pdo) {
            return null;
        }

        try {
            $query = $this->pdo->prepare('SELECT * FROM recettes WHERE id = :id LIMIT 1');
            $query->execute(['id' => (int) $id]);
            return $this->normalizeRecetteRow($query->fetch(PDO::FETCH_ASSOC) ?: null);
        } catch (Throwable $exception) {
            return null;
        }
    }

    public function create($nom, $description, $tempsPreparation, $niveauDifficulte, $imageUrl = null, array $alimentsQuantites = [])
    {
        if (!$this->pdo) {
            return false;
        }

        try {
            $query = $this->pdo->prepare(
                'INSERT INTO recettes (nom, description, temps_preparation, niveau_difficulte, image_url)
                 VALUES (:nom, :description, :temps_preparation, :niveau_difficulte, :image_url)'
            );
            $created = $query->execute([
                'nom' => trim((string) $nom),
                'description' => trim((string) $description),
                'temps_preparation' => trim((string) $tempsPreparation),
                'niveau_difficulte' => trim((string) $niveauDifficulte),
                'image_url' => $this->normalizeImagePath($imageUrl),
            ]);

            if (!$created) {
                return false;
            }

            $this->replaceAliments((int) $this->pdo->lastInsertId(), $alimentsQuantites);
            return true;
        } catch (Throwable $exception) {
            return false;
        }
    }

    public function updateRecipe($id, $nom, $description, $tempsPreparation, $niveauDifficulte, $imageUrl = null, array $alimentsQuantites = [])
    {
        if (!$this->pdo) {
            return false;
        }

        try {
            $query = $this->pdo->prepare(
                'UPDATE recettes
                 SET nom = :nom,
                     description = :description,
                     temps_preparation = :temps_preparation,
                     niveau_difficulte = :niveau_difficulte,
                     image_url = :image_url
                 WHERE id = :id'
            );
            $updated = $query->execute([
                'id' => (int) $id,
                'nom' => trim((string) $nom),
                'description' => trim((string) $description),
                'temps_preparation' => trim((string) $tempsPreparation),
                'niveau_difficulte' => trim((string) $niveauDifficulte),
                'image_url' => $this->normalizeImagePath($imageUrl),
            ]);

            if (!$updated) {
                return false;
            }

            $this->replaceAliments((int) $id, $alimentsQuantites);
            return true;
        } catch (Throwable $exception) {
            return false;
        }
    }

    public function delete($id)
    {
        if (!$this->pdo) {
            return false;
        }

        $columns = $this->getLiaisonColumns();

        try {
            $this->pdo->beginTransaction();

            $deleteAssociations = $this->pdo->prepare(
                "DELETE FROM recette_aliment WHERE {$columns['recette']} = :id"
            );
            $deleteAssociations->execute(['id' => (int) $id]);

            $query = $this->pdo->prepare('DELETE FROM recettes WHERE id = :id');
            $deleted = $query->execute(['id' => (int) $id]);

            if ($deleted) {
                $this->pdo->commit();
                return true;
            }

            $this->pdo->rollBack();
            return false;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return false;
        }
    }

    public function getAlimentsByRecette($idRecette)
    {
        if (!$this->pdo) {
            return [];
        }

        $columns = $this->getLiaisonColumns();
        $selects = [
            'a.id',
            'a.nom',
            'a.calories',
            'a.type',
            'a.proteines',
            'a.glucides',
            'a.lipides',
            'a.unite',
        ];

        if ($this->columnExists('aliments', 'sucre_g')) {
            $selects[] = 'a.sucre_g';
        }
        if ($this->columnExists('aliments', 'fibres')) {
            $selects[] = 'a.fibres';
        }
        if ($this->columnExists('aliments', 'image_url')) {
            $selects[] = 'a.image_url';
        }

        $selects[] = "ra.{$columns['quantite']} AS quantite";

        try {
            $query = $this->pdo->prepare(
                'SELECT ' . implode(', ', $selects) . '
                 FROM recette_aliment ra
                 INNER JOIN aliments a ON a.id = ra.' . $columns['aliment'] . '
                 WHERE ra.' . $columns['recette'] . ' = :id_recette
                 ORDER BY a.nom ASC'
            );
            $query->execute(['id_recette' => (int) $idRecette]);

            return $this->normalizeAlimentRows($query->fetchAll(PDO::FETCH_ASSOC));
        } catch (Throwable $exception) {
            return [];
        }
    }

    public function calculateNutritionTotals($idRecette)
    {
        $totaux = [
            'calories' => 0.0,
            'proteines' => 0.0,
            'glucides' => 0.0,
            'lipides' => 0.0,
            'sucre_g' => 0.0,
            'fibres' => 0.0,
        ];

        foreach ($this->getAlimentsByRecette($idRecette) as $aliment) {
            $quantite = (float) ($aliment['quantite'] ?? 0);

            $totaux['calories'] += ((float) ($aliment['calories'] ?? 0) * $quantite) / 100;
            $totaux['proteines'] += ((float) ($aliment['proteines'] ?? 0) * $quantite) / 100;
            $totaux['glucides'] += ((float) ($aliment['glucides'] ?? 0) * $quantite) / 100;
            $totaux['lipides'] += ((float) ($aliment['lipides'] ?? 0) * $quantite) / 100;
            $totaux['sucre_g'] += ((float) ($aliment['sucre_g'] ?? 0) * $quantite) / 100;
            $totaux['fibres'] += ((float) ($aliment['fibres'] ?? 0) * $quantite) / 100;
        }

        return $totaux;
    }

    public function replaceAliments($idRecette, array $alimentsQuantites)
    {
        if (!$this->pdo) {
            return false;
        }

        $alimentsQuantites = $this->sanitizeQuantitesMap($alimentsQuantites);
        $columns = $this->getLiaisonColumns();

        $delete = $this->pdo->prepare(
            "DELETE FROM recette_aliment WHERE {$columns['recette']} = :id_recette"
        );
        $delete->execute(['id_recette' => (int) $idRecette]);

        if (empty($alimentsQuantites)) {
            return true;
        }

        $insert = $this->pdo->prepare(
            "INSERT INTO recette_aliment ({$columns['recette']}, {$columns['aliment']}, {$columns['quantite']})
             VALUES (:id_recette, :id_aliment, :quantite)"
        );

        foreach ($alimentsQuantites as $idAliment => $quantite) {
            $insert->execute([
                'id_recette' => (int) $idRecette,
                'id_aliment' => (int) $idAliment,
                'quantite' => (float) $quantite,
            ]);
        }

        return true;
    }

    public function applyOptimisation($idRecette, array $nouvellesQuantites)
    {
        if (!$this->pdo) {
            return false;
        }

        $nouvellesQuantites = $this->sanitizeQuantitesMap($nouvellesQuantites);
        if ((int) $idRecette <= 0 || empty($nouvellesQuantites)) {
            return false;
        }

        $columns = $this->getLiaisonColumns();

        try {
            $stmt = $this->pdo->prepare(
                "UPDATE recette_aliment
                 SET {$columns['quantite']} = :qte
                 WHERE {$columns['recette']} = :id_recette AND {$columns['aliment']} = :id_aliment"
            );

            foreach ($nouvellesQuantites as $idAliment => $quantite) {
                $stmt->execute([
                    'qte' => (float) $quantite,
                    'id_recette' => (int) $idRecette,
                    'id_aliment' => (int) $idAliment,
                ]);
            }

            return true;
        } catch (Throwable $exception) {
            return false;
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getTempsPreparation()
    {
        return $this->temps_preparation;
    }

    public function getNiveauDifficulte()
    {
        return $this->niveau_difficulte;
    }

    public function getImageUrl()
    {
        return $this->image_url;
    }

    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setTempsPreparation($temps_preparation)
    {
        $this->temps_preparation = $temps_preparation;
    }

    public function setNiveauDifficulte($niveau_difficulte)
    {
        $this->niveau_difficulte = $niveau_difficulte;
    }

    public function setImageUrl($image_url)
    {
        $this->image_url = $image_url;
    }

    private function getLiaisonColumns()
    {
        if ($this->liaisonColumns !== null) {
            return $this->liaisonColumns;
        }

        $this->liaisonColumns = [
            'recette' => $this->columnExists('recette_aliment', 'recette_id') ? 'recette_id' : 'id_recette',
            'aliment' => $this->columnExists('recette_aliment', 'aliment_id') ? 'aliment_id' : 'id_aliment',
            'quantite' => 'quantite',
        ];

        return $this->liaisonColumns;
    }

    private function sanitizeQuantitesMap($alimentsQuantites)
    {
        $cleaned = [];
        if (!is_array($alimentsQuantites)) {
            return $cleaned;
        }

        foreach ($alimentsQuantites as $idAliment => $quantite) {
            $idAliment = (int) $idAliment;
            if ($idAliment <= 0 || !is_numeric($quantite)) {
                continue;
            }

            $quantite = (float) $quantite;
            if ($quantite <= 0) {
                continue;
            }

            $cleaned[$idAliment] = $quantite;
        }

        return $cleaned;
    }

    private function normalizeRecetteRows(array $rows)
    {
        $normalized = [];
        foreach ($rows as $row) {
            $normalizedRow = $this->normalizeRecetteRow($row);
            if ($normalizedRow !== null) {
                $normalized[] = $normalizedRow;
            }
        }

        return $normalized;
    }

    private function normalizeRecetteRow($row)
    {
        if (!is_array($row)) {
            return null;
        }

        $row['image_url'] = $this->normalizeImagePath($row['image_url'] ?? null);
        return $row;
    }

    private function normalizeAlimentRows(array $rows)
    {
        $normalized = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $row['image_url'] = $this->normalizeImagePath($row['image_url'] ?? null);
            $normalized[] = $row;
        }

        return $normalized;
    }

    private function normalizeImagePath($imagePath)
    {
        $imagePath = trim((string) $imagePath);
        if ($imagePath === '') {
            return null;
        }

        if (strpos($imagePath, '/projetwebmalek/') === 0) {
            return '/projet-web-25-26/' . ltrim(substr($imagePath, strlen('/projetwebmalek/')), '/');
        }

        if (strpos($imagePath, 'projetwebmalek/') === 0) {
            return '/projet-web-25-26/' . ltrim(substr($imagePath, strlen('projetwebmalek/')), '/');
        }

        if (strpos($imagePath, '/projet-web-25-26/') === 0) {
            return $imagePath;
        }

        if (strpos($imagePath, 'view/uploads/') === 0) {
            return '/projet-web-25-26/' . $imagePath;
        }

        return $imagePath;
    }

    private function columnExists($tableName, $columnName)
    {
        if (!$this->pdo) {
            return false;
        }

        $cacheKey = $tableName . '.' . $columnName;
        if (array_key_exists($cacheKey, $this->columnExistsCache)) {
            return $this->columnExistsCache[$cacheKey];
        }

        try {
            $stmt = $this->pdo->prepare("SHOW COLUMNS FROM `$tableName` LIKE ?");
            $stmt->execute([$columnName]);
            $exists = (bool) $stmt->fetchColumn();
        } catch (Throwable $exception) {
            $exists = false;
        }

        $this->columnExistsCache[$cacheKey] = $exists;
        return $exists;
    }
}
