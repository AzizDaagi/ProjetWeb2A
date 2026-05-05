<?php

require_once __DIR__ . '/ChronoNutritionModel.php';

class ChronoNutritionService
{
    private $model;

    public function __construct(PDO $pdo)
    {
        $this->model = new ChronoNutritionModel($pdo);
    }

    public function getOptimalTiming($userId)
    {
        $profile = $this->model->getProfile($userId)['data'];
        $chronotype = $profile['chronotype'] ?? 'standard';

        $timings = [
            'leve_tot' => [
                'breakfast' => ['start' => '06:15', 'end' => '07:45'],
                'lunch' => ['start' => '11:30', 'end' => '13:00'],
                'dinner' => ['start' => '18:30', 'end' => '20:00'],
            ],
            'standard' => [
                'breakfast' => ['start' => '07:00', 'end' => '08:30'],
                'lunch' => ['start' => '12:00', 'end' => '13:30'],
                'dinner' => ['start' => '19:00', 'end' => '20:30'],
            ],
            'couche_tard' => [
                'breakfast' => ['start' => '07:45', 'end' => '09:15'],
                'lunch' => ['start' => '12:30', 'end' => '14:00'],
                'dinner' => ['start' => '19:30', 'end' => '21:00'],
            ],
        ];

        $result = $timings[$chronotype];
        if ($result['dinner']['end'] > '21:30') {
            $result['dinner']['end'] = '21:30';
        }

        return [
            'data' => $result,
            'error' => null,
            'cached' => false,
        ];
    }

    public function getFastingWindow($userId)
    {
        $profile = $this->model->getProfile($userId)['data'];
        $wakeTime = $profile['wake_time'] ?? '07:00:00';
        $sleepTime = $profile['sleep_time'] ?? '23:00:00';

        $fastStart = date('H:i', strtotime($sleepTime));
        $fastEnd = date('H:i', strtotime($wakeTime));
        $duration = (strtotime($wakeTime) - strtotime($sleepTime)) / 3600;

        if ($duration < 10) {
            $duration = 10;
        } elseif ($duration > 13) {
            $duration = 13;
        }

        return [
            'data' => [
                'fast_start' => $fastStart,
                'fast_end' => $fastEnd,
                'duration_h' => $duration,
                'message' => 'Ces conseils sont informatifs, consultez un professionnel de sante pour un suivi personnalise.',
            ],
            'error' => null,
            'cached' => false,
        ];
    }

    public function getNutrientTiming()
    {
        return [
            'data' => [
                'morning' => [
                    'nutrients' => ['proteines', 'fibres', 'glucides complexes'],
                    'tip' => 'Commencez votre journee avec des aliments riches en energie.',
                ],
                'noon' => [
                    'nutrients' => ['repas complet equilibre'],
                    'tip' => 'Un dejeuner equilibre pour maintenir votre energie.',
                ],
                'evening' => [
                    'nutrients' => ['repas leger', 'eviter les sucres rapides'],
                    'tip' => 'Un diner leger pour une meilleure digestion.',
                ],
            ],
            'error' => null,
            'cached' => false,
        ];
    }

    public function sleepSync($data)
    {
        $quality = $data['sleep_quality'] ?? 'moyenne';
        $recommendations = [];

        if ($quality === 'mauvaise') {
            $recommendations = [
                ['title' => 'Aliments riches en magnesium', 'description' => 'Privilegiez amandes, noix de cajou, epinards, banane, chocolat noir 70%+.', 'priority' => 'high'],
                ['title' => 'Limiter la cafeine', 'description' => 'Evitez cafe, the noir et sodas apres 14h.', 'priority' => 'high'],
                ['title' => 'Diner leger et anticipe', 'description' => 'Prenez un repas leger au moins 2h avant votre heure de coucher.', 'priority' => 'high'],
                ['title' => 'Disclaimer', 'description' => 'Ces conseils sont informatifs, consultez un professionnel de sante pour un suivi personnalise.', 'priority' => 'low'],
            ];
        } elseif ($quality === 'moyenne') {
            $recommendations = [
                ['title' => "Eviter l'alcool le soir", 'description' => "L'alcool perturbe les cycles de sommeil.", 'priority' => 'medium'],
                ['title' => 'Tisane avant le coucher', 'description' => 'Essayez une tisane de camomille ou valeriane.', 'priority' => 'medium'],
                ['title' => 'Diner anticipe', 'description' => 'Prenez votre diner au moins 2h avant de dormir.', 'priority' => 'medium'],
                ['title' => 'Disclaimer', 'description' => 'Ces conseils sont informatifs, consultez un professionnel de sante pour un suivi personnalise.', 'priority' => 'low'],
            ];
        } else {
            $recommendations = [
                ['title' => 'Maintenir vos bonnes habitudes', 'description' => 'Continuez a diner leger le soir.', 'priority' => 'low'],
                ['title' => 'Rythme alimentaire synchronise', 'description' => 'Votre rythme alimentaire semble bien adapte.', 'priority' => 'low'],
                ['title' => 'Disclaimer', 'description' => 'Ces conseils sont informatifs, consultez un professionnel de sante pour un suivi personnalise.', 'priority' => 'low'],
            ];
        }

        return [
            'data' => ['recommendations' => $recommendations],
            'error' => null,
            'cached' => false,
        ];
    }
}
