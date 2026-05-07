<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Smart Nutrition' ?></title>
    <?php $assetVersion = time(); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="/smart_nutrition/View/style.css?v=<?= $assetVersion ?>">
    <link rel="stylesheet" href="/smart_nutrition/View/backoffice.css?v=<?= $assetVersion ?>">
</head>
<body class="has-sidebar">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
