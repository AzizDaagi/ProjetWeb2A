<?php

require_once __DIR__ . '/../models/PredictionModel.php';

class PredictionService
{
    private const DISCLAIMER = "Projection indicative bas\u{00E9}e sur les donn\u{00E9}es enregistr\u{00E9}es, \u{00E0} interpr\u{00E9}ter avec prudence.";
    private const INSUFFICIENT_TREND_MESSAGE = "Tendance actuelle insuffisante pour projeter une date.";
    private const MISSING_GOAL_MESSAGE = "Objectif restant insuffisamment d\u{00E9}fini pour calculer une date.";
    private const MISSING_TARGET_WEIGHT_MESSAGE = "Objectif restant non d\u{00E9}fini. Ajoute un poids cible pour calculer une date d'atteinte.";
    private const BEHAVIORAL_OBJECTIVE_MESSAGE = "Projection de date non disponible pour les objectifs comportementaux comme la réduction du sucre.";

    private $model;

    public function __construct(PDO $pdo)
    {
        $this->model = new PredictionModel($pdo);
    }

    public function getWeeklyTrend(int $userId): array
    {
        $snapshot = $this->buildProjectionSnapshot($userId);

        return [
            'period_days' => $snapshot['period_days'],
            'logged_days' => $snapshot['logged_days'],
            'objective_type' => $snapshot['objective_type'],
            'delta_mode' => $snapshot['delta_mode'],
            'weekly' => $snapshot['weekly'],
            'trend' => [
                'slope' => $snapshot['trend']['slope'],
                'direction' => $snapshot['trend']['direction'],
                'message' => $snapshot['trend']['message'],
            ],
            'disclaimer' => self::DISCLAIMER,
        ];
    }

    public function getScenarios(int $userId): array
    {
        $snapshot = $this->buildProjectionSnapshot($userId);

        return $this->buildScenariosPayload($snapshot);
    }

    public function getGoalDate(int $userId): array
    {
        $scenariosPayload = $this->getScenarios($userId);
        $currentScenario = $this->findScenarioByName($scenariosPayload['scenarios'] ?? [], 'current');
        $confidence = isset($currentScenario['confidence']) ? (float) $currentScenario['confidence'] : 0.0;

        return [
            'predicted_goal_date' => $currentScenario['predicted_goal_date'] ?? null,
            'confidence' => round($confidence, 3),
            'confidence_label' => $this->resolveConfidenceLabel($confidence),
            'objective_type' => $scenariosPayload['objective_type'] ?? '',
            'delta_mode' => $scenariosPayload['delta_mode'] ?? 'deficit_progress',
            'message' => ($currentScenario !== null && !empty($currentScenario['predicted_goal_date']))
                ? "Projection bas\u{00E9}e sur le sc\u{00E9}nario actuel."
                : ($currentScenario['message'] ?? self::INSUFFICIENT_TREND_MESSAGE),
            'disclaimer' => self::DISCLAIMER,
        ];
    }

    public function getConfidence(int $userId): array
    {
        $snapshot = $this->buildProjectionSnapshot($userId);

        return [
            'logged_days' => $snapshot['logged_days'],
            'period_days' => $snapshot['period_days'],
            'objective_type' => $snapshot['objective_type'],
            'delta_mode' => $snapshot['delta_mode'],
            'std_dev' => $snapshot['std_dev'],
            'data_score' => $snapshot['confidence']['data_score'],
            'regularity_score' => $snapshot['confidence']['regularity_score'],
            'confidence' => $snapshot['confidence']['confidence'],
            'label' => $snapshot['confidence']['label'],
            'disclaimer' => self::DISCLAIMER,
        ];
    }

