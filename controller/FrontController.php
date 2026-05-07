<?php
require_once __DIR__ . '/../model/Activite.php';
require_once __DIR__ . '/../model/Exercice.php';

class FrontController {
    private function encode($str) {
        return utf8_decode($str);
    }

    public function home() {
        require_once __DIR__ . '/../View/front_home.php';
    }

    public function listActivites() {
        $activiteModel = new Activite();
        $activites = $activiteModel->getAll();
        
        require_once __DIR__ . '/../View/front_activites.php';
    }

    public function showExercices() {
        if (!isset($_GET['id'])) {
            header('Location: index.php?action=activites');
            exit;
        }

        $id = $_GET['id'];
        $activiteModel = new Activite();
        $activite = $activiteModel->getById($id);

        if (!$activite) {
            header('Location: index.php?action=activites');
            exit;
        }

        $exerciceModel = new Exercice();
        $exercices = $exerciceModel->getByActiviteId($id);

        require_once __DIR__ . '/../View/front_exercices.php';
    }

    public function nutritionRequest() {
        require_once __DIR__ . '/../View/front_nutrition_form.php';
    }

    public function processNutritionRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['user_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $weight = $_POST['current_weight'] ?? '';
            $goal = $_POST['current_goal'] ?? '';
            $height = $_POST['height'] ?? '';
            $message = trim($_POST['message'] ?? '');

            // Strict Validation
            if (empty($name) || empty($email) || empty($weight) || empty($goal)) {
                header('Location: index.php?action=nutrition_request&error=empty_fields');
                exit;
            }
            if (is_numeric($name)) {
                header('Location: index.php?action=nutrition_request&error=invalid_name');
                exit;
            }
            if (!is_numeric($weight) || $weight < 1 || $weight > 300) {
                header('Location: index.php?action=nutrition_request&error=invalid_weight');
                exit;
            }
            if (!in_array($goal, ['lose weight', 'gain muscle', 'maintain weight'])) {
                header('Location: index.php?action=nutrition_request&error=invalid_goal');
                exit;
            }
            if (!empty($height) && (!is_numeric($height) || $height < 50 || $height > 250)) {
                header('Location: index.php?action=nutrition_request&error=invalid_height');
                exit;
            }

            require_once __DIR__ . '/../utils/ProfanityFilter.php';
            if (ProfanityFilter::checkArray([$name, $message])) {
                header('Location: index.php?action=nutrition_request&error=profanity');
                exit;
            }

            require_once __DIR__ . '/../model/NutritionRequest.php';
            $requestModel = new NutritionRequest();
            $requestModel->user_name = htmlspecialchars($name);
            $requestModel->email = htmlspecialchars($email);
            $requestModel->current_weight = (float)$weight;
            $requestModel->current_goal = $goal;
            $requestModel->height = !empty($height) ? (float)$height : null;
            $requestModel->message = htmlspecialchars($message);
            
            // Generate Suggestions
            $activiteModel = new Activite();
            session_start();
            $excludeIds = $_SESSION['last_suggested_ids'] ?? [];
            $suggestedActs = $activiteModel->getSuggestedActivities($goal, $excludeIds);
            $actNames = array_column($suggestedActs, 'nom_activite');
            $requestModel->generated_activities = implode(", ", $actNames);

            $exerciceModel = new Exercice();
            $suggestedExs = [];
            foreach ($suggestedActs as $act) {
                $exs = $exerciceModel->getByActiviteId($act['id_activite']);
                foreach ($exs as $ex) {
                    $suggestedExs[] = $ex['nom_exercice'];
                }
            }
            $requestModel->generated_exercises = implode(", ", array_slice($suggestedExs, 0, 5));
            
            $requestModel->status = 'pending';
            $requestModel->selected_exercises = '';

            $requestId = $requestModel->create();

            if ($requestId) {
                $suggestedIds = array_column($suggestedActs, 'id_activite');
                $_SESSION['last_suggested_ids'] = $suggestedIds;
                
                // Get API Exercises
                require_once __DIR__ . '/../utils/ExerciseApiService.php';
                $apiService = new ExerciseApiService();
                $apiExs = $apiService->getRandomExercises(2);

                $_SESSION['last_suggestions'] = [
                    'activities' => $suggestedActs,
                    'api_exercises' => $apiExs,
                    'goal' => $goal
                ];

                // Send confirmation email via PHPMailer/Brevo
                require_once __DIR__ . '/../utils/MailService.php';
                MailService::sendThankYouEmail($email, $name);

                header("Location: index.php?action=nutrition_success");
                exit;
            } else {
                header('Location: index.php?action=nutrition_request&error=db_error');
                exit;
            }
        }
    }

    public function nutritionSuccess() {
        require_once __DIR__ . '/../View/front_nutrition_success.php';
    }

    public function myRequests() {
        $term = $_GET['search'] ?? '';
        $sort = $_GET['sort'] ?? 'date_desc';

        require_once __DIR__ . '/../model/NutritionRequest.php';
        $requestModel = new NutritionRequest();
        $requests = $requestModel->searchAndSort($term, $sort);
        
        require_once __DIR__ . '/../View/front_nutrition_list.php';
    }

    public function activites() {
        $term = $_GET['search'] ?? '';
        $sort = $_GET['sort'] ?? 'name_asc';

        require_once __DIR__ . '/../model/Activite.php';
        $activiteModel = new Activite();
        $activites = $activiteModel->searchAndSort($term, $sort);
        
        require_once __DIR__ . '/../View/front_activites.php';
    }

    public function editRequest() {
        if (!isset($_GET['id'])) {
            header('Location: index.php?action=my_nutrition_requests');
            exit;
        }
        
        require_once __DIR__ . '/../model/NutritionRequest.php';
        $requestModel = new NutritionRequest();
        $request = $requestModel->getById((int)$_GET['id']);
        
        if (!$request) {
            header('Location: index.php?action=my_nutrition_requests');
            exit;
        }
        
        require_once __DIR__ . '/../View/front_nutrition_edit.php';
    }

    public function updateRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            
            require_once __DIR__ . '/../model/NutritionRequest.php';
            $requestModel = new NutritionRequest();
            $existing = $requestModel->getById($id);

            if (!$existing) {
                header('Location: index.php?action=my_nutrition_requests');
                exit;
            }

            $name = trim($_POST['user_name'] ?? '');
            $weight = $_POST['current_weight'] ?? '';
            $goal = $_POST['current_goal'] ?? '';
            $height = $_POST['height'] ?? '';
            $message = trim($_POST['message'] ?? '');

            // Server-side validation
            if (empty($name) || empty($weight) || empty($goal)) {
                header("Location: index.php?action=edit_nutrition_request&id=$id&error=empty_fields");
                exit;
            }
            if (is_numeric($name)) {
                header("Location: index.php?action=edit_nutrition_request&id=$id&error=invalid_name");
                exit;
            }
            if (!is_numeric($weight) || $weight <= 0 || $weight > 300) {
                header("Location: index.php?action=edit_nutrition_request&id=$id&error=invalid_weight");
                exit;
            }
            if (!empty($height) && (!is_numeric($height) || $height < 50 || $height > 250)) {
                header("Location: index.php?action=edit_nutrition_request&id=$id&error=invalid_height");
                exit;
            }

            $requestModel->id = $id;
            $requestModel->user_name = htmlspecialchars($name);
            $requestModel->current_weight = (float)$weight;
            $requestModel->current_goal = $goal;
            $requestModel->height = !empty($height) ? (float)$height : null;
            $requestModel->message = htmlspecialchars($message);
            
            $requestModel->updateUser();
            
            header("Location: index.php?action=my_nutrition_requests");
            exit;
        }
    }

    public function deleteRequest() {
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            require_once __DIR__ . '/../model/NutritionRequest.php';
            $requestModel = new NutritionRequest();
            
            $requestData = $requestModel->getById($id);
            if ($requestData) {
                $requestModel->delete($id);
            }
        }
        header('Location: index.php?action=my_nutrition_requests');
        exit;
    }

    public function exportNutritionPDF() {
        $term = $_GET['search'] ?? '';
        $sort = $_GET['sort'] ?? 'date_desc';

        require_once __DIR__ . '/../model/NutritionRequest.php';
        $requestModel = new NutritionRequest();
        $requests = $requestModel->searchAndSort($term, $sort);

        require_once __DIR__ . '/../utils/SmartPDF.php';
        $pdf = new SmartPDF();
        $pdf->AliasNbPages();
        $pdf->setReportTitle('RAPPORT DES DEMANDES NUTRITIONNELLES');
        $pdf->AddPage();
        
        $header = ['Utilisateur', 'Objectif', 'Poids', 'Statut', 'Date'];
        $widths = [50, 50, 25, 35, 30];
        $pdf->StyledHeader($header, $widths);

        $fill = false;
        foreach ($requests as $r) {
            $pdf->SetFillColor(245, 245, 245);
            $pdf->Cell($widths[0], 10, $this->encode($r['user_name'] ?? 'N/A'), 'LR', 0, 'L', $fill);
            $pdf->Cell($widths[1], 10, $this->encode($r['current_goal'] ?? 'N/A'), 'LR', 0, 'L', $fill);
            $pdf->Cell($widths[2], 10, ($r['current_weight'] ?? '0') . ' kg', 'LR', 0, 'C', $fill);
            $pdf->Cell($widths[3], 10, $this->encode($r['status'] ?? 'pending'), 'LR', 0, 'C', $fill);
            $pdf->Cell($widths[4], 10, substr($r['created_at'] ?? date('Y-m-d'), 0, 10), 'LR', 1, 'C', $fill);
            $fill = !$fill;
        }
        $pdf->Cell(array_sum($widths), 0, '', 'T'); // Bottom line

        if (ob_get_length()) ob_clean();
        $pdf->Output('I', 'demandes_nutritionnelles.pdf');
        exit;
    }

    public function exportActivitePDF() {
        $term = $_GET['search'] ?? '';
        $sort = $_GET['sort'] ?? 'name_asc';

        require_once __DIR__ . '/../model/Activite.php';
        $activiteModel = new Activite();
        $activites = $activiteModel->searchAndSort($term, $sort);

        require_once __DIR__ . '/../utils/SmartPDF.php';
        $pdf = new SmartPDF();
        $pdf->AliasNbPages();
        $pdf->setReportTitle('CATALOGUE DES ACTIVITES SPORTIVES');
        $pdf->AddPage();

        foreach ($activites as $a) {
            // Block Header
            $pdf->SetFillColor(240, 240, 240);
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->SetTextColor(15, 23, 42); // Dark Blue
            $pdf->Cell(0, 12, '  ' . $this->encode($a['nom_activite']), 0, 1, 'L', true);
            
            // Sub-info
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Cell(95, 8, '   ' . $this->encode('Durée: ') . $a['duree_minutes'] . ' min', 0, 0);
            $pdf->Cell(95, 8, 'Calories: ' . $a['calories_brulees'] . ' kcal', 0, 1);
            
            // Description
            $pdf->SetFont('Arial', '', 11);
            $pdf->SetTextColor(50, 50, 50);
            $pdf->SetX(15);
            $pdf->MultiCell(180, 7, $this->encode($a['description']), 0, 'L');
            
            $pdf->Ln(5);
            $pdf->SetDrawColor(200, 200, 200);
            $pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX() + 190, $pdf->GetY());
            $pdf->Ln(5);
        }

        if (ob_get_length()) ob_clean();
        $pdf->Output('I', 'catalogue_activites.pdf');
        exit;
    }
}
