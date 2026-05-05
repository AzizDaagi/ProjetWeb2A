document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("chrono-form");
    const results = {
        timing: document.getElementById("block-timing"),
        fasting: document.getElementById("block-fasting"),
        nutrients: document.getElementById("block-nutrients"),
        sleep: document.getElementById("block-sleep")
    };

    // Fetch profile on load
    fetch("index.php?action=chrono_profile_get")
        .then(response => response.json())
        .then(response => {
            if (response.data) {
                form.chronotype.value = response.data.chronotype;
                form.wake_time.value = response.data.wake_time;
                form.sleep_time.value = response.data.sleep_time;
                form.sleep_quality.value = response.data.sleep_quality;
                loadResults();
            } else {
                Object.values(results).forEach(block => {
                    block.innerHTML = "<p>Sauvegardez d'abord votre profil</p>";
                });
            }
        })
        .catch(() => {
            Object.values(results).forEach(block => {
                block.innerHTML = "<p>Erreur lors du chargement du profil</p>";
            });
        });

    // Save profile on submit
    form.addEventListener("submit", (e) => {
        e.preventDefault();
        const payload = {
            chronotype: form.chronotype.value,
            wake_time: form.wake_time.value,
            sleep_time: form.sleep_time.value,
            sleep_quality: form.sleep_quality.value
        };

        fetch("index.php?action=chrono_profile_save", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        })
            .then(response => response.json())
            .then(() => loadResults())
            .catch(() => {
                alert("Erreur lors de la sauvegarde du profil");
            });
    });

   // Mapping clé → action réelle
const actionMap = {
    timing:    'chrono_optimal_timing',
    fasting:   'chrono_fasting_window',
    nutrients: 'chrono_nutrient_timing',
    sleep:     'chrono_sleep_sync'
};

function loadResults() {
    Object.keys(results).forEach(key => {
        const block   = results[key];
        const action  = actionMap[key]; // ← utiliser le bon nom d'action
        block.innerHTML = "<div class='spinner'></div>";

        fetch(`index.php?action=${action}`) // ← plus chrono_${key}
            .then(response => response.json())
            .then(response => {
                if (response.error) {
                    block.innerHTML = `<p class='error'>${response.error}</p>`;
                    return;
                }
                const data = response.data;
                if (!data) {
                    block.innerHTML = "<p>Aucune donnée disponible</p>";
                    return;
                }
                switch (key) {
                    case "timing":    block.innerHTML = renderTiming(data);    break;
                    case "fasting":   block.innerHTML = renderFasting(data);   break;
                    case "nutrients": block.innerHTML = renderNutrients(data); break;
                    case "sleep":     block.innerHTML = renderSleep(data);     break;
                }
            })
            .catch(() => {
                block.innerHTML = "<p>Erreur lors du chargement des données</p>";
            })
            .finally(() => {
                const spinner = block.querySelector(".spinner");
                if (spinner) spinner.remove();
            });
    });
}

    function renderTiming(data) {
        return `
            <div class="meal-slot">
                <span class="meal-label">Petit-déjeuner</span>
                <span class="meal-time">${data.breakfast.start} – ${data.breakfast.end}</span>
            </div>
            <div class="meal-slot">
                <span class="meal-label">Déjeuner</span>
                <span class="meal-time">${data.lunch.start} – ${data.lunch.end}</span>
            </div>
            <div class="meal-slot">
                <span class="meal-label">Dîner</span>
                <span class="meal-time">${data.dinner.start} – ${data.dinner.end}</span>
            </div>
        `;
    }

    function renderFasting(data) {
        return `
            <p>Début du jeûne : ${data.fast_start}</p>
            <p>Fin du jeûne : ${data.fast_end}</p>
            <p>Durée : ${data.duration_h} heures</p>
            <p>${data.message}</p>
        `;
    }

    function renderNutrients(data) {
        return `
            <div>
                <h4>Matin</h4>
                <p>Nutriments : ${data.morning.nutrients.join(", ")}</p>
                <p>Conseil : ${data.morning.tip}</p>
            </div>
            <div>
                <h4>Midi</h4>
                <p>Nutriments : ${data.noon.nutrients.join(", ")}</p>
                <p>Conseil : ${data.noon.tip}</p>
            </div>
            <div>
                <h4>Soir</h4>
                <p>Nutriments : ${data.evening.nutrients.join(", ")}</p>
                <p>Conseil : ${data.evening.tip}</p>
            </div>
        `;
    }

    function renderSleep(data) {
        return data.recommendations.map(rec => `
            <div class="recommendation">
                <span class="badge ${rec.priority}">${rec.priority}</span>
                <h4>${rec.title}</h4>
                <p>${rec.description}</p>
            </div>
        `).join("");
    }
});