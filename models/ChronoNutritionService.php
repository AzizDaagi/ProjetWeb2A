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
        $profile = $this->getProfileData($userId);

        if ($profile === null) {
            return $this->buildEmptyProfileResponse();
        }

        $chronotype = $profile['chronotype'] ?? 'standard';
        $sleepMetrics = $this->calculateSleepMetrics($profile);

        $timings = [
            'leve_tot' => [
                'label' => 'Leve-tot',
                'message' => "Ton profil leve-tot favorise des repas plus tot dans la journee.",
                'meals' => [
                    'breakfast' => ['label' => 'Petit-dejeuner', 'start' => '06:15', 'end' => '07:45', 'message' => 'Un premier repas assez tot aide a lancer la journee avec regularite.'],
                    'lunch' => ['label' => 'Dejeuner', 'start' => '11:30', 'end' => '13:00', 'message' => 'Un dejeuner place en milieu de journee soutient ton energie sans trop decaler le soir.'],
                    'dinner' => ['label' => 'Diner', 'start' => '18:30', 'end' => '20:00', 'message' => 'Un diner plutot avance laisse une transition plus calme avant la nuit.'],
                ],
            ],
            'standard' => [
                'label' => 'Standard',
                'message' => "Ton profil suit des horaires alimentaires classiques.",
                'meals' => [
                    'breakfast' => ['label' => 'Petit-dejeuner', 'start' => '07:00', 'end' => '08:30', 'message' => 'Cette plage matinale reste compatible avec un rythme quotidien classique.'],
                    'lunch' => ['label' => 'Dejeuner', 'start' => '12:00', 'end' => '13:30', 'message' => 'Un dejeuner regulier aide a garder un rythme stable jusqu au soir.'],
                    'dinner' => ['label' => 'Diner', 'start' => '19:00', 'end' => '20:30', 'message' => 'Un diner en debut de soiree limite les repas trop tardifs.'],
                ],
            ],
            'couche_tard' => [
                'label' => 'Couche-tard',
                'message' => "Ton profil couche-tard decale legerement les repas, tout en evitant un diner trop tardif.",
                'meals' => [
                    'breakfast' => ['label' => 'Petit-dejeuner', 'start' => '07:45', 'end' => '09:15', 'message' => 'Le matin demarre un peu plus tard, sans repousser excessivement le premier repas.'],
                    'lunch' => ['label' => 'Dejeuner', 'start' => '12:30', 'end' => '14:00', 'message' => 'Ce dejeuner legerement decale suit mieux un rythme plus tardif.'],
                    'dinner' => ['label' => 'Diner', 'start' => '19:30', 'end' => '21:00', 'message' => 'Le diner reste maitrise pour eviter de manger trop pres du coucher.'],
                ],
            ],
        ];

        $timingConfig = $timings[$chronotype] ?? $timings['standard'];
        $meals = $timingConfig['meals'];

        if ($meals['dinner']['end'] > '21:30') {
            $meals['dinner']['end'] = '21:30';
        }

        $mealList = $this->buildMealPlan($meals, $profile);
        $personalization = $this->buildPersonalization($profile, $sleepMetrics, $timingConfig);
        $preferredMealsCount = (int) ($profile['preferred_meals_count'] ?? 3);

        return [
            'data' => [
                'summary' => [
                    'chronotype' => $chronotype,
                    'chronotype_label' => $timingConfig['label'],
                    'message' => $timingConfig['message'],
                    'wake_time' => $this->formatTime($profile['wake_time'] ?? '07:00'),
                    'sleep_time' => $this->formatTime($profile['sleep_time'] ?? '23:00'),
                    'sleep_duration_h' => $sleepMetrics['duration_h'],
                    'sleep_duration_label' => $sleepMetrics['label'],
                    'preferred_meals_count' => $preferredMealsCount,
                    'preferred_meals_count_label' => $this->formatMealsCountLabel($preferredMealsCount),
                ],
                'meals' => $mealList,
                'personalization' => $personalization,
            ],
            'error' => null,
            'cached' => false,
        ];
    }

    public function getFastingWindow($userId)
    {
        $profile = $this->getProfileData($userId);

        if ($profile === null) {
            return $this->buildEmptyProfileResponse();
        }

        $wakeTime = $profile['wake_time'] ?? '07:00';
        $sleepTime = $profile['sleep_time'] ?? '23:00';
        $chronotype = $profile['chronotype'] ?? 'standard';
        $sleepQuality = $profile['sleep_quality'] ?? 'moyenne';
        $workoutTime = $profile['workout_time'] ?? 'aucun';
        $age = $this->resolveChronoAge($profile);
        $wakeMinutes = $this->timeToMinutes($wakeTime);
        $sleepMinutes = $this->timeToMinutes($sleepTime);

        if ($wakeMinutes === null || $sleepMinutes === null) {
            return [
                'data' => null,
                'error' => "Impossible de calculer le jeune intermittent pour le moment.",
                'cached' => false,
            ];
        }

        $protocol = '12/12';
        $fastDuration = 12;
        $message = "Une fenetre 12/12 permet de structurer les repas sans approche restrictive.";

        if ($sleepQuality === 'mauvaise') {
            $protocol = '10/14';
            $fastDuration = 10;
            $message = "En cas de fatigue importante, une approche douce aide a garder des reperes sans trop retarder le premier repas.";
        }

        if ($workoutTime === 'matin') {
            $protocol = '12/12';
            $fastDuration = 12;
            $message = "Avec un sport le matin, mieux vaut eviter un jeune long et prevoir hydratation puis repas adapte apres effort.";
        }

        if ($age === null || $age < 18) {
            $protocol = $sleepQuality === 'mauvaise' ? '10/14' : '12/12';
            $fastDuration = $protocol === '10/14' ? 10 : 12;
            $message = "Quand l age est inconnu ou encore jeune, reste sur une pause douce et adapte les horaires selon les besoins du quotidien.";
        }

        $eatingDuration = 24 - $fastDuration;
        $eatingStartMinutes = $this->resolveEatingStartMinutes($wakeMinutes, $chronotype, $sleepQuality, $workoutTime);
        $latestEatingEnd = $chronotype === 'couche_tard' ? (21 * 60) + 30 : 21 * 60;
        $sleepBasedLimit = $this->normalizeMinutes($sleepMinutes - 120);
        $eatingEndMinutes = $this->normalizeMinutes($eatingStartMinutes + ($eatingDuration * 60));
        $candidateEnd = min($eatingEndMinutes, $latestEatingEnd, $sleepBasedLimit);

        if ($candidateEnd <= $eatingStartMinutes) {
            $candidateEnd += 24 * 60;
        }

        $actualEatingDuration = round(($candidateEnd - $eatingStartMinutes) / 60, 2);
        $actualFastDuration = round(24 - $actualEatingDuration, 2);
        $displayEatingEnd = $this->normalizeMinutes($candidateEnd);
        $displayFastStart = $displayEatingEnd;
        $displayFastEnd = $eatingStartMinutes;

        if ($actualFastDuration >= 11.5 && $actualFastDuration <= 12.4) {
            $protocol = '12/12';
        } elseif ($actualFastDuration >= 9.5 && $actualFastDuration <= 10.4) {
            $protocol = '10/14';
        }

        return [
            'data' => [
                'protocol' => $protocol,
                'fast_start' => $this->minutesToTime($displayFastStart),
                'fast_end' => $this->minutesToTime($displayFastEnd),
                'fast_duration_h' => $actualFastDuration,
                'eating_start' => $this->minutesToTime($eatingStartMinutes),
                'eating_end' => $this->minutesToTime($displayEatingEnd),
                'eating_duration_h' => $actualEatingDuration,
                'message' => $message,
                'disclaimer' => "Conseil informatif, pas une prescription medicale.",
            ],
            'error' => null,
            'cached' => false,
        ];
    }

    public function getNutrientTiming()
    {
        $periods = [
            'morning' => [
                'label' => 'Matin',
                'nutrients' => ['Proteines', 'Fibres', 'Glucides complexes'],
                'tip' => 'Mise sur un depart progressif avec des aliments rassasiants et faciles a tenir toute la matinee.',
            ],
            'noon' => [
                'label' => 'Midi',
                'nutrients' => ['Proteines maigres', 'Legumes', 'Feculents complets'],
                'tip' => 'Un repas complet et equilibre aide a garder une energie stable sur la deuxieme partie de journee.',
            ],
            'evening' => [
                'label' => 'Soir',
                'nutrients' => ['Legumes', 'Proteines legeres', 'Sucres rapides limites'],
                'tip' => 'Le soir, privilegie un repas plus simple et facile a digerer.',
            ],
        ];

        return [
            'data' => [
                'periods' => [
                    array_merge(['key' => 'morning'], $periods['morning']),
                    array_merge(['key' => 'noon'], $periods['noon']),
                    array_merge(['key' => 'evening'], $periods['evening']),
                ],
                'morning' => $periods['morning'],
                'noon' => $periods['noon'],
                'evening' => $periods['evening'],
            ],
            'error' => null,
            'cached' => false,
        ];
    }

    public function sleepSync($userId, array $data = [])
    {
        $profile = $this->getProfileData($userId);

        if ($profile === null) {
            return $this->buildEmptyProfileResponse();
        }

        $quality = $data['sleep_quality'] ?? ($profile['sleep_quality'] ?? 'moyenne');
        $sleepMetrics = $this->calculateSleepMetrics($profile);
        $caffeineRecommendation = $this->buildCaffeineRecommendation($profile);
        $recommendations = [];
        $qualityLabels = [
            'bonne' => 'Bonne',
            'moyenne' => 'Moyenne',
            'mauvaise' => 'Mauvaise',
        ];

        $recommendations[] = $this->buildSleepRecommendation(
            $sleepMetrics['label'],
            $sleepMetrics['message'],
            $sleepMetrics['priority']
        );

        if ($quality === 'mauvaise') {
            $recommendations = array_merge($recommendations, [
                $this->buildSleepRecommendation('Aliments riches en magnesium', 'Privilegie amandes, noix de cajou, epinards, banane ou chocolat noir en quantite raisonnable.', 'high'),
                $this->buildSleepRecommendation('Limiter la cafeine', 'Evite cafe, the noir et sodas cafeines en deuxieme partie de journee.', 'high'),
                $this->buildSleepRecommendation('Diner leger et anticipe', 'Essaie de garder un repas du soir simple avec au moins deux heures avant le coucher.', 'high'),
            ]);
        } elseif ($quality === 'moyenne') {
            $recommendations = array_merge($recommendations, [
                $this->buildSleepRecommendation("Eviter l'alcool le soir", "L'alcool peut fragmenter le sommeil meme s'il donne une impression de detente.", 'medium'),
                $this->buildSleepRecommendation('Tisane avant le coucher', 'Une tisane simple en fin de soiree peut aider a installer un rituel plus apaisant.', 'medium'),
                $this->buildSleepRecommendation('Diner anticipe', 'Garde un peu de marge entre le diner et le coucher pour limiter l inconfort digestif.', 'medium'),
            ]);
        } else {
            $recommendations = array_merge($recommendations, [
                $this->buildSleepRecommendation('Maintenir tes bonnes habitudes', 'Ton rythme actuel semble deja plutot stable, garde surtout une regularite sur la semaine.', 'low'),
                $this->buildSleepRecommendation('Rythme alimentaire synchronise', 'Ton organisation alimentaire parait bien alignee avec ton sommeil.', 'low'),
            ]);
        }

        if ($caffeineRecommendation !== null) {
            $recommendations[] = $caffeineRecommendation;
        }

        return [
            'data' => [
                'sleep_quality' => $quality,
                'sleep_quality_label' => $qualityLabels[$quality] ?? 'Moyenne',
                'summary' => 'Quelques ajustements simples peuvent aider a mieux aligner le diner, la cafeine et le sommeil.',
                'sleep_duration_h' => $sleepMetrics['duration_h'],
                'sleep_duration_label' => $sleepMetrics['label'],
                'recommendations' => array_slice($recommendations, 0, 5),
            ],
            'error' => null,
            'cached' => false,
        ];
    }

    private function getProfileData($userId)
    {
        $response = $this->model->getProfile($userId);
        $profile = $response['data'] ?? null;

        return is_array($profile) ? $profile : null;
    }

    private function buildEmptyProfileResponse()
    {
        return [
            'data' => null,
            'error' => "Sauvegardez d'abord votre profil chrono pour voir les recommandations.",
            'cached' => false,
        ];
    }

    private function calculateSleepMetrics(array $profile)
    {
        $wakeTimestamp = strtotime((string) ($profile['wake_time'] ?? '07:00'));
        $sleepTimestamp = strtotime((string) ($profile['sleep_time'] ?? '23:00'));

        if ($wakeTimestamp === false || $sleepTimestamp === false) {
            return [
                'duration_h' => null,
                'label' => 'Sommeil estime indisponible',
                'message' => 'La duree estimee ne peut pas etre calculee pour le moment.',
                'priority' => 'medium',
            ];
        }

        if ($wakeTimestamp <= $sleepTimestamp) {
            $wakeTimestamp += 24 * 3600;
        }

        $duration = round(($wakeTimestamp - $sleepTimestamp) / 3600, 1);

        if ($duration < 6.5) {
            return [
                'duration_h' => $duration,
                'label' => 'Sommeil estime court',
                'message' => "Ta duree de sommeil estimee semble courte. Essaie de proteger davantage la fin de soiree et le coucher.",
                'priority' => 'high',
            ];
        }

        if ($duration <= 8.5) {
            return [
                'duration_h' => $duration,
                'label' => 'Sommeil estime correct',
                'message' => "Ta duree de sommeil estimee reste dans une zone plutot correcte pour garder un rythme regulier.",
                'priority' => 'low',
            ];
        }

        if ($duration > 9) {
            return [
                'duration_h' => $duration,
                'label' => 'Sommeil estime long',
                'message' => "La duree de sommeil parait genereuse. Observe surtout si tu te sens bien repose au reveil.",
                'priority' => 'low',
            ];
        }

        return [
            'duration_h' => $duration,
            'label' => 'Sommeil estime stable',
            'message' => "Ton amplitude de sommeil parait stable. Garde surtout de la regularite sur les horaires.",
            'priority' => 'low',
        ];
    }

    private function buildMealPlan(array $baseMeals, array $profile)
    {
        $preferredMealsCount = (int) ($profile['preferred_meals_count'] ?? 3);
        $meals = [];

        if ($preferredMealsCount === 2) {
            $meals[] = [
                'key' => 'first_main',
                'label' => 'Premier repas principal',
                'start' => $this->shiftTime($baseMeals['lunch']['start'], -150),
                'end' => $this->shiftTime($baseMeals['lunch']['start'], -60),
                'message' => 'Deux repas peuvent convenir si ce premier temps fort reste complet et rassasiant, sans collation forcee.',
            ];
            $meals[] = [
                'key' => 'dinner',
                'label' => 'Diner',
                'start' => $baseMeals['dinner']['start'],
                'end' => $baseMeals['dinner']['end'],
                'message' => 'Le deuxieme grand repere de la journee reste volontairement digeste pour ne pas trop charger la soiree.',
            ];

            return $meals;
        }

        $meals[] = array_merge(['key' => 'breakfast'], $baseMeals['breakfast']);

        if ($preferredMealsCount === 4) {
            $snackMeal = $this->buildSnackMeal($baseMeals, $profile);

            if (($profile['energy_dip'] ?? 'aucun') === 'fin_matin') {
                $meals[] = $snackMeal;
            }
        }

        $meals[] = array_merge(['key' => 'lunch'], $baseMeals['lunch']);

        if ($preferredMealsCount === 4 && ($profile['energy_dip'] ?? 'aucun') !== 'fin_matin') {
            $meals[] = $this->buildSnackMeal($baseMeals, $profile);
        }

        $meals[] = array_merge(['key' => 'dinner'], $baseMeals['dinner']);

        return $meals;
    }

    private function buildSnackMeal(array $baseMeals, array $profile)
    {
        $energyDip = $profile['energy_dip'] ?? 'aucun';

        if ($energyDip === 'fin_matin') {
            return [
                'key' => 'snack',
                'label' => 'Collation recommandee',
                'start' => $this->shiftTime($baseMeals['lunch']['start'], -120),
                'end' => $this->shiftTime($baseMeals['lunch']['start'], -90),
                'message' => 'Une petite collation peut aider a lisser le creux de fin de matinee si tu en ressens le besoin.',
            ];
        }

        if ($energyDip === 'soir') {
            return [
                'key' => 'snack',
                'label' => 'Collation recommandee',
                'start' => $this->shiftTime($baseMeals['dinner']['start'], -120),
                'end' => $this->shiftTime($baseMeals['dinner']['start'], -90),
                'message' => 'Si un petit creux arrive en fin de journee, prefere une option simple pour eviter le grignotage sucre tardif.',
            ];
        }

        if ($energyDip === 'apres_midi') {
            return [
                'key' => 'snack',
                'label' => 'Collation recommandee',
                'start' => $this->shiftTime($baseMeals['lunch']['end'], 150),
                'end' => $this->shiftTime($baseMeals['lunch']['end'], 180),
                'message' => 'Une collation courte peut etre placee ici pour soutenir l energie de l apres-midi.',
            ];
        }

        return [
            'key' => 'snack',
            'label' => 'Collation optionnelle',
            'start' => $this->shiftTime($baseMeals['lunch']['end'], 150),
            'end' => $this->shiftTime($baseMeals['lunch']['end'], 180),
            'message' => 'Cette collation reste optionnelle. Elle sert surtout de repere si tu preferes une structure a quatre prises.',
        ];
    }

    private function buildPersonalization(array $profile, array $sleepMetrics, array $timingConfig)
    {
        $preferredMealsCount = (int) ($profile['preferred_meals_count'] ?? 3);
        $energyPeak = $profile['energy_peak'] ?? null;
        $energyDip = $profile['energy_dip'] ?? 'aucun';
        $workoutTime = $profile['workout_time'] ?? 'aucun';
        $lastCaffeineTime = $profile['last_caffeine_time'] ?? 'aucun';

        $badges = [
            [
                'label' => 'Sommeil estime',
                'value' => $sleepMetrics['duration_h'] !== null ? $sleepMetrics['duration_h'] . ' h' : '--',
            ],
            [
                'label' => 'Repas souhaites',
                'value' => $this->formatMealsCountLabel($preferredMealsCount),
            ],
        ];

        if ($energyPeak !== null) {
            $badges[] = [
                'label' => "Pic d'energie",
                'value' => $this->formatEnergyPeakLabel($energyPeak),
            ];
        }

        if ($energyDip !== 'aucun') {
            $badges[] = [
                'label' => "Creux d'energie",
                'value' => $this->formatEnergyDipLabel($energyDip),
            ];
        }

        if ($workoutTime !== 'aucun') {
            $badges[] = [
                'label' => 'Sport',
                'value' => $this->formatWorkoutTimeLabel($workoutTime),
            ];
        }

        if ($lastCaffeineTime !== 'aucun') {
            $badges[] = [
                'label' => 'Dernier cafe',
                'value' => $this->formatLastCaffeineLabel($lastCaffeineTime),
            ];
        }

        $recommendations = [
            $this->buildChronoRecommendation($sleepMetrics['label'], $sleepMetrics['message'], $sleepMetrics['priority']),
            $this->buildChronoRecommendation(
                'Structure de repas',
                $this->buildMealsCountMessage($preferredMealsCount),
                $preferredMealsCount === 4 ? 'medium' : 'low'
            ),
            $this->buildChronoRecommendation('Rythme general', $timingConfig['message'], 'low'),
        ];

        if ($energyPeak !== null) {
            $recommendations[] = $this->buildChronoRecommendation(
                "Pic d'energie",
                $this->buildEnergyPeakMessage($energyPeak),
                'medium'
            );
        }

        if ($caffeineRecommendation = $this->buildCaffeineRecommendation($profile)) {
            $recommendations[] = $caffeineRecommendation;
        }

        if ($workoutTime !== 'aucun') {
            $recommendations[] = $this->buildChronoRecommendation(
                'Routine sport',
                $this->buildWorkoutMessage($workoutTime),
                'medium'
            );
        }

        if ($energyDip !== 'aucun') {
            $recommendations[] = $this->buildChronoRecommendation(
                "Creux d'energie",
                $this->buildEnergyDipMessage($energyDip),
                'low'
            );
        }

        return [
            'title' => 'Personnalisation chrono',
            'intro' => 'Ces ajustements s appuient sur ton energie percue, le sport, la cafeine, le nombre de repas souhaite et la duree de sommeil estimee.',
            'badges' => array_slice($badges, 0, 6),
            'recommendations' => array_slice($recommendations, 0, 5),
        ];
    }

    private function formatTime($time)
    {
        $timestamp = strtotime((string) $time);

        if ($timestamp === false) {
            return '--:--';
        }

        return date('H:i', $timestamp);
    }

    private function timeToMinutes($time)
    {
        $formatted = $this->formatTime($time);

        if ($formatted === '--:--') {
            return null;
        }

        [$hours, $minutes] = explode(':', $formatted);
        return ((int) $hours * 60) + (int) $minutes;
    }

    private function minutesToTime($minutes)
    {
        $normalizedMinutes = $this->normalizeMinutes($minutes);
        $hours = str_pad((string) floor($normalizedMinutes / 60), 2, '0', STR_PAD_LEFT);
        $mins = str_pad((string) ($normalizedMinutes % 60), 2, '0', STR_PAD_LEFT);

        return $hours . ':' . $mins;
    }

    private function normalizeMinutes($minutes)
    {
        $normalizedMinutes = ((int) $minutes) % 1440;

        if ($normalizedMinutes < 0) {
            $normalizedMinutes += 1440;
        }

        return $normalizedMinutes;
    }

    private function resolveEatingStartMinutes($wakeMinutes, $chronotype, $sleepQuality, $workoutTime)
    {
        $offset = 45;

        if ($chronotype === 'leve_tot') {
            $offset = 35;
        } elseif ($chronotype === 'couche_tard') {
            $offset = 60;
        }

        if ($sleepQuality === 'mauvaise') {
            $offset = min($offset, 40);
        }

        if ($workoutTime === 'matin') {
            $offset = 30;
        }

        return $this->normalizeMinutes($wakeMinutes + $offset);
    }

    private function resolveChronoAge(array $profile)
    {
        if (isset($profile['age']) && is_numeric($profile['age'])) {
            return (int) $profile['age'];
        }

        return null;
    }

    private function shiftTime($time, $minutes)
    {
        $timestamp = strtotime((string) $time);

        if ($timestamp === false) {
            return '--:--';
        }

        $baseMinutes = ((int) date('H', $timestamp) * 60) + (int) date('i', $timestamp);
        $normalizedMinutes = ($baseMinutes + (int) $minutes) % 1440;

        if ($normalizedMinutes < 0) {
            $normalizedMinutes += 1440;
        }

        $hours = str_pad((string) floor($normalizedMinutes / 60), 2, '0', STR_PAD_LEFT);
        $mins = str_pad((string) ($normalizedMinutes % 60), 2, '0', STR_PAD_LEFT);

        return $hours . ':' . $mins;
    }

    private function buildSleepRecommendation($title, $description, $priority)
    {
        $priorityLabels = [
            'high' => 'Priorite elevee',
            'medium' => 'Priorite moyenne',
            'low' => 'Priorite faible',
        ];

        return [
            'title' => $title,
            'description' => $description,
            'priority' => $priority,
            'priority_label' => $priorityLabels[$priority] ?? 'Priorite moyenne',
        ];
    }

    private function buildChronoRecommendation($title, $description, $priority)
    {
        return $this->buildSleepRecommendation($title, $description, $priority);
    }

    private function buildCaffeineRecommendation(array $profile)
    {
        $lastCaffeineTime = $profile['last_caffeine_time'] ?? 'aucun';
        $sleepTime = $this->formatTime($profile['sleep_time'] ?? '23:00');

        if ($lastCaffeineTime === 'apres_17h') {
            return $this->buildChronoRecommendation(
                'Cafeine tardive',
                "Un dernier cafe apres 17h peut se rapprocher du coucher a {$sleepTime} et rendre l endormissement moins fluide.",
                'high'
            );
        }

        if ($lastCaffeineTime === '14_17h') {
            return $this->buildChronoRecommendation(
                'Cafeine a surveiller',
                "Une cafeine prise entre 14h et 17h peut rester sensible si ton coucher arrive relativement tot vers {$sleepTime}.",
                'medium'
            );
        }

        return null;
    }

    private function buildMealsCountMessage($preferredMealsCount)
    {
        if ((int) $preferredMealsCount === 2) {
            return 'Deux repas principaux peuvent convenir si chacun reste complet, sans transformer la journee en restriction extreme ni imposer une collation.';
        }

        if ((int) $preferredMealsCount === 4) {
            return 'Une structure a quatre prises peut inclure une collation bien placee selon ton creux d energie, sans surcharge inutile.';
        }

        return 'Trois repas gardent une structure classique petit-dejeuner, dejeuner et diner, simple a tenir dans la duree.';
    }

    private function buildEnergyPeakMessage($energyPeak)
    {
        if ($energyPeak === 'matin') {
            return "Ton pic d energie arrive le matin : renforce surtout un petit-dejeuner equilibre avec proteines, fibres et glucides complexes.";
        }

        if ($energyPeak === 'apres_midi') {
            return "Ton pic d energie arrive l apres-midi : mise sur un dejeuner complet et garde une collation possible si la journee se prolonge.";
        }

        return "Ton energie monte davantage le soir : evite quand meme de repousser un gros repas trop tard et prefere un diner digeste.";
    }

    private function buildEnergyDipMessage($energyDip)
    {
        if ($energyDip === 'fin_matin') {
            return "En cas de creux en fin de matinee, une petite collation legere peut suffire si tu en ressens vraiment le besoin.";
        }

        if ($energyDip === 'apres_midi') {
            return "Pour un creux l apres-midi, fruit, yaourt ou noix restent souvent plus stables qu un snack tres sucre.";
        }

        return "Si le creux arrive le soir, evite le grignotage sucre tardif et garde plutot un repere de diner stable.";
    }

    private function buildWorkoutMessage($workoutTime)
    {
        if ($workoutTime === 'matin') {
            return 'Pour un sport le matin, pense a l hydratation puis a un petit-dejeuner adapte apres l effort.';
        }

        if ($workoutTime === 'midi' || $workoutTime === 'apres_midi') {
            return 'Autour d un sport a midi ou l apres-midi, vise un repas equilibre avant ou apres selon ton organisation.';
        }

        return 'Apres un sport le soir, garde un diner post-effort leger mais suffisamment proteine.';
    }

    private function formatEnergyPeakLabel($energyPeak)
    {
        $labels = [
            'matin' => 'Matin',
            'apres_midi' => 'Apres-midi',
            'soir' => 'Soir',
        ];

        return $labels[$energyPeak] ?? 'Non defini';
    }

    private function formatEnergyDipLabel($energyDip)
    {
        $labels = [
            'aucun' => 'Aucun',
            'fin_matin' => 'Fin de matinee',
            'apres_midi' => 'Apres-midi',
            'soir' => 'Soir',
        ];

        return $labels[$energyDip] ?? 'Aucun';
    }

    private function formatWorkoutTimeLabel($workoutTime)
    {
        $labels = [
            'aucun' => 'Aucun',
            'matin' => 'Matin',
            'midi' => 'Midi',
            'apres_midi' => 'Apres-midi',
            'soir' => 'Soir',
        ];

        return $labels[$workoutTime] ?? 'Aucun';
    }

    private function formatLastCaffeineLabel($lastCaffeineTime)
    {
        $labels = [
            'aucun' => 'Aucun',
            'avant_12h' => 'Avant 12h',
            '12_14h' => '12h-14h',
            '14_17h' => '14h-17h',
            'apres_17h' => 'Apres 17h',
        ];

        return $labels[$lastCaffeineTime] ?? 'Aucun';
    }

    private function formatMealsCountLabel($preferredMealsCount)
    {
        return (int) $preferredMealsCount . ' repas';
    }
}
