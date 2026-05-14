<?php

require_once __DIR__ . '/../model/Recette.php';
require_once __DIR__ . '/../model/aliment.php';
require_once __DIR__ . '/../model/Database.php';

class RecetteController
{
    private PDO $db;
    private Recette $model;
    private Aliment $alimentModel;
    private string $baseUrl = '/projet-web-25-26';

    public function __construct($pdo = null)
    {
        $this->db = $pdo instanceof PDO ? $pdo : Database::getConnection();
        $this->model = new Recette($this->db);
        $this->alimentModel = new Aliment($this->db);
    }

    public function listRecettes(): array { return $this->model->getAll(); }
    public function countRecettes(): int { return $this->model->countAll(); }
    public function getLatestRecettes(int $limit = 5): array { return $this->model->getLatest($limit); }
    public function getRecette($id): ?array { return $this->model->getById((int) $id); }
    public function getAlimentsByRecette($recetteId): array { return $this->model->getIngredientsByRecette((int) $recetteId); }
    public function addRecette($nom, $description, $tempsPreparation, $difficulte, $imageUrl = null, $alimentsQuantites = []): bool
    {
        return $this->model->create([
            'nom' => $nom,
            'description' => $description,
            'temps_preparation' => $tempsPreparation,
            'difficulte' => $difficulte,
            'image_url' => $imageUrl,
        ], $alimentsQuantites);
    }
    public function updateRecette($id, $nom, $description, $tempsPreparation, $difficulte, $imageUrl = null, $alimentsQuantites = []): bool
    {
        return $this->model->updateRecette((int) $id, [
            'nom' => $nom,
            'description' => $description,
            'temps_preparation' => $tempsPreparation,
            'difficulte' => $difficulte,
            'image_url' => $imageUrl,
        ], $alimentsQuantites);
    }
    public function deleteRecette($id): bool { return $this->model->deleteRecette((int) $id); }
    public function checkEquilibreNutritionnel($alimentsQuantites): array { return $this->model->checkEquilibreNutritionnel($alimentsQuantites); }
    public function calculerNutritionTotale($recetteId): array { return $this->model->calculerNutritionTotale((int) $recetteId); }
    public function generateRecipeFromConstraints($maxKcal, $minProt, $maxLipides, $dietType) { return $this->model->generateRecipeFromConstraints((float) $maxKcal, (float) $minProt, (float) $maxLipides, (string) $dietType); }
    public function optimiserRecette($recetteId, $objectif = 'equilibre_global') { return $this->model->optimiserRecette((int) $recetteId, (string) $objectif); }
    public function appliquerOptimisation($recetteId, $nouvellesQuantites): void { $this->model->appliquerOptimisation((int) $recetteId, (array) $nouvellesQuantites); }
    public function getStatistiquesNutritionnelles() { return $this->model->getStatistiquesNutritionnelles(); }

    private function hasRecipeSchema(): bool
    {
        return Database::tableExists($this->db, 'recettes')
            && Database::tableExists($this->db, 'aliments')
            && Database::tableExists($this->db, 'recette_aliment');
    }

    private function getRecipesStylesheets(): array
    {
        $recipesStylesheetPath = __DIR__ . '/../view/front/assets/css/recipes.css';

        return is_file($recipesStylesheetPath)
            ? [$this->baseUrl . '/view/front/assets/css/recipes.css?v=' . filemtime($recipesStylesheetPath)]
            : [];
    }

    private function renderFront(string $viewPath, array $vars = []): void
    {
        $pageTitle = $vars['pageTitle'] ?? 'Smart Nutrition - Recettes';
        $bodyClass = trim((string) (($vars['bodyClass'] ?? '') . ' recipes-page'));
        $showFooter = $vars['showFooter'] ?? false;
        $baseUrl = $this->baseUrl;
        extract($vars, EXTR_SKIP);
        require __DIR__ . '/../view/layouts/header.php';
        require $viewPath;
        require __DIR__ . '/../view/layouts/footer.php';
    }

    private function renderAdmin(string $viewPath, array $vars = []): void
    {
        $pageTitle = $vars['pageTitle'] ?? 'Back Office - Recettes';
        $isAdminTemplate = true;
        $bodyClass = trim((string) (($vars['bodyClass'] ?? '') . ' backoffice-page recipes-admin-page'));
        $baseUrl = $this->baseUrl;
        extract($vars, EXTR_SKIP);
        require __DIR__ . '/../view/layouts/header.php';
        require $viewPath;
        require __DIR__ . '/../view/layouts/footer.php';
    }

