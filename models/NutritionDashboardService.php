<?php

require_once __DIR__ . '/NutritionDashboardModel.php';

class NutritionDashboardService
{
    private $model;

    public function __construct(PDO $pdo)
    {
        $this->model = new NutritionDashboardModel($pdo);
    }

    public function getDashboardSummary(int $userId): array
    {
        $profile = $this->model->getUserProfile($userId);
        $todayData = $this->model->getTodayNutritionData($userId);
        $weeklyAnalysis = $this->getWeeklyAnalysis($userId, 7);
        $healthScore = $this->getHealthScore($userId, $todayData, $weeklyAnalysis, $profile);
        $hydration = $this->buildHydrationSummary($todayData);
        $recommendations = $this->getDailyRecommendations($userId, $todayData, $weeklyAnalysis, $profile);
        $reminder = $this->getSmartReminder($userId, $todayData, $weeklyAnalysis);

        return [
            'today' => $todayData,
            'sugar_today_g' => round((float) ($todayData['sugar_today_g'] ?? 0), 1),
            'sugar_goal_g' => isset($todayData['sugar_goal_g']) ? $todayData['sugar_goal_g'] : null,
            'sugar_status' => $todayData['sugar_status'] ?? 'not_configured',
            'profile' => [
                'name' => $profile['name'] ?? 'Utilisateur',
                'target_calories' => (int) round((float) ($profile['target_calories'] ?? 2000)),
            ],
            'hydration' => $hydration,
            'health_score' => $healthScore,
            'daily_recommendations' => $recommendations,
            'weekly_analysis' => $weeklyAnalysis,
            'smart_reminder' => $reminder,
        ];
    }

    public function getHealthScore(
        int $userId,
        ?array $todayData = null,
        ?array $weeklyAnalysis = null,
        ?array $profile = null
    ): array {
        $todayData = $todayData ?? $this->model->getTodayNutritionData($userId);
        $weeklyAnalysis = $weeklyAnalysis ?? $this->getWeeklyAnalysis($userId, 7);
        $profile = $profile ?? $this->model->getUserProfile($userId);

        $targetCalories = max(1, (float) ($todayData['target_calories'] ?? $profile['target_calories'] ?? 2000));
        $proteinTarget = max(1, (float) ($todayData['protein_target_g'] ?? 75));
        $hydrationTarget = max(1, (int) ($todayData['hydration_target_ml'] ?? 2000));
        $mealCount = (int) ($todayData['meal_count'] ?? 0);
        $totalCalories = (float) ($todayData['total_calories'] ?? 0);
        $proteins = (float) ($todayData['proteins_g'] ?? 0);
        $waterMl = (int) ($todayData['water_ml'] ?? 0);
        $consistencyRate = (int) ($weeklyAnalysis['consistency_rate'] ?? 0);
        $sugarGoal = is_numeric($todayData['sugar_goal_g'] ?? null) ? (float) $todayData['sugar_goal_g'] : null;
        $sugarToday = (float) ($todayData['sugar_today_g'] ?? 0);

        $calorieRatio = $totalCalories / $targetCalories;
        $proteinRatio = $proteins / $proteinTarget;
        $hydrationRatio = $waterMl / $hydrationTarget;

        $calorieScore = 0;

        if ($mealCount === 0) {
            $calorieScore = 0;
        } elseif ($calorieRatio >= 0.9 && $calorieRatio <= 1.1) {
            $calorieScore = 35;
        } elseif ($calorieRatio >= 0.75 && $calorieRatio <= 1.2) {
            $calorieScore = 24;
        } elseif ($calorieRatio >= 0.6 && $calorieRatio <= 1.35) {
            $calorieScore = 14;
        } else {
            $calorieScore = 6;
        }

        $proteinScore = 0;

        if ($proteinRatio >= 1) {
            $proteinScore = 25;
        } elseif ($proteinRatio >= 0.8) {
            $proteinScore = 19;
        } elseif ($proteinRatio >= 0.6) {
            $proteinScore = 10;
        } else {
            $proteinScore = 4;
        }

        $hydrationScore = 0;

        if ($hydrationRatio >= 1) {
            $hydrationScore = 10;
        } elseif ($hydrationRatio >= 0.75) {
            $hydrationScore = 8;
        } elseif ($hydrationRatio >= 0.5) {
            $hydrationScore = 5;
        } elseif ($hydrationRatio > 0) {
            $hydrationScore = 2;
        }

        $consistencyScore = (int) round(min(20, ($consistencyRate / 100) * 20));

        $structureScore = 0;

        if ($mealCount >= 3) {
            $structureScore = 10;
        } elseif ($mealCount === 2) {
            $structureScore = 7;
        } elseif ($mealCount === 1) {
            $structureScore = 3;
        }

        $score = max(0, min(100, $calorieScore + $proteinScore + $hydrationScore + $consistencyScore + $structureScore));

        if ($sugarGoal !== null && $sugarGoal > 0 && $sugarToday > $sugarGoal) {
            $score = max(0, $score - 5);
        }

        if ($score >= 80) {
            $label = 'excellent';
            $summary = 'Journee bien equilibree dans l ensemble.';
        } elseif ($score >= 60) {
            $label = 'correct';
            $summary = 'Base correcte avec quelques ajustements possibles.';
        } elseif ($score >= 40) {
            $label = 'fragile';
            $summary = 'Des ajustements simples peuvent ameliorer la journee.';
        } else {
            $label = 'low';
            $summary = 'Le suivi du jour reste incomplet ou desequilibre.';
        }

        return [
            'score' => $score,
            'label' => $label,
            'summary' => $summary,
            'breakdown' => [
                'calories' => $calorieScore,
                'protein' => $proteinScore,
                'hydration' => $hydrationScore,
                'consistency' => $consistencyScore,
                'structure' => $structureScore,
            ],
        ];
    }

