<footer class="admin-footer">
    <span>Smart Nutrition Back Office</span>
    <span><?= date('Y') ?></span>
</footer>
<?php if (!empty($loadMainAppAssets)): ?>
    <script src="<?= htmlspecialchars($basePath) ?>/view/assets/app.js?v=<?= $assetVersion ?>" defer></script>
<?php endif; ?>
<script src="<?= htmlspecialchars($assetBase) ?>/js/backoffice.js" defer></script>
</body>
</html>
