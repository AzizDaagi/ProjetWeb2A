<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Nutrition</title>
    <link rel="stylesheet" href="/projet-web-25-26/view/front/assets/css/style.css">
    <link rel="stylesheet" href="/projet-web-25-26/view/front/assets/css/nutrition-dashboard.css">
</head>

<body>
    <?php require __DIR__ . '/../partials/navbar.php'; ?>

    <div
        class="nutrition-dashboard-page"
        data-nutrition-dashboard
        data-summary-url="index.php?action=nutrition_dashboard_summary"
        data-health-url="index.php?action=nutrition_health_score"
        data-recommendations-url="index.php?action=nutrition_daily_recommendations"
        data-weekly-url="index.php?action=nutrition_weekly_analysis">
        <div class="container nutrition-dashboard-shell">
            <section class="nutrition-hero">
                <div class="nutrition-hero-copy">
                    <p class="nutrition-kicker">Smart Nutrition</p>
                    <h1>Gestion nutritionnelle intelligente</h1>
                    <p class="nutrition-subtitle">
                        Un resume simple de ta journee, de ta semaine et des actions utiles a faire ensuite.
                    </p>
                </div>
                <div class="nutrition-hero-action">
                    <button type="button" class="btn btn-primary nutrition-refresh-btn" id="nutritionRefresh">
                        Rafraichir
                    </button>
                </div>
            </section>

            <div id="nutritionDashboardError" class="nutrition-alert nutrition-alert-error" hidden></div>
            <div id="nutritionDashboardInfo" class="nutrition-alert nutrition-alert-info">Chargement du dashboard nutrition...</div>

            <section class="nutrition-grid">
                <article class="nutrition-card">
                    <p class="nutrition-card-label">Score du jour</p>
                    <h2 id="nutritionHealthScore">--</h2>
                    <p id="nutritionHealthSummary" class="nutrition-muted">En attente des donnees.</p>
                </article>

                <article class="nutrition-card nutrition-card-hydration">
                    <p class="nutrition-card-label">Hydratation du jour</p>
                    <h2 id="nutritionHydrationAmount">-- / 2000 ml</h2>
                    <p id="nutritionHydrationGlasses" class="nutrition-muted">0 verre</p>
                    <div class="nutrition-progress" aria-hidden="true">
                        <span id="nutritionHydrationProgressBar" class="nutrition-progress-bar" style="width:0%;"></span>
                    </div>
                    <p id="nutritionHydrationProgressText" class="nutrition-progress-text">0%</p>
                </article>

                <article class="nutrition-card nutrition-card-summary">
                    <p class="nutrition-card-label">Resume de la journee</p>
                    <h2 id="nutritionDayHeading">--</h2>
                    <p id="nutritionDaySummary" class="nutrition-muted">Resume quotidien en attente.</p>
                    <p id="nutritionSugarSummary" class="nutrition-muted" hidden>Sucre du jour : --</p>
                </article>
            </section>

            <section class="nutrition-panels">
                <article class="nutrition-panel">
                    <div class="nutrition-panel-head">
                        <h3>Recommandations du jour</h3>
                    </div>
                    <div id="nutritionRecommendations" class="nutrition-list">
                        <p class="nutrition-empty">Aucune recommandation pour le moment.</p>
                    </div>
                </article>

                <article class="nutrition-panel">
                    <div class="nutrition-panel-head">
                        <h3>Analyse hebdomadaire</h3>
                    </div>
                    <div id="nutritionWeeklyDetails" class="nutrition-list">
                        <p class="nutrition-empty">Aucune analyse disponible.</p>
                    </div>
                </article>
            </section>
        </div>
    </div>

<script src="/projetwebmalek/view/front/assets/js/theme.js"></script>
    <script src="view/front/assets/js/nutrition-dashboard.js"></script>
</body>

</html>
