<?php
require_once '../../model/connection.php';
require_once '../../model/Post.php';
require_once '../../model/Comment.php';
require_once '../../model/AiModeration.php';
require_once '../../model/ImageModeration.php';

$adminName = $_SESSION['user_name'] ?? 'Admin';
$myId = 1;

$db = config::getConnexion();
$postModel = new Post($db);
$commentModel = new Comment($db);
$aiModeration = new AiModeration($db);
$imageModeration = new ImageModeration($db);

$posts = $postModel->getAllPosts();
$postModerationResults = $aiModeration->getResultsForContentType('post');
$postImageModerationResults = $imageModeration->getResultsForContentType('post');
$commentModerationResults = $aiModeration->getResultsForContentType('comment');
$moderationCounts = $aiModeration->getStatusCounts();
$imageModerationCounts = $imageModeration->getStatusCounts();

$postCommentData = [];
$totalComments = 0;
foreach ($posts as $post) {
    $comments = $commentModel->getComments((int) $post['id']);
    $postCommentData[(int) $post['id']] = $comments;
    $totalComments += count($comments);
}

function resolvePostImageSrc($image)
{
    if (!$image) {
        return null;
    }

    if (strpos($image, '/Web/view/post_uploads/posts/') === 0) {
        return $image;
    }

    return null;
}

function renderAiModerationBadge($moderation)
{
    if (!$moderation) {
        return '<span class="ai-badge ai-badge-missing"><i class="fa-solid fa-circle-question"></i> IA : non verifie</span>';
    }

    $status = strtolower((string) ($moderation['status'] ?? 'error'));
    $label = (string) ($moderation['label'] ?? 'inconnu');
    $score = isset($moderation['score']) ? round(((float) $moderation['score']) * 100) : 0;

    return '<span class="ai-badge ai-badge-' . htmlspecialchars($status) . '"><i class="fa-solid fa-wand-magic-sparkles"></i> IA : ' . htmlspecialchars($status === 'allowed' ? 'autorise' : ($status === 'review' ? 'a verifier' : $status)) . ' - ' . htmlspecialchars($label) . ' ' . $score . '%</span>';
}

function renderImageModerationBadge($moderation)
{
    if (!$moderation) {
        return '<span class="ai-badge ai-badge-missing"><i class="fa-solid fa-image"></i> Image : non verifiee</span>';
    }

    $status = strtolower((string) ($moderation['status'] ?? 'error'));
    $label = (string) ($moderation['label'] ?? 'inconnu');
    $score = isset($moderation['score']) ? round(((float) $moderation['score']) * 100) : 0;

    return '<span class="ai-badge ai-badge-' . htmlspecialchars($status) . '"><i class="fa-solid fa-image"></i> Image : ' . htmlspecialchars($status === 'allowed' ? 'autorisee' : ($status === 'review' ? 'a verifier' : $status)) . ' - ' . htmlspecialchars($label) . ' ' . $score . '%</span>';
}

