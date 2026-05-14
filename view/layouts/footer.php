    </main>

    <?php if (isset($showFooter) && $showFooter === true): ?>
    <footer class="footer">
        <div class="footer-container">
            <p>&copy; 2026 Smart Nutrition. Tous droits reserves.</p>
            <p>Concu avec <i class="fa-solid fa-heart"></i> pour une nutrition saine</p>
        </div>
    </footer>
    <?php endif; ?>

    <?php if (!isset($_GET['embed']) || $_GET['embed'] !== 'true'): ?>
    <video id="gestureVideoHidden" class="gesture-video-hidden" autoplay playsinline muted></video>
    <canvas id="gestureCanvasHidden" class="gesture-canvas-hidden" aria-hidden="true"></canvas>
    <div id="gestureCursor" class="gesture-cursor" aria-hidden="true"></div>
    <div id="voiceControlDock" class="voice-control-dock" aria-live="polite">
        <button type="button" id="voiceToggleBtn" class="voice-toggle-btn" aria-pressed="false" aria-label="Activer le controle vocal">
            <i class="fa-solid fa-microphone"></i>
            <span>Voix</span>
        </button>
        <div id="voiceControlPanel" class="voice-control-panel">
            <div class="voice-control-head">
                <div>
                    <p class="voice-control-eyebrow">Controle vocal</p>
                    <strong id="voiceStatusLabel">Inactif</strong>
                </div>
                <span id="voiceStatusBadge" class="voice-status-badge">Pret</span>
            </div>
            <p id="voiceTranscript" class="voice-transcript">Cliquez sur le micro puis dites une commande.</p>
            <p id="voiceLastAction" class="voice-last-action">Exemples: "ouvre profil", "descendre", "cliquer deconnexion".</p>
        </div>
    </div>
    <?php endif; ?>

    <script async src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js"></script>
    <script async src="https://cdn.jsdelivr.net/npm/@mediapipe/hands/hands.js"></script>
    <script src="/smart_nutritionn/gestionActiviteesportive/view/assets/app.js?v=<?= $assetVersion ?>"></script>
    <script src="/smart_nutritionn/gestionActiviteesportive/view/assets/controlesaisie.js?v=<?= $assetVersion ?>"></script>
    <?php if (!empty($additionalScripts) && is_array($additionalScripts)): ?>
        <?php foreach ($additionalScripts as $scriptSrc): ?>
            <script src="<?= htmlspecialchars($scriptSrc, ENT_QUOTES, 'UTF-8') ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
