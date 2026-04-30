<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Nutrition</title>
    <link rel="stylesheet" href="views/front/assets/css/style.css">
    <script src="views/front/assets/js/app.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            opacity: 1 !important;
            visibility: visible !important;
        }

        .hero-wrapper {
            opacity: 1 !important;
            visibility: visible !important;
        }

        .section-wrapper {
            width: 90%;
            max-width: 960px;
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
            margin-bottom: 28px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
        }

        body.theme-light .glass-card {
            background: rgba(255, 255, 255, 0.82);
            border-color: rgba(148, 163, 184, 0.18);
            box-shadow: 0 10px 30px rgba(148, 163, 184, 0.18);
        }

        .calorie-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            background: linear-gradient(135deg, rgba(46, 204, 113, 0.18), rgba(52, 152, 219, 0.12));
            border: 1px solid rgba(46, 204, 113, 0.30);
            border-radius: 14px;
            padding: 18px 28px;
            margin-bottom: 28px;
        }

        .calorie-badge .fire-icon {
            font-size: 2.4rem;
        }

        .calorie-badge .calorie-value {
            font-size: 2rem;
            font-weight: 800;
            color: #2ecc71;
            letter-spacing: -1px;
        }

        .calorie-badge .calorie-date {
            display: inline-block;
            margin-left: 10px;
            font-size: 1rem;
            font-weight: 600;
            color: rgba(236, 240, 241, 0.9);
            letter-spacing: 0;
        }

        body.theme-light .calorie-badge .calorie-date {
            color: #374151;
        }

        .calorie-badge .calorie-label {
            font-size: 0.9rem;
            color: rgba(236, 240, 241, 0.82);
            margin-top: 2px;
        }

        body.theme-light .calorie-badge .calorie-label {
            color: #4b5563;
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
            letter-spacing: 0.5px;
        }

        .form-group select,
        .form-group input[type="text"],
        .form-group input[type="date"] {
            display: block;
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.07);
            color: var(--text-main, #ecf0f1);
            font-size: 0.95rem;
            box-sizing: border-box;
            transition: border-color 0.2s, background 0.2s;
        }

        body.theme-light .form-group select,
        body.theme-light .form-group input[type="text"],
        body.theme-light .form-group input[type="date"] {
            color: #0f172a;
            background: rgba(248, 250, 252, 0.92);
            border-color: rgba(148, 163, 184, 0.25);
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: rgba(46, 204, 113, 0.5);
            background: rgba(255, 255, 255, 0.11);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 22px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
            text-decoration: none;
        }

        .btn:active {
            transform: scale(0.97);
        }

        .btn-primary {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: #fff;
            box-shadow: 0 4px 16px rgba(46, 204, 113, 0.30);
        }

        .btn-primary:hover {
            box-shadow: 0 6px 24px rgba(46, 204, 113, 0.45);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-main, #ecf0f1);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        body.theme-light .btn-secondary {
            color: #0f172a;
            background: rgba(248, 250, 252, 0.88);
            border-color: rgba(148, 163, 184, 0.24);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
            border-radius: 6px;
        }

        .btn-danger {
            background: rgba(231, 76, 60, 0.15);
            border: 1px solid rgba(231, 76, 60, 0.3);
            color: #e74c3c;
        }

        .btn-danger:hover {
            background: rgba(231, 76, 60, 0.3);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .autocomplete-wrapper {
            position: relative;
        }

        .suggestions-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            z-index: 20;
            display: none;
            background: rgba(15, 23, 42, 0.96);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            box-shadow: 0 16px 34px rgba(15, 23, 42, 0.34);
            overflow: hidden;
        }

        body.theme-light .suggestions-dropdown {
            background: rgba(255, 255, 255, 0.98);
            border-color: rgba(148, 163, 184, 0.24);
            box-shadow: 0 14px 28px rgba(148, 163, 184, 0.22);
        }

        .suggestions-dropdown.is-visible {
            display: block;
        }

        .suggestion-item,
        .suggestion-empty {
            padding: 12px 14px;
        }

        .suggestion-item {
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .suggestion-item:hover {
            background: rgba(46, 204, 113, 0.12);
        }

        body.theme-light .suggestion-item:hover {
            background: rgba(46, 204, 113, 0.10);
        }

        .suggestion-name {
            display: block;
            color: var(--text-main, #ecf0f1);
            font-weight: 700;
        }

        body.theme-light .suggestion-name {
            color: #0f172a;
        }

        .suggestion-meta,
        .suggestion-empty {
            color: rgba(236, 240, 241, 0.68);
            font-size: 0.84rem;
        }

        body.theme-light .suggestion-meta,
        body.theme-light .suggestion-empty {
            color: #64748b;
        }

        .history-filter-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 8px;
        }

        .styled-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .styled-table th {
            background: rgba(46, 204, 113, 0.12);
            color: #2ecc71;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid rgba(46, 204, 113, 0.25);
        }

        .styled-table td {
            padding: 13px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            color: var(--text-main, #ecf0f1);
            font-size: 0.93rem;
        }

        body.theme-light .styled-table td {
            color: #0f172a;
            border-bottom-color: rgba(148, 163, 184, 0.2);
        }

        .styled-table tr:last-child td {
            border-bottom: none;
        }

        .styled-table tbody tr {
            transition: background 0.15s;
            cursor: pointer;
        }

        .styled-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.04);
        }

        body.theme-light .styled-table tbody tr:hover {
            background: rgba(148, 163, 184, 0.08);
        }

        .filter-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }

        .top-actions {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        .alert-success,
        .alert-error {
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 18px;
        }

        .alert-success {
            background: rgba(46, 204, 113, 0.12);
            border: 1px solid rgba(46, 204, 113, 0.3);
            color: #b8f1cd;
        }

        .alert-error {
            background: rgba(231, 76, 60, 0.12);
            border: 1px solid rgba(231, 76, 60, 0.3);
            color: #f4b3ab;
        }

        body.theme-light .alert-success {
            color: #166534;
            background: rgba(46, 204, 113, 0.08);
        }

        body.theme-light .alert-error {
            color: #9f2d20;
            background: rgba(231, 76, 60, 0.08);
        }

        .alert-error ul {
            margin: 8px 0 0;
            padding-left: 18px;
        }

        .meal-helper {
            margin: 12px 0 0;
            color: rgba(236, 240, 241, 0.76);
            font-size: 0.93rem;
            line-height: 1.55;
        }

        body.theme-light .meal-helper {
            color: #5b6472;
        }

        .meal-status-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(46, 204, 113, 0.12);
            border: 1px solid rgba(46, 204, 113, 0.22);
            color: #a7f3d0;
            font-size: 0.85rem;
            font-weight: 700;
        }

        body.theme-light .meal-status-chip {
            color: #166534;
        }

        .meal-total-card {
            margin-top: 18px;
            padding: 18px;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(46, 204, 113, 0.14), rgba(52, 152, 219, 0.10));
            border: 1px solid rgba(46, 204, 113, 0.18);
        }

        .meal-total-card strong {
            display: block;
            font-size: 1rem;
        }

        .meal-total-value {
            font-size: 1.4rem;
            font-weight: 800;
            color: #2ecc71;
            white-space: nowrap;
        }

        .history-total.is-under,
        .history-objectif.is-under {
            color: #2ecc71;
            font-weight: 700;
        }

        .history-total.is-over,
        .history-objectif.is-over {
            color: #e74c3c;
            font-weight: 700;
        }

        .history-total.is-empty,
        .history-objectif.is-empty {
            color: rgba(236, 240, 241, 0.72);
            font-weight: 700;
        }

        body.theme-light .history-total.is-empty,
        body.theme-light .history-objectif.is-empty {
            color: #6b7280;
        }

        .history-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .history-status-badge.is-ok {
            background: rgba(46, 204, 113, 0.16);
            color: #b7f7ca;
        }

        .history-status-badge.is-over {
            background: rgba(231, 76, 60, 0.16);
            color: #f5b5ae;
        }

        .history-status-badge.is-under {
            background: rgba(59, 130, 246, 0.16);
            color: #bfdcff;
        }

        .history-status-badge.is-empty {
            background: rgba(148, 163, 184, 0.16);
            color: #d3dbe8;
        }

        body.theme-light .history-status-badge.is-ok {
            color: #166534;
        }

        body.theme-light .history-status-badge.is-over {
            color: #b91c1c;
        }

        body.theme-light .history-status-badge.is-under {
            color: #1d4ed8;
        }

        body.theme-light .history-status-badge.is-empty {
            color: #475569;
        }

        .history-empty,
        .empty-detail-card {
            text-align: center;
            color: rgba(236, 240, 241, 0.74);
        }

        body.theme-light .history-empty,
        body.theme-light .empty-detail-card {
            color: #6b7280;
        }

        .empty-detail-card {
            padding: 8px 0;
        }

        .empty-detail-icon {
            font-size: 2.2rem;
            color: #94a3b8;
            margin-bottom: 12px;
        }

        .inline-form {
            display: inline;
            margin: 0;
        }

        @media (max-width: 860px) {
            .history-filter-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 600px) {
            .form-row,
            .history-filter-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php require __DIR__ . '/../partials/navbar.php'; ?>
    <?php
    $alimentErrors = $_SESSION['aliment_error'] ?? [];
    $alimentSuccess = $_SESSION['aliment_success'] ?? null;

    if (!is_array($alimentErrors) && !empty($alimentErrors)) {
        $alimentErrors = [$alimentErrors];
    }

    $historyFilters = is_array($historyFilters ?? null) ? $historyFilters : [];
    $historyPeriod = (string) ($historyFilters['period'] ?? '');
    $historyStatus = (string) ($historyFilters['status'] ?? '');
    $historyStartDate = (string) ($historyFilters['start_date'] ?? '');
    $historyEndDate = (string) ($historyFilters['end_date'] ?? '');
    $hasHistoryFilters = $historyPeriod !== ''
        || $historyStatus !== ''
        || $historyStartDate !== ''
        || $historyEndDate !== '';

    $typeLabels = [
        'proteine' => 'Proteine',
        'glucide' => 'Glucide',
        'lipide' => 'Lipide',
    ];

    $statusLabels = [
        'ok' => 'Objectif atteint',
        'depasse' => 'Depasse',
        'sous' => 'Sous objectif',
        'aucune' => 'Aucune consommation',
    ];

    $selectedUserId = $selectedUserId ?? '';

    unset($_SESSION['aliment_error'], $_SESSION['aliment_success']);
    ?>

    <div class="hero-wrapper">
        <div class="cycle-diagram">
            <svg class="orbit-ring" viewBox="0 0 400 400">
                <circle cx="200" cy="200" r="140" class="ring-track" />
                <circle cx="200" cy="200" r="140" class="ring-glow" />
            </svg>
            <div class="node node-1">
                <div class="node-icon"><i class="fa-solid fa-leaf"></i></div>
            </div>
            <div class="node node-2">
                <div class="node-icon"><i class="fa-solid fa-apple-whole"></i></div>
            </div>
            <div class="node node-3">
                <div class="node-icon"><i class="fa-solid fa-person-running"></i></div>
            </div>
            <div class="node node-4">
                <div class="node-icon"><i class="fa-solid fa-utensils"></i></div>
            </div>
            <div class="center-piece">
                <div class="pulse-core"></div>
                <h3>Smart<br>System</h3>
            </div>
        </div>

        <div class="hero-content">
            <h1>Smart Nutrition</h1>
            <p class="subtitle-text">Sustainable &amp; Intelligent Food System</p>
            <p class="description-text">Analysez et suivez votre alimentation en temps reel</p>
        </div>
    </div>

    <?php if (!empty($details)): ?>
        <div class="section-wrapper">
            <?php if (!empty($alimentSuccess)): ?>
                <div class="alert-success"><?= htmlspecialchars((string) $alimentSuccess) ?></div>
            <?php endif; ?>

            <?php if (!empty($alimentErrors)): ?>
                <div class="alert-error">
                    <strong>Erreur :</strong>
                    <ul>
                        <?php foreach ($alimentErrors as $alimentError): ?>
                            <li><?= htmlspecialchars((string) $alimentError) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <h2 class="section-title"><i class="fa-solid fa-calendar-day"></i> Detail du jour</h2>

            <div class="glass-card">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Aliment</th>
                            <th>Quantite</th>
                            <th>Type</th>
                            <th>Calories</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($details as $d): ?>
                            <tr>
                                <td><?= htmlspecialchars((string) ($d['nom'] ?? 'Aliment')) ?></td>
                                <td><?= htmlspecialchars((string) ($d['quantite'] ?? 0)) ?> <?= (($d['unite'] ?? 'g') === 'piece') ? 'piece' : 'g' ?></td>
                                <td><?= htmlspecialchars((string) ($typeLabels[$d['type'] ?? ''] ?? ($d['type'] ?? '-'))) ?></td>
                                <td><?= round((float) ($d['calories_calculees'] ?? 0)) ?> kcal</td>
                                <td onclick="event.stopPropagation();">
                                    <div class="top-actions" style="margin-top: 0; justify-content: flex-start;">
                                        <a href="index.php?controller=suivi&action=edit&id=<?= (int) $d['id'] ?>" class="btn btn-secondary btn-sm">
                                            <i class="fa-solid fa-pen"></i> Modifier
                                        </a>
                                        <a href="index.php?controller=suivi&action=delete&id=<?= (int) $d['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ?')">
                                            <i class="fa-solid fa-trash"></i> Supprimer
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="top-actions">
                <a class="btn btn-primary" href="index.php?controller=suivi&action=index&mode=add&date=<?= urlencode($date ?? ($details[0]['date_consommation'] ?? date('Y-m-d'))) ?>">
                    <i class="fa-solid fa-plus"></i> Ajouter pour cette date
                </a>
                <a class="btn btn-secondary" href="index.php?controller=suivi&action=index">
                    <i class="fa-solid fa-arrow-left"></i> Retour au tableau de bord
                </a>
            </div>
        </div>
    <?php elseif (!empty($isEmptyDetailMode)): ?>
        <div class="section-wrapper">
            <?php if (!empty($alimentSuccess)): ?>
                <div class="alert-success"><?= htmlspecialchars((string) $alimentSuccess) ?></div>
            <?php endif; ?>

            <?php if (!empty($alimentErrors)): ?>
                <div class="alert-error">
                    <strong>Erreur :</strong>
                    <ul>
                        <?php foreach ($alimentErrors as $alimentError): ?>
                            <li><?= htmlspecialchars((string) $alimentError) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <h2 class="section-title"><i class="fa-solid fa-calendar-day"></i> Detail du jour</h2>

            <div class="glass-card empty-detail-card">
                <div class="empty-detail-icon">
                    <i class="fa-solid fa-calendar-xmark"></i>
                </div>
                <h3>Aucune consommation pour cette journee</h3>
                <p>Aucun repas n'est encore associe au <strong><?= htmlspecialchars($detailDate ?? date('Y-m-d')) ?></strong>.</p>
                <?php if (!empty($emptyDetailRow['objectif'])): ?>
                    <p class="meal-helper">Objectif prevu : <strong><?= round((float) $emptyDetailRow['objectif']) ?> kcal</strong></p>
                <?php endif; ?>
            </div>

            <div class="top-actions">
                <a class="btn btn-primary" href="index.php?controller=suivi&action=index&mode=add&date=<?= urlencode($detailDate ?? date('Y-m-d')) ?>">
                    <i class="fa-solid fa-plus"></i> Ajouter pour cette date
                </a>
                <a class="btn btn-secondary" href="index.php?controller=suivi&action=index">
                    <i class="fa-solid fa-arrow-left"></i> Retour au tableau de bord
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="section-wrapper">
            <?php if (!empty($alimentSuccess)): ?>
                <div class="alert-success"><?= htmlspecialchars((string) $alimentSuccess) ?></div>
            <?php endif; ?>

            <?php if (!empty($alimentErrors)): ?>
                <div class="alert-error">
                    <strong>Erreur :</strong>
                    <ul>
                        <?php foreach ($alimentErrors as $alimentError): ?>
                            <li><?= htmlspecialchars((string) $alimentError) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="calorie-badge">
                <span class="fire-icon">🔥</span>
                <div>
                    <div class="calorie-value">
                        <?= round((float) $total) ?> kcal
                        <?php if (!empty($showTrackedDate)): ?>
                            <span class="calorie-date"><?= htmlspecialchars((string) $trackingDate) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="calorie-label">
                        <?= !empty($isAddMode) ? 'consommees pour cette date' : "consommees aujourd'hui" ?>
                    </div>
                </div>
            </div>

            <div class="glass-card">
                <h3 class="section-title">
                    <i class="fa-solid fa-plus-circle"></i>
                    <?= !empty($isAddMode) ? 'Ajouter un aliment pour le ' . htmlspecialchars((string) $selectedDate) : 'Ajouter un aliment' ?>
                </h3>

                <?php if (!empty($hasMealDateConflict) && !empty($mealDate)): ?>
                    <p class="meal-helper">
                        Un repas est deja en cours pour le <strong><?= htmlspecialchars((string) $mealDate) ?></strong>.
                        Continuez ce repas ou annulez-le avant de composer une autre date.
                    </p>
                    <div class="top-actions">
                        <a href="index.php?controller=suivi&action=index&mode=add&date=<?= urlencode((string) $mealDate) ?>" class="btn btn-primary">
                            <i class="fa-solid fa-utensils"></i> Continuer ce repas
                        </a>
                        <a href="index.php?controller=suivi&action=createCustom" class="btn btn-secondary">
                            <i class="fa-solid fa-pen-to-square"></i> Ajouter aliment personnalise
                        </a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="index.php?controller=suivi&action=store" novalidate>
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars((string) $selectedUserId) ?>">
                        <input type="hidden" name="date_consommation" value="<?= htmlspecialchars((string) $composerDate) ?>">
                        <input type="hidden" name="origin" value="<?= !empty($isAddMode) ? 'history' : 'main' ?>">
                        <input type="hidden" name="aliment_id" id="alimentIdInput" value="">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Type</label>
                                <select id="typeSelect" name="type">
                                    <option value="">-- Choisir type --</option>
                                    <option value="proteine">Proteine</option>
                                    <option value="glucide">Glucide</option>
                                    <option value="lipide">Lipide</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Aliment</label>
                                <div class="autocomplete-wrapper">
                                    <input id="searchInput" type="text" placeholder="Rechercher un aliment..." disabled autocomplete="off">
                                    <div id="suggestions" class="suggestions-dropdown"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label id="quantityLabel">Quantite</label>
                                <input id="quantityInput" type="text" name="quantite" placeholder="Ex : 150">
                            </div>
                            <div style="display:flex; align-items:flex-end;">
                                <button type="submit" class="btn btn-primary" style="width:100%;">
                                    <i class="fa-solid fa-plus"></i> Ajouter au repas
                                </button>
                            </div>
                        </div>
                    </form>

                    <p class="meal-helper">
                        Ajoutez plusieurs aliments a ce repas, verifiez le total calorique puis cliquez sur
                        <strong>Valider le repas</strong> quand la composition vous convient.
                    </p>

                    <?php if (!$isAddMode && !empty($mealDate)): ?>
                        <p class="meal-helper">
                            Le repas en cours est rattache au <strong><?= htmlspecialchars((string) $mealDate) ?></strong>.
                        </p>
                    <?php endif; ?>

                    <div class="top-actions">
                        <a href="index.php?controller=suivi&action=createCustom" class="btn btn-secondary">
                            <i class="fa-solid fa-pen-to-square"></i> Ajouter aliment personnalise
                        </a>
                        <?php if (!empty($isAddMode)): ?>
                            <a href="index.php?controller=suivi&action=index&mode=detail&date=<?= urlencode((string) $selectedDate) ?>" class="btn btn-secondary">
                                <i class="fa-solid fa-calendar-day"></i> Voir le detail
                            </a>
                            <a href="index.php?controller=suivi&action=index" class="btn btn-secondary">
                                <i class="fa-solid fa-clock-rotate-left"></i> Retour a l'historique
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($showMealSection)): ?>
                <div class="glass-card">
                    <div class="top-actions" style="margin-top: 0; margin-bottom: 18px;">
                        <h3 class="section-title" style="margin-bottom: 0;">
                            <i class="fa-solid fa-utensils"></i> Repas en cours
                        </h3>
                        <?php if (!empty($mealDate)): ?>
                            <span class="meal-status-chip">
                                <i class="fa-solid fa-calendar-day"></i>
                                <?= htmlspecialchars((string) $mealDate) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>Aliment</th>
                                <th>Quantite</th>
                                <th>Calories</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mealItems as $mealIndex => $mealItem): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string) ($mealItem['nom'] ?? 'Aliment')) ?></td>
                                    <td>
                                        <?= htmlspecialchars((string) ($mealItem['quantite'] ?? 0)) ?>
                                        <?= (($mealItem['unite'] ?? 'g') === 'piece') ? 'piece' : 'g' ?>
                                    </td>
                                    <td><?= round((float) ($mealItem['calories'] ?? 0)) ?> kcal</td>
                                    <td>
                                        <form method="POST" action="index.php?controller=suivi&action=removeMealItem" class="inline-form">
                                            <input type="hidden" name="item_index" value="<?= (int) $mealIndex ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fa-solid fa-xmark"></i> Retirer
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="meal-total-card">
                        <strong>Total du repas</strong>
                        <div class="meal-total-value"><?= round((float) $mealTotal) ?> kcal</div>
                        <p class="meal-helper" style="margin-top: 8px;">Ce total sera enregistre en base seulement apres validation.</p>
                    </div>

                    <div class="top-actions">
                        <form method="POST" action="index.php?controller=suivi&action=validateMeal" class="inline-form">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-check"></i> Valider le repas
                            </button>
                        </form>
                        <form method="POST" action="index.php?controller=suivi&action=cancelMeal" class="inline-form">
                            <button type="submit" class="btn btn-secondary">
                                <i class="fa-solid fa-xmark"></i> Annuler
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (empty($isAddMode)): ?>
                <div class="glass-card">
                    <h3 class="section-title"><i class="fa-solid fa-clock-rotate-left"></i> Historique</h3>

                    <form method="GET" action="index.php" class="filter-row" style="display:block;">
                        <input type="hidden" name="controller" value="suivi">
                        <input type="hidden" name="action" value="index">
                        <div class="history-filter-grid">
                            <div class="form-group" style="margin:0;">
                                <label for="history_period">Periode</label>
                                <select id="history_period" name="history_period">
                                    <option value="" <?= $historyPeriod === '' ? 'selected' : '' ?>>Plan en cours</option>
                                    <option value="today" <?= $historyPeriod === 'today' ? 'selected' : '' ?>>Aujourd hui</option>
                                    <option value="last7" <?= $historyPeriod === 'last7' ? 'selected' : '' ?>>7 derniers jours</option>
                                    <option value="custom" <?= $historyPeriod === 'custom' ? 'selected' : '' ?>>Plage personnalisee</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label for="history_status">Statut</label>
                                <select id="history_status" name="history_status">
                                    <option value="" <?= $historyStatus === '' ? 'selected' : '' ?>>Tous</option>
                                    <option value="ok" <?= $historyStatus === 'ok' ? 'selected' : '' ?>>Objectif atteint</option>
                                    <option value="depasse" <?= $historyStatus === 'depasse' ? 'selected' : '' ?>>Depasse</option>
                                    <option value="sous" <?= $historyStatus === 'sous' ? 'selected' : '' ?>>Sous objectif</option>
                                    <option value="aucune" <?= $historyStatus === 'aucune' ? 'selected' : '' ?>>Aucune consommation</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label for="history_start_date">Date debut</label>
                                <input id="history_start_date" type="date" name="history_start_date" value="<?= htmlspecialchars($historyStartDate) ?>">
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label for="history_end_date">Date fin</label>
                                <input id="history_end_date" type="date" name="history_end_date" value="<?= htmlspecialchars($historyEndDate) ?>">
                            </div>
                        </div>
                        <div class="top-actions" style="justify-content:flex-start;">
                            <button type="submit" class="btn btn-secondary">
                                <i class="fa-solid fa-filter"></i> Filtrer
                            </button>
                            <a href="index.php?controller=suivi&action=index" class="btn btn-secondary">
                                <i class="fa-solid fa-rotate-left"></i> Reinitialiser
                            </a>
                        </div>
                    </form>

                    <?php if (!empty($history)): ?>
                        <table class="styled-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Total Calories</th>
                                    <th>Objectif</th>
                                    <th>Statut</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $row): ?>
                                    <?php
                                    $rowStatus = (string) ($row['statut'] ?? '');
                                    $valueClass = $rowStatus === 'depasse'
                                        ? 'is-over'
                                        : ($rowStatus === 'aucune' ? 'is-empty' : 'is-under');
                                    $badgeClass = $rowStatus === 'depasse'
                                        ? 'is-over'
                                        : ($rowStatus === 'ok' ? 'is-ok' : ($rowStatus === 'aucune' ? 'is-empty' : 'is-under'));
                                    $detailLink = 'index.php?controller=suivi&action=index&mode=detail&date=' . urlencode((string) ($row['date_consommation'] ?? date('Y-m-d')));
                                    ?>
                                    <tr onclick="window.location='<?= htmlspecialchars($detailLink, ENT_QUOTES) ?>'">
                                        <td><?= htmlspecialchars((string) ($row['date_consommation'] ?? '-')) ?></td>
                                        <td class="history-total <?= $valueClass ?>"><?= round((float) ($row['total_calories'] ?? 0)) ?> kcal</td>
                                        <td class="history-objectif <?= $valueClass ?>"><?= round((float) ($row['objectif'] ?? 0)) ?> kcal</td>
                                        <td>
                                            <span class="history-status-badge <?= $badgeClass ?>">
                                                <?= htmlspecialchars($statusLabels[$rowStatus] ?? 'Statut') ?>
                                            </span>
                                        </td>
                                        <td><span class="btn btn-secondary btn-sm">Voir <i class="fa-solid fa-chevron-right"></i></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="history-empty" style="padding: 16px 0 4px;">
                            <?= $hasHistoryFilters ? 'Aucun resultat pour les filtres selectionnes.' : 'Aucun historique disponible pour le moment.' ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <script>
        const typeSelect = document.getElementById('typeSelect');
        const searchInput = document.getElementById('searchInput');
        const suggestions = document.getElementById('suggestions');
        const alimentIdInput = document.getElementById('alimentIdInput');
        const quantityLabel = document.getElementById('quantityLabel');
        const quantityInput = document.getElementById('quantityInput');
        const historyPeriodSelect = document.getElementById('history_period');
        const historyStartDateInput = document.getElementById('history_start_date');
        const historyEndDateInput = document.getElementById('history_end_date');
        const macroConfig = {
            proteine: { key: 'proteines', label: 'prot' },
            glucide: { key: 'glucides', label: 'gluc' },
            lipide: { key: 'lipides', label: 'lip' }
        };
        let selectedAliment = null;
        let activeSearchToken = 0;

        function formatNumber(value) {
            const number = Number(value ?? 0);
            return Number.isFinite(number) ? number.toFixed(1).replace(/\.0$/, '') : '0';
        }

        function buildMacroSummary(aliment, selectedType) {
            const order = ['proteine', 'glucide', 'lipide'];

            if (order.includes(selectedType)) {
                order.splice(order.indexOf(selectedType), 1);
                order.unshift(selectedType);
            }

            return order.map(function (type) {
                const config = macroConfig[type];
                return formatNumber(aliment[config.key]) + 'g ' + config.label;
            }).join(' | ');
        }

        function getUnitLabel(aliment) {
            return aliment && aliment.unite === 'piece' ? 'piece' : 'g';
        }

        function getCalorieLabel(aliment) {
            return aliment && aliment.unite === 'piece' ? 'kcal/piece' : 'kcal/100g';
        }

        function setQuantityState(aliment) {
            if (!quantityLabel || !quantityInput) {
                return;
            }

            const unit = getUnitLabel(aliment);
            quantityLabel.textContent = 'Quantite (' + unit + ')';
            quantityInput.placeholder = unit === 'piece' ? 'Ex : 2' : 'Ex : 150';
            quantityInput.inputMode = unit === 'piece' ? 'numeric' : 'decimal';
        }

        function hideSuggestions() {
            if (!suggestions) {
                return;
            }

            suggestions.innerHTML = '';
            suggestions.classList.remove('is-visible');
        }

        function clearSelectedAliment(resetInput) {
            selectedAliment = null;

            if (alimentIdInput) {
                alimentIdInput.value = '';
            }

            if (resetInput && searchInput) {
                searchInput.value = '';
            }

            setQuantityState(null);
        }

        function selectAliment(aliment) {
            selectedAliment = aliment;

            if (alimentIdInput) {
                alimentIdInput.value = String(aliment.id);
            }

            if (searchInput) {
                searchInput.value = aliment.nom;
            }

            setQuantityState(aliment);
            hideSuggestions();
        }

        function renderSuggestions(items, selectedType) {
            if (!suggestions) {
                return;
            }

            suggestions.innerHTML = '';

            if (!items.length) {
                const emptyNode = document.createElement('div');
                emptyNode.className = 'suggestion-empty';
                emptyNode.textContent = 'Aucun aliment trouve pour cette recherche.';
                suggestions.appendChild(emptyNode);
                suggestions.classList.add('is-visible');
                return;
            }

            items.forEach(function (item) {
                const option = document.createElement('div');
                const name = document.createElement('span');
                const meta = document.createElement('span');

                option.className = 'suggestion-item';
                name.className = 'suggestion-name';
                meta.className = 'suggestion-meta';

                name.textContent = item.nom;
                meta.textContent = formatNumber(item.calories) + ' ' + getCalorieLabel(item) + ' | ' + buildMacroSummary(item, selectedType);

                option.appendChild(name);
                option.appendChild(meta);
                option.addEventListener('click', function () {
                    selectAliment(item);
                });

                suggestions.appendChild(option);
            });

            suggestions.classList.add('is-visible');
        }

        function fetchSuggestions() {
            if (!typeSelect || !searchInput) {
                return;
            }

            const selectedType = typeSelect.value;
            const query = searchInput.value.trim();

            clearSelectedAliment(false);

            if (selectedType === '' || query === '') {
                hideSuggestions();
                return;
            }

            const currentToken = ++activeSearchToken;

            fetch('index.php?controller=suivi&action=searchAliment&query=' + encodeURIComponent(query) + '&type=' + encodeURIComponent(selectedType))
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (currentToken !== activeSearchToken) {
                        return;
                    }

                    renderSuggestions(Array.isArray(data) ? data : [], selectedType);
                })
                .catch(function () {
                    if (currentToken !== activeSearchToken) {
                        return;
                    }

                    hideSuggestions();
                });
        }

        if (typeSelect && searchInput) {
            typeSelect.addEventListener('change', function () {
                const hasType = this.value !== '';

                clearSelectedAliment(true);
                hideSuggestions();

                searchInput.disabled = !hasType;
                searchInput.placeholder = hasType ? 'Rechercher un aliment...' : 'Choisissez un type d abord';
            });

            searchInput.addEventListener('input', function () {
                fetchSuggestions();
            });

            document.addEventListener('click', function (event) {
                const clickedInsideAutocomplete = event.target === searchInput
                    || (suggestions && suggestions.contains(event.target));

                if (!clickedInsideAutocomplete) {
                    hideSuggestions();
                }
            });

            searchInput.disabled = true;
            searchInput.placeholder = 'Choisissez un type d abord';
            setQuantityState(null);
        }

        if (historyPeriodSelect && historyStartDateInput && historyEndDateInput) {
            const syncHistoryDateInputs = function () {
                const isCustom = historyPeriodSelect.value === 'custom';
                historyStartDateInput.disabled = !isCustom;
                historyEndDateInput.disabled = !isCustom;
            };

            historyPeriodSelect.addEventListener('change', syncHistoryDateInputs);
            syncHistoryDateInputs();
        }
    </script>
    <script src="/projet-web-25-26/public/assets/template_only_template/assets/js/script.js"></script>
</body>

</html>
