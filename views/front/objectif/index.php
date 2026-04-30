<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Objectif Nutritionnel</title>
    <link rel="stylesheet" href="views/front/assets/css/style.css">
    <style>
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
            grid-template-columns: repeat(2, minmax(0, 1fr));
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
            margin-top: 24px;
        }

        .plan-card h2 {
            margin-bottom: 8px;
        }

        .plan-card .muted {
            margin-top: 0;
        }

        .plan-list {
            display: grid;
            gap: 12px;
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
            transition: border-color 0.2s ease, background 0.2s ease, opacity 0.2s ease;
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
            box-shadow: 0 0 0 1px rgba(46, 204, 113, 0.12);
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

        @media (max-width: 768px) {
            .objectif-form-grid,
            .objectif-stats-grid,
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
    $objectifForm = $_SESSION['objectif_form'] ?? [];

    if (!is_array($objectifErrors) && !empty($objectifErrors)) {
        $objectifErrors = [$objectifErrors];
    }

    unset($_SESSION['objectif_error'], $_SESSION['objectif_form']);

    $formSource = !empty($objectifForm)
        ? $objectifForm
        : (!empty($todayObjectif) ? $todayObjectif : (!empty($objectif) ? $objectif : []));

    $selectedPoids = $formSource['poids'] ?? '';
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

    if (!function_exists('getProgressClass')) {
        function getProgressClass($current, $target)
        {
            if ((float) $target === 0.0) {
                return '';
            }

            $ratio = $current / $target;

            if ($ratio <= 0.8) {
                return 'progress-ok';
            }

            if ($ratio <= 1) {
                return 'progress-warning';
            }

            return 'progress-over';
        }
    }
    ?>

    <div class="main-content">
        <div class="container">
            <h1>Objectif Nutritionnel</h1>
            <p class="subtitle">Definis ton profil physique et laisse l'application calculer automatiquement tes calories.</p>

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

            <?php if (!empty($objectifMessage)): ?>
                <div class="objectif-note" style="margin-bottom: 20px;">
                    <?= htmlspecialchars((string) $objectifMessage) ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <form method="POST" action="index.php?controller=objectif&action=store" novalidate>
                    <div class="objectif-form-grid">
                        <div class="field">
                            <label>Poids (kg)</label>
                            <input type="text" name="poids" placeholder="Ex: 70" value="<?= htmlspecialchars((string) $selectedPoids) ?>">
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

            <?php if (!empty($planRows)): ?>
                <div class="card plan-card">
                    <h2>Plan calorique sur 7 jours</h2>
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
                            <div class="plan-day is-<?= htmlspecialchars((string) $planState) ?>">
                                <div class="plan-day__meta">
                                    <span class="plan-day__label">Jour <?= htmlspecialchars((string) ($planIndex + 1)) ?></span>
                                    <span class="plan-day__date"><?= htmlspecialchars((string) date('d/m/Y', strtotime($planDate))) ?></span>
                                </div>

                                <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; justify-content:flex-end;">
                                    <span class="plan-day__badge is-<?= htmlspecialchars((string) $planState) ?>">
                                        <?= htmlspecialchars((string) $planStateLabel) ?>
                                    </span>
                                    <span class="plan-day__value"><?= htmlspecialchars((string) round((float) ($planRow['calories_cible'] ?? 0))) ?> kcal</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($objectif)): ?>
                <?php
                $caloriesCible = (float) ($objectif['calories_cible'] ?? 0);
                $proteinesObjectif = (float) ($objectif['proteines'] ?? 0);
                $glucidesObjectif = (float) ($objectif['glucides'] ?? 0);
                $lipidesObjectif = (float) ($objectif['lipides'] ?? 0);

                $proteinesConsommees = (float) ($todayMacros['proteines'] ?? 0);
                $glucidesConsommes = (float) ($todayMacros['glucides'] ?? 0);
                $lipidesConsommes = (float) ($todayMacros['lipides'] ?? 0);

                $remaining = round($caloriesCible - $total_today);
                $ratio = $caloriesCible > 0 ? $total_today / $caloriesCible : 0;
                $progress = min($ratio * 100, 100);

                if ($ratio < 0.8) {
                    $status = "En dessous";
                    $color = "#3498db";
                } elseif ($ratio <= 1) {
                    $status = "Parfait";
                    $color = "#2ecc71";
                } elseif ($ratio <= 1.2) {
                    $status = "Attention";
                    $color = "#f39c12";
                } else {
                    $status = "Depasse";
                    $color = "#e74c3c";
                }

                $macroRows = [
                    [
                        'label' => 'Proteines',
                        'consumed' => $proteinesConsommees,
                        'target' => $proteinesObjectif,
                    ],
                    [
                        'label' => 'Glucides',
                        'consumed' => $glucidesConsommes,
                        'target' => $glucidesObjectif,
                    ],
                    [
                        'label' => 'Lipides',
                        'consumed' => $lipidesConsommes,
                        'target' => $lipidesObjectif,
                    ],
                ];
                ?>

                <div class="card" style="margin-top: 24px;">
                    <div style="display:flex; justify-content:space-between; gap:12px; align-items:center; flex-wrap:wrap;">
                        <div>
                            <h2 style="margin:0 0 8px;">Objectif du jour</h2>
                            <?php if (!empty($objectif['date_creation'])): ?>
                                <p class="muted" style="margin:0;">Date de l'objectif : <?= htmlspecialchars((string) $objectif['date_creation']) ?></p>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($todayObjectif) && (int) ($todayObjectif['id'] ?? 0) === (int) ($objectif['id'] ?? 0)): ?>
                            <span class="objectif-chip">Objectif du jour</span>
                        <?php endif; ?>
                    </div>

                    <div class="summary-grid">
                        <div class="summary-item">
                            <small>Calories cible</small>
                            <strong><?= round($caloriesCible) ?> kcal</strong>
                        </div>
                        <div class="summary-item">
                            <small>Type d'objectif</small>
                            <strong><?= htmlspecialchars((string) ($objectifTypeOptions[$objectif['objectif_type'] ?? 'maintien'] ?? 'Maintien')) ?></strong>
                        </div>
                        <div class="summary-item">
                            <small>Activite</small>
                            <strong><?= htmlspecialchars((string) ($activiteDisplayLabel ?? '-')) ?></strong>
                        </div>
                    </div>

                    <?php if (!empty($objectifSummary)): ?>
                        <div class="objectif-stats-grid">
                            <div class="summary-item">
                                <small>Profil</small>
                                <strong>
                                    <?= htmlspecialchars(number_format((float) ($objectif['poids'] ?? 0), 1, '.', ' ')) ?> kg
                                    /
                                    <?= htmlspecialchars(number_format((float) ($objectif['taille'] ?? 0), 0, '.', ' ')) ?> cm
                                    /
                                    <?= htmlspecialchars((string) ($objectif['age'] ?? '-')) ?> ans
                                </strong>
                                <div class="muted" style="margin-top:8px;">
                                    <?= htmlspecialchars((string) ($sexeOptions[$objectif['sexe'] ?? ''] ?? '-')) ?>
                                </div>
                            </div>

                            <div class="summary-item">
                                <small>
                                    <span class="metric-tooltip">
                                        BMR
                                        <span class="metric-tooltip__icon" aria-hidden="true">?</span>
                                        <span class="metric-tooltip__bubble">Calories br&ucirc;l&eacute;es au repos (m&eacute;tabolisme de base)</span>
                                    </span>
                                </small>
                                <strong><?= htmlspecialchars((string) ($objectifSummary['bmr'] ?? round($caloriesCible))) ?> kcal</strong>
                            </div>

                            <div class="summary-item">
                                <small>
                                    <span class="metric-tooltip">
                                        TDEE
                                        <span class="metric-tooltip__icon" aria-hidden="true">?</span>
                                        <span class="metric-tooltip__bubble">Calories totales d&eacute;pens&eacute;es par jour (incluant l&apos;activit&eacute; physique)</span>
                                    </span>
                                </small>
                                <strong><?= htmlspecialchars((string) ($objectifSummary['tdee'] ?? round($caloriesCible))) ?> kcal</strong>
                                <?php if (isset($objectifSummary['activity_factor'])): ?>
                                    <div class="muted" style="margin-top:8px;">Facteur activite x<?= htmlspecialchars((string) $objectifSummary['activity_factor']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="objectif-note" style="margin-top: 18px;">
                            Cet objectif ne contient pas encore toutes les donnees physiques necessaires au calcul automatique. Mets-le a jour pour profiter du nouveau systeme complet.
                        </div>
                    <?php endif; ?>

                    <p><strong>Proteines :</strong> <?= round($proteinesObjectif) ?> g</p>
                    <p><strong>Glucides :</strong> <?= round($glucidesObjectif) ?> g</p>
                    <p><strong>Lipides :</strong> <?= round($lipidesObjectif) ?> g</p>

                    <div class="objectif-actions">
                        <?php if (!empty($canModifyPlanToday) && !empty($planStartObjectif['id'])): ?>
                            <a href="index.php?controller=objectif&action=edit&id=<?= urlencode((string) $planStartObjectif['id']) ?>" class="btn btn-secondary">
                                Modifier le plan
                            </a>
                        <?php endif; ?>
                        <?php if (empty($activePlan['is_locked'])): ?>
                            <a href="index.php?controller=objectif&action=delete&id=<?= urlencode((string) $objectif['id']) ?>" class="btn btn-secondary" onclick="return confirm('Supprimer cet objectif ?');">
                                Supprimer
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card" style="margin-top: 20px;">
                    <h2>Comparaison quotidienne</h2>
                    <p><strong>Consomme aujourd'hui :</strong> <?= round($total_today) ?> kcal</p>
                    <p><strong>Restant :</strong> <?= $remaining ?> kcal</p>
                    <div class="progress-bar">
                        <div class="progress" style="width: <?= round($progress, 2) ?>%; background: <?= htmlspecialchars((string) $color) ?>;"></div>
                    </div>
                    <p style="color: <?= htmlspecialchars((string) $color) ?>; margin-top: 12px;">
                        <?= htmlspecialchars((string) $status) ?>
                    </p>
                </div>

                <div class="card" style="margin-top: 20px;">
                    <h2>Comparaison des macronutriments</h2>
                    <div class="macro-list">
                        <?php foreach ($macroRows as $macroRow): ?>
                            <?php
                            $macroTarget = (float) $macroRow['target'];
                            $macroConsumed = (float) $macroRow['consumed'];
                            $macroRatio = $macroTarget > 0 ? $macroConsumed / $macroTarget : 0;
                            $macroProgress = min($macroRatio * 100, 100);
                            $macroPercent = $macroTarget > 0 ? round($macroRatio * 100) : 0;
                            $macroClass = getProgressClass($macroConsumed, $macroTarget);

                            if ($macroRatio <= 0.8) {
                                $macroColor = '#2ecc71';
                                $macroStatus = 'Bonne marge';
                            } elseif ($macroRatio <= 1) {
                                $macroColor = '#f1c40f';
                                $macroStatus = 'Proche de la cible';
                            } else {
                                $macroColor = '#e74c3c';
                                $macroStatus = 'Objectif depasse';
                            }
                            ?>
                            <div class="macro-row">
                                <div class="macro-head">
                                    <span class="macro-name"><?= htmlspecialchars((string) $macroRow['label']) ?></span>
                                    <span class="macro-values">
                                        <?= round($macroConsumed, 1) ?> g / <?= round($macroTarget, 1) ?> g
                                        <span class="macro-percent">- <?= $macroPercent ?>%</span>
                                    </span>
                                </div>
                                <div class="macro-bar">
                                    <div class="macro-fill <?= htmlspecialchars((string) $macroClass) ?>" style="width: <?= round($macroProgress, 2) ?>%;"></div>
                                </div>
                                <div class="macro-status" style="color: <?= htmlspecialchars((string) $macroColor) ?>;">
                                    <?= htmlspecialchars((string) $macroStatus) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="stats-link-wrap">
                    <a href="index.php?controller=stats&action=index" class="btn btn-primary">
                        Voir statistiques
                    </a>

                    <a href="index.php?controller=suivi&action=sendReport" class="btn btn-secondary">
                        Envoyer rapport hebdomadaire
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="/projet-web-25-26/public/assets/template_only_template/assets/js/script.js"></script>
</body>

</html>
