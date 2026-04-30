<?php
$chatWidgetHistory = $_SESSION['chat_history'] ?? [];
$chatbotBasePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');

if ($chatbotBasePath === '/' || $chatbotBasePath === '\\') {
    $chatbotBasePath = '';
}

if (!is_array($chatWidgetHistory)) {
    $chatWidgetHistory = [];
}

$chatWidgetAutoOpen = !empty($openChatbotOnLoad);
?>
<link href="<?= htmlspecialchars($chatbotBasePath . '/public/css/chatbot.css') ?>" rel="stylesheet">
<button
    type="button"
    id="chatToggle"
    aria-label="<?= $chatWidgetAutoOpen ? 'Fermer le chatbot' : 'Chat' ?>"
    aria-controls="chatBox"
    aria-expanded="<?= $chatWidgetAutoOpen ? 'true' : 'false' ?>">
    <?= $chatWidgetAutoOpen ? '&#10006;' : '&#128172;' ?>
</button>

<div
    id="chatBox"
    class="<?= $chatWidgetAutoOpen ? '' : 'chat-hidden' ?>"
    data-auto-open="<?= $chatWidgetAutoOpen ? '1' : '0' ?>"
    aria-hidden="<?= $chatWidgetAutoOpen ? 'false' : 'true' ?>"
    aria-label="Widget chatbot nutrition">
    <div class="chat-header">
        <div class="chat-header-title">
            <span class="chat-status-dot" aria-hidden="true"></span>
            <h2 class="chat-title">Assistant Nutrition &#x1F957;</h2>
        </div>

        <div class="chat-header-actions">
            <button type="button" id="clearChat" class="chat-header-button" aria-label="Effacer la conversation">&#128465;</button>
        </div>
    </div>

    <div id="chatNotice" class="chat-notice"></div>

    <div id="chatMessages" class="chat-messages">
        <?php if (empty($chatWidgetHistory)): ?>
            <div class="chat-message is-bot">
                <div class="chat-bubble">
                    Bonjour, je peux repondre rapidement sur les calories, les proteines, l'hydratation et quelques conseils nutritionnels simples.
                </div>
                <div class="chat-meta">
                    <span><?= htmlspecialchars(date('H:i')) ?></span>
                    <span class="chat-source">local</span>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($chatWidgetHistory as $chatMessage): ?>
                <?php
                $role = ($chatMessage['role'] ?? '') === 'assistant' ? 'bot' : 'user';
                $time = trim((string) ($chatMessage['time'] ?? ''));
                $source = trim((string) ($chatMessage['source'] ?? ''));
                ?>
                <div class="chat-message is-<?= htmlspecialchars($role) ?>">
                    <div class="chat-bubble"><?= nl2br(htmlspecialchars((string) ($chatMessage['message'] ?? ''))) ?></div>
                    <div class="chat-meta">
                        <span><?= htmlspecialchars($time !== '' ? $time : date('H:i')) ?></span>
                        <?php if ($role === 'bot'): ?>
                            <span class="chat-source"><?= htmlspecialchars($source !== '' ? $source : 'local') ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <form id="chatForm" class="chat-input-bar">
        <textarea
            id="chatInput"
            name="message"
            rows="1"
            placeholder="Posez votre question nutritionnelle..."
            aria-label="Votre message au chatbot"></textarea>

        <button type="submit" id="chatSend" aria-label="Envoyer le message">Envoyer</button>
    </form>
</div>
<script src="<?= htmlspecialchars($chatbotBasePath . '/public/js/chatbot.js') ?>" defer></script>
