<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || (($_SESSION['user_role'] ?? 'user') !== 'admin')) {
    header('Location: /Web/index.php?action=login');
    exit;
}
if (!defined('SMART_ADMIN_VIEW')) {
    $target = '/Web/index.php?action=admin-community-review-post';
    if (isset($_GET['report_id'])) {
        $target .= '&report_id=' . urlencode((string) $_GET['report_id']);
    }
    if (isset($_GET['post_id'])) {
        $target .= '&post_id=' . urlencode((string) $_GET['post_id']);
    }
    header('Location: ' . $target);
    exit;
}
require_once __DIR__ . '/../../model/Connection.php';
require_once __DIR__ . '/../../model/Post.php';
require_once __DIR__ . '/../../model/Comment.php';

$adminName = $_SESSION['user_name'] ?? 'Admin';
$myId = 1;
$postModel = new Post(config::getConnexion());
$commentModel = new Comment(config::getConnexion());
$reportId = isset($_GET['report_id']) ? (int) $_GET['report_id'] : 0;
$postId = isset($_GET['post_id']) ? (int) $_GET['post_id'] : 0;

$report = $reportId > 0 ? $postModel->getReportById($reportId) : null;
$post = $postId > 0 ? $postModel->getPostById($postId) : null;
$comments = $post ? $commentModel->getComments($postId) : [];

function resolvePostImageSrcForReview($image)
{
    if (!$image) {
        return null;
    }

    if (strpos($image, '/Web/view/post_uploads/posts/') === 0) {
        return $image;
    }

    return null;
}

function organizeReviewComments($comments)
{
    $topLevelComments = [];
    $repliesByParent = [];

    foreach ($comments as $comment) {
        $parentCommentId = $comment['parent_comment_id'] ?? null;
        if (empty($parentCommentId)) {
            $topLevelComments[] = $comment;
            continue;
        }

        $repliesByParent[(int) $parentCommentId][] = $comment;
    }

    return [$topLevelComments, $repliesByParent];
}

