<?php
$pageTitle = $pageTitle ?? 'Backoffice';
$showNav = true;
$isAdminTemplate = true;
$totalUsers = (int) ($totalUsers ?? 24);
$recentUsers = isset($recentUsers) && is_array($recentUsers) ? $recentUsers : [
    ['prenom' => 'Sara', 'nom' => 'Benali', 'email' => 'sara@example.com'],
    ['prenom' => 'Yassine', 'nom' => 'Khalil', 'email' => 'yassine@example.com'],
];
$evolutionLabels = isset($evolutionLabels) && is_array($evolutionLabels) ? $evolutionLabels : ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Juin'];

include __DIR__ . '/includes/header.php';
include __DIR__ . '/pages/dashboard.php';
include __DIR__ . '/includes/footer.php';
