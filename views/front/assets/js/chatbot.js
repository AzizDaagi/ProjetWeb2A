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
  const irisL = document.getElementById('iris-left');
  const irisR = document.getElementById('iris-right');
  const eyeL = document.getElementById('eye-left');
  const eyeR = document.getElementById('eye-right');
  const specLeft = document.getElementById('spec-left');
  const specRight = document.getElementById('spec-right');
  const spec2Left = document.getElementById('spec2-left');
  const spec2Right = document.getElementById('spec2-right');
  const quickReplyButtons = document.querySelectorAll('.qr-btn');

  const pulseStorageKey = 'snChatWidgetPulseSeen';
  const welcomeMessage = "Bonjour, je peux répondre rapidement sur les calories, les protéines, l'hydratation et quelques conseils nutritionnels simples.";
  let botReplyHandledManually = false;
  let blockSpeech = false;

  if (!toggle || !box || !input || !clearButton || !form || !sendButton || !messages) {
    return;
  }

  document.documentElement.appendChild(toggle);
  document.documentElement.appendChild(box);
  eyeL.classList.add('mascot-eye');
  eyeR.classList.add('mascot-eye');

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

  function isChatOpen() {
    return !box.classList.contains('chat-hidden');
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

  function hasConversationMessages() {
    return messages.querySelector('.chat-message') !== null;
  }

  function appendMessage(role, text, source, time, customNode) {
    if (!customNode && (!text || text.trim() === '')) {
      return;
    }

    removeEmptyState();

    const wrapper = document.createElement('div');
    wrapper.className = 'chat-message ' + (role === 'user' ? 'is-user' : 'is-bot');

    const bubble = document.createElement('div');
    bubble.className = 'chat-bubble';

    if (customNode) {
      bubble.appendChild(customNode);
    } else {
      bubble.innerHTML = text;
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
    const mascotPaths = mascot ? mascot.querySelectorAll('path') : [];
    const leftArmEls = [mascotPaths[1], mascotPaths[2]].filter(Boolean);
    const rightArmEls = [mascotPaths[3], mascotPaths[4]].filter(Boolean);

    if (!mascot || !eyeL || !eyeR || !mouthEl || !browL || !browR) {
      return;
    }

    leftArmEls.forEach(function (armPath) {
      armPath.classList.add('mascot-arm-left');
    });

    rightArmEls.forEach(function (armPath) {
      armPath.classList.add('mascot-arm-right');
    });

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
    let entranceSequenceTimer = null;
    let armWaveResetTimer = null;
    let blinkTimer = null;
    let replyBounceTimer = null;
    let hoverSpeechTimer = null;
    let pendingQuickReplyReaction = null;
    let pendingQuickReplyTimer = null;
    let suppressNextBotReplySpeech = false;
    let cinematicTimers = [];
    let cinematicEyeMotionActive = false;
    let isMascotHovering = false;
    let lx = 44;
    let rx = 76;
    let ly = 42;
    let ry = 42;
    let tlx = 44;
    let trx = 76;
    let tly = 42;
    let tryValue = 42;
    let lastMouseMoveAt = Date.now();
    let hoverRotateCurrent = 0;
    let hoverRotateTarget = 0;
    let hoverLiftCurrent = 0;
    let hoverLiftTarget = 0;
    let hoverScaleCurrent = 1;
    let hoverScaleTarget = 1;
    let gazeShiftXCurrent = 0;
    let gazeShiftXTarget = 0;
    let gazeShiftYCurrent = 0;
    let gazeShiftYTarget = 0;
    let gazeRotateCurrent = 0;
    let gazeRotateTarget = 0;

    function clamp(value, min, max) {
      return Math.max(min, Math.min(max, value));
    }

    function clearHideTimers() {
      window.clearTimeout(hideTimer);
    }

    function clearCinematicTimers() {
      cinematicTimers.forEach(function (timerId) {
        window.clearTimeout(timerId);
      });
      cinematicTimers = [];
      window.clearTimeout(armWaveResetTimer);
      armWaveResetTimer = null;
    }

    function queueCinematicStep(callback, delay) {
      const timerId = window.setTimeout(function () {
        cinematicTimers = cinematicTimers.filter(function (activeId) {
          return activeId !== timerId;
        });
        callback();
      }, delay);

      cinematicTimers.push(timerId);
      return timerId;
    }

    function scheduleRandomBlink() {
      window.clearTimeout(blinkTimer);
      blinkTimer = window.setTimeout(function () {
        if (!mascot.classList.contains('is-cinematic')) {
          eyeL.classList.add('is-blinking');
          eyeR.classList.add('is-blinking');

          window.setTimeout(function () {
            eyeL.classList.remove('is-blinking');
            eyeR.classList.remove('is-blinking');
          }, 110);
        }

        scheduleRandomBlink();
      }, 3000 + Math.random() * 3000);
    }

    function showHoverSpeech() {
      window.clearTimeout(hoverSpeechTimer);
      showSpeech('👀');
      hoverSpeechTimer = window.setTimeout(function () {
        if (isMascotHovering && !mascot.classList.contains('is-cinematic')) {
          showSpeech('👀');
          return;
        }

        hideSpeech();
      }, 700);
    }

    function showMascot() {
      if (mascot.classList.contains('is-cinematic')) {
        return;
      }

      mascot.classList.remove('is-hiding');
      mascot.classList.add('is-visible');
      clearHideTimers();
    }

    function scheduleMascotHide(delay) {
      if (mascot.classList.contains('is-cinematic')) {
        return;
      }

      clearHideTimers();
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
      const cinematicClass = mascot.classList.contains('is-cinematic') ? ' is-cinematic' : '';

      mascot.className = 'peek-mascot state-' + stateName + visibilityClass + hidingClass + cinematicClass;
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

      if (blockSpeech) {
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

    function hideSpeechInstant() {
      if (!speechEl) {
        return;
      }

      speechEl.classList.remove('show');
      speechEl.classList.add('speech-hidden');
    }

    function showSpeechSmooth() {
      if (!speechEl) {
        return;
      }

      speechEl.classList.add('show');
      requestAnimationFrame(function () {
        speechEl.classList.remove('speech-hidden');
      });
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

    function replayWave() {
      mascot.classList.remove('state-wave');
      void mascot.offsetWidth;
      mascot.classList.add('state-wave');
    }

    function setCinematicEyes(offsetX, offsetY) {
      cinematicEyeMotionActive = true;
      tlx = 44 + offsetX;
      trx = 76 + offsetX;
      tly = 42 + offsetY;
      tryValue = 42 + offsetY;
      gazeShiftXTarget = 0;
      gazeShiftYTarget = 0;
      gazeRotateTarget = 0;
    }

    function resetCinematicEyes() {
      cinematicEyeMotionActive = false;
      tlx = 44;
      trx = 76;
      tly = 42;
      tryValue = 42;
      gazeShiftXTarget = 0;
      gazeShiftYTarget = 0;
      gazeRotateTarget = 0;
    }

    function replayArmWave() {
      if (!leftArmEls.length && !rightArmEls.length) {
        return;
      }

      window.clearTimeout(armWaveResetTimer);

      leftArmEls.forEach(function (armPath) {
        armPath.classList.remove('arm-wave-cinematic');
        void armPath.offsetWidth;
        armPath.classList.add('arm-wave-cinematic');
      });

      rightArmEls.forEach(function (armPath) {
        armPath.classList.remove('arm-wave-cinematic');
        void armPath.offsetWidth;
        armPath.classList.add('arm-wave-cinematic');
      });

      armWaveResetTimer = window.setTimeout(function () {
        leftArmEls.forEach(function (armPath) {
          armPath.classList.remove('arm-wave-cinematic');
        });
        rightArmEls.forEach(function (armPath) {
          armPath.classList.remove('arm-wave-cinematic');
        });
      }, 1020);
    }

    function nudgeMascotOnReply() {
      if (mascot.classList.contains('is-cinematic')) {
        return;
      }

      window.clearTimeout(replyBounceTimer);
      clearHideTimers();
      showMascot();
      setCinematicEyes(1.6, -1.2);

      mascot.style.transition = 'none';
      mascot.style.transform = 'translateY(0) translateX(0) scale(1)';

      requestAnimationFrame(function () {
        requestAnimationFrame(function () {
          mascot.style.transition = 'transform 170ms ease-out';
          mascot.style.transform = 'translateY(-9px) translateX(-2px) scaleX(1.04) scaleY(0.98)';
        });
      });

      replyBounceTimer = window.setTimeout(function () {
        mascot.style.transition = 'transform 210ms ease-in-out';
        mascot.style.transform = 'translateY(2px) translateX(0) scaleX(0.985) scaleY(1.02)';
      }, 180);

      replyBounceTimer = window.setTimeout(function () {
        mascot.style.transition = '';
        mascot.style.transform = '';
        resetCinematicEyes();
      }, 430);
    }

    function resetCinematicPresentation() {
      clearCinematicTimers();
      resetCinematicEyes();
      window.clearTimeout(replyBounceTimer);
      window.clearTimeout(hoverSpeechTimer);
      leftArmEls.forEach(function (armPath) {
        armPath.classList.remove('arm-wave-cinematic');
      });
      rightArmEls.forEach(function (armPath) {
        armPath.classList.remove('arm-wave-cinematic');
      });
      mascot.style.transition = '';
      mascot.style.transform = '';
      mascot.style.opacity = '';
      mascot.classList.remove('is-cinematic');
      hoverRotateCurrent = 0;
      hoverRotateTarget = 0;
      hoverLiftCurrent = 0;
      hoverLiftTarget = 0;
      hoverScaleCurrent = 1;
      hoverScaleTarget = 1;
      gazeShiftXCurrent = 0;
      gazeShiftXTarget = 0;
      gazeShiftYCurrent = 0;
      gazeShiftYTarget = 0;
      gazeRotateCurrent = 0;
      gazeRotateTarget = 0;
      mascot.style.setProperty('--hover-rotate', '0deg');
      mascot.style.setProperty('--hover-lift', '0px');
      mascot.style.setProperty('--hover-scale', '1');
      mascot.style.setProperty('--gaze-shift-x', '0px');
      mascot.style.setProperty('--gaze-shift-y', '0px');
      mascot.style.setProperty('--gaze-rotate', '0deg');
      blockSpeech = false;
      hideSpeech();
      setState('idle');
    }

    function cancelEntranceSequence() {
      if (!entranceSequenceTimer) {
        return;
      }

      window.clearTimeout(entranceSequenceTimer);
      entranceSequenceTimer = null;
      box.dataset.cinematicOpen = '0';
    }

    function playEntranceSequence() {
      cancelEntranceSequence();

      if (!isChatOpen() || mascot.classList.contains('is-cinematic')) {
        box.dataset.cinematicOpen = '0';
        return;
      }

      clearHideTimers();
      clearCinematicTimers();
      blockSpeech = true;
      box.dataset.cinematicOpen = '1';
      mascot.classList.add('is-cinematic', 'is-visible');
      mascot.classList.remove('is-peeking', 'is-hiding');
      hideSpeechInstant();

      mascot.style.transition = 'none';
      mascot.style.transform = 'translateY(0) translateX(0) scale(1) rotate(0deg)';
      mascot.style.opacity = '1';
      setCinematicEyes(0, 0);

      requestAnimationFrame(function () {
        requestAnimationFrame(function () {
          mascot.style.transition = 'transform 180ms ease-out';
          mascot.style.transform = 'translateY(14px) translateX(-10px) scale(0.94) rotate(2deg)';
        });
      });

      queueCinematicStep(function () {
        mascot.style.transition = 'transform 420ms cubic-bezier(0.34, 1.56, 0.64, 1)';
        mascot.style.transform = 'translateY(-136px) translateX(-90px) scale(2.54) rotate(-5.6deg)';
        setCinematicEyes(3.2, -3.5);
      }, 190);

      queueCinematicStep(function () {
        mascot.style.transition = 'transform 220ms ease-out';
        mascot.style.transform = 'translateY(-102px) translateX(-80px) scale(2.28) rotate(1.8deg)';
        setCinematicEyes(4.8, -1.2);
      }, 640);

      queueCinematicStep(function () {
        if (!mascot.classList.contains('is-cinematic')) {
          return;
        }

        setState('wave');
        replayWave();
        replayArmWave();
        spawnParticles(['👋', '✨', '⭐', '🌟']);
      }, 930);

      queueCinematicStep(function () {
        if (!mascot.classList.contains('is-cinematic')) {
          return;
        }

        setCinematicEyes(-1.6, 1.2);
      }, 1130);

      queueCinematicStep(function () {
        if (!mascot.classList.contains('is-cinematic')) {
          return;
        }

        mascot.style.transition = 'transform 500ms cubic-bezier(0.25, 1, 0.5, 1)';
        mascot.style.transform = 'translateY(0) translateX(0) scale(1) rotate(0deg)';
        setState('idle');
        setCinematicEyes(0, 0);
      }, 1450);

      queueCinematicStep(function () {
        resetCinematicEyes();
        mascot.style.transition = '';
        mascot.style.transform = '';
        mascot.style.opacity = '';
        mascot.classList.remove('is-cinematic');
        box.dataset.cinematicOpen = '0';
        blockSpeech = false;
        hideSpeech();
        if (!hasConversationMessages()) {
          botReplyHandledManually = true;
          appendMessage('bot', welcomeMessage, 'local', currentTime());
          botReplyHandledManually = false;
        }
        scheduleMascotHide(3500);
      }, 1930);
    }

    function playExitSequence() {
      return new Promise(function (resolve) {
        cancelEntranceSequence();

        if (mascot.classList.contains('is-cinematic')) {
          resolve();
          return;
        }

        clearHideTimers();
        clearCinematicTimers();
        blockSpeech = true;
        box.dataset.cinematicOpen = '0';
        mascot.classList.add('is-cinematic', 'is-visible');
        mascot.classList.remove('is-peeking', 'is-hiding');
        hideSpeechInstant();

        mascot.style.transition = 'none';
        mascot.style.transform = 'translateY(0) translateX(0) scale(1) rotate(0deg)';
        mascot.style.opacity = '1';
        setCinematicEyes(0, 0);

        requestAnimationFrame(function () {
          requestAnimationFrame(function () {
            mascot.style.transition = 'transform 170ms ease-out';
            mascot.style.transform = 'translateY(10px) translateX(-8px) scale(0.95) rotate(2deg)';
          });
        });

        queueCinematicStep(function () {
          mascot.style.transition = 'transform 360ms cubic-bezier(0.34, 1.56, 0.64, 1)';
          mascot.style.transform = 'translateY(-132px) translateX(-90px) scale(2.48) rotate(-6deg)';
          setCinematicEyes(-2.8, -2.8);
        }, 180);

        queueCinematicStep(function () {
          mascot.style.transition = 'transform 220ms ease-out';
          mascot.style.transform = 'translateY(-100px) translateX(-80px) scale(2.24) rotate(1.3deg)';
          setCinematicEyes(-4.1, -0.8);
        }, 560);

        queueCinematicStep(function () {
          if (!mascot.classList.contains('is-cinematic')) {
            return;
          }

          setState('wave');
          replayWave();
          replayArmWave();
          spawnParticles(['👋', '✨', '💫']);
        }, 840);

        queueCinematicStep(function () {
          if (!mascot.classList.contains('is-cinematic')) {
            return;
          }

          setCinematicEyes(0.8, 1.4);
        }, 1040);

        queueCinematicStep(function () {
          if (!mascot.classList.contains('is-cinematic')) {
            return;
          }

          hideSpeech();
          mascot.style.transition = 'transform 340ms ease-in, opacity 340ms ease-in';
          mascot.style.transform = 'translateY(44px) translateX(0) scale(0.12) rotate(8deg)';
          mascot.style.opacity = '0';
          setCinematicEyes(0, 0);
        }, 1280);

        queueCinematicStep(function () {
          resetCinematicPresentation();
          mascot.classList.remove('is-visible', 'is-hiding');
          resolve();
        }, 1680);
      });
    }

    function queueEntranceSequence() {
      cancelEntranceSequence();
      if (mascot.classList.contains('is-cinematic')) {
        return;
      }

      box.dataset.cinematicOpen = '1';
      entranceSequenceTimer = window.setTimeout(function () {
        entranceSequenceTimer = null;
        playEntranceSequence();
      }, 60);
    }

    window.setState = setState;
    window.triggerState = triggerState;
    window.showSpeech = showSpeech;
    window.hideSpeech = hideSpeech;
    window.__chatMascotCinematics = {
      queueEntranceSequence: queueEntranceSequence,
      playExitSequence: playExitSequence,
      cancelEntranceSequence: cancelEntranceSequence
    };
    window.onBotReply = function (source) {
      if (mascot.classList.contains('is-cinematic')) {
        return;
      }

      showMascot();
      nudgeMascotOnReply();

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
      if (mascot.classList.contains('is-cinematic')) {
        return;
      }

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

    mascot.addEventListener('mouseenter', function () {
      if (mascot.classList.contains('is-cinematic')) {
        return;
      }

      isMascotHovering = true;
      hoverScaleTarget = 1.05;
      hoverLiftTarget = -4;
      showMascot();
      clearHideTimers();
      showHoverSpeech();
    });

    mascot.addEventListener('mousemove', function (event) {
      if (!isMascotHovering || mascot.classList.contains('is-cinematic')) {
        return;
      }

      const rect = mascot.getBoundingClientRect();
      const relativeX = ((event.clientX - (rect.left + rect.width / 2)) / rect.width);
      const relativeY = ((event.clientY - (rect.top + rect.height / 2)) / rect.height);

      hoverRotateTarget = Math.max(-6, Math.min(6, relativeX * 10));
      hoverLiftTarget = Math.max(-6, Math.min(-1, -4 + (relativeY * -2)));
      hoverScaleTarget = 1.05;
      lastMouseMoveAt = Date.now();
    });

    mascot.addEventListener('mouseleave', function () {
      isMascotHovering = false;
      hoverRotateTarget = 0;
      hoverLiftTarget = 0;
      hoverScaleTarget = 1;
      window.clearTimeout(hoverSpeechTimer);
      hideSpeech();
      scheduleMascotHide(2500);
    });

    if (inputEl) {
      inputEl.addEventListener('focus', function () {
        if (box.dataset.cinematicOpen === '1' || mascot.classList.contains('is-cinematic')) {
          box.dataset.introPending = '0';
          return;
        }

        showMascot();
        window.clearTimeout(hideTimer);

        if (box.dataset.introPending === '1') {
          box.dataset.introPending = '0';
          triggerState('wave', 'focus', 1200);
          scheduleMascotHide(1600);
        }
      });

      inputEl.addEventListener('blur', function () {
        if (mascot.classList.contains('is-cinematic')) {
          return;
        }

        setState('idle');
        hideSpeech();
        scheduleMascotHide(3000);
      });

      inputEl.addEventListener('input', function () {
        if (mascot.classList.contains('is-cinematic')) {
          return;
        }

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
        if (mascot.classList.contains('is-cinematic')) {
          return;
        }

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
        if (mascot.classList.contains('is-cinematic')) {
          return;
        }

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

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && mascot.classList.contains('is-cinematic')) {
        resetCinematicPresentation();
        mascot.classList.remove('is-visible');
        box.dataset.cinematicOpen = '0';
        scheduleMascotHide(500);
      }
    });

    showMascot();
    scheduleRandomBlink();
    scheduleMascotHide(2000);

    document.addEventListener('mousemove', function (event) {
      if (cinematicEyeMotionActive || mascot.classList.contains('is-cinematic')) {
        return;
      }

      lastMouseMoveAt = Date.now();
      const rect = mascot.getBoundingClientRect();
      const centerX = rect.left + rect.width / 2;
      const centerY = rect.top + rect.height / 2;
      const normX = clamp((event.clientX - centerX) / (window.innerWidth * 0.18), -1, 1);
      const normY = clamp((event.clientY - centerY) / (window.innerHeight * 0.22), -1, 1);

      tlx = 44 + (normX * 5.2);
      trx = 76 + (normX * 5.2);
      tly = 42 + (normY * 4.1);
      tryValue = 42 + (normY * 4.1);
      gazeShiftXTarget = normX * 4.4;
      gazeShiftYTarget = normY * -2.2;
      gazeRotateTarget = normX * 3.4;
    });

    (function tick() {
      // Let the gaze linger a bit so the follow remains visible.
      if (!cinematicEyeMotionActive && !isMascotHovering && Date.now() - lastMouseMoveAt > 520) {
        tlx = 44;
        trx = 76;
        tly = 42;
        tryValue = 42;
        gazeShiftXTarget = 0;
        gazeShiftYTarget = 0;
        gazeRotateTarget = 0;
      }

      lx += (tlx - lx) * .13;
      rx += (trx - rx) * .13;
      ly += (tly - ly) * .13;
      ry += (tryValue - ry) * .13;

      if (!mascot.classList.contains('is-cinematic')) {
        hoverRotateCurrent += (hoverRotateTarget - hoverRotateCurrent) * .12;
        hoverLiftCurrent += (hoverLiftTarget - hoverLiftCurrent) * .12;
        hoverScaleCurrent += (hoverScaleTarget - hoverScaleCurrent) * .12;
        gazeShiftXCurrent += (gazeShiftXTarget - gazeShiftXCurrent) * .08;
        gazeShiftYCurrent += (gazeShiftYTarget - gazeShiftYCurrent) * .08;
        gazeRotateCurrent += (gazeRotateTarget - gazeRotateCurrent) * .08;
        mascot.style.setProperty('--hover-rotate', hoverRotateCurrent.toFixed(2) + 'deg');
        mascot.style.setProperty('--hover-lift', hoverLiftCurrent.toFixed(2) + 'px');
        mascot.style.setProperty('--hover-scale', hoverScaleCurrent.toFixed(3));
        mascot.style.setProperty('--gaze-shift-x', gazeShiftXCurrent.toFixed(2) + 'px');
        mascot.style.setProperty('--gaze-shift-y', gazeShiftYCurrent.toFixed(2) + 'px');
        mascot.style.setProperty('--gaze-rotate', gazeRotateCurrent.toFixed(2) + 'deg');
      }

      const irisLeftX = 44 + ((lx - 44) * 0.65);
      const irisRightX = 76 + ((rx - 76) * 0.65);
      const irisLeftY = 42 + ((ly - 42) * 0.65);
      const irisRightY = 42 + ((ry - 42) * 0.65);
      const pupilLeftX  = Math.min(48.0, Math.max(40.0, lx));
      const pupilRightX = Math.min(80.0, Math.max(72.0, rx));
      const pupilLeftY  = Math.min(46.0, Math.max(38.0, ly));
      const pupilRightY = Math.min(46.0, Math.max(38.0, ry));

      if (irisL && irisR) {
        irisL.setAttribute('cx', irisLeftX.toFixed(2));
        irisR.setAttribute('cx', irisRightX.toFixed(2));
        irisL.setAttribute('cy', irisLeftY.toFixed(2));
        irisR.setAttribute('cy', irisRightY.toFixed(2));
      }

      eyeL.setAttribute('cx', pupilLeftX.toFixed(2));
      eyeR.setAttribute('cx', pupilRightX.toFixed(2));
      eyeL.setAttribute('cy', pupilLeftY.toFixed(2));
      eyeR.setAttribute('cy', pupilRightY.toFixed(2));

      if (specLeft && specRight && spec2Left && spec2Right) {
        specLeft.setAttribute('cx', (pupilLeftX + 1.9).toFixed(2));
        specRight.setAttribute('cx', (pupilRightX + 1.9).toFixed(2));
        specLeft.setAttribute('cy', (pupilLeftY - 1.9).toFixed(2));
        specRight.setAttribute('cy', (pupilRightY - 1.9).toFixed(2));
        spec2Left.setAttribute('cx', (pupilLeftX - 1.1).toFixed(2));
        spec2Right.setAttribute('cx', (pupilRightX - 1.1).toFixed(2));
        spec2Left.setAttribute('cy', (pupilLeftY + 1.35).toFixed(2));
        spec2Right.setAttribute('cy', (pupilRightY + 1.35).toFixed(2));
      }

      requestAnimationFrame(tick);
    })();

    // ─── DEBUG VISUAL TEST ──────────────────────────────────────────────────
    // Set to true to auto-run on mascot click, or call window.testMascotMotion()
    // from the browser console at any time.
    const MASCOT_ANIMATION_DEBUG = false;

    window.testMascotMotion = function () {
      // 1. Audit every selector and warn loudly if anything is missing
      var checks = {
        mascot:        mascot,
        'eye-left':    eyeL,
        'eye-right':   eyeR,
        'arm-right':   rightArmEls[0] || null,
        'arm-left':    leftArmEls[0]  || null,
      };
      var allOk = true;
      Object.keys(checks).forEach(function (label) {
        if (!checks[label]) {
          console.warn('[testMascotMotion] MISSING element: ' + label);
          allOk = false;
        }
      });
      if (!allOk) {
        console.warn('[testMascotMotion] Some elements are null — animation will be partial.');
      }

      // 2. Eyes → SVG setAttribute (same path the tick loop uses)
      if (eyeL) { eyeL.setAttribute('cx', 49); eyeL.setAttribute('cy', 40); }
      if (eyeR) { eyeR.setAttribute('cx', 81); eyeR.setAttribute('cy', 40); }

      // 3. Body tilt → CSS custom properties (same path the tick loop uses)
      if (mascot) {
        mascot.style.setProperty('--hover-rotate', '5deg');
        mascot.style.setProperty('--hover-lift',   '-8px');
        mascot.style.setProperty('--gaze-shift-x', '5px');
        mascot.style.setProperty('--gaze-shift-y', '-3px');
        mascot.style.setProperty('--gaze-rotate',  '4deg');
        mascot.style.setProperty('--hover-scale',  '1.08');
      }

      // 4. Arms → cinematic wave class (same path replayArmWave uses)
      window.clearTimeout(armWaveResetTimer);
      leftArmEls.forEach(function (el) {
        el.classList.remove('arm-wave-cinematic');
        void el.offsetWidth; // force reflow so re-adding triggers animation
        el.classList.add('arm-wave-cinematic');
      });
      rightArmEls.forEach(function (el) {
        el.classList.remove('arm-wave-cinematic');
        void el.offsetWidth;
        el.classList.add('arm-wave-cinematic');
      });

      // 5. Return to neutral after 900 ms
      armWaveResetTimer = window.setTimeout(function () {
        // Reset eyes to neutral targets; the tick loop will lerp them back
        tlx = 44; trx = 76; tly = 42; tryValue = 42;

        // Reset body CSS vars to neutral
        if (mascot) {
          mascot.style.setProperty('--hover-rotate', '0deg');
          mascot.style.setProperty('--hover-lift',   '0px');
          mascot.style.setProperty('--gaze-shift-x', '0px');
          mascot.style.setProperty('--gaze-shift-y', '0px');
          mascot.style.setProperty('--gaze-rotate',  '0deg');
          mascot.style.setProperty('--hover-scale',  '1');
        }

        // Remove arm classes
        leftArmEls.forEach(function (el)  { el.classList.remove('arm-wave-cinematic'); });
        rightArmEls.forEach(function (el) { el.classList.remove('arm-wave-cinematic'); });
      }, 900);

      console.info('[testMascotMotion] fired — watch the mascot for ~900 ms.');
    };

    if (MASCOT_ANIMATION_DEBUG) {
      mascot.addEventListener('click', function () {
        window.testMascotMotion();
      });
    }
    // ─── END DEBUG ───────────────────────────────────────────────────────────
  })();

  toggle.addEventListener('click', async function (event) {
    if (!mascot || !window.__chatMascotCinematics) {
      return;
    }

    if (mascot.classList.contains('is-cinematic')) {
      event.preventDefault();
      event.stopImmediatePropagation();
      return;
    }

    if (isChatOpen()) {
      event.preventDefault();
      event.stopImmediatePropagation();
      box.dataset.introPending = '0';
      box.dataset.cinematicOpen = '0';
      window.__chatMascotCinematics.cancelEntranceSequence();
      await window.__chatMascotCinematics.playExitSequence();

      if (typeof window.hideSpeech === 'function') {
        window.hideSpeech();
      }

      box.classList.add('chat-hidden');
      syncState(true);
      return;
    }

    window.__chatMascotCinematics.queueEntranceSequence();
  }, true);

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
