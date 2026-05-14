<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Objectif du Jour</title>
    <link rel="stylesheet" href="/projet-web-25-26/view/front/assets/css/style.css">
    <style>
        .objectif-page .container {
            width: min(1360px, calc(100vw - 48px));
            max-width: 1360px;
        }

        .objectif-shell {
            padding: 28px 0 56px;
        }

        .objectif-page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            flex-wrap: wrap;
            margin-bottom: 28px;
        }

        .objectif-page-header h1 {
            margin: 14px 0 10px;
        }

        .objectif-back-link {
            width: auto;
            margin-top: 0;
            padding: 10px 16px;
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

        .summary-grid,
        .objectif-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 18px;
        }

        .objectif-stats-grid {
            margin-top: 16px;
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

        .objectif-actions,
        .stats-link-wrap {
            margin-top: 20px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .stats-link-wrap {
            justify-content: center;
        }

        .stats-link-wrap .btn,
        .objectif-actions .btn {
            width: auto;
            margin-top: 0;
            padding: 10px 16px;
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
            .objectif-page .container {
                width: min(100%, calc(100vw - 28px));
            }

            .summary-grid,
            .objectif-stats-grid,
            .objectif-detail-grid {
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
    $objectifDate = (string) ($objectif['date_creation'] ?? date('Y-m-d'));
    $objectifDateLabel = date('d/m/Y', strtotime($objectifDate));
    $dayChipLabel = $isSelectedDayToday ? "Aujourd'hui" : "Jour " . $selectedDay;
    $caloriesCible = (float) ($objectif['calories_cible'] ?? 0);
    $proteinesObjectif = (float) ($objectif['proteines'] ?? 0);
    $glucidesObjectif = (float) ($objectif['glucides'] ?? 0);
    $lipidesObjectif = (float) ($objectif['lipides'] ?? 0);

    $proteinesConsommees = (float) ($dayMacros['proteines'] ?? 0);
    $glucidesConsommes = (float) ($dayMacros['glucides'] ?? 0);
    $lipidesConsommes = (float) ($dayMacros['lipides'] ?? 0);

    $remaining = round($caloriesCible - $totalForDay);
    $ratio = $caloriesCible > 0 ? $totalForDay / $caloriesCible : 0;
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

    <div class="main-content objectif-page">
        <div class="container">
            <div class="objectif-shell">
                <div class="objectif-page-header">
                    <div>
                        <a href="index.php?controller=objectif&action=index" class="btn btn-secondary objectif-back-link">
                            Retour au plan nutritionnel
                        </a>
                        <h1>Detail objectif du jour</h1>
                        <p class="subtitle">
                            Jour <?= htmlspecialchars((string) $selectedDay) ?> du plan calorique - <?= htmlspecialchars((string) $objectifDateLabel) ?>
                        </p>
                    </div>

                    <span class="objectif-chip"><?= htmlspecialchars((string) $dayChipLabel) ?></span>
                </div>

                <div class="objectif-detail-grid">
                    <div class="card objectif-detail-card">
                        <div style="display:flex; justify-content:space-between; gap:12px; align-items:center; flex-wrap:wrap;">
                            <div>
                                <h2 style="margin:0 0 8px;">Objectif du jour</h2>
                                <p class="muted" style="margin:0;">Date de l'objectif : <?= htmlspecialchars((string) $objectifDateLabel) ?></p>
                            </div>

                            <?php if ($isSelectedDayToday): ?>
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
                            <div class="objectif-note">
                                Cet objectif ne contient pas encore toutes les donnees physiques necessaires au calcul automatique. Mets-le a jour pour profiter du nouveau systeme complet.
                            </div>
                        <?php endif; ?>

                        <div class="summary-grid">
                            <div class="summary-item">
                                <small>Proteines cible</small>
                                <strong><?= round($proteinesObjectif) ?> g</strong>
                            </div>
                            <div class="summary-item">
                                <small>Glucides cible</small>
                                <strong><?= round($glucidesObjectif) ?> g</strong>
                            </div>
                            <div class="summary-item">
                                <small>Lipides cible</small>
                                <strong><?= round($lipidesObjectif) ?> g</strong>
                            </div>
                        </div>

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

                    <div class="card objectif-detail-card">
                        <h2>Comparaison quotidienne</h2>
                        <p><strong>Consomme sur cette journee :</strong> <?= round($totalForDay) ?> kcal</p>
                        <p><strong>Restant :</strong> <?= $remaining ?> kcal</p>
                        <div class="progress-bar">
                            <div class="progress" style="width: <?= round($progress, 2) ?>%; background: <?= htmlspecialchars((string) $color) ?>;"></div>
                        </div>
                        <p style="color: <?= htmlspecialchars((string) $color) ?>; margin-top: 12px;">
                            <?= htmlspecialchars((string) $status) ?>
                        </p>
                    </div>

                    <div class="card objectif-detail-card objectif-detail-card--wide">
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
                </div>
            </div>
        </div>
    </div>

    <?php require __DIR__ . '/../partials/chatbot_widget.php'; ?>
<script src="/projet-web-25-26/view/front/assets/js/theme.js"></script>
</body>

</html>
