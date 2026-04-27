    </main>

    <?php if (($showFooter ?? false) === true): ?>
    <footer class="footer">
        <div class="footer-container">
            <p>&copy; 2026 Smart Nutrition. All rights reserved.</p>
            <p>Designed for a cleaner MVC product workflow.</p>
        </div>
    </footer>
    <?php endif; ?>

    <?php $scriptPath = __DIR__ . '/assets/js/app.js'; ?>
    <?php $backofficeScriptPath = __DIR__ . '/assets/js/backoffice.js'; ?>
    <?php $scriptVersion = is_file($scriptPath) ? (string) filemtime($scriptPath) : (string) time(); ?>
    <?php $backofficeScriptVersion = is_file($backofficeScriptPath) ? (string) filemtime($backofficeScriptPath) : $scriptVersion; ?>
    <?php if (str_contains((string) ($bodyClass ?? ''), 'back-office')): ?>
    <script src="<?= htmlspecialchars(asset_url('js/backoffice.js')) ?>?v=<?= htmlspecialchars($backofficeScriptVersion) ?>"></script>
    <?php else: ?>
    <script src="<?= htmlspecialchars(asset_url('js/app.js')) ?>?v=<?= htmlspecialchars($scriptVersion) ?>"></script>
    <?php endif; ?>
</body>
</html>
