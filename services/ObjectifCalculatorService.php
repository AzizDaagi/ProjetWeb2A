<?php

class ObjectifCalculatorService
{
    private $defaultActivite = 'moderate';

    private $sexeOptions = [
        'homme' => 'Homme',
        'femme' => 'Femme',
    ];

    private $activiteOptions = [
        '1_2' => "S\u{00E9}dentaire \u{00E0} l\u{00E9}ger",
        '3_4' => "Mod\u{00E9}r\u{00E9} \u{00E0} actif",
        '5_plus' => "Tr\u{00E8}s actif \u{00E0} extra actif",
    ];

    private $activiteSelectOptions = [
        'sedentary' => "S\u{00E9}dentaire : peu ou pas d'exercice, travail de bureau",
        'light' => "L\u{00E9}ger : exercice l\u{00E9}ger 1 \u{00E0} 3 fois par semaine",
        'moderate' => "Mod\u{00E9}r\u{00E9} : exercice mod\u{00E9}r\u{00E9} 3 \u{00E0} 5 fois par semaine",
        'active' => "Actif : exercice quotidien ou activit\u{00E9} physique r\u{00E9}guli\u{00E8}re",
        'very_active' => "Tr\u{00E8}s actif : exercice intense 6 \u{00E0} 7 fois par semaine",
        'extra_active' => "Extra actif : exercice tr\u{00E8}s intense quotidien ou travail physique",
    ];

    private $activiteDisplayLabels = [
        'sedentary' => "S\u{00E9}dentaire : peu ou pas d'exercice",
        'light' => "L\u{00E9}ger : 1 \u{00E0} 3 fois / semaine",
        'moderate' => "Mod\u{00E9}r\u{00E9} : 3 \u{00E0} 5 fois / semaine",
        'active' => "Actif : exercice quotidien",
        'very_active' => "Tr\u{00E8}s actif : 6 \u{00E0} 7 fois / semaine",
        'extra_active' => "Extra actif : travail physique",
    ];

    private $objectifTypeOptions = [
        'maintien' => 'Maintien',
        'prise_muscle' => 'Prise de muscle',
    ];

    private $activityFactors = [
        'sedentary' => 1.2,
        'light' => 1.375,
        'moderate' => 1.55,
        'active' => 1.725,
        'very_active' => 1.9,
        'extra_active' => 2.0,
    ];

    private $legacyActiviteMap = [
        'faible' => '1_2',
        'moderee' => '3_4',
        'elevee' => '5_plus',
    ];

    private $selectActiviteMap = [
        'sedentary' => '1_2',
        'light' => '1_2',
        'moderate' => '3_4',
        'active' => '3_4',
        'very_active' => '5_plus',
        'extra_active' => '5_plus',
    ];

    private $storedToSelectMap = [
        '1_2' => 'sedentary',
        '3_4' => 'moderate',
        '5_plus' => 'very_active',
    ];

    private $selectToLegacyStorageMap = [
        'sedentary' => 'faible',
        'light' => 'faible',
        'moderate' => 'moderee',
        'active' => 'moderee',
        'very_active' => 'elevee',
        'extra_active' => 'elevee',
    ];

    public function getSexeOptions()
    {
        return $this->sexeOptions;
    }

    public function getActiviteOptions()
    {
        return $this->activiteOptions;
    }

    public function getActiviteSelectOptions()
    {
        return $this->activiteSelectOptions;
    }

    public function getDefaultActivite()
    {
        return $this->defaultActivite;
    }

    public function getObjectifTypeOptions()
    {
        return $this->objectifTypeOptions;
    }

    public function getSexeLabel($sexe)
    {
        return $this->sexeOptions[$sexe] ?? $sexe;
    }

    public function getActiviteLabel($activite)
    {
        if (is_array($activite)) {
            $activite = $this->getActiviteSelectValue($activite);
        }

        if (isset($this->activiteDisplayLabels[$activite])) {
            return $this->activiteDisplayLabels[$activite];
        }

        $activite = $this->normalizeActivite($activite);
        $activite = $this->storedToSelectMap[$activite] ?? $this->defaultActivite;

        return $this->activiteDisplayLabels[$activite] ?? '-';
    }

