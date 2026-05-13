<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /Web/index.php?action=login');
    exit;
}

require_once __DIR__ . '/../../model/connection.php';
require_once __DIR__ . '/../../model/Post.php';
require_once __DIR__ . '/../../model/Comment.php';
require_once __DIR__ . '/../../model/News.php';

$myId = (int) ($_SESSION['user_id'] ?? 0);
$sessionUserName = $_SESSION['user_name'] ?? 'Utilisateur';
$isLoggedIn = $myId > 0;

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
        'title' => 'Composer des bols repas equilibres pour les journees chargees',
        'summary' => 'Associez proteines maigres, legumes colores, cereales completes et bonnes graisses pour des repas rassasiants.',
        'content' => '<p>Un bol repas equilibre se construit simplement par couches. Commencez par une base riche en fibres comme le riz complet, le quinoa, les lentilles ou la patate douce rotie. Ajoutez une source de proteines comme le poulet grille, le thon, les oeufs, le tofu, les haricots ou les pois chiches. Completez avec des legumes pour la couleur, le volume et les micronutriments.</p><p>Terminez avec une bonne graisse comme l avocat, une vinaigrette a l huile d olive, des noix ou des graines. Ce melange aide a rester rassasie tout en gardant une energie plus stable dans la journee.</p>',
        'image_url' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=900&q=80',
        'category' => 'nutrition',
        'source' => 'Smart Nutrition',
        'source_url' => '',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'id' => 'fallback-protein-breakfast',
        'title' => 'Idees simples de petits-dejeuners riches en proteines',
        'summary' => 'Yaourt grec, oeufs aux legumes, flocons d avoine aux noix ou smoothies peuvent rendre le petit-dejeuner plus nourrissant.',
        'content' => '<p>Un bon petit-dejeuner n a pas besoin d etre complique. Essayez d associer proteines, fibres et un fruit ou un legume. Un yaourt grec avec des fruits rouges et des graines, des oeufs avec epinards et tomates, des flocons d avoine avec du beurre de cacahuete ou un smoothie au lait, banane et yaourt peuvent tres bien fonctionner.</p><p>Les proteines le matin favorisent la satiete, tandis que les fibres soutiennent la digestion et une energie plus reguliere.</p>',
        'image_url' => 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&w=900&q=80',
        'category' => 'nutrition',
        'source' => 'Smart Nutrition',
        'source_url' => '',
        'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
    ],
    [
        'id' => 'fallback-hydration-meals',
        'title' => 'Aliments hydratants a ajouter aux repas sains',
        'summary' => 'Concombre, agrumes, fruits rouges, tomates, legumes verts et soupes peuvent aider l hydratation en complement de l eau.',
        'content' => '<p>L eau reste essentielle pour s hydrater, mais les repas peuvent aussi aider. Le concombre, les oranges, les fruits rouges, les tomates, les courgettes, les legumes verts et les soupes a base de bouillon apportent de l eau ainsi que des vitamines et mineraux utiles.</p><p>Ajoutez une salade, un fruit ou une soupe de legumes lorsque vous voulez un repas leger, frais et favorable a l hydratation.</p>',
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
        'love' => ['label' => 'J aime', 'icon' => 'fa-heart'],
        'laugh' => ['label' => 'Drole', 'icon' => 'fa-face-laugh-squint'],
        'sad' => ['label' => 'Triste', 'icon' => 'fa-face-sad-tear'],
        'angry' => ['label' => 'En colere', 'icon' => 'fa-face-angry']
    ];
}

function getReportReasonOptions()
{
    return [
        'spam' => 'Spam',
        'harassment' => 'Harcelement',
        'false_information' => 'Fausse information',
        'inappropriate_content' => 'Contenu inapproprie',
        'other' => 'Autre'
    ];
}

function getPostCategoryOptions()
{
    return [
        'question' => ['label' => 'Question', 'icon' => 'fa-circle-question'],
        'recipe' => ['label' => 'Recipe', 'icon' => 'fa-utensils'],
        'progress' => ['label' => 'Progress', 'icon' => 'fa-chart-line'],
        'advice' => ['label' => 'Advice', 'icon' => 'fa-lightbulb'],
        'product_review' => ['label' => 'Product Review', 'icon' => 'fa-star-half-stroke']
    ];
}

function getPostCategoryMeta($category)
{
    $options = getPostCategoryOptions();
    return $options[$category] ?? $options['advice'];
}

function getCommunityBadges($post)
{
    $badges = [];
    $postsCount = (int) ($post['author_posts_count'] ?? 0);
    $commentsCount = (int) ($post['author_comments_count'] ?? 0);
    $recipesCount = (int) ($post['author_recipes_count'] ?? 0);

    if ($postsCount >= 5 || ($postsCount + $commentsCount) >= 10) {
        $badges[] = ['label' => 'Top Contributor', 'icon' => 'fa-trophy', 'class' => 'badge-top'];
    }
    if ($commentsCount >= 5) {
        $badges[] = ['label' => 'Helpful Member', 'icon' => 'fa-hand-holding-heart', 'class' => 'badge-helpful'];
    }
    if ($recipesCount >= 1 || ($post['post_category'] ?? '') === 'recipe') {
        $badges[] = ['label' => 'Recipe Sharer', 'icon' => 'fa-utensils', 'class' => 'badge-recipe'];
    }

    return $badges;
}

function getNewsCategoryLabel($category)
{
    $labels = [
        'nutrition' => 'Nutrition',
        'healthy_meals' => 'Repas sains',
        'fitness' => 'Fitness',
        'wellness' => 'Bien-etre',
        'health_tips' => 'Conseils sante'
    ];

    return $labels[$category] ?? ucfirst(str_replace('_', ' ', (string) $category));
}