    public function simulateWhatIf(int $userId, int $dailyCalorieChange): array
    {
        $snapshot = $this->buildProjectionSnapshot($userId);
        $scenariosPayload = $this->buildScenariosPayload($snapshot);
        $currentScenario = $this->findScenarioByName($scenariosPayload['scenarios'] ?? [], 'current');
        $baselineGoalDate = $currentScenario['predicted_goal_date'] ?? null;
        $newMeanDailyDelta = $this->calculateProgressDelta(
            $snapshot['mean_target_calories'],
            $snapshot['mean_consumed_calories'] + $dailyCalorieChange,
            $snapshot['objective_type']
        );
        $newWeeklyDelta = $newMeanDailyDelta * 7;
        $newGoalDate = null;
        $gainDays = null;
        $message = self::MISSING_GOAL_MESSAGE;

        if ($this->isBehavioralObjective($snapshot['objective_type'])) {
            $message = self::BEHAVIORAL_OBJECTIVE_MESSAGE;
        } elseif ($snapshot['remaining_goal_kcal'] === null) {
            $message = $snapshot['goal_definition_message'] === self::MISSING_TARGET_WEIGHT_MESSAGE
                ? self::MISSING_TARGET_WEIGHT_MESSAGE
                : "Objectif restant insuffisamment d\u{00E9}fini pour recalculer une date.";
        } elseif ($this->shouldSuppressGoalDate($snapshot['objective_type'], $newMeanDailyDelta, $newWeeklyDelta)) {
            $message = "Avec ce changement, la projection ne permet pas d'estimer une date d'atteinte.";
        } else {
            $newGoalDate = $this->resolveProjectedGoalDate($snapshot['remaining_goal_kcal'], $newWeeklyDelta);

            if ($baselineGoalDate !== null && $newGoalDate !== null) {
                $baselineDateObject = new DateTimeImmutable($baselineGoalDate);
                $newDateObject = new DateTimeImmutable($newGoalDate);
                $gainDays = (int) $newDateObject->diff($baselineDateObject)->format('%r%a');
            }

            $message = $this->buildWhatIfMessage($dailyCalorieChange, $gainDays, $newGoalDate);
        }

        return [
            'input' => [
                'daily_calorie_change' => $dailyCalorieChange,
            ],
            'objective_type' => $snapshot['objective_type'],
            'delta_mode' => $snapshot['delta_mode'],
            'baseline_goal_date' => $baselineGoalDate,
            'impact' => [
                'new_goal_date' => $newGoalDate,
                'gain_days' => $gainDays,
                'message' => $message,
            ],
            'disclaimer' => self::DISCLAIMER,
        ];
    }

    private function buildProjectionSnapshot(int $userId): array
    {
        $periodDays = 28;
        $selectedObjectiveId = $this->model->getSelectedProjectionObjectiveId($userId, $periodDays);
        $currentObjective = $selectedObjectiveId !== null
            ? $this->model->getObjectiveById($userId, $selectedObjectiveId)
            : $this->model->getCurrentObjective($userId);
        $objectiveType = $this->normalizeObjectiveType($currentObjective['objective_type'] ?? '');
        $deltaMode = $this->resolveDeltaMode($objectiveType);
        $logs = $selectedObjectiveId !== null
            ? $this->model->getRecentDailyLogsForObjective($userId, $selectedObjectiveId, $periodDays)
            : $this->model->getRecentDailyLogs($userId, $periodDays);
        $dailyPoints = [];
        $sumConsumedCalories = 0.0;
        $sumTargetCalories = 0.0;
        $meanConsumedCalories = 0.0;
        $meanTargetCalories = 0.0;

        foreach ($logs as $row) {
            $targetCalories = (float) ($row['target_calories'] ?? 0);
            $consumedCalories = (float) ($row['consumed_calories'] ?? 0);
            $progressDelta = $targetCalories > 0
                ? $this->calculateProgressDelta($targetCalories, $consumedCalories, $objectiveType)
                : null;
            $dailyPoints[] = [
                'log_date' => (string) ($row['log_date'] ?? ''),
                'consumed_calories' => $consumedCalories,
                'target_calories' => $targetCalories,
                'delta' => $progressDelta,
                'objectif_id' => $row['objectif_id'] ?? null,
            ];

            if ($progressDelta !== null) {
                $sumConsumedCalories += $consumedCalories;
                $sumTargetCalories += $targetCalories;
            }
        }

        $deltaPoints = array_values(array_filter($dailyPoints, function (array $point) {
            return $point['delta'] !== null;
        }));
        $deltaPointsCount = count($deltaPoints);

        if ($deltaPointsCount > 0) {
            $meanConsumedCalories = $sumConsumedCalories / $deltaPointsCount;
            $meanTargetCalories = $sumTargetCalories / $deltaPointsCount;
        }

        $trend = $this->calculateTrend($deltaPoints);
        $stdDev = round($this->calculateStandardDeviation($deltaPoints), 1);
        $loggedDays = count($logs);
        $confidence = $this->calculateConfidence($loggedDays, $stdDev);
        $goalContext = $this->buildGoalContext($currentObjective);

        return [
            'period_days' => $periodDays,
            'logged_days' => $loggedDays,
            'selected_objectif_id' => $selectedObjectiveId ?? ($currentObjective['objective_id'] ?? null),
            'objective_type' => $objectiveType,
            'delta_mode' => $deltaMode,
            'daily_points' => $dailyPoints,
            'mean_consumed_calories' => $meanConsumedCalories,
            'mean_target_calories' => $meanTargetCalories,
            'trend' => $trend,
            'weekly' => $this->buildWeeklyBreakdown($dailyPoints, $periodDays),
            'std_dev' => $stdDev,
            'confidence' => $confidence,
            'current_objective' => $currentObjective,
            'remaining_goal_kcal' => $goalContext['remaining_goal_kcal'],
            'goal_progress' => $goalContext['goal_progress'],
            'goal_definition_message' => $goalContext['goal_definition_message'],
        ];
    }

