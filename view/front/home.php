<div class="hero-wrapper">
    <div class="cycle-diagram">
        <svg class="orbit-ring" viewBox="0 0 400 400">
            <circle cx="200" cy="200" r="140" class="ring-track" />
            <circle cx="200" cy="200" r="140" class="ring-glow" />
        </svg>

        <button
            type="button"
            class="node node-1 home-topic-btn active"
            title="Durabilite"
            data-topic-title="Durabilite"
            data-topic-description="Faites des choix alimentaires durables en privilegient les produits locaux, de saison et en limitant le gaspillage."
        >
            <span class="node-icon"><i class="fa-solid fa-leaf"></i></span>
            <span class="node-label">Durabilite</span>
        </button>
        <button
            type="button"
            class="node node-2 home-topic-btn"
            title="Alimentation saine"
            data-topic-title="Alimentation saine"
            data-topic-description="Construisez des repas equilibres avec plus d'aliments frais, des portions adaptees et moins de produits ultra-transformes."
        >
            <span class="node-icon"><i class="fa-solid fa-apple-whole"></i></span>
            <span class="node-label">Alimentation saine</span>
        </button>
        <button
            type="button"
            class="node node-3 home-topic-btn"
            title="Mode de vie"
            data-topic-title="Mode de vie"
            data-topic-description="Adoptez un mode de vie actif avec une bonne hydratation, un sommeil regulier et des habitudes quotidiennes stables."
        >
            <span class="node-icon"><i class="fa-solid fa-person-running"></i></span>
            <span class="node-label">Mode de vie</span>
        </button>
        <button
            type="button"
            class="node node-4 home-topic-btn"
            title="Nutrition"
            data-topic-title="Nutrition"
            data-topic-description="Suivez vos besoins nutritionnels, ajustez vos apports et comprenez l'impact des macronutriments sur votre energie."
        >
            <span class="node-icon"><i class="fa-solid fa-utensils"></i></span>
            <span class="node-label">Nutrition</span>
        </button>

        <div class="center-piece">
            <div class="pulse-core"></div>
            <h3>Systeme<br>Smart</h3>
        </div>
    </div>

    <div class="hero-content">
        <h1>Smart Nutrition</h1>
        <p class="subtitle-text">Systeme alimentaire durable et intelligent</p>
        <p class="description-text">
            Bienvenue sur votre assistant nutritionnel personnel.<br>
            Analysez, suivez et optimisez votre nutrition en temps reel.
        </p>
    </div>

    <div id="homeTopicDescription" class="topic-description-card" tabindex="-1">
        <h2 id="homeTopicTitle">Durabilite</h2>
        <p id="homeTopicText">Faites des choix alimentaires durables en privilegient les produits locaux, de saison et en limitant le gaspillage.</p>
    </div>

    <section
        id="homeWeatherCard"
        class="home-weather-card is-loading"
        data-weather-card="true"
        data-weather-endpoint="/smart_nutritionn/gestionActiviteesportive/index.php?action=weather-sport"
        aria-live="polite"
    >
        <div class="home-weather-head">
            <div>
                <p class="home-weather-eyebrow">Meteo sportive</p>
                <h2>Verifier si c'est le bon moment pour faire du sport</h2>
            </div>
            <span class="home-weather-badge" id="homeWeatherBadge">Chargement...</span>
        </div>

        <div class="home-weather-body">
            <div class="home-weather-main">
                <div class="home-weather-icon" id="homeWeatherIcon">
                    <i class="fa-solid fa-cloud-sun"></i>
                </div>
                <div>
                    <p class="home-weather-location" id="homeWeatherLocation">Localisation en cours...</p>
                    <p class="home-weather-temp" id="homeWeatherTemp">--.-°C</p>
                    <p class="home-weather-condition" id="homeWeatherCondition">Recherche de la meteo actuelle...</p>
                </div>
            </div>

            <div class="home-weather-stats">
                <div class="home-weather-stat">
                    <span>Ressenti</span>
                    <strong id="homeWeatherFeelsLike">--.-°C</strong>
                </div>
                <div class="home-weather-stat">
                    <span>Humidite</span>
                    <strong id="homeWeatherHumidity">--%</strong>
                </div>
                <div class="home-weather-stat">
                    <span>Vent</span>
                    <strong id="homeWeatherWind">-- km/h</strong>
                </div>
                <div class="home-weather-stat">
                    <span>Mise a jour</span>
                    <strong id="homeWeatherUpdated">--:--</strong>
                </div>
            </div>
        </div>

        <div class="home-weather-advice">
            <h3 id="homeWeatherTitle">Analyse en cours</h3>
            <p id="homeWeatherAdvice">Nous recuperons la meteo actuelle pour vous dire si une sortie sportive est recommandee.</p>
        </div>
    </section>
</div>
