<?php
require_once '../../model/connection.php';
require_once '../../model/Post.php';
require_once '../../model/Comment.php';

$myId = 1;
$sessionUserName = $_SESSION['user_name'] ?? 'User';
$isLoggedIn = isset($_SESSION['user_id']) || $myId === 1;

$postModel = new Post(config::getConnexion());
$posts = $postModel->getAllPosts();
$commentModel = new Comment(config::getConnexion());

function resolvePostImageSrc($image)
{
    if (!$image) {
        return null;
    }

    if (strpos($image, 'data:image/') === 0) {
        return $image;
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
    <title>Communauté</title>
    <link rel="stylesheet" href="../backOffice/style/community.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>
    <nav class="navbar">
        <div class="navbar-brand">
            <a href="community.php" class="brand-link">
                <img
                    src="../backOffice/style/logo.png"
                    alt="Smart Nutrition"
                    class="brand-logo navbar-preview-logo"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                <span class="brand-fallback"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
            </a>
        </div>
        <ul class="navbar-menu">
            <li><a href="community.php" class="nav-link active"><i class="fa-solid fa-users"></i> Community</a></li>
            <li><a href="#new-post-panel" class="nav-link"><i class="fa-solid fa-pen-to-square"></i> New Post</a></li>
            <li><a href="#posts-container" class="nav-link"><i class="fa-solid fa-newspaper"></i> Posts</a></li>
        </ul>
        <div class="navbar-footer">
            <button type="button" id="themeToggle" class="nav-link theme-toggle" aria-label="Changer le mode de couleur" aria-pressed="false">
                <i class="fa-solid fa-moon"></i> Dark
            </button>
            <?php if ($isLoggedIn): ?>
                <p class="user-info">Connected: <strong><?= htmlspecialchars($sessionUserName) ?></strong></p>
            <?php endif; ?>
        </div>
    </nav>
    <div class="main-content">
        <div class="container">
            <h1 class="mb-4"><i class="fas fa-users"></i> Communauté</h1>

            <div class="card card-primary shadow-sm mb-5" id="new-post-panel">
                <div class="card-header">
                    <h3 class="card-title">Quoi de neuf ?</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <input type="text" id="new-title" class="form-control mb-2" placeholder="Titre de votre publication">
                        <textarea id="new-content" class="form-control" rows="3" placeholder="Écrivez votre message ici..."></textarea>
                        <div class="form-group mt-3">
                            <label class="form-label">Image (optionnel)</label>
                            <input type="file" id="new-image" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <button onclick="submitPost()" class="btn">Publier</button>
                </div>
            </div>

            <div id="posts-container">
                <?php if (!empty($posts)): ?>
                    <?php foreach ($posts as $post): ?>
                        <?php $postImageSrc = resolvePostImageSrc($post['image'] ?? null); ?>
                        <div class="post-card" id="post-<?php echo $post['id']; ?>">
                            <div class="post-header">
                                <div>
                                    <strong><i class="fas fa-user text-muted"></i> <?php echo htmlspecialchars($post['username']); ?></strong>
                                    <small class="text-muted ml-2"><?php echo $post['created_at']; ?></small>
                                </div>

                                <?php if ($post['user_id'] == $myId): ?>
                                    <div class="btn-group ml-auto">
                                        <button class="btn btn-sm btn-outline-info" onclick="toggleEdit(<?php echo $post['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deletePost(<?php echo $post['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="card-body">
                                <h5 id="display-title-<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></h5>
                                <p id="display-content-<?php echo $post['id']; ?>"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                                <?php if ($postImageSrc): ?>
                                    <img src="<?= htmlspecialchars($postImageSrc) ?>" alt="Post image" class="post-image mb-3 rounded" style="max-height: 250px; width: auto; max-width: 100%; height: auto; object-fit: contain;">
                                <?php endif; ?>

                                <div id="edit-block-<?php echo $post['id']; ?>" class="edit-form mt-3" style="display: none;">
                                    <input type="text" id="edit-title-<?php echo $post['id']; ?>" class="form-control mb-2" value="<?php echo htmlspecialchars($post['title']); ?>">
                                    <textarea id="edit-content-<?php echo $post['id']; ?>" class="form-control mb-2"><?php echo htmlspecialchars($post['content']); ?></textarea>
                                    <?php if ($postImageSrc): ?>
                                        <div class="mb-2 d-flex align-items-center" id="post-image-container-<?php echo $post['id']; ?>">
                                            <img src="<?= htmlspecialchars($postImageSrc) ?>" class="img-thumbnail me-2" style="max-width: 80px; max-height: 80px; object-fit: contain;" alt="Post image">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage(<?php echo $post['id']; ?>)">
                                                <i class="fas fa-trash"></i> Supprimer image
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                    <div class="form-group">
                                        <label class="form-label">Nouvelle image (optionnel)</label>
                                        <input type="file" id="edit-image-<?php echo $post['id']; ?>" class="form-control" accept="image/*">
                                    </div>
                                    <button class="btn btn-success btn-sm" onclick="saveEdit(<?php echo $post['id']; ?>)">Enregistrer</button>
                                    <button class="btn btn-secondary btn-sm" onclick="toggleEdit(<?php echo $post['id']; ?>)">Annuler</button>
                                </div>

                                <div class="comments-section mt-4">
                                    <h6><i class="fas fa-comments"></i> Commentaires (<?php echo count($commentModel->getComments($post['id'])); ?>)</h6>
                                    <div id="comments-list-<?php echo $post['id']; ?>">
                                        <?php
                                        $comments = $commentModel->getComments($post['id']);
                                        if (!empty($comments)):
                                        ?>
                                            <?php foreach ($comments as $comment): ?>
                                                <div class="comment-item mb-2 p-3 border-bottom position-relative" id="comment-<?php echo $comment['id']; ?>">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <small class="text-muted"><i class="fas fa-user"></i> <?php echo htmlspecialchars($comment['username']); ?></small>
                                                        <?php if ($comment['user_id'] == $myId): ?>
                                                            <div class="btn-group btn-group-sm">
                                                                <button class="btn btn-outline-info btn-sm" onclick="toggleCommentEdit(<?php echo $comment['id']; ?>)" title="Modifier">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-outline-danger btn-sm" onclick="deleteComment(<?php echo $comment['id']; ?>)" title="Supprimer">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div id="display-comment-text-<?php echo $comment['id']; ?>"><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></div>

                                                    <div id="edit-comment-block-<?php echo $comment['id']; ?>" class="comment-edit-form mt-2" style="display: none;">
                                                        <textarea id="edit-comment-text-<?php echo $comment['id']; ?>" class="form-control form-control-sm" rows="2"><?php echo htmlspecialchars($comment['comment_text']); ?></textarea>
                                                        <div class="mt-1">
                                                            <button class="btn btn-success btn-sm" onclick="saveCommentEdit(<?php echo $comment['id']; ?>)">Enregistrer</button>
                                                            <button class="btn btn-secondary btn-sm" onclick="toggleCommentEdit(<?php echo $comment['id']; ?>)">Annuler</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="text-center text-muted">Aucun commentaire pour le moment.</p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="comment-form mt-3">
                                        <textarea id="comment-content-<?php echo $post['id']; ?>" class="form-control" rows="2" placeholder="Ajoutez un commentaire..."></textarea>
                                        <button onclick="addComment(<?php echo $post['id']; ?>)" class="btn btn-outline-secondary btn-sm mt-2">Commenter</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">Aucune publication pour le moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../backOffice/style/community.js"></script>
    <script>
        function submitPost() {
            const title = document.getElementById('new-title').value;
            const content = document.getElementById('new-content').value;
            const imageInput = document.getElementById('new-image');

            const formData = new FormData();
            formData.append('title', title);
            formData.append('content', content);
            if (imageInput.files[0]) {
                formData.append('image', imageInput.files[0]);
            }

            fetch('../../controller/postController.php?action=create', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || "Erreur lors de la publication");
                    }
                });
        }

        function toggleEdit(id) {
            const block = document.getElementById(`edit-block-${id}`);
            block.style.display = (block.style.display === 'block') ? 'none' : 'block';
        }

        let imageToRemove = {};

        function removeImage(id) {
            if (confirm("Supprimer définitivement l'image ?")) {
                imageToRemove[id] = true;
                const container = document.getElementById(`post-image-container-${id}`);
                container.innerHTML = '<small class="text-success"><i class="fas fa-check-circle"></i> Image supprimée (sera effacée à l\'enregistrement)</small>';
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
                        alert(data.message || "Erreur lors de la modification");
                    }
                });
        }

        function deletePost(id) {
            if (!confirm("Voulez-vous vraiment supprimer ce post ?")) return;

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
                        document.getElementById(`post-${id}`).style.opacity = '0';
                        setTimeout(() => document.getElementById(`post-${id}`).remove(), 300);
                    }
                });
        }

        function addComment(postId) {
            const content = document.getElementById(`comment-content-${postId}`).value.trim();
            if (!content) return alert("Le commentaire ne peut pas être vide");

            fetch('../../controller/commentController.php?action=add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `post_id=${postId}&content=${encodeURIComponent(content)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(`comment-content-${postId}`).value = '';
                        location.reload();
                    } else {
                        alert(data.message || "Erreur lors de l'ajout du commentaire");
                    }
                });
        }

        function toggleCommentEdit(id) {
            const block = document.getElementById(`edit-comment-block-${id}`);
            const display = document.getElementById(`display-comment-text-${id}`);
            block.style.display = block.style.display === 'block' ? 'none' : 'block';
            display.style.display = block.style.display === 'block' ? 'none' : 'block';
        }

        function saveCommentEdit(id) {
            const content = document.getElementById(`edit-comment-text-${id}`).value;
            if (!content.trim()) return alert("Le commentaire ne peut pas être vide");

            fetch('../../controller/commentController.php?action=update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${id}&content=${encodeURIComponent(content)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(`display-comment-text-${id}`).innerHTML = content.replace(/\n/g, '<br>');
                        toggleCommentEdit(id);
                    } else {
                        alert(data.message || "Erreur modification");
                    }
                })
                .catch(() => {
                    alert("Erreur réseau ou serveur");
                });
        }

        function deleteComment(id) {
            if (!confirm("Supprimer ce commentaire ?")) return;

            fetch('../../controller/commentController.php?action=delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${id}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(`comment-${id}`).style.opacity = '0';
                        setTimeout(() => document.getElementById(`comment-${id}`).remove(), 300);
                    } else {
                        alert(data.message || 'Erreur suppression');
                    }
                });
        }
    </script>
</body>

</html>
