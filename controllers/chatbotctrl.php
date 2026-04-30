<?php

require_once __DIR__ . '/../models/ChatbotService.php';

class chatbotctrl
{
    private $chatbotService;

    public function __construct($pdo)
    {
        $this->chatbotService = new ChatbotService($pdo);
    }

    public function index()
    {
        $openChatbotOnLoad = true;

        require __DIR__ . '/../views/front/chatbot/chat.php';
    }

    public function chatbot()
    {
        $this->handle();
    }

    public function handle()
    {
        header('Content-Type: application/json; charset=UTF-8');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'response' => "Methode non autorisee.",
                'source' => 'local',
                'error' => true,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        $message = $this->sanitizeMessage($_POST['message'] ?? '');

        if ($message === '') {
            http_response_code(422);
            echo json_encode([
                'response' => "Veuillez saisir un message avant l'envoi.",
                'source' => 'local',
                'error' => true,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        $userId = !empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
        $result = $this->chatbotService->generateResponse($message, $userId);

        $this->appendHistoryMessage('user', $message);
        $this->appendHistoryMessage('assistant', $result['response'], $result['source']);

        echo json_encode([
            'response' => $result['response'],
            'source' => $result['source'],
            'time' => date('H:i'),
            'error' => false,
            'history' => $_SESSION['chat_history'] ?? [],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public function clearChat()
    {
        $this->clear_chat();
    }

    public function clear_chat()
    {
        header('Content-Type: application/json; charset=UTF-8');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'response' => "Methode non autorisee.",
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        unset($_SESSION['chat_history']);

        echo json_encode([
            'success' => true,
            'response' => "Conversation effacee ✓",
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private function sanitizeMessage($message)
    {
        $message = strip_tags((string) $message);
        $message = preg_replace('/\s+/u', ' ', $message);

        if ($message === null) {
            return '';
        }

        $message = trim($message);

        if ($message === '') {
            return '';
        }

        return mb_substr($message, 0, 500, 'UTF-8');
    }

    private function appendHistoryMessage($role, $message, $source = null)
    {
        if (!isset($_SESSION['chat_history']) || !is_array($_SESSION['chat_history'])) {
            $_SESSION['chat_history'] = [];
        }

        $_SESSION['chat_history'][] = [
            'role' => $role === 'assistant' ? 'assistant' : 'user',
            'message' => (string) $message,
            'source' => $source,
            'time' => date('H:i'),
        ];

        $_SESSION['chat_history'] = array_slice($_SESSION['chat_history'], -20);
    }
}