    public function getActiviteSelectValue($activite)
    {
        if (is_array($activite)) {
            $activityContext = $this->resolveActivityContext($activite);

            return $activityContext['key'] ?? $this->defaultActivite;
        }

        if (isset($this->activiteSelectOptions[$activite])) {
            return $activite;
        }

        $activite = $this->normalizeActivite($activite);

        return $this->storedToSelectMap[$activite] ?? $this->defaultActivite;
    }

    public function getObjectifTypeLabel($objectifType)
    {
        return $this->objectifTypeOptions[$objectifType] ?? $objectifType;
    }

    public function buildObjectifData(array $profile)
    {
        $metrics = $this->calculateNutritionTargets($profile);
        $activite = $this->resolveActiviteStorageValue($profile['activite'] ?? null);

        return [
            'poids' => (float) $profile['poids'],
            'taille' => (float) $profile['taille'],
            'age' => (int) $profile['age'],
            'sexe' => $profile['sexe'],
            'activite' => $activite,
            'objectif_type' => $profile['objectif_type'],
            'calories_cible' => (int) $metrics['calories_cible'],
            'proteines' => (int) $metrics['proteines'],
            'lipides' => (int) $metrics['lipides'],
            'glucides' => (int) $metrics['glucides'],
            'calculation_debug' => [
                'bmr' => (int) $metrics['bmr'],
                'activity_factor' => (float) $metrics['activity_factor'],
                'tdee' => (int) $metrics['tdee'],
                'calories_cible' => (int) $metrics['calories_cible'],
                'activity_key' => $metrics['activity_key'] ?? $this->defaultActivite,
            ],
        ];
    }

    public function calculateMacroTargetsForCalories($calories, $objectifType)
    {
        $calories = max(0, (float) $calories);

        if ($objectifType === 'prise_muscle') {
            $proteines = ($calories * 0.35) / 4;
            $lipides = ($calories * 0.20) / 9;
            $glucides = ($calories * 0.45) / 4;
        } else {
            $proteines = ($calories * 0.30) / 4;
            $lipides = ($calories * 0.25) / 9;
            $glucides = ($calories * 0.45) / 4;
        }

        return [
            'proteines' => max(0, (int) round($proteines)),
            'lipides' => max(0, (int) round($lipides)),
            'glucides' => max(0, (int) round($glucides)),
        ];
    }

    public function normalizeActivite($activite)
    {
        if (isset($this->selectActiviteMap[$activite])) {
            return $this->selectActiviteMap[$activite];
        }

        if (isset($this->legacyActiviteMap[$activite])) {
            return $this->legacyActiviteMap[$activite];
        }

        if (isset($this->activiteOptions[$activite])) {
            return $activite;
        }

        return '3_4';
    }

    public function resolveActiviteStorageValue($activite)
    {
        if (isset($this->activiteSelectOptions[$activite])) {
            return $this->selectToLegacyStorageMap[$activite] ?? 'moderee';
        }

        if (isset($this->legacyActiviteMap[$activite])) {
            $activite = $this->legacyActiviteMap[$activite];
        }

        if (isset($this->activiteOptions[$activite])) {
            return match ($activite) {
                '1_2' => 'faible',
                '3_4' => 'moderee',
                '5_plus' => 'elevee',
                default => 'moderee',
            };
        }

        return 'moderee';
    }

    public function calculateNutritionTargets(array $profile)
    {
        $poids = (float) ($profile['poids'] ?? 0);
        $taille = (float) ($profile['taille'] ?? 0);
        $age = (int) ($profile['age'] ?? 0);
        $sexe = $profile['sexe'] ?? 'homme';
        $objectifType = $profile['objectif_type'] ?? 'maintien';
        $bmr = $this->calculateBmr($poids, $taille, $age, $sexe);
        $activityContext = $this->resolveActivityContext($profile, $bmr, $objectifType);
        $activityFactor = (float) ($activityContext['factor'] ?? 1.2);
        $tdee = isset($activityContext['tdee'])
            ? (float) $activityContext['tdee']
            : ($bmr * $activityFactor);
        $calories = $objectifType === 'prise_muscle'
            ? $tdee + 300
            : $tdee;
        $macros = $this->calculateMacroTargetsForCalories($calories, $objectifType);

        if (!empty($profile['debug_log'])) {
            error_log(sprintf(
                '[ObjectifCalculator] BMR=%s | factor=%s | final_calories=%s | activite=%s',
                round($bmr, 2),
                $activityFactor,
                round($calories),
                $activityContext['key'] ?? $this->defaultActivite
            ));
        }

        return [
            'bmr' => round($bmr),
            'tdee' => round($tdee),
            'activity_factor' => $activityFactor,
            'activity_key' => $activityContext['key'] ?? $this->defaultActivite,
            'calories_cible' => max(0, (int) round($calories)),
            'proteines' => $macros['proteines'],
            'lipides' => $macros['lipides'],
            'glucides' => $macros['glucides'],
        ];
    }

