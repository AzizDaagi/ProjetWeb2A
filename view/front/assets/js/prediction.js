document.addEventListener('DOMContentLoaded', function () {
  const root = document.querySelector('[data-prediction-dashboard]');

  if (!root) {
    return;
  }

  const urls = {
    scenarios: root.dataset.scenariosUrl || 'index.php?action=prediction_scenarios',
    trend: root.dataset.trendUrl || 'index.php?action=prediction_weekly_trend',
    confidence: root.dataset.confidenceUrl || 'index.php?action=prediction_confidence',
    whatIf: root.dataset.whatIfUrl || 'index.php?action=prediction_what_if'
  };
  const infoBox = document.getElementById('predictionInfo');
  const errorBox = document.getElementById('predictionError');
  const lowDataBox = document.getElementById('predictionLowData');
  const disclaimerBox = document.getElementById('predictionDisclaimer');
  const whatIfForm = document.getElementById('predictionWhatIfForm');
  const whatIfButton = document.getElementById('predictionWhatIfButton');

  function setInfo(message) {
    if (!infoBox) {
      return;
    }

    infoBox.hidden = !message;
    infoBox.textContent = message || '';
  }

  function setError(message) {
    if (!errorBox) {
      return;
    }

    errorBox.hidden = !message;
    errorBox.textContent = message || '';
  }

  function setDisclaimer(message) {
    if (!disclaimerBox) {
      return;
    }

    disclaimerBox.textContent = message || 'Projection indicative basee sur les donnees enregistrees, a interpreter avec prudence.';
  }

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function formatDate(value) {
    if (!value) {
      return 'Non calculable';
    }

    const date = new Date(value + 'T00:00:00');

    if (Number.isNaN(date.getTime())) {
      return value;
    }

    return date.toLocaleDateString('fr-FR', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  }

  function formatNumber(value, suffix) {
    const parsedValue = Number(value);

    if (Number.isNaN(parsedValue)) {
      return '--';
    }

    if (!suffix) {
      return parsedValue.toString();
    }

    return parsedValue + ' ' + suffix;
  }

  function setUpdatedAt() {
    const element = document.getElementById('predictionUpdatedAt');

    if (!element) {
      return;
    }

    element.textContent = new Date().toLocaleString('fr-FR', {
      dateStyle: 'medium',
      timeStyle: 'short'
    });
  }

  function getConfidenceTone(label) {
    if ((label || '').indexOf('Projection fiable') === 0) {
      return 'reliable';
    }

    if ((label || '').indexOf('Projection indicative') === 0) {
      return 'indicative';
    }

    return 'insufficient';
  }

  function getConfidenceBadgeClass(label) {
    const tone = getConfidenceTone(label);

    if (tone === 'reliable') {
      return 'prediction-badge prediction-badge-reliable';
    }

    if (tone === 'indicative') {
      return 'prediction-badge prediction-badge-indicative';
    }

    return 'prediction-badge prediction-badge-insufficient';
  }

  function renderConfidence(confidenceData) {
    const badge = document.getElementById('predictionConfidenceBadge');
    const value = document.getElementById('predictionConfidenceValue');
    const label = document.getElementById('predictionConfidenceLabel');
    const loggedDays = document.getElementById('predictionLoggedDays');
    const stdDev = document.getElementById('predictionStdDev');

    if (badge) {
      badge.className = getConfidenceBadgeClass(confidenceData.label || '');
      badge.textContent = confidenceData.label || '--';
    }

    if (value) {
      value.textContent = typeof confidenceData.confidence !== 'undefined'
        ? Number(confidenceData.confidence).toFixed(3)
        : '--';
    }

    if (label) {
      label.textContent = confidenceData.label || 'En attente des donnees.';
    }

    if (loggedDays) {
      loggedDays.textContent = (confidenceData.logged_days ?? 0) + ' / ' + (confidenceData.period_days ?? 28);
    }

    if (stdDev) {
      stdDev.textContent = formatNumber(confidenceData.std_dev, 'kcal');
    }
  }

  function renderSummary(scenariosData, confidenceData) {
    const currentScenario = Array.isArray(scenariosData.scenarios)
      ? scenariosData.scenarios.find(function (scenario) { return scenario.name === 'current'; })
      : null;
    const mainDate = document.getElementById('predictionMainDate');
    const mainMessage = document.getElementById('predictionMainMessage');
    const progressWrap = document.getElementById('predictionProgressWrap');
    const progressBar = document.getElementById('predictionProgressBar');
    const progressText = document.getElementById('predictionProgressText');
    const progressWeights = document.getElementById('predictionProgressWeights');
    const progressNote = document.getElementById('predictionProgressNote');
    const goalProgress = scenariosData.goal_progress || null;
    const hasPositiveWeeklyDelta = currentScenario && Number(currentScenario.weekly_delta || 0) > 0;
    const currentScenarioMessage = currentScenario && typeof currentScenario.message === 'string'
      ? currentScenario.message.toLowerCase()
      : '';
    const hasMissingGoalDefinitionMessage = currentScenario
      && currentScenarioMessage.indexOf('objectif restant non') === 0;

    if (mainDate) {
      if (currentScenario && currentScenario.predicted_goal_date) {
        mainDate.textContent = formatDate(currentScenario.predicted_goal_date);
      } else if (hasPositiveWeeklyDelta && hasMissingGoalDefinitionMessage) {
        mainDate.textContent = 'Objectif restant non defini';
      } else {
        mainDate.textContent = 'Date non calculable';
      }
    }

    if (mainMessage) {
      mainMessage.textContent = currentScenario && currentScenario.message
        ? currentScenario.message
        : 'Projection indisponible pour le moment.';
    }

    if (goalProgress && typeof goalProgress.progress_percent !== 'undefined') {
      const progressPercent = Math.max(0, Math.min(100, Number(goalProgress.progress_percent)));

      if (progressWrap) {
        progressWrap.hidden = false;
      }

      if (progressBar) {
        progressBar.style.width = progressPercent + '%';
      }

      if (progressText) {
        progressText.textContent = progressPercent + '%';
      }

      if (progressWeights) {
        progressWeights.textContent = (goalProgress.current_weight ?? '--') + ' kg / cible ' + (goalProgress.target_weight ?? '--') + ' kg';
      }

      if (progressNote) {
        progressNote.textContent = 'Progression estimee selon les donnees de poids disponibles.';
      }
    } else {
      if (progressWrap) {
        progressWrap.hidden = true;
      }

      if (progressNote) {
        progressNote.textContent = 'Progression vers l\'objectif indisponible avec les donnees actuelles.';
      }
    }

    if (confidenceData && confidenceData.label) {
      const badge = document.getElementById('predictionConfidenceBadge');

      if (badge) {
        badge.className = getConfidenceBadgeClass(confidenceData.label);
        badge.textContent = confidenceData.label;
      }
    }
  }

  function renderScenarios(scenarios) {
    const container = document.getElementById('predictionScenarioCards');

    if (!container) {
      return;
    }

    if (!Array.isArray(scenarios) || scenarios.length === 0) {
      container.innerHTML = '<article class="prediction-card prediction-card-scenario"><p class="prediction-muted">Aucun scenario disponible.</p></article>';
      return;
    }

    container.innerHTML = scenarios.map(function (scenario) {
      const confidence = typeof scenario.confidence !== 'undefined'
        ? Number(scenario.confidence).toFixed(3)
        : '--';

      return [
        '<article class="prediction-card prediction-card-scenario">',
        '<div class="prediction-card__head">',
        '<div>',
        '<p class="prediction-card__label">' + escapeHtml(scenario.label || 'Scenario') + '</p>',
        '<h3>' + escapeHtml(formatDate(scenario.predicted_goal_date)) + '</h3>',
        '</div>',
        '<span class="prediction-badge prediction-badge-soft">' + escapeHtml(confidence) + '</span>',
        '</div>',
        '<p class="prediction-scenario-delta">' + escapeHtml(formatNumber(scenario.weekly_delta, 'kcal / semaine')) + '</p>',
        '<p class="prediction-muted">' + escapeHtml(scenario.message || '') + '</p>',
        '</article>'
      ].join('');
    }).join('');
  }

  function renderTrend(trendData) {
    const tableBody = document.getElementById('predictionWeeklyTable');
    const message = document.getElementById('predictionTrendMessage');
    const direction = document.getElementById('predictionTrendDirection');
    const slope = document.getElementById('predictionTrendSlope');
    const weeklyRows = Array.isArray(trendData.weekly) ? trendData.weekly : [];

    if (tableBody) {
      tableBody.innerHTML = weeklyRows.length
        ? weeklyRows.map(function (row) {
            return [
              '<tr>',
              '<td>Semaine ' + escapeHtml(row.week) + '</td>',
              '<td>' + escapeHtml(formatNumber(row.avg_calories, 'kcal')) + '</td>',
              '<td>' + escapeHtml(formatNumber(row.avg_delta, 'kcal')) + '</td>',
              '<td>' + escapeHtml(row.logged_days) + '</td>',
              '</tr>'
            ].join('');
          }).join('')
        : '<tr><td colspan="4">Aucune tendance disponible.</td></tr>';
    }

    if (message) {
      message.textContent = trendData.trend && trendData.trend.message
        ? trendData.trend.message
        : 'Pas assez de donnees pour calculer une tendance.';
    }

    if (direction) {
      direction.textContent = trendData.trend && trendData.trend.direction
        ? trendData.trend.direction
        : '--';
      direction.className = 'prediction-badge ' + resolveTrendBadgeClass(trendData.trend ? trendData.trend.direction : '');
    }

    if (slope) {
      slope.textContent = 'Slope ' + (trendData.trend && typeof trendData.trend.slope !== 'undefined'
        ? Number(trendData.trend.slope).toFixed(1)
        : '--');
    }
  }

  function resolveTrendBadgeClass(direction) {
    if (direction === 'improving') {
      return 'prediction-badge-reliable';
    }

    if (direction === 'degrading') {
      return 'prediction-badge-insufficient';
    }

    if (direction === 'stable') {
      return 'prediction-badge-indicative';
    }

    return 'prediction-badge-neutral';
  }

  function renderLowDataBanner(loggedDays, confidence) {
    if (!lowDataBox) {
      return;
    }

    if ((loggedDays || 0) < 7 || Number(confidence || 0) < 0.50) {
      lowDataBox.hidden = false;
      lowDataBox.textContent = 'Continuez a enregistrer vos repas pour obtenir une projection plus fiable.';
      return;
    }

    lowDataBox.hidden = true;
    lowDataBox.textContent = '';
  }

  function renderWhatIfResult(payload) {
    const container = document.getElementById('predictionWhatIfResult');

    if (!container) {
      return;
    }

    const impact = payload && payload.impact ? payload.impact : {};
    const input = payload && payload.input ? payload.input : {};

    container.innerHTML = [
      '<div class="prediction-simulation__grid">',
      '<div class="prediction-mini-stat">',
      '<span>Entree</span>',
      '<strong>' + escapeHtml(formatNumber(input.daily_calorie_change || 0, 'kcal / jour')) + '</strong>',
      '</div>',
      '<div class="prediction-mini-stat">',
      '<span>Date actuelle</span>',
      '<strong>' + escapeHtml(formatDate(payload.baseline_goal_date)) + '</strong>',
      '</div>',
      '<div class="prediction-mini-stat">',
      '<span>Nouvelle date</span>',
      '<strong>' + escapeHtml(formatDate(impact.new_goal_date)) + '</strong>',
      '</div>',
      '<div class="prediction-mini-stat">',
      '<span>Gain / perte</span>',
      '<strong>' + escapeHtml(impact.gain_days === null || typeof impact.gain_days === 'undefined' ? 'Non calculable' : impact.gain_days + ' jours') + '</strong>',
      '</div>',
      '</div>',
      '<p class="prediction-simulation__message">' + escapeHtml(impact.message || 'Simulation terminee.') + '</p>'
    ].join('');
  }

  async function fetchJson(url, options) {
    const response = await fetch(url, options || {
      headers: {
        'Accept': 'application/json'
      }
    });
    const payload = await response.json();

    if (!response.ok || payload.error) {
      throw new Error(payload.error || 'Erreur API');
    }

    return payload.data || null;
  }

  async function loadDashboard() {
    setError('');
    setInfo('Chargement de la projection nutritionnelle...');

    try {
      const results = await Promise.all([
        fetchJson(urls.scenarios),
        fetchJson(urls.trend),
        fetchJson(urls.confidence)
      ]);
      const scenariosData = results[0] || {};
      const trendData = results[1] || {};
      const confidenceData = results[2] || {};

      renderConfidence(confidenceData);
      renderSummary(scenariosData, confidenceData);
      renderScenarios(scenariosData.scenarios || []);
      renderTrend(trendData);
      renderLowDataBanner(confidenceData.logged_days, confidenceData.confidence);
      setDisclaimer(scenariosData.disclaimer || trendData.disclaimer || confidenceData.disclaimer || '');
      setUpdatedAt();
      setInfo('');
    } catch (error) {
      setInfo('');
      setError(error && error.message ? error.message : 'Impossible de charger la projection nutritionnelle.');
    }
  }

  if (whatIfForm) {
    whatIfForm.addEventListener('submit', async function (event) {
      event.preventDefault();
      setError('');

      if (whatIfButton) {
        whatIfButton.disabled = true;
        whatIfButton.textContent = 'Simulation...';
      }

      try {
        const input = document.getElementById('predictionCalorieChange');
        const value = input ? Number(input.value) : 0;
        const payload = await fetchJson(urls.whatIf, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            daily_calorie_change: value
          })
        });

        renderWhatIfResult(payload || {});
      } catch (error) {
        setError(error && error.message ? error.message : 'Impossible de lancer la simulation.');
      } finally {
        if (whatIfButton) {
          whatIfButton.disabled = false;
          whatIfButton.textContent = 'Simuler';
        }
      }
    });
  }

  loadDashboard();
});