    public function showRecipesManagement(): void
    {
        $moduleUnavailableMessage = null;
        if (!$this->hasRecipeSchema()) {
            $moduleUnavailableMessage = 'Module recettes temporairement indisponible. Lancez la migration fix_recettes_integration.php.';
        }

        $recettes = $this->hasRecipeSchema() ? $this->listRecettes() : [];
        $aliments = Database::tableExists($this->db, 'aliments') ? $this->alimentModel->getAll() : [];

        $this->renderFront(__DIR__ . '/../view/front/recettes/index.php', [
            'pageTitle' => 'Nos Recettes',
            'bodyClass' => 'recipe-catalog-page',
            'additionalStylesheets' => $this->getRecipesStylesheets(),
            'recettes' => $recettes,
            'aliments' => $aliments,
            'moduleUnavailableMessage' => $moduleUnavailableMessage,
        ]);
    }

    public function showRecipeDetails(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $recette = $id > 0 ? $this->getRecette($id) : null;
        $aliments_associes = $recette ? $this->getAlimentsByRecette((int) $recette['id']) : [];
        $nutrition_totale = $recette ? $this->calculerNutritionTotale((int) $recette['id']) : [];
        $optimised_flash = isset($_GET['optimised']) && $_GET['optimised'] === '1';

        $this->renderFront(__DIR__ . '/../view/front/recettes/details.php', [
            'pageTitle' => 'Detail recette',
            'bodyClass' => 'recipe-detail-page',
            'additionalStylesheets' => $this->getRecipesStylesheets(),
            'recette' => $recette,
            'aliments_associes' => $aliments_associes,
            'nutrition_totale' => $nutrition_totale,
            'optimised_flash' => $optimised_flash,
        ]);
    }

    public function showAlimentDetails(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $aliment = $id > 0 ? $this->alimentModel->getById($id) : null;

        $this->renderFront(__DIR__ . '/../view/front/recettes/aliment.php', [
            'pageTitle' => 'Detail aliment',
            'bodyClass' => 'recipe-aliment-page',
            'additionalStylesheets' => $this->getRecipesStylesheets(),
            'aliment' => $aliment,
        ]);
    }

    public function showGenerator(): void
    {
        $generated = null;
        $errorMsg = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
            $generated = $this->generateRecipeFromConstraints(
                (float) ($_POST['max_kcal'] ?? 0),
                (float) ($_POST['min_prot'] ?? 0),
                (float) ($_POST['max_lipides'] ?? 0),
                (string) ($_POST['diet_type'] ?? 'standard')
            );

            if (!$generated) {
                $errorMsg = "Aucune combinaison trouvee pour ces contraintes. Essayez d'etre moins restrictif.";
            }
        }

