<?php
require_once '../../model/connection.php';
require_once '../../model/Post.php';
require_once '../../model/Comment.php';
require_once '../../model/News.php';

$myId = 1;
$sessionUserName = $_SESSION['user_name'] ?? 'User';
$isLoggedIn = isset($_SESSION['user_id']) || $myId === 1;

$postModel = new Post(config::getConnexion());
$posts = $postModel->getAllPosts();
$commentModel = new Comment(config::getConnexion());

// Load news articles
$newsModel = new News(config::getConnexion());
News::createTableIfNotExists(config::getConnexion());
// Show only up to 3 items. Prefer nutrition and healthy meals, then add fitness.
$featuredNewsNutrition = $newsModel->getNewsByCategory('nutrition', 3);
$featuredNewsHealthTips = $newsModel->getNewsByCategory('health_tips', 1);
$featuredNewsFitness = $newsModel->getNewsByCategory('fitness', 1);

// Merge and keep only 3.
$featuredNews = array_merge($featuredNewsNutrition, $featuredNewsHealthTips, $featuredNewsFitness);
$featuredNews = array_slice($featuredNews, 0, 3);

$fallbackNews = [
    [
        'id' => 'fallback-healthy-meal-prep',
        'title' => 'Build Balanced Meal Prep Bowls for Busy Days',
        'summary' => 'Use lean protein, colorful vegetables, whole grains, and healthy fats to create filling meals that support steady energy.',
        'content' => '<p>A balanced meal prep bowl is easiest when you build it in layers. Start with a fiber-rich base like brown rice, quinoa, lentils, or roasted sweet potato. Add a protein such as grilled chicken, tuna, eggs, tofu, beans, or chickpeas. Then fill half the bowl with vegetables for volume, color, and micronutrients.</p><p>Finish with a healthy fat like avocado, olive oil dressing, nuts, or seeds. This mix helps meals feel satisfying while keeping energy more stable through the day.</p>',
        'image_url' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=900&q=80',
        'category' => 'nutrition',
        'source' => 'Smart Nutrition',
        'source_url' => '',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'id' => 'fallback-protein-breakfast',
        'title' => 'Simple High-Protein Breakfast Ideas That Feel Fresh',
        'summary' => 'Greek yogurt bowls, eggs with vegetables, oats with nuts, or smoothies can make breakfast more nourishing.',
        'content' => '<p>A good breakfast does not need to be complicated. Aim for protein, fiber, and a fruit or vegetable. Greek yogurt with berries and seeds, eggs with spinach and tomatoes, oatmeal with peanut butter, or a smoothie with milk, banana, and protein-rich yogurt can all work well.</p><p>Protein in the morning can help with fullness, while fiber supports digestion and more steady energy.</p>',
        'image_url' => 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&w=900&q=80',
        'category' => 'nutrition',
        'source' => 'Smart Nutrition',
        'source_url' => '',
        'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
    ],
    [
        'id' => 'fallback-hydration-meals',
        'title' => 'Hydrating Foods to Add to Healthy Meals',
        'summary' => 'Cucumber, citrus, berries, tomatoes, leafy greens, and soups can support hydration alongside water.',
        'content' => '<p>Water is still the main hydration tool, but meals can help too. Foods like cucumber, oranges, berries, tomatoes, zucchini, leafy greens, and broth-based soups add fluid plus useful vitamins and minerals.</p><p>Try adding a side salad, fruit snack, or vegetable soup to meals when you want something light, fresh, and supportive of hydration.</p>',
        'image_url' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=900&q=80',
        'category' => 'nutrition',
        'source' => 'Smart Nutrition',
        'source_url' => '',
        'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
    ]
];

$existingNewsKeys = [];
foreach ($featuredNews as $newsArticle) {
    $existingNewsKeys[] = strtolower(trim($newsArticle['title'] ?? ''));
}

