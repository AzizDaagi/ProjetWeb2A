<?php
require_once '../../model/connection.php';
require_once '../../model/Post.php';
require_once '../../model/Comment.php';

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
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Back Office - Review Post</title>
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
            <h1 class="mb-4"><i class="fa-solid fa-magnifying-glass"></i> Review Reported Post</h1>

            <?php if ($report): ?>
                <div class="review-topbar">
                    <a href="report_details.php?id=<?= (int) $report['id'] ?>" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Back to Report</a>
                    <span class="status-pill status-<?= htmlspecialchars($report['status'] ?: 'pending') ?>">
                        Report <?= htmlspecialchars(ucfirst($report['status'] ?: 'pending')) ?>
                    </span>
                </div>
            <?php endif; ?>

            <?php if ($post): ?>
                <div class="card card-primary shadow-sm moderation-card">
                    <div class="card-header">
                        <h3 class="card-title">Post Moderation</h3>
                    </div>
                    <div class="card-body">
                        <div class="detail-grid">
                            <div class="detail-card">
                                <h4>Report Context</h4>
                                <?php if ($report): ?>
                                    <p><strong>Reason:</strong> <?= htmlspecialchars(ucwords(str_replace('_', ' ', $report['reason'] ?? ''))) ?></p>
                                    <p><strong>Reporter:</strong> <?= htmlspecialchars($report['reporter_username'] ?? 'Unknown') ?></p>
                                    <p><strong>Details:</strong> <?= nl2br(htmlspecialchars($report['details'] ?? 'No extra details provided.')) ?></p>
                                <?php else: ?>
                                    <p class="text-muted">No report details were found for this moderation session.</p>
                                <?php endif; ?>
                            </div>
                            <div class="detail-card">
                                <h4>Post Snapshot</h4>
                                <p><strong>Post ID:</strong> <?= (int) $post['id'] ?></p>
                                <p><strong>Title:</strong> <?= htmlspecialchars($post['title']) ?></p>
                                <p><strong>Content:</strong> <?= nl2br(htmlspecialchars($post['content'])) ?></p>
                                <?php if ($postImageSrc): ?>
                                    <img src="<?= htmlspecialchars($postImageSrc) ?>" alt="Post image" class="post-image" style="max-height: 320px; width: auto; max-width: 100%; object-fit: contain;">
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="edit-form moderation-edit-form">
                            <h4>Moderate Post</h4>
                            <input type="text" id="review-title" class="form-control mb-2" value="<?= htmlspecialchars($post['title']) ?>">
                            <textarea id="review-content" class="form-control mb-2"><?= htmlspecialchars($post['content']) ?></textarea>
                            <?php if ($postImageSrc): ?>
                                <div class="mb-2 d-flex align-items-center" id="review-post-image-container">
                                    <img src="<?= htmlspecialchars($postImageSrc) ?>" class="img-thumbnail me-2" style="max-width: 96px; max-height: 96px; object-fit: contain;" alt="Post image">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeReviewImage()">
                                        <i class="fas fa-trash"></i> Remove image
                                    </button>
                                </div>
                            <?php endif; ?>
                            <div class="form-group">
                                <label class="form-label">Replace image (optional)</label>
                                <input type="file" id="review-image" class="form-control" accept="image/*">
                            </div>
                            <div class="report-action-row">
                                <button class="btn btn-sm" onclick="saveReviewedPost(<?= (int) $post['id'] ?>)">Save Changes</button>
                                <button class="btn btn-outline-danger btn-sm" onclick="deleteReviewedPost(<?= (int) $post['id'] ?>)">Delete Post</button>
                                <?php if ($report && ($report['status'] ?? 'pending') !== 'resolved'): ?>
                                    <button class="btn btn-success btn-sm" onclick="resolveReviewedReport(<?= (int) $report['id'] ?>)">Resolve Report</button>
                                <?php endif; ?>
                            </div>
                            <div id="review-feedback" class="review-feedback"></div>
                        </div>

                        <div class="comments-section mt-4">
                            <h6><i class="fas fa-comments"></i> Comments (<?= count($comments) ?>)</h6>
                            <?php if (!empty($topLevelComments)): ?>
                                <?php foreach ($topLevelComments as $comment): ?>
                                    <div class="comment-item mb-2 p-3 position-relative" id="review-comment-<?= (int) $comment['id'] ?>">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <small class="text-muted"><i class="fas fa-user"></i> <?= htmlspecialchars($comment['username']) ?></small>
                                            <div class="btn-group btn-group-sm">
                                                <span class="text-muted"><?= htmlspecialchars($comment['created_at']) ?></span>
                                                <button class="btn btn-outline-info btn-sm" onclick="toggleReviewCommentEdit(<?= (int) $comment['id'] ?>)" title="Edit comment">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-danger btn-sm" onclick="deleteReviewComment(<?= (int) $comment['id'] ?>)" title="Delete comment">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div id="review-comment-text-<?= (int) $comment['id'] ?>"><?= nl2br(htmlspecialchars($comment['comment_text'])) ?></div>
                                        <div id="review-comment-edit-<?= (int) $comment['id'] ?>" class="comment-edit-form mt-2" style="display: none;">
                                            <textarea id="review-comment-input-<?= (int) $comment['id'] ?>" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($comment['comment_text']) ?></textarea>
                                            <div class="mt-1">
                                                <button class="btn btn-success btn-sm" onclick="saveReviewComment(<?= (int) $comment['id'] ?>)">Save</button>
                                                <button class="btn btn-secondary btn-sm" onclick="toggleReviewCommentEdit(<?= (int) $comment['id'] ?>)">Cancel</button>
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
                                                                <button class="btn btn-outline-info btn-sm" onclick="toggleReviewCommentEdit(<?= (int) $reply['id'] ?>)" title="Edit reply">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-outline-danger btn-sm" onclick="deleteReviewComment(<?= (int) $reply['id'] ?>)" title="Delete reply">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div id="review-comment-text-<?= (int) $reply['id'] ?>"><?= nl2br(htmlspecialchars($reply['comment_text'])) ?></div>
                                                        <div id="review-comment-edit-<?= (int) $reply['id'] ?>" class="comment-edit-form mt-2" style="display: none;">
                                                            <textarea id="review-comment-input-<?= (int) $reply['id'] ?>" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($reply['comment_text']) ?></textarea>
                                                            <div class="mt-1">
                                                                <button class="btn btn-success btn-sm" onclick="saveReviewComment(<?= (int) $reply['id'] ?>)">Save</button>
                                                                <button class="btn btn-secondary btn-sm" onclick="toggleReviewCommentEdit(<?= (int) $reply['id'] ?>)">Cancel</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">This post does not have comments.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card card-primary shadow-sm">
                    <div class="card-body">
                        <p class="text-muted">The reported post is no longer available.</p>
                        <?php if ($report && ($report['status'] ?? 'pending') !== 'resolved'): ?>
                            <button class="btn btn-sm" onclick="resolveReviewedReport(<?= (int) $report['id'] ?>)">Resolve Report</button>
                        <?php endif; ?>
                        <div id="review-feedback" class="review-feedback"></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="style/community.js"></script>
    <script>
        let reviewImageToRemove = false;

        function setReviewFeedback(message, isError = false) {
            const feedback = document.getElementById('review-feedback');
            if (!feedback) return;
            feedback.textContent = message;
            feedback.className = `review-feedback${isError ? ' is-error' : ' is-success'}`;
        }

        function removeReviewImage() {
            if (!confirm("Remove this image from the post?")) return;
            reviewImageToRemove = true;
            const container = document.getElementById('review-post-image-container');
            if (container) {
                container.innerHTML = '<small class="text-success"><i class="fas fa-check-circle"></i> Image will be removed when you save.</small>';
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
                setReviewFeedback('Comment content cannot be empty.', true);
                return;
            }

            fetch('../../controller/commentController.php?action=admin_update', {
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
                        setReviewFeedback('Comment updated successfully.');
                    } else {
                        setReviewFeedback(data.message || 'Unable to update the comment.', true);
                    }
                });
        }

        function deleteReviewComment(commentId) {
            if (!confirm('Delete this comment permanently?')) return;

            fetch('../../controller/commentController.php?action=admin_delete', {
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
                        setReviewFeedback('Comment deleted successfully.');
                    } else {
                        setReviewFeedback(data.message || 'Unable to delete the comment.', true);
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

            fetch('../../controller/postController.php?action=update', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        setReviewFeedback('Post updated successfully.');
                        window.location.reload();
                    } else {
                        setReviewFeedback(data.message || 'Unable to update the post.', true);
                    }
                });
        }

        function deleteReviewedPost(postId) {
            if (!confirm("Delete this post permanently?")) return;

            fetch('../../controller/postController.php?action=delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${postId}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        setReviewFeedback('Post deleted successfully.');
                        window.location.reload();
                    } else {
                        setReviewFeedback(data.message || 'Unable to delete the post.', true);
                    }
                });
        }

        function resolveReviewedReport(reportId) {
            fetch('report_resolve.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `report_id=${reportId}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        setReviewFeedback('Report resolved successfully.');
                        window.location.reload();
                    } else {
                        setReviewFeedback(data.message || 'Unable to resolve the report.', true);
                    }
                });
        }
    </script>
</body>
</html>