        $this->renderFront(__DIR__ . '/../view/front/recettes/generate.php', [
            'pageTitle' => 'Generation recette',
            'bodyClass' => 'recipe-generator-page',
            'additionalStylesheets' => $this->getRecipesStylesheets(),
            'generated' => $generated,
            'errorMsg' => $errorMsg,
        ]);
    }

    public function showOptimizer(): void
    {
        $objectif = (string) ($_POST['objectif'] ?? $_GET['objectif'] ?? 'equilibre_global');
        $id_recette = isset($_POST['id_recette']) ? (int) $_POST['id_recette'] : (int) ($_GET['id'] ?? 0);
        $recette = $id_recette > 0 ? $this->getRecette($id_recette) : null;
        $result = $recette ? $this->optimiserRecette($id_recette, $objectif) : null;

        $objectifLabels = [
            'equilibre_global' => ['label' => 'Equilibre Global', 'icon' => 'fa-scale-balanced', 'color' => '#2ecc71'],
            'plus_proteines' => ['label' => 'Plus de Proteines', 'icon' => 'fa-dumbbell', 'color' => '#3498db'],
            'moins_lipides' => ['label' => 'Moins de Lipides', 'icon' => 'fa-droplet-slash', 'color' => '#f39c12'],
            'plus_fibres' => ['label' => 'Plus de Fibres', 'icon' => 'fa-leaf', 'color' => '#27ae60'],
        ];
        $objInfo = $objectifLabels[$objectif] ?? $objectifLabels['equilibre_global'];

        $this->renderFront(__DIR__ . '/../view/front/recettes/optimize.php', [
            'pageTitle' => 'Optimisation recette',
            'bodyClass' => 'recipe-optimizer-page',
            'additionalStylesheets' => $this->getRecipesStylesheets(),
            'objectif' => $objectif,
            'id_recette' => $id_recette,
            'recette' => $recette,
            'result' => $result,
            'objectifLabels' => $objectifLabels,
            'objInfo' => $objInfo,
        ]);
    }

    public function saveOptimization(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->baseUrl . '/index.php?action=recipes-management');
            exit;
        }

        $id_recette = (int) ($_POST['id_recette'] ?? 0);
        $objectif = (string) ($_POST['objectif'] ?? 'equilibre_global');
        $nouvellesQuantites = $_POST['nouvelles_quantites'] ?? [];
        $quantitesCastes = [];

        foreach ($nouvellesQuantites as $alimentId => $qte) {
            $alimentIdInt = (int) $alimentId;
            $qteFloat = (float) $qte;
            if ($alimentIdInt > 0 && $qteFloat >= 0) {
                $quantitesCastes[$alimentIdInt] = $qteFloat;
            }
        }

        if ($id_recette <= 0 || empty($quantitesCastes)) {
            header('Location: ' . $this->baseUrl . '/index.php?action=recipe-optimize&id=' . $id_recette . '&objectif=' . urlencode($objectif) . '&error=empty');
            exit;
        }

        $this->appliquerOptimisation($id_recette, $quantitesCastes);
        header('Location: ' . $this->baseUrl . '/index.php?action=recipe-details&id=' . $id_recette . '&optimised=1');
        exit;
    }

    public function showStats(): void
    {
        $stats = $this->getStatistiquesNutritionnelles();
        $this->renderFront(__DIR__ . '/../view/front/recettes/stats.php', [
            'pageTitle' => 'Statistiques nutritionnelles',
            'bodyClass' => 'recipe-stats-page',
            'additionalStylesheets' => $this->getRecipesStylesheets(),
            'stats' => $stats,
        ]);
    }

    public function exportPdf(): void
    {
        require __DIR__ . '/../view/front/recettes/export_pdf.php';
    }

    public function adminIndex(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $alimentsIds = $_POST['aliments'] ?? [];
            $quantitesRaw = $_POST['quantites'] ?? [];
            $alimentsQuantites = [];
            foreach ($alimentsIds as $id) {
                $alimentsQuantites[(int) $id] = (float) ($quantitesRaw[$id] ?? 0);
            }

            $imageUrl = $_POST['existing_image_url'] ?? null;
            if (isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../view/uploads/recettes/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileName = uniqid('recette_', true) . '_' . basename((string) $_FILES['image']['name']);
                $targetPath = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $imageUrl = $this->baseUrl . '/view/uploads/recettes/' . $fileName;
                }
            }

            if ($_POST['action'] === 'add') {
                $this->addRecette($_POST['nom'] ?? '', $_POST['description'] ?? '', $_POST['temps_preparation'] ?? '', $_POST['difficulte'] ?? '', $imageUrl, $alimentsQuantites);
                $_SESSION['flash_warnings'] = $this->checkEquilibreNutritionnel($alimentsQuantites);
            } elseif ($_POST['action'] === 'update') {
                $this->updateRecette((int) ($_POST['id'] ?? 0), $_POST['nom'] ?? '', $_POST['description'] ?? '', $_POST['temps_preparation'] ?? '', $_POST['difficulte'] ?? '', $imageUrl, $alimentsQuantites);
                $_SESSION['flash_warnings'] = $this->checkEquilibreNutritionnel($alimentsQuantites);
            } elseif ($_POST['action'] === 'delete') {
                $this->deleteRecette((int) ($_POST['id'] ?? 0));
            }

            header('Location: ' . $this->baseUrl . '/index.php?action=admin-recipes');
            exit;
        }

        $flashWarnings = $_SESSION['flash_warnings'] ?? [];
        unset($_SESSION['flash_warnings']);

        $recettes = $this->listRecettes();
        $tous_aliments = $this->alimentModel->getAll();
        $recettes_aliments_map = [];
        $recettes_aliments_quantites_map = [];
        $recettesNutritionMap = [];

        foreach ($recettes as $recette) {
            $assoc = $this->getAlimentsByRecette((int) $recette['id']);
            $recettes_aliments_map[$recette['id']] = array_map(static fn($a) => $a['id'], $assoc);
            $quantitesMap = [];
            foreach ($assoc as $aliment) {
                $quantitesMap[$aliment['id']] = $aliment['quantite'];
            }
            $recettes_aliments_quantites_map[$recette['id']] = $quantitesMap;
            $recettesNutritionMap[$recette['id']] = $this->calculerNutritionTotale((int) $recette['id']);
        }

        $recetteToEdit = null;
        $editId = (int) ($_GET['edit_id'] ?? 0);
        if ($editId > 0) {
            foreach ($recettes as $recette) {
                if ((int) $recette['id'] === $editId) {
                    $recetteToEdit = $recette;
                    break;
                }
            }
        }

        $this->renderAdmin(__DIR__ . '/../view/back/recettes/index.php', [
            'pageTitle' => 'Back Office - Recettes',
            'recettes' => $recettes,
            'tous_aliments' => $tous_aliments,
            'recettes_aliments_map' => $recettes_aliments_map,
            'recettes_aliments_quantites_map' => $recettes_aliments_quantites_map,
            'recettesNutritionMap' => $recettesNutritionMap,
            'recetteToEdit' => $recetteToEdit,
            'flashWarnings' => array_filter((array) $flashWarnings),
        ]);
    }
}
