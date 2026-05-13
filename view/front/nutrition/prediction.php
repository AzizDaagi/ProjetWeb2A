<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projection Nutritionnelle</title>
    <link rel="stylesheet" href="view/front/assets/css/style.css">
    <link rel="stylesheet" href="view/front/assets/css/prediction.css">
</head>

<body>
    <?php require __DIR__ . '/../partials/navbar.php'; ?>

    <main
        class="prediction-page"
        data-prediction-dashboard
        data-scenarios-url="index.php?action=prediction_scenarios"
        data-trend-url="index.php?action=prediction_weekly_trend"
        data-confidence-url="index.php?action=prediction_confidence"
        data-what-if-url="index.php?action=prediction_what_if">
        <div class="container prediction-shell">
            <section class="prediction-hero">
                <div class="prediction-hero__content">
                    <p class="prediction-kicker">Smart Nutrition</p>
                    <h1>Projection Nutritionnelle</h1>
                    <p class="prediction-subtitle">
                        Estime ton evolution a partir de tes habitudes reelles.
                    </p>
                </div>

                <div class="prediction-hero__meta">
                    <div class="prediction-meta-card">
                        <span class="prediction-meta-card__label">Mise a jour</span>
                        <strong id="predictionUpdatedAt">--</strong>
                    </div>
                    <div class="prediction-meta-card">
                        <span class="prediction-meta-card__label">Confiance</span>
                        <strong id="predictionConfidenceBadge" class="prediction-badge prediction-badge-neutral">--</strong>
                    </div>
                </div>
            </section>

            <div id="predictionError" class="prediction-alert prediction-alert-error" hidden></div>
            <div id="predictionInfo" class="prediction-alert prediction-alert-info">Chargement de la projection nutritionnelle...</div>
            <div id="predictionLowData" class="prediction-alert prediction-alert-warning" hidden></div>

            <section class="prediction-card prediction-explainer" aria-labelledby="predictionHowItWorksTitle">
                <div class="prediction-section__head prediction-section__head--compact">
                    <div>
                        <p class="prediction-card__label">Comment ca marche ?</p>
                        <h2 id="predictionHowItWorksTitle">Comprendre la projection</h2>
                    </div>

                    <div class="prediction-explainer__badges" aria-hidden="true">
                        <span class="prediction-badge prediction-badge-soft">28 jours</span>
                        <span class="prediction-badge prediction-badge-soft">3 scenarios</span>
                        <span class="prediction-badge prediction-badge-soft">What-if</span>
                    </div>
                </div>

                <p class="prediction-explainer__lead">
                    Ce module analyse tes repas enregistres sur les 28 derniers jours et les compare a ton objectif calorique.
                    Il calcule ensuite ta tendance moyenne, la regularite de tes ecarts et un niveau de confiance.
                    A partir de ces donnees, il genere trois scenarios : rythme actuel, scenario optimiste et scenario prudent.
                    Le simulateur what-if permet de tester l'impact d'un changement quotidien, par exemple manger 200 kcal de moins ou de plus par jour.
                    La projection reste indicative et depend de la quantite de donnees enregistrees.
                </p>

                <ul class="prediction-explainer__list">
                    <li>Analyse les 28 derniers jours de repas enregistres.</li>
                    <li>Compare les calories consommees a l'objectif calorique.</li>
                    <li>Calcule tendance, regularite et niveau de confiance.</li>
                    <li>Genere 3 scenarios : actuel, optimiste et prudent.</li>
                    <li>Le simulateur what-if teste un changement de calories par jour.</li>
                    <li>La projection reste indicative, pas definitive.</li>
                </ul>
            </section>

            <section class="prediction-summary-grid">
                <article class="prediction-card prediction-card-summary">
                    <div class="prediction-card__head">
                        <div>
                            <p class="prediction-card__label">Resume principal</p>
                            <h2 id="predictionMainDate">--</h2>
                        </div>
                    </div>
                    <p id="predictionMainMessage" class="prediction-muted">
                        Chargement de la projection en cours.
                    </p>

                    <div id="predictionProgressWrap" class="prediction-progress-wrap" hidden>
                        <div class="prediction-progress-meta">
                            <span id="predictionProgressText">0%</span>
                            <span id="predictionProgressWeights">--</span>
                        </div>
                        <div class="prediction-progress" aria-hidden="true">
                            <span id="predictionProgressBar" class="prediction-progress__bar" style="width:0%;"></span>
                        </div>
                    </div>

                    <p id="predictionProgressNote" class="prediction-muted">
                        Progression vers l'objectif indisponible avec les donnees actuelles.
                    </p>
                </article>

                <article class="prediction-card prediction-card-side">
                    <p class="prediction-card__label">Fiabilite actuelle</p>
                    <h2 id="predictionConfidenceValue">--</h2>
                    <p id="predictionConfidenceLabel" class="prediction-muted">En attente des donnees.</p>
                    <div class="prediction-confidence-metrics">
                        <div class="prediction-mini-stat">
                            <span>Jours logges</span>
                            <strong id="predictionLoggedDays">--</strong>
                        </div>
                        <div class="prediction-mini-stat">
                            <span>Ecart-type</span>
                            <strong id="predictionStdDev">--</strong>
                        </div>
                    </div>
                </article>
            </section>

            <section class="prediction-section">
                <div class="prediction-section__head">
                    <div>
                        <h2>Scenarios predictifs</h2>
                        <p class="prediction-muted">Compare le rythme actuel avec deux variantes simples.</p>
                    </div>
                </div>
                <div id="predictionScenarioCards" class="prediction-scenarios">
                    <article class="prediction-card prediction-card-scenario">
                        <p class="prediction-muted">Chargement des scenarios...</p>
                    </article>
                </div>
            </section>

            <section class="prediction-grid">
                <article class="prediction-card">
                    <div class="prediction-section__head prediction-section__head--compact">
                        <div>
                            <h2>Tendance des 4 semaines</h2>
                            <p id="predictionTrendMessage" class="prediction-muted">Analyse en cours.</p>
                        </div>
                        <div class="prediction-trend-badges">
                            <span id="predictionTrendDirection" class="prediction-badge prediction-badge-neutral">--</span>
                            <span id="predictionTrendSlope" class="prediction-badge prediction-badge-soft">Slope --</span>
                        </div>
                    </div>

                    <div class="prediction-table-wrap">
                        <table class="prediction-table">
                            <thead>
                                <tr>
                                    <th>Semaine</th>
                                    <th>Calories moy.</th>
                                    <th>Delta moy.</th>
                                    <th>Jours logges</th>
                                </tr>
                            </thead>
                            <tbody id="predictionWeeklyTable">
                                <tr>
                                    <td colspan="4">Chargement des tendances...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="prediction-card">
                    <div class="prediction-section__head prediction-section__head--compact">
                        <div>
                            <h2>Simulateur what-if</h2>
                            <p class="prediction-muted">Teste un ajustement de calories sans recharger la page.</p>
                        </div>
                    </div>

                    <form id="predictionWhatIfForm" class="prediction-form" novalidate>
                        <label for="predictionCalorieChange">Modifier mes calories de X kcal/jour</label>
                        <div class="prediction-form__row">
                            <input
                                type="number"
                                id="predictionCalorieChange"
                                min="-1000"
                                max="1000"
                                step="50"
                                value="-200">
                            <button type="submit" id="predictionWhatIfButton" class="btn btn-primary">Simuler</button>
                        </div>
                    </form>

                    <div id="predictionWhatIfResult" class="prediction-simulation">
                        <p class="prediction-muted">Aucune simulation lancee pour le moment.</p>
                    </div>
                </article>
            </section>

            <p id="predictionDisclaimer" class="prediction-disclaimer">
                Projection indicative basee sur les donnees enregistrees, a interpreter avec prudence.
            </p>
        </div>
    </main>

<script src="/projetwebmalek/view/front/assets/js/theme.js"></script>
    <script src="view/front/assets/js/prediction.js"></script>
</body>

</html>
