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

        /* ── SECTION WRAPPER PARTAGÉ ── */
        .section-wrapper {
            width: 90%;
            max-width: 860px;
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

        /* ── GLASSMORPHISM CARDS ── */
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

        /* ── CALORIES BADGE ── */
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

        /* ── FORMULAIRES ── */
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
        .form-group input[type="text"] {
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

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
            border-radius: 6px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        @media (max-width: 600px) {
            .form-row { grid-template-columns: 1fr; }
        }

        /* ── TABLEAUX ── */
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

        .delete-btn {
            background: rgba(231, 76, 60, 0.15);
            border: 1px solid rgba(231, 76, 60, 0.25);
            color: #e74c3c;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 7px;
            font-size: 0.8rem;
            transition: background 0.15s;
        }

        .delete-btn:hover {
            background: rgba(231, 76, 60, 0.28);
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn-danger {
            background: rgba(231, 76, 60, 0.15);
            border: 1px solid rgba(231, 76, 60, 0.3);
            color: #e74c3c;
        }

        .btn-danger:hover {
            background: rgba(231, 76, 60, 0.3);
        }

        /* ── FILTER ROW ── */
        .filter-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }

        .filter-row input[type="text"] {
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.07);
            color: var(--text-main, #ecf0f1);
            font-size: 0.9rem;
        }

        .divider {
            border: none;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            margin: 0;
        }

        /* ── TOP ACTIONS ── */
        .top-actions {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        .alert-error {
            background: rgba(231, 76, 60, 0.12);
            border: 1px solid rgba(231, 76, 60, 0.3);
            color: #f4b3ab;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 18px;
        }

        body.theme-light .alert-error {
            color: #9f2d20;
            background: rgba(231, 76, 60, 0.08);
        }

        .alert-error ul {
            margin: 8px 0 0;
            padding-left: 18px;
        }
    </style>
</head>

<body>
    <?php require __DIR__ . '/../partials/navbar.php'; ?>
    <?php
    $alimentErrors = $_SESSION['aliment_error'] ?? [];

    if (!is_array($alimentErrors) && !empty($alimentErrors)) {
        $alimentErrors = [$alimentErrors];
    }

    unset($_SESSION['aliment_error']);
    ?>

    <!-- HERO -->
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
            <p class="description-text">Analysez et suivez votre alimentation en temps réel</p>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════
         CAS 1 : Détail d'une journée (vue détail)
    ════════════════════════════════════════════════════ -->
    <?php if (!empty($details)): ?>

    <div class="section-wrapper">
        <h2 class="section-title"><i class="fa-solid fa-calendar-day"></i> Détail du jour</h2>

        <div class="glass-card">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Aliment</th>
                        <th>Quantité</th>
                        <th>Type</th>
                        <th>Calories</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($details as $d): ?>
                    <tr>
                        <td><?= htmlspecialchars($d['nom']) ?></td>
                        <td><?= htmlspecialchars($d['quantite']) ?> <?= ($d['unite'] ?? 'g') === 'piece' ? 'piece' : 'g' ?></td>
                        <td><?= htmlspecialchars($d['type']) ?></td>
                        <td><?= round($d['calories_calculees']) ?> kcal</td>
                        <td onclick="event.stopPropagation();">
                            <div class="action-buttons">
                                <a href="index.php?controller=aliment&action=edit&id=<?= $d['id'] ?>" class="btn btn-secondary btn-sm">
                                    <i class="fa-solid fa-pen"></i> Modifier
                                </a>

                                <a href="index.php?controller=aliment&action=delete&id=<?= $d['id'] ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Supprimer ?')">
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
            <a class="btn btn-primary" href="index.php?controller=aliment&action=index&mode=add&date=<?= urlencode($date ?? ($details[0]['date_consommation'] ?? date('Y-m-d'))) ?>">
                <i class="fa-solid fa-plus"></i> Ajouter pour cette date
            </a>
            <a class="btn btn-secondary" href="index.php?controller=aliment&action=index">
                <i class="fa-solid fa-arrow-left"></i> Retour au tableau de bord
            </a>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════
         CAS 2 : Tableau de bord principal
    ════════════════════════════════════════════════════ -->
    <?php else: ?>

    <div class="section-wrapper">
        <?php if (!empty($alimentErrors)): ?>
            <div class="alert-error">
                <strong>Erreur :</strong>
                <ul>
                    <?php foreach ($alimentErrors as $alimentError): ?>
                        <li><?= htmlspecialchars($alimentError) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Badge calories du jour -->
        <div class="calorie-badge">
            <span class="fire-icon">🔥</span>
            <div>
                <div class="calorie-value">
                    <?= round($total) ?> kcal
                    <?php if (!empty($isAddMode)): ?>
                        <span class="calorie-date"><?= htmlspecialchars($selectedDate) ?></span>
                    <?php endif; ?>
                </div>
                <div class="calorie-label">
                    <?= !empty($isAddMode) ? 'consommées pour cette date' : "consommées aujourd'hui" ?>
                </div>
            </div>
        </div>

        <!-- ── Ajouter une consommation ── -->
        <div class="glass-card">
            <h3 class="section-title">
                <i class="fa-solid fa-plus-circle"></i>
                <?= !empty($isAddMode) ? 'Ajouter une consommation pour le ' . htmlspecialchars($selectedDate) : 'Ajouter une consommation' ?>
            </h3>
            <form method="POST" action="index.php?controller=aliment&action=store" novalidate>
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($selectedUserId ?? '') ?>">
                <?php if (!empty($isAddMode)): ?>
                    <input type="hidden" name="date_consommation" value="<?= htmlspecialchars($selectedDate) ?>">
                    <input type="hidden" name="return_to_add" value="1">
                <?php endif; ?>
                <div class="form-row">
                    <div class="form-group">
                        <label>Type</label>
                        <select id="typeSelect" name="type">
                            <option value="">-- Choisir type --</option>
                            <option value="proteine">Protéine</option>
                            <option value="glucide">Glucide</option>
                            <option value="lipide">Lipide</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Aliment</label>
                        <select id="alimentSelect" name="aliment_id" disabled>
                            <option value="">-- Choisir aliment --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label id="quantityLabel">Quantite</label>
                        <input id="quantityInput" type="text" name="quantite" placeholder="Ex : 150">
                    </div>
                    <div style="display:flex; align-items:flex-end;">
                        <button type="submit" class="btn btn-primary" style="width:100%;">
                            <i class="fa-solid fa-plus"></i> Ajouter
                        </button>
                    </div>
                </div>
            </form>

            <div class="top-actions">
                <a href="index.php?controller=aliment&action=createCustom" class="btn btn-secondary">
                    <i class="fa-solid fa-pen-to-square"></i> Ajouter aliment personnalisé
                </a>
                <?php if (!empty($isAddMode)): ?>
                    <a href="index.php?controller=aliment&action=index&mode=detail&date=<?= urlencode($selectedDate) ?>" class="btn btn-secondary">
                        <i class="fa-solid fa-calendar-day"></i> Voir le détail
                    </a>
                    <a href="index.php?controller=aliment&action=index" class="btn btn-secondary">
                        <i class="fa-solid fa-clock-rotate-left"></i> Retour à l'historique
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (empty($isAddMode)): ?>
            <!-- ── Historique ── -->
            <div class="glass-card">
                <h3 class="section-title"><i class="fa-solid fa-clock-rotate-left"></i> Historique</h3>

                <!-- Filtre par date (hors du tableau) -->
                <form method="GET" action="index.php" class="filter-row">
                    <input type="hidden" name="controller" value="aliment">
                    <input type="hidden" name="action" value="index">
                    <div class="form-group" style="margin:0; flex:1;">
                        <input type="text" name="date" value="<?= htmlspecialchars($date ?? '') ?>" placeholder="YYYY-MM-DD">
                    </div>
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa-solid fa-filter"></i> Filtrer
                    </button>
                </form>

                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total Calories</th>
                            <th>Détail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $h): ?>
                        <tr onclick="window.location='index.php?controller=aliment&action=index&mode=detail&date=<?= htmlspecialchars($h['date_consommation']) ?>'">
                            <td><?= htmlspecialchars($h['date_consommation']) ?></td>
                            <td><?= round($h['total']) ?> kcal</td>
                            <td><span class="btn btn-secondary btn-sm">Voir <i class="fa-solid fa-chevron-right"></i></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>

    <?php endif; ?>

    <script>
        const aliments = <?= json_encode($aliments, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        const typeSelect = document.getElementById('typeSelect');
        const alimentSelect = document.getElementById('alimentSelect');
        const quantityLabel = document.getElementById('quantityLabel');
        const quantityInput = document.getElementById('quantityInput');
        const macroConfig = {
            proteine: { key: 'proteines', label: 'prot' },
            glucide: { key: 'glucides', label: 'gluc' },
            lipide: { key: 'lipides', label: 'lip' }
        };

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

        function getSelectedAliment() {
            const selectedId = Number(alimentSelect.value);

            return aliments.find(function (aliment) {
                return Number(aliment.id) === selectedId;
            }) || null;
        }

        if (typeSelect && alimentSelect) {
            typeSelect.addEventListener('change', function () {
                const selectedType = this.value;

                alimentSelect.innerHTML = '<option value="">-- Choisir aliment --</option>';
                alimentSelect.disabled = selectedType === '';
                setQuantityState(null);

                aliments.forEach(function (aliment) {
                    if (aliment.type === selectedType) {
                        const option = document.createElement('option');
                        option.value = aliment.id;
                        option.textContent = aliment.nom + ' (' + formatNumber(aliment.calories) + ' ' + getCalorieLabel(aliment) + ' | ' + buildMacroSummary(aliment, selectedType) + ')';
                        alimentSelect.appendChild(option);
                    }
                });
            });

            alimentSelect.addEventListener('change', function () {
                setQuantityState(getSelectedAliment());
            });

            setQuantityState(null);
        }
    </script>
    <script src="/projet-web-25-26/public/assets/template_only_template/assets/js/script.js"></script>
</body>
</html>
