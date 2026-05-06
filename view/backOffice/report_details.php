<?php
require_once '../../model/connection.php';
require_once '../../model/Post.php';

$adminName = $_SESSION['user_name'] ?? 'Admin';
$postModel = new Post(config::getConnexion());
$reportId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$resolutionMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resolve_report_id'])) {
    $resolveReportId = (int) $_POST['resolve_report_id'];
    if ($resolveReportId > 0) {
        $postModel->resolveReport($resolveReportId);
        $resolutionMessage = 'Report resolved successfully.';
    }
}

$report = $reportId > 0 ? $postModel->getReportById($reportId) : null;

function resolvePostImageSrcForReport($image)
{
    if (!$image) {
        return null;
    }

    if (strpos($image, '/Web/view/post_uploads/posts/') === 0) {
        return $image;
    }

    return null;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Back Office - Report Details</title>
    <link rel="stylesheet" href="style/community.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">
            <a href="community.php" class="brand-link">
                <img src="style/logo.png" alt="Smart Nutrition" class="brand-logo navbar-preview-logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                <span class="brand-fallback"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
            </a>
        </div>
        <ul class="navbar-menu">
            <li><a href="community.php" class="nav-link"><i class="fa-solid fa-user-shield"></i> Community</a></li>
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
            <h1 class="mb-4"><i class="fa-solid fa-triangle-exclamation"></i> Report Details</h1>

            <?php if ($resolutionMessage): ?>
                <div class="status-banner success-banner"><?= htmlspecialchars($resolutionMessage) ?></div>
            <?php endif; ?>

            <?php if ($report): ?>
                <?php $reportImageSrc = resolvePostImageSrcForReport($report['post_image'] ?? null); ?>
                <div class="card card-primary shadow-sm">
                    <div class="card-header report-detail-header">
                        <div>
                            <h3 class="card-title">Report #<?= (int) $report['id'] ?></h3>
                            <p class="report-meta-line">
                                Status:
                                <span class="status-pill status-<?= htmlspecialchars($report['status'] ?: 'pending') ?>">
                                    <?= htmlspecialchars(ucfirst($report['status'] ?: 'pending')) ?>
                                </span>
                            </p>
                        </div>
                        <div class="report-action-row">
                            <a href="reports.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Back to Reports</a>
                            <a href="review_post.php?report_id=<?= (int) $report['id'] ?>&post_id=<?= (int) $report['post_id'] ?>" class="btn btn-sm">
                                <i class="fa-solid fa-eye"></i> Review Post
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="detail-grid">
                            <div class="detail-card">
                                <h4>Report Info</h4>
                                <p><strong>Reason:</strong> <?= htmlspecialchars(ucwords(str_replace('_', ' ', $report['reason'] ?? ''))) ?></p>
                                <p><strong>Reporter:</strong> <?= htmlspecialchars($report['reporter_username'] ?? 'Unknown') ?></p>
                                <p><strong>Date:</strong> <?= htmlspecialchars($report['created_at'] ?? '-') ?></p>
                                <p><strong>Details:</strong> <?= nl2br(htmlspecialchars($report['details'] ?? 'No extra details provided.')) ?></p>
                            </div>
                            <div class="detail-card">
                                <h4>Reported Post</h4>
                                <p><strong>Title:</strong> <?= htmlspecialchars($report['post_title'] ?? '[Deleted post]') ?></p>
                                <p><strong>Author:</strong> <?= htmlspecialchars($report['post_author_username'] ?? 'Unknown') ?></p>
                                <p><strong>Created:</strong> <?= htmlspecialchars($report['post_created_at'] ?? '-') ?></p>
                                <p><strong>Preview:</strong> <?= nl2br(htmlspecialchars($report['post_content'] ?? 'This post is no longer available.')) ?></p>
                                <?php if ($reportImageSrc): ?>
                                    <img src="<?= htmlspecialchars($reportImageSrc) ?>" alt="Reported post image" class="post-image" style="max-height: 280px; width: auto; max-width: 100%; object-fit: contain;">
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="report-resolution-bar">
                            <?php if (($report['status'] ?? 'pending') !== 'resolved'): ?>
                                <form method="post">
                                    <input type="hidden" name="resolve_report_id" value="<?= (int) $report['id'] ?>">
                                    <button type="submit" class="btn btn-sm"><i class="fa-solid fa-circle-check"></i> Resolve Report</button>
                                </form>
                            <?php else: ?>
                                <span class="status-banner">This report has already been resolved.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card card-primary shadow-sm">
                    <div class="card-body">
                        <p class="text-muted">Report not found.</p>
                        <a href="reports.php" class="btn btn-outline-secondary btn-sm">Back to Reports</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="style/community.js"></script>
</body>
</html>
