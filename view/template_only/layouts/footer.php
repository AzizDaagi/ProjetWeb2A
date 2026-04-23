    </main>

    <!-- Footer optionnel -->
    <?php if (isset($showFooter) && $showFooter === true): ?>
    <footer class="footer">
        <div class="footer-container">
            <p>&copy; 2026 Smart Nutrition. All rights reserved.</p>
            <p>Designed with <i class="fa-solid fa-heart"></i> for healthy nutrition</p>
        </div>
    </footer>
    <?php endif; ?>

    <video id="gestureVideoHidden" class="gesture-video-hidden" autoplay playsinline muted></video>
    <canvas id="gestureCanvasHidden" class="gesture-canvas-hidden" aria-hidden="true"></canvas>
    <div id="gestureCursor" class="gesture-cursor" aria-hidden="true"></div>

    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/hands/hands.js"></script>
    <script src="/projetwebmalek/view/template_only/assets/js/app.js?v=<?= $assetVersion ?>"></script>
</body>
</html>
