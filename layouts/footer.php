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

    <script src="<?= htmlspecialchars(app_url('assets/js/app.js')) ?>?v=<?= $assetVersion ?>"></script>
</body>
</html>
