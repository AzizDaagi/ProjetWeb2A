<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un aliment personnalise</title>
    <link rel="stylesheet" href="/Web/view/front/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            opacity: 1 !important;
            visibility: visible !important;
        }

        .section-wrapper {
            width: 90%;
            max-width: 620px;
            margin: 40px auto;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-main, #ecf0f1);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-group label {
            display: block;
            color: rgba(236, 240, 241, 0.7);
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .form-group input,
        .form-group select {
            display: block;
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.07);
            color: var(--text-main, #ecf0f1);
            font-size: 0.95rem;
            box-sizing: border-box;
        }

        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .actions .btn {
            width: auto;
            margin-top: 0;
        }

        .inline-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .lookup-message {
            margin-top: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            display: none;
            font-size: 0.92rem;
        }

        .lookup-message.is-visible {
            display: block;
        }

        .lookup-message.is-loading {
            background: #eff6ff;
            border: 1px solid #93c5fd;
            color: #1d4ed8;
        }

        .lookup-message.is-success {
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #166534;
        }

        .lookup-message.is-error {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #b91c1c;
        }

        .lookup-message.is-warning {
            background: #fff7ed;
            border: 1px solid #fdba74;
            color: #c2410c;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-main, #ecf0f1);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
    </style>
</head>

<body>
    <?php require __DIR__ . '/../partials/navbar.php'; ?>
    <?php
    $customErrors = $_SESSION['custom_aliment_error'] ?? [];
    $customForm = $_SESSION['custom_aliment_form'] ?? [];

    if (!is_array($customErrors) && !empty($customErrors)) {
        $customErrors = [$customErrors];
    }

    unset($_SESSION['custom_aliment_error'], $_SESSION['custom_aliment_form']);
    ?>

    <div class="section-wrapper">
        <h2 class="section-title">
            <i class="fa-solid fa-pen-to-square"></i> Ajouter un aliment personnalise
        </h2>

        <div class="glass-card">
            <?php if (!empty($customErrors)): ?>
                <div style="background: rgba(231, 76, 60, 0.12); border: 1px solid rgba(231, 76, 60, 0.3); color: #f4b3ab; border-radius: 10px; padding: 12px 14px; margin-bottom: 18px;">
                    <strong>Erreur :</strong>
                    <ul style="margin: 8px 0 0; padding-left: 18px;">
                        <?php foreach ($customErrors as $customError): ?>
                            <li><?= htmlspecialchars($customError) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php?controller=suivi&action=storeCustom" novalidate>
                <div class="form-group">
                    <label>Nom</label>
                    <div class="inline-actions">
                        <input type="text" id="customNomInput" name="nom" placeholder="Nom" value="<?= htmlspecialchars((string) ($customForm['nom'] ?? '')) ?>">
                        <button type="button" class="btn btn-secondary" id="externalLookupButton">Auto-remplir</button>
                    </div>
                    <div id="externalLookupMessage" class="lookup-message" aria-live="polite"></div>
                </div>

                <div class="form-group">
                    <label>Type</label>
                    <select name="type">
                        <option value="proteine" <?= ($customForm['type'] ?? 'proteine') === 'proteine' ? 'selected' : '' ?>>Proteine</option>
                        <option value="glucide" <?= ($customForm['type'] ?? 'proteine') === 'glucide' ? 'selected' : '' ?>>Glucide</option>
                        <option value="lipide" <?= ($customForm['type'] ?? 'proteine') === 'lipide' ? 'selected' : '' ?>>Lipide</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Unite</label>
                    <select name="unite">
                        <option value="g" <?= ($customForm['unite'] ?? 'g') === 'g' ? 'selected' : '' ?>>Grammes</option>
                        <option value="piece" <?= ($customForm['unite'] ?? 'g') === 'piece' ? 'selected' : '' ?>>Piece</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Calories / unite</label>
                    <input type="text" id="customCaloriesInput" name="calories" placeholder="Ex : 120" value="<?= htmlspecialchars((string) ($customForm['calories'] ?? '')) ?>">
                </div>

                <div class="form-group">
                    <label>Proteines / unite</label>
                    <input type="text" id="customProteinesInput" name="proteines" placeholder="Ex : 24" value="<?= htmlspecialchars((string) ($customForm['proteines'] ?? '')) ?>">
                </div>

                <div class="form-group">
                    <label>Glucides / unite</label>
                    <input type="text" id="customGlucidesInput" name="glucides" placeholder="Ex : 12" value="<?= htmlspecialchars((string) ($customForm['glucides'] ?? '')) ?>">
                </div>

                <div class="form-group">
                    <label>Lipides / unite</label>
                    <input type="text" id="customLipidesInput" name="lipides" placeholder="Ex : 8" value="<?= htmlspecialchars((string) ($customForm['lipides'] ?? '')) ?>">
                </div>

                <div class="form-group">
                    <label>Sucre / unite (g)</label>
                    <input type="text" id="customSucreInput" name="sucre_g" placeholder="Ex : 5" value="<?= htmlspecialchars((string) ($customForm['sucre_g'] ?? '')) ?>">
                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <a href="index.php?controller=suivi&action=index" class="btn btn-secondary">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php require __DIR__ . '/../partials/chatbot_widget.php'; ?>
    <script>
        const customNomInput = document.getElementById('customNomInput');
        const customCaloriesInput = document.getElementById('customCaloriesInput');
        const customProteinesInput = document.getElementById('customProteinesInput');
        const customGlucidesInput = document.getElementById('customGlucidesInput');
        const customLipidesInput = document.getElementById('customLipidesInput');
        const customSucreInput = document.getElementById('customSucreInput');
        const externalLookupButton = document.getElementById('externalLookupButton');
        const externalLookupMessage = document.getElementById('externalLookupMessage');

        function setLookupMessage(message, type) {
            if (!externalLookupMessage) {
                return;
            }

            externalLookupMessage.textContent = message;
            externalLookupMessage.className = 'lookup-message is-visible';

            if (type) {
                externalLookupMessage.classList.add(type);
            }
        }

        function clearLookupMessage() {
            if (!externalLookupMessage) {
                return;
            }

            externalLookupMessage.textContent = '';
            externalLookupMessage.className = 'lookup-message';
        }

        function pickNumber(obj, keys) {
            for (const key of keys) {
                if (obj && obj[key] !== undefined && obj[key] !== null && obj[key] !== '') {
                    return obj[key];
                }
            }

            return '';
        }

        function fillNutritionFields(data) {
            const fields = {
                nom: document.querySelector('#customNomInput'),
                calories: document.querySelector('#customCaloriesInput'),
                proteines: document.querySelector('#customProteinesInput'),
                glucides: document.querySelector('#customGlucidesInput'),
                lipides: document.querySelector('#customLipidesInput'),
                sucre: document.querySelector('#customSucreInput')
            };
            const calories = pickNumber(data, ['calories', 'calories_kcal', 'kcal']);
            const proteines = pickNumber(data, ['protein_g', 'proteins_g', 'proteines', 'proteines_g', 'protein']);
            const glucides = pickNumber(data, ['carbohydrates_total_g', 'carbs_g', 'glucides', 'glucides_g', 'carbohydrates']);
            const lipides = pickNumber(data, ['fat_total_g', 'fat_g', 'lipides', 'lipides_g', 'fat']);
            const sucre = pickNumber(data, ['sugar_g', 'sugars_g', 'sucre_g', 'sugars']);
            let affectedFields = 0;

            console.log('[Nutrition Lookup] data:', data);
            console.log('[Nutrition Lookup] champs DOM trouves:', fields);
            console.log('[Nutrition Lookup] mapped:', { calories, proteines, glucides, lipides, sucre });

            if (!fields.nom && !fields.calories && !fields.proteines && !fields.glucides && !fields.lipides && !fields.sucre) {
                setLookupMessage('Aucun champ cible trouve dans le formulaire.', 'is-error');
                return false;
            }

            if (!data) {
                return false;
            }

            if (fields.calories && calories !== '') {
                fields.calories.value = calories;
                affectedFields++;
            }

            if (fields.proteines && proteines !== '') {
                fields.proteines.value = proteines;
                affectedFields++;
            }

            if (fields.glucides && glucides !== '') {
                fields.glucides.value = glucides;
                affectedFields++;
            }

            if (fields.lipides && lipides !== '') {
                fields.lipides.value = lipides;
                affectedFields++;
            }

            if (fields.sucre && sucre !== '') {
                fields.sucre.value = sucre;
                affectedFields++;
            }

            if (fields.nom && data.name && fields.nom.value.trim() === '') {
                fields.nom.value = data.name;
                affectedFields++;
            }

            console.log('[Auto-remplir] valeurs affectees', {
                calories: fields.calories ? fields.calories.value : null,
                proteines: fields.proteines ? fields.proteines.value : null,
                glucides: fields.glucides ? fields.glucides.value : null,
                lipides: fields.lipides ? fields.lipides.value : null,
                sucre: fields.sucre ? fields.sucre.value : null,
                nom: fields.nom ? fields.nom.value : null
            });

            if (affectedFields === 0) {
                setLookupMessage('Aucune valeur nutritionnelle n a pu etre affectee.', 'is-error');
                return false;
            }

            return {
                success: true,
                warning: data.warning || ''
            };
        }

        function lookupExternalNutrition() {
            if (!customNomInput || !externalLookupButton) {
                return;
            }

            const query = customNomInput.value.trim();
            const originalInputValue = customNomInput.value;

            if (query === '') {
                setLookupMessage('Saisis un aliment avant de lancer l auto-remplissage.', 'is-error');
                return;
            }

            externalLookupButton.disabled = true;
            setLookupMessage('Recherche en cours...', 'is-loading');

            fetch('index.php?action=nutrition_usda_lookup', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ query: query })
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (payload) {
                    console.log('[Nutrition Lookup] response:', payload);

                    if (!payload || payload.error) {
                        throw new Error(payload && payload.error ? payload.error : 'Erreur API.');
                    }

                    if (!payload.data) {
                        setLookupMessage('Aucune donnee trouvee.', 'is-warning');
                        return;
                    }

                    const fillResult = fillNutritionFields(payload.data);

                    if (fillResult && fillResult.success) {
                        if (customNomInput) {
                            customNomInput.value = originalInputValue;
                        }

                        const resultName = payload.data.name ? 'Resultat USDA utilise : ' + payload.data.name + '. ' : '';
                        const normalizedQuery = payload.data.normalized_query ? 'Recherche utilisee : ' + payload.data.normalized_query + '. ' : '';
                        const translationSource = payload.data.translation_source ? 'Source traduction : ' + payload.data.translation_source + '. ' : '';
                        const warning = fillResult.warning ? fillResult.warning + ' ' : '';
                        setLookupMessage(
                            resultName + normalizedQuery + translationSource + warning + 'Verifiez les valeurs avant d enregistrer.',
                            fillResult.warning ? 'is-warning' : 'is-success'
                        );
                    }
                })
                .catch(function (error) {
                    setLookupMessage(error.message || 'Erreur API.', 'is-error');
                })
                .finally(function () {
                    externalLookupButton.disabled = false;
                });
        }

        if (externalLookupButton) {
            externalLookupButton.addEventListener('click', lookupExternalNutrition);
        }

        if (customNomInput) {
            customNomInput.addEventListener('input', clearLookupMessage);
        }
    </script>
<script src="/Web/view/front/assets/js/theme.js"></script>
</body>

</html>
