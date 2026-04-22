<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Objectif Nutritionnel</title>
    <link rel="stylesheet" href="views/front/assets/css/style.css">
</head>

<body>
    <?php require __DIR__ . '/../partials/navbar.php'; ?>

    <div class="main-content">
        <div class="container">
            <h1>Modifier Objectif Nutritionnel</h1>
            <p class="subtitle">Ajuste ton objectif calorique quotidien</p>

            <div class="card">
                <form method="POST" action="index.php?controller=objectif&action=update" novalidate>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($objectif['id']) ?>">

                    <div class="field">
                        <label>Calories cible</label>
                        <input
                            type="text"
                            name="calories_cible"
                            value="<?= htmlspecialchars($objectif['calories_cible']) ?>">
                    </div>

                    <div class="field">
                        <label>Type d'objectif</label>
                        <select name="objectif_type">
                            <option value="maintien" <?= ($objectif['objectif_type'] ?? 'maintien') === 'maintien' ? 'selected' : '' ?>>Maintien</option>
                            <option value="prise_muscle" <?= ($objectif['objectif_type'] ?? 'maintien') === 'prise_muscle' ? 'selected' : '' ?>>Prise de muscle</option>
                        </select>
                    </div>

                    <button type="submit">Enregistrer</button>

                    <a class="btn" href="index.php?controller=objectif&action=index">
                        Annuler
                    </a>
                </form>
            </div>
        </div>
    </div>

    <script src="/projet-web-25-26/public/assets/template_only_template/assets/js/script.js"></script>
</body>
</html>
