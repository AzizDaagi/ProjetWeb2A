    </main>

    <?php if (isset($showFooter) && $showFooter === true): ?>
    <footer class="footer">
        <div class="footer-container">
            <p>&copy; 2026 Smart Nutrition. Tous droits reserves.</p>
            <p>Concu avec <i class="fa-solid fa-heart"></i> pour une nutrition saine</p>
        </div>
    </footer>
    <?php endif; ?>

    <video id="gestureVideoHidden" class="gesture-video-hidden" autoplay playsinline muted></video>
    <canvas id="gestureCanvasHidden" class="gesture-canvas-hidden" aria-hidden="true"></canvas>
    <div id="gestureCursor" class="gesture-cursor" aria-hidden="true"></div>

    <script async src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js"></script>
    <script async src="https://cdn.jsdelivr.net/npm/@mediapipe/hands/hands.js"></script>
    <script src="/smart_nutrition/view/assets/app.js?v=<?= $assetVersion ?>"></script>
    <script src="/smart_nutrition/view/assets/controlesaisie.js?v=<?= $assetVersion ?>"></script>
</body>
</html>
