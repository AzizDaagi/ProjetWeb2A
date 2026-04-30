document.addEventListener('DOMContentLoaded', function () {
  const toggle = document.getElementById('chatToggle');
  const box = document.getElementById('chatBox');
  const input = document.getElementById('chatInput');
  const clearButton = document.getElementById('clearChat');
  const form = document.getElementById('chatForm');
  const sendButton = document.getElementById('chatSend');
  const messages = document.getElementById('chatMessages');
  const notice = document.getElementById('chatNotice');
  const pulseStorageKey = 'snChatWidgetPulseSeen';
  const welcomeMessage = "Bonjour, je peux repondre rapidement sur les calories, les proteines, l'hydratation et quelques conseils nutritionnels simples.";

  if (!toggle || !box || !input || !clearButton || !form || !sendButton || !messages) {
    return;
  }

  document.documentElement.appendChild(toggle);
  document.documentElement.appendChild(box);

  function currentTime() {
    return new Intl.DateTimeFormat('fr-FR', {
      hour: '2-digit',
      minute: '2-digit'
    }).format(new Date());
  }

  function syncState() {
    const isHidden = box.classList.contains('chat-hidden');
    toggle.textContent = isHidden ? '\u{1F4AC}' : '\u2716';
    toggle.setAttribute('aria-expanded', isHidden ? 'false' : 'true');
    toggle.setAttribute('aria-label', isHidden ? 'Chat' : 'Fermer le chatbot');
    box.setAttribute('aria-hidden', isHidden ? 'true' : 'false');
  }

  function showNotice(message) {
    if (!notice) {
      return;
    }

    notice.textContent = message || '';

    if (message) {
      window.clearTimeout(showNotice.timeoutId);
      showNotice.timeoutId = window.setTimeout(function () {
        notice.textContent = '';
      }, 2200);
    }
  }

  function removeEmptyState() {
    const emptyState = document.getElementById('chatEmptyState');

    if (emptyState) {
      emptyState.remove();
    }
  }

  function scrollMessages() {
    messages.scrollTop = messages.scrollHeight;
  }

  function appendMessage(role, text, source, time, customNode) {
    removeEmptyState();

    const wrapper = document.createElement('div');
    wrapper.className = 'chat-message ' + (role === 'user' ? 'is-user' : 'is-bot');

    const bubble = document.createElement('div');
    bubble.className = 'chat-bubble';

    if (customNode) {
      bubble.appendChild(customNode);
    } else {
      bubble.textContent = text;
    }

    const meta = document.createElement('div');
    meta.className = 'chat-meta';

    const timeNode = document.createElement('span');
    timeNode.textContent = time || currentTime();
    meta.appendChild(timeNode);

    if (role !== 'user') {
      const sourceNode = document.createElement('span');
      sourceNode.className = 'chat-source';
      sourceNode.textContent = source || 'local';
      meta.appendChild(sourceNode);
    }

    wrapper.appendChild(bubble);
    wrapper.appendChild(meta);
    messages.appendChild(wrapper);
    scrollMessages();

    return wrapper;
  }

  function createTypingIndicator() {
    const dots = document.createElement('div');
    dots.className = 'chatbox-typing';

    for (let index = 0; index < 3; index += 1) {
      dots.appendChild(document.createElement('span'));
    }

    return appendMessage('bot', '', 'local', currentTime(), dots);
  }

  toggle.addEventListener('click', function () {
    box.classList.toggle('chat-hidden');
    toggle.textContent = box.classList.contains('chat-hidden') ? '\u{1F4AC}' : '\u2716';
    syncState();
    if (!box.classList.contains('chat-hidden')) {
      input.focus();
      scrollMessages();
    }
  });

  clearButton.addEventListener('click', async function () {
    try {
      const response = await fetch('index.php?action=clear_chat', {
        method: 'POST'
      });
      const data = await response.json();

      if (!response.ok || !data.success) {
        showNotice(data.response || "Impossible d'effacer la conversation.");
        return;
      }

      messages.innerHTML = '';
      appendMessage('bot', welcomeMessage, 'local', currentTime());
      showNotice('Conversation effacee ✓');
    } catch (error) {
      showNotice("Impossible d'effacer la conversation.");
    }
  });

  form.addEventListener('submit', async function (event) {
    event.preventDefault();

    const message = input.value.trim();

    if (!message) {
      showNotice('Veuillez saisir un message.');
      input.focus();
      return;
    }

    appendMessage('user', message, '', currentTime());
    input.value = '';
    sendButton.disabled = true;
    showNotice('Assistant en train de repondre...');
    const typingNode = createTypingIndicator();

    try {
      const response = await fetch('index.php?action=chatbot', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: new URLSearchParams({
          message: message
        })
      });
      const data = await response.json();
      typingNode.remove();

      if (!response.ok || data.error) {
        appendMessage('bot', data.response || "Je n'ai pas pu traiter votre message.", data.source || 'local', data.time || currentTime());
      } else {
        appendMessage('bot', data.response || "Je n'ai pas trouve de reponse.", data.source || 'local', data.time || currentTime());
      }
    } catch (error) {
      typingNode.remove();
      appendMessage('bot', "Le service est momentanement indisponible. Essayez une question plus simple ou revenez plus tard.", 'local', currentTime());
    } finally {
      sendButton.disabled = false;
      input.focus();
      scrollMessages();
    }
  });

  if (!sessionStorage.getItem(pulseStorageKey)) {
    toggle.classList.add('is-pulsing');
    sessionStorage.setItem(pulseStorageKey, '1');
    window.setTimeout(function () {
      toggle.classList.remove('is-pulsing');
    }, 5600);
  }

  if (box.dataset.autoOpen === '1') {
    box.classList.remove('chat-hidden');
  }

  syncState();
  scrollMessages();
});
