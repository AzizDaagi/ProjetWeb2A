<?php
require_once __DIR__ . '/../model/Recette.php';
require_once __DIR__ . '/../model/Database.php';

class RecetteController {
    private $db;

    public function __construct($pdo = null) {
        $this->db = $pdo ?: Database::getConnection();
    }

    public function listRecettes() {
        $query = $this->db->query("SELECT * FROM recettes");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countRecettes() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM recettes");
        return $query->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getLatestRecettes($limit = 5) {
        $query = $this->db->query("SELECT * FROM recettes ORDER BY id DESC LIMIT $limit");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecette($id) {
        $query = $this->db->prepare("SELECT * FROM recettes WHERE id = :id");
        $query->execute(['id' => $id]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    // Récupérer les aliments associés à une recette avec leur quantité
    public function getAlimentsByRecette($recette_id) {
        $query = $this->db->prepare("
            SELECT a.*, ra.quantite FROM aliments a
            JOIN recette_aliment ra ON a.id = ra.id_aliment
            WHERE ra.id_recette = :id_recette
        ");
        $query->execute(['id_recette' => $recette_id]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addRecette($nom, $description, $temps_preparation, $difficulte, $image_url = null, $aliments_quantites = []) {
        $query = $this->db->prepare("INSERT INTO recettes (nom, description, temps_preparation, niveau_difficulte, image_url) VALUES (:nom, :description, :temps_preparation, :niveau_difficulte, :image_url)");
        $query->execute([
            'nom'              => $nom,
            'description'      => $description,
            'temps_preparation'=> $temps_preparation,
            'niveau_difficulte'=> $difficulte,
            'image_url'        => $image_url
        ]);

        $recetteId = $this->db->lastInsertId();

        // Insérer les aliments associés avec leur quantité
        if (!empty($aliments_quantites)) {
            $stmt = $this->db->prepare("INSERT INTO recette_aliment (id_recette, id_aliment, quantite) VALUES (:id_recette, :id_aliment, :quantite)");
            foreach ($aliments_quantites as $aliment_id => $quantite) {
                $stmt->execute([
                    'id_recette' => $recetteId,
                    'id_aliment' => $aliment_id,
                    'quantite'   => $quantite
                ]);
            }
        }
    }

    public function updateRecette($id, $nom, $description, $temps_preparation, $difficulte, $image_url = null, $aliments_quantites = []) {
        $query = $this->db->prepare("UPDATE recettes SET nom = :nom, description = :description, temps_preparation = :temps_preparation, niveau_difficulte = :niveau_difficulte, image_url = :image_url WHERE id = :id");
        $query->execute([
            'nom'              => $nom,
            'description'      => $description,
            'temps_preparation'=> $temps_preparation,
            'niveau_difficulte'=> $difficulte,
            'image_url'        => $image_url,
            'id'               => $id
        ]);

        // Mettre à jour les aliments associés (supprimer puis recréer)
        $del = $this->db->prepare("DELETE FROM recette_aliment WHERE id_recette = :id_recette");
        $del->execute(['id_recette' => $id]);

        if (!empty($aliments_quantites)) {
            $stmt = $this->db->prepare("INSERT INTO recette_aliment (id_recette, id_aliment, quantite) VALUES (:id_recette, :id_aliment, :quantite)");
            foreach ($aliments_quantites as $aliment_id => $quantite) {
                $stmt->execute([
                    'id_recette' => $id,
                    'id_aliment' => $aliment_id,
                    'quantite'   => $quantite
                ]);
            }
        }
    }

    public function deleteRecette($id) {
        $query = $this->db->prepare("DELETE FROM recettes WHERE id = :id");
        $query->execute(['id' => $id]);
    }

    public function checkEquilibreNutritionnel($aliments_quantites) {
        if (empty($aliments_quantites)) {
            return []; // Aucune alerte si pas d'aliments
        }

        $totalProteines = 0;
        $totalGlucides = 0;
        $totalLipides = 0;

        $ids = array_keys($aliments_quantites);
        $in = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $this->db->prepare("SELECT id, proteines, glucides, lipides FROM aliments WHERE id IN ($in)");
        $stmt->execute($ids);
        $aliments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($aliments as $aliment) {
            $qte = isset($aliments_quantites[$aliment['id']]) ? (float)$aliments_quantites[$aliment['id']] : 0;
            $totalProteines += ((float)$aliment['proteines'] * $qte) / 100;
            $totalGlucides += ((float)$aliment['glucides'] * $qte) / 100;
            $totalLipides += ((float)$aliment['lipides'] * $qte) / 100;
        }

        // Calcul de la répartition calorique
        $calProteines = $totalProteines * 4;
        $calGlucides = $totalGlucides * 4;
        $calLipides = $totalLipides * 9;

        $totalCalories = $calProteines + $calGlucides + $calLipides;

        if ($totalCalories == 0) {
            return []; // Pas de macros, pas d'alerte
        }

        $pctProteines = ($calProteines / $totalCalories) * 100;
        $pctGlucides = ($calGlucides / $totalCalories) * 100;
        $pctLipides = ($calLipides / $totalCalories) * 100;

        $warnings = [];

        // Protéines entre 15% et 35%
        if ($pctProteines < 15) {
            $warnings[] = "Recette trop pauvre en protéines (" . round($pctProteines, 1) . "%). L'idéal est entre 15% et 35%.";
        } elseif ($pctProteines > 35) {
            $warnings[] = "Recette trop riche en protéines (" . round($pctProteines, 1) . "%). L'idéal est entre 15% et 35%.";
        }

        // Glucides entre 40% et 60%
        if ($pctGlucides < 40) {
            $warnings[] = "Recette trop pauvre en glucides (" . round($pctGlucides, 1) . "%). L'idéal est entre 40% et 60%.";
        } elseif ($pctGlucides > 60) {
            $warnings[] = "Recette trop riche en glucides (" . round($pctGlucides, 1) . "%). L'idéal est entre 40% et 60%.";
        }

        // Lipides entre 20% et 35%
        if ($pctLipides < 20) {
            $warnings[] = "Recette trop pauvre en lipides (" . round($pctLipides, 1) . "%). L'idéal est entre 20% et 35%.";
        } elseif ($pctLipides > 35) {
            $warnings[] = "Recette trop riche en lipides (" . round($pctLipides, 1) . "%). L'idéal est entre 20% et 35%.";
        }

        return $warnings;
    }

    public function calculerNutritionTotale($recette_id) {
        $aliments = $this->getAlimentsByRecette($recette_id);
        
        $totaux = [
            'calories' => 0,
            'proteines' => 0,
            'glucides' => 0,
            'lipides' => 0,
            'fibres' => 0,
            'sucre_g' => 0
        ];

        foreach ($aliments as $aliment) {
            $qte = (float)($aliment['quantite'] ?: 0);
            
            $totaux['calories'] += ((float)$aliment['calories'] * $qte) / 100;
            $totaux['proteines'] += ((float)$aliment['proteines'] * $qte) / 100;
            $totaux['glucides'] += ((float)$aliment['glucides'] * $qte) / 100;
            $totaux['lipides'] += ((float)$aliment['lipides'] * $qte) / 100;
            
            // Si fibres existe dans la table
            if (isset($aliment['fibres'])) {
                $totaux['fibres'] += ((float)$aliment['fibres'] * $qte) / 100;
            }
            
            // Si sucre_g existe dans la table
            if (isset($aliment['sucre_g'])) {
                $totaux['sucre_g'] += ((float)$aliment['sucre_g'] * $qte) / 100;
            }
        }
        
        return $totaux;
    }

    public function generateRecipeFromConstraints($maxKcal, $minProt, $maxLipides, $dietType) {
        $query = "SELECT * FROM aliments";
        $aliments = $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);

        // Filtrage selon le régime
        $filtered = [];
        foreach ($aliments as $al) {
            $nom = mb_strtolower($al['nom'], 'UTF-8');
            $type = mb_strtolower($al['type'], 'UTF-8');
            
            if ($dietType === 'vegetarien') {
                if (strpos($nom, 'viande') !== false || strpos($type, 'viande') !== false ||
                    strpos($nom, 'poisson') !== false || strpos($type, 'poisson') !== false ||
                    strpos($nom, 'poulet') !== false || strpos($type, 'poulet') !== false ||
                    strpos($nom, 'boeuf') !== false || strpos($nom, 'porc') !== false) {
                    continue; // Exclure viandes
                }
            }
            if ($dietType === 'sans_gluten') {
                if (strpos($nom, 'blé') !== false || strpos($type, 'blé') !== false ||
                    strpos($nom, 'pâte') !== false || strpos($type, 'pâte') !== false ||
                    strpos($nom, 'pain') !== false || strpos($type, 'pain') !== false ||
                    strpos($nom, 'farine') !== false) {
                    continue; // Exclure gluten
                }
            }
            $filtered[] = $al;
        }

        // Si aucun aliment dispo, retourner false
        if (empty($filtered)) {
            return false;
        }

        // Catégorisation basique (Heuristique)
        $proteins = [];
        $carbs = [];
        $veggies = [];

        foreach ($filtered as $al) {
            if ($al['proteines'] >= 10 && $al['proteines'] >= $al['glucides']) {
                $proteins[] = $al;
            } elseif ($al['glucides'] >= 15) {
                $carbs[] = $al;
            } else {
                // Le reste (légumes, autres, faibles en calories)
                $veggies[] = $al;
            }
        }

        if (empty($proteins)) $proteins = $filtered;
        if (empty($carbs)) $carbs = $filtered;
        if (empty($veggies)) $veggies = $filtered;

        $bestCombo = null;
        $bestScore = INF;

        // Boucle pour essayer 100 combinaisons aléatoires
        for ($i = 0; $i < 100; $i++) {
            $p = $proteins[array_rand($proteins)];
            $c = $carbs[array_rand($carbs)];
            $v = $veggies[array_rand($veggies)];

            // Quantités de base
            $qP = 150;
            $qC = 100;
            $qV = 150;

            // Ajustement basique
            $calcP = ($p['proteines'] * $qP/100) + ($c['proteines'] * $qC/100) + ($v['proteines'] * $qV/100);
            if ($calcP < $minProt) {
                $qP += 50; // Ajouter des protéines
            }

            $calcK = ($p['calories'] * $qP/100) + ($c['calories'] * $qC/100) + ($v['calories'] * $qV/100);
            if ($calcK > $maxKcal) {
                $qC -= 40; // Réduire les glucides pour baisser les calories
                if ($qC < 0) $qC = 0;
                $qP -= 20;
                if ($qP < 50) $qP = 50;
            }

            // Recalcul des valeurs finales
            $calcP = ($p['proteines'] * $qP/100) + ($c['proteines'] * $qC/100) + ($v['proteines'] * $qV/100);
            $calcL = ($p['lipides'] * $qP/100) + ($c['lipides'] * $qC/100) + ($v['lipides'] * $qV/100);
            $calcK = ($p['calories'] * $qP/100) + ($c['calories'] * $qC/100) + ($v['calories'] * $qV/100);
            $calcG = ($p['glucides'] * $qP/100) + ($c['glucides'] * $qC/100) + ($v['glucides'] * $qV/100);
            $calcF = (($p['fibres'] ?? 0) * $qP/100) + (($c['fibres'] ?? 0) * $qC/100) + (($v['fibres'] ?? 0) * $qV/100);

            // Évaluation (Pénalités si on dépasse les contraintes)
            $score = 0;
            if ($calcK > $maxKcal) $score += ($calcK - $maxKcal) * 2;
            if ($calcP < $minProt) $score += ($minProt - $calcP) * 5;
            if ($calcL > $maxLipides) $score += ($calcL - $maxLipides) * 3;

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
                        'fibres' => round($calcF, 1)
                    ],
                    'score' => $score
                ];
            }
            
            // Si le score est 0, on a trouvé une solution parfaite, on peut s'arrêter
            if ($score === 0) break;
        }

        // Nettoyage : retirer les aliments en double s'il y a lieu, fusionner les quantités
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
        $bestCombo['aliments'] = array_values($finalAliments); // re-index

        return $bestCombo;
    }

    /**
     * Optimise les quantités d'une recette existante vers un objectif nutritionnel.
     * Objectifs possibles : equilibre_global | plus_proteines | moins_lipides | plus_fibres
     * Retourne : ['avant' => [...], 'apres' => [...], 'nouvelles_quantites' => [id=>qte], 'analyse' => [...]]
     */
    public function optimiserRecette($id_recette, $objectif = 'equilibre_global') {
        $aliments = $this->getAlimentsByRecette($id_recette);
        if (empty($aliments)) return false;

        // === 1. Calcul de l'état actuel ===
        // Note: PDO retourne "" (chaîne vide) pour les FLOAT NULL, pas null
        // On utilise ?: pour couvrir null, "" et 0
        $avant = ['calories'=>0,'proteines'=>0,'glucides'=>0,'lipides'=>0,'fibres'=>0];
        foreach ($aliments as $a) {
            $q = (float)($a['quantite'] ?: 0);
            $avant['calories']  += ($a['calories']  * $q) / 100;
            $avant['proteines'] += ($a['proteines'] * $q) / 100;
            $avant['glucides']  += ($a['glucides']  * $q) / 100;
            $avant['lipides']   += ($a['lipides']   * $q) / 100;
            $avant['fibres']    += (($a['fibres'] ?: 0) * $q) / 100;
        }

        // === 2. Analyse des écarts ===
        $calProt = $avant['proteines'] * 4;
        $calGluc = $avant['glucides']  * 4;
        $calLip  = $avant['lipides']   * 9;
        $calTotal = $calProt + $calGluc + $calLip;

        $pctProt = $calTotal > 0 ? ($calProt / $calTotal * 100) : 0;
        $pctGluc = $calTotal > 0 ? ($calGluc / $calTotal * 100) : 0;
        $pctLip  = $calTotal > 0 ? ($calLip  / $calTotal * 100) : 0;

        $ecarts = [];
        if ($pctProt < 15) $ecarts[] = ['type' => 'prot_faible', 'label' => 'Protéines trop faibles ('  . round($pctProt,1) . '% < 15%)'];
        if ($pctProt > 35) $ecarts[] = ['type' => 'prot_eleve', 'label' => 'Protéines trop élevées ('  . round($pctProt,1) . '% > 35%)'];
        if ($pctGluc < 40) $ecarts[] = ['type' => 'gluc_faible','label' => 'Glucides insuffisants ('    . round($pctGluc,1) . '% < 40%)'];
        if ($pctGluc > 60) $ecarts[] = ['type' => 'gluc_eleve', 'label' => 'Glucides excessifs ('       . round($pctGluc,1) . '% > 60%)'];
        if ($pctLip  < 20) $ecarts[] = ['type' => 'lip_faible', 'label' => 'Lipides insuffisants ('     . round($pctLip,1)  . '% < 20%)'];
        if ($pctLip  > 35) $ecarts[] = ['type' => 'lip_eleve',  'label' => 'Lipides trop élevés ('      . round($pctLip,1)  . '% > 35%)'];
        if ($avant['fibres'] < 5) $ecarts[] = ['type' => 'fibres_faible','label' => 'Fibres insuffisantes (' . round($avant['fibres'],1) . 'g < 5g)'];

        // === 3. Construction des nouvelles quantités selon l'objectif ===
        $nouvellesQuantites = [];

        foreach ($aliments as $a) {
            $id = $a['id'];
            // Si la quantite est vide/null/0, on utilise 100g par défaut pour l'optimisation
            $q  = (float)($a['quantite'] ?: 100);

            $protPer100 = (float)$a['proteines'];
            $glucPer100 = (float)$a['glucides'];
            $lipPer100  = (float)$a['lipides'];
            $fibrePer100= (float)($a['fibres'] ?? 0);
            $calPer100  = (float)$a['calories'];

            $isHighProt  = $protPer100 >= 15;
            $isHighLip   = $lipPer100  >= 10;
            $isHighFibre = $fibrePer100 >= 3;
            $isHighCarb  = $glucPer100  >= 20 && $protPer100 < 10;

            switch ($objectif) {

                case 'plus_proteines':
                    if ($isHighProt)  $q = min($q * 1.5, $q + 80);  // +50% pour les protéinés
                    if ($isHighCarb)  $q = max($q * 0.75, 30);       // -25% glucides
                    break;

                case 'moins_lipides':
                    if ($isHighLip)   $q = max($q * 0.55, 20);       // -45% aliments gras
                    if ($isHighProt && !$isHighLip) $q = min($q * 1.2, $q + 40); // compenser
                    break;

                case 'plus_fibres':
                    if ($isHighFibre) $q = min($q * 1.6, $q + 100);  // +60% fibres
                    if ($calPer100 > 200 && !$isHighFibre) $q = max($q * 0.8, 30); // réduire dense
                    break;

                case 'equilibre_global':
                default:
                    // Cible : Prot 25%, Gluc 50%, Lip 25%
                    if ($isHighProt && $pctProt < 20)  $q = min($q * 1.4, $q + 60);
                    if ($isHighCarb && $pctGluc < 40)  $q = min($q * 1.3, $q + 50);
                    if ($isHighLip  && $pctLip  > 30)  $q = max($q * 0.65, 20);
                    if ($isHighFibre && $avant['fibres'] < 5) $q = min($q * 1.5, $q + 60);
                    break;
            }

            $nouvellesQuantites[$id] = round($q);
        }

        // === 4. Recalcul des nouvelles valeurs nutritionnelles ===
        $apres = ['calories'=>0,'proteines'=>0,'glucides'=>0,'lipides'=>0,'fibres'=>0];
        foreach ($aliments as $a) {
            $q = $nouvellesQuantites[$a['id']] ?? (float)$a['quantite'];
            $apres['calories']  += ($a['calories']  * $q) / 100;
            $apres['proteines'] += ($a['proteines'] * $q) / 100;
            $apres['glucides']  += ($a['glucides']  * $q) / 100;
            $apres['lipides']   += ($a['lipides']   * $q) / 100;
            $apres['fibres']    += (($a['fibres'] ?? 0) * $q) / 100;
        }

        foreach ($avant as $k => $v) $avant[$k] = round($v, 1);
        foreach ($apres as $k => $v) $apres[$k] = round($v, 1);

        // Pourcentages caloriques APRÈS
        $cpProt = $apres['proteines'] * 4;
        $cpGluc = $apres['glucides']  * 4;
        $cpLip  = $apres['lipides']   * 9;
        $cpTot  = $cpProt + $cpGluc + $cpLip;

        return [
            'avant' => $avant,
            'apres' => $apres,
            'nouvelles_quantites' => $nouvellesQuantites,
            'aliments' => $aliments,
            'ecarts' => $ecarts,
            'pct_avant' => [
                'prot' => round($pctProt, 1),
                'gluc' => round($pctGluc, 1),
                'lip'  => round($pctLip,  1),
            ],
            'pct_apres' => [
                'prot' => $cpTot > 0 ? round($cpProt / $cpTot * 100, 1) : 0,
                'gluc' => $cpTot > 0 ? round($cpGluc / $cpTot * 100, 1) : 0,
                'lip'  => $cpTot > 0 ? round($cpLip  / $cpTot * 100, 1) : 0,
            ],
        ];
    }

    /**
     * Applique une version optimisée en mettant à jour les quantités dans la DB.
     */
    public function appliquerOptimisation($recette_id, $nouvelles_quantites) {
        $recette_id = (int)$recette_id;
        $stmt = $this->db->prepare("UPDATE recette_aliment SET quantite = :qte WHERE id_recette = :id_recette AND id_aliment = :id_aliment");
        foreach ($nouvelles_quantites as $aliment_id => $qte) {
            $stmt->execute([
                'qte'        => (float)$qte,
                'id_recette' => $recette_id,
                'id_aliment' => (int)$aliment_id
            ]);
        }
    }

    /**
     * Calcule les statistiques nutritionnelles globales sur toutes les recettes.
     */
    public function getStatistiquesNutritionnelles() {
        $recettes = $this->listRecettes();

        if (empty($recettes)) {
            return null;
        }

        $totaux = ['calories'=>0,'proteines'=>0,'glucides'=>0,'lipides'=>0,'fibres'=>0];
        $plusCalorique  = null;
        $moinsCalorique = null;
        $nbValides = 0; // recettes avec au moins un aliment renseigné

        foreach ($recettes as $r) {
            $nutrition = $this->calculerNutritionTotale($r['id']);

            // Ignorer les recettes sans aliments associés (calories = 0)
            if ($nutrition['calories'] <= 0) continue;

            $nbValides++;
            $totaux['calories']  += $nutrition['calories'];
            $totaux['proteines'] += $nutrition['proteines'];
            $totaux['glucides']  += $nutrition['glucides'];
            $totaux['lipides']   += $nutrition['lipides'];
            $totaux['fibres']    += $nutrition['fibres'];

            // Plus calorique
            if ($plusCalorique === null || $nutrition['calories'] > $plusCalorique['nutrition']['calories']) {
                $plusCalorique = ['recette' => $r, 'nutrition' => $nutrition];
            }

            // Moins calorique
            if ($moinsCalorique === null || $nutrition['calories'] < $moinsCalorique['nutrition']['calories']) {
                $moinsCalorique = ['recette' => $r, 'nutrition' => $nutrition];
            }
        }

        if ($nbValides === 0) return null;

        // Moyennes arrondies
        $moyennes = [
            'calories'  => round($totaux['calories']  / $nbValides),
            'proteines' => round($totaux['proteines'] / $nbValides, 1),
            'glucides'  => round($totaux['glucides']  / $nbValides, 1),
            'lipides'   => round($totaux['lipides']   / $nbValides, 1),
            'fibres'    => round($totaux['fibres']    / $nbValides, 1),
        ];

        return [
            'nb_recettes'     => count($recettes),
            'nb_valides'      => $nbValides,
            'moyennes'        => $moyennes,
            'plus_calorique'  => $plusCalorique,
            'moins_calorique' => $moinsCalorique,
        ];
    }
}
