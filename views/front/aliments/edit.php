<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une consommation</title>
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

        .form-group input {
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

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 22px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: #fff;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-main, #ecf0f1);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <?php require __DIR__ . '/../partials/navbar.php'; ?>
    <?php
    $editErrors = $_SESSION['aliment_edit_error'] ?? [];

    if (!is_array($editErrors) && !empty($editErrors)) {
        $editErrors = [$editErrors];
    }

    unset($_SESSION['aliment_edit_error']);
    ?>

    <div class="section-wrapper">
        <h2 class="section-title">
            <i class="fa-solid fa-pen"></i> Modifier la consommation
        </h2>

        <div class="glass-card">
            <?php if (!empty($editErrors)): ?>
                <div style="background: rgba(231, 76, 60, 0.12); border: 1px solid rgba(231, 76, 60, 0.3); color: #f4b3ab; border-radius: 10px; padding: 12px 14px; margin-bottom: 18px;">
                    <strong>Erreur :</strong>
                    <ul style="margin: 8px 0 0; padding-left: 18px;">
                        <?php foreach ($editErrors as $editError): ?>
                            <li><?= htmlspecialchars($editError) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php?controller=suivi&action=update" novalidate>
                <input type="hidden" name="id" value="<?= htmlspecialchars($entry['id']) ?>">

                <div class="form-group">
                    <label>Aliment</label>
                    <input type="text" value="<?= htmlspecialchars($entry['nom']) ?>" disabled>
                </div>

                <div class="form-group">
                    <label>Date</label>
                    <input type="text" value="<?= htmlspecialchars($entry['date_consommation']) ?>" disabled>
                </div>

                <div class="form-group">
                    <label>Quantite (<?= ($entry['unite'] ?? 'g') === 'piece' ? 'piece' : 'g' ?>)</label>
                    <input type="text" name="quantite" value="<?= htmlspecialchars($entry['quantite']) ?>">
                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Enregistrer
                    </button>

                    <a class="btn btn-secondary" href="index.php?controller=suivi&action=index&mode=detail&date=<?= urlencode($entry['date_consommation']) ?>">
                        <i class="fa-solid fa-arrow-left"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
    <script src="/projet-web-25-26/public/assets/template_only_template/assets/js/script.js"></script>
</body>

</html>
