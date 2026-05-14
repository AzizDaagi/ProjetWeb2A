<?php
require_once __DIR__ . '/../model/Activite.php';

class AdminController {
    private function startSessionIfNeeded() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    private function requireAdmin() {
        $this->startSessionIfNeeded();
        if (!isset($_SESSION['user_id']) || (($_SESSION['user_role'] ?? 'user') !== 'admin')) {
            header('Location: /smart_nutritionn/gestionActiviteesportive/index.php?action=home');
            exit;
        }
    }

    public function loginView() {
        header('Location: /smart_nutritionn/gestionActiviteesportive/index.php?action=login');
        exit;
    }

    public function authenticate() {
        header('Location: /smart_nutritionn/gestionActiviteesportive/index.php?action=login');
        exit;
    }

    public function dashboard() {
        $this->requireAdmin();

        $activiteModel = new Activite();
        $stats = $activiteModel->getDashboardStats();
        
        $activites = $activiteModel->getAll();
        $error = isset($_GET['error']) ? $_GET['error'] : '';

        require_once __DIR__ . '/../view/admin_dashboard.php';
    }

    public function logout() {
        $this->startSessionIfNeeded();
        session_destroy();
        header('Location: index.php?action=home');
        exit;
    }
}
?>
