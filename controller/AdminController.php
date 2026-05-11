<?php
require_once __DIR__ . '/../model/Activite.php';

class AdminController {
    private function startSessionIfNeeded() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function loginView() {
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            header('Location: index.php?action=admin_dashboard');
            exit;
        }
        $error = isset($_GET['error']) ? $_GET['error'] : null;
        require_once __DIR__ . '/../View/admin_login.php';
    }

    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = isset($_POST['admin_code']) ? trim($_POST['admin_code']) : '';
            
            // Input Validation Server-Side
            if (empty($code)) {
                header('Location: index.php?action=admin_login&error=empty');
                exit;
            }
            if (!is_numeric($code) || strlen($code) !== 6) {
                header('Location: index.php?action=admin_login&error=invalid_format');
                exit;
            }

            // Check correctness
            if ($code === '000000') {
                $this->startSessionIfNeeded();
                $_SESSION['admin_logged_in'] = true;
                header('Location: index.php?action=admin_dashboard');
                exit;
            } else {
                header('Location: index.php?action=admin_login&error=incorrect');
                exit;
            }
        }
        header('Location: index.php?action=admin_login');
        exit;
    }

    public function dashboard() {
        $this->startSessionIfNeeded();
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: index.php?action=admin_login');
            exit;
        }

        $activiteModel = new Activite();
        $stats = $activiteModel->getDashboardStats();
        
        $activites = $activiteModel->getAll();
        $error = isset($_GET['error']) ? $_GET['error'] : '';

        require_once __DIR__ . '/../View/admin_dashboard.php';
    }

    public function logout() {
        $this->startSessionIfNeeded();
        session_destroy();
        header('Location: index.php?action=home');
        exit;
    }
}
?>
