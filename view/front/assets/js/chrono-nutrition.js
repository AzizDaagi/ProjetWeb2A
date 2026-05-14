document.addEventListener("DOMContentLoaded", () => {
    const APP_BASE_URL = "/Web";
    const form = document.getElementById("chrono-form");
    const saveButton = document.getElementById("saveProfile");
    const feedback = document.getElementById("chrono-feedback");
    const disclaimer = document.getElementById("chrono-disclaimer");
    const blocks = {
        summary: document.getElementById("block-summary"),
        timing: document.getElementById("block-timing"),
        personalization: document.getElementById("block-personalization"),
        fasting: document.getElementById("block-fasting"),
        nutrients: document.getElementById("block-nutrients"),
        sleep: document.getElementById("block-sleep")
    };

    const actionMap = {
        timing: "chrono_optimal_timing",
        fasting: "chrono_fasting_window",
        nutrients: "chrono_nutrient_timing",
        sleep: "chrono_sleep_sync"
    };

    loadProfile();

    form.addEventListener("submit", async (event) => {
        event.preventDefault();

        setSavingState(true);
        hideFeedback();

        const payload = {
            chronotype: form.chronotype.value,
            wake_time: form.wake_time.value,
            sleep_time: form.sleep_time.value,
            sleep_quality: form.sleep_quality.value,
            energy_peak: form.energy_peak.value,
            energy_dip: form.energy_dip.value,
            workout_time: form.workout_time.value,
            last_caffeine_time: form.last_caffeine_time.value,
            preferred_meals_count: form.preferred_meals_count.value
        };

        try {
            const response = await fetchJson(`${APP_BASE_URL}/index.php?action=chrono_profile_save`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });

            if (response.error) {
                throw new Error(response.error);
            }

            if (response.data && response.data.profile) {
                applyProfileToForm(response.data.profile);
            }

            showFeedback(
                (response.data && response.data.message) || "Profil chrono enregistre avec succes.",
                "success"
            );

            await loadResults();
        } catch (error) {
            showFeedback(error.message || "Erreur lors de la sauvegarde du profil chrono.", "error");
        } finally {
            setSavingState(false);
        }
    });

    async function loadProfile() {
        renderLoadingState(blocks.summary, "Resume chrono");
        renderLoadingState(blocks.timing, "Horaires recommandes");
        renderLoadingState(blocks.personalization, "Personnalisation chrono");
        renderLoadingState(blocks.fasting, "JeÃ»ne intermittent");
        renderLoadingState(blocks.nutrients, "Nutriments par moment");
        renderLoadingState(blocks.sleep, "Synchronisation sommeil");
        setDisclaimer("");

        try {
            const response = await fetchJson(`${APP_BASE_URL}/index.php?action=chrono_profile_get`);

            if (response.error) {
                throw new Error(response.error);
            }

            if (response.data) {
                applyProfileToForm(response.data);
                await loadResults();
                return;
            }

            renderEmptyModule("Sauvegarde ton profil chrono pour afficher des recommandations adaptees a ton rythme.");
        } catch (error) {
            renderModuleError(error.message || "Erreur lors du chargement du profil chrono.");
        }
    }

    async function loadResults() {
        renderLoadingState(blocks.summary, "Resume chrono");
        renderLoadingState(blocks.timing, "Horaires recommandes");
        renderLoadingState(blocks.personalization, "Personnalisation chrono");
        renderLoadingState(blocks.fasting, "JeÃ»ne intermittent");
        renderLoadingState(blocks.nutrients, "Nutriments par moment");
        renderLoadingState(blocks.sleep, "Synchronisation sommeil");
        setDisclaimer("");

        const timingPromise = fetchJson(`${APP_BASE_URL}/index.php?action=${actionMap.timing}`)
            .then((response) => {
                if (response.error || !response.data) {
                    throw new Error(response.error || "Aucune recommandation d horaires disponible.");
                }

                renderSummary(response.data.summary);
                renderTiming(response.data.meals || []);
                renderPersonalization(response.data.personalization);
            })
            .catch((error) => {
                renderErrorState(blocks.summary, "Resume chrono", error.message || "Impossible de charger le resume chrono.");
                renderErrorState(blocks.timing, "Horaires recommandes", error.message || "Impossible de charger les horaires recommandes.");
                renderErrorState(blocks.personalization, "Personnalisation chrono", error.message || "Impossible de charger la personnalisation chrono.");
            });

        const fastingPromise = fetchJson(`${APP_BASE_URL}/index.php?action=${actionMap.fasting}`)
            .then((response) => {
                if (response.error || !response.data) {
                    throw new Error(response.error || "Aucune indication de jeÃ»ne intermittent disponible.");
                }

                renderFasting(response.data);
                setDisclaimer(response.data.disclaimer || "");
            })
            .catch((error) => {
                renderErrorState(blocks.fasting, "JeÃ»ne intermittent", error.message || "Impossible de charger le jeÃ»ne intermittent.");
                setDisclaimer("");
            });

        const nutrientPromise = fetchJson(`${APP_BASE_URL}/index.php?action=${actionMap.nutrients}`)
            .then((response) => {
                if (response.error || !response.data) {
                    throw new Error(response.error || "Aucun conseil nutritionnel disponible.");
                }

                renderNutrients(response.data.periods || []);
            })
            .catch((error) => {
                renderErrorState(blocks.nutrients, "Nutriments par moment", error.message || "Impossible de charger les conseils nutritionnels.");
            });

        const sleepPromise = fetchJson(`${APP_BASE_URL}/index.php?action=${actionMap.sleep}`)
            .then((response) => {
                if (response.error || !response.data) {
                    throw new Error(response.error || "Aucune recommandation sommeil disponible.");
                }

                renderSleep(response.data);
            })
            .catch((error) => {
                renderErrorState(blocks.sleep, "Synchronisation sommeil", error.message || "Impossible de charger les recommandations sommeil.");
            });

        await Promise.allSettled([timingPromise, fastingPromise, nutrientPromise, sleepPromise]);
    }

    function renderSummary(summary) {
        if (!summary) {
            renderEmptyState(blocks.summary, "Resume chrono", "Aucune information de profil chrono n est disponible pour le moment.");
            return;
        }

        blocks.summary.innerHTML = `
            <div class="chrono-panel">
                <div class="chrono-section-head">
                    <div>
                        <h2 class="chrono-panel__title">Resume chrono</h2>
                        <p class="chrono-panel__intro">${escapeHtml(summary.message || "")}</p>
                    </div>
                    <span class="chrono-highlight">${escapeHtml(summary.chronotype_label || "Profil")}</span>
                </div>
                <div class="chrono-summary-list">
                    <div class="chrono-meta-item">
                        <small>Chronotype</small>
                        <strong>${escapeHtml(summary.chronotype_label || "--")}</strong>
                    </div>
                    <div class="chrono-meta-item">
                        <small>Heure de reveil</small>
                        <strong>${escapeHtml(summary.wake_time || "--:--")}</strong>
                    </div>
                    <div class="chrono-meta-item">
                        <small>Heure de coucher</small>
                        <strong>${escapeHtml(summary.sleep_time || "--:--")}</strong>
                    </div>
                    <div class="chrono-meta-item">
                        <small>Sommeil estime</small>
                        <strong>${escapeHtml(formatDuration(summary.sleep_duration_h))}</strong>
                        <p class="chrono-meta-note">${escapeHtml(summary.sleep_duration_label || "")}</p>
                    </div>
                    <div class="chrono-meta-item">
                        <small>Repas souhaites</small>
                        <strong>${escapeHtml(summary.preferred_meals_count_label || "--")}</strong>
                    </div>
                </div>
            </div>
        `;
    }

    function renderTiming(meals) {
        if (!Array.isArray(meals) || meals.length === 0) {
            renderEmptyState(blocks.timing, "Horaires recommandes", "Aucun horaire recommande n est disponible pour ce profil.");
            return;
        }

        blocks.timing.innerHTML = `
            <div class="chrono-panel">
                <h2 class="chrono-panel__title">Horaires recommandes</h2>
                <p class="chrono-panel__intro">Chaque plage indique un repere de rythme alimentaire, pas une heure obligatoire a suivre minute par minute.</p>
                <div class="chrono-meal-grid">
                    ${meals.map((meal) => `
                        <article class="chrono-subcard">
                            <span class="chrono-badge ${periodBadgeClass(meal.key)}">
                                ${escapeHtml(periodLabel(meal.key))}
                            </span>
                            <h3>${escapeHtml(meal.label || "--")}</h3>
                            <span class="chrono-time">${escapeHtml(meal.start || "--:--")} - ${escapeHtml(meal.end || "--:--")}</span>
                            <p>${escapeHtml(meal.message || "")}</p>
                        </article>
                    `).join("")}
                </div>
            </div>
        `;
    }

    function renderPersonalization(data) {
        if (!data || !Array.isArray(data.recommendations) || data.recommendations.length === 0) {
            renderEmptyState(blocks.personalization, "Personnalisation chrono", "Aucune personnalisation chrono n est disponible pour le moment.");
            return;
        }

        const badges = Array.isArray(data.badges) ? data.badges : [];

        blocks.personalization.innerHTML = `
            <div class="chrono-panel">
                <h2 class="chrono-panel__title">${escapeHtml(data.title || "Personnalisation chrono")}</h2>
                <p class="chrono-panel__intro">${escapeHtml(data.intro || "")}</p>
                ${badges.length > 0 ? `
                    <div class="chrono-badges">
                        ${badges.map((badge) => `
                            <span class="chrono-badge chrono-badge--soft">
                                ${escapeHtml(`${badge.label || ""} : ${badge.value || ""}`)}
                            </span>
                        `).join("")}
                    </div>
                ` : ""}
                <div class="chrono-advice-list">
                    ${data.recommendations.map((recommendation) => `
                        <article class="chrono-subcard">
                            <span class="chrono-badge ${priorityBadgeClass(recommendation.priority)}">
                                ${escapeHtml(recommendation.priority_label || "Priorite moyenne")}
                            </span>
                            <h3>${escapeHtml(recommendation.title || "--")}</h3>
                            <p>${escapeHtml(recommendation.description || "")}</p>
                        </article>
                    `).join("")}
                </div>
            </div>
        `;
    }

    function renderFasting(data) {
        console.log('[Chrono fasting data]', data);

        blocks.fasting.innerHTML = `
            <div class="chrono-panel">
                <div class="chrono-section-head">
                    <div>
                        <h2 class="chrono-panel__title">JeÃ»ne intermittent</h2>
                        <p class="chrono-panel__intro">${escapeHtml(data.message || "Cette fenetre indique la periode de jeune estimee entre le dernier repas de la journee et le premier repas du lendemain.")}</p>
                    </div>
                    <span class="chrono-badge chrono-badge--protocol">Protocole ${escapeHtml(data.protocol || "12/12")}</span>
                </div>
                <div class="chrono-fasting-grid">
                    <article class="chrono-subcard chrono-subcard--window">
                        <span class="chrono-badge chrono-badge--period-noon">FenÃªtre alimentaire</span>
                        <h3>${escapeHtml(data.eating_start || "--:--")} &rarr; ${escapeHtml(data.eating_end || "--:--")}</h3>
                        <p>DurÃ©e alimentaire : ${escapeHtml(formatDuration(data.eating_duration_h))}</p>
                    </article>
                    <article class="chrono-subcard chrono-subcard--window">
                        <span class="chrono-badge chrono-badge--period-evening">FenÃªtre de jeÃ»ne</span>
                        <h3>${escapeHtml(data.fast_start || "--:--")} &rarr; ${escapeHtml(data.fast_end || "--:--")}</h3>
                        <p>DurÃ©e du jeÃ»ne : ${escapeHtml(formatDuration(data.fast_duration_h))}</p>
                    </article>
                </div>
                <div class="chrono-inline-list">
                    <div class="chrono-row">
                        <span>Type recommandÃ©</span>
                        <strong>${escapeHtml(data.protocol || "12/12")}</strong>
                    </div>
                    <div class="chrono-row">
                        <span>FenÃªtre alimentaire</span>
                        <strong>${escapeHtml(data.eating_start || "--:--")} - ${escapeHtml(data.eating_end || "--:--")}</strong>
                    </div>
                    <div class="chrono-row">
                        <span>DurÃ©e alimentaire</span>
                        <strong>${escapeHtml(formatDuration(data.eating_duration_h))}</strong>
                    </div>
                    <div class="chrono-row">
                        <span>FenÃªtre de jeÃ»ne</span>
                        <strong>${escapeHtml(data.fast_start || "--:--")} - ${escapeHtml(data.fast_end || "--:--")}</strong>
                    </div>
                    <div class="chrono-row">
                        <span>DurÃ©e du jeÃ»ne</span>
                        <strong>${escapeHtml(formatDuration(data.fast_duration_h))}</strong>
                    </div>
                </div>
                ${data.disclaimer ? `<p class="chrono-disclaimer-inline">${escapeHtml(data.disclaimer)}</p>` : ""}
            </div>
        `;
    }

    function renderNutrients(periods) {
        if (!Array.isArray(periods) || periods.length === 0) {
            renderEmptyState(blocks.nutrients, "Nutriments par moment", "Aucun conseil de repartition nutritionnelle n est disponible.");
            return;
        }

        blocks.nutrients.innerHTML = `
            <div class="chrono-panel">
                <h2 class="chrono-panel__title">Nutriments selon le moment</h2>
                <p class="chrono-panel__intro">Les priorites changent legerement entre matin, midi et soir pour garder une lecture plus pratique.</p>
                <div class="chrono-nutrient-grid">
                    ${periods.map((period) => `
                        <article class="chrono-subcard">
                            <span class="chrono-badge ${periodBadgeClass(period.key)}">
                                ${escapeHtml(period.label || "--")}
                            </span>
                            <h3>${escapeHtml(period.label || "--")}</h3>
                            <div class="chrono-badges">
                                ${(Array.isArray(period.nutrients) ? period.nutrients : []).map((nutrient) => `
                                    <span class="chrono-badge">${escapeHtml(nutrient)}</span>
                                `).join("")}
                            </div>
                            <p>${escapeHtml(period.tip || "")}</p>
                        </article>
                    `).join("")}
                </div>
            </div>
        `;
    }

    function renderSleep(data) {
        const recommendations = Array.isArray(data.recommendations) ? data.recommendations : [];

        if (recommendations.length === 0) {
            renderEmptyState(blocks.sleep, "Synchronisation sommeil", "Aucune recommandation sommeil n est disponible pour le moment.");
            return;
        }

        blocks.sleep.innerHTML = `
            <div class="chrono-panel">
                <div class="chrono-section-head">
                    <div>
                        <h2 class="chrono-panel__title">Synchronisation sommeil</h2>
                        <p class="chrono-panel__intro">${escapeHtml(data.summary || "")}</p>
                    </div>
                    <span class="chrono-highlight">Sommeil ${escapeHtml(data.sleep_quality_label || "--")}</span>
                </div>
                <div class="chrono-sleep-grid">
                    ${recommendations.map((recommendation) => `
                        <article class="chrono-subcard">
                            <span class="chrono-badge ${priorityBadgeClass(recommendation.priority)}">
                                ${escapeHtml(recommendation.priority_label || "Priorite moyenne")}
                            </span>
                            <h3>${escapeHtml(recommendation.title || "--")}</h3>
                            <p>${escapeHtml(recommendation.description || "")}</p>
                        </article>
                    `).join("")}
                </div>
            </div>
        `;
    }

    function renderLoadingState(block, title) {
        block.innerHTML = `
            <div class="chrono-state">
                <div class="chrono-state__content">
                    <div class="spinner"></div>
                    <h3>${escapeHtml(title)}</h3>
                    <p>Chargement des donnees...</p>
                </div>
            </div>
        `;
    }

    function renderEmptyState(block, title, message) {
        block.innerHTML = `
            <div class="chrono-state">
                <div class="chrono-state__content">
                    <h3>${escapeHtml(title)}</h3>
                    <p>${escapeHtml(message)}</p>
                </div>
            </div>
        `;
    }

    function renderErrorState(block, title, message) {
        block.innerHTML = `
            <div class="chrono-state">
                <div class="chrono-state__content">
                    <h3>${escapeHtml(title)}</h3>
                    <p class="error">${escapeHtml(message)}</p>
                </div>
            </div>
        `;
    }

    function renderEmptyModule(message) {
        renderEmptyState(blocks.summary, "Resume chrono", message);
        renderEmptyState(blocks.timing, "Horaires recommandes", message);
        renderEmptyState(blocks.personalization, "Personnalisation chrono", message);
        renderEmptyState(blocks.fasting, "JeÃ»ne intermittent", message);
        renderEmptyState(blocks.nutrients, "Nutriments par moment", message);
        renderEmptyState(blocks.sleep, "Synchronisation sommeil", message);
        setDisclaimer("");
    }

    function renderModuleError(message) {
        renderErrorState(blocks.summary, "Resume chrono", message);
        renderErrorState(blocks.timing, "Horaires recommandes", message);
        renderErrorState(blocks.personalization, "Personnalisation chrono", message);
        renderErrorState(blocks.fasting, "JeÃ»ne intermittent", message);
        renderErrorState(blocks.nutrients, "Nutriments par moment", message);
        renderErrorState(blocks.sleep, "Synchronisation sommeil", message);
        setDisclaimer("");
    }

    function applyProfileToForm(profile) {
        form.chronotype.value = profile.chronotype || "standard";
        form.wake_time.value = profile.wake_time || "07:00";
        form.sleep_time.value = profile.sleep_time || "23:00";
        form.sleep_quality.value = profile.sleep_quality || "moyenne";
        form.energy_peak.value = profile.energy_peak || "";
        form.energy_dip.value = profile.energy_dip || "aucun";
        form.workout_time.value = profile.workout_time || "aucun";
        form.last_caffeine_time.value = profile.last_caffeine_time || "aucun";
        form.preferred_meals_count.value = String(profile.preferred_meals_count || 3);
    }

    function setSavingState(isSaving) {
        saveButton.disabled = isSaving;
        saveButton.textContent = isSaving ? "Sauvegarde..." : "Sauvegarder mon profil";
    }

    function showFeedback(message, tone) {
        feedback.hidden = false;
        feedback.className = `chrono-feedback is-${tone}`;
        feedback.textContent = message;
    }

    function hideFeedback() {
        feedback.hidden = true;
        feedback.className = "chrono-feedback";
        feedback.textContent = "";
    }

    function setDisclaimer(message) {
        if (!message) {
            disclaimer.hidden = true;
            disclaimer.textContent = "";
            return;
        }

        disclaimer.hidden = false;
        disclaimer.textContent = message;
    }

    function priorityBadgeClass(priority) {
        if (priority === "high") {
            return "chrono-badge--priority-high";
        }

        if (priority === "low") {
            return "chrono-badge--priority-low";
        }

        return "chrono-badge--priority-medium";
    }

    function periodBadgeClass(key) {
        if (key === "breakfast" || key === "morning") {
            return "chrono-badge--period-morning";
        }

        if (key === "lunch" || key === "noon" || key === "first_main") {
            return "chrono-badge--period-noon";
        }

        if (key === "dinner" || key === "evening") {
            return "chrono-badge--period-evening";
        }

        return "chrono-badge--period-neutral";
    }

    function periodLabel(key) {
        if (key === "breakfast" || key === "morning") {
            return "Matin";
        }

        if (key === "lunch" || key === "noon" || key === "first_main") {
            return "Midi";
        }

        if (key === "dinner" || key === "evening") {
            return "Soir";
        }

        return "Repere";
    }

    function formatDuration(duration) {
        if (duration === null || duration === undefined || duration === "") {
            return "--";
        }

        const parsedDuration = Number(duration);

        if (Number.isNaN(parsedDuration)) {
            return String(duration);
        }

        const totalMinutes = Math.round(parsedDuration * 60);
        const hours = Math.floor(totalMinutes / 60);
        const minutes = totalMinutes % 60;

        if (minutes === 0) {
            return `${hours} h`;
        }

        return `${hours} h ${minutes}`;
    }

    async function fetchJson(url, options = {}) {
        const response = await fetch(url, {
            credentials: "same-origin",
            headers: {
                "Accept": "application/json",
                ...(options.headers || {})
            },
            ...options
        });

        const contentType = response.headers.get("content-type") || "";
        const text = await response.text();

        if (!response.ok) {
            throw new Error("Le service chrono-nutrition ne rÃ©pond pas correctement.");
        }

        if (!contentType.includes("application/json")) {
            console.error("RÃ©ponse non JSON reÃ§ue:", text);
            throw new Error("Le serveur a retournÃ© une page HTML au lieu du JSON. VÃ©rifie la session, la route ou une erreur PHP.");
        }

        try {
            return JSON.parse(text);
        } catch (error) {
            console.error("JSON invalide reÃ§u:", text);
            throw new Error("RÃ©ponse JSON invalide du service chrono-nutrition.");
        }
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }
});
