<?php
$aliment = $aliment ?? [];
$isEdit = !empty($aliment['id']);
$baseUrl = $baseUrl ?? '/projet-web-25-26';
$formAction = $isEdit ? 'admin-aliment-update' : 'admin-aliment-store';
?>
<form method="POST" action="<?= $baseUrl ?>/index.php?action=<?= $formAction ?>" class="admin-form" novalidate>
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= htmlspecialchars((string) $aliment['id']) ?>">
    <?php endif; ?>

    <div class="admin-form-grid">
        <div class="admin-form-group">
            <label for="nom">Nom</label>
            <input id="nom" type="text" name="nom" value="<?= htmlspecialchars((string) ($aliment['nom'] ?? '')) ?>" required>
        </div>

        <div class="admin-form-group">
            <label for="unite">Unite</label>
            <select id="unite" name="unite">
                <option value="g" <?= ($aliment['unite'] ?? 'g') === 'g' ? 'selected' : '' ?>>Grammes</option>
                <option value="piece" <?= ($aliment['unite'] ?? 'g') === 'piece' ? 'selected' : '' ?>>Piece</option>
            </select>
        </div>

        <div class="admin-form-group">
            <label for="calories">Calories / unite</label>
            <input id="calories" type="number" step="0.01" min="0" name="calories" value="<?= htmlspecialchars((string) ($aliment['calories'] ?? '')) ?>" required>
        </div>

        <div class="admin-form-group">
            <label for="proteines">Proteines / unite</label>
            <input id="proteines" type="number" step="0.01" min="0" name="proteines" value="<?= htmlspecialchars((string) ($aliment['proteines'] ?? 0)) ?>">
        </div>

        <div class="admin-form-group">
            <label for="glucides">Glucides / unite</label>
            <input id="glucides" type="number" step="0.01" min="0" name="glucides" value="<?= htmlspecialchars((string) ($aliment['glucides'] ?? 0)) ?>">
        </div>

        <div class="admin-form-group">
            <label for="lipides">Lipides / unite</label>
            <input id="lipides" type="number" step="0.01" min="0" name="lipides" value="<?= htmlspecialchars((string) ($aliment['lipides'] ?? 0)) ?>">
        </div>

        <div class="admin-form-group full">
            <label for="type">Type</label>
            <select id="type" name="type" required>
                <option value="">-- Choisir type --</option>
                <option value="proteine" <?= ($aliment['type'] ?? '') === 'proteine' ? 'selected' : '' ?>>Proteine</option>
                <option value="glucide" <?= ($aliment['type'] ?? '') === 'glucide' ? 'selected' : '' ?>>Glucide</option>
                <option value="lipide" <?= ($aliment['type'] ?? '') === 'lipide' ? 'selected' : '' ?>>Lipide</option>
            </select>
        </div>
    </div>

    <div class="admin-actions">
        <button type="submit" class="admin-btn">
            <i class="fa-solid fa-floppy-disk"></i>
            <?= $isEdit ? 'Enregistrer les modifications' : 'Ajouter l\'aliment' ?>
        </button>

        <a href="<?= $baseUrl ?>/index.php?action=admin-aliments" class="admin-btn secondary">
            Annuler
        </a>
    </div>
</form>