    private function buildScenariosPayload(array $snapshot): array
    {
        $weeklyDeltaCurrent = (int) round($snapshot['trend']['mean_daily_delta'] * 7);
        $payload = [
            'objective_type' => $snapshot['objective_type'],
            'delta_mode' => $snapshot['delta_mode'],
            'scenarios' => $this->buildScenarios(
                $snapshot['trend']['mean_daily_delta'],
                $snapshot['confidence']['confidence'],
                $snapshot['remaining_goal_kcal'],
                $snapshot['objective_type'],
                $snapshot['goal_definition_message']
            ),
            'debug_objective' => [
                'selected_objectif_id' => $snapshot['selected_objectif_id'],
                'poids' => $snapshot['current_objective']['poids'] ?? null,
                'poids_cible' => $snapshot['current_objective']['poids_cible'] ?? null,
                'objectif_restant_kcal' => $snapshot['remaining_goal_kcal'] !== null
                    ? (int) round($snapshot['remaining_goal_kcal'])
                    : null,
                'weekly_delta_current' => $weeklyDeltaCurrent,
            ],
            'disclaimer' => self::DISCLAIMER,
        ];

        if ($snapshot['goal_progress'] !== null) {
            $payload['goal_progress'] = $snapshot['goal_progress'];
        }

        return $payload;
    }

    private function calculateTrend(array $deltaPoints): array
    {
        $count = count($deltaPoints);

        if ($count === 0) {
            return [
                'slope' => 0.0,
                'intercept' => 0.0,
                'direction' => 'insufficient_data',
                'message' => "Pas assez de donn\u{00E9}es pour calculer une tendance.",
                'mean_daily_delta' => 0.0,
            ];
        }

        $deltas = array_map(function (array $point) {
            return (float) $point['delta'];
        }, $deltaPoints);
        $meanDailyDelta = array_sum($deltas) / $count;

        if ($count < 5) {
            return [
                'slope' => 0.0,
                'intercept' => round($meanDailyDelta, 1),
                'direction' => 'insufficient_data',
                'message' => "Pas assez de donn\u{00E9}es pour calculer une tendance.",
                'mean_daily_delta' => $meanDailyDelta,
            ];
        }

        $sumX = 0.0;
        $sumY = 0.0;
        $sumXY = 0.0;
        $sumX2 = 0.0;

        foreach ($deltas as $index => $delta) {
            $x = $index + 1;
            $sumX += $x;
            $sumY += $delta;
            $sumXY += ($x * $delta);
            $sumX2 += ($x * $x);
        }

        $denominator = ($count * $sumX2) - ($sumX * $sumX);
        $slope = $denominator !== 0.0
            ? (($count * $sumXY) - ($sumX * $sumY)) / $denominator
            : 0.0;
        $intercept = ($sumY - ($slope * $sumX)) / $count;
        $direction = 'stable';
        $message = "Ton rythme est stable.";

        if ($slope > 5) {
            $direction = 'improving';
            $message = "Ta r\u{00E9}gularit\u{00E9} s'am\u{00E9}liore progressivement.";
        } elseif ($slope < -5) {
            $direction = 'degrading';
            $message = "Ta tendance se d\u{00E9}grade \u{2014} attention aux \u{00E9}carts.";
        }

        return [
            'slope' => round($slope, 1),
            'intercept' => round($intercept, 1),
            'direction' => $direction,
            'message' => $message,
            'mean_daily_delta' => $meanDailyDelta,
        ];
    }

    private function calculateStandardDeviation(array $deltaPoints): float
    {
        $count = count($deltaPoints);

        if ($count === 0) {
            return 0.0;
        }

        $deltas = array_map(function (array $point) {
            return (float) $point['delta'];
        }, $deltaPoints);
        $mean = array_sum($deltas) / $count;
        $sum = 0.0;

        foreach ($deltas as $delta) {
            $sum += pow($delta - $mean, 2);
        }

        return sqrt($sum / $count);
    }

