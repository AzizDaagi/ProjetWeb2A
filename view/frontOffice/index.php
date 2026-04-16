<?php
header('Location: community.php');
exit;
// Inclusion des fichiers nécessaires pour l'affichage initial
require_once '../model/Connection.php';
require_once '../model/Post.php';

// Simulation de l'utilisateur connecté (À remplacer par $_SESSION['user_id'] plus tard)
$myId = 1; 

$postModel = new Post(config::getConnexion());
$posts = $postModel->getAllPosts();

function resolvePostImageSrc($image) {
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="../backOffice/style/community.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="p-4 bg-light">

<div class="container">
    <h1 class="mb-4"><i class="fas fa-users"></i> Communauté</h1>

    <div class="card card-primary shadow-sm mb-5">
        <div class="card-header">
            <h3 class="card-title">Quoi de neuf ?</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <input type="text" id="new-title" class="form-control mb-2" placeholder="Titre de votre publication">
                <textarea id="new-content" class="form-control" rows="3" placeholder="Écrivez votre message ici..."></textarea>
            </div>
        </div>
        <div class="card-footer">
            <button onclick="submitPost()" class="btn btn-primary">Publier</button>
        </div>
    </div>

    <div id="posts-container">
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <?php $postImageSrc = resolvePostImageSrc($post['image'] ?? null); ?>
                <div class="card post-card shadow-sm" id="post-<?php echo $post['id']; ?>">
                    <div class="post-header">
                        <div>
                            <strong><i class="fas fa-user text-muted"></i> <?php echo htmlspecialchars($post['username']); ?></strong>
                            <small class="text-muted ml-2"><?php echo $post['created_at']; ?></small>
                        </div>

                        <?php if ($post['user_id'] == $myId): ?>
                            <div class="btn-group">
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
                            <img src="<?= htmlspecialchars($postImageSrc) ?>" alt="Post image" class="post-image mb-3 rounded">
                        <?php endif; ?>

                        <div id="edit-block-<?php echo $post['id']; ?>" class="edit-form mt-3">
                            <input type="text" id="edit-title-<?php echo $post['id']; ?>" class="form-control mb-2" value="<?php echo htmlspecialchars($post['title']); ?>">
                            <textarea id="edit-content-<?php echo $post['id']; ?>" class="form-control mb-2"><?php echo htmlspecialchars($post['content']); ?></textarea>
                            <button class="btn btn-success btn-sm" onclick="saveEdit(<?php echo $post['id']; ?>)">Enregistrer</button>
                            <button class="btn btn-secondary btn-sm" onclick="toggleEdit(<?php echo $post['id']; ?>)">Annuler</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-muted">Aucune publication pour le moment.</p>
        <?php endif; ?>
    </div>
</div>

<script>
// --- AJOUTER ---
function submitPost() {
    const title = document.getElementById('new-title').value;
    const content = document.getElementById('new-content').value;

    fetch('../controller/postController.php?action=create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `title=${encodeURIComponent(title)}&content=${encodeURIComponent(content)}`
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) location.reload(); 
        else alert("Erreur lors de la publication");
    });
}

// --- MODIFIER (UI) ---
function toggleEdit(id) {
    const block = document.getElementById(`edit-block-${id}`);
    block.style.display = (block.style.display === 'block') ? 'none' : 'block';
}

// --- ENREGISTRER MODIF ---
function saveEdit(id) {
    const title = document.getElementById(`edit-title-${id}`).value;
    const content = document.getElementById(`edit-content-${id}`).value;

    fetch('../controller/postController.php?action=update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}&title=${encodeURIComponent(title)}&content=${encodeURIComponent(content)}`
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            document.getElementById(`display-title-${id}`).innerText = title;
            document.getElementById(`display-content-${id}`).innerText = content;
            toggleEdit(id);
        } else {
            alert(data.message || "Erreur lors de la modification");
        }
    });
}

// --- SUPPRIMER ---
function deletePost(id) {
    if(!confirm("Voulez-vous vraiment supprimer ce post ?")) return;

    fetch('../controller/postController.php?action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}`
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            document.getElementById(`post-${id}`).style.opacity = '0';
            setTimeout(() => document.getElementById(`post-${id}`).remove(), 300);
        }
    });
}
</script>

</body>
</html>
