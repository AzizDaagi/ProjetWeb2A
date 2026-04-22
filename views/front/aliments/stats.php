<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evolution des calories consommees par rapport a l'objectif</title>
    <link rel="stylesheet" href="views/front/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --kpi-ok: #2ecc71;
            --kpi-over: #e74c3c;
            --kpi-neutral: #ecf0f1;
            --kpi-card-bg: rgba(19, 27, 38, 0.78);
            --kpi-card-border: rgba(236, 240, 241, 0.12);
            --kpi-label: rgba(236, 240, 241, 0.72);
            --gauge-card-bg: rgba(255, 255, 255, 0.05);
            --gauge-card-border: rgba(236, 240, 241, 0.12);
            --gauge-text-color: #475569;
            --gauge-text-bright: #94a3b8;
        }

        .stats-container {
            max-width: 1100px;
        }

        .stats-title {
            text-align: center;
            margin-bottom: 18px;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-top: 24px;
        }

        .kpi-card,
        .goal-gauge-card {
            padding: 18px;
            border-radius: 8px;
            background: var(--kpi-card-bg);
            border: 1px solid var(--kpi-card-border);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }

        .goal-gauge-card {
            background: var(--gauge-card-bg);
            border: 1px solid var(--gauge-card-border);
        }

        .kpi-card {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .kpi-label,
        .goal-gauge-title,
        .goal-metric-label,
        .goal-legend,
        .gauge-center-sub {
            color: var(--kpi-label);
        }

        .kpi-label {
            font-size: 0.9rem;
            color: #94a3b8 !important;
        }

        .kpi-value {
            font-size: 1.9rem;
            font-weight: 800;
            color: #ffffff !important;
        }

        .kpi-value.is-ok {
            color: #2ecc71 !important;
        }

        .kpi-value.is-over {
            color: #e74c3c !important;
        }

        body.theme-light .kpi-label {
            color: #94a3b8 !important;
        }

        body.theme-light .kpi-value {
            color: #ffffff !important;
        }

        body.theme-light .kpi-value.is-ok {
            color: #2ecc71 !important;
        }

        body.theme-light .kpi-value.is-over {
            color: #e74c3c !important;
        }

        .kpi-value.is-ok {
            color: var(--kpi-ok);
        }

        .kpi-value.is-over {
            color: var(--kpi-over);
        }

        .goal-gauge-title {
            font-size: 1.2rem;
            margin-bottom: 14px;
            text-align: center;
            font-weight: 700;
        }

        .goal-gauge-card .goal-gauge-title,
        .goal-gauge-card .goal-legend {
            color: var(--text-main);
        }

        .goal-gauge-card .goal-metric-label {
            color: var(--text-main);
            opacity: 0.7;
        }

        .goal-gauge-card .goal-metric-value,
        .goal-gauge-card .gauge-center-pct {
            color: var(--gauge-text-bright);
            font-weight: bold;
        }

        .goal-gauge-card .gauge-center-sub {
            color: var(--text-main);
            opacity: 0.7;
        }

        .goal-metrics {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 16px;
        }

        .goal-metric {
            text-align: center;
            padding: 18px;
            border-radius: 8px;
            background: var(--kpi-card-bg);
            border: 1px solid var(--kpi-card-border);
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .goal-metric-label {
            display: block;
            font-size: 0.9rem;
            color: var(--kpi-label);
            margin-bottom: 0;
        }

        .goal-metric-value {
            display: block;
            font-size: 1.9rem;
            font-weight: 800;
            color: var(--kpi-neutral);
        }

        .goal-legend {
            display: flex;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
            font-size: 0.8rem;
            margin-bottom: 12px;
        }

        .goal-legend-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .goal-swatch {
            width: 10px;
            height: 10px;
            border-radius: 2px;
            display: inline-block;
        }

        .gauge-wrap {
            position: relative;
            height: 250px;
        }

        .gauge-center {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .gauge-center-pct {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
            color: var(--kpi-neutral);
        }

        .gauge-center-sub {
            font-size: 0.8rem;
            margin-top: 6px;
        }

        .stats-card {
            margin-top: 24px;
        }

        .goal-card-wrap {
            display: flex;
            justify-content: center;
            margin-top: 24px;
        }

        .goal-gauge-card {
            width: min(100%, 420px);
        }

        .chart-area {
            position: relative;
            width: 100%;
            height: 400px;
        }

        .stats-actions {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .stats-actions .btn {
            width: auto;
            margin-top: 0;
            padding: 10px 16px;
        }

        @media (max-width: 768px) {
            .kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .goal-metrics {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php require __DIR__ . '/../partials/navbar.php'; ?>

    <div class="main-content">
        <div class="container stats-container">
            <h1 class="stats-title">Evolution des calories consommees par rapport a l'objectif</h1>

            <div class="kpi-grid">
                <div class="kpi-card">
                    <span class="kpi-label">Jours dans l'objectif</span>
                    <span class="kpi-value is-ok" id="kpi-ok">...</span>
                </div>

                <div class="kpi-card">
                    <span class="kpi-label">Jours en depassement</span>
                    <span class="kpi-value is-over" id="kpi-over">...</span>
                </div>

                <div class="kpi-card">
                    <span class="kpi-label">Moyenne / jour</span>
                    <span class="kpi-value" id="kpi-avg">...</span>
                </div>

                <div class="kpi-card">
                    <span class="kpi-label">Pic journalier</span>
                    <span class="kpi-value" id="kpi-max">...</span>
                </div>
            </div>

            <div class="card stats-card">
                <div class="chart-area">
                    <canvas id="calorieChart" style="max-width:100%; height:400px;"></canvas>
                </div>
            </div>

            <div class="goal-card-wrap">
                <div class="goal-gauge-card">
                    <div class="goal-gauge-title">Reussite calorique ce mois</div>

                    <div class="goal-metrics">
                        <div class="goal-metric">
                            <span class="goal-metric-label">Jours ecoules</span>
                            <span class="goal-metric-value"><?= (int) ($monthlyGoal['days_elapsed'] ?? 0) ?></span>
                        </div>
                        <div class="goal-metric">
                            <span class="goal-metric-label">Objectif respecte</span>
                            <span class="goal-metric-value"><?= (int) ($monthlyGoal['success_days'] ?? 0) ?></span>
                        </div>
                        <div class="goal-metric">
                            <span class="goal-metric-label">Taux</span>
                            <span class="goal-metric-value"><?= (int) ($monthlyGoal['rate'] ?? 0) ?>%</span>
                        </div>
                    </div>

                    <div class="goal-legend">
                        <span class="goal-legend-item">
                            <span class="goal-swatch" style="background:#10b981"></span>Jours reussis
                        </span>
                        <span class="goal-legend-item">
                            <span class="goal-swatch" style="background:#cbd5e1"></span>Jours manques
                        </span>
                    </div>

                    <div class="gauge-wrap">
                        <canvas
                            id="goalGauge"
                            role="img"
                            aria-label="Jauge : <?= (int) ($monthlyGoal['rate'] ?? 0) ?>% des jours avec objectif calorique respecte ce mois-ci">
                            <?= (int) ($monthlyGoal['success_days'] ?? 0) ?> jours reussis sur <?= (int) ($monthlyGoal['days_elapsed'] ?? 0) ?>.
                        </canvas>
                        <div class="gauge-center">
                            <div class="gauge-center-pct"><?= (int) ($monthlyGoal['rate'] ?? 0) ?>%</div>
                            <div class="gauge-center-sub">ce mois</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stats-actions">
                <a href="index.php" class="btn btn-secondary">Retour</a>
            </div>
        </div>
    </div>

    <script>
        const data = <?= json_encode($data30 ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        const chartCanvas = document.getElementById('calorieChart');
        const goalGaugeCanvas = document.getElementById('goalGauge');
        const kpiOk = document.getElementById('kpi-ok');
        const kpiOver = document.getElementById('kpi-over');
        const kpiAvg = document.getElementById('kpi-avg');
        const kpiMax = document.getElementById('kpi-max');

        fetch('index.php?controller=stats&action=kpiJson')
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Erreur KPI');
                }

                return response.json();
            })
            .then(function (kpis) {
                if (kpiOk) {
                    kpiOk.textContent = kpis.jours_ok;
                }
                if (kpiOver) {
                    kpiOver.textContent = kpis.jours_over;
                }
                if (kpiAvg) {
                    kpiAvg.textContent = kpis.moyenne + ' kcal';
                }
                if (kpiMax) {
                    kpiMax.textContent = kpis.pic + ' kcal';
                }
            })
            .catch(function () {
                if (kpiOk) {
                    kpiOk.textContent = '--';
                }
                if (kpiOver) {
                    kpiOver.textContent = '--';
                }
                if (kpiAvg) {
                    kpiAvg.textContent = '--';
                }
                if (kpiMax) {
                    kpiMax.textContent = '--';
                }
            });

        if (goalGaugeCanvas && typeof Chart !== 'undefined') {
            const gaugeContext = goalGaugeCanvas.getContext('2d');
            const gaugeGradient = gaugeContext.createLinearGradient(0, 0, 0, 200);
            gaugeGradient.addColorStop(0, '#10b981');
            gaugeGradient.addColorStop(1, '#059669');

            new Chart(goalGaugeCanvas, {
                type: 'doughnut',
                data: {
                    labels: ['Reussis', 'Manques'],
                    datasets: [
                        {
                            data: [
                                <?= (int) ($monthlyGoal['success_days'] ?? 0) ?>,
                                <?= (int) ($monthlyGoal['missed_days'] ?? 0) ?>
                            ],
                            backgroundColor: [gaugeGradient, '#cbd5e1'],
                            borderWidth: 0,
                            borderRadius: 4,
                            spacing: 3,
                            hoverOffset: 8
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: {
                            display: false,
                            labels: {
                                color: '#1e293b'
                            }
                        },
                        tooltip: {
                            backgroundColor: '#f1f5f9',
                            titleColor: '#1e293b',
                            bodyColor: '#1e293b',
                            borderColor: 'rgba(15, 23, 42, 0.2)',
                            borderWidth: 1,
                            callbacks: {
                                label: function (context) {
                                    const value = Number(context.raw || 0);
                                    const total = context.dataset.data.reduce(function (sum, item) {
                                        return sum + Number(item || 0);
                                    }, 0);
                                    const percent = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return ' ' + value + ' jour' + (value > 1 ? 's' : '') + ' (' + percent + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        if (chartCanvas && typeof Chart !== 'undefined') {
            const labels = data.map(function (day) {
                return day.date_consommation;
            });

            const calories = data.map(function (day) {
                return Number(day.total);
            });

            const objectif = <?= !empty($objectif['calories_cible']) ? (float) $objectif['calories_cible'] : 2000 ?>;
            const objectifLine = labels.map(function () {
                return objectif;
            });

            const pointColors = calories.map(function (calorie) {
                return calorie <= objectif ? '#22c55e' : '#ef4444';
            });

            new Chart(chartCanvas, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Calories',
                            data: calories,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.16)',
                            pointBackgroundColor: pointColors,
                            pointBorderColor: pointColors,
                            pointHoverRadius: 6,
                            pointRadius: 4,
                            tension: 0.3,
                            fill: false
                        },
                        {
                            label: 'Objectif',
                            data: objectifLine,
                            borderColor: '#22c55e',
                            borderDash: [5, 5],
                            pointRadius: 0,
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#94a3b8'
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#94a3b8'
                            },
                            grid: {
                                color: 'rgba(148, 163, 184, 0.12)'
                            }
                        },
                        y: {
                            ticks: {
                                color: '#94a3b8'
                            },
                            grid: {
                                color: 'rgba(148, 163, 184, 0.12)'
                            }
                        }
                    }
                }
            });
        }
    </script>
    <script src="/projet-web-25-26/public/assets/template_only_template/assets/js/script.js"></script>
</body>

</html>