function postExcerpt($text, $limit = 120)
{
    $text = trim((string) $text);
    if (strlen($text) <= $limit) {
        return $text;
    }

    return substr($text, 0, $limit - 3) . '...';
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Back Office - Communaute</title>
    <link rel="stylesheet" href="style/community.css?v=<?= filemtime(__DIR__ . '/style/community.css') ?>">
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
            <li><a href="community.php" class="nav-link active"><i class="fa-solid fa-users"></i> Communaute</a></li>
            <li><a href="reports.php" class="nav-link"><i class="fa-solid fa-flag"></i> Signalements</a></li>
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
            <h1 class="mb-4"><i class="fas fa-user-shield"></i> Moderation de la communaute</h1>

            <div class="admin-cards" id="moderation-panel">
                <div class="admin-card">
                    <h3><?= count($posts) ?></h3>
                    <p>Total des publications</p>
                </div>
                <div class="admin-card">
                    <h3><?= $totalComments ?></h3>
                    <p>Total des commentaires</p>
                </div>
                <div class="admin-card ai-card-review">
                    <h3><?= (int) ($moderationCounts['review'] ?? 0) ?></h3>
                    <p>Revision IA requise</p>
                </div>
                <div class="admin-card ai-card-allowed">
                    <h3><?= (int) ($moderationCounts['allowed'] ?? 0) ?></h3>
                    <p>IA autorisee</p>
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

            <div class="card card-primary shadow-sm admin-community-card">
                <div class="card-header admin-list-header">
                    <h3 class="card-title">Liste de moderation des publications</h3>
                    <span class="text-muted"><span data-table-count="community-posts"><?= count($posts) ?></span> item(s)</span>
                </div>
                <div class="card-body">
                    <?php if (!empty($posts)): ?>
                        <div class="admin-table-tools" data-table-controls="community-posts">
                            <label class="admin-search-field">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="search" class="form-control" data-table-search placeholder="Rechercher par titre, contenu, auteur...">
                            </label>
                            <label class="admin-select-field">
                                <span>Trier par</span>
                                <select class="form-control" data-table-sort>
                                    <option value="date_desc">Plus recentes</option>
                                    <option value="date_asc">Plus anciennes</option>
                                    <option value="title_asc">Titre A-Z</option>
                                    <option value="author_asc">Auteur A-Z</option>
                                    <option value="comments_desc">Plus commentees</option>
                                    <option value="ai_review">Revision IA d'abord</option>
                                </select>
                            </label>
                        </div>
                        <div class="reports-table-wrap">
                            <table class="reports-table admin-community-table" data-filter-table="community-posts">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Publication</th>
                                        <th>Auteur</th>
                                        <th>AI</th>
                                        <th>IA image</th>
                                        <th>Commentaires</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($posts as $post): ?>
                                        <?php
                                        $postId = (int) $post['id'];
                                        $postImageSrc = resolvePostImageSrc($post['image'] ?? null);
                                        $comments = $postCommentData[$postId] ?? [];
                                        $postModeration = $postModerationResults[$postId] ?? null;
                                        $imageModeration = $postImageModerationResults[$postId] ?? null;
                                        $postAiStatus = strtolower((string) ($postModeration['status'] ?? 'missing'));
                                        $postImageAiStatus = strtolower((string) ($imageModeration['status'] ?? 'missing'));
                                        $postSearchText = trim(implode(' ', [
                                            $postId,
                                            $post['title'] ?? '',
                                            $post['content'] ?? '',
                                            $post['username'] ?? '',
                                            $post['created_at'] ?? '',
                                            $postAiStatus,
                                            $postImageAiStatus
                                        ]));
                                        ?>
                                        <tr id="post-<?= $postId ?>"
                                            class="js-filter-row"
                                            data-row-id="<?= $postId ?>"
                                            data-search="<?= htmlspecialchars($postSearchText, ENT_QUOTES) ?>"
                                            data-title="<?= htmlspecialchars($post['title'] ?? '', ENT_QUOTES) ?>"
                                            data-author="<?= htmlspecialchars($post['username'] ?? 'Inconnu', ENT_QUOTES) ?>"
                                            data-date="<?= htmlspecialchars($post['created_at'] ?? '', ENT_QUOTES) ?>"
                                            data-comments="<?= count($comments) ?>"
                                            data-ai="<?= htmlspecialchars($postAiStatus, ENT_QUOTES) ?>"
                                            data-image-ai="<?= htmlspecialchars($postImageAiStatus, ENT_QUOTES) ?>">
                                            <td>#<?= $postId ?></td>
                                            <td class="admin-post-cell">
                                                <strong id="display-title-<?= $postId ?>"><?= htmlspecialchars($post['title']) ?></strong>
                                                <p id="display-content-<?= $postId ?>"><?= htmlspecialchars(postExcerpt($post['content'])) ?></p>
                                                <?php if ($postImageSrc): ?>
                                                    <span class="admin-media-chip"><i class="fa-solid fa-image"></i> Image</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($post['username'] ?? 'Inconnu') ?></td>
                                            <td><?= renderAiModerationBadge($postModeration) ?></td>
                                            <td><?= renderImageModerationBadge($imageModeration) ?></td>
                                            <td><?= count($comments) ?></td>
                                            <td><?= htmlspecialchars($post['created_at'] ?? '-') ?></td>
                                            <td class="admin-row-actions">
                                                <button class="btn btn-sm btn-outline-info" onclick="toggleEdit(<?= $postId ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deletePost(<?= $postId ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr class="admin-details-row" data-details-for="<?= $postId ?>">
                                            <td colspan="8">
                                                <details class="admin-post-details">
                                                    <summary>
                                                        <span><i class="fa-solid fa-clipboard-list"></i> Details de revision</span>
                                                        <span class="text-muted">Modifier la publication, verifier les commentaires</span>
                                                    </summary>

                                                    <div class="admin-details-grid">
                                                        <section class="admin-detail-panel">
                                                            <h4>Publication complete</h4>
                                                            <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                                                            <?php if ($postImageSrc): ?>
                                                                <img src="<?= htmlspecialchars($postImageSrc) ?>" alt="Image de la publication" class="admin-post-thumbnail">
                                                                <div class="ai-inline-status"><?= renderImageModerationBadge($postImageModerationResults[$postId] ?? null) ?></div>
                                                            <?php endif; ?>
                                                        </section>

                                                        <section class="admin-detail-panel">
                                                            <h4>Modifier la publication</h4>
                                                            <div id="edit-block-<?= $postId ?>" class="edit-form">
                                                                <input type="text" id="edit-title-<?= $postId ?>" class="form-control mb-2" value="<?= htmlspecialchars($post['title']) ?>">
                                                                <textarea id="edit-content-<?= $postId ?>" class="form-control mb-2"><?= htmlspecialchars($post['content']) ?></textarea>
                                                                <?php if ($postImageSrc): ?>
                                                                    <div class="mb-2 d-flex align-items-center" id="post-image-container-<?= $postId ?>">
                                                                        <img src="<?= htmlspecialchars($postImageSrc) ?>" class="img-thumbnail me-2" style="max-width: 80px; max-height: 80px; object-fit: contain;" alt="Image de la publication">
                                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage(<?= $postId ?>)">
                                                                            <i class="fas fa-trash"></i> Retirer l image
                                                                        </button>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <label class="form-label">Remplacer l image</label>
                                                                <input type="file" id="edit-image-<?= $postId ?>" class="form-control" accept="image/*">
                                                                <div class="admin-form-actions">
                                                                    <button class="btn btn-success btn-sm" onclick="saveEdit(<?= $postId ?>)">Enregistrer</button>
                                                                </div>
                                                            </div>
                                                        </section>
                                                    </div>

                                                    <section class="admin-comments-list">
                                                        <h4>Commentaires</h4>
                                                        <?php if (!empty($comments)): ?>
                                                            <?php foreach ($comments as $comment): ?>
                                                                <?php $commentId = (int) $comment['id']; ?>
                                                                <div class="admin-comment-row" id="comment-<?= $commentId ?>">
                                                                    <div>
                                                                        <strong><?= htmlspecialchars($comment['username'] ?? 'Inconnu') ?></strong>
                                                                        <span class="text-muted"><?= htmlspecialchars($comment['created_at'] ?? '-') ?></span>
                                                                        <div><?= renderAiModerationBadge($commentModerationResults[$commentId] ?? null) ?></div>
                                                                    </div>
                                                                    <div class="admin-comment-text" id="display-comment-text-<?= $commentId ?>"><?= nl2br(htmlspecialchars($comment['comment_text'])) ?></div>
                                                                    <div class="admin-row-actions">
                                                                        <button class="btn btn-outline-info btn-sm" onclick="toggleCommentEdit(<?= $commentId ?>)" title="Modifier">
                                                                            <i class="fas fa-edit"></i>
                                                                        </button>
                                                                        <button class="btn btn-outline-danger btn-sm" onclick="deleteComment(<?= $commentId ?>)" title="Supprimer">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                    <div id="edit-comment-block-<?= $commentId ?>" class="comment-edit-form admin-comment-edit" style="display: none;">
                                                                        <textarea id="edit-comment-text-<?= $commentId ?>" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($comment['comment_text']) ?></textarea>
                                                                        <button class="btn btn-success btn-sm" onclick="saveCommentEdit(<?= $commentId ?>)">Enregistrer</button>
                                                                        <button class="btn btn-secondary btn-sm" onclick="toggleCommentEdit(<?= $commentId ?>)">Annuler</button>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <p class="text-muted">Aucun commentaire.</p>
                                                        <?php endif; ?>
                                                    </section>
                                                </details>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">Aucune publication pour le moment.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="style/community.js?v=<?= filemtime(__DIR__ . '/style/community.js') ?>"></script>
    <script>
        let imageToRemove = {};

        function toggleEdit(id) {
            const block = document.getElementById(`edit-block-${id}`);
            if (!block) return;
            const details = block.closest('details');
            if (details) details.open = true;
            block.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function removeImage(id) {
            if (confirm("Retirer cette image ?")) {
                imageToRemove[id] = true;
                const container = document.getElementById(`post-image-container-${id}`);
                if (container) {
                    container.innerHTML = '<small class="text-success"><i class="fas fa-check-circle"></i> L image sera supprimee lors de l enregistrement</small>';
                }
            }
        }

        function saveEdit(id) {
            const title = document.getElementById(`edit-title-${id}`).value;
            const content = document.getElementById(`edit-content-${id}`).value;
            const imageInput = document.getElementById(`edit-image-${id}`);

            const formData = new FormData();
            formData.append('id', id);
            formData.append('title', title);
            formData.append('content', content);
            if (imageToRemove[id]) {
                formData.append('remove_image', '1');
            }
            if (imageInput.files[0]) {
                formData.append('image', imageInput.files[0]);
            }

            fetch('../../controller/postController.php?action=update', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || "Echec de la modification");
                    }
                });
        }

        function deletePost(id) {
            if (!confirm("Supprimer cette publication ?")) return;

            fetch('../../controller/postController.php?action=delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${id}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || "Echec de la suppression");
                    }
                });
        }

        function toggleCommentEdit(id) {
            const block = document.getElementById(`edit-comment-block-${id}`);
            const display = document.getElementById(`display-comment-text-${id}`);
            if (!block || !display) return;
            block.style.display = block.style.display === 'block' ? 'none' : 'block';
            display.style.display = block.style.display === 'block' ? 'none' : 'block';
        }

        function saveCommentEdit(id) {
            const content = document.getElementById(`edit-comment-text-${id}`).value;
            if (!content.trim()) return alert("Le commentaire ne peut pas etre vide");

            fetch('../../controller/commentController.php?action=admin_update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${id}&content=${encodeURIComponent(content)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || "Echec de la modification");
                    }
                });
        }

        function deleteComment(id) {
            if (!confirm("Supprimer ce commentaire ?")) return;

            fetch('../../controller/commentController.php?action=admin_delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${id}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(`comment-${id}`).remove();
                    } else {
                        alert(data.message || 'Echec de la suppression');
                    }
                });
        }
    </script>
</body>

</html>