foreach ($fallbackNews as $fallbackArticle) {
    if (count($featuredNews) >= 3) {
        break;
    }

    $fallbackKey = strtolower($fallbackArticle['title']);
    if (in_array($fallbackKey, $existingNewsKeys, true)) {
        continue;
    }

    $featuredNews[] = $fallbackArticle;
    $existingNewsKeys[] = $fallbackKey;
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

function organizeCommentsByThread($comments)
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

function getReactionOptions()
{
    return [
        'love' => ['label' => 'Love', 'icon' => 'fa-heart'],
        'laugh' => ['label' => 'Laugh', 'icon' => 'fa-face-laugh-squint'],
        'sad' => ['label' => 'Sad', 'icon' => 'fa-face-sad-tear'],
        'angry' => ['label' => 'Angry', 'icon' => 'fa-face-angry']
    ];
}

function getReportReasonOptions()
{
    return [
        'spam' => 'Spam',
        'harassment' => 'Harassment',
        'false_information' => 'False information',
        'inappropriate_content' => 'Inappropriate content',
        'other' => 'Other'
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Communauté</title>
    <link rel="stylesheet" href="/Web/view/backoffice/style/community.css?v=<?= filemtime(__DIR__ . '/../backoffice/style/community.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>
    <nav class="navbar">
        <div class="navbar-brand">
            <a href="community.php" class="brand-link">
                <img
                    src="/Web/view/backoffice/style/logo.png"
                    alt="Smart Nutrition"
                    class="brand-logo navbar-preview-logo"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                <span class="brand-fallback"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
            </a>
        </div>
        <ul class="navbar-menu">
            <li><a href="community.php" class="nav-link active"><i class="fa-solid fa-users"></i> Community</a></li>
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

            <div id="new-post-panel" class="section-anchor"></div>
            <div class="card card-primary shadow-sm mb-5">
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

            <!-- News & Tips Section -->
            <div class="news-section mb-5">
                <h2 class="section-title mb-4"><i class="fas fa-newspaper"></i> Nutrition & Fitness News</h2>

                <?php if (!empty($featuredNews)): ?>
                    <div class="news-carousel" id="newsCarousel" data-interval="4000" aria-label="Nutrition and fitness news slideshow">
                        <div class="news-carousel-viewport">
                            <div class="news-carousel-track" aria-live="polite">
                                <?php foreach ($featuredNews as $index => $newsArticle): ?>
                                    <div class="news-carousel-slide" aria-label="News slide <?= $index + 1 ?> of <?= count($featuredNews) ?>">
                                        <div class="news-card shadow-sm">
                                            <div class="news-image-container">
                                                <?php if ($newsArticle['image_url']): ?>
                                                    <img src="<?= htmlspecialchars($newsArticle['image_url']) ?>" alt="<?= htmlspecialchars($newsArticle['title']) ?>" class="news-image" onerror="this.src='https://via.placeholder.com/400x250?text=Article'">
                                                <?php else: ?>
                                                    <div class="news-image-placeholder"><i class="fas fa-image"></i></div>
                                                <?php endif; ?>
                                                <span class="news-category-badge badge"><?= htmlspecialchars($newsArticle['category']) ?></span>
                                            </div>
                                            <div class="news-card-body">
                                                <h5 class="news-title"><?= htmlspecialchars($newsArticle['title']) ?></h5>
                                                <p class="news-summary text-muted"><?= htmlspecialchars($newsArticle['summary'] ?? substr(strip_tags($newsArticle['content']), 0, 100)) ?>...</p>
                                                <div class="news-meta">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar"></i> <?= date('M d, Y', strtotime($newsArticle['created_at'])) ?>
                                                    </small>
                                                    <?php if ($newsArticle['source'] !== 'AI Generated'): ?>
                                                        <small class="text-muted ml-2">
                                                            <i class="fas fa-source"></i> <?= htmlspecialchars($newsArticle['source']) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="news-actions mt-3">
                                                    <button class="btn btn-sm btn-outline-primary" onclick='viewNewsArticle(<?= json_encode((string) $newsArticle['id']) ?>)'>
                                                        <i class="fas fa-eye"></i> Read More
                                                    </button>
                                                    <?php if ($newsArticle['source_url']): ?>
                                                        <a href="<?= htmlspecialchars($newsArticle['source_url']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                            <i class="fas fa-external-link-alt"></i> Original
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php if (count($featuredNews) > 1): ?>
                            <div class="news-carousel-controls">
                                <button type="button" class="news-carousel-btn" id="newsPrev" data-carousel-direction="prev" aria-label="Previous news article">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <div class="news-carousel-dots" id="newsDots" aria-label="News slide navigation"></div>
                                <button type="button" class="news-carousel-btn" id="newsNext" data-carousel-direction="next" aria-label="Next news article">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No news articles available yet. Check back soon!
                    </div>
                <?php endif; ?>
            </div>

            <div id="posts-container">
                <?php if (!empty($posts)): ?>
                    <?php foreach ($posts as $post): ?>
                        <?php $postImageSrc = resolvePostImageSrc($post['image'] ?? null); ?>
                        <?php $reactionSummary = $postModel->getReactionSummary($post['id'], $myId); ?>
                        <?php $reactionOptions = getReactionOptions(); ?>
                        <?php $reportReasonOptions = getReportReasonOptions(); ?>
                        <?php $userReport = $postModel->getUserReportForPost($post['id'], $myId); ?>
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

                                <div class="post-reactions" id="post-reactions-<?php echo $post['id']; ?>">
                                    <?php foreach ($reactionOptions as $reactionType => $reactionMeta): ?>
                                        <?php $isActiveReaction = ($reactionSummary['user_reaction'] ?? null) === $reactionType; ?>
                                        <button
                                            type="button"
                                            class="reaction-btn<?php echo $isActiveReaction ? ' is-active reaction-' . $reactionType : ' reaction-' . $reactionType; ?>"
                                            onclick="reactToPost(<?php echo $post['id']; ?>, '<?php echo $reactionType; ?>')"
                                            data-post-id="<?php echo $post['id']; ?>"
                                            data-reaction-type="<?php echo $reactionType; ?>"
                                            aria-pressed="<?php echo $isActiveReaction ? 'true' : 'false'; ?>">
                                            <i class="fa-solid <?php echo $reactionMeta['icon']; ?>"></i>
                                            <span><?php echo $reactionMeta['label']; ?></span>
                                            <span class="reaction-count" id="reaction-count-<?php echo $post['id']; ?>-<?php echo $reactionType; ?>"><?php echo (int) ($reactionSummary['counts'][$reactionType] ?? 0); ?></span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>

                                <div class="post-report-tools">
                                    <button
                                        type="button"
                                        class="report-toggle-btn<?php echo $userReport ? ' is-reported' : ''; ?>"
                                        onclick="toggleReportForm(<?php echo $post['id']; ?>)">
                                        <i class="fa-solid fa-flag"></i>
                                        <span id="report-toggle-label-<?php echo $post['id']; ?>"><?php echo $userReport ? 'Reported' : 'Report'; ?></span>
                                    </button>
                                    <span class="report-status-text" id="report-status-<?php echo $post['id']; ?>">
                                        <?php if ($userReport): ?>
                                            You reported this post for <?php echo htmlspecialchars(str_replace('_', ' ', $userReport['reason'])); ?>.
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <div id="report-form-<?php echo $post['id']; ?>" class="report-form-panel" style="display: none;">
                                    <div class="form-group">
                                        <label class="form-label" for="report-reason-<?php echo $post['id']; ?>">Reason</label>
                                        <select id="report-reason-<?php echo $post['id']; ?>" class="form-control form-control-sm">
                                            <?php foreach ($reportReasonOptions as $reasonValue => $reasonLabel): ?>
                                                <option value="<?php echo $reasonValue; ?>" <?php echo (($userReport['reason'] ?? '') === $reasonValue) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($reasonLabel); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label class="form-label" for="report-details-<?php echo $post['id']; ?>">Details (optional)</label>
                                        <textarea id="report-details-<?php echo $post['id']; ?>" class="form-control form-control-sm" rows="3" placeholder="Add a short note if needed..."><?php echo htmlspecialchars($userReport['details'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="report-form-actions">
                                        <button class="btn btn-sm btn-outline-danger" onclick="submitReport(<?php echo $post['id']; ?>)">Send report</button>
                                        <button class="btn btn-secondary btn-sm" onclick="toggleReportForm(<?php echo $post['id']; ?>)">Cancel</button>
                                    </div>
                                </div>

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
                                    <?php
                                    $comments = $commentModel->getComments($post['id']);
                                    [$topLevelComments, $repliesByParent] = organizeCommentsByThread($comments);
                                    ?>
                                    <h6><i class="fas fa-comments"></i> Commentaires (<?php echo count($comments); ?>)</h6>
                                    <div id="comments-list-<?php echo $post['id']; ?>">
                                        <?php if (!empty($topLevelComments)): ?>
                                            <?php foreach ($topLevelComments as $comment): ?>
                                                <div class="comment-item mb-2 p-3 border-bottom position-relative" id="comment-<?php echo $comment['id']; ?>">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <small class="text-muted"><i class="fas fa-user"></i> <?php echo htmlspecialchars($comment['username']); ?></small>
                                                        <div class="btn-group btn-group-sm">
                                                            <button class="btn btn-outline-secondary btn-sm" onclick="toggleReplyForm(<?php echo $comment['id']; ?>)" title="Repondre">
                                                                <i class="fas fa-reply"></i>
                                                            </button>
                                                            <?php if ($comment['user_id'] == $myId): ?>
                                                                <button class="btn btn-outline-info btn-sm" onclick="toggleCommentEdit(<?php echo $comment['id']; ?>)" title="Modifier">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-outline-danger btn-sm" onclick="deleteComment(<?php echo $comment['id']; ?>)" title="Supprimer">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div id="display-comment-text-<?php echo $comment['id']; ?>"><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></div>

                                                    <div id="edit-comment-block-<?php echo $comment['id']; ?>" class="comment-edit-form mt-2" style="display: none;">
                                                        <textarea id="edit-comment-text-<?php echo $comment['id']; ?>" class="form-control form-control-sm" rows="2"><?php echo htmlspecialchars($comment['comment_text']); ?></textarea>
                                                        <div class="mt-1">
                                                            <button class="btn btn-success btn-sm" onclick="saveCommentEdit(<?php echo $comment['id']; ?>)">Enregistrer</button>
                                                            <button class="btn btn-secondary btn-sm" onclick="toggleCommentEdit(<?php echo $comment['id']; ?>)">Annuler</button>
                                                        </div>
                                                    </div>

                                                    <div id="reply-form-<?php echo $comment['id']; ?>" class="comment-edit-form mt-2" style="display: none;">
                                                        <textarea id="reply-content-<?php echo $comment['id']; ?>" class="form-control form-control-sm" rows="2" placeholder="Ecrire une reponse..."></textarea>
                                                        <div class="mt-1">
                                                            <button class="btn btn-success btn-sm" onclick="addReply(<?php echo $post['id']; ?>, <?php echo $comment['id']; ?>)">Repondre</button>
                                                            <button class="btn btn-secondary btn-sm" onclick="toggleReplyForm(<?php echo $comment['id']; ?>)">Annuler</button>
                                                        </div>
                                                    </div>

                                                    <?php if (!empty($repliesByParent[$comment['id']])): ?>
                                                        <div class="mt-3" style="margin-left: 28px;">
                                                            <?php foreach ($repliesByParent[$comment['id']] as $reply): ?>
                                                                <div class="comment-item mb-2 p-3 position-relative" id="comment-<?php echo $reply['id']; ?>">
                                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                                        <small class="text-muted"><i class="fas fa-user"></i> <?php echo htmlspecialchars($reply['username']); ?></small>
                                                                        <?php if ($reply['user_id'] == $myId): ?>
                                                                            <div class="btn-group btn-group-sm">
                                                                                <button class="btn btn-outline-info btn-sm" onclick="toggleCommentEdit(<?php echo $reply['id']; ?>)" title="Modifier">
                                                                                    <i class="fas fa-edit"></i>
                                                                                </button>
                                                                                <button class="btn btn-outline-danger btn-sm" onclick="deleteComment(<?php echo $reply['id']; ?>)" title="Supprimer">
                                                                                    <i class="fas fa-trash"></i>
                                                                                </button>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <div id="display-comment-text-<?php echo $reply['id']; ?>"><?php echo nl2br(htmlspecialchars($reply['comment_text'])); ?></div>

                                                                    <div id="edit-comment-block-<?php echo $reply['id']; ?>" class="comment-edit-form mt-2" style="display: none;">
                                                                        <textarea id="edit-comment-text-<?php echo $reply['id']; ?>" class="form-control form-control-sm" rows="2"><?php echo htmlspecialchars($reply['comment_text']); ?></textarea>
                                                                        <div class="mt-1">
                                                                            <button class="btn btn-success btn-sm" onclick="saveCommentEdit(<?php echo $reply['id']; ?>)">Enregistrer</button>
                                                                            <button class="btn btn-secondary btn-sm" onclick="toggleCommentEdit(<?php echo $reply['id']; ?>)">Annuler</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
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

    <script src="/Web/view/backoffice/style/community.js?v=<?= filemtime(__DIR__ . '/../backoffice/style/community.js') ?>"></script>
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

        function reactToPost(postId, reactionType) {
            fetch('../../controller/postController.php?action=react', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `post_id=${postId}&reaction_type=${encodeURIComponent(reactionType)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        updateReactionButtons(postId, data.reactionSummary || null);
                    } else {
                        alert(data.message || "Erreur reaction");
                    }
                });
        }

        function updateReactionButtons(postId, reactionSummary) {
            if (!reactionSummary || !reactionSummary.counts) return;

            document.querySelectorAll(`#post-reactions-${postId} .reaction-btn`).forEach((button) => {
                const reactionType = button.dataset.reactionType;
                const countElement = document.getElementById(`reaction-count-${postId}-${reactionType}`);
                const isActive = reactionSummary.user_reaction === reactionType;

                if (countElement) {
                    countElement.textContent = reactionSummary.counts[reactionType] ?? 0;
                }

                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
        }

        function toggleReportForm(postId) {
            const block = document.getElementById(`report-form-${postId}`);
            if (!block) return;
            block.style.display = block.style.display === 'block' ? 'none' : 'block';
        }

        function submitReport(postId) {
            const reasonField = document.getElementById(`report-reason-${postId}`);
            const detailsField = document.getElementById(`report-details-${postId}`);
            const reason = reasonField.value;
            const details = detailsField.value.trim();

            fetch('../../controller/postController.php?action=report', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `post_id=${postId}&reason=${encodeURIComponent(reason)}&details=${encodeURIComponent(details)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        updateReportUi(postId, data.report || null);
                        toggleReportForm(postId);
                    } else {
                        alert(data.message || "Erreur lors du signalement");
                    }
                });
        }

        function updateReportUi(postId, report) {
            const statusElement = document.getElementById(`report-status-${postId}`);
            const labelElement = document.getElementById(`report-toggle-label-${postId}`);
            const toggleButton = document.querySelector(`#post-${postId} .report-toggle-btn`);
            const detailsField = document.getElementById(`report-details-${postId}`);
            const reasonField = document.getElementById(`report-reason-${postId}`);

            if (!statusElement || !labelElement || !toggleButton) return;

            if (report) {
                const reasonText = String(report.reason || '').replaceAll('_', ' ');
                statusElement.textContent = `You reported this post for ${reasonText}.`;
                labelElement.textContent = 'Reported';
                toggleButton.classList.add('is-reported');
                if (detailsField) {
                    detailsField.value = report.details || '';
                }
                if (reasonField && report.reason) {
                    reasonField.value = report.reason;
                }
            } else {
                statusElement.textContent = '';
                labelElement.textContent = 'Report';
                toggleButton.classList.remove('is-reported');
            }
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

        function toggleReplyForm(commentId) {
            const block = document.getElementById(`reply-form-${commentId}`);
            if (!block) return;
            block.style.display = block.style.display === 'block' ? 'none' : 'block';
        }

        function addReply(postId, parentCommentId) {
            const contentField = document.getElementById(`reply-content-${parentCommentId}`);
            const content = contentField.value.trim();
            if (!content) return alert("La reponse ne peut pas etre vide");

            fetch('../../controller/commentController.php?action=add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `post_id=${postId}&parent_comment_id=${parentCommentId}&content=${encodeURIComponent(content)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        contentField.value = '';
                        location.reload();
                    } else {
                        alert(data.message || "Erreur lors de l'ajout de la reponse");
                    }
                });
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

        const fallbackNewsArticles = <?= json_encode(array_column($fallbackNews, null, 'id'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

        // News Functions
        function viewNewsArticle(newsId) {
            if (fallbackNewsArticles[newsId]) {
                showNewsModal(fallbackNewsArticles[newsId]);
                return;
            }

            fetch(`../../controller/newsController.php?action=getById&id=${newsId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showNewsModal(data.data);
                    } else {
                        alert(data.message || 'Error loading article');
                    }
                });
        }

        function showNewsModal(article) {
            const oldModal = document.getElementById('newsModal');
            if (oldModal) {
                oldModal.remove();
            }

            const modal = document.createElement('div');
            const articleContent = formatArticleContent(article.content || article.summary || 'No article content available.');

            modal.className = 'modal-overlay news-modal-overlay';
            modal.id = 'newsModal';
            modal.innerHTML = `
                <div class="news-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="newsModalTitle">
                    <button type="button" class="modal-close" onclick="this.closest('.modal-overlay').remove()" aria-label="Close article">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="modal-header">
                        <h2 id="newsModalTitle">${htmlEscape(article.title)}</h2>
                        <div class="modal-meta">
                            <span class="badge">${htmlEscape(article.category)}</span>
                            <span class="text-muted ml-2">
                                <i class="fas fa-calendar"></i> ${new Date(article.created_at).toLocaleDateString()}
                            </span>
                            ${article.source ? `<span class="text-muted ml-2"><i class="fas fa-source"></i> ${htmlEscape(article.source)}</span>` : ''}
                        </div>
                    </div>
                    <div class="modal-body">
                        ${article.image_url ? `<img src="${htmlEscape(article.image_url)}" alt="${htmlEscape(article.title)}" class="modal-image" onerror="this.src='https://via.placeholder.com/600x400?text=Article'">` : ''}
                        <div class="article-content mt-4">
                            ${articleContent}
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.remove();
            });
        }

        function htmlEscape(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text || '').replace(/[&<>"']/g, m => map[m]);
        }

        function formatArticleContent(content) {
            const text = String(content || '').trim();
            if (!text) {
                return '<p>No article content available.</p>';
            }

            if (/<[a-z][\s\S]*>/i.test(text)) {
                return text;
            }

            return '<p>' + htmlEscape(text).replace(/\n{2,}/g, '</p><p>').replace(/\n/g, '<br>') + '</p>';
        }
    </script>
</body>

</html>
