<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Objectif Nutritionnel</title>
    <link rel="stylesheet" href="views/front/assets/css/style.css">
    <style>
        .objectif-page .container {
            width: min(1360px, calc(100vw - 48px));
            max-width: 1360px;
        }

        .objectif-shell {
            padding: 28px 0 56px;
        }

        .objectif-page-header {
            margin-bottom: 28px;
        }

        .objectif-page-header h1 {
            margin-bottom: 10px;
        }

        .objectif-section {
            margin-top: 28px;
        }

        .objectif-section-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 18px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .objectif-section-kicker {
            margin: 0 0 8px;
            color: #2ecc71;
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .objectif-section-head h2 {
            margin: 0;
        }

        .objectif-section-head .muted {
            margin: 8px 0 0;
            max-width: 760px;
        }

        .progress-bar {
            width: 100%;
            height: 10px;
            background: #2c3e50;
            border-radius: 5px;
            margin-top: 10px;
            overflow: hidden;
        }

        .progress {
            height: 10px;
            background: #2ecc71;
            border-radius: 5px;
            transition: width 0.25s ease;
        }

        .success-message {
            background: rgba(46, 204, 113, 0.1);
            border: 1px solid rgba(46, 204, 113, 0.3);
            color: #27ae60;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .error-message {
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.3);
            color: #e74c3c;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .warning-message {
            background: rgba(241, 196, 15, 0.12);
            border: 1px solid rgba(241, 196, 15, 0.3);
            color: #f4d35e;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        body.theme-light .warning-message {
            color: #92400e;
        }

        .debug-message {
            background: rgba(52, 152, 219, 0.1);
            border: 1px solid rgba(52, 152, 219, 0.3);
            color: #8ed0ff;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        body.theme-light .debug-message {
            color: #1d4ed8;
        }

        .objectif-form-grid,
        .objectif-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .objectif-stats-grid {
            margin-top: 18px;
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

        .objectif-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(46, 204, 113, 0.12);
            color: #2ecc71;
            font-weight: 700;
            font-size: 0.88rem;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 18px;
        }

        .summary-item {
            padding: 14px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .summary-item small {
            display: block;
            color: rgba(236, 240, 241, 0.65);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        body.theme-light .summary-item small {
            color: #64748b;
        }

        .summary-item strong {
            font-size: 1.2rem;
        }

        button[disabled] {
            opacity: 0.7;
            cursor: not-allowed;
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

        .stats-link-wrap {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
            grid-column: 1 / -1;
        }

        .stats-link-wrap .btn,
        .objectif-actions .btn {
            width: auto;
            margin-top: 0;
            padding: 10px 16px;
        }

        .objectif-actions {
            margin-top: 18px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .plan-card {
            margin-top: 0;
        }

        .plan-card h2 {
            margin-bottom: 8px;
        }

        .plan-card .muted {
            margin-top: 0;
        }

        .plan-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-top: 18px;
        }

        .plan-day {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 14px 16px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            text-decoration: none;
            color: inherit;
            cursor: pointer;
            transition: border-color 0.2s ease, background 0.2s ease, opacity 0.2s ease, transform 0.18s ease, box-shadow 0.18s ease;
        }

        .plan-day:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.18);
            border-color: rgba(52, 152, 219, 0.36);
        }

        .plan-day:focus-visible {
            outline: 2px solid rgba(46, 204, 113, 0.7);
            outline-offset: 2px;
        }

        .plan-day__meta {
            display: grid;
            gap: 4px;
        }

        .plan-day__label {
            font-weight: 700;
        }

        .plan-day__date {
            font-size: 0.92rem;
            color: rgba(236, 240, 241, 0.72);
        }

        .plan-day__hint {
            font-size: 0.8rem;
            color: rgba(236, 240, 241, 0.62);
        }

        .plan-day__value {
            font-weight: 700;
            font-size: 1rem;
            white-space: nowrap;
        }

        .plan-day.is-past {
            opacity: 0.58;
            background: rgba(148, 163, 184, 0.08);
            border-color: rgba(148, 163, 184, 0.18);
        }

        .plan-day.is-today {
            background: rgba(46, 204, 113, 0.12);
            border-color: rgba(46, 204, 113, 0.45);
            box-shadow: 0 0 0 1px rgba(46, 204, 113, 0.12), 0 18px 30px rgba(46, 204, 113, 0.12);
        }

        .plan-day.is-future {
            opacity: 1;
        }

        .plan-day__badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .plan-day__badge.is-past {
            background: rgba(148, 163, 184, 0.14);
            color: #cbd5e1;
        }

        .plan-day__badge.is-today {
            background: rgba(46, 204, 113, 0.2);
            color: #9ff0bd;
        }

        .plan-day__badge.is-future {
            background: rgba(52, 152, 219, 0.14);
            color: #8ed0ff;
        }

        body.theme-light .plan-day__date {
            color: #64748b;
        }

        body.theme-light .plan-day.is-past {
            background: rgba(148, 163, 184, 0.12);
            border-color: rgba(148, 163, 184, 0.22);
        }

        body.theme-light .plan-day.is-today {
            background: rgba(46, 204, 113, 0.12);
        }

        body.theme-light .plan-day__badge.is-past {
            color: #64748b;
        }

        body.theme-light .plan-day__badge.is-today {
            color: #166534;
        }

        body.theme-light .plan-day__badge.is-future {
            color: #1d4ed8;
        }

        .objectif-chrono-entry {
            margin-top: 18px;
            padding: 18px;
            border-radius: 16px;
            background: rgba(52, 152, 219, 0.08);
            border: 1px solid rgba(52, 152, 219, 0.18);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            flex-wrap: wrap;
        }

        .objectif-chrono-entry__content {
            max-width: 720px;
        }

        .objectif-chrono-entry__title {
            margin: 0 0 8px;
            font-size: 1.05rem;
        }

        .objectif-chrono-entry__text {
            margin: 0;
            color: rgba(236, 240, 241, 0.78);
            line-height: 1.55;
        }

        .objectif-chrono-entry__action {
            width: auto;
            margin-top: 0;
            padding: 10px 16px;
            white-space: nowrap;
        }

        body.theme-light .objectif-chrono-entry__text {
            color: #475569;
        }

        .macro-list {
            display: grid;
            gap: 14px;
            margin-top: 16px;
        }

        .macro-row {
            padding: 14px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .macro-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 8px;
        }

        .macro-name {
            font-weight: 700;
        }

        .macro-values {
            color: rgba(236, 240, 241, 0.82);
        }

        body.theme-light .macro-values {
            color: #4b5563;
        }

        body.theme-light .macro-percent {
            color: #374151;
        }

        .macro-bar {
            width: 100%;
            height: 8px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 6px;
            overflow: hidden;
        }

        .macro-fill {
            height: 8px;
            border-radius: 6px;
            transition: 0.3s ease;
        }

        .progress-ok {
            background: linear-gradient(90deg, #2ecc71, #27ae60);
        }

        .progress-warning {
            background: linear-gradient(90deg, #f1c40f, #f39c12);
        }

        .progress-over {
            background: linear-gradient(90deg, #e74c3c, #c0392b);
            box-shadow: 0 0 12px rgba(231, 76, 60, 0.35);
        }

        .macro-status {
            margin-top: 8px;
            font-size: 0.92rem;
            font-weight: 600;
        }

        .macro-percent {
            font-weight: 700;
        }

        .objectif-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            align-items: start;
        }

        .objectif-detail-card {
            margin-top: 0 !important;
        }

        .objectif-detail-card--wide {
            grid-column: 1 / -1;
        }

        .objectif-day-anchor {
            scroll-margin-top: 92px;
        }

        .objectif-focus-flash {
            animation: objectifFlash 1.1s ease;
        }

        @keyframes objectifFlash {
            0% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0); }
            30% { box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.24); }
            100% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0); }
        }

        body.theme-light .plan-day__hint {
            color: #64748b;
        }

        @media (max-width: 768px) {
            .objectif-form-grid,
            .objectif-stats-grid,
            .summary-grid,
            .plan-list,
            .objectif-detail-grid {
                grid-template-columns: 1fr;
            }

            .objectif-page .container {
                width: min(100%, calc(100vw - 28px));
            }

            .objectif-section-head {
                align-items: flex-start;
            }

            .objectif-chrono-entry {
                align-items: flex-start;
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
    $objectifForm = $_SESSION['objectif_form'] ?? [];

    if (!is_array($objectifErrors) && !empty($objectifErrors)) {
        $objectifErrors = [$objectifErrors];
    }

    unset($_SESSION['objectif_error'], $_SESSION['objectif_form']);

    $formSource = !empty($objectifForm)
        ? $objectifForm
        : (!empty($todayObjectif) ? $todayObjectif : (!empty($objectif) ? $objectif : []));

    $selectedPoids = $formSource['poids'] ?? '';
    $selectedPoidsCible = $formSource['poids_cible'] ?? '';
    $selectedSucreMax = $formSource['sucre_max_g'] ?? '';
    $selectedTaille = $formSource['taille'] ?? '';
    $selectedAge = $formSource['age'] ?? '';
    $selectedSexe = $formSource['sexe'] ?? 'homme';
    $selectedActivite = $formSource['activite_input'] ?? ($formSource['activite'] ?? 'moderate');
    $selectedObjectifType = $formSource['objectif_type'] ?? 'maintien';
    $planRangeStart = !empty($planRows[0]['date_creation'])
        ? (string) $planRows[0]['date_creation']
        : null;
    $lastPlanRow = !empty($planRows)
        ? $planRows[count($planRows) - 1]
        : null;
    $planRangeEnd = !empty($lastPlanRow['date_creation'])
        ? (string) $lastPlanRow['date_creation']
        : null;

    ?>

    <div class="main-content objectif-page">
        <div class="container">
            <div class="objectif-shell">
            <div class="objectif-page-header">
                <h1>Objectif Nutritionnel</h1>
                <p class="subtitle">Definis ton profil physique, visualise ton plan calorique sur 7 jours et accede rapidement au detail du jour.</p>
            </div>

            <?php if (!empty($_SESSION['objectif_success'])): ?>
                <div class="success-message">
                    OK <?= htmlspecialchars((string) $_SESSION['objectif_success']) ?>
                </div>
                <?php unset($_SESSION['objectif_success']); ?>
            <?php endif; ?>

            <?php if (!empty($objectifDebug)): ?>
                <div class="debug-message">
                    <strong>Debug calcul :</strong>
                    BMR <?= htmlspecialchars((string) ($objectifDebug['bmr'] ?? '-')) ?> kcal |
                    facteur <?= htmlspecialchars((string) ($objectifDebug['activity_factor'] ?? '-')) ?> |
                    TDEE <?= htmlspecialchars((string) ($objectifDebug['tdee'] ?? '-')) ?> kcal |
                    calories finales <?= htmlspecialchars((string) ($objectifDebug['calories_cible'] ?? '-')) ?> kcal
                </div>
            <?php endif; ?>

            <?php if (!empty($objectifErrors)): ?>
                <div class="error-message">
                    <strong>Erreur :</strong>
                    <ul style="margin: 8px 0 0; padding-left: 18px;">
                        <?php foreach ($objectifErrors as $objectifError): ?>
                            <li><?= htmlspecialchars((string) $objectifError) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($objectifWarning)): ?>
                <div class="warning-message">
                    <?= htmlspecialchars((string) $objectifWarning) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($objectifMessage)): ?>
                <div class="objectif-note" style="margin-bottom: 20px;">
                    <?= htmlspecialchars((string) $objectifMessage) ?>
                </div>
            <?php endif; ?>

            <section class="objectif-section">
                <div class="objectif-section-head">
                    <div>
                        <h2>Objectif nutritionnel et profil</h2>
                        <p class="muted">Renseigne tes parametres et genere ton plan calorique hebdomadaire.</p>
                    </div>
                </div>

            <div class="card">
                <form method="POST" action="index.php?controller=objectif&action=store" novalidate>
                    <div class="objectif-form-grid">
                        <div class="field">
                            <label>Poids (kg)</label>
                            <input type="text" name="poids" placeholder="Ex: 70" value="<?= htmlspecialchars((string) $selectedPoids) ?>">
                        </div>

                        <div class="field">
                            <label for="poids_cible">Poids objectif (kg)</label>
                            <input type="number" step="0.1" min="20" max="300" name="poids_cible" id="poids_cible" placeholder="Ex: 75" value="<?= htmlspecialchars((string) $selectedPoidsCible) ?>">
                            <small>Utilise pour estimer la date d'atteinte dans la projection nutritionnelle.</small>
                        </div>

                        <div class="field">
                            <label for="sucre_max_g">Sucre maximum par jour (g)</label>
                            <input type="number" step="1" min="0" max="300" name="sucre_max_g" id="sucre_max_g" placeholder="Ex: 50" value="<?= htmlspecialchars((string) $selectedSucreMax) ?>">
                            <small>Utilise pour suivre la consommation de sucre quotidienne.</small>
                        </div>

                        <div class="field">
                            <label>Taille (cm)</label>
                            <input type="text" name="taille" placeholder="Ex: 175" value="<?= htmlspecialchars((string) $selectedTaille) ?>">
                        </div>

                        <div class="field">
                            <label>Age</label>
                            <input type="text" name="age" placeholder="Ex: 30" value="<?= htmlspecialchars((string) $selectedAge) ?>">
                        </div>

                        <div class="field">
                            <label>Sexe</label>
                            <select name="sexe">
                                <?php foreach ($sexeOptions as $value => $label): ?>
                                    <option value="<?= htmlspecialchars((string) $value) ?>" <?= $selectedSexe === $value ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label>Niveau d'activite</label>
                            <select name="activite">
                                <?php foreach ($activiteInputOptions as $value => $label): ?>
                                    <option value="<?= htmlspecialchars((string) $value) ?>" <?= $selectedActivite === $value ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label>Type d'objectif</label>
                            <select name="objectif_type">
                                <?php foreach ($objectifTypeOptions as $value => $label): ?>
                                    <option value="<?= htmlspecialchars((string) $value) ?>" <?= $selectedObjectifType === $value ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="objectif-note">
                        Le plan genere 7 objectifs quotidiens d'un coup a partir de la formule de Mifflin-St Jeor, puis applique une variation calorique sur la semaine.
                    </div>

                    <?php if (!empty($activePlan['is_locked'])): ?>
                        <div class="objectif-note" style="margin-top: 16px;">
                            Plan actif du <?= htmlspecialchars((string) ($activePlan['start_date'] ?? '-')) ?> au <?= htmlspecialchars((string) ($activePlan['end_date'] ?? '-')) ?>.
                            <?php if (!empty($canModifyPlanToday)): ?>
                                Vous pouvez encore le modifier aujourd'hui depuis le bouton Modifier le plan.
                            <?php else: ?>
                                Vous pourrez generer un nouveau plan dans <?= htmlspecialchars((string) ($activePlan['remaining_days'] ?? 0)) ?> <?= ((int) ($activePlan['remaining_days'] ?? 0) > 1) ? 'jours' : 'jour' ?>.
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <button type="submit" <?= !empty($activePlan['is_locked']) ? 'disabled aria-disabled="true"' : '' ?>>
                        <?= !empty($activePlan['is_locked']) ? "Plan actif en cours" : "Generer le plan sur 7 jours" ?>
                    </button>
                </form>
            </div>
            </section>

            <?php if (!empty($planRows)): ?>
                <section class="objectif-section">
                    <div class="objectif-section-head">
                        <div>
                            <h2>Plan calorique sur 7 jours</h2>
                            <p class="muted">Le jour en cours est mis en avant. Clique sur une carte pour rejoindre le detail du jour.</p>
                        </div>
                    </div>

                <div class="card plan-card">
                    <p class="muted">
                        Vue complete du dernier plan enregistre, du <?= htmlspecialchars((string) date('d/m/Y', strtotime((string) ($activePlan['start_date'] ?? $planRangeStart ?? 'now')))) ?>
                        au <?= htmlspecialchars((string) date('d/m/Y', strtotime((string) ($activePlan['end_date'] ?? $planRangeEnd ?? 'now')))) ?>.
                    </p>

                    <div class="plan-list">
                        <?php foreach ($planRows as $planIndex => $planRow): ?>
                            <?php
                            $planDate = date('Y-m-d', strtotime((string) ($planRow['date_creation'] ?? '')));
                            $todayDate = date('Y-m-d');

                            if ($planDate < $todayDate) {
                                $planState = 'past';
                                $planStateLabel = 'Passe';
                            } elseif ($planDate === $todayDate) {
                                $planState = 'today';
                                $planStateLabel = "Aujourd'hui";
                            } else {
                                $planState = 'future';
                                $planStateLabel = 'A venir';
                            }
                            ?>
                            <a href="index.php?controller=objectif&action=jour&day=<?= urlencode((string) ($planIndex + 1)) ?>" class="plan-day is-<?= htmlspecialchars((string) $planState) ?>">
                                <div class="plan-day__meta">
                                    <span class="plan-day__label">Jour <?= htmlspecialchars((string) ($planIndex + 1)) ?></span>
                                    <span class="plan-day__date"><?= htmlspecialchars((string) date('d/m/Y', strtotime($planDate))) ?></span>
                                    <span class="plan-day__hint">Voir le detail du jour</span>
                                </div>

                                <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; justify-content:flex-end;">
                                    <span class="plan-day__badge is-<?= htmlspecialchars((string) $planState) ?>">
                                        <?= htmlspecialchars((string) $planStateLabel) ?>
                                    </span>
                                    <span class="plan-day__value"><?= htmlspecialchars((string) round((float) ($planRow['calories_cible'] ?? 0))) ?> kcal</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <div class="objectif-chrono-entry">
                        <div class="objectif-chrono-entry__content">
                            <h3 class="objectif-chrono-entry__title">Chrono-Nutrition</h3>
                            <p class="objectif-chrono-entry__text">
                                Ajuste tes horaires de repas selon ton rythme de sommeil, ton energie et ton activite.
                            </p>
                        </div>

                        <a href="index.php?action=chrono_nutrition" class="btn btn-secondary objectif-chrono-entry__action">
                            Optimiser mes horaires
                        </a>
                    </div>

                    <div class="objectif-chrono-entry">
                        <div class="objectif-chrono-entry__content">
                            <h3 class="objectif-chrono-entry__title">Projection nutritionnelle</h3>
                            <p class="objectif-chrono-entry__text">
                                Estime ton evolution selon tes habitudes reelles et teste differents scenarios.
                            </p>
                        </div>

                        <a href="index.php?action=prediction_dashboard" class="btn btn-secondary objectif-chrono-entry__action">
                            Voir la projection
                        </a>
                    </div>
                </div>
                </section>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <?php require __DIR__ . '/../partials/chatbot_widget.php'; ?>
    <script src="/projet-web-25-26/views/front/assets/js/theme.js"></script>
</body>

</html>
