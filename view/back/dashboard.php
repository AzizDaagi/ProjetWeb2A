<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || (($_SESSION['user_role'] ?? 'user') !== 'admin')) {
    header('Location: /projet-web-25-26/index.php?action=login');
    exit;
}
header('Location: /projet-web-25-26/index.php?action=admin-community');
exit;
require_once __DIR__ . '/../../model/Connection.php';
require_once __DIR__ . '/../../model/Post.php';
require_once __DIR__ . '/../../model/Comment.php';

$adminName = $_SESSION['user_name'] ?? 'Admin';

$postModel = new Post(config::getConnexion());
$commentModel = new Comment(config::getConnexion());

$newPosts = $postModel->getNewPostsCountLast24h();
$newComments = $commentModel->getNewCommentsCountLast24h();
$newReports = $postModel->getNewReportsCountLast24h();
$mostInteractedPost = $postModel->getMostInteractedPostThisWeek();

$postsSparkline = $postModel->getPostsDailyCountsLast7Days();
$commentsSparkline = $commentModel->getCommentsDailyCountsLast7Days();
$reportsSparkline = $postModel->getReportsDailyCountsLast7Days();

function buildSparkline(array $values, string $colorClass = ''): string {
    if (empty($values) || max($values) === 0) {
        return '<svg class="sparkline-svg ' . htmlspecialchars($colorClass) . '" viewBox="0 0 100 40" preserveAspectRatio="none"><polyline points="0,20 100,20" style="stroke-opacity:0.15;"/></svg>';
    }

    $max = max($values);
    $min = min($values);
    $range = $max - $min;
    if ($range === 0) {
        $range = 1;
    }

    $count = count($values);
    $stepX = 100 / ($count - 1);
    $points = [];
    $fillPoints = ["0,40"];

    foreach ($values as $i => $val) {
        $x = $i * $stepX;
        $y = 40 - (($val - $min) / $range) * 34 - 3; // leave 3px padding top/bottom
        $points[] = "$x,$y";
        $fillPoints[] = "$x,$y";
    }
    $fillPoints[] = "100,40";

    $polyline = implode(' ', $points);
    $fillPoly = implode(' ', $fillPoints);

    $svg = '<svg class="sparkline-svg ' . htmlspecialchars($colorClass) . '" viewBox="0 0 100 40" preserveAspectRatio="none">';
    $svg .= '<polygon class="sparkline-fill" points="' . $fillPoly . '"/>';
    $svg .= '<polyline points="' . $polyline . '"/>';
    $svg .= '</svg>';

    return $svg;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Back Office - Tableau de bord</title>
    <link rel="stylesheet" href="/projet-web-25-26/view/back/style/community.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="backoffice-page">
    <nav class="navbar">
        <div class="navbar-brand">
            <a href="/projet-web-25-26/view/back/community.php" class="brand-link">
                <img src="/projet-web-25-26/view/back/style/logo.png" alt="Smart Nutrition" class="brand-logo navbar-preview-logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                <span class="brand-fallback"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
            </a>
        </div>
        <ul class="navbar-menu">
            <li><a href="/projet-web-25-26/view/back/dashboard.php" class="nav-link active"><i class="fa-solid fa-chart-line"></i> Tableau de bord</a></li>
            <li><a href="/projet-web-25-26/view/back/community.php" class="nav-link"><i class="fa-solid fa-users"></i> Communaute</a></li>
            <li><a href="/projet-web-25-26/view/back/reports.php" class="nav-link"><i class="fa-solid fa-flag"></i> Signalements</a></li>
        </ul>
        <div class="navbar-footer">
            <button type="button" id="themeToggle" class="nav-link theme-toggle" aria-label="Changer le mode de couleur" aria-pressed="false">
                <i class="fa-solid fa-moon"></i> Sombre
            </button>
            <p class="user-info">Admin: <strong><?= htmlspecialchars($adminName) ?></strong></p>
        </div>
    </nav>

    <div class="main-content">
        <div class="container">
            <h1 class="mb-4"><i class="fa-solid fa-chart-line"></i> Tableau de bord</h1>

            <div class="dashboard-grid">
                <!-- New Posts -->
                <div class="metric-card">
                    <div class="metric-card-header">
                        <div class="metric-card-label">Nouvelles publications sur les dernieres 24 heures</div>
                        <div class="metric-card-icon"><i class="fa-solid fa-file-pen"></i></div>
                    </div>
                    <div class="metric-card-value"><?= (int) $newPosts ?></div>
                    <div class="sparkline-wrap">
                        <?= buildSparkline($postsSparkline) ?>
                    </div>
                </div>

                <!-- New Comments -->
                <div class="metric-card">
                    <div class="metric-card-header">
                        <div class="metric-card-label">Nouveaux commentaires sur les dernieres 24 heures</div>
                        <div class="metric-card-icon accent-green"><i class="fa-solid fa-comments"></i></div>
                    </div>
                    <div class="metric-card-value"><?= (int) $newComments ?></div>
                    <div class="sparkline-wrap">
                        <?= buildSparkline($commentsSparkline, 'green') ?>
                    </div>
                </div>

                <!-- New Reports -->
                <div class="metric-card">
                    <div class="metric-card-header">
                        <div class="metric-card-label">Nouveaux signalements sur les dernieres 24 heures</div>
                        <div class="metric-card-icon accent-orange"><i class="fa-solid fa-flag"></i></div>
                    </div>
                    <div class="metric-card-value"><?= (int) $newReports ?></div>
                    <div class="sparkline-wrap">
                        <?= buildSparkline($reportsSparkline, 'orange') ?>
                    </div>
                </div>

                <!-- Most Interacted Post -->
                <div class="metric-card highlight">
                    <div class="metric-card-header">
                        <div class="metric-card-label">Publication la plus active cette semaine</div>
                        <div class="metric-card-icon accent-purple"><i class="fa-solid fa-fire"></i></div>
                    </div>
                    <?php if ($mostInteractedPost): ?>
                        <div class="metric-card-value"><?= (int) $mostInteractedPost['total_interactions'] ?> <span style="font-size:1rem;font-weight:600;color:var(--text-muted);">interactions</span></div>
                        <div class="metric-card-subtitle"><?= htmlspecialchars($mostInteractedPost['title'] ?? 'Sans titre') ?> - <strong>@<?= htmlspecialchars($mostInteractedPost['username'] ?? 'Inconnu') ?></strong></div>
                        <div class="interaction-breakdown">
                            <span><i class="fa-solid fa-comment"></i> <?= (int) $mostInteractedPost['comments_count'] ?> commentaires</span>
                            <span><i class="fa-solid fa-reply"></i> <?= (int) $mostInteractedPost['replies_count'] ?> reponses</span>
                            <span><i class="fa-solid fa-heart"></i> <?= (int) $mostInteractedPost['reactions_count'] ?> reactions</span>
                        </div>
                    <?php else: ?>
                        <div class="metric-card-value">&mdash;</div>
                        <div class="metric-card-subtitle">Aucune interaction cette semaine</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="/projet-web-25-26/view/back/style/community.js"></script>
</body>
</html>
