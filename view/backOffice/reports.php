<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || (($_SESSION['user_role'] ?? 'user') !== 'admin')) {
    header('Location: /Web/index.php?action=login');
    exit;
}
if (!defined('SMART_ADMIN_VIEW')) {
    header('Location: /Web/index.php?action=admin-community-reports');
    exit;
}
require_once __DIR__ . '/../../model/connection.php';
require_once __DIR__ . '/../../model/Post.php';
require_once __DIR__ . '/../../model/AiModeration.php';
require_once __DIR__ . '/../../model/ImageModeration.php';

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
        <div class="container">
            <h1 class="mb-4"><i class="fa-solid fa-flag"></i> Centre des signalements</h1>

            <div class="admin-cards">
                <div class="admin-card">
                    <h3><?= count($reports) ?></h3>
                    <p>Total des signalements</p>
                </div>
                <div class="admin-card">
                    <h3><?= $pendingReports ?></h3>
                    <p>En attente</p>
                </div>
                <div class="admin-card">
                    <h3><?= $resolvedReports ?></h3>
                    <p>Resolus</p>
                </div>
                <div class="admin-card ai-card-review">
                    <h3><?= (int) ($moderationCounts['review'] ?? 0) ?></h3>
                    <p>Revision IA requise</p>
                </div>
                <div class="admin-card ai-card-error">
                    <h3><?= (int) ($moderationCounts['error'] ?? 0) ?></h3>
                    <p>Erreurs IA</p>
                </div>
                <div class="admin-card ai-card-review">
                    <h3><?= (int) ($imageModerationCounts['review'] ?? 0) ?></h3>
                    <p>Revision image requise</p>
                </div>
            </div>

            <div class="card card-primary shadow-sm">
                <div class="card-header admin-list-header">
                    <h3 class="card-title">Tous les signalements</h3>
                    <span class="text-muted"><span data-table-count="reports-list"><?= count($reports) ?></span> item(s)</span>
                </div>
                <div class="card-body">
                    <?php if (!empty($reports)): ?>
                        <div class="admin-table-tools" data-table-controls="reports-list">
                            <label class="admin-search-field">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="search" class="form-control" data-table-search placeholder="Rechercher par raison, publication, utilisateur...">
                            </label>
                            <label class="admin-select-field">
                                <span>Statut</span>
                                <select class="form-control" data-table-filter="status">
                                    <option value="">Tous</option>
                                    <option value="pending">En attente</option>
                                    <option value="resolved">Resolus</option>
                                </select>
                            </label>
                            <label class="admin-select-field">
                                <span>Trier par</span>
                                <select class="form-control" data-table-sort>
                                    <option value="date_desc">Plus recents</option>
                                    <option value="date_asc">Plus anciens</option>
                                    <option value="status_asc">Statut</option>
                                    <option value="reason_asc">Raison A-Z</option>
                                    <option value="reporter_asc">Utilisateur A-Z</option>
                                    <option value="title_asc">Publication A-Z</option>
                                    <option value="ai_review">Revision IA d'abord</option>
                                </select>
                            </label>
                        </div>
                        <div class="reports-table-wrap">
                            <table class="reports-table" data-filter-table="reports-list">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Statut</th>
                                        <th>Raison</th>
                                        <th>AI</th>
                                        <th>IA image</th>
                                        <th>Publication</th>
                                        <th>Signale par</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reports as $report): ?>
                                        <?php
                                        $reportId = (int) $report['id'];
                                        $reportStatus = (string) ($report['status'] ?: 'pending');
                                        $reportPostId = (int) ($report['post_id'] ?? 0);
                                        $reportAi = $postModerationResults[$reportPostId] ?? null;
                                        $reportImageAi = $postImageModerationResults[$reportPostId] ?? null;
                                        $reportAiStatus = strtolower((string) ($reportAi['status'] ?? 'missing'));
                                        $reportImageAiStatus = strtolower((string) ($reportImageAi['status'] ?? 'missing'));
                                        $reportReason = ucwords(str_replace('_', ' ', $report['reason'] ?? ''));
                                        $reportTitle = $report['post_title'] ?? '[Publication supprimee]';
                                        $reporterName = $report['reporter_username'] ?? 'Inconnu';
                                        $reportSearchText = trim(implode(' ', [
                                            $reportId,
                                            $reportStatus,
                                            $reportReason,
                                            $reportTitle,
                                            $reporterName,
                                            $report['created_at'] ?? '',
                                            $reportAiStatus,
                                            $reportImageAiStatus
                                        ]));
                                        ?>
                                        <tr class="js-filter-row"
                                            data-search="<?= htmlspecialchars($reportSearchText, ENT_QUOTES) ?>"
                                            data-status="<?= htmlspecialchars($reportStatus, ENT_QUOTES) ?>"
                                            data-reason="<?= htmlspecialchars($reportReason, ENT_QUOTES) ?>"
                                            data-title="<?= htmlspecialchars($reportTitle, ENT_QUOTES) ?>"
                                            data-reporter="<?= htmlspecialchars($reporterName, ENT_QUOTES) ?>"
                                            data-date="<?= htmlspecialchars($report['created_at'] ?? '', ENT_QUOTES) ?>"
                                            data-ai="<?= htmlspecialchars($reportAiStatus, ENT_QUOTES) ?>"
                                            data-image-ai="<?= htmlspecialchars($reportImageAiStatus, ENT_QUOTES) ?>">
                                            <td>#<?= (int) $report['id'] ?></td>
                                            <td>
                                                <span class="status-pill status-<?= htmlspecialchars($reportStatus) ?>">
                                                    <?= htmlspecialchars($reportStatus === 'resolved' ? 'Resolu' : 'En attente') ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($reportReason) ?></td>
                                            <td>
                                                <?php if ($reportAi): ?>
                                                    <span class="ai-badge ai-badge-<?= htmlspecialchars($reportAi['status']) ?>">
                                                        <?= htmlspecialchars(ucfirst($reportAi['status'])) ?> <?= (int) round(((float) $reportAi['score']) * 100) ?>%
                                                    </span>
                                                <?php else: ?>
                                                    <span class="ai-badge ai-badge-missing">Non verifie</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($reportImageAi): ?>
                                                    <span class="ai-badge ai-badge-<?= htmlspecialchars($reportImageAi['status']) ?>">
                                                        <?= htmlspecialchars(ucfirst($reportImageAi['status'])) ?> <?= (int) round(((float) $reportImageAi['score']) * 100) ?>%
                                                    </span>
                                                <?php else: ?>
                                                    <span class="ai-badge ai-badge-missing">Non verifie</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($reportTitle) ?></td>
                                            <td><?= htmlspecialchars($reporterName) ?></td>
                                            <td><?= htmlspecialchars($report['created_at'] ?? '-') ?></td>
                                            <td>
                                                <a class="btn btn-outline-secondary btn-sm" href="/Web/index.php?action=admin-community-report-details&id=<?= (int) $report['id'] ?>">
                                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Ouvrir
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Aucun signalement n a encore ete envoye.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