    private function calculateConfidence(int $loggedDays, float $stdDev): array
    {
        $dataScore = min(1.0, $loggedDays / 21);
        $regularityScore = max(0.0, 1.0 - ($stdDev / 800));
        $confidence = round(($dataScore * 0.6) + ($regularityScore * 0.4), 3);

        return [
            'data_score' => round($dataScore, 3),
            'regularity_score' => round($regularityScore, 3),
            'confidence' => $confidence,
            'label' => $this->resolveConfidenceLabel($confidence),
        ];
    }

    private function resolveConfidenceLabel(float $confidence): string
    {
        if ($confidence >= 0.75) {
            return "Projection fiable";
        }

        if ($confidence >= 0.50) {
            return "Projection indicative";
        }

        return "Donn\u{00E9}es insuffisantes \u{2014} continuez \u{00E0} enregistrer vos repas";
    }

    private function buildWeeklyBreakdown(array $dailyPoints, int $periodDays): array
    {
        $weeks = [
            1 => ['sum_calories' => 0.0, 'sum_delta' => 0.0, 'logged_days' => 0, 'delta_days' => 0],
            2 => ['sum_calories' => 0.0, 'sum_delta' => 0.0, 'logged_days' => 0, 'delta_days' => 0],
            3 => ['sum_calories' => 0.0, 'sum_delta' => 0.0, 'logged_days' => 0, 'delta_days' => 0],
            4 => ['sum_calories' => 0.0, 'sum_delta' => 0.0, 'logged_days' => 0, 'delta_days' => 0],
        ];
        $startDate = (new DateTimeImmutable('today'))
            ->modify('-' . ($periodDays - 1) . ' days');

        foreach ($dailyPoints as $point) {
            try {
                $pointDate = new DateTimeImmutable((string) $point['log_date']);
            } catch (Exception $exception) {
                continue;
            }

            $offset = (int) $startDate->diff($pointDate)->format('%r%a');

            if ($offset < 0 || $offset >= $periodDays) {
                continue;
            }

            $weekNumber = (int) floor($offset / 7) + 1;
            $weeks[$weekNumber]['sum_calories'] += (float) $point['consumed_calories'];
            $weeks[$weekNumber]['logged_days']++;

            if ($point['delta'] !== null) {
                $weeks[$weekNumber]['sum_delta'] += (float) $point['delta'];
                $weeks[$weekNumber]['delta_days']++;
            }
        }

        $result = [];

        foreach ($weeks as $weekNumber => $weekData) {
            $avgCalories = $weekData['logged_days'] > 0
                ? round($weekData['sum_calories'] / $weekData['logged_days'])
                : 0;
            $avgDelta = $weekData['delta_days'] > 0
                ? round($weekData['sum_delta'] / $weekData['delta_days'])
                : 0;

            $result[] = [
                'week' => $weekNumber,
                'avg_calories' => (int) $avgCalories,
                'avg_delta' => (int) $avgDelta,
                'logged_days' => (int) $weekData['logged_days'],
            ];
        }

        return $result;
    }

    private function buildGoalContext(?array $objective): array
    {
        if ($this->isBehavioralObjective($this->normalizeObjectiveType($objective['objective_type'] ?? ''))) {
            return [
                'remaining_goal_kcal' => null,
                'goal_progress' => null,
                'goal_definition_message' => self::BEHAVIORAL_OBJECTIVE_MESSAGE,
            ];
        }

        $currentWeight = $this->extractPositiveFloat($objective['current_weight'] ?? null);
        $targetWeight = $this->extractPositiveFloat($objective['target_weight'] ?? null);
        $startWeight = $this->extractPositiveFloat($objective['start_weight'] ?? null);
        $remainingGoalKcal = null;
        $goalProgress = null;
        $goalDefinitionMessage = self::MISSING_GOAL_MESSAGE;

        if ($currentWeight !== null && $targetWeight !== null) {
            $remainingGoalKcal = abs($currentWeight - $targetWeight) * 7700;
            $goalDefinitionMessage = null;

            if (abs($currentWeight - $targetWeight) < 0.0001) {
                $goalProgress = [
                    'current_weight' => round($currentWeight, 1),
                    'target_weight' => round($targetWeight, 1),
                    'progress_percent' => 100,
                    'remaining_kcal' => 0,
                ];
            } elseif ($startWeight !== null && abs($startWeight - $targetWeight) > 0.0001) {
                $totalChange = abs($startWeight - $targetWeight);
                $remainingChange = abs($currentWeight - $targetWeight);
                $progressPercent = max(0, min(100, round((1 - ($remainingChange / $totalChange)) * 100, 1)));
                $goalProgress = [
                    'current_weight' => round($currentWeight, 1),
                    'target_weight' => round($targetWeight, 1),
                    'start_weight' => round($startWeight, 1),
                    'progress_percent' => $progressPercent,
                    'remaining_kcal' => round($remainingGoalKcal),
                ];
            }
        } elseif ($currentWeight !== null && $targetWeight === null) {
            $goalDefinitionMessage = self::MISSING_TARGET_WEIGHT_MESSAGE;
        }

        return [
            'remaining_goal_kcal' => $remainingGoalKcal,
            'goal_progress' => $goalProgress,
            'goal_definition_message' => $goalDefinitionMessage,
        ];
    }