function decodeProductAnalysis($json)
{
    $data = json_decode((string) $json, true);
    return is_array($data) ? $data : null;
}

function hasPostLocation($post)
{
    return isset($post['latitude'], $post['longitude'])
        && is_numeric($post['latitude'])
        && is_numeric($post['longitude']);
}
?>

<div class="container">
            <?php $showAdminReturn = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'; ?>
            <?php if ($showAdminReturn): ?>
                <div class="admin-return-bar mb-4">
                    <a href="/Web/index.php?action=admin-dashboard" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-arrow-left"></i> Retour au tableau de bord admin
                    </a>
                </div>
            <?php endif; ?>

            <div id="new-post-panel" class="section-anchor"></div>
            <div class="card card-primary shadow-sm mb-5">
                <div class="card-header">
                    <h3 class="card-title">Quoi de neuf ?</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label" for="new-post-category">Type de publication</label>
                        <select id="new-post-category" class="form-control mb-2">
                            <?php foreach (getPostCategoryOptions() as $categoryValue => $categoryMeta): ?>
                                <option value="<?php echo htmlspecialchars($categoryValue); ?>"><?php echo htmlspecialchars($categoryMeta['label']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" id="new-title" class="form-control mb-2" placeholder="Titre de votre publication">
                        <textarea id="new-content" class="form-control" rows="3" placeholder="Écrivez votre message ici..."></textarea>
                        <div class="product-analysis-box mt-3">
                            <label class="form-label" for="product-query">Analyse nutritionnelle (optionnel)</label>
                            <div class="product-analysis-controls">
                                <input type="text" id="product-query" class="form-control" placeholder="Nom ou code-barres du produit">
                                <input type="hidden" id="product-analysis-json" value="">
                                <button type="button" class="btn btn-outline-secondary" onclick="analyzeProduct()">
                                    <i class="fa-solid fa-magnifying-glass-chart"></i> Analyser
                                </button>
                            </div>
                            <p class="product-analysis-hint">Recherche dans Open Food Facts, avec priorite aux produits vendus en Tunisie.</p>
                            <div id="product-analysis-result" class="product-analysis-result" hidden></div>
                        </div>
                        <div class="form-group mt-3">
                            <label class="form-label">Image (optionnel)</label>
                            <input type="file" id="new-image" class="form-control" accept="image/*">
                        </div>
                        <div class="post-location-box mt-3">
                            <div>
                                <label class="form-label">Localisation de creation (optionnel)</label>
                                <p id="post-location-status" class="post-location-status">La position sera demandee au moment de publier.</p>
                            </div>
                            <input type="hidden" id="new-latitude" value="">
                            <input type="hidden" id="new-longitude" value="">
                            <input type="hidden" id="new-location-accuracy" value="">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="requestCurrentPostLocation(true)">
                                <i class="fa-solid fa-location-crosshairs"></i> Utiliser ma position
                            </button>
                        </div>
                        <div id="new-post-location-preview" class="post-location-preview" hidden>
                            <div
                                id="new-post-map"
                                class="post-mini-map"
                                data-location-preview-map
                                aria-label="Apercu de la position de creation"></div>
                        </div>
                    </div>
                    <button onclick="submitPost()" class="btn">Publier</button>
                </div>
            </div>

            <!-- Section actualites et conseils -->
            <div class="news-section mb-5">
                <h2 class="section-title mb-4"><i class="fas fa-newspaper"></i> Actualites nutrition et fitness</h2>

                <?php if (!empty($featuredNews)): ?>
                    <div class="news-carousel" id="newsCarousel" data-interval="4000" aria-label="Diaporama des actualites nutrition et fitness">
                        <div class="news-carousel-viewport">
                            <div class="news-carousel-track" aria-live="polite">
                                <?php foreach ($featuredNews as $index => $newsArticle): ?>
                                    <div class="news-carousel-slide" aria-label="Actualite <?= $index + 1 ?> sur <?= count($featuredNews) ?>">
                                        <div class="news-card shadow-sm">
                                            <div class="news-image-container">
                                                <?php if ($newsArticle['image_url']): ?>
                                                    <img src="<?= htmlspecialchars($newsArticle['image_url']) ?>" alt="<?= htmlspecialchars($newsArticle['title']) ?>" class="news-image" onerror="this.src='https://via.placeholder.com/400x250?text=Article'">
                                                <?php else: ?>
                                                    <div class="news-image-placeholder"><i class="fas fa-image"></i></div>
                                                <?php endif; ?>
                                                <span class="news-category-badge badge"><?= htmlspecialchars(getNewsCategoryLabel($newsArticle['category'] ?? '')) ?></span>
                                            </div>
                                            <div class="news-card-body">
                                                <h5 class="news-title"><?= htmlspecialchars($newsArticle['title']) ?></h5>
                                                <p class="news-summary text-muted"><?= htmlspecialchars($newsArticle['summary'] ?? substr(strip_tags($newsArticle['content']), 0, 100)) ?>...</p>
                                                <div class="news-meta">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($newsArticle['created_at'])) ?>
                                                    </small>
                                                    <?php if ($newsArticle['source'] !== 'Genere par IA'): ?>
                                                        <small class="text-muted ml-2">
                                                            <i class="fas fa-source"></i> <?= htmlspecialchars($newsArticle['source']) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="news-actions mt-3">
                                                    <button class="btn btn-sm btn-outline-primary" onclick='viewNewsArticle(<?= json_encode((string) $newsArticle['id']) ?>)'>
                                                        <i class="fas fa-eye"></i> Lire la suite
                                                    </button>
                                                    <?php if ($newsArticle['source_url']): ?>
                                                        <a href="<?= htmlspecialchars($newsArticle['source_url']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                            <i class="fas fa-external-link-alt"></i> Source
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
                                <button type="button" class="news-carousel-btn" id="newsPrev" data-carousel-direction="prev" aria-label="Actualite precedente">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <div class="news-carousel-dots" id="newsDots" aria-label="Navigation des actualites"></div>
                                <button type="button" class="news-carousel-btn" id="newsNext" data-carousel-direction="next" aria-label="Actualite suivante">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Aucune actualite disponible pour le moment. Revenez bientot !
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
                        <?php $productAnalysis = decodeProductAnalysis($post['product_analysis_json'] ?? null); ?>
                        <?php $postHasLocation = hasPostLocation($post); ?>
                        <?php $postLatitude = $postHasLocation ? (float) $post['latitude'] : null; ?>
                        <?php $postLongitude = $postHasLocation ? (float) $post['longitude'] : null; ?>
                        <?php $postCategory = $post['post_category'] ?? 'advice'; ?>
                        <?php $postCategoryMeta = getPostCategoryMeta($postCategory); ?>
                        <?php $postCategoryIsAi = ($post['post_category_source'] ?? '') === 'ai'; ?>
                        <?php $postCategoryScore = isset($post['post_category_score']) ? round(((float) $post['post_category_score']) * 100) : null; ?>
                        <?php $communityBadges = getCommunityBadges($post); ?>
                        <div class="post-card" id="post-<?php echo $post['id']; ?>">
                            <div class="post-header">
                                <div>
                                    <strong><i class="fas fa-user text-muted"></i> <?php echo htmlspecialchars($post['username']); ?></strong>
                                    <small class="text-muted ml-2"><?php echo $post['created_at']; ?></small>
                                    <?php if (!empty($communityBadges)): ?>
                                        <div class="community-badges">
                                            <?php foreach ($communityBadges as $badge): ?>
                                                <span class="community-badge <?php echo htmlspecialchars($badge['class']); ?>">
                                                    <i class="fa-solid <?php echo htmlspecialchars($badge['icon']); ?>"></i>
                                                    <?php echo htmlspecialchars($badge['label']); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
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
                                <div class="post-meta-row">
                                    <span class="post-category-chip post-category-<?php echo htmlspecialchars($postCategory); ?>">
                                        <i class="fa-solid <?php echo htmlspecialchars($postCategoryMeta['icon']); ?>"></i>
                                        <?php echo htmlspecialchars($postCategoryMeta['label']); ?>
                                        <?php if ($postCategoryIsAi): ?>
                                            <small>AI<?php echo $postCategoryScore !== null ? ' ' . (int) $postCategoryScore . '%' : ''; ?></small>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <h5 id="display-title-<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></h5>
                                <p id="display-content-<?php echo $post['id']; ?>"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                                <?php if ($postImageSrc): ?>
                                    <img src="<?= htmlspecialchars($postImageSrc) ?>" alt="Image de la publication" class="post-image mb-3 rounded" style="max-height: 250px; width: auto; max-width: 100%; height: auto; object-fit: contain;">
                                <?php endif; ?>

                                <?php if ($productAnalysis): ?>
                                    <div class="post-product-analysis">
                                        <button type="button" class="product-analysis-toggle" onclick="toggleProductAnalysis(<?php echo (int) $post['id']; ?>)">
                                            <i class="fa-solid fa-chart-pie"></i>
                                            Voir l analyse nutritionnelle
                                            <span class="nutrition-score nutrition-score-<?php echo htmlspecialchars(strtolower((string) ($productAnalysis['nutriScore'] ?? ''))); ?>">
                                                Nutri-Score <?php echo htmlspecialchars($productAnalysis['nutriScore'] ?? 'N/A'); ?>
                                            </span>
                                        </button>
                                        <div id="product-analysis-panel-<?php echo (int) $post['id']; ?>" class="product-analysis-result post-product-panel" hidden>
                                            <div class="product-analysis-card">
                                                <?php if (!empty($productAnalysis['image'])): ?>
                                                    <img src="<?php echo htmlspecialchars($productAnalysis['image']); ?>" alt="<?php echo htmlspecialchars($productAnalysis['name'] ?? 'Produit'); ?>" class="product-analysis-image">
                                                <?php else: ?>
                                                    <div class="product-analysis-image product-analysis-placeholder"><i class="fa-solid fa-bowl-food"></i></div>
                                                <?php endif; ?>
                                                <div class="product-analysis-content">
                                                    <div class="product-analysis-title-row">
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($productAnalysis['name'] ?? 'Produit alimentaire'); ?></strong>
                                                            <?php if (!empty($productAnalysis['brand'])): ?>
                                                                <small><?php echo htmlspecialchars($productAnalysis['brand']); ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                        <span class="nutrition-score nutrition-score-<?php echo htmlspecialchars(strtolower((string) ($productAnalysis['nutriScore'] ?? ''))); ?>">
                                                            Nutri-Score <?php echo htmlspecialchars($productAnalysis['nutriScore'] ?? 'N/A'); ?>
                                                        </span>
                                                    </div>
                                                    <div class="nutrition-metrics">
                                                        <span><b><?php echo htmlspecialchars($productAnalysis['calories'] ?? '-'); ?></b> kcal</span>
                                                        <span><b><?php echo htmlspecialchars($productAnalysis['sugar'] ?? '-'); ?></b> sucres</span>
                                                        <span><b><?php echo htmlspecialchars($productAnalysis['fat'] ?? '-'); ?></b> matieres grasses</span>
                                                        <span><b><?php echo htmlspecialchars($productAnalysis['salt'] ?? '-'); ?></b> sel</span>
                                                    </div>
                                                    <div class="product-allergens">
                                                        <?php if (!empty($productAnalysis['allergens']) && is_array($productAnalysis['allergens'])): ?>
                                                            <?php foreach (array_slice($productAnalysis['allergens'], 0, 6) as $allergen): ?>
                                                                <span><?php echo htmlspecialchars($allergen); ?></span>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <span>Aucun allergene renseigne</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if (!empty($productAnalysis['ingredients'])): ?>
                                                        <p class="product-ingredients"><strong>Ingredients :</strong> <?php echo htmlspecialchars($productAnalysis['ingredients']); ?></p>
                                                    <?php endif; ?>
                                                    <small class="text-muted">Source : Open Food Facts</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($postHasLocation): ?>
                                    <section class="post-location-map-panel" aria-label="Carte de localisation de la publication">
                                        <div class="post-location-map-header">
                                            <span><i class="fa-solid fa-map-location-dot"></i> Lieu de creation</span>
                                            <?php if (!empty($post['location_accuracy'])): ?>
                                                <small>Precision ~<?php echo (int) $post['location_accuracy']; ?> m</small>
                                            <?php endif; ?>
                                        </div>
                                        <div
                                            id="front-post-map-<?php echo (int) $post['id']; ?>"
                                            class="post-mini-map"
                                            data-front-post-map
                                            data-lat="<?php echo htmlspecialchars((string) $postLatitude, ENT_QUOTES); ?>"
                                            data-lng="<?php echo htmlspecialchars((string) $postLongitude, ENT_QUOTES); ?>"
                                            data-title="<?php echo htmlspecialchars((string) $post['title'], ENT_QUOTES); ?>"
                                            data-accuracy="<?php echo htmlspecialchars((string) ($post['location_accuracy'] ?? ''), ENT_QUOTES); ?>"></div>
                                        <a class="post-map-link" href="https://www.openstreetmap.org/?mlat=<?php echo urlencode((string) $postLatitude); ?>&mlon=<?php echo urlencode((string) $postLongitude); ?>#map=16/<?php echo urlencode((string) $postLatitude); ?>/<?php echo urlencode((string) $postLongitude); ?>" target="_blank" rel="noopener">
                                            Ouvrir dans OpenStreetMap
                                        </a>
                                    </section>
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
                                        <span id="report-toggle-label-<?php echo $post['id']; ?>"><?php echo $userReport ? 'Signale' : 'Signaler'; ?></span>
                                    </button>
                                    <span class="report-status-text" id="report-status-<?php echo $post['id']; ?>">
                                        <?php if ($userReport): ?>
                                            Vous avez signale cette publication pour : <?php echo htmlspecialchars(str_replace('_', ' ', $userReport['reason'])); ?>.
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <div id="report-form-<?php echo $post['id']; ?>" class="report-form-panel" style="display: none;">
                                    <div class="form-group">
                                        <label class="form-label" for="report-reason-<?php echo $post['id']; ?>">Raison</label>
                                        <select id="report-reason-<?php echo $post['id']; ?>" class="form-control form-control-sm">
                                            <?php foreach ($reportReasonOptions as $reasonValue => $reasonLabel): ?>
                                                <option value="<?php echo $reasonValue; ?>" <?php echo (($userReport['reason'] ?? '') === $reasonValue) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($reasonLabel); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label class="form-label" for="report-details-<?php echo $post['id']; ?>">Details (optionnels)</label>
                                        <textarea id="report-details-<?php echo $post['id']; ?>" class="form-control form-control-sm" rows="3" placeholder="Ajoutez une courte explication si necessaire..."><?php echo htmlspecialchars($userReport['details'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="report-form-actions">
                                        <button class="btn btn-sm btn-outline-danger" onclick="submitReport(<?php echo $post['id']; ?>)">Envoyer le signalement</button>
                                        <button class="btn btn-secondary btn-sm" onclick="toggleReportForm(<?php echo $post['id']; ?>)">Annuler</button>
                                    </div>
                                </div>

                                <div id="edit-block-<?php echo $post['id']; ?>" class="edit-form mt-3" style="display: none;">
                                    <label class="form-label" for="edit-post-category-<?php echo $post['id']; ?>">Type de publication</label>
                                    <select id="edit-post-category-<?php echo $post['id']; ?>" class="form-control mb-2">
                                        <?php foreach (getPostCategoryOptions() as $categoryValue => $categoryMeta): ?>
                                            <option value="<?php echo htmlspecialchars($categoryValue); ?>" <?php echo $postCategory === $categoryValue ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($categoryMeta['label']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="text" id="edit-title-<?php echo $post['id']; ?>" class="form-control mb-2" value="<?php echo htmlspecialchars($post['title']); ?>">
                                    <textarea id="edit-content-<?php echo $post['id']; ?>" class="form-control mb-2"><?php echo htmlspecialchars($post['content']); ?></textarea>
                                    <?php if ($postImageSrc): ?>
                                        <div class="mb-2 d-flex align-items-center" id="post-image-container-<?php echo $post['id']; ?>">
                                            <img src="<?= htmlspecialchars($postImageSrc) ?>" class="img-thumbnail me-2" style="max-width: 80px; max-height: 80px; object-fit: contain;" alt="Image de la publication">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage(<?php echo $post['id']; ?>)">
                                                <i class="fas fa-trash"></i> Supprimer image
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                    <div class="form-group">
                                        <label class="form-label">Nouvelle image (optionnel)</label>
                                        <input type="file" id="edit-image-<?php echo $post['id']; ?>" class="form-control" accept="image/*">
                                    </div>
                                    <div class="product-analysis-box mt-3">
                                        <label class="form-label" for="edit-product-query-<?php echo $post['id']; ?>">Analyse nutritionnelle</label>
                                        <div class="product-analysis-controls">
                                            <input type="text" id="edit-product-query-<?php echo $post['id']; ?>" class="form-control" placeholder="Nom ou code-barres du produit">
                                            <input type="hidden" id="edit-product-analysis-json-<?php echo $post['id']; ?>" value="<?php echo htmlspecialchars($post['product_analysis_json'] ?? '', ENT_QUOTES); ?>">
                                            <button type="button" class="btn btn-outline-secondary" onclick="analyzeProductForEdit(<?php echo (int) $post['id']; ?>)">
                                                <i class="fa-solid fa-magnifying-glass-chart"></i> Analyser
                                            </button>
                                        </div>
                                        <div id="edit-product-analysis-result-<?php echo $post['id']; ?>" class="product-analysis-result" <?php echo $productAnalysis ? '' : 'hidden'; ?>>
                                            <?php if ($productAnalysis): ?>
                                                Analyse deja associee a cette publication.
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <button class="btn btn-success btn-sm" onclick="saveEdit(<?php echo $post['id']; ?>)">Enregistrer</button>
                                    <button class="btn btn-secondary btn-sm" onclick="toggleEdit(<?php echo $post['id']; ?>)">Annuler</button>
                                </div>

                                <div class="comments-section mt-4">
                                    <?php
                                    $comments = $commentModel->getComments($post['id'], $myId);
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
                                                    <div class="comment-actions">
                                                        <button
                                                            type="button"
                                                            class="comment-like-btn<?php echo !empty($comment['user_liked']) ? ' is-active' : ''; ?>"
                                                            onclick="likeComment(<?php echo (int) $comment['id']; ?>)"
                                                            aria-pressed="<?php echo !empty($comment['user_liked']) ? 'true' : 'false'; ?>">
                                                            <i class="fa-solid fa-thumbs-up"></i>
                                                            <span>Like</span>
                                                            <span id="comment-like-count-<?php echo (int) $comment['id']; ?>"><?php echo (int) ($comment['likes_count'] ?? 0); ?></span>
                                                        </button>
                                                    </div>

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
                                                                    <div class="comment-actions">
                                                                        <button
                                                                            type="button"
                                                                            class="comment-like-btn<?php echo !empty($reply['user_liked']) ? ' is-active' : ''; ?>"
                                                                            onclick="likeComment(<?php echo (int) $reply['id']; ?>)"
                                                                            aria-pressed="<?php echo !empty($reply['user_liked']) ? 'true' : 'false'; ?>">
                                                                            <i class="fa-solid fa-thumbs-up"></i>
                                                                            <span>Like</span>
                                                                            <span id="comment-like-count-<?php echo (int) $reply['id']; ?>"><?php echo (int) ($reply['likes_count'] ?? 0); ?></span>
                                                                        </button>
                                                                    </div>

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

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="/Web/view/back/style/community.js?v=<?= filemtime(__DIR__ . '/../back/style/community.js') ?>"></script>
    <script>
        let currentProductAnalysis = null;
        const frontPostMaps = {};
        let newPostPreviewMap = null;
        let newPostPreviewMarker = null;
        let newPostPreviewCircle = null;

        function createOpenStreetMapLayer() {
            return L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            });
        }

        function reverseGeocodeLocation(lat, lng) {
            const cacheKey = `osm-place-${lat.toFixed(5)}-${lng.toFixed(5)}`;
            const cached = localStorage.getItem(cacheKey);
            if (cached) {
                return Promise.resolve(cached);
            }

            const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}&zoom=16&addressdetails=0`;
            return fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.ok ? res.json() : null)
                .then(data => {
                    const label = data && data.display_name ? String(data.display_name) : `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                    localStorage.setItem(cacheKey, label);
                    return label;
                })
                .catch(() => `${lat.toFixed(5)}, ${lng.toFixed(5)}`);
        }

        function setMapPlaceLabel(mapElement, label) {
            if (!mapElement || !label) return;

            let labelElement = mapElement.parentElement ? mapElement.parentElement.querySelector('.post-map-place-label') : null;
            if (!labelElement) {
                labelElement = document.createElement('div');
                labelElement.className = 'post-map-place-label';
                mapElement.insertAdjacentElement('afterend', labelElement);
            }

            labelElement.innerHTML = `<i class="fa-solid fa-location-dot"></i> ${htmlEscape(label)}`;
        }

        function initFrontPostMap(mapElement) {
            if (!mapElement || mapElement.dataset.ready === 'true' || typeof L === 'undefined') return;

            const lat = Number(mapElement.dataset.lat);
            const lng = Number(mapElement.dataset.lng);
            const accuracy = Number(mapElement.dataset.accuracy || 0);
            const title = mapElement.dataset.title || 'Publication';

            if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

            const map = L.map(mapElement, {
                scrollWheelZoom: false
            }).setView([lat, lng], 15);

            createOpenStreetMapLayer().addTo(map);
            const marker = L.marker([lat, lng]).addTo(map).bindPopup(title);

            if (Number.isFinite(accuracy) && accuracy > 0) {
                L.circle([lat, lng], {
                    radius: accuracy,
                    color: '#2f80ed',
                    fillColor: '#2f80ed',
                    fillOpacity: 0.12,
                    weight: 1
                }).addTo(map);
            }

            mapElement.dataset.ready = 'true';
            frontPostMaps[mapElement.id] = map;
            window.setTimeout(() => map.invalidateSize(), 100);

            reverseGeocodeLocation(lat, lng).then(placeLabel => {
                marker.bindPopup(`<strong>${htmlEscape(title)}</strong><br>${htmlEscape(placeLabel)}`);
                setMapPlaceLabel(mapElement, placeLabel);
            });
        }

        function initAllFrontPostMaps() {
            if (typeof L === 'undefined') return;
            document.querySelectorAll('[data-front-post-map]').forEach(initFrontPostMap);
        }

        function updateNewPostLocationPreview(lat, lng, accuracy) {
            if (typeof L === 'undefined') return;

            const preview = document.getElementById('new-post-location-preview');
            const mapElement = document.getElementById('new-post-map');
            if (!preview || !mapElement) return;

            preview.hidden = false;

            if (!newPostPreviewMap) {
                newPostPreviewMap = L.map(mapElement, {
                    scrollWheelZoom: false
                }).setView([lat, lng], 15);
                createOpenStreetMapLayer().addTo(newPostPreviewMap);
            } else {
                newPostPreviewMap.setView([lat, lng], 15);
            }

            if (newPostPreviewMarker) {
                newPostPreviewMarker.setLatLng([lat, lng]);
            } else {
                newPostPreviewMarker = L.marker([lat, lng]).addTo(newPostPreviewMap).bindPopup('Position de creation');
            }

            if (newPostPreviewCircle) {
                newPostPreviewMap.removeLayer(newPostPreviewCircle);
                newPostPreviewCircle = null;
            }

            if (Number.isFinite(accuracy) && accuracy > 0) {
                newPostPreviewCircle = L.circle([lat, lng], {
                    radius: accuracy,
                    color: '#2f80ed',
                    fillColor: '#2f80ed',
                    fillOpacity: 0.12,
                    weight: 1
                }).addTo(newPostPreviewMap);
            }

            window.setTimeout(() => newPostPreviewMap.invalidateSize(), 100);

            reverseGeocodeLocation(lat, lng).then(placeLabel => {
                if (newPostPreviewMarker) {
                    newPostPreviewMarker.bindPopup(`<strong>Position de creation</strong><br>${htmlEscape(placeLabel)}`);
                }
                setMapPlaceLabel(mapElement, placeLabel);
            });
        }

        function clearNewPostLocationPreview() {
            const preview = document.getElementById('new-post-location-preview');
            if (preview) preview.hidden = true;
        }

        function analyzeProduct() {
            const queryField = document.getElementById('product-query');
            const resultBox = document.getElementById('product-analysis-result');
            const hiddenField = document.getElementById('product-analysis-json');
            const query = queryField ? queryField.value.trim() : '';

            if (!query) {
                alert('Veuillez saisir un nom de produit ou un code-barres.');
                return;
            }

            resultBox.hidden = false;
            resultBox.className = 'product-analysis-result is-loading';
            resultBox.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Analyse du produit en cours...';

            fetch('/Web/controller/productAnalysisController.php?q=' + encodeURIComponent(query), {
                    cache: 'no-store'
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        currentProductAnalysis = null;
                        if (hiddenField) hiddenField.value = '';
                        resultBox.className = 'product-analysis-result is-error';
                        resultBox.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> ${htmlEscape(data.message || 'Produit non trouve.')}`;
                        return;
                    }

                    currentProductAnalysis = data.product;
                    if (hiddenField) hiddenField.value = JSON.stringify(data.product);
                    resultBox.className = 'product-analysis-result is-ready';
                    resultBox.innerHTML = renderProductAnalysis(data.product);
                })
                .catch(() => {
                    currentProductAnalysis = null;
                    if (hiddenField) hiddenField.value = '';
                    resultBox.className = 'product-analysis-result is-error';
                    resultBox.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Impossible de contacter Open Food Facts pour le moment.';
                });
        }

        function analyzeProductForEdit(postId) {
            const queryField = document.getElementById(`edit-product-query-${postId}`);
            const resultBox = document.getElementById(`edit-product-analysis-result-${postId}`);
            const hiddenField = document.getElementById(`edit-product-analysis-json-${postId}`);
            const query = queryField ? queryField.value.trim() : '';

            if (!query) {
                alert('Veuillez saisir un nom de produit ou un code-barres.');
                return;
            }

            resultBox.hidden = false;
            resultBox.className = 'product-analysis-result is-loading';
            resultBox.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Analyse du produit en cours...';

            fetch('/Web/controller/productAnalysisController.php?q=' + encodeURIComponent(query), {
                    cache: 'no-store'
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        if (hiddenField) hiddenField.value = '';
                        resultBox.className = 'product-analysis-result is-error';
                        resultBox.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> ${htmlEscape(data.message || 'Produit non trouve.')}`;
                        return;
                    }

                    if (hiddenField) hiddenField.value = JSON.stringify(data.product);
                    resultBox.className = 'product-analysis-result is-ready';
                    resultBox.innerHTML = renderProductAnalysis(data.product).replace('onclick="insertProductAnalysisInPost()"', 'disabled');
                })
                .catch(() => {
                    if (hiddenField) hiddenField.value = '';
                    resultBox.className = 'product-analysis-result is-error';
                    resultBox.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Impossible de contacter Open Food Facts pour le moment.';
                });
        }

        function renderProductAnalysis(product) {
            const image = product.image
                ? `<img src="${htmlEscape(product.image)}" alt="${htmlEscape(product.name)}" class="product-analysis-image">`
                : '<div class="product-analysis-image product-analysis-placeholder"><i class="fa-solid fa-bowl-food"></i></div>';

            const allergens = Array.isArray(product.allergens) && product.allergens.length
                ? product.allergens.slice(0, 4).map(item => `<span>${htmlEscape(item)}</span>`).join('')
                : '<span>Aucun allergene renseigne</span>';

            return `
                <div class="product-analysis-card">
                    ${image}
                    <div class="product-analysis-content">
                        <div class="product-analysis-title-row">
                            <div>
                                <strong>${htmlEscape(product.name)}</strong>
                                ${product.brand ? `<small>${htmlEscape(product.brand)}</small>` : ''}
                            </div>
                            <span class="nutrition-score nutrition-score-${htmlEscape(String(product.nutriScore || '').toLowerCase())}">
                                Nutri-Score ${htmlEscape(product.nutriScore || 'N/A')}
                            </span>
                        </div>
                        <div class="nutrition-metrics">
                            <span><b>${formatMetric(product.calories)}</b> kcal</span>
                            <span><b>${formatMetric(product.sugar)}</b> sucres</span>
                            <span><b>${formatMetric(product.fat)}</b> matieres grasses</span>
                            <span><b>${formatMetric(product.salt)}</b> sel</span>
                        </div>
                        <div class="product-allergens">${allergens}</div>
                        <button type="button" class="btn btn-sm btn-success" onclick="insertProductAnalysisInPost()">
                            <i class="fa-solid fa-plus"></i> Ajouter au message
                        </button>
                    </div>
                </div>
            `;
        }

        function formatMetric(value) {
            if (value === null || value === undefined || value === '') {
                return '-';
            }
            return htmlEscape(String(value));
        }

        function insertProductAnalysisInPost() {
            if (!currentProductAnalysis) return;

            const contentField = document.getElementById('new-content');
            if (!contentField) return;

            const product = currentProductAnalysis;
            const allergens = Array.isArray(product.allergens) && product.allergens.length
                ? product.allergens.join(', ')
                : 'non renseignes';

            const summary = [
                '',
                'Analyse nutritionnelle du produit :',
                '- Produit : ' + (product.name || 'Non renseigne'),
                '- Marque : ' + (product.brand || 'Non renseignee'),
                '- Nutri-Score : ' + (product.nutriScore || 'Non disponible'),
                '- Calories : ' + (product.calories ?? '-') + ' kcal / 100g',
                '- Sucres : ' + (product.sugar ?? '-') + ' g / 100g',
                '- Sel : ' + (product.salt ?? '-') + ' g / 100g',
                '- Allergenes : ' + allergens,
                'Source : Open Food Facts'
            ].join('\n');

            contentField.value = contentField.value.trim()
                ? contentField.value.trim() + '\n' + summary
                : summary.trim();
            contentField.focus();
        }

        function toggleProductAnalysis(postId) {
            const panel = document.getElementById(`product-analysis-panel-${postId}`);
            if (!panel) return;
            panel.hidden = !panel.hidden;
        }

        function requestCurrentPostLocation(fromButton) {
            const status = document.getElementById('post-location-status');
            const latInput = document.getElementById('new-latitude');
            const lngInput = document.getElementById('new-longitude');
            const accuracyInput = document.getElementById('new-location-accuracy');

            if (!navigator.geolocation || !latInput || !lngInput || !accuracyInput) {
                if (status) status.textContent = 'La geolocalisation n est pas disponible sur ce navigateur ou cette page doit etre ouverte en localhost/HTTPS.';
                clearNewPostLocationPreview();
                return Promise.resolve(false);
            }

            if (latInput.value && lngInput.value && !fromButton) {
                return Promise.resolve(true);
            }

            if (status) status.textContent = 'Recherche de votre position...';

            return new Promise(resolve => {
                navigator.geolocation.getCurrentPosition(
                    position => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        const accuracy = Math.round(position.coords.accuracy || 0);

                        latInput.value = lat.toFixed(8);
                        lngInput.value = lng.toFixed(8);
                        accuracyInput.value = accuracy;
                        if (status) {
                            status.textContent = 'Position ajoutee. La carte ci-dessous montre le lieu de creation.';
                        }
                        updateNewPostLocationPreview(lat, lng, accuracy);
                        resolve(true);
                    },
                    error => {
                        latInput.value = '';
                        lngInput.value = '';
                        accuracyInput.value = '';
                        if (status) {
                            const denied = error && error.code === error.PERMISSION_DENIED;
                            status.textContent = denied
                                ? 'Acces a la position refuse. Autorisez la localisation du navigateur pour ajouter la carte.'
                                : 'Position non ajoutee. La publication peut quand meme etre envoyee.';
                        }
                        clearNewPostLocationPreview();
                        resolve(false);
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 7000,
                        maximumAge: 60000
                    }
                );
            });
        }

        function submitPost() {
            const title = document.getElementById('new-title').value;
            const content = document.getElementById('new-content').value;
            const categoryInput = document.getElementById('new-post-category');
            const imageInput = document.getElementById('new-image');
            const productAnalysisInput = document.getElementById('product-analysis-json');
            const latitudeInput = document.getElementById('new-latitude');
            const longitudeInput = document.getElementById('new-longitude');
            const accuracyInput = document.getElementById('new-location-accuracy');

            requestCurrentPostLocation(false).then(() => {
                const formData = new FormData();
                formData.append('title', title);
                formData.append('content', content);
                formData.append('post_category', categoryInput ? categoryInput.value : 'advice');
                if (productAnalysisInput && productAnalysisInput.value) {
                    formData.append('product_analysis_json', productAnalysisInput.value);
                }
                if (latitudeInput && latitudeInput.value && longitudeInput && longitudeInput.value) {
                    formData.append('latitude', latitudeInput.value);
                    formData.append('longitude', longitudeInput.value);
                    if (accuracyInput && accuracyInput.value) {
                        formData.append('location_accuracy', accuracyInput.value);
                    }
                }
                if (imageInput.files[0]) {
                    formData.append('image', imageInput.files[0]);
                }

                fetch('/Web/controller/postController.php?action=create', {
                        method: 'POST',
                        body: formData
                    })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (data.moderationQueued && typeof triggerModerationJobs === 'function') {
                            triggerModerationJobs();
                        }
                        location.reload();
                    } else {
                        alert(data.message || "Erreur lors de la publication");
                    }
                });
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
            const categoryInput = document.getElementById(`edit-post-category-${id}`);
            const imageInput = document.getElementById(`edit-image-${id}`);
            const productAnalysisInput = document.getElementById(`edit-product-analysis-json-${id}`);

            const formData = new FormData();
            formData.append('id', id);
            formData.append('title', title);
            formData.append('content', content);
            formData.append('post_category', categoryInput ? categoryInput.value : 'advice');
            if (productAnalysisInput) {
                formData.append('product_analysis_json', productAnalysisInput.value);
            }
            if (imageToRemove[id]) {
                formData.append('remove_image', '1');
            }
            if (imageInput.files[0]) {
                formData.append('image', imageInput.files[0]);
            }

            fetch('/Web/controller/postController.php?action=update', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (data.moderationQueued && typeof triggerModerationJobs === 'function') {
                            triggerModerationJobs();
                        }
                        location.reload();
                    } else {
                        alert(data.message || "Erreur lors de la modification");
                    }
                });
        }

        function deletePost(id) {
            if (!confirm("Voulez-vous vraiment supprimer cette publication ?")) return;

            fetch('/Web/controller/postController.php?action=delete', {
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
            fetch('/Web/controller/postController.php?action=react', {
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

        function copyPostLink(postId, button) {
            const url = `${window.location.origin}${window.location.pathname}#post-${postId}`;
            const done = () => {
                if (!button) return;
                const original = button.innerHTML;
                button.innerHTML = '<i class="fa-solid fa-check"></i> Copied';
                window.setTimeout(() => {
                    button.innerHTML = original;
                }, 1600);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(done).catch(() => prompt('Copiez le lien de la publication :', url));
                return;
            }

            prompt('Copiez le lien de la publication :', url);
            done();
        }

        function likeComment(commentId) {
            fetch('/Web/controller/commentController.php?action=like', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${commentId}`
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        alert(data.message || 'Erreur like');
                        return;
                    }

                    const summary = data.likeSummary || {};
                    const countElement = document.getElementById(`comment-like-count-${commentId}`);
                    const button = document.querySelector(`#comment-${commentId} .comment-like-btn`);

                    if (countElement) {
                        countElement.textContent = summary.likes_count ?? 0;
                    }
                    if (button) {
                        const active = Boolean(summary.user_liked);
                        button.classList.toggle('is-active', active);
                        button.setAttribute('aria-pressed', active ? 'true' : 'false');
                    }
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

            fetch('/Web/controller/postController.php?action=report', {
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
                statusElement.textContent = `Vous avez signale cette publication pour : ${reasonText}.`;
                labelElement.textContent = 'Signale';
                toggleButton.classList.add('is-reported');
                if (detailsField) {
                    detailsField.value = report.details || '';
                }
                if (reasonField && report.reason) {
                    reasonField.value = report.reason;
                }
            } else {
                statusElement.textContent = '';
                labelElement.textContent = 'Signaler';
                toggleButton.classList.remove('is-reported');
            }
        }

        function addComment(postId) {
            const content = document.getElementById(`comment-content-${postId}`).value.trim();
            if (!content) return alert("Le commentaire ne peut pas être vide");

            fetch('/Web/controller/commentController.php?action=add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `post_id=${postId}&content=${encodeURIComponent(content)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (data.moderationQueued && typeof triggerModerationJobs === 'function') {
                            triggerModerationJobs();
                        }
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

            fetch('/Web/controller/commentController.php?action=add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `post_id=${postId}&parent_comment_id=${parentCommentId}&content=${encodeURIComponent(content)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (data.moderationQueued && typeof triggerModerationJobs === 'function') {
                            triggerModerationJobs();
                        }
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

            fetch('/Web/controller/commentController.php?action=update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${id}&content=${encodeURIComponent(content)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (data.moderationQueued && typeof triggerModerationJobs === 'function') {
                            triggerModerationJobs();
                        }
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

            fetch('/Web/controller/commentController.php?action=delete', {
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

        // Fonctions actualites
        function viewNewsArticle(newsId) {
            if (fallbackNewsArticles[newsId]) {
                showNewsModal(fallbackNewsArticles[newsId]);
                return;
            }

            fetch(`/Web/controller/newsController.php?action=getById&id=${newsId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showNewsModal(data.data);
                    } else {
                        alert(data.message || 'Erreur lors du chargement de l article');
                    }
                });
        }

        function showNewsModal(article) {
            const oldModal = document.getElementById('newsModal');
            if (oldModal) {
                oldModal.remove();
            }

            const modal = document.createElement('div');
            const articleContent = formatArticleContent(article.content || article.summary || 'Aucun contenu disponible pour cet article.');

            modal.className = 'modal-overlay news-modal-overlay';
            modal.id = 'newsModal';
            modal.innerHTML = `
                <div class="news-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="newsModalTitle">
                    <button type="button" class="modal-close" onclick="this.closest('.modal-overlay').remove()" aria-label="Fermer l article">
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
                return '<p>Aucun contenu disponible pour cet article.</p>';
            }

            if (/<[a-z][\s\S]*>/i.test(text)) {
                return text;
            }

            return '<p>' + htmlEscape(text).replace(/\n{2,}/g, '</p><p>').replace(/\n/g, '<br>') + '</p>';
        }

        initAllFrontPostMaps();
    </script>
