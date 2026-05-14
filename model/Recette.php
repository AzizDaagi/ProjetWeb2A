<?php

require_once __DIR__ . '/Database.php';

class Recette
{
    private ?PDO $db = null;
    private $id;
    private $nom;
    private $description;
    private $temps_preparation;
    private $difficulte;
    private $image_url;

    public function __construct($id = null, $nom = null, $description = null, $temps_preparation = null, $difficulte = null, $image_url = null)
    {
        if ($id instanceof PDO) {
            $this->db = $id;
            $this->id = null;
            $this->nom = $nom;
            $this->description = $description;
            $this->temps_preparation = $temps_preparation;
            $this->difficulte = $difficulte;
            $this->image_url = $image_url;
            return;
        }

        $this->db = Database::getConnection();
        $this->id = $id;
        $this->nom = $nom;
        $this->description = $description;
        $this->temps_preparation = $temps_preparation;
        $this->difficulte = $difficulte;
        $this->image_url = $image_url;
    }

    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getDescription() { return $this->description; }
    public function getTempsPreparation() { return $this->temps_preparation; }
    public function getDifficulte() { return $this->difficulte; }
    public function getImageUrl() { return $this->image_url; }

    public function setNom($nom) { $this->nom = $nom; }
    public function setDescription($description) { $this->description = $description; }
    public function setTempsPreparation($temps_preparation) { $this->temps_preparation = $temps_preparation; }
    public function setDifficulte($difficulte) { $this->difficulte = $difficulte; }
    public function setImageUrl($image_url) { $this->image_url = $image_url; }

    private function getDb(): PDO
    {
        if (!$this->db instanceof PDO) {
            $this->db = Database::getConnection();
        }

        return $this->db;
    }

    private function tableExists(string $table): bool
    {
        return Database::tableExists($this->getDb(), $table);
    }

    private function columnExists(string $table, string $column): bool
    {
        return Database::columnExists($this->getDb(), $table, $column);
    }

    private function getRecetteAlimentColumns(): array
    {
        $recetteColumn = $this->columnExists('recette_aliment', 'id_recette') ? 'id_recette' : 'recette_id';
        $alimentColumn = $this->columnExists('recette_aliment', 'id_aliment') ? 'id_aliment' : 'aliment_id';

        return [$recetteColumn, $alimentColumn];
    }

    private function getDifficultySelectExpression(): string
    {
        if ($this->columnExists('recettes', 'niveau_difficulte')) {
            return "COALESCE(niveau_difficulte, 'Moyen') AS difficulte";
        }

        if ($this->columnExists('recettes', 'difficulte')) {
            return "COALESCE(difficulte, 'Moyen') AS difficulte";
        }

        return "'Moyen' AS difficulte";
    }

    private function getDifficultyColumn(): string
    {
        if ($this->columnExists('recettes', 'niveau_difficulte')) {
            return 'niveau_difficulte';
        }

        if ($this->columnExists('recettes', 'difficulte')) {
            return 'difficulte';
        }

        return 'niveau_difficulte';
    }