    private function buildScenarios(
        float $meanDailyDelta,
        float $baseConfidence,
        ?float $remainingGoalKcal,
        string $objectiveType,
        ?string $remainingGoalMessage = null
    ): array
    {
        $definitions = [
            [
                'name' => 'current',
                'label' => 'Rythme actuel',
                'factor' => 1.0,
                'penalty' => 0.0,
            ],
            [
                'name' => 'optimistic',
                'label' => "Sc\u{00E9}nario optimiste",
                'factor' => 1.2,
                'penalty' => 0.07,
            ],
            [
                'name' => 'pessimistic',
                'label' => "Sc\u{00E9}nario prudent",
                'factor' => 0.8,
                'penalty' => 0.11,
            ],
        ];
        $scenarios = [];

        foreach ($definitions as $definition) {
            $weeklyDeltaRaw = $meanDailyDelta * 7 * $definition['factor'];
            $projectedGoalDate = null;
            $message = $this->buildPositiveScenarioMessage($objectiveType, $definition['name']);

            if ($this->shouldSuppressGoalDate($objectiveType, $meanDailyDelta, $weeklyDeltaRaw)) {
                $message = $this->buildInsufficientScenarioMessage($objectiveType);
            } elseif ($remainingGoalKcal === null) {
                $message = $this->resolveMissingGoalMessage($remainingGoalMessage);
            } else {
                $projectedGoalDate = $this->resolveProjectedGoalDate($remainingGoalKcal, $weeklyDeltaRaw);
            }

            $scenarios[] = [
                'name' => $definition['name'],
                'label' => $definition['label'],
                'weekly_delta' => (int) round($weeklyDeltaRaw),
                'predicted_goal_date' => $projectedGoalDate,
                'confidence' => round($this->clampConfidence($baseConfidence - $definition['penalty']), 3),
                'message' => $message,
            ];
        }

        return $scenarios;
    }

    private function calculateProgressDelta(float $targetCalories, float $consumedCalories, string $objectiveType): float
    {
        $mode = $this->resolveDeltaMode($objectiveType);

        if ($mode === 'surplus_progress') {
            return $consumedCalories - $targetCalories;
        }

        if ($mode === 'maintenance_balance') {
            return -abs($consumedCalories - $targetCalories);
        }

        return $targetCalories - $consumedCalories;
    }

    private function normalizeObjectiveType(string $objectiveType): string
    {
        return trim(mb_strtolower($objectiveType, 'UTF-8'));
    }

    private function resolveDeltaMode(string $objectiveType): string
    {
        if ($this->matchesObjectiveType($objectiveType, ['prise', 'prise_muscle', 'masse'])) {
            return 'surplus_progress';
        }

        if ($this->matchesObjectiveType($objectiveType, ['maintien'])) {
            return 'maintenance_balance';
        }

        return 'deficit_progress';
    }

