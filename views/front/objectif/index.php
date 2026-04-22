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

        .stats-link-wrap {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .stats-link-wrap .btn {
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
    $selectedObjectifType = $objectifForm['objectif_type']
        ?? (!empty($objectif['objectif_type']) ? $objectif['objectif_type'] : 'maintien');
    $objectifCaloriesValue = $objectifForm['calories_cible']
        ?? (!empty($objectif) ? $objectif['calories_cible'] : '');
    ?>

    <div class="main-content">
        <div class="container">
            <h1>Objectif Nutritionnel</h1>
            <p class="subtitle">Definis ton objectif calorique et tes macros pour la journee</p>

            <?php if (!empty($_SESSION['objectif_success'])): ?>
                <div class="success-message">
                    OK <?= htmlspecialchars($_SESSION['objectif_success']) ?>
                </div>
                <?php unset($_SESSION['objectif_success']); ?>
            <?php endif; ?>

            <?php if (!empty($objectifErrors)): ?>
                <div class="error-message">
                    <strong>Erreur :</strong>
                    <ul style="margin: 8px 0 0; padding-left: 18px;">
                        <?php foreach ($objectifErrors as $objectifError): ?>
                            <li><?= htmlspecialchars($objectifError) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card">
                <form method="POST" action="index.php?controller=objectif&action=store" novalidate>
                    <div class="field">
                        <label>Calories cible</label>
                        <input type="text" name="calories_cible" placeholder="Ex: 2000" value="<?= htmlspecialchars((string) $objectifCaloriesValue) ?>">
                    </div>
                    <div class="field">
                        <label>Type d'objectif</label>
                        <select name="objectif_type">
                            <option value="maintien" <?= $selectedObjectifType === 'maintien' ? 'selected' : '' ?>>Maintien</option>
                            <option value="prise_muscle" <?= $selectedObjectifType === 'prise_muscle' ? 'selected' : '' ?>>Prise de muscle</option>
                        </select>
                    </div>
                    <button type="submit">
                        <?php if (!empty($objectif)): ?>
                            Mettre a jour l'objectif
                        <?php else: ?>
                            Definir l'objectif
                        <?php endif; ?>
                    </button>
                </form>
            </div>

            <?php if (!empty($objectif)): ?>
                <?php
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
                    <h2>Objectif defini</h2>
                    <p><strong>Calories cibles :</strong> <?= round($caloriesCible) ?> kcal</p>
                    <p><strong>Type d'objectif :</strong> <?= ($objectif['objectif_type'] ?? 'maintien') === 'prise_muscle' ? 'Prise de muscle' : 'Maintien' ?></p>
                    <p><strong>Proteines :</strong> <?= round($proteinesObjectif) ?> g</p>
                    <p><strong>Glucides :</strong> <?= round($glucidesObjectif) ?> g</p>
                    <p><strong>Lipides :</strong> <?= round($lipidesObjectif) ?> g</p>
                    <?php if (!empty($objectif['date_creation'])): ?>
                        <p class="muted">Cree le <?= htmlspecialchars($objectif['date_creation']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="card" style="margin-top: 20px;">
                    <h2>Comparaison quotidienne</h2>
                    <p><strong>Consomme aujourd'hui :</strong> <?= round($total_today) ?> kcal</p>
                    <p><strong>Restant :</strong> <?= $remaining ?> kcal</p>
                    <div class="progress-bar">
                        <div class="progress" style="width: <?= round($progress, 2) ?>%; background: <?= $color ?>;"></div>
                    </div>
                    <p style="color: <?= $color ?>; margin-top: 12px;">
                        <?= $status ?>
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
                                    <span class="macro-name"><?= $macroRow['label'] ?></span>
                                    <span class="macro-values">
                                        <?= round($macroConsumed, 1) ?> g / <?= round($macroTarget, 1) ?> g
                                        <span class="macro-percent">• <?= $macroPercent ?>%</span>
                                    </span>
                                </div>
                                <div class="macro-bar">
                                    <div class="macro-fill <?= $macroClass ?>" style="width: <?= round($macroProgress, 2) ?>%;"></div>
                                </div>
                                <div class="macro-status" style="color: <?= $macroColor ?>;">
                                    <?= $macroStatus ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="stats-link-wrap">
                    <a href="index.php?controller=stats&action=index" class="btn btn-primary">
                        Voir statistiques
                    </a>

                    <a href="index.php?controller=aliment&action=sendReport" class="btn btn-secondary">
                        Envoyer rapport hebdomadaire
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="/projet-web-25-26/public/assets/template_only_template/assets/js/script.js"></script>
</body>
</html>
