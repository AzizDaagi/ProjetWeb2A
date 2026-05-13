<?php

class WeatherController
{
    private $config;

    public function __construct()
    {
        $configFile = __DIR__ . '/../model/config.php';
        $this->config = file_exists($configFile) ? include $configFile : [];
    }

    public function currentSportWeather()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->respondJson([
                'success' => false,
                'message' => 'Session invalide.',
            ], 401);
        }

        $apiKey = trim((string) getenv('OPENWEATHER_API_KEY'));
        if ($apiKey === '') {
            $this->respondJson([
                'success' => false,
                'message' => 'La cle API meteo est manquante.',
            ], 503);
        }

        $defaultLat = (float) ($this->config['weather_default_lat'] ?? 36.8065);
        $defaultLon = (float) ($this->config['weather_default_lon'] ?? 10.1815);
        $lat = $this->normalizeCoordinate($_GET['lat'] ?? null, $defaultLat, -90, 90);
        $lon = $this->normalizeCoordinate($_GET['lon'] ?? null, $defaultLon, -180, 180);

        $weather = $this->fetchWeather($lat, $lon, $apiKey);
        if (!$weather['success']) {
            $this->respondJson($weather, 502);
        }

        $payload = $this->buildSportAdvicePayload($weather['data']);
        $this->respondJson([
            'success' => true,
            'weather' => $payload,
        ]);
    }

    private function respondJson($payload, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        exit;
    }

    private function normalizeCoordinate($value, $defaultValue, $min, $max)
    {
        if ($value === null || $value === '') {
            return $defaultValue;
        }

        if (!is_numeric($value)) {
            return $defaultValue;
        }

        $numericValue = (float) $value;
        if ($numericValue < $min || $numericValue > $max) {
            return $defaultValue;
        }

        return $numericValue;
    }

    private function fetchWeather($lat, $lon, $apiKey)
    {
        if (!function_exists('curl_init')) {
            return [
                'success' => false,
                'message' => 'cURL indisponible pour la meteo.',
            ];
        }

        $query = http_build_query([
            'lat' => $lat,
            'lon' => $lon,
            'appid' => $apiKey,
            'units' => 'metric',
            'lang' => 'fr',
        ]);

        $url = 'https://api.openweathermap.org/data/2.5/weather?' . $query;
        $timeout = (int) ($this->config['weather_api_timeout'] ?? 10);
        if ($timeout < 1) {
            $timeout = 10;
        }

        $ch = curl_init($url);
        if ($ch === false) {
            return [
                'success' => false,
                'message' => 'Impossible d\'initialiser la requete meteo.',
            ];
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlError !== '') {
            return [
                'success' => false,
                'message' => 'Erreur meteo: ' . $curlError,
            ];
        }

        $decoded = json_decode((string) $response, true);
        if (!is_array($decoded)) {
            return [
                'success' => false,
                'message' => 'Reponse meteo invalide.',
            ];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $message = trim((string) ($decoded['message'] ?? 'Service meteo indisponible.'));
            return [
                'success' => false,
                'message' => 'API meteo ' . $httpCode . ': ' . $message,
            ];
        }

        return [
            'success' => true,
            'data' => $decoded,
        ];
    }

    private function buildSportAdvicePayload(array $weather)
    {
        $main = $weather['main'] ?? [];
        $wind = $weather['wind'] ?? [];
        $weatherList = $weather['weather'] ?? [];
        $weatherItem = is_array($weatherList) && isset($weatherList[0]) && is_array($weatherList[0])
            ? $weatherList[0]
            : [];

        $conditionId = (int) ($weatherItem['id'] ?? 0);
        $condition = trim((string) ($weatherItem['description'] ?? 'Conditions indisponibles'));
        $temperature = (float) ($main['temp'] ?? 0);
        $feelsLike = (float) ($main['feels_like'] ?? $temperature);
        $humidity = (int) ($main['humidity'] ?? 0);
        $windKmh = round(((float) ($wind['speed'] ?? 0)) * 3.6, 1);
        $city = trim((string) ($weather['name'] ?? 'Votre zone'));
        $country = trim((string) ($weather['sys']['country'] ?? ''));

        $sportStatus = 'good';
        $sportTitle = 'Bon moment pour faire du sport';
        $sportAdvice = 'Les conditions actuelles sont favorables a une activite physique en exterieur.';
        $iconClass = 'fa-solid fa-person-running';

        if ($conditionId >= 200 && $conditionId < 300) {
            $sportStatus = 'bad';
            $sportTitle = 'Sport exterieur deconseille';
            $sportAdvice = 'Risque d\'orage detecte. Privilegiez une activite sportive en interieur.';
            $iconClass = 'fa-solid fa-cloud-bolt';
        } elseif (($conditionId >= 300 && $conditionId < 600) || $windKmh >= 40) {
            $sportStatus = 'bad';
            $sportTitle = 'Sortie sportive a eviter';
            $sportAdvice = 'La pluie ou le vent sont trop presents pour une seance confortable dehors.';
            $iconClass = 'fa-solid fa-cloud-rain';
        } elseif ($conditionId >= 600 && $conditionId < 700) {
            $sportStatus = 'bad';
            $sportTitle = 'Conditions glissantes';
            $sportAdvice = 'La neige ou le verglas rendent les deplacements exterieurs peu surs.';
            $iconClass = 'fa-solid fa-snowflake';
        } elseif ($temperature >= 33 || $feelsLike >= 35) {
            $sportStatus = 'caution';
            $sportTitle = 'Sport avec prudence';
            $sportAdvice = 'La chaleur est elevee. Hydratez-vous bien et privilegiez les heures fraiches.';
            $iconClass = 'fa-solid fa-temperature-high';
        } elseif ($temperature <= 5) {
            $sportStatus = 'caution';
            $sportTitle = 'Sport possible mais couvrez-vous';
            $sportAdvice = 'Le froid est marque. Echauffez-vous davantage et adaptez la duree de la seance.';
            $iconClass = 'fa-solid fa-temperature-low';
        } elseif ($humidity >= 85) {
            $sportStatus = 'caution';
            $sportTitle = 'Sport modere recommande';
            $sportAdvice = 'L\'air est tres humide. Ralentissez le rythme si vous sentez une gene respiratoire.';
            $iconClass = 'fa-solid fa-water';
        } elseif ($conditionId === 800) {
            $iconClass = 'fa-solid fa-sun';
        } elseif ($conditionId > 800) {
            $iconClass = 'fa-solid fa-cloud-sun';
        }

        return [
            'location' => trim($city . ($country !== '' ? ', ' . $country : '')),
            'temperature_c' => round($temperature, 1),
            'feels_like_c' => round($feelsLike, 1),
            'humidity' => $humidity,
            'wind_kmh' => $windKmh,
            'condition' => $condition,
            'sport_status' => $sportStatus,
            'sport_title' => $sportTitle,
            'sport_advice' => $sportAdvice,
            'icon_class' => $iconClass,
            'updated_at' => date('H:i'),
        ];
    }
}