[$topLevelComments, $repliesByParent] = organizeReviewComments($comments);
$postImageSrc = $post ? resolvePostImageSrcForReview($post['image'] ?? null) : null;
?>
        <div class="container">
            <h1 class="mb-4"><i class="fa-solid fa-magnifying-glass"></i> Examiner la publication signalee</h1>

            <?php if ($report): ?>
                <div class="review-topbar">
                    <a href="/Web/index.php?action=admin-community-report-details&id=<?= (int) $report['id'] ?>" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Retour au signalement</a>
                    <span class="status-pill status-<?= htmlspecialchars($report['status'] ?: 'pending') ?>">
                        Signalement <?= htmlspecialchars(($report['status'] ?? 'pending') === 'resolved' ? 'resolu' : 'en attente') ?>
                    </span>
                </div>
            <?php endif; ?>

            <?php if ($post): ?>
                <div class="card card-primary shadow-sm moderation-card">
                    <div class="card-header">
                        <h3 class="card-title">Moderation de la publication</h3>
                    </div>
                    <div class="card-body">
                        <div class="detail-grid">
                            <div class="detail-card">
                                <h4>Contexte du signalement</h4>
                                <?php if ($report): ?>
                                    <p><strong>Raison :</strong> <?= htmlspecialchars(ucwords(str_replace('_', ' ', $report['reason'] ?? ''))) ?></p>
                                    <p><strong>Signale par :</strong> <?= htmlspecialchars($report['reporter_username'] ?? 'Inconnu') ?></p>
                                    <p><strong>Details :</strong> <?= nl2br(htmlspecialchars($report['details'] ?? 'Aucun detail supplementaire.')) ?></p>
                                <?php else: ?>
                                    <p class="text-muted">Aucun detail de signalement trouve pour cette session de moderation.</p>
                                <?php endif; ?>
                            </div>
                            <div class="detail-card">
                                <h4>Apercu de la publication</h4>
                                <p><strong>ID publication :</strong> <?= (int) $post['id'] ?></p>
                                <p><strong>Titre :</strong> <?= htmlspecialchars($post['title']) ?></p>
                                <p><strong>Contenu :</strong> <?= nl2br(htmlspecialchars($post['content'])) ?></p>
                                <?php if ($postImageSrc): ?>
                                    <img src="<?= htmlspecialchars($postImageSrc) ?>" alt="Image de la publication" class="post-image" style="max-height: 320px; width: auto; max-width: 100%; object-fit: contain;">
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="edit-form moderation-edit-form">
                            <h4>Moderer la publication</h4>
                            <input type="text" id="review-title" class="form-control mb-2" value="<?= htmlspecialchars($post['title']) ?>">
                            <textarea id="review-content" class="form-control mb-2"><?= htmlspecialchars($post['content']) ?></textarea>
                            <?php if ($postImageSrc): ?>
                                <div class="mb-2 d-flex align-items-center" id="review-post-image-container">
                                    <img src="<?= htmlspecialchars($postImageSrc) ?>" class="img-thumbnail me-2" style="max-width: 96px; max-height: 96px; object-fit: contain;" alt="Image de la publication">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeReviewImage()">
                                        <i class="fas fa-trash"></i> Retirer l image
                                    </button>
                                </div>
                            <?php endif; ?>
                            <div class="form-group">
                                <label class="form-label">Remplacer l image (optionnel)</label>
                                <input type="file" id="review-image" class="form-control" accept="image/*">
                            </div>
                            <div class="report-action-row">
                                <button class="btn btn-sm" onclick="saveReviewedPost(<?= (int) $post['id'] ?>)">Enregistrer les modifications</button>
                                <button class="btn btn-outline-danger btn-sm" onclick="deleteReviewedPost(<?= (int) $post['id'] ?>)">Supprimer la publication</button>
                                <?php if ($report && ($report['status'] ?? 'pending') !== 'resolved'): ?>
                                    <button class="btn btn-success btn-sm" onclick="resolveReviewedReport(<?= (int) $report['id'] ?>)">Resoudre le signalement</button>
                                <?php endif; ?>
                            </div>
                            <?php if ($report && ($report['status'] ?? 'pending') !== 'resolved'): ?>
                                <textarea id="review-resolution-note" class="form-control form-control-sm" rows="3" placeholder="Note de revision optionnelle pour l email de l auteur..."></textarea>
                            <?php endif; ?>
                            <div id="review-feedback" class="review-feedback"></div>
                        </div>

                        <div class="comments-section mt-4">
                            <h6><i class="fas fa-comments"></i> Commentaires (<?= count($comments) ?>)</h6>
                            <?php if (!empty($topLevelComments)): ?>
                                <?php foreach ($topLevelComments as $comment): ?>
                                    <div class="comment-item mb-2 p-3 position-relative" id="review-comment-<?= (int) $comment['id'] ?>">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <small class="text-muted"><i class="fas fa-user"></i> <?= htmlspecialchars($comment['username']) ?></small>
                                            <div class="btn-group btn-group-sm">
                                                <span class="text-muted"><?= htmlspecialchars($comment['created_at']) ?></span>
                                                <button class="btn btn-outline-info btn-sm" onclick="toggleReviewCommentEdit(<?= (int) $comment['id'] ?>)" title="Modifier le commentaire">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-danger btn-sm" onclick="deleteReviewComment(<?= (int) $comment['id'] ?>)" title="Supprimer le commentaire">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div id="review-comment-text-<?= (int) $comment['id'] ?>"><?= nl2br(htmlspecialchars($comment['comment_text'])) ?></div>
                                        <div id="review-comment-edit-<?= (int) $comment['id'] ?>" class="comment-edit-form mt-2" style="display: none;">
                                            <textarea id="review-comment-input-<?= (int) $comment['id'] ?>" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($comment['comment_text']) ?></textarea>
                                            <div class="mt-1">
                                                <button class="btn btn-success btn-sm" onclick="saveReviewComment(<?= (int) $comment['id'] ?>)">Enregistrer</button>
                                                <button class="btn btn-secondary btn-sm" onclick="toggleReviewCommentEdit(<?= (int) $comment['id'] ?>)">Annuler</button>
                                            </div>
                                        </div>
                                        <?php if (!empty($repliesByParent[$comment['id']])): ?>
                                            <div class="mt-3" style="margin-left: 28px;">
                                                <?php foreach ($repliesByParent[$comment['id']] as $reply): ?>
                                                    <div class="comment-item mb-2 p-3 position-relative" id="review-comment-<?= (int) $reply['id'] ?>">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <small class="text-muted"><i class="fas fa-user"></i> <?= htmlspecialchars($reply['username']) ?></small>
                                                            <div class="btn-group btn-group-sm">
                                                                <span class="text-muted"><?= htmlspecialchars($reply['created_at']) ?></span>
                                                                <button class="btn btn-outline-info btn-sm" onclick="toggleReviewCommentEdit(<?= (int) $reply['id'] ?>)" title="Modifier la reponse">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-outline-danger btn-sm" onclick="deleteReviewComment(<?= (int) $reply['id'] ?>)" title="Supprimer la reponse">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div id="review-comment-text-<?= (int) $reply['id'] ?>"><?= nl2br(htmlspecialchars($reply['comment_text'])) ?></div>
                                                        <div id="review-comment-edit-<?= (int) $reply['id'] ?>" class="comment-edit-form mt-2" style="display: none;">
                                                            <textarea id="review-comment-input-<?= (int) $reply['id'] ?>" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($reply['comment_text']) ?></textarea>
                                                            <div class="mt-1">
                                                                <button class="btn btn-success btn-sm" onclick="saveReviewComment(<?= (int) $reply['id'] ?>)">Enregistrer</button>
                                                                <button class="btn btn-secondary btn-sm" onclick="toggleReviewCommentEdit(<?= (int) $reply['id'] ?>)">Annuler</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">Cette publication n a pas encore de commentaires.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card card-primary shadow-sm">
                    <div class="card-body">
                        <p class="text-muted">La publication signalee n est plus disponible.</p>
                        <?php if ($report && ($report['status'] ?? 'pending') !== 'resolved'): ?>
                            <textarea id="review-resolution-note" class="form-control form-control-sm" rows="3" placeholder="Note de revision optionnelle pour l email de l auteur..."></textarea>
                            <button class="btn btn-sm" onclick="resolveReviewedReport(<?= (int) $report['id'] ?>)">Resoudre le signalement</button>
                        <?php endif; ?>
                        <div id="review-feedback" class="review-feedback"></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    <script src="/Web/view/back/style/community.js"></script>
    <script>
        let reviewImageToRemove = false;

        function setReviewFeedback(message, isError = false) {
            const feedback = document.getElementById('review-feedback');
            if (!feedback) return;
            feedback.textContent = message;
            feedback.className = `review-feedback${isError ? ' is-error' : ' is-success'}`;
        }

        function removeReviewImage() {
            if (!confirm("Retirer cette image de la publication ?")) return;
            reviewImageToRemove = true;
            const container = document.getElementById('review-post-image-container');
            if (container) {
                container.innerHTML = '<small class="text-success"><i class="fas fa-check-circle"></i> L image sera supprimee lors de l enregistrement.</small>';
            }
        }

        function toggleReviewCommentEdit(commentId) {
            const editBlock = document.getElementById(`review-comment-edit-${commentId}`);
            const displayBlock = document.getElementById(`review-comment-text-${commentId}`);
            if (!editBlock || !displayBlock) return;

            const isVisible = editBlock.style.display === 'block';
            editBlock.style.display = isVisible ? 'none' : 'block';
            displayBlock.style.display = isVisible ? 'block' : 'none';
        }

        function saveReviewComment(commentId) {
            const input = document.getElementById(`review-comment-input-${commentId}`);
            if (!input) return;

            const content = input.value.trim();
            if (!content) {
                setReviewFeedback('Le commentaire ne peut pas etre vide.', true);
                return;
            }

            fetch('/Web/controller/commentController.php?action=admin_update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${commentId}&content=${encodeURIComponent(content)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(`review-comment-text-${commentId}`).innerHTML = content.replace(/\n/g, '<br>');
                        toggleReviewCommentEdit(commentId);
                        setReviewFeedback('Commentaire modifie avec succes.');
                    } else {
                        setReviewFeedback(data.message || 'Impossible de modifier le commentaire.', true);
                    }
                });
        }

        function deleteReviewComment(commentId) {
            if (!confirm('Supprimer definitivement ce commentaire ?')) return;

            fetch('/Web/controller/commentController.php?action=admin_delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${commentId}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const commentElement = document.getElementById(`review-comment-${commentId}`);
                        if (commentElement) {
                            commentElement.remove();
                        }
                        setReviewFeedback('Commentaire supprime avec succes.');
                    } else {
                        setReviewFeedback(data.message || 'Impossible de supprimer le commentaire.', true);
                    }
                });
        }

        function saveReviewedPost(postId) {
            const title = document.getElementById('review-title').value;
            const content = document.getElementById('review-content').value;
            const imageInput = document.getElementById('review-image');

            const formData = new FormData();
            formData.append('id', postId);
            formData.append('title', title);
            formData.append('content', content);
            if (reviewImageToRemove) {
                formData.append('remove_image', '1');
            }
            if (imageInput && imageInput.files[0]) {
                formData.append('image', imageInput.files[0]);
            }

            fetch('/Web/controller/postController.php?action=update', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        setReviewFeedback('Publication modifiee avec succes.');
                        window.location.reload();
                    } else {
                        setReviewFeedback(data.message || 'Impossible de modifier la publication.', true);
                    }
                });
        }

        function deleteReviewedPost(postId) {
            if (!confirm("Supprimer definitivement cette publication ?")) return;

            fetch('/Web/controller/postController.php?action=delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${postId}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        setReviewFeedback('Publication supprimee avec succes.');
                        window.location.reload();
                    } else {
                        setReviewFeedback(data.message || 'Impossible de supprimer la publication.', true);
                    }
                });
        }

        function resolveReviewedReport(reportId) {
            const noteField = document.getElementById('review-resolution-note');
            const reviewNote = noteField ? noteField.value.trim() : '';

            fetch('/Web/view/back/report_resolve.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `report_id=${reportId}&review_note=${encodeURIComponent(reviewNote)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        setReviewFeedback('Signalement resolu avec succes.');
                        window.location.reload();
                    } else {
                        setReviewFeedback(data.message || 'Impossible de resoudre le signalement.', true);
                    }
                });
        }
    </script>