    private function calculateBmr($poids, $taille, $age, $sexe)
    {
        if ($sexe === 'homme') {
            return (10 * $poids) + (6.25 * $taille) - (5 * $age) + 5;
        }

        return (10 * $poids) + (6.25 * $taille) - (5 * $age) - 161;
    }

    private function resolveActivityContext(array $profile, $bmr = null, $objectifType = null)
    {
        $bmr = $bmr ?? $this->calculateBmr(
            (float) ($profile['poids'] ?? 0),
            (float) ($profile['taille'] ?? 0),
            (int) ($profile['age'] ?? 0),
            $profile['sexe'] ?? 'homme'
        );
        $objectifType = $objectifType ?? ($profile['objectif_type'] ?? 'maintien');
        $rawActivite = $profile['activite_input'] ?? $profile['activite'] ?? null;

        if (isset($this->activiteSelectOptions[$rawActivite])) {
            return [
                'key' => $rawActivite,
                'factor' => $this->resolveExactActivityFactor($rawActivite),
            ];
        }

        if (!empty($profile['allow_calorie_activity_inference'])) {
            $storedContext = $this->deriveStoredActivityContext($profile, $bmr, $objectifType);

            if ($storedContext !== null) {
                return $storedContext;
            }
        }

        $storedActivite = $this->normalizeActivite($rawActivite);
        $fallbackKey = $this->storedToSelectMap[$storedActivite] ?? $this->defaultActivite;

        return [
            'key' => $fallbackKey,
            'factor' => $this->resolveExactActivityFactor($fallbackKey),
        ];
    }

    private function deriveStoredActivityContext(array $profile, $bmr, $objectifType)
    {
        $calories = (float) ($profile['calories_cible'] ?? 0);

        if ($bmr <= 0 || $calories <= 0) {
            return null;
        }

        $baseCalories = $calories;
        $planDayIndex = isset($profile['plan_day_index']) ? (int) $profile['plan_day_index'] : null;

        if ($planDayIndex !== null && $planDayIndex >= 0 && $planDayIndex <= 6) {
            $baseCalories -= $this->resolvePlanDayVariation($planDayIndex);
        }

        $storedTdee = $objectifType === 'prise_muscle'
            ? $baseCalories - 300
            : $baseCalories;

        if ($storedTdee <= 0) {
            return null;
        }

        $derivedFactor = $storedTdee / $bmr;
        $nearestKey = $this->findNearestActivityKey($derivedFactor);

        return [
            'key' => $nearestKey,
            'factor' => $this->resolveExactActivityFactor($nearestKey),
            'tdee' => $storedTdee,
        ];
    }

    private function resolvePlanDayVariation(int $index): int
    {
        if ($index === 5) {
            return 300;
        }

        if ($index === 6) {
            return 200;
        }

        return -100;
    }

    private function findNearestActivityKey($factor)
    {
        $closestKey = $this->defaultActivite;
        $smallestDifference = null;

        foreach ($this->activityFactors as $key => $expectedFactor) {
            $difference = abs($expectedFactor - (float) $factor);

            if ($smallestDifference === null || $difference < $smallestDifference) {
                $smallestDifference = $difference;
                $closestKey = $key;
            }
        }

        return $closestKey;
    }

    private function resolveExactActivityFactor($activite)
    {
        return match ($activite) {
            'sedentary' => 1.2,
            'light' => 1.375,
            'moderate' => 1.55,
            'active' => 1.725,
            'very_active' => 1.9,
            'extra_active' => 2.0,
            default => 1.55,
        };
    }
}
