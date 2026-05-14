<?php
$chatWidgetHistory = $_SESSION['chat_history'] ?? [];

if (!is_array($chatWidgetHistory)) {
    $chatWidgetHistory = [];
}

$chatWidgetAutoOpen = !empty($openChatbotOnLoad);
?>
<link href="/projet-web-25-26/view/front/assets/css/chatbot.css" rel="stylesheet">

<button
    type="button"
    id="chatToggle"
    aria-label="<?= $chatWidgetAutoOpen ? 'Fermer le chatbot' : 'Chat' ?>"
    aria-controls="chatBox"
    aria-expanded="<?= $chatWidgetAutoOpen ? 'true' : 'false' ?>">
    <?php if ($chatWidgetAutoOpen): ?>
        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true">
            <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
        </svg>
    <?php else: ?>
        <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true">
            <path d="M20 2H4C2.9 2 2 2.9 2 4v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
        </svg>
    <?php endif; ?>
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
            <span class="mood-badge" id="moodBadge">Ã°Å¸ËœÅ  En forme</span>
        </div>
        <div class="chat-header-actions">
            <button type="button" id="clearChat" class="chat-header-button" aria-label="Effacer la conversation" title="Effacer la conversation">
                <svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor" aria-hidden="true">
                    <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                </svg>
            </button>
        </div>
    </div>

    <div id="chatMessages" class="chat-messages">
        <div id="chatNotice" class="chat-notice" aria-live="polite"></div>

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
    </div>

    <div class="quick-replies">
        <button class="qr-btn" data-msg="Quelles sont mes calories aujourd'hui ?">Calories</button>
        <button class="qr-btn" data-msg="Parle-moi des protÃƒÂ©ines">Proteines</button>
        <button class="qr-btn" data-msg="Conseils hydratation">Hydratation</button>
        <button class="qr-btn" data-msg="Donne-moi un conseil">Conseil</button>
        <button class="qr-btn" data-msg="Mon statut nutritionnel"> Statut</button>
    </div>

    <form id="chatForm" class="chat-input-bar">
        <div class="mascot-speech" id="mascotSpeech"></div>

        <svg class="peek-mascot state-idle" id="mascotSvg" viewBox="0 0 120 150" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <line x1="60" y1="6" x2="60" y2="22" stroke="#94a3b8" stroke-width="2.5" stroke-linecap="round"/>
            <circle cx="60" cy="5" r="5.5" fill="#22c55e"/>
            <circle cx="60" cy="5" r="2.8" fill="#86efac"/>
            <g class="ear-l">
                <ellipse cx="23" cy="44" rx="7.5" ry="10" fill="#e2e8f0" stroke="#cbd5e1" stroke-width="1.2"/>
                <ellipse cx="23" cy="44" rx="4" ry="5.5" fill="#bfdbfe"/>
            </g>
            <g class="ear-r">
                <ellipse cx="97" cy="44" rx="7.5" ry="10" fill="#e2e8f0" stroke="#cbd5e1" stroke-width="1.2"/>
                <ellipse cx="97" cy="44" rx="4" ry="5.5" fill="#bfdbfe"/>
            </g>
            <ellipse cx="60" cy="44" rx="31" ry="29" fill="#e8edf2" opacity="0.08"/>
            <ellipse cx="60" cy="44" rx="31" ry="29" fill="#f8fafc" stroke="#e2e8f0" stroke-width="1.5"/>
            <path d="M38 68 Q30 80 34 96 Q42 108 60 110 Q78 108 86 96 Q90 80 82 68 Q70 72 60 71 Q50 72 38 68Z" fill="#f0f4f8" stroke="#e2e8f0" stroke-width="1.2"/>
            <path d="M38 76 Q22 82 20 92 Q20 98 26 98" stroke="#e2e8f0" stroke-width="8" stroke-linecap="round" fill="none"/>
            <path d="M38 76 Q22 82 20 92 Q20 98 26 98" stroke="#f8fafc" stroke-width="5" stroke-linecap="round" fill="none"/>
            <path d="M82 78 Q98 84 100 94 Q100 100 94 100" stroke="#e2e8f0" stroke-width="8" stroke-linecap="round" fill="none"/>
            <path d="M82 78 Q98 84 100 94 Q100 100 94 100" stroke="#f8fafc" stroke-width="5" stroke-linecap="round" fill="none"/>
            <path id="browL" d="M40 29 Q44 26 48 28" stroke="#94a3b8" stroke-width="1.8" fill="none" stroke-linecap="round"/>
            <path id="browR" d="M72 30 Q76 27 80 29" stroke="#94a3b8" stroke-width="1.8" fill="none" stroke-linecap="round"/>
            <ellipse cx="44" cy="42" rx="7.5" ry="7.5" fill="#eef7ff" stroke="#9fc8ef" stroke-width="1"/>
            <ellipse cx="76" cy="42" rx="7.5" ry="7.5" fill="#eef7ff" stroke="#9fc8ef" stroke-width="1"/>
            <circle id="iris-left"  cx="44" cy="42" r="5.8" fill="#2e7ecb" opacity="0.82"/>
            <circle id="iris-right" cx="76" cy="42" r="5.8" fill="#2e7ecb" opacity="0.82"/>
            <circle id="eye-left"   cx="44" cy="42" r="3.2" fill="#0d1b2a"/>
            <circle id="eye-right"  cx="76" cy="42" r="3.2" fill="#0d1b2a"/>
            <circle id="spec-left"  cx="46" cy="40" r="1.5" fill="white"/>
            <circle id="spec-right" cx="78" cy="40" r="1.5" fill="white"/>
            <circle id="spec2-left"  cx="43" cy="44" r="0.7" fill="white" opacity="0.5"/>
            <circle id="spec2-right" cx="75" cy="44" r="0.7" fill="white" opacity="0.5"/>

            <ellipse cx="33" cy="53" rx="8.5" ry="5" fill="#fda4af" opacity="0.4"/>
            <ellipse cx="87" cy="53" rx="8.5" ry="5" fill="#fda4af" opacity="0.4"/>
            <path id="mouth" d="M50 58 Q60 67 70 58" stroke="#94a3b8" stroke-width="2" fill="none" stroke-linecap="round"/>
        </svg>

        <textarea
            id="chatInput"
            name="message"
            rows="1"
            placeholder="Posez votre question nutritionnelle..."
            aria-label="Votre message au chatbot"></textarea>

        <button type="submit" id="chatSend" aria-label="Envoyer le message">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M2 21l21-9L2 3v7l15 2-15 2z"/>
            </svg>
        </button>
    </form>
</div>

<script src="/projet-web-25-26/view/front/assets/js/chatbot.js" defer></script>
