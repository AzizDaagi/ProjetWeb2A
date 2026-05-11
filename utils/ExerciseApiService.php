<?php

class ExerciseApiService {
    private $apiKey = 'sXexCYbiAwQJraoAxzMjzPHmKzbE1Tgaxrs4uM9q';
    private $baseUrl = 'https://api.api-ninjas.com/v1/exercises';

    public function getRandomExercises($limit = 2) {
        // Valid muscle groups for API Ninjas
        $muscles = [
            'abdominals', 'abductors', 'adductors', 'biceps', 'calves', 
            'chest', 'forearms', 'glutes', 'hamstrings', 'lats', 
            'lower_back', 'middle_back', 'neck', 'quadriceps', 'traps', 'triceps'
        ];
        
        $randomMuscle = $muscles[array_rand($muscles)];
        $url = $this->baseUrl . '?muscle=' . $randomMuscle;
        
        $data = $this->callApi($url);

        // If no results for a specific muscle, try without parameters to get general exercises
        if (empty($data)) {
            $data = $this->callApi($this->baseUrl);
        }

        if (!empty($data)) {
            shuffle($data);
            return array_slice($data, 0, $limit);
        }
        
        // Final fallback: Local data to ensure section is never empty
        return $this->getStaticFallback($limit);
    }

    private function callApi($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Api-Key: ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            return json_decode($response, true);
        }
        return null;
    }

    private function getStaticFallback($limit) {
        $fallbacks = [
            [
                'name' => 'Pushups',
                'muscle' => 'chest',
                'difficulty' => 'beginner',
                'instructions' => "Start in a plank position. Lower your body until your chest nearly touches the floor. Push yourself back up."
            ],
            [
                'name' => 'Bodyweight Squats',
                'muscle' => 'quadriceps',
                'difficulty' => 'beginner',
                'instructions' => "Stand with feet shoulder-width apart. Lower your hips back and down as if sitting in a chair. Keep your chest up and return to standing."
            ],
            [
                'name' => 'Plank',
                'muscle' => 'abdominals',
                'difficulty' => 'beginner',
                'instructions' => "Hold a pushup position but with your weight on your forearms. Keep your body in a straight line and hold for as long as possible."
            ]
        ];
        shuffle($fallbacks);
        return array_slice($fallbacks, 0, $limit);
    }
}
