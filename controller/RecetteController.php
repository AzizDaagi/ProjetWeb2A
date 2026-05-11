<?php

require_once __DIR__ . '/../model/Database.php';
require_once __DIR__ . '/../model/Recette.php';
require_once __DIR__ . '/../model/aliment.php';

class RecetteController
{
    private $db;
    private $recetteModel;
    private $alimentModel;

    public function __construct($pdo = null)
    {
        $this->db = $pdo instanceof PDO ? $pdo : Database::getConnection();
        $this->recetteModel = new Recette($this->db);
        $this->alimentModel = new Aliment($this->db);
    }

    public function listRecettes()
    {
        return $this->recetteModel->getAll();
    }

    public function countRecettes()
    {
        return $this->recetteModel->countAll();
    }

    public function getLatestRecettes($limit = 5)
    {
        return $this->recetteModel->getLatest($limit);
    }

    public function getRecette($id)
    {
        return $this->recetteModel->findById($id);
    }

    public function listAliments()
    {
        try {
            return $this->normalizeAlimentRows($this->alimentModel->getForRecipeSelection());
        } catch (Throwable $exception) {
            return [];
        }
    }

    public function getAliment($id)
    {
        try {
            return $this->normalizeAlimentRow($this->alimentModel->findById($id));
        } catch (Throwable $exception) {
            return null;
        }
    }

    public function getAlimentsByRecette($idRecette)
    {
        return $this->recetteModel->getAlimentsByRecette($idRecette);
    }

    public function addRecette($nom, $description, $tempsPreparation, $niveauDifficulte, $imageUrl = null, $alimentsQuantites = [])
    {
        return $this->recetteModel->create(
            $nom,
            $description,
            $tempsPreparation,
            $niveauDifficulte,
            $imageUrl,
            (array) $alimentsQuantites
        );
    }

    public function updateRecette($id, $nom, $description, $tempsPreparation, $niveauDifficulte, $imageUrl = null, $alimentsQuantites = [])
    {
        return $this->recetteModel->updateRecipe(
            $id,
            $nom,
            $description,
            $tempsPreparation,
            $niveauDifficulte,
            $imageUrl,
            (array) $alimentsQuantites
        );
    }

    public function deleteRecette($id)
    {
        return $this->recetteModel->delete($id);
    }

    public function checkEquilibreNutritionnel($alimentsQuantites)
    {
        $alimentsQuantites = $this->sanitizeQuantitesMap($alimentsQuantites);
        if (empty($alimentsQuantites)) {
            return [];
        }

        $totalProteines = 0.0;
        $totalGlucides = 0.0;
        $totalLipides = 0.0;

        foreach ($this->fetchAlimentsByIds(array_keys($alimentsQuantites)) as $aliment) {
            $quantite = (float) ($alimentsQuantites[$aliment['id']] ?? 0);
            $totalProteines += ((float) ($aliment['proteines'] ?? 0) * $quantite) / 100;
            $totalGlucides += ((float) ($aliment['glucides'] ?? 0) * $quantite) / 100;
            $totalLipides += ((float) ($aliment['lipides'] ?? 0) * $quantite) / 100;
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
            $warnings[] = "Recette trop pauvre en proteines (" . round($pctProteines, 1) . "%). L ideal est entre 15% et 35%.";
        } elseif ($pctProteines > 35) {
            $warnings[] = "Recette trop riche en proteines (" . round($pctProteines, 1) . "%). L ideal est entre 15% et 35%.";
        }

        if ($pctGlucides < 40) {
            $warnings[] = "Recette trop pauvre en glucides (" . round($pctGlucides, 1) . "%). L ideal est entre 40% et 60%.";
        } elseif ($pctGlucides > 60) {
            $warnings[] = "Recette trop riche en glucides (" . round($pctGlucides, 1) . "%). L ideal est entre 40% et 60%.";
        }

        if ($pctLipides < 20) {
            $warnings[] = "Recette trop pauvre en lipides (" . round($pctLipides, 1) . "%). L ideal est entre 20% et 35%.";
        } elseif ($pctLipides > 35) {
            $warnings[] = "Recette trop riche en lipides (" . round($pctLipides, 1) . "%). L ideal est entre 20% et 35%.";
        }

        return $warnings;
    }

    public function calculerNutritionTotale($idRecette)
    {
        return $this->recetteModel->calculateNutritionTotals($idRecette);
    }

