<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Plan Nutritionnel</title>
    <link rel="stylesheet" href="views/front/assets/css/style.css">
    <style>
        .objectif-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .objectif-note {
            margin: 16px 0 0;
            padding: 12px 14px;
            border-radius: 8px;
            background: rgba(52, 152, 219, 0.1);
            border: 1px solid rgba(52, 152, 219, 0.25);
            color: rgba(236, 240, 241, 0.88);
        }

        body.theme-light .objectif-note {
            color: #334155;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 20px;
        }

        .summary-item {
            padding: 14px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .summary-item small {
            display: block;
            margin-bottom: 8px;
            color: rgba(236, 240, 241, 0.7);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        body.theme-light .summary-item small {
            color: #64748b;
        }

        .message-box {
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 18px;
        }

        .message-box.is-error {
            background: rgba(231, 76, 60, 0.12);
            border: 1px solid rgba(231, 76, 60, 0.3);
            color: #e74c3c;
        }

        .metric-tooltip {
            position: relative;
            display: inline-block;
            cursor: help;
            white-space: nowrap;
        }

        .metric-tooltip__bubble {
            display: none;
            visibility: hidden;
            opacity: 0;
            position: absolute;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            background-color: #333;
            color: #fff;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 0.8rem;
            line-height: 1.35;
            white-space: nowrap;
            text-align: center;
            text-transform: none;
            letter-spacing: normal;
            font-weight: 400;
            z-index: 100;
            pointer-events: none;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.22);
        }

        .metric-tooltip__bubble::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border-width: 6px;
            border-style: solid;
            border-color: #333 transparent transparent transparent;
        }

        .metric-tooltip:hover .metric-tooltip__bubble {
            display: block;
            visibility: visible;
            opacity: 1;
        }

        .metric-tooltip__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            margin-left: 5px;
            border-radius: 50%;
            background: #4CAF50;
            color: #fff;
            font-size: 0.7rem;
            font-weight: 700;
            line-height: 1;
            vertical-align: middle;
        }

        @media (max-width: 768px) {
            .objectif-form-grid,
            .summary-grid {
                grid-template-columns: 1fr;
            }

            .metric-tooltip__bubble {
                left: 0;
                transform: none;
                width: min(220px, calc(100vw - 48px));
                white-space: normal;
            }

            .metric-tooltip__bubble::after {
                left: 18px;
                transform: none;
            }
        }
    </style>
</head>

<body>
    <?php require __DIR__ . '/../partials/navbar.php'; ?>
    <?php
    $objectifErrors = $_SESSION['objectif_error'] ?? [];

    if (!is_array($objectifErrors) && !empty($objectifErrors)) {
        $objectifErrors = [$objectifErrors];
    }

    unset($_SESSION['objectif_error']);
    ?>

        <div class="main-content">
        <div class="container">
            <h1>Modifier le Plan Nutritionnel</h1>
            <p class="subtitle">Le nouvel enregistrement remplacera tout le plan de 7 jours. Cette action n'est autorisee que le jour 1.</p>

            <?php if (!empty($objectifErrors)): ?>
                <div class="message-box is-error">
                    <strong>Erreur :</strong>
                    <ul style="margin: 8px 0 0; padding-left: 18px;">
                        <?php foreach ($objectifErrors as $objectifError): ?>
                            <li><?= htmlspecialchars((string) $objectifError) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card">
                <form method="POST" action="index.php?controller=objectif&action=update" novalidate>
                    <input type="hidden" name="id" value="<?= htmlspecialchars((string) $objectif['id']) ?>">

                    <div class="objectif-form-grid">
                        <div class="field">
                            <label>Poids (kg)</label>
                            <input type="text" name="poids" value="<?= htmlspecialchars((string) ($objectif['poids'] ?? '')) ?>">
                        </div>

                        <div class="field">
                            <label>Taille (cm)</label>
                            <input type="text" name="taille" value="<?= htmlspecialchars((string) ($objectif['taille'] ?? '')) ?>">
                        </div>

                        <div class="field">
                            <label>Age</label>
                            <input type="text" name="age" value="<?= htmlspecialchars((string) ($objectif['age'] ?? '')) ?>">
                        </div>

                        <div class="field">
                            <label>Sexe</label>
                            <select name="sexe">
                                <?php foreach ($sexeOptions as $value => $label): ?>
                                    <option value="<?= htmlspecialchars((string) $value) ?>" <?= ($objectif['sexe'] ?? 'homme') === $value ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label>Niveau d'activite</label>
                            <select name="activite">
                                <?php foreach ($activiteInputOptions as $value => $label): ?>
                                    <option value="<?= htmlspecialchars((string) $value) ?>" <?= ($objectif['activite_input'] ?? 'moderate') === $value ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label>Type d'objectif</label>
                            <select name="objectif_type">
                                <?php foreach ($objectifTypeOptions as $value => $label): ?>
                                    <option value="<?= htmlspecialchars((string) $value) ?>" <?= ($objectif['objectif_type'] ?? 'maintien') === $value ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="objectif-note">
                        Les calories seront recalculees automatiquement puis le plan complet sur 7 jours sera regenere dans une transaction unique.
                    </div>

                    <?php if (!empty($objectifSummary)): ?>
                        <div class="summary-grid">
                            <div class="summary-item">
                                <small>Calories cible actuelles</small>
                                <strong><?= htmlspecialchars((string) ($objectifSummary['calories_cible'] ?? $objectif['calories_cible'])) ?> kcal</strong>
                            </div>
                            <div class="summary-item">
                                <small>
                                    <span class="metric-tooltip">
                                        BMR
                                        <span class="metric-tooltip__icon" aria-hidden="true">?</span>
                                        <span class="metric-tooltip__bubble">Calories br&ucirc;l&eacute;es au repos (m&eacute;tabolisme de base)</span>
                                    </span>
                                </small>
                                <strong><?= htmlspecialchars((string) ($objectifSummary['bmr'] ?? '-')) ?> kcal</strong>
                            </div>
                            <div class="summary-item">
                                <small>
                                    <span class="metric-tooltip">
                                        TDEE
                                        <span class="metric-tooltip__icon" aria-hidden="true">?</span>
                                        <span class="metric-tooltip__bubble">Calories totales d&eacute;pens&eacute;es par jour (incluant l&apos;activit&eacute; physique)</span>
                                    </span>
                                </small>
                                <strong><?= htmlspecialchars((string) ($objectifSummary['tdee'] ?? '-')) ?> kcal</strong>
                            </div>
                        </div>
                    <?php endif; ?>

                    <button type="submit">Remplacer le plan sur 7 jours</button>

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