    public function getDailyRecommendations(
        int $userId,
        ?array $todayData = null,
        ?array $weeklyAnalysis = null,
        ?array $profile = null
    ): array {
        $todayData = $todayData ?? $this->model->getTodayNutritionData($userId);
        $weeklyAnalysis = $weeklyAnalysis ?? $this->getWeeklyAnalysis($userId, 7);
        $profile = $profile ?? $this->model->getUserProfile($userId);

        return $this->generateDailyRecommendations($todayData, $weeklyAnalysis, $profile);
    }

    public function getWeeklyAnalysis(int $userId, int $days = 7): array
    {
        $weeklyData = $this->model->getWeeklyNutritionData($userId, $days);

        return $this->generateWeeklyAnalysis($weeklyData, $days);
    }

    public function getSmartReminder(
        int $userId,
        ?array $todayData = null,
        ?array $weeklyAnalysis = null,
        ?DateTimeInterface $now = null
    ): array {
        $todayData = $todayData ?? $this->model->getTodayNutritionData($userId);
        $weeklyAnalysis = $weeklyAnalysis ?? $this->getWeeklyAnalysis($userId, 7);
        $now = $now ?? new DateTimeImmutable('now');
        $alreadySentToday = $this->model->hasReminderBeenSentToday($userId);
        $lastLoggedDate = $this->model->getLastLoggedConsumptionDate($userId);
        $mealCount = (int) ($todayData['meal_count'] ?? 0);
        $currentHour = (int) $now->format('H');
        $twoDaysAgo = $now->modify('-2 days')->format('Y-m-d');

        if ($alreadySentToday) {
            return [
                'should_send' => false,
                'reason' => 'already_sent_today',
                'message' => "Un rappel a deja ete envoye aujourd'hui.",
                'priority' => 'low',
            ];
        }

        // TODO: replace meal_count with a true meal/session count if meal groups are added later.
        if ($mealCount >= 2) {
            return [
                'should_send' => false,
                'reason' => 'day_complete',
                'message' => "La journee semble deja bien renseignee, aucun rappel n'est necessaire.",
                'priority' => 'low',
            ];
        }

        if ($lastLoggedDate === null || $lastLoggedDate <= $twoDaysAgo) {
            return [
                'should_send' => true,
                'reason' => 'inactive_two_days',
                'message' => "Tu n'as rien enregistre depuis au moins 2 jours.",
                'priority' => 'high',
            ];
        }

        if ($mealCount === 0) {
            return [
                'should_send' => true,
                'reason' => 'no_meals_logged',
                'message' => "Tu n'as pas encore enregistre tes repas aujourd'hui.",
                'priority' => 'high',
            ];
        }

        if ($mealCount === 1 && $currentHour >= 21) {
            return [
                'should_send' => true,
                'reason' => 'only_one_meal_logged',
                'message' => "Un seul repas a ete enregistre aujourd'hui avant 21h.",
                'priority' => 'medium',
            ];
        }

        if ((int) ($weeklyAnalysis['consistency_rate'] ?? 0) < 50) {
            return [
                'should_send' => true,
                'reason' => 'low_weekly_consistency',
                'message' => "Ta regularite hebdomadaire reste faible.",
                'priority' => 'medium',
            ];
        }

        return [
            'should_send' => false,
            'reason' => 'no_reminder_needed',
            'message' => "Aucun rappel n'est necessaire pour aujourd'hui.",
            'priority' => 'low',
        ];
    }