    public function generateRecipeFromConstraints($maxKcal, $minProt, $maxLipides, $dietType)
    {
        $aliments = $this->listAliments();
        if (empty($aliments)) {
            return false;
        }

        $filtered = [];
        foreach ($aliments as $aliment) {
            $nom = $this->lowercase((string) ($aliment['nom'] ?? ''));
            $type = $this->lowercase((string) ($aliment['type'] ?? ''));

            if ($dietType === 'vegetarien') {
                if (
                    strpos($nom, 'viande') !== false ||
                    strpos($type, 'viande') !== false ||
                    strpos($nom, 'poisson') !== false ||
                    strpos($type, 'poisson') !== false ||
                    strpos($nom, 'poulet') !== false ||
                    strpos($type, 'poulet') !== false ||
                    strpos($nom, 'boeuf') !== false ||
                    strpos($nom, 'porc') !== false
                ) {
                    continue;
                }
            }

            if ($dietType === 'sans_gluten') {
                if (
                    strpos($nom, 'ble') !== false ||
                    strpos($type, 'ble') !== false ||
                    strpos($nom, 'pate') !== false ||
                    strpos($type, 'pate') !== false ||
                    strpos($nom, 'pain') !== false ||
                    strpos($type, 'pain') !== false ||
                    strpos($nom, 'farine') !== false
                ) {
                    continue;
                }
            }

            $filtered[] = $aliment;
        }

        if (empty($filtered)) {
            return false;
        }

        $proteins = [];
        $carbs = [];
        $veggies = [];

        foreach ($filtered as $aliment) {
            if ((float) ($aliment['proteines'] ?? 0) >= 10 && (float) ($aliment['proteines'] ?? 0) >= (float) ($aliment['glucides'] ?? 0)) {
                $proteins[] = $aliment;
            } elseif ((float) ($aliment['glucides'] ?? 0) >= 15) {
                $carbs[] = $aliment;
            } else {
                $veggies[] = $aliment;
            }
        }

        if (empty($proteins)) {
            $proteins = $filtered;
        }
        if (empty($carbs)) {
            $carbs = $filtered;
        }
        if (empty($veggies)) {
            $veggies = $filtered;
        }

        $bestCombo = null;
        $bestScore = INF;

        for ($i = 0; $i < 100; $i++) {
            $protein = $proteins[array_rand($proteins)];
            $carb = $carbs[array_rand($carbs)];
            $veggie = $veggies[array_rand($veggies)];

            $quantiteProtein = 150;
            $quantiteCarb = 100;
            $quantiteVeggie = 150;

            $calcProteines = ((float) $protein['proteines'] * $quantiteProtein / 100)
                + ((float) $carb['proteines'] * $quantiteCarb / 100)
                + ((float) $veggie['proteines'] * $quantiteVeggie / 100);

            if ($calcProteines < $minProt) {
                $quantiteProtein += 50;
            }

            $calcCalories = ((float) $protein['calories'] * $quantiteProtein / 100)
                + ((float) $carb['calories'] * $quantiteCarb / 100)
                + ((float) $veggie['calories'] * $quantiteVeggie / 100);

            if ($calcCalories > $maxKcal) {
                $quantiteCarb = max(0, $quantiteCarb - 40);
                $quantiteProtein = max(50, $quantiteProtein - 20);
            }

            $calcProteines = ((float) $protein['proteines'] * $quantiteProtein / 100)
                + ((float) $carb['proteines'] * $quantiteCarb / 100)
                + ((float) $veggie['proteines'] * $quantiteVeggie / 100);
            $calcLipides = ((float) $protein['lipides'] * $quantiteProtein / 100)
                + ((float) $carb['lipides'] * $quantiteCarb / 100)
                + ((float) $veggie['lipides'] * $quantiteVeggie / 100);
            $calcCalories = ((float) $protein['calories'] * $quantiteProtein / 100)
                + ((float) $carb['calories'] * $quantiteCarb / 100)
                + ((float) $veggie['calories'] * $quantiteVeggie / 100);
            $calcGlucides = ((float) $protein['glucides'] * $quantiteProtein / 100)
                + ((float) $carb['glucides'] * $quantiteCarb / 100)
                + ((float) $veggie['glucides'] * $quantiteVeggie / 100);
            $calcFibres = ((float) ($protein['fibres'] ?? 0) * $quantiteProtein / 100)
                + ((float) ($carb['fibres'] ?? 0) * $quantiteCarb / 100)
                + ((float) ($veggie['fibres'] ?? 0) * $quantiteVeggie / 100);

            $score = 0;
            if ($calcCalories > $maxKcal) {
                $score += ($calcCalories - $maxKcal) * 2;
            }
            if ($calcProteines < $minProt) {
                $score += ($minProt - $calcProteines) * 5;
            }
            if ($calcLipides > $maxLipides) {
                $score += ($calcLipides - $maxLipides) * 3;
            }

            if ($score < $bestScore) {
                $bestScore = $score;
                $bestCombo = [
                    'aliments' => [
                        (int) $protein['id'] => ['aliment' => $protein, 'quantite' => $quantiteProtein],
                        (int) $carb['id'] => ['aliment' => $carb, 'quantite' => $quantiteCarb],
                        (int) $veggie['id'] => ['aliment' => $veggie, 'quantite' => $quantiteVeggie],
                    ],
                    'totaux' => [
                        'calories' => round($calcCalories),
                        'proteines' => round($calcProteines, 1),
                        'lipides' => round($calcLipides, 1),
                        'glucides' => round($calcGlucides, 1),
                        'fibres' => round($calcFibres, 1),
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
            if ($data['quantite'] <= 0) {
                continue;
            }

            if (isset($finalAliments[$id])) {
                $finalAliments[$id]['quantite'] += $data['quantite'];
            } else {
                $finalAliments[$id] = $data;
            }
        }

        $bestCombo['aliments'] = array_values($finalAliments);
        return $bestCombo;
    }

    public function optimiserRecette($idRecette, $objectif = 'equilibre_global')
    {
        $aliments = $this->getAlimentsByRecette($idRecette);
        if (empty($aliments)) {
            return false;
        }

        $avant = ['calories' => 0, 'proteines' => 0, 'glucides' => 0, 'lipides' => 0, 'fibres' => 0];
        foreach ($aliments as $aliment) {
            $quantite = (float) ($aliment['quantite'] ?: 0);
            $avant['calories'] += ((float) ($aliment['calories'] ?? 0) * $quantite) / 100;
            $avant['proteines'] += ((float) ($aliment['proteines'] ?? 0) * $quantite) / 100;
            $avant['glucides'] += ((float) ($aliment['glucides'] ?? 0) * $quantite) / 100;
            $avant['lipides'] += ((float) ($aliment['lipides'] ?? 0) * $quantite) / 100;
            $avant['fibres'] += ((float) ($aliment['fibres'] ?? 0) * $quantite) / 100;
        }

        $calProt = $avant['proteines'] * 4;
        $calGluc = $avant['glucides'] * 4;
        $calLip = $avant['lipides'] * 9;
        $calTotal = $calProt + $calGluc + $calLip;

        $pctProt = $calTotal > 0 ? ($calProt / $calTotal * 100) : 0;
        $pctGluc = $calTotal > 0 ? ($calGluc / $calTotal * 100) : 0;
        $pctLip = $calTotal > 0 ? ($calLip / $calTotal * 100) : 0;

        $ecarts = [];
        if ($pctProt < 15) {
            $ecarts[] = ['type' => 'prot_faible', 'label' => 'Proteines trop faibles (' . round($pctProt, 1) . '% < 15%)'];
        }
        if ($pctProt > 35) {
            $ecarts[] = ['type' => 'prot_eleve', 'label' => 'Proteines trop elevees (' . round($pctProt, 1) . '% > 35%)'];
        }
        if ($pctGluc < 40) {
            $ecarts[] = ['type' => 'gluc_faible', 'label' => 'Glucides insuffisants (' . round($pctGluc, 1) . '% < 40%)'];
        }
        if ($pctGluc > 60) {
            $ecarts[] = ['type' => 'gluc_eleve', 'label' => 'Glucides excessifs (' . round($pctGluc, 1) . '% > 60%)'];
        }
        if ($pctLip < 20) {
            $ecarts[] = ['type' => 'lip_faible', 'label' => 'Lipides insuffisants (' . round($pctLip, 1) . '% < 20%)'];
        }
        if ($pctLip > 35) {
            $ecarts[] = ['type' => 'lip_eleve', 'label' => 'Lipides trop eleves (' . round($pctLip, 1) . '% > 35%)'];
        }
        if ($avant['fibres'] < 5) {
            $ecarts[] = ['type' => 'fibres_faible', 'label' => 'Fibres insuffisantes (' . round($avant['fibres'], 1) . 'g < 5g)'];
        }

        $nouvellesQuantites = [];
        foreach ($aliments as $aliment) {
            $idAliment = (int) $aliment['id'];
            $quantite = (float) ($aliment['quantite'] ?: 100);

            $protPer100 = (float) ($aliment['proteines'] ?? 0);
            $glucPer100 = (float) ($aliment['glucides'] ?? 0);
            $lipPer100 = (float) ($aliment['lipides'] ?? 0);
            $fibrePer100 = (float) ($aliment['fibres'] ?? 0);
            $calPer100 = (float) ($aliment['calories'] ?? 0);

            $isHighProt = $protPer100 >= 15;
            $isHighLip = $lipPer100 >= 10;
            $isHighFibre = $fibrePer100 >= 3;
            $isHighCarb = $glucPer100 >= 20 && $protPer100 < 10;

            switch ($objectif) {
                case 'plus_proteines':
                    if ($isHighProt) {
                        $quantite = min($quantite * 1.5, $quantite + 80);
                    }
                    if ($isHighCarb) {
                        $quantite = max($quantite * 0.75, 30);
                    }
                    break;

                case 'moins_lipides':
                    if ($isHighLip) {
                        $quantite = max($quantite * 0.55, 20);
                    }
                    if ($isHighProt && !$isHighLip) {
                        $quantite = min($quantite * 1.2, $quantite + 40);
                    }
                    break;

                case 'plus_fibres':
                    if ($isHighFibre) {
                        $quantite = min($quantite * 1.6, $quantite + 100);
                    }
                    if ($calPer100 > 200 && !$isHighFibre) {
                        $quantite = max($quantite * 0.8, 30);
                    }
                    break;

                case 'equilibre_global':
                default:
                    if ($isHighProt && $pctProt < 20) {
                        $quantite = min($quantite * 1.4, $quantite + 60);
                    }
                    if ($isHighCarb && $pctGluc < 40) {
                        $quantite = min($quantite * 1.3, $quantite + 50);
                    }
                    if ($isHighLip && $pctLip > 30) {
                        $quantite = max($quantite * 0.65, 20);
                    }
                    if ($isHighFibre && $avant['fibres'] < 5) {
                        $quantite = min($quantite * 1.5, $quantite + 60);
                    }
                    break;
            }

            $nouvellesQuantites[$idAliment] = round($quantite);
        }

        $apres = ['calories' => 0, 'proteines' => 0, 'glucides' => 0, 'lipides' => 0, 'fibres' => 0];
        foreach ($aliments as $aliment) {
            $quantite = $nouvellesQuantites[(int) $aliment['id']] ?? (float) ($aliment['quantite'] ?? 0);
            $apres['calories'] += ((float) ($aliment['calories'] ?? 0) * $quantite) / 100;
            $apres['proteines'] += ((float) ($aliment['proteines'] ?? 0) * $quantite) / 100;
            $apres['glucides'] += ((float) ($aliment['glucides'] ?? 0) * $quantite) / 100;
            $apres['lipides'] += ((float) ($aliment['lipides'] ?? 0) * $quantite) / 100;
            $apres['fibres'] += ((float) ($aliment['fibres'] ?? 0) * $quantite) / 100;
        }

        foreach ($avant as $key => $value) {
            $avant[$key] = round($value, 1);
        }
        foreach ($apres as $key => $value) {
            $apres[$key] = round($value, 1);
        }

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

    public function appliquerOptimisation($idRecette, $nouvellesQuantites)
    {
        return $this->recetteModel->applyOptimisation($idRecette, (array) $nouvellesQuantites);
    }

    public function getStatistiquesNutritionnelles()
    {
        $recettes = $this->listRecettes();
        if (empty($recettes)) {
            return null;
        }

        $totaux = ['calories' => 0, 'proteines' => 0, 'glucides' => 0, 'lipides' => 0, 'fibres' => 0];
        $plusCalorique = null;
        $moinsCalorique = null;
        $nbValides = 0;

        foreach ($recettes as $recette) {
            $nutrition = $this->calculerNutritionTotale($recette['id']);
            if (($nutrition['calories'] ?? 0) <= 0) {
                continue;
            }

            $nbValides++;
            $totaux['calories'] += $nutrition['calories'];
            $totaux['proteines'] += $nutrition['proteines'];
            $totaux['glucides'] += $nutrition['glucides'];
            $totaux['lipides'] += $nutrition['lipides'];
            $totaux['fibres'] += $nutrition['fibres'];

            if ($plusCalorique === null || $nutrition['calories'] > $plusCalorique['nutrition']['calories']) {
                $plusCalorique = ['recette' => $recette, 'nutrition' => $nutrition];
            }

            if ($moinsCalorique === null || $nutrition['calories'] < $moinsCalorique['nutrition']['calories']) {
                $moinsCalorique = ['recette' => $recette, 'nutrition' => $nutrition];
            }
        }

        if ($nbValides === 0 || $plusCalorique === null || $moinsCalorique === null) {
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

    private function fetchAlimentsByIds(array $ids)
    {
        try {
            return $this->normalizeAlimentRows($this->alimentModel->findManyByIds($ids));
        } catch (Throwable $exception) {
            return [];
        }
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
            $normalizedRow = $this->normalizeAlimentRow($row);
            if ($normalizedRow !== null) {
                $normalized[] = $normalizedRow;
            }
        }

        return $normalized;
    }

    private function normalizeAlimentRow($row)
    {
        if (!is_array($row)) {
            return null;
        }

        $row['image_url'] = $this->normalizeImagePath($row['image_url'] ?? null);
        return $row;
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

    private function lowercase($value)
    {
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($value, 'UTF-8');
        }

        return strtolower($value);
    }
}
