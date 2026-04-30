<?php
$aliment = $aliment ?? [];
$isEdit = !empty($aliment['id']);
$formAction = $isEdit ? 'suiviUpdate' : 'suiviStore';
?>
<form method="POST" action="index.php?controller=backoffice&action=<?= $formAction ?>" class="admin-form" novalidate>
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= htmlspecialchars((string) $aliment['id']) ?>">
    <?php endif; ?>

    <div class="form-grid">
        <div class="field">
            <label for="nom">Nom</label>
            <input id="nom" type="text" name="nom" value="<?= htmlspecialchars((string) ($aliment['nom'] ?? '')) ?>" required>
        </div>

        <div class="field">
            <label for="unite">Unite</label>
            <select id="unite" name="unite">
                <option value="g" <?= ($aliment['unite'] ?? 'g') === 'g' ? 'selected' : '' ?>>Grammes</option>
                <option value="piece" <?= ($aliment['unite'] ?? 'g') === 'piece' ? 'selected' : '' ?>>Piece</option>
            </select>
        </div>

        <div class="field">
            <label for="calories">Calories / unite</label>
            <input id="calories" type="number" step="0.01" min="0" name="calories" value="<?= htmlspecialchars((string) ($aliment['calories'] ?? '')) ?>" required>
        </div>

        <div class="field">
            <label for="proteines">Proteines / unite</label>
            <input id="proteines" type="number" step="0.01" min="0" name="proteines" value="<?= htmlspecialchars((string) ($aliment['proteines'] ?? 0)) ?>">
        </div>

        <div class="field">
            <label for="glucides">Glucides / unite</label>
            <input id="glucides" type="number" step="0.01" min="0" name="glucides" value="<?= htmlspecialchars((string) ($aliment['glucides'] ?? 0)) ?>">
        </div>

        <div class="field">
            <label for="lipides">Lipides / unite</label>
            <input id="lipides" type="number" step="0.01" min="0" name="lipides" value="<?= htmlspecialchars((string) ($aliment['lipides'] ?? 0)) ?>">
        </div>

        <div class="field field-full">
            <label for="type">Type</label>
            <select id="type" name="type" required>
                <option value="">-- Choisir type --</option>
                <option value="proteine" <?= ($aliment['type'] ?? '') === 'proteine' ? 'selected' : '' ?>>Proteine</option>
                <option value="glucide" <?= ($aliment['type'] ?? '') === 'glucide' ? 'selected' : '' ?>>Glucide</option>
                <option value="lipide" <?= ($aliment['type'] ?? '') === 'lipide' ? 'selected' : '' ?>>Lipide</option>
            </select>
        </div>
    </div>

    <div class="admin-form-actions">
        <button type="submit" class="admin-btn admin-btn-primary">
            <i class="fa-solid fa-floppy-disk"></i>
            <?= $isEdit ? 'Enregistrer les modifications' : 'Ajouter l\'aliment' ?>
        </button>

        <a href="index.php?controller=backoffice&action=suivi" class="admin-btn admin-btn-secondary">
            Annuler
        </a>
    </div>
</form>
