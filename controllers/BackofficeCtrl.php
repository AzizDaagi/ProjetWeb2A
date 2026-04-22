<?php

require_once __DIR__ . '/../models/aliment.php';
require_once __DIR__ . '/../models/suivi.php';
require_once __DIR__ . '/../models/utilisateur.php';

class BackofficeCtrl
{
    private $alimentModel;
    private $suiviModel;
    private $utilisateurModel;

    public function __construct($pdo)
    {
        $this->alimentModel = new Aliment($pdo);
        $this->suiviModel = new Suivi($pdo);
        $this->utilisateurModel = new Utilisateur($pdo);
    }

    public function dashboard()
    {
        $evolutionData = $this->suiviModel->getEvolutionData(7);

        $this->render('dashboard.php', [
            'pageTitle' => 'Dashboard',
            'currentSection' => 'dashboard',
            'totalUsers' => $this->utilisateurModel->countAll(),
            'totalRepas' => $this->suiviModel->countAllMeals(),
            'totalCalories' => $this->suiviModel->getTotalCaloriesTracked(),
            'totalAliments' => $this->alimentModel->countAll(),
            'recentUsers' => $this->utilisateurModel->getRecent(5),
            'evolutionLabels' => array_column($evolutionData, 'label'),
            'caloriesTrendPoints' => $this->buildPolylinePoints(array_column($evolutionData, 'total_calories')),
            'repasTrendPoints' => $this->buildPolylinePoints(array_column($evolutionData, 'repas_count')),
        ]);
    }

    public function users()
    {
        $this->render('users/index.php', [
            'pageTitle' => 'Utilisateurs',
            'currentSection' => 'users',
            'users' => $this->utilisateurModel->getAll(),
        ]);
    }

    private function render($relativeView, array $data = [])
    {
        extract($data, EXTR_SKIP);

        $view = __DIR__ . '/../views/back/' . ltrim($relativeView, '/');
        require __DIR__ . '/../views/back/layout.php';
    }

    private function buildPolylinePoints(array $values)
    {
        if (empty($values)) {
            return '0,220 860,220';
        }

        $width = 860;
        $maxHeight = 220;
        $topPadding = 18;
        $bottomPadding = 18;
        $maxValue = max(1, (float) max($values));
        $count = count($values);
        $stepX = $count > 1 ? $width / ($count - 1) : $width;
        $points = [];

        foreach ($values as $index => $value) {
            $x = round($index * $stepX, 2);
            $normalized = ((float) $value) / $maxValue;
            $usableHeight = $maxHeight - $topPadding - $bottomPadding;
            $y = round($maxHeight - ($normalized * $usableHeight) - $bottomPadding, 2);
            $points[] = $x . ',' . $y;
        }

        return implode(' ', $points);
    }
}
