document.addEventListener('DOMContentLoaded', function () {
  const root = document.querySelector('[data-nutrition-dashboard]');

  if (!root) {
    return;
  }

  const summaryUrl = root.dataset.summaryUrl || 'index.php?action=nutrition_dashboard_summary';
  const healthUrl = root.dataset.healthUrl || 'index.php?action=nutrition_health_score';
  const recommendationsUrl = root.dataset.recommendationsUrl || 'index.php?action=nutrition_daily_recommendations';
  const weeklyUrl = root.dataset.weeklyUrl || 'index.php?action=nutrition_weekly_analysis';
  const infoBox = document.getElementById('nutritionDashboardInfo');
  const errorBox = document.getElementById('nutritionDashboardError');
  const refreshButton = document.getElementById('nutritionRefresh');

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

  function renderText(id, value, fallback) {
    const element = document.getElementById(id);

    if (!element) {
      return;
    }

    element.textContent = value || fallback || '--';
  }

  function renderHydrationCard(hydration) {
    const amountEl = document.getElementById('nutritionHydrationAmount');
    const glassesEl = document.getElementById('nutritionHydrationGlasses');
    const progressTextEl = document.getElementById('nutritionHydrationProgressText');
    const progressBarEl = document.getElementById('nutritionHydrationProgressBar');
    const totalMl = Number(hydration && hydration.total_ml ? hydration.total_ml : 0);
    const targetMl = Number(hydration && hydration.target_ml ? hydration.target_ml : 2000);
    const progress = Number(hydration && hydration.progress ? hydration.progress : 0);
    const glasses = Number(hydration && hydration.glasses ? hydration.glasses : 0);

    if (amountEl) {
      amountEl.textContent = totalMl + ' / ' + targetMl + ' ml';
    }

    if (glassesEl) {
      glassesEl.textContent = glasses + (glasses > 1 ? ' verres' : ' verre');
    }

    if (progressTextEl) {
      progressTextEl.textContent = progress + '%';
    }

    if (progressBarEl) {
      progressBarEl.style.width = Math.max(0, Math.min(100, progress)) + '%';
    }
  }

  function badgeClass(priority) {
    if (priority === 'high') {
      return 'nutrition-badge nutrition-badge-high';
    }

    if (priority === 'medium') {
      return 'nutrition-badge nutrition-badge-medium';
    }

    return 'nutrition-badge nutrition-badge-low';
  }

  function buildRecommendationMarkup(item) {
    return [
      '<div class="nutrition-item">',
      '<span class="nutrition-item-title">' + (item.title || 'Recommendation') + '</span>',
      '<div>' + (item.message || '') + '</div>',
      '<div class="nutrition-item-meta">',
      '<span class="' + badgeClass(item.priority || 'low') + '">' + (item.priority || 'low') + '</span>',
      ' - ',
      (item.action || 'Aucune action'),
      '</div>',
      '</div>'
    ].join('');
  }

  function buildWeeklyMarkup(weekly) {
    const strengths = Array.isArray(weekly.strengths) ? weekly.strengths : [];
    const improvements = Array.isArray(weekly.improvements) ? weekly.improvements : [];
    const parts = [];

    parts.push('<div class="nutrition-item">');
    parts.push('<span class="nutrition-item-title">Resume</span>');
    parts.push('<div>' + (weekly.summary || 'Aucune analyse disponible.') + '</div>');
    parts.push('<div class="nutrition-item-meta">');
    parts.push('Jours logges : ' + (weekly.logged_days ?? 0) + ' / ' + (weekly.period_days ?? 7));
    parts.push(' - Calories moyennes : ' + (weekly.average_calories ?? 0));
    parts.push(' - Proteines moyennes : ' + (weekly.average_protein ?? 0) + ' g');
    parts.push('</div>');
    parts.push('</div>');

    strengths.forEach(function (text) {
      parts.push('<div class="nutrition-item"><span class="nutrition-item-title">Point fort</span><div>' + text + '</div></div>');
    });

    improvements.forEach(function (text) {
      parts.push('<div class="nutrition-item"><span class="nutrition-item-title">A ameliorer</span><div>' + text + '</div></div>');
    });

    return parts.join('');
  }

  function buildFreshUrl(url, forceFresh) {
    if (!forceFresh) {
      return url;
    }

    const freshUrl = new URL(url, window.location.href);
    freshUrl.searchParams.set('refresh', Date.now().toString());
    return freshUrl.toString();
  }

  async function fetchJson(url, forceFresh) {
    const response = await fetch(buildFreshUrl(url, forceFresh), {
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

  async function loadDashboard(forceFresh) {
    setError('');
    setInfo('Chargement du dashboard nutrition...');

    try {
      const [summary, health, recommendations, weekly] = await Promise.all([
        fetchJson(summaryUrl, forceFresh),
        fetchJson(healthUrl, forceFresh),
        fetchJson(recommendationsUrl, forceFresh),
        fetchJson(weeklyUrl, forceFresh)
      ]);

      const today = summary && summary.today ? summary.today : {};
      const score = health && typeof health.score !== 'undefined'
        ? health
        : (summary && summary.health_score ? summary.health_score : {});
      const weeklyData = weekly || (summary && summary.weekly_analysis ? summary.weekly_analysis : {});
      const recs = Array.isArray(recommendations)
        ? recommendations
        : (summary && Array.isArray(summary.daily_recommendations) ? summary.daily_recommendations : []);
      const hydration = summary && summary.hydration ? summary.hydration : {
        total_ml: Number(today.water_ml || 0),
        target_ml: Number(today.hydration_target_ml || 2000),
        progress: Math.min(100, Math.round((Number(today.water_ml || 0) / Math.max(1, Number(today.hydration_target_ml || 2000))) * 100)),
        glasses: Math.round((Number(today.water_ml || 0) / 250) * 10) / 10
      };
      const mealCount = Number(today.meal_count || 0);
      let dayHeading = 'Aucune saisie';
      let daySummary = 'Aucun repas n a encore ete enregistre aujourd hui.';

      if (mealCount > 0) {
        dayHeading = mealCount + (mealCount > 1 ? ' enregistrements' : ' enregistrement');
        daySummary = 'Aujourd hui, ' + mealCount + (mealCount > 1 ? ' apports ont ete enregistres. ' : ' apport a ete enregistre. ');

        if (score && score.summary) {
          daySummary += score.summary;
        } else {
          daySummary += 'Le suivi du jour est disponible ci-dessous.';
        }
      }

      renderText('nutritionHealthScore', typeof score.score !== 'undefined' ? String(score.score) + '/100' : '--');
      renderText('nutritionHealthSummary', score.summary || 'Aucun score disponible.', 'Aucun score disponible.');
      renderText('nutritionDayHeading', dayHeading);
      renderText('nutritionDaySummary', daySummary);
      renderHydrationCard(hydration);

      const recommendationsBox = document.getElementById('nutritionRecommendations');

      if (recommendationsBox) {
        recommendationsBox.innerHTML = recs.length
          ? recs.map(buildRecommendationMarkup).join('')
          : '<p class="nutrition-empty">Aucune recommandation pour le moment.</p>';
      }

      const weeklyBox = document.getElementById('nutritionWeeklyDetails');

      if (weeklyBox) {
        weeklyBox.innerHTML = weeklyData && Object.keys(weeklyData).length
          ? buildWeeklyMarkup(weeklyData)
          : '<p class="nutrition-empty">Aucune analyse disponible.</p>';
      }

      setInfo('');
    } catch (error) {
      setInfo('');
      setError(error && error.message ? error.message : 'Impossible de charger le dashboard nutrition.');
    }
  }

  if (refreshButton) {
    refreshButton.addEventListener('click', function () {
      loadDashboard(true);
    });
  }

  loadDashboard(false);
});
