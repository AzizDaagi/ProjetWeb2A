document.addEventListener('DOMContentLoaded', function () {
  const toggle = document.getElementById('chatToggle');
  const box = document.getElementById('chatBox');
  const input = document.getElementById('chatInput');
  const clearButton = document.getElementById('clearChat');
  const form = document.getElementById('chatForm');
  const sendButton = document.getElementById('chatSend');
  const messages = document.getElementById('chatMessages');
  const notice = document.getElementById('chatNotice');
  const mascot = document.getElementById('mascotSvg');
  const eyeL = document.getElementById('eye-left');
  const eyeR = document.getElementById('eye-right');
  const quickReplyButtons = document.querySelectorAll('.qr-btn');

  const pulseStorageKey = 'snChatWidgetPulseSeen';
  const welcomeMessage = "Bonjour, je peux répondre rapidement sur les calories, les protéines, l'hydratation et quelques conseils nutritionnels simples.";
  let botReplyHandledManually = false;

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

  function renderToggleIcon(isHidden) {
    toggle.innerHTML = isHidden
      ? '<svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true"><path d="M20 2H4C2.9 2 2 2.9 2 4v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>'
      : '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>';
  }

  function syncState(forcedHidden) {
    const isHidden = typeof forcedHidden === 'boolean'
      ? forcedHidden
      : box.classList.contains('chat-hidden');

    toggle.setAttribute('aria-expanded', isHidden ? 'false' : 'true');
    toggle.setAttribute('aria-label', isHidden ? 'Chat' : 'Fermer le chatbot');
    box.setAttribute('aria-hidden', isHidden ? 'true' : 'false');
    renderToggleIcon(isHidden);
  }

  function showNotice(message) {
    if (!notice) {
      return;
    }

    notice.textContent = message || '';

    if (message) {
      window.clearTimeout(showNotice._tid);
      showNotice._tid = window.setTimeout(function () {
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

    if (role !== 'user' && !customNode && typeof window.onBotReply === 'function' && !botReplyHandledManually) {
      window.onBotReply(source);
    }

    return wrapper;
  }

  function createTypingIndicator() {
    const dots = document.createElement('div');
    dots.className = 'chatbox-typing';

    for (let i = 0; i < 3; i += 1) {
      dots.appendChild(document.createElement('span'));
    }

    return appendMessage('bot', '', 'local', currentTime(), dots);
  }

  function autoResize() {
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 100) + 'px';
  }

  (function initMascot() {
    const mouthEl = document.getElementById('mouth');
    const browL = document.getElementById('browL');
    const browR = document.getElementById('browR');
    const speechEl = document.getElementById('mascotSpeech');
    const badgeEl = document.getElementById('moodBadge');
    const inputEl = document.getElementById('chatInput');
    const formEl = document.getElementById('chatForm');

    if (!mascot || !eyeL || !eyeR || !mouthEl || !browL || !browR) {
      return;
    }

    const SPEECHES = {
      focus: "Hmm… dis-moi tout, je t'écoute 👂",
      typing: "Je lis ce que tu écris…",
      waiting: "Tu voulais me demander quelque chose ? 😊",
      sending: "Hmm… laisse-moi analyser ça pour toi 🤔",
      replyLocal: "Voilà ! C'est ma spécialité 💪",
      replyMistral: "J'ai demandé à Mistral pour toi 🤖",
      empty: "Écris quelque chose d'abord ! ✍️",
      click: "Coucou ! Besoin d'un conseil nutrition ? 🥗",
      happy: "Super question ! 🎉",
      calories: "Je vérifie tes calories du jour 🔥",
      proteines: "Les protéines, mon domaine ! 💪",
      hydration: "Reste bien hydraté(e) ! 💧",
      conseil: "Un conseil personnalisé pour toi 💡",
      statut: "Analyse de ton statut en cours… 📊"
    };

    const STATES = {
      idle: { badge: '😊 En forme', mouth: 'M50 58 Q60 67 70 58', bL: 'M40 29 Q44 26 48 28', bR: 'M72 30 Q76 27 80 29' },
      happy: { badge: '🎉 Super !', mouth: 'M48 56 Q60 68 72 56', bL: 'M40 27 Q44 23 48 25', bR: 'M72 25 Q76 23 80 27' },
      surprise: { badge: '😲 Surpris !', mouth: 'M52 57 Q60 68 68 57', bL: 'M40 25 Q44 21 48 23', bR: 'M72 23 Q76 21 80 25' },
      think: { badge: '🤔 Réflexion', mouth: 'M50 60 Q60 62 70 60', bL: 'M40 30 Q44 28 48 32', bR: 'M72 28 Q76 26 80 30' },
      shy: { badge: '🙈 Timide…', mouth: 'M52 62 Q60 58 68 62', bL: 'M41 32 Q44 30 47 31', bR: 'M73 31 Q76 30 79 32' },
      wave: { badge: '👋 Bonjour !', mouth: 'M48 56 Q60 68 72 56', bL: 'M40 27 Q44 23 48 25', bR: 'M72 25 Q76 23 80 27' },
      shake: { badge: '😤 Non non !', mouth: 'M50 62 Q60 58 70 62', bL: 'M40 28 Q43 25 47 28', bR: 'M73 28 Q77 25 80 28' }
    };

    const QR_REACTIONS = {
      calories: { s: 'happy', parts: ['🔥', '⚡', '✨'], sk: 'calories' },
      'protéines': { s: 'happy', parts: ['💪', '⭐', '🎯'], sk: 'proteines' },
      hydratation: { s: 'wave', parts: ['💧', '🌊', '✨'], sk: 'hydration' },
      conseil: { s: 'think', parts: ['💡', '🌟', '✨'], sk: 'conseil' },
      statut: { s: 'surprise', parts: ['📊', '🎉', '⭐'], sk: 'statut' }
    };

    let stateTimer = null;
    let hideTimer = null;
    let typeTimer = null;
    let pendingQuickReplyReaction = null;
    let pendingQuickReplyTimer = null;
    let suppressNextBotReplySpeech = false;
    let lx = 44;
    let rx = 76;
    let ly = 42;
    let ry = 42;
    let tlx = 44;
    let trx = 76;
    let tly = 42;
    let tryValue = 42;
    function showMascot() {
      mascot.classList.remove('is-hiding');
      mascot.classList.add('is-visible');
      window.clearTimeout(hideTimer);
    }

    function scheduleMascotHide(delay) {
      window.clearTimeout(hideTimer);
      hideTimer = window.setTimeout(function () {
        mascot.classList.remove('is-visible');
        mascot.classList.add('is-hiding');
      }, delay || 3000);
    }

    function setState(stateName) {
      if (stateTimer) {
        window.clearTimeout(stateTimer);
      }

      const data = STATES[stateName] || STATES.idle;
      const visibilityClass = mascot.classList.contains('is-visible') ? ' is-visible' : '';
      const hidingClass = mascot.classList.contains('is-hiding') ? ' is-hiding' : '';

      mascot.className = 'peek-mascot state-' + stateName + visibilityClass + hidingClass;
      mouthEl.setAttribute('d', data.mouth);
      browL.setAttribute('d', data.bL);
      browR.setAttribute('d', data.bR);

      if (badgeEl) {
        badgeEl.textContent = data.badge;
      }
    }

    function showSpeech(text) {
      if (!speechEl || !text) {
        return;
      }

      speechEl.textContent = text;
      speechEl.classList.add('show');
    }

    function hideSpeech() {
      if (speechEl) {
        speechEl.classList.remove('show');
      }
    }

    function triggerState(stateName, speechKey, duration) {
      setState(stateName);
      showSpeech(SPEECHES[speechKey] || speechKey);
      stateTimer = window.setTimeout(function () {
        setState('idle');
        hideSpeech();
      }, duration || 1800);
    }

    function spawnParticles(emojis) {
      const rect = mascot.getBoundingClientRect();

      emojis.forEach(function (emoji, index) {
        window.setTimeout(function () {
          const particle = document.createElement('div');
          particle.className = 'mascot-particle';
          particle.textContent = emoji;
          particle.style.left = (rect.right - 26 + (Math.random() - .5) * 40) + 'px';
          particle.style.top = (rect.top - 8 + (Math.random() - .5) * 16) + 'px';
          document.body.appendChild(particle);
          window.setTimeout(function () {
            particle.remove();
          }, 1100);
        }, index * 100);
      });
    }

    window.setState = setState;
    window.triggerState = triggerState;
    window.showSpeech = showSpeech;
    window.hideSpeech = hideSpeech;
    window.onBotReply = function (source) {
      showMascot();

      if (suppressNextBotReplySpeech) {
        suppressNextBotReplySpeech = false;
        return;
      }

      if (pendingQuickReplyReaction || pendingQuickReplyTimer) {
        return;
      }

      if (source === 'huggingface' || source === 'mistral' || source === 'api') {
        triggerState('surprise', 'replyMistral', 2200);
        spawnParticles(['🤖', '⚡', '🌟']);
      } else {
        triggerState('happy', 'replyLocal', 1800);
        spawnParticles(['✨', '⭐']);
      }

      scheduleMascotHide(3500);
    };

    mascot.addEventListener('click', function () {
      const options = [
        { s: 'happy', sk: 'click', parts: ['⭐', '✨', '🌟'] },
        { s: 'wave', sk: 'happy', parts: ['👋', '🌟', '✨'] },
        { s: 'surprise', sk: 'click', parts: ['🎉', '⭐', '✨'] }
      ];
      const option = options[Math.floor(Math.random() * options.length)];

      showMascot();
      triggerState(option.s, option.sk);
      spawnParticles(option.parts);
      scheduleMascotHide(3000);
    });

    if (inputEl) {
      inputEl.addEventListener('focus', function () {
        showMascot();
        window.clearTimeout(hideTimer);

        if (box.dataset.introPending === '1') {
          box.dataset.introPending = '0';
          triggerState('wave', 'focus', 1200);
          scheduleMascotHide(1600);
        }
      });

      inputEl.addEventListener('blur', function () {
        setState('idle');
        hideSpeech();
        scheduleMascotHide(3000);
      });

      inputEl.addEventListener('input', function () {
        showMascot();
        if (pendingQuickReplyTimer) {
          window.clearTimeout(pendingQuickReplyTimer);
          pendingQuickReplyTimer = null;
        }
        pendingQuickReplyReaction = null;
        scheduleMascotHide(4000);
      });
    }

    if (formEl) {
      formEl.addEventListener('submit', function () {
        const text = inputEl ? inputEl.value.trim() : '';

        if (!text) {
          showMascot();
          triggerState('shake', 'empty', 1500);
          scheduleMascotHide(2500);
          return;
        }

        showMascot();
        setState('think');
        showSpeech(SPEECHES.sending);
        spawnParticles(['💭', '⚡']);
        scheduleMascotHide(6000);
      }, true);
    }

    document.querySelectorAll('.qr-btn').forEach(function (button) {
      button.addEventListener('click', function () {
        const message = (button.dataset.msg || '').toLowerCase();
        const key = Object.keys(QR_REACTIONS).find(function (entry) {
          return message.includes(entry);
        });

        if (!key) {
          return;
        }

        const reaction = QR_REACTIONS[key];
        if (pendingQuickReplyTimer) {
          window.clearTimeout(pendingQuickReplyTimer);
        }
        pendingQuickReplyReaction = reaction;
        showMascot();
        setState('think');
        showSpeech(SPEECHES.sending);
        pendingQuickReplyTimer = window.setTimeout(function () {
          if (!pendingQuickReplyReaction) {
            pendingQuickReplyTimer = null;
            return;
          }

          triggerState(pendingQuickReplyReaction.s, pendingQuickReplyReaction.sk, 3000);
          spawnParticles(pendingQuickReplyReaction.parts);
          pendingQuickReplyReaction = null;
          pendingQuickReplyTimer = null;
          suppressNextBotReplySpeech = true;
          scheduleMascotHide(3400);
        }, 2800);
        scheduleMascotHide(6000);
      });
    });

    showMascot();
    scheduleMascotHide(2000);

    document.addEventListener('mousemove', function (event) {
      const rect = mascot.getBoundingClientRect();
      const centerX = rect.left + rect.width / 2;
      const centerY = rect.top + rect.height / 2;
      const dx = ((event.clientX - centerX) / window.innerWidth) * 7;
      const dy = ((event.clientY - centerY) / window.innerHeight) * 7;
      tlx = 44 + dx;
      trx = 76 + dx;
      tly = 42 + dy;
      tryValue = 42 + dy;
    });

    (function tick() {
      lx += (tlx - lx) * .09;
      rx += (trx - rx) * .09;
      ly += (tly - ly) * .09;
      ry += (tryValue - ry) * .09;

      eyeL.setAttribute('cx', Math.min(47, Math.max(41, lx)));
      eyeR.setAttribute('cx', Math.min(79, Math.max(73, rx)));
      eyeL.setAttribute('cy', Math.min(45, Math.max(39, ly)));
      eyeR.setAttribute('cy', Math.min(45, Math.max(39, ry)));

      requestAnimationFrame(tick);
    })();
  })();

  toggle.addEventListener('click', function () {
    const isHidden = box.classList.contains('chat-hidden');

    if (isHidden) {
      box.classList.remove('chat-hidden');
      syncState(false);
      box.dataset.introPending = '1';

      if (mascot) {
        mascot.classList.remove('is-hiding');
        mascot.classList.add('is-visible');
      }

      input.focus();
      scrollMessages();
      return;
    }

    box.dataset.introPending = '0';

    if (typeof window.hideSpeech === 'function') {
      window.hideSpeech();
    }

    if (mascot) {
      mascot.classList.remove('is-visible');
      mascot.classList.add('is-hiding');
    }

    box.classList.add('chat-hidden');
    syncState(true);
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
      showNotice('Conversation effacée ✓');
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
    autoResize();
    sendButton.disabled = true;
    showNotice('Assistant en train de répondre...');

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
      botReplyHandledManually = true;
      appendMessage(
        'bot',
        data.response || (response.ok ? "Je n'ai pas trouvé de réponse." : "Je n'ai pas pu traiter votre message."),
        data.source || 'local',
        data.time || currentTime()
      );
      if (typeof window.onBotReply === 'function') {
        window.onBotReply(data.source || 'local');
      }
      botReplyHandledManually = false;
    } catch (error) {
      typingNode.remove();
      botReplyHandledManually = true;
      appendMessage('bot', "Le service est momentanément indisponible. Essayez une question plus simple ou revenez plus tard.", 'local', currentTime());
      if (typeof window.onBotReply === 'function') {
        window.onBotReply('local');
      }
      botReplyHandledManually = false;
    } finally {
      sendButton.disabled = false;
      showNotice('');
      input.focus();
      scrollMessages();
    }
  });

  input.addEventListener('keydown', function (event) {
    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault();
      form.dispatchEvent(new Event('submit', { cancelable: true }));
    }
  });

  input.addEventListener('input', autoResize);

  quickReplyButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      const message = button.dataset.msg;

      if (!message) {
        return;
      }

      input.value = message;
      autoResize();
      form.dispatchEvent(new Event('submit', { cancelable: true }));
    });
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
  autoResize();
});
