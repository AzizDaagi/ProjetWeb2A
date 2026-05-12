<?php
require_once '../../model/connection.php';
require_once '../../model/Post.php';
require_once '../../model/Notification.php';
require_once '../../model/InputValidator.php';

$adminName = $_SESSION['user_name'] ?? 'Admin';
$db = config::getConnexion();
$postModel = new Post($db);
$notificationModel = new Notification($db);
$reportId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$resolutionMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resolve_report_id'])) {
    $resolveReportId = (int) $_POST['resolve_report_id'];
    if ($resolveReportId > 0) {
        $reportBeforeResolve = $postModel->getReportById($resolveReportId);
        $reviewNote = InputValidator::cleanMultiline($_POST['review_note'] ?? '');
        $validationError = InputValidator::validateReviewNote($reviewNote);
        if ($validationError) {
            $resolutionMessage = $validationError;
        } else {
        $postModel->resolveReport($resolveReportId, $reviewNote);
        if ($reportBeforeResolve) {
            $recipientUserId = !empty($reportBeforeResolve['post_user_id']) ? (int) $reportBeforeResolve['post_user_id'] : (int) ($reportBeforeResolve['user_id'] ?? 0);
            $postTitle = $reportBeforeResolve['post_title'] ?? 'the reported post';
            $reason = ucwords(str_replace('_', ' ', $reportBeforeResolve['reason'] ?? 'report'));
            $message = !empty($reportBeforeResolve['post_user_id'])
                ? 'Un administrateur a examine et resolu le signalement concernant votre publication "' . $postTitle . '". Raison : ' . $reason . '.'
                : 'Un administrateur a examine et resolu votre signalement concernant une publication qui a depuis ete supprimee. Raison : ' . $reason . '.';
            if ($reviewNote !== '') {
                $message .= ' Note de revision : ' . $reviewNote;
            }
            if ($recipientUserId > 0) {
                $linkUrl = !empty($reportBeforeResolve['post_user_id'])
                    ? '/Web/view/frontoffice/community.php#post-' . (int) $reportBeforeResolve['post_id']
                    : '/Web/view/frontoffice/community.php';

                $notificationModel->create(
                    $recipientUserId,
                    1,
                    'report_resolved',
                    'Signalement resolu',
                    $message,
                    $linkUrl,
                    (int) $reportBeforeResolve['post_id'],
                    null,
                    (int) $reportBeforeResolve['id']
                );
            }
        }
        $resolutionMessage = 'Signalement resolu avec succes.';
        }
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
    <title>Back Office - Details du signalement</title>
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
            <li><a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> Tableau de bord</a></li>
            <li><a href="community.php" class="nav-link"><i class="fa-solid fa-users"></i> Communaute</a></li>
            <li><a href="reports.php" class="nav-link active"><i class="fa-solid fa-flag"></i> Signalements</a></li>
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
            <h1 class="mb-4"><i class="fa-solid fa-triangle-exclamation"></i> Details du signalement</h1>

            <?php if ($resolutionMessage): ?>
                <div class="status-banner success-banner"><?= htmlspecialchars($resolutionMessage) ?></div>
            <?php endif; ?>

            <?php if ($report): ?>
                <?php $reportImageSrc = resolvePostImageSrcForReport($report['post_image'] ?? null); ?>
                <div class="card card-primary shadow-sm">
                    <div class="card-header report-detail-header">
                        <div>
                            <h3 class="card-title">Signalement #<?= (int) $report['id'] ?></h3>
                            <p class="report-meta-line">
                                Statut :
                                <span class="status-pill status-<?= htmlspecialchars($report['status'] ?: 'pending') ?>">
                                    <?= htmlspecialchars(($report['status'] ?? 'pending') === 'resolved' ? 'Resolu' : 'En attente') ?>
                                </span>
                            </p>
                        </div>
                        <div class="report-action-row">
                            <a href="reports.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Retour aux signalements</a>
                            <a href="review_post.php?report_id=<?= (int) $report['id'] ?>&post_id=<?= (int) $report['post_id'] ?>" class="btn btn-sm">
                                <i class="fa-solid fa-eye"></i> Examiner la publication
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="detail-grid">
                            <div class="detail-card">
                                <h4>Informations du signalement</h4>
                                <p><strong>Raison :</strong> <?= htmlspecialchars(ucwords(str_replace('_', ' ', $report['reason'] ?? ''))) ?></p>
                                <p><strong>Signale par :</strong> <?= htmlspecialchars($report['reporter_username'] ?? 'Inconnu') ?></p>
                                <p><strong>Date :</strong> <?= htmlspecialchars($report['created_at'] ?? '-') ?></p>
                                <p><strong>Details :</strong> <?= nl2br(htmlspecialchars($report['details'] ?? 'Aucun detail supplementaire.')) ?></p>
                            </div>
                            <div class="detail-card">
                                <h4>Publication signalee</h4>
                                <p><strong>Titre :</strong> <?= htmlspecialchars($report['post_title'] ?? '[Publication supprimee]') ?></p>
                                <p><strong>Auteur :</strong> <?= htmlspecialchars($report['post_author_username'] ?? 'Inconnu') ?></p>
                                <p><strong>Cree le :</strong> <?= htmlspecialchars($report['post_created_at'] ?? '-') ?></p>
                                <p><strong>Apercu :</strong> <?= nl2br(htmlspecialchars($report['post_content'] ?? 'Cette publication n est plus disponible.')) ?></p>
                                <?php if ($reportImageSrc): ?>
                                    <img src="<?= htmlspecialchars($reportImageSrc) ?>" alt="Image de la publication signalee" class="post-image" style="max-height: 280px; width: auto; max-width: 100%; object-fit: contain;">
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="report-resolution-bar">
                            <?php if (($report['status'] ?? 'pending') !== 'resolved'): ?>
                                <form method="post">
                                    <input type="hidden" name="resolve_report_id" value="<?= (int) $report['id'] ?>">
                                    <label class="form-label" for="review-note">Note de revision pour l email de l auteur</label>
                                    <textarea id="review-note" name="review_note" class="form-control form-control-sm" rows="3" placeholder="Expliquez ce que l administrateur a examine ou decide..."></textarea>
                                    <button type="submit" class="btn btn-sm"><i class="fa-solid fa-circle-check"></i> Resoudre le signalement</button>
                                </form>
                            <?php else: ?>
                                <span class="status-banner">Ce signalement est deja resolu.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card card-primary shadow-sm">
                    <div class="card-body">
                        <p class="text-muted">Signalement introuvable.</p>
                        <a href="reports.php" class="btn btn-outline-secondary btn-sm">Retour aux signalements</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="style/community.js"></script>
</body>
</html>