    private function matchesObjectiveType(string $objectiveType, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && mb_strpos($objectiveType, $needle, 0, 'UTF-8') !== false) {
                return true;
            }
        }

        return false;
    }

    private function shouldSuppressGoalDate(string $objectiveType, float $meanDailyDelta, float $weeklyDelta): bool
    {
        if ($this->isBehavioralObjective($objectiveType)) {
            return true;
        }

        if ($this->resolveDeltaMode($objectiveType) === 'maintenance_balance') {
            return true;
        }

        return $meanDailyDelta <= 0 || $weeklyDelta <= 0;
    }

    private function buildInsufficientScenarioMessage(string $objectiveType): string
    {
        if ($this->isBehavioralObjective($objectiveType)) {
            return self::BEHAVIORAL_OBJECTIVE_MESSAGE;
        }

        if ($this->resolveDeltaMode($objectiveType) === 'surplus_progress') {
            return "Ton apport reste insuffisant pour projeter une progression.";
        }

        return self::INSUFFICIENT_TREND_MESSAGE;
    }

    private function buildPositiveScenarioMessage(string $objectiveType, string $scenarioName): string
    {
        if ($this->resolveDeltaMode($objectiveType) === 'surplus_progress') {
            if ($scenarioName === 'optimistic') {
                return "Projection si ton surplus progresse encore plus favorablement.";
            }

            if ($scenarioName === 'pessimistic') {
                return "Projection avec un surplus plus prudent sur la semaine.";
            }

            return "Tu progresses vers un surplus compatible avec la prise de masse.";
        }

        if ($this->resolveDeltaMode($objectiveType) === 'deficit_progress') {
            if ($scenarioName === 'optimistic') {
                return "Projection si ton d\u{00E9}ficit reste plus r\u{00E9}gulier.";
            }

            if ($scenarioName === 'pessimistic') {
                return "Projection avec un d\u{00E9}ficit plus prudent sur la semaine.";
            }

            return "Tu maintiens un d\u{00E9}ficit compatible avec ton objectif.";
        }

        return "Projection bas\u{00E9}e sur ton rythme actuel.";
    }

    private function resolveMissingGoalMessage(?string $message): string
    {
        if ($message === self::BEHAVIORAL_OBJECTIVE_MESSAGE) {
            return self::BEHAVIORAL_OBJECTIVE_MESSAGE;
        }

        return $message === self::MISSING_TARGET_WEIGHT_MESSAGE
            ? self::MISSING_TARGET_WEIGHT_MESSAGE
            : self::MISSING_GOAL_MESSAGE;
    }

    private function resolveProjectedGoalDate(float $remainingGoalKcal, float $weeklyDelta): ?string
    {
        if ($weeklyDelta <= 0) {
            return null;
        }

        $daysToGoal = (int) round(($remainingGoalKcal / $weeklyDelta) * 7);
        $daysToGoal = max(0, $daysToGoal);

        return (new DateTimeImmutable('today'))
            ->modify('+' . $daysToGoal . ' days')
            ->format('Y-m-d');
    }

    private function clampConfidence(float $value): float
    {
        return max(0.0, min(1.0, $value));
    }

    private function findScenarioByName(array $scenarios, string $name): ?array
    {
        foreach ($scenarios as $scenario) {
            if (($scenario['name'] ?? '') === $name) {
                return $scenario;
            }
        }

        return null;
    }

    private function buildWhatIfMessage(int $dailyCalorieChange, ?int $gainDays, ?string $newGoalDate): string
    {
        if ($newGoalDate === null) {
            return "Cette simulation ne permet pas de calculer une nouvelle date pour le moment.";
        }

        if ($gainDays === null) {
            return "La simulation a recalcul\u{00E9} une nouvelle date indicative.";
        }

        if ($gainDays > 0) {
            return sprintf(
                "Avec %s, ton objectif pourrait \u{00EA}tre atteint environ %d jours plus t\u{00F4}t.",
                $this->describeDailyCalorieChange($dailyCalorieChange),
                $gainDays
            );
        }

        if ($gainDays < 0) {
            return sprintf(
                "Avec %s, ton objectif pourrait \u{00EA}tre atteint environ %d jours plus tard.",
                $this->describeDailyCalorieChange($dailyCalorieChange),
                abs($gainDays)
            );
        }

        return "Avec ce changement, la date estim\u{00E9}e resterait globalement inchang\u{00E9}e.";
    }

    private function describeDailyCalorieChange(int $dailyCalorieChange): string
    {
        if ($dailyCalorieChange < 0) {
            return abs($dailyCalorieChange) . " kcal de moins par jour";
        }

        if ($dailyCalorieChange > 0) {
            return abs($dailyCalorieChange) . " kcal de plus par jour";
        }

        return "ce changement";
    }

    private function extractPositiveFloat($value): ?float
    {
        if (!is_numeric($value)) {
            return null;
        }

        $value = (float) $value;

        return $value > 0 ? $value : null;
    }

    private function isBehavioralObjective(string $objectiveType): bool
    {
        return $this->matchesObjectiveType($objectiveType, ['reduction_sucre', 'sucre']);
    }
}