    public function getAll(): array
    {
        if (!$this->tableExists('recettes')) {
            return [];
        }

        $difficultySelect = $this->getDifficultySelectExpression();
        $stmt = $this->getDb()->query("
            SELECT
                id,
                nom,
                description,
                temps_preparation,
                {$difficultySelect},
                image_url
            FROM recettes
            ORDER BY nom ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAll(): array
    {
        return $this->getAll();
    }

    public function countAll(): int
    {
        if (!$this->tableExists('recettes')) {
            return 0;
        }

        return (int) $this->getDb()->query('SELECT COUNT(*) FROM recettes')->fetchColumn();
    }

    public function getLatest(int $limit = 5): array
    {
        if (!$this->tableExists('recettes')) {
            return [];
        }

        $limit = max(1, $limit);
        $difficultySelect = $this->getDifficultySelectExpression();
        $stmt = $this->getDb()->query("
            SELECT
                id,
                nom,
                description,
                temps_preparation,
                {$difficultySelect},
                image_url
            FROM recettes
            ORDER BY id DESC
            LIMIT {$limit}
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        if (!$this->tableExists('recettes')) {
            return null;
        }

        $difficultySelect = $this->getDifficultySelectExpression();
        $stmt = $this->getDb()->prepare("
            SELECT
                id,
                nom,
                description,
                temps_preparation,
                {$difficultySelect},
                image_url
            FROM recettes
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getIngredientsByRecette(int $recetteId): array
    {
        if (!$this->tableExists('recette_aliment') || !$this->tableExists('aliments')) {
            return [];
        }

        [$recetteColumn, $alimentColumn] = $this->getRecetteAlimentColumns();

        $stmt = $this->getDb()->prepare("
            SELECT
                a.id,
                a.nom,
                COALESCE(a.calories, 0) AS calories,
                COALESCE(a.type, '') AS type,
                COALESCE(a.proteines, 0) AS proteines,
                COALESCE(a.glucides, 0) AS glucides,
                COALESCE(a.lipides, 0) AS lipides,
                COALESCE(a.unite, 'g') AS unite,
                COALESCE(a.sucre_g, 0) AS sucre_g,
                COALESCE(a.fibres, 0) AS fibres,
                a.image_url,
                COALESCE(ra.quantite, 0) AS quantite
            FROM recette_aliment ra
            JOIN aliments a ON a.id = ra.`{$alimentColumn}`
            WHERE ra.`{$recetteColumn}` = :id_recette
            ORDER BY a.nom ASC
        ");
        $stmt->execute(['id_recette' => $recetteId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data, array $alimentsQuantites = []): bool
    {
        if (!$this->tableExists('recettes')) {
            return false;
        }

        $difficultyColumn = $this->getDifficultyColumn();
        $stmt = $this->getDb()->prepare("
            INSERT INTO recettes (nom, description, temps_preparation, `{$difficultyColumn}`, image_url)
            VALUES (:nom, :description, :temps_preparation, :niveau_difficulte, :image_url)
        ");
        $ok = $stmt->execute([
            'nom' => $data['nom'] ?? '',
            'description' => $data['description'] ?? null,
            'temps_preparation' => $data['temps_preparation'] ?? null,
            'niveau_difficulte' => $data['difficulte'] ?? ($data['niveau_difficulte'] ?? null),
            'image_url' => $data['image_url'] ?? null,
        ]);

        if (!$ok) {
            return false;
        }

        $recetteId = (int) $this->getDb()->lastInsertId();
        $this->saveIngredientLinks($recetteId, $alimentsQuantites);
        return true;
    }

    public function updateRecette(int $id, array $data, array $alimentsQuantites = []): bool
    {
        if (!$this->tableExists('recettes')) {
            return false;
        }

        $difficultyColumn = $this->getDifficultyColumn();
        $stmt = $this->getDb()->prepare("
            UPDATE recettes
            SET
                nom = :nom,
                description = :description,
                temps_preparation = :temps_preparation,
                `{$difficultyColumn}` = :niveau_difficulte,
                image_url = :image_url
            WHERE id = :id
        ");
        $ok = $stmt->execute([
            'id' => $id,
            'nom' => $data['nom'] ?? '',
            'description' => $data['description'] ?? null,
            'temps_preparation' => $data['temps_preparation'] ?? null,
            'niveau_difficulte' => $data['difficulte'] ?? ($data['niveau_difficulte'] ?? null),
            'image_url' => $data['image_url'] ?? null,
        ]);

        if (!$ok) {
            return false;
        }

        $this->deleteIngredientLinks($id);
        $this->saveIngredientLinks($id, $alimentsQuantites);
        return true;
    }

    public function deleteRecette(int $id): bool
    {
        if (!$this->tableExists('recettes')) {
            return false;
        }

        $this->deleteIngredientLinks($id);
        $stmt = $this->getDb()->prepare('DELETE FROM recettes WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    private function deleteIngredientLinks(int $recetteId): void
    {
        if (!$this->tableExists('recette_aliment')) {
            return;
        }

        [$recetteColumn] = $this->getRecetteAlimentColumns();
        $stmt = $this->getDb()->prepare("DELETE FROM recette_aliment WHERE `{$recetteColumn}` = :id_recette");
        $stmt->execute(['id_recette' => $recetteId]);
    }

    private function saveIngredientLinks(int $recetteId, array $alimentsQuantites): void
    {
        if (!$this->tableExists('recette_aliment') || !$this->tableExists('aliments')) {
            return;
        }

        [$recetteColumn, $alimentColumn] = $this->getRecetteAlimentColumns();
        $stmt = $this->getDb()->prepare("
            INSERT INTO recette_aliment (`{$recetteColumn}`, `{$alimentColumn}`, `quantite`)
            VALUES (:id_recette, :id_aliment, :quantite)
        ");

        foreach ($alimentsQuantites as $alimentId => $quantite) {
            $stmt->execute([
                'id_recette' => $recetteId,
                'id_aliment' => (int) $alimentId,
                'quantite' => (float) $quantite,
            ]);
        }
    }

    public function checkEquilibreNutritionnel(array $alimentsQuantites): array
    {
        if (empty($alimentsQuantites) || !$this->tableExists('aliments')) {
            return [];
        }

        $ids = array_map('intval', array_keys($alimentsQuantites));
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->getDb()->prepare("SELECT id, proteines, glucides, lipides FROM aliments WHERE id IN ({$placeholders})");
        $stmt->execute($ids);
        $aliments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalProteines = 0.0;
        $totalGlucides = 0.0;
        $totalLipides = 0.0;

        foreach ($aliments as $aliment) {
            $qte = (float) ($alimentsQuantites[$aliment['id']] ?? 0);
            $totalProteines += ((float) $aliment['proteines'] * $qte) / 100;
            $totalGlucides += ((float) $aliment['glucides'] * $qte) / 100;
            $totalLipides += ((float) $aliment['lipides'] * $qte) / 100;
        }

        $calProteines = $totalProteines * 4;
        $calGlucides = $totalGlucides * 4;
        $calLipides = $totalLipides * 9;
        $totalCalories = $calProteines + $calGlucides + $calLipides;

        if ($totalCalories <= 0) {
            return [];
        }

        $pctProteines = ($calProteines / $totalCalories) * 100;
        $pctGlucides = ($calGlucides / $totalCalories) * 100;
        $pctLipides = ($calLipides / $totalCalories) * 100;

        $warnings = [];

        if ($pctProteines < 15) {
            $warnings[] = "Recette trop pauvre en proteines (" . round($pctProteines, 1) . "%).";
        } elseif ($pctProteines > 35) {
            $warnings[] = "Recette trop riche en proteines (" . round($pctProteines, 1) . "%).";
        }

        if ($pctGlucides < 40) {
            $warnings[] = "Recette trop pauvre en glucides (" . round($pctGlucides, 1) . "%).";
        } elseif ($pctGlucides > 60) {
            $warnings[] = "Recette trop riche en glucides (" . round($pctGlucides, 1) . "%).";
        }

        if ($pctLipides < 20) {
            $warnings[] = "Recette trop pauvre en lipides (" . round($pctLipides, 1) . "%).";
        } elseif ($pctLipides > 35) {
            $warnings[] = "Recette trop riche en lipides (" . round($pctLipides, 1) . "%).";
        }

        return $warnings;
    }

    public function calculerNutritionTotale(int $recetteId): array
    {
        $totaux = [
            'calories' => 0.0,
            'proteines' => 0.0,
            'glucides' => 0.0,
            'lipides' => 0.0,
            'fibres' => 0.0,
            'sucre_g' => 0.0,
        ];

        foreach ($this->getIngredientsByRecette($recetteId) as $aliment) {
            $qte = (float) ($aliment['quantite'] ?? 0);
            $totaux['calories'] += ((float) $aliment['calories'] * $qte) / 100;
            $totaux['proteines'] += ((float) $aliment['proteines'] * $qte) / 100;
            $totaux['glucides'] += ((float) $aliment['glucides'] * $qte) / 100;
            $totaux['lipides'] += ((float) $aliment['lipides'] * $qte) / 100;
            $totaux['fibres'] += ((float) $aliment['fibres'] * $qte) / 100;
            $totaux['sucre_g'] += ((float) $aliment['sucre_g'] * $qte) / 100;
        }

        return $totaux;
    }

    public function generateRecipeFromConstraints(float $maxKcal, float $minProt, float $maxLipides, string $dietType)
    {
        if (!$this->tableExists('aliments')) {
            return false;
        }

        $aliments = $this->getDb()->query("
            SELECT id, nom, calories, COALESCE(type, '') AS type, COALESCE(proteines, 0) AS proteines,
                   COALESCE(glucides, 0) AS glucides, COALESCE(lipides, 0) AS lipides, COALESCE(fibres, 0) AS fibres
            FROM aliments
        ")->fetchAll(PDO::FETCH_ASSOC);

        $filtered = [];
        foreach ($aliments as $al) {
            $nom = mb_strtolower((string) $al['nom'], 'UTF-8');
            $type = mb_strtolower((string) $al['type'], 'UTF-8');

            if ($dietType === 'vegetarien') {
                if (strpos($nom, 'viande') !== false || strpos($type, 'viande') !== false ||
                    strpos($nom, 'poisson') !== false || strpos($type, 'poisson') !== false ||
                    strpos($nom, 'poulet') !== false || strpos($type, 'poulet') !== false ||
                    strpos($nom, 'boeuf') !== false || strpos($nom, 'porc') !== false) {
                    continue;
                }
            }

            if ($dietType === 'sans_gluten') {
                if (strpos($nom, 'ble') !== false || strpos($type, 'ble') !== false ||
                    strpos($nom, 'pate') !== false || strpos($type, 'pate') !== false ||
                    strpos($nom, 'pain') !== false || strpos($type, 'pain') !== false ||
                    strpos($nom, 'farine') !== false) {
                    continue;
                }
            }

            $filtered[] = $al;
        }

        if (empty($filtered)) {
            return false;
        }

        $proteins = [];
        $carbs = [];
        $veggies = [];

        foreach ($filtered as $al) {
            if ((float) $al['proteines'] >= 10 && (float) $al['proteines'] >= (float) $al['glucides']) {
                $proteins[] = $al;
            } elseif ((float) $al['glucides'] >= 15) {
                $carbs[] = $al;
            } else {
                $veggies[] = $al;
            }
        }

        if (empty($proteins)) { $proteins = $filtered; }
        if (empty($carbs)) { $carbs = $filtered; }
        if (empty($veggies)) { $veggies = $filtered; }

        $bestCombo = null;
        $bestScore = INF;

        for ($i = 0; $i < 100; $i++) {
            $p = $proteins[array_rand($proteins)];
            $c = $carbs[array_rand($carbs)];
            $v = $veggies[array_rand($veggies)];

            $qP = 150;
            $qC = 100;
            $qV = 150;

            $calcP = ($p['proteines'] * $qP / 100) + ($c['proteines'] * $qC / 100) + ($v['proteines'] * $qV / 100);
            if ($calcP < $minProt) {
                $qP += 50;
            }

            $calcK = ($p['calories'] * $qP / 100) + ($c['calories'] * $qC / 100) + ($v['calories'] * $qV / 100);
            if ($calcK > $maxKcal) {
                $qC = max($qC - 40, 0);
                $qP = max($qP - 20, 50);
            }

            $calcP = ($p['proteines'] * $qP / 100) + ($c['proteines'] * $qC / 100) + ($v['proteines'] * $qV / 100);
            $calcL = ($p['lipides'] * $qP / 100) + ($c['lipides'] * $qC / 100) + ($v['lipides'] * $qV / 100);
            $calcK = ($p['calories'] * $qP / 100) + ($c['calories'] * $qC / 100) + ($v['calories'] * $qV / 100);
            $calcG = ($p['glucides'] * $qP / 100) + ($c['glucides'] * $qC / 100) + ($v['glucides'] * $qV / 100);
            $calcF = (($p['fibres'] ?? 0) * $qP / 100) + (($c['fibres'] ?? 0) * $qC / 100) + (($v['fibres'] ?? 0) * $qV / 100);

            $score = 0;
            if ($calcK > $maxKcal) { $score += ($calcK - $maxKcal) * 2; }
            if ($calcP < $minProt) { $score += ($minProt - $calcP) * 5; }
            if ($calcL > $maxLipides) { $score += ($calcL - $maxLipides) * 3; }

            if ($score < $bestScore) {
                $bestScore = $score;
                $bestCombo = [
                    'aliments' => [
                        $p['id'] => ['aliment' => $p, 'quantite' => $qP],
                        $c['id'] => ['aliment' => $c, 'quantite' => $qC],
                        $v['id'] => ['aliment' => $v, 'quantite' => $qV],
                    ],
                    'totaux' => [
                        'calories' => round($calcK),
                        'proteines' => round($calcP, 1),
                        'lipides' => round($calcL, 1),
                        'glucides' => round($calcG, 1),
                        'fibres' => round($calcF, 1),
                    ],
                    'score' => $score,
                ];
            }

            if ($score === 0) {
                break;
            }
        }

        if ($bestCombo === null) {
            return false;
        }

        $finalAliments = [];
        foreach ($bestCombo['aliments'] as $id => $data) {
            if ($data['quantite'] > 0) {
                if (isset($finalAliments[$id])) {
                    $finalAliments[$id]['quantite'] += $data['quantite'];
                } else {
                    $finalAliments[$id] = $data;
                }
            }
        }

        $bestCombo['aliments'] = array_values($finalAliments);
        return $bestCombo;
    }

    public function optimiserRecette(int $recetteId, string $objectif = 'equilibre_global')
    {
        $aliments = $this->getIngredientsByRecette($recetteId);
        if (empty($aliments)) {
            return false;
        }

        $avant = ['calories' => 0, 'proteines' => 0, 'glucides' => 0, 'lipides' => 0, 'fibres' => 0];
        foreach ($aliments as $a) {
            $q = (float) ($a['quantite'] ?: 0);
            $avant['calories'] += ($a['calories'] * $q) / 100;
            $avant['proteines'] += ($a['proteines'] * $q) / 100;
            $avant['glucides'] += ($a['glucides'] * $q) / 100;
            $avant['lipides'] += ($a['lipides'] * $q) / 100;
            $avant['fibres'] += (($a['fibres'] ?: 0) * $q) / 100;
        }

        $calProt = $avant['proteines'] * 4;
        $calGluc = $avant['glucides'] * 4;
        $calLip = $avant['lipides'] * 9;
        $calTotal = $calProt + $calGluc + $calLip;

        $pctProt = $calTotal > 0 ? ($calProt / $calTotal * 100) : 0;
        $pctGluc = $calTotal > 0 ? ($calGluc / $calTotal * 100) : 0;
        $pctLip = $calTotal > 0 ? ($calLip / $calTotal * 100) : 0;

        $ecarts = [];
        if ($pctProt < 15) { $ecarts[] = ['type' => 'prot_faible', 'label' => 'Proteines trop faibles (' . round($pctProt, 1) . '% < 15%)']; }
        if ($pctProt > 35) { $ecarts[] = ['type' => 'prot_eleve', 'label' => 'Proteines trop elevees (' . round($pctProt, 1) . '% > 35%)']; }
        if ($pctGluc < 40) { $ecarts[] = ['type' => 'gluc_faible', 'label' => 'Glucides insuffisants (' . round($pctGluc, 1) . '% < 40%)']; }
        if ($pctGluc > 60) { $ecarts[] = ['type' => 'gluc_eleve', 'label' => 'Glucides excessifs (' . round($pctGluc, 1) . '% > 60%)']; }
        if ($pctLip < 20) { $ecarts[] = ['type' => 'lip_faible', 'label' => 'Lipides insuffisants (' . round($pctLip, 1) . '% < 20%)']; }
        if ($pctLip > 35) { $ecarts[] = ['type' => 'lip_eleve', 'label' => 'Lipides trop eleves (' . round($pctLip, 1) . '% > 35%)']; }
        if ($avant['fibres'] < 5) { $ecarts[] = ['type' => 'fibres_faible', 'label' => 'Fibres insuffisantes (' . round($avant['fibres'], 1) . 'g < 5g)']; }

        $nouvellesQuantites = [];
        foreach ($aliments as $a) {
            $id = (int) $a['id'];
            $q = (float) ($a['quantite'] ?: 100);
            $protPer100 = (float) $a['proteines'];
            $glucPer100 = (float) $a['glucides'];
            $lipPer100 = (float) $a['lipides'];
            $fibrePer100 = (float) ($a['fibres'] ?? 0);
            $calPer100 = (float) $a['calories'];

            $isHighProt = $protPer100 >= 15;
            $isHighLip = $lipPer100 >= 10;
            $isHighFibre = $fibrePer100 >= 3;
            $isHighCarb = $glucPer100 >= 20 && $protPer100 < 10;

            switch ($objectif) {
                case 'plus_proteines':
                    if ($isHighProt) { $q = min($q * 1.5, $q + 80); }
                    if ($isHighCarb) { $q = max($q * 0.75, 30); }
                    break;
                case 'moins_lipides':
                    if ($isHighLip) { $q = max($q * 0.55, 20); }
                    if ($isHighProt && !$isHighLip) { $q = min($q * 1.2, $q + 40); }
                    break;
                case 'plus_fibres':
                    if ($isHighFibre) { $q = min($q * 1.6, $q + 100); }
                    if ($calPer100 > 200 && !$isHighFibre) { $q = max($q * 0.8, 30); }
                    break;
                case 'equilibre_global':
                default:
                    if ($isHighProt && $pctProt < 20) { $q = min($q * 1.4, $q + 60); }
                    if ($isHighCarb && $pctGluc < 40) { $q = min($q * 1.3, $q + 50); }
                    if ($isHighLip && $pctLip > 30) { $q = max($q * 0.65, 20); }
                    if ($isHighFibre && $avant['fibres'] < 5) { $q = min($q * 1.5, $q + 60); }
                    break;
            }

            $nouvellesQuantites[$id] = round($q);
        }

        $apres = ['calories' => 0, 'proteines' => 0, 'glucides' => 0, 'lipides' => 0, 'fibres' => 0];
        foreach ($aliments as $a) {
            $q = $nouvellesQuantites[$a['id']] ?? (float) $a['quantite'];
            $apres['calories'] += ($a['calories'] * $q) / 100;
            $apres['proteines'] += ($a['proteines'] * $q) / 100;
            $apres['glucides'] += ($a['glucides'] * $q) / 100;
            $apres['lipides'] += ($a['lipides'] * $q) / 100;
            $apres['fibres'] += (($a['fibres'] ?? 0) * $q) / 100;
        }

        foreach ($avant as $k => $v) { $avant[$k] = round($v, 1); }
        foreach ($apres as $k => $v) { $apres[$k] = round($v, 1); }

        $cpProt = $apres['proteines'] * 4;
        $cpGluc = $apres['glucides'] * 4;
        $cpLip = $apres['lipides'] * 9;
        $cpTot = $cpProt + $cpGluc + $cpLip;

        return [
            'avant' => $avant,
            'apres' => $apres,
            'nouvelles_quantites' => $nouvellesQuantites,
            'aliments' => $aliments,
            'ecarts' => $ecarts,
            'pct_avant' => [
                'prot' => round($pctProt, 1),
                'gluc' => round($pctGluc, 1),
                'lip' => round($pctLip, 1),
            ],
            'pct_apres' => [
                'prot' => $cpTot > 0 ? round($cpProt / $cpTot * 100, 1) : 0,
                'gluc' => $cpTot > 0 ? round($cpGluc / $cpTot * 100, 1) : 0,
                'lip' => $cpTot > 0 ? round($cpLip / $cpTot * 100, 1) : 0,
            ],
        ];
    }

    public function appliquerOptimisation(int $recetteId, array $nouvellesQuantites): void
    {
        if (!$this->tableExists('recette_aliment')) {
            return;
        }

        [$recetteColumn, $alimentColumn] = $this->getRecetteAlimentColumns();
        $stmt = $this->getDb()->prepare("
            UPDATE recette_aliment
            SET quantite = :qte
            WHERE `{$recetteColumn}` = :id_recette AND `{$alimentColumn}` = :id_aliment
        ");

        foreach ($nouvellesQuantites as $alimentId => $qte) {
            $stmt->execute([
                'qte' => (float) $qte,
                'id_recette' => $recetteId,
                'id_aliment' => (int) $alimentId,
            ]);
        }
    }

    public function getStatistiquesNutritionnelles()
    {
        $recettes = $this->getAll();
        if (empty($recettes)) {
            return null;
        }

        $totaux = ['calories' => 0, 'proteines' => 0, 'glucides' => 0, 'lipides' => 0, 'fibres' => 0];
        $plusCalorique = null;
        $moinsCalorique = null;
        $nbValides = 0;

        foreach ($recettes as $r) {
            $nutrition = $this->calculerNutritionTotale((int) $r['id']);
            if ($nutrition['calories'] <= 0) {
                continue;
            }

            $nbValides++;
            $totaux['calories'] += $nutrition['calories'];
            $totaux['proteines'] += $nutrition['proteines'];
            $totaux['glucides'] += $nutrition['glucides'];
            $totaux['lipides'] += $nutrition['lipides'];
            $totaux['fibres'] += $nutrition['fibres'];

            if ($plusCalorique === null || $nutrition['calories'] > $plusCalorique['nutrition']['calories']) {
                $plusCalorique = ['recette' => $r, 'nutrition' => $nutrition];
            }

            if ($moinsCalorique === null || $nutrition['calories'] < $moinsCalorique['nutrition']['calories']) {
                $moinsCalorique = ['recette' => $r, 'nutrition' => $nutrition];
            }
        }

        if ($nbValides === 0) {
            return null;
        }

        return [
            'nb_recettes' => count($recettes),
            'nb_valides' => $nbValides,
            'moyennes' => [
                'calories' => round($totaux['calories'] / $nbValides),
                'proteines' => round($totaux['proteines'] / $nbValides, 1),
                'glucides' => round($totaux['glucides'] / $nbValides, 1),
                'lipides' => round($totaux['lipides'] / $nbValides, 1),
                'fibres' => round($totaux['fibres'] / $nbValides, 1),
            ],
            'plus_calorique' => $plusCalorique,
            'moins_calorique' => $moinsCalorique,
        ];
    }
}