    public function generateWeeklyAnalysis(array $weeklyData, int $days = 7): array
    {
        $days = max(1, (int) $days);
        $loggedDays = 0;
        $caloriesTotal = 0.0;
        $proteinTotal = 0.0;
        $strengths = [];
        $improvements = [];

        foreach ($weeklyData as $day) {
            $dayCalories = (float) ($day['total_calories'] ?? 0);
            $dayProtein = (float) ($day['total_protein'] ?? 0);
            $dayMeals = (int) ($day['meal_items'] ?? 0);

            if ($dayCalories > 0 || $dayProtein > 0 || $dayMeals > 0) {
                $loggedDays++;
                $caloriesTotal += $dayCalories;
                $proteinTotal += $dayProtein;
            }
        }

        $averageCalories = $loggedDays > 0 ? (int) round($caloriesTotal / $loggedDays) : 0;
        $averageProtein = $loggedDays > 0 ? (int) round($proteinTotal / $loggedDays) : 0;
        $consistencyRate = (int) round(($loggedDays / $days) * 100);

        if ($loggedDays >= max(5, $days - 2)) {
            $strengths[] = 'Bonne regularite sur la semaine';
        } else {
            $improvements[] = "Essaie d'enregistrer tes repas plus regulierement";
        }

        if ($averageCalories >= 1700 && $averageCalories <= 2400) {
            $strengths[] = 'Apport calorique globalement stable';
        } elseif ($loggedDays > 0 && $averageCalories < 1700) {
            $improvements[] = 'Tes apports semblent un peu bas certains jours';
        } elseif ($averageCalories > 2400) {
            $improvements[] = 'Tes apports semblent assez eleves sur la semaine';
        }

        if ($averageProtein >= 75) {
            $strengths[] = 'Apport en proteines plutot correct';
        } elseif ($loggedDays > 0) {
            $improvements[] = 'Tu peux renforcer un peu les proteines de tes repas';
        }

        if ($loggedDays === 0) {
            $summary = "Aucun repas n'a ete suivi sur les {$days} derniers jours.";
        } elseif ($loggedDays === $days) {
            $summary = "Excellent suivi : tu as enregistre tes repas {$loggedDays} jours sur {$days}.";
        } else {
            $summary = "Tu as suivi tes repas {$loggedDays} jours sur {$days}.";
        }

        if (empty($strengths) && $loggedDays > 0) {
            $strengths[] = 'Tu as au moins une base de suivi exploitable cette semaine';
        }

        if (empty($improvements) && $loggedDays > 0) {
            $improvements[] = 'Continue sur ce rythme pour garder une bonne regularite';
        }

        return [
            'period_days' => $days,
            'logged_days' => $loggedDays,
            'average_calories' => $averageCalories,
            'average_protein' => $averageProtein,
            'consistency_rate' => $consistencyRate,
            'strengths' => array_values(array_unique($strengths)),
            'improvements' => array_values(array_unique($improvements)),
            'summary' => $summary,
        ];
    }

