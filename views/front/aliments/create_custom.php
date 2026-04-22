<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un aliment personnalise</title>
    <link rel="stylesheet" href="views/front/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            opacity: 1 !important;
            visibility: visible !important;
        }

        .section-wrapper {
            width: 90%;
            max-width: 620px;
            margin: 40px auto;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-main, #ecf0f1);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-group label {
            display: block;
            color: rgba(236, 240, 241, 0.7);
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .form-group input,
        .form-group select {
            display: block;
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.07);
            color: var(--text-main, #ecf0f1);
            font-size: 0.95rem;
            box-sizing: border-box;
        }

        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .actions .btn {
            width: auto;
            margin-top: 0;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-main, #ecf0f1);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
    </style>
</head>

<body>
    <?php require __DIR__ . '/../partials/navbar.php'; ?>
    <?php
    $customErrors = $_SESSION['custom_aliment_error'] ?? [];
    $customForm = $_SESSION['custom_aliment_form'] ?? [];

    if (!is_array($customErrors) && !empty($customErrors)) {
        $customErrors = [$customErrors];
    }

    unset($_SESSION['custom_aliment_error'], $_SESSION['custom_aliment_form']);
    ?>

    <div class="section-wrapper">
        <h2 class="section-title">
            <i class="fa-solid fa-pen-to-square"></i> Ajouter un aliment personnalise
        </h2>

        <div class="glass-card">
            <?php if (!empty($customErrors)): ?>
                <div style="background: rgba(231, 76, 60, 0.12); border: 1px solid rgba(231, 76, 60, 0.3); color: #f4b3ab; border-radius: 10px; padding: 12px 14px; margin-bottom: 18px;">
                    <strong>Erreur :</strong>
                    <ul style="margin: 8px 0 0; padding-left: 18px;">
                        <?php foreach ($customErrors as $customError): ?>
                            <li><?= htmlspecialchars($customError) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php?controller=aliment&action=storeCustom" novalidate>
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" placeholder="Nom" value="<?= htmlspecialchars((string) ($customForm['nom'] ?? '')) ?>">
                </div>

                <div class="form-group">
                    <label>Type</label>
                    <select name="type">
                        <option value="proteine" <?= ($customForm['type'] ?? 'proteine') === 'proteine' ? 'selected' : '' ?>>Proteine</option>
                        <option value="glucide" <?= ($customForm['type'] ?? 'proteine') === 'glucide' ? 'selected' : '' ?>>Glucide</option>
                        <option value="lipide" <?= ($customForm['type'] ?? 'proteine') === 'lipide' ? 'selected' : '' ?>>Lipide</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Unite</label>
                    <select name="unite">
                        <option value="g" <?= ($customForm['unite'] ?? 'g') === 'g' ? 'selected' : '' ?>>Grammes</option>
                        <option value="piece" <?= ($customForm['unite'] ?? 'g') === 'piece' ? 'selected' : '' ?>>Piece</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Calories / unite</label>
                    <input type="text" name="calories" placeholder="Ex : 120" value="<?= htmlspecialchars((string) ($customForm['calories'] ?? '')) ?>">
                </div>

                <div class="form-group">
                    <label>Proteines / unite</label>
                    <input type="text" name="proteines" placeholder="Ex : 24" value="<?= htmlspecialchars((string) ($customForm['proteines'] ?? '')) ?>">
                </div>

                <div class="form-group">
                    <label>Glucides / unite</label>
                    <input type="text" name="glucides" placeholder="Ex : 12" value="<?= htmlspecialchars((string) ($customForm['glucides'] ?? '')) ?>">
                </div>

                <div class="form-group">
                    <label>Lipides / unite</label>
                    <input type="text" name="lipides" placeholder="Ex : 8" value="<?= htmlspecialchars((string) ($customForm['lipides'] ?? '')) ?>">
                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <a href="index.php?controller=aliment&action=index" class="btn btn-secondary">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="/projet-web-25-26/public/assets/template_only_template/assets/js/script.js"></script>
</body>

</html>
