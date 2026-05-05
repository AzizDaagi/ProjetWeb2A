<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chrono-Nutrition</title>
    <link rel="stylesheet" href="views/front/assets/css/chrono-nutrition.css">
    <script src="views/front/assets/js/chrono-nutrition.js" defer></script>
</head>
<body>
    <header>
        <!-- Include header if exists -->
    </header>
    <main>
        <section>
            <h1>Chrono-Nutrition</h1>
            <form id="chrono-form">
                <label for="chronotype">Chronotype :</label>
                <select id="chronotype" name="chronotype">
                    <option value="leve_tot">Lève-tôt</option>
                    <option value="standard">Standard</option>
                    <option value="couche_tard">Couche-tard</option>
                </select>

                <label for="wake_time">Heure de réveil :</label>
                <input type="time" id="wake_time" name="wake_time">

                <label for="sleep_time">Heure de coucher :</label>
                <input type="time" id="sleep_time" name="sleep_time">

                <label for="sleep_quality">Qualité du sommeil :</label>
                <select id="sleep_quality" name="sleep_quality">
                    <option value="bonne">Bonne</option>
                    <option value="moyenne">Moyenne</option>
                    <option value="mauvaise">Mauvaise</option>
                </select>

                <button type="submit" id="saveProfile">Sauvegarder mon profil</button>
            </form>
        </section>

        <section id="results">
            <div id="block-timing" class="result-block">Horaires recommandés</div>
            <div id="block-fasting" class="result-block">Pause alimentaire nocturne</div>
            <div id="block-nutrients" class="result-block">Conseils nutritionnels</div>
            <div id="block-sleep" class="result-block">Recommandations sommeil</div>
        </section>
    </main>
    <footer>
        <!-- Include footer if exists -->
    </footer>
</body>
</html>