    public function generateDailyRecommendations(array $todayData, array $weeklyData, array $profile): array
    {
        $priorityRank = [
            'high' => 3,
            'medium' => 2,
            'low' => 1,
        ];
        $recommendations = [];
        $seen = [];
        $order = 0;

        $targetCalories = (float) ($todayData['target_calories'] ?? $profile['target_calories'] ?? 2000);
        $weightKg = max(45, (float) ($profile['weight_kg'] ?? 70));
        $proteinTarget = (float) ($todayData['protein_target_g'] ?? round($weightKg * 1.2, 1));
        $hydrationTarget = (int) ($todayData['hydration_target_ml'] ?? round($weightKg * 35));

        $totalCalories = (float) ($todayData['total_calories'] ?? 0);
        $proteinsG = (float) ($todayData['proteins_g'] ?? 0);
        $waterMl = (int) ($todayData['water_ml'] ?? 0);
        $mealCount = (int) ($todayData['meal_count'] ?? 0);
        $daysLogged = max(0, min(7, (int) ($weeklyData['logged_days'] ?? 0)));
        $consistencyRate = (float) ($weeklyData['consistency_rate'] ?? ($daysLogged / 7) * 100);
        $objectiveType = (string) ($todayData['objective_type'] ?? 'maintien');
        $sugarGoal = is_numeric($todayData['sugar_goal_g'] ?? null) ? (float) $todayData['sugar_goal_g'] : null;
        $sugarToday = (float) ($todayData['sugar_today_g'] ?? 0);

        $calorieRatio = $targetCalories > 0 ? $totalCalories / $targetCalories : 0;
        $proteinRatio = $proteinTarget > 0 ? $proteinsG / $proteinTarget : 0;
        $hydrationRatio = $hydrationTarget > 0 ? $waterMl / $hydrationTarget : 0;

        $addRecommendation = function (
            string $type,
            string $title,
            string $message,
            string $priority,
            string $action
        ) use (&$recommendations, &$seen, &$order) {
            $key = $type . '|' . $title;

            if (isset($seen[$key])) {
                return;
            }

            $seen[$key] = true;
            $recommendations[] = [
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'priority' => $priority,
                'action' => $action,
                '_order' => $order++,
            ];
        };

        if ($mealCount === 0) {
            $addRecommendation(
                'consistency',
                "Commence ton suivi aujourd'hui",
                "Aucun repas n'est enregistre aujourd'hui. Meme une seule saisie aide a garder une journee exploitable.",
                'high',
                'Ajouter un premier repas'
            );

            $addRecommendation(
                'meal',
                'Ajoute un repas simple',
                'Commence par un repas facile avec une source de proteines et un feculent simple.',
                'medium',
                'Saisir un repas complet'
            );
        }

        if ($mealCount > 0) {
            if ($calorieRatio < 0.60) {
                $addRecommendation(
                    'calories',
                    'Apport calorique trop bas',
                    "Ton apport du jour reste tres bas par rapport a l'objectif.",
                    'high',
                    'Ajouter un repas ou une collation utile'
                );
            } elseif ($calorieRatio < 0.85) {
                $addRecommendation(
                    'calories',
                    'Complete ta journee',
                    "Tu es encore sous ton objectif calorique. Un repas leger mais complet peut suffire.",
                    'medium',
                    'Ajouter un repas leger'
                );
            }

            if ($calorieRatio > 1.20) {
                $addRecommendation(
                    'calories',
                    'Apport calorique eleve',
                    "Tu as deja bien depasse ton objectif. Le reste de la journee peut rester plus leger.",
                    'high',
                    'Privilegier un repas leger'
                );
            } elseif ($calorieRatio > 1.05) {
                $addRecommendation(
                    'calories',
                    'Reste leger pour la suite',
                    "Ton objectif est deja atteint ou presque depasse.",
                    'medium',
                    'Choisir un repas simple et leger'
                );
            }
        }

        if ($proteinRatio < 0.70) {
            $addRecommendation(
                'protein',
                'Proteines a renforcer',
                "Ton apport en proteines semble faible aujourd'hui.",
                'high',
                'Ajouter oeufs, yaourt grec, thon, poulet ou legumes secs'
            );
        } elseif ($proteinRatio < 0.90) {
            $addRecommendation(
                'protein',
                'Ajoute une source proteinee',
                'Il manque encore un peu de proteines pour une journee bien structuree.',
                'medium',
                'Completer le prochain repas avec une source de proteines'
            );
        }

        if ($hydrationRatio < 0.50) {
            $addRecommendation(
                'hydration',
                'Hydratation faible',
                'Ton hydratation semble basse pour aujourd hui.',
                'high',
                "Boire 2 grands verres d'eau dans l'heure"
            );
        } elseif ($hydrationRatio < 0.80) {
            $addRecommendation(
                'hydration',
                'Pense a boire davantage',
                "Ton hydratation peut encore etre amelioree avant la fin de journee.",
                'medium',
                "Ajouter 500 a 750 ml d'eau"
            );
        }

        if ($mealCount > 0 && $mealCount < 3) {
            $addRecommendation(
                'meal',
                'Repartis mieux tes repas',
                "Peu de prises ont ete enregistrees aujourd'hui.",
                'medium',
                'Viser 3 prises alimentaires regulieres'
            );
        }

        if ($objectiveType === 'reduction_sucre') {
            $addRecommendation(
                'sugar',
                'Surveille le sucre du jour',
                "Surveille les boissons sucrees et les desserts aujourd'hui.",
                'medium',
                'Verifier les produits les plus sucres'
            );
        }

        if ($sugarGoal !== null && $sugarGoal > 0 && $sugarToday > $sugarGoal) {
            $addRecommendation(
                'sugar',
                'Seuil de sucre depasse',
                "Ton seuil de sucre est depasse aujourd'hui.",
                'high',
                'Limiter les apports sucres sur le reste de la journee'
            );
        }

        if ($daysLogged <= 3 || $consistencyRate < 50) {
            $addRecommendation(
                'consistency',
                'Travaille la regularite',
                'Une saisie simple chaque jour vaut mieux qu un suivi parfait mais rare.',
                'medium',
                'Enregistrer au moins un repas par jour cette semaine'
            );
        }

        $isGoodDay = (
            $mealCount >= 3 &&
            $calorieRatio >= 0.90 && $calorieRatio <= 1.10 &&
            $proteinRatio >= 0.90 &&
            $hydrationRatio >= 0.80
        );

        if ($isGoodDay) {
            $addRecommendation(
                'consistency',
                'Bonne journee nutritionnelle',
                'La journee est globalement bien equilibree. Le plus utile est de garder ce rythme.',
                'low',
                'Reproduire cette structure demain'
            );
        }

        if (count($recommendations) < 3) {
            $addRecommendation(
                'hydration',
                'Garde une base simple',
                "Penser a boire regulierement reste un repere facile et utile.",
                'low',
                "Prevoir une bouteille d'eau a portee de main"
            );
        }

        if (count($recommendations) < 3) {
            $addRecommendation(
                'meal',
                'Prepare la suite',
                "Anticiper le prochain repas aide souvent a mieux tenir l'objectif.",
                'low',
                "Prevoir le prochain repas a l'avance"
            );
        }

        usort($recommendations, function (array $a, array $b) use ($priorityRank) {
            $priorityDiff = $priorityRank[$b['priority']] <=> $priorityRank[$a['priority']];

            if ($priorityDiff !== 0) {
                return $priorityDiff;
            }

            return $a['_order'] <=> $b['_order'];
        });

        $recommendations = array_slice($recommendations, 0, 5);

        return array_map(function (array $item) {
            unset($item['_order']);
            return $item;
        }, $recommendations);
    }

    public function ensureReminderLogsTable(): bool
    {
        return $this->model->ensureReminderLogsTable();
    }

    public function markReminderSent(int $userId): bool
    {
        return $this->model->markReminderSent($userId);
    }

    private function buildHydrationSummary(array $todayData): array
    {
        $totalMl = max(0, (int) ($todayData['water_ml'] ?? 0));
        $targetMl = max(1, (int) ($todayData['hydration_target_ml'] ?? 2000));

        return [
            'total_ml' => $totalMl,
            'target_ml' => $targetMl,
            'progress' => min(100, (int) round(($totalMl / $targetMl) * 100)),
            'glasses' => round($totalMl / 250, 1),
        ];
    }
}
