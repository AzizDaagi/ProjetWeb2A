<section class="chrono-page">
    <div class="chrono-shell">
        <section class="chrono-hero">
            <div class="chrono-hero__content">
                <a href="/Web/index.php?action=objectif" class="chrono-back-link">
                    Retour a mon objectif nutritionnel
                </a>
                <span class="chrono-hero-badge">Rythme circadien</span>
                <h1>Chrono-Nutrition</h1>
                <p class="chrono-subtitle">
                    Optimise tes horaires de repas selon ton rythme de sommeil, ton energie et ton activite.
                </p>
            </div>
        </section>

        <section class="chrono-card chrono-card--form">
            <div class="chrono-section-head">
                <div>
                    <h2>Profil chrono</h2>
                    <p class="chrono-muted">
                        Le formulaire reste en haut pour ajuster facilement ton profil et actualiser les recommandations.
                    </p>
                </div>
            </div>

            <form id="chrono-form" class="chrono-form" novalidate>
                <div class="chrono-form-grid">
                    <div class="chrono-field">
                        <label for="chronotype">Chronotype</label>
                        <select id="chronotype" name="chronotype">
                            <option value="leve_tot">Leve-tot</option>
                            <option value="standard">Standard</option>
                            <option value="couche_tard">Couche-tard</option>
                        </select>
                    </div>

                    <div class="chrono-field">
                        <label for="wake_time">Heure de reveil</label>
                        <input type="time" id="wake_time" name="wake_time">
                    </div>

                    <div class="chrono-field">
                        <label for="sleep_time">Heure de coucher</label>
                        <input type="time" id="sleep_time" name="sleep_time">
                    </div>

                    <div class="chrono-field">
                        <label for="sleep_quality">Qualite du sommeil</label>
                        <select id="sleep_quality" name="sleep_quality">
                            <option value="bonne">Bonne</option>
                            <option value="moyenne">Moyenne</option>
                            <option value="mauvaise">Mauvaise</option>
                        </select>
                    </div>

                    <div class="chrono-field">
                        <label for="energy_peak">Pic d'energie</label>
                        <select id="energy_peak" name="energy_peak">
                            <option value="">Choisir</option>
                            <option value="matin">Matin</option>
                            <option value="apres_midi">Apres-midi</option>
                            <option value="soir">Soir</option>
                        </select>
                    </div>

                    <div class="chrono-field">
                        <label for="energy_dip">Creux d'energie</label>
                        <select id="energy_dip" name="energy_dip">
                            <option value="aucun">Aucun</option>
                            <option value="fin_matin">Fin de matinee</option>
                            <option value="apres_midi">Apres-midi</option>
                            <option value="soir">Soir</option>
                        </select>
                    </div>

                    <div class="chrono-field">
                        <label for="workout_time">Heure habituelle de sport</label>
                        <select id="workout_time" name="workout_time">
                            <option value="aucun">Aucun</option>
                            <option value="matin">Matin</option>
                            <option value="midi">Midi</option>
                            <option value="apres_midi">Apres-midi</option>
                            <option value="soir">Soir</option>
                        </select>
                    </div>

                    <div class="chrono-field">
                        <label for="last_caffeine_time">Dernier cafe</label>
                        <select id="last_caffeine_time" name="last_caffeine_time">
                            <option value="aucun">Aucun</option>
                            <option value="avant_12h">Avant 12h</option>
                            <option value="12_14h">12h-14h</option>
                            <option value="14_17h">14h-17h</option>
                            <option value="apres_17h">Apres 17h</option>
                        </select>
                    </div>

                    <div class="chrono-field">
                        <label for="preferred_meals_count">Nombre de repas souhaite</label>
                        <select id="preferred_meals_count" name="preferred_meals_count">
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                    </div>
                </div>

                <div class="chrono-form-actions">
                    <button type="submit" id="saveProfile">Sauvegarder mon profil</button>
                    <p class="chrono-muted chrono-form-note">
                        Les recommandations restent informatives et s affichent juste en dessous du formulaire.
                    </p>
                </div>
            </form>

            <div id="chrono-feedback" class="chrono-feedback" hidden></div>
        </section>

        <section id="results" class="chrono-results">
            <div id="block-summary" class="chrono-card result-block"></div>
            <div id="block-timing" class="chrono-card result-block"></div>
            <div id="block-personalization" class="chrono-card result-block"></div>
            <div id="block-fasting" class="chrono-card result-block"></div>
            <div id="block-nutrients" class="chrono-card result-block"></div>
            <div id="block-sleep" class="chrono-card result-block chrono-card--wide"></div>
        </section>

        <p id="chrono-disclaimer" class="chrono-disclaimer" hidden></p>
    </div>
</section>
