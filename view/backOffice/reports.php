<?php
require_once '../../model/connection.php';
require_once '../../model/Post.php';
require_once '../../model/AiModeration.php';
require_once '../../model/ImageModeration.php';

$adminName = $_SESSION['user_name'] ?? 'Admin';
$postModel = new Post(config::getConnexion());
$aiModeration = new AiModeration(config::getConnexion());
$imageModeration = new ImageModeration(config::getConnexion());
$reports = $postModel->getAllReports();
$postModerationResults = $aiModeration->getResultsForContentType('post');
$postImageModerationResults = $imageModeration->getResultsForContentType('post');
$moderationCounts = $aiModeration->getStatusCounts();
$imageModerationCounts = $imageModeration->getStatusCounts();

$pendingReports = 0;
$resolvedReports = 0;
foreach ($reports as $report) {
    if (($report['status'] ?? '') === 'resolved') {
        $resolvedReports++;
    } else {
        $pendingReports++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Back Office - Reports</title>
    <link rel="stylesheet" href="style/community.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="backoffice-page">
    <nav class="navbar">
        <div class="navbar-brand">
            <a href="community.php" class="brand-link">
                <img src="style/logo.png" alt="Smart Nutrition" class="brand-logo navbar-preview-logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                <span class="brand-fallback"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
            </a>
        </div>
        <ul class="navbar-menu">
            <li><a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
            <li><a href="community.php" class="nav-link"><i class="fa-solid fa-users"></i> Community</a></li>
            <li><a href="reports.php" class="nav-link active"><i class="fa-solid fa-flag"></i> Reports</a></li>
        </ul>
        <div class="navbar-footer">
            <button type="button" id="themeToggle" class="nav-link theme-toggle" aria-label="Changer le mode de couleur" aria-pressed="false">
                <i class="fa-solid fa-moon"></i> Dark
            </button>
            <p class="user-info">Admin: <strong><?= htmlspecialchars($adminName) ?></strong></p>
        </div>
    </nav>

    <div class="main-content">
        <div class="container">
            <h1 class="mb-4"><i class="fa-solid fa-flag"></i> Report Center</h1>

            <div class="admin-cards">
                <div class="admin-card">
                    <h3><?= count($reports) ?></h3>
                    <p>Total reports</p>
                </div>
                <div class="admin-card">
                    <h3><?= $pendingReports ?></h3>
                    <p>Pending</p>
                </div>
                <div class="admin-card">
                    <h3><?= $resolvedReports ?></h3>
                    <p>Resolved</p>
                </div>
                <div class="admin-card ai-card-review">
                    <h3><?= (int) ($moderationCounts['review'] ?? 0) ?></h3>
                    <p>AI review needed</p>
                </div>
                <div class="admin-card ai-card-error">
                    <h3><?= (int) ($moderationCounts['error'] ?? 0) ?></h3>
                    <p>AI errors</p>
                </div>
                <div class="admin-card ai-card-review">
                    <h3><?= (int) ($imageModerationCounts['review'] ?? 0) ?></h3>
                    <p>Image review needed</p>
                </div>
            </div>

            <div class="card card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">All Reports</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($reports)): ?>
                        <div class="reports-table-wrap">
                            <table class="reports-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Status</th>
                                        <th>Reason</th>
                                        <th>AI</th>
                                        <th>Image AI</th>
                                        <th>Post</th>
                                        <th>Reporter</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reports as $report): ?>
                                        <tr>
                                            <td>#<?= (int) $report['id'] ?></td>
                                            <td>
                                                <span class="status-pill status-<?= htmlspecialchars($report['status'] ?: 'pending') ?>">
                                                    <?= htmlspecialchars(ucfirst($report['status'] ?: 'pending')) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', $report['reason'] ?? ''))) ?></td>
                                            <td>
                                                <?php $ai = $postModerationResults[(int) ($report['post_id'] ?? 0)] ?? null; ?>
                                                <?php if ($ai): ?>
                                                    <span class="ai-badge ai-badge-<?= htmlspecialchars($ai['status']) ?>">
                                                        <?= htmlspecialchars(ucfirst($ai['status'])) ?> <?= (int) round(((float) $ai['score']) * 100) ?>%
                                                    </span>
                                                <?php else: ?>
                                                    <span class="ai-badge ai-badge-missing">Not checked</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php $imageAi = $postImageModerationResults[(int) ($report['post_id'] ?? 0)] ?? null; ?>
                                                <?php if ($imageAi): ?>
                                                    <span class="ai-badge ai-badge-<?= htmlspecialchars($imageAi['status']) ?>">
                                                        <?= htmlspecialchars(ucfirst($imageAi['status'])) ?> <?= (int) round(((float) $imageAi['score']) * 100) ?>%
                                                    </span>
                                                <?php else: ?>
                                                    <span class="ai-badge ai-badge-missing">Not checked</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($report['post_title'] ?? '[Deleted post]') ?></td>
                                            <td><?= htmlspecialchars($report['reporter_username'] ?? 'Unknown') ?></td>
                                            <td><?= htmlspecialchars($report['created_at'] ?? '-') ?></td>
                                            <td>
                                                <a class="btn btn-outline-secondary btn-sm" href="report_details.php?id=<?= (int) $report['id'] ?>">
                                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Open
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No reports have been submitted yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="style/community.js"></script>
</body>
</html>
