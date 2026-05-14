<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assistant Nutrition</title>
    <link rel="stylesheet" href="/Web/view/front/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .chatbot-page-shell {
            max-width: 760px;
        }

        .chatbot-page-card {
            padding: 26px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 16px 38px rgba(15, 23, 42, 0.18);
        }

        .chatbot-page-card p {
            color: rgba(236, 240, 241, 0.84);
            line-height: 1.6;
        }

        body.theme-light .chatbot-page-card {
            background: rgba(255, 255, 255, 0.82);
            border-color: rgba(148, 163, 184, 0.22);
            box-shadow: 0 18px 38px rgba(148, 163, 184, 0.18);
        }

        body.theme-light .chatbot-page-card p {
            color: #475569;
        }
    </style>
</head>

<body>
    <?php require __DIR__ . '/../partials/navbar.php'; ?>

    <div class="container chatbot-page-shell">
        <div class="chatbot-page-card">
            <h1>Assistant Nutrition</h1>
            <p>
                Le chatbot est maintenant disponible sous forme de widget flottant sur les pages front.
                Il s'ouvre automatiquement ici, et vous pouvez aussi l'utiliser depuis le bouton en bas a droite sur le suivi, les objectifs et les statistiques.
            </p>
        </div>
    </div>

    <?php require __DIR__ . '/../partials/chatbot_widget.php'; ?>
<script src="/Web/view/front/assets/js/theme.js"></script>
</body>

</html>
