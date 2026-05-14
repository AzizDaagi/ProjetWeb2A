document.addEventListener('DOMContentLoaded', function () {
    document.body.classList.add('is-ready');

    runInitSafely(initThemeToggle, 'theme-toggle');
    runInitSafely(initAutoDismissAlerts, 'alerts');
    runInitSafely(initFormSubmitLock, 'form-lock');
    runInitSafely(initFaceAuth, 'face-auth');
    runInitSafely(initHomeTopicButtons, 'home-topics');
    runInitSafely(initHomeWeatherCard, 'home-weather');
    runInitSafely(initVoiceControl, 'voice-control');
    runInitSafely(initNotifications, 'notifications');
    runInitSafely(initAdminModuleButtons, 'admin-modules');
    runInitSafely(initAdminUsersList, 'admin-users');
    runInitSafely(initBackgroundParallax, 'background-parallax');
    runInitSafely(initAdvancedBackground, 'background-canvas');
    runInitSafely(initFullScreenHandControl, 'gesture-control');
});

var faceAuthRuntime = {
    scriptPromise: null,
    modelsPromise: null
};

function runInitSafely(initFn, label) {
    try {
        initFn();
    } catch (error) {
        console.error('Initialization failed for ' + label + ':', error);
    }
}

function initThemeToggle() {
    var storageKey = 'smartNutritionTheme';
    var legacyStorageKey = 'theme';
    var savedTheme = null;

    try {
        savedTheme = localStorage.getItem(storageKey);
        if (savedTheme !== 'light' && savedTheme !== 'dark') {
            savedTheme = localStorage.getItem(legacyStorageKey);
        }
    } catch (error) {
        savedTheme = null;
    }

    var prefersLight =
        window.matchMedia &&
        typeof window.matchMedia === 'function' &&
        window.matchMedia('(prefers-color-scheme: light)').matches;

    var initialTheme =
        savedTheme === 'light' || savedTheme === 'dark'
            ? savedTheme
            : (prefersLight ? 'light' : 'dark');

    applyTheme(initialTheme, storageKey);

    var toggleButtons = document.querySelectorAll('#themeToggle, .theme-toggle');
    if (!toggleButtons.length) {
        return;
    }

    toggleButtons.forEach(function (toggleButton) {
        if (toggleButton.dataset.themeBound === 'true') {
            return;
        }

        toggleButton.dataset.themeBound = 'true';
        toggleButton.addEventListener('click', function () {
            var nextTheme = document.body.classList.contains('theme-light')
                ? 'dark'
                : 'light';
            applyTheme(nextTheme, storageKey);
        });
    });
}

function applyTheme(theme, storageKey) {
    var isLight = theme === 'light';

    document.body.classList.toggle('dark', !isLight);
    document.body.classList.toggle('theme-light', isLight);
    document.body.classList.toggle('theme-dark', !isLight);
    document.documentElement.style.colorScheme = isLight ? 'light' : 'dark';

    document.querySelectorAll('#themeToggle, .theme-toggle').forEach(function (toggleButton) {
        toggleButton.setAttribute('aria-pressed', isLight ? 'true' : 'false');
        toggleButton.title = isLight ? 'Passer en mode sombre' : 'Passer en mode clair';
        toggleButton.innerHTML = isLight
            ? '<i class="fa-solid fa-sun"></i> Clair'
            : '<i class="fa-solid fa-moon"></i> Sombre';
    });

    try {
        localStorage.setItem(storageKey, isLight ? 'light' : 'dark');
        localStorage.setItem('theme', isLight ? 'light' : 'dark');
    } catch (error) {
    }
}

function initAutoDismissAlerts() {
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.35s ease';
            alert.style.opacity = '0';
            setTimeout(function () {
                alert.remove();
            }, 360);
        }, 3500);
    });
}

function initFormSubmitLock() {
    var form = document.querySelector('form');
    if (!form) {
        return;
    }

    form.addEventListener('submit', function (event) {
        if (event.defaultPrevented) {
            return;
        }

        var submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Traitement...';
        }
    });
}

function initFaceAuth() {
    var cards = document.querySelectorAll('.face-auth-card[data-face-auth-mode]');
    if (!cards.length) {
        return;
    }

    cards.forEach(function (card) {
        setupFaceAuthCard(card);
    });
}

function setupFaceAuthCard(card) {
    var mode = card.getAttribute('data-face-auth-mode') || '';
    var endpoint = card.getAttribute('data-endpoint') || '';
    var clearEndpoint = card.getAttribute('data-clear-endpoint') || '';
    var isRegisterMode = mode === 'register';

    var video = card.querySelector('.face-video');
    var canvas = card.querySelector('.face-canvas');
    var statusEl = card.querySelector('.face-status');
    var badgeEl = card.querySelector('.face-state-badge');
    var startButton = card.querySelector('.js-face-start');
    var submitButton = card.querySelector('.js-face-submit');
    var clearButton = card.querySelector('.js-face-clear');
    var previewWrap = card.querySelector('.face-preview-wrap');
    var descriptorInput = card.querySelector('[data-face-descriptor-input]');

    if (!video || !canvas || !statusEl || !startButton || !submitButton || !previewWrap) {
        return;
    }

    if (!endpoint && !isRegisterMode) {
        return;
    }

    var state = {
        mode: mode,
        endpoint: endpoint,
        clearEndpoint: clearEndpoint,
        stream: null,
        isBusy: false,
        faceapi: null,
        descriptorInput: descriptorInput,
        hasSavedDescriptor: badgeEl ? badgeEl.classList.contains('is-ready') : false
    };

    function setStartButtonState(isActive) {
        startButton.innerHTML = isActive
            ? '<i class="fa-solid fa-video-slash"></i>Desactiver la camera'
            : '<i class="fa-solid fa-video"></i>Activer la camera';
    }

    function updateBadge(isReady) {
        if (!badgeEl) {
            return;
        }

        badgeEl.classList.toggle('is-ready', isReady);
        badgeEl.classList.toggle('is-missing', !isReady);
        if (state.mode === 'register') {
            badgeEl.textContent = isReady
                ? 'Empreinte faciale prete.'
                : 'Aucune empreinte faciale capturee.';
            return;
        }

        badgeEl.textContent = isReady
            ? 'Empreinte faciale active.'
            : 'Aucune empreinte faciale enregistree.';
    }

    function setStatus(message, tone) {
        statusEl.textContent = message;
        previewWrap.classList.remove('is-ready', 'is-error');

        if (tone === 'success') {
            previewWrap.classList.add('is-ready');
        }

        if (tone === 'error') {
            previewWrap.classList.add('is-error');
        }
    }

    function syncButtons() {
        var hasStream = !!state.stream;
        startButton.disabled = state.isBusy;
        submitButton.disabled = state.isBusy || !hasStream;

        if (clearButton) {
            if (state.mode === 'register') {
                var hasDescriptorValue = !!(state.descriptorInput && state.descriptorInput.value);
                clearButton.disabled = state.isBusy || !hasDescriptorValue;
            } else {
                clearButton.disabled = state.isBusy || !state.hasSavedDescriptor || !state.clearEndpoint;
            }
        }
    }

    function resizeCanvasToVideo() {
        var width = video.videoWidth || video.clientWidth || 0;
        var height = video.videoHeight || video.clientHeight || 0;

        if (width > 0 && height > 0) {
            canvas.width = width;
            canvas.height = height;
        }
    }

    function clearCanvas() {
        var ctx = canvas.getContext('2d');
        if (!ctx) {
            return;
        }

        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }

    function drawDetection(detection) {
        var ctx = canvas.getContext('2d');
        if (!ctx || !detection || !detection.detection || !detection.detection.box) {
            return;
        }

        resizeCanvasToVideo();
        clearCanvas();

        var box = detection.detection.box;
        ctx.strokeStyle = 'rgba(46, 204, 113, 0.95)';
        ctx.lineWidth = 3;
        ctx.strokeRect(box.x, box.y, box.width, box.height);
    }

    function waitFor(ms) {
        return new Promise(function (resolve) {
            setTimeout(resolve, ms);
        });
    }

    async function loadFaceApiLibrary() {
        if (window.faceapi) {
            return window.faceapi;
        }

        if (faceAuthRuntime.scriptPromise) {
            return faceAuthRuntime.scriptPromise;
        }

        faceAuthRuntime.scriptPromise = new Promise(function (resolve, reject) {
            var existing = document.querySelector('script[data-face-api="true"]');
            if (existing) {
                existing.addEventListener('load', function () {
                    if (window.faceapi) {
                        resolve(window.faceapi);
                        return;
                    }

                    reject(new Error('Bibliotheque face-api indisponible.'));
                }, { once: true });

                existing.addEventListener('error', function () {
                    reject(new Error('Impossible de charger face-api.js.'));
                }, { once: true });

                return;
            }

            var script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js';
            script.async = true;
            script.defer = true;
            script.setAttribute('data-face-api', 'true');

            script.onload = function () {
                if (window.faceapi) {
                    resolve(window.faceapi);
                    return;
                }

                reject(new Error('Bibliotheque face-api indisponible.'));
            };

            script.onerror = function () {
                reject(new Error('Impossible de charger face-api.js.'));
            };

            document.head.appendChild(script);
        }).catch(function (error) {
            faceAuthRuntime.scriptPromise = null;
            throw error;
        });

        return faceAuthRuntime.scriptPromise;
    }

    async function loadFaceApiModels(faceapiLib) {
        if (faceAuthRuntime.modelsPromise) {
            return faceAuthRuntime.modelsPromise;
        }

        var modelBase = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
        faceAuthRuntime.modelsPromise = Promise.all([
            faceapiLib.nets.tinyFaceDetector.loadFromUri(modelBase),
            faceapiLib.nets.faceLandmark68Net.loadFromUri(modelBase),
            faceapiLib.nets.faceRecognitionNet.loadFromUri(modelBase)
        ]).catch(function (error) {
            faceAuthRuntime.modelsPromise = null;
            throw error;
        });

        return faceAuthRuntime.modelsPromise;
    }

    async function ensureFaceApiReady() {
        if (state.faceapi) {
            return state.faceapi;
        }

        var faceapiLib = await loadFaceApiLibrary();
        await loadFaceApiModels(faceapiLib);
        state.faceapi = faceapiLib;

        return state.faceapi;
    }

    function stopCamera() {
        if (state.stream) {
            state.stream.getTracks().forEach(function (track) {
                track.stop();
            });
        }

        state.stream = null;
        video.srcObject = null;
        clearCanvas();
        setStartButtonState(false);
        syncButtons();
    }

    async function startCamera() {
        if (state.stream) {
            stopCamera();
            setStatus('Camera desactivee.', 'info');
            return;
        }

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            setStatus('Votre navigateur ne supporte pas la camera web.', 'error');
            return;
        }

        state.isBusy = true;
        syncButtons();
        setStatus('Chargement du module facial...', 'info');

        try {
            await ensureFaceApiReady();

            state.stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                },
                audio: false
            });

            video.srcObject = state.stream;
            await video.play();
            resizeCanvasToVideo();
            setStartButtonState(true);
            setStatus('Camera active. Placez votre visage dans le cadre.', 'success');
        } catch (error) {
            console.error('Face auth camera error:', error);
            stopCamera();
            setStatus('Impossible d\'activer la camera ou les modeles faciaux.', 'error');
        } finally {
            state.isBusy = false;
            syncButtons();
        }
    }

    async function captureDescriptor() {
        if (!state.stream || !state.faceapi) {
            return null;
        }

        clearCanvas();

        for (var attempt = 0; attempt < 4; attempt++) {
            var detection = await state.faceapi
                .detectSingleFace(
                    video,
                    new state.faceapi.TinyFaceDetectorOptions({
                        inputSize: 224,
                        scoreThreshold: 0.45
                    })
                )
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (detection && detection.descriptor) {
                drawDetection(detection);
                return Array.prototype.slice.call(detection.descriptor);
            }

            await waitFor(140);
        }

        return null;
    }

    async function postJson(url, payload) {
        var response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload || {})
        });

        var data = null;
        try {
            data = await response.json();
        } catch (error) {
            data = null;
        }

        return {
            ok: response.ok,
            data: data
        };
    }

    async function submitFaceAction() {
        if (state.isBusy) {
            return;
        }

        if (!state.stream) {
            setStatus('Activez d\'abord la camera.', 'error');
            return;
        }

        if (state.mode === 'register') {
            if (!state.descriptorInput) {
                setStatus('Champ de sauvegarde manquant.', 'error');
                return;
            }

            state.isBusy = true;
            syncButtons();
            setStatus('Analyse faciale en cours...', 'info');

            try {
                var registerDescriptor = await captureDescriptor();
                if (!registerDescriptor) {
                    setStatus('Aucun visage detecte. Regardez la camera et reessayez.', 'error');
                    return;
                }

                state.descriptorInput.value = JSON.stringify(registerDescriptor);
                state.hasSavedDescriptor = true;
                updateBadge(true);
                setStatus('Empreinte faciale prete. Vous pouvez terminer l\'inscription.', 'success');
            } catch (error) {
                console.error('Face submit error:', error);
                setStatus('Erreur de communication avec le module facial.', 'error');
            } finally {
                state.isBusy = false;
                syncButtons();
            }

            return;
        }

        var payload = {};
        if (state.mode === 'login') {
            var emailInput = document.getElementById('loginEmail') || document.querySelector('input[name="email"]');
            var emailValue = emailInput ? (emailInput.value || '').trim() : '';
            var isEmailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailValue);

            if (!isEmailValid) {
                setStatus('Saisissez un e-mail valide avant la verification faciale.', 'error');
                if (emailInput) {
                    emailInput.focus();
                }
                return;
            }

            payload.email = emailValue;
        }

        state.isBusy = true;
        syncButtons();
        setStatus('Analyse faciale en cours...', 'info');

        try {
            var descriptor = await captureDescriptor();
            if (!descriptor) {
                setStatus('Aucun visage detecte. Regardez la camera et reessayez.', 'error');
                return;
            }

            payload.descriptor = descriptor;

            var result = await postJson(state.endpoint, payload);
            var message = result.data && result.data.message
                ? result.data.message
                : 'Erreur lors de la verification faciale.';

            if (!result.ok || !result.data || result.data.success !== true) {
                setStatus(message, 'error');
                return;
            }

            setStatus(message, 'success');

            if (state.mode === 'login' && result.data.redirect) {
                window.location.href = result.data.redirect;
                return;
            }

            if (state.mode === 'enroll') {
                state.hasSavedDescriptor = true;
                updateBadge(true);
            }
        } catch (error) {
            console.error('Face submit error:', error);
            setStatus('Erreur de communication avec le serveur.', 'error');
        } finally {
            state.isBusy = false;
            syncButtons();
        }
    }

    async function clearFaceAction() {
        if (state.mode === 'register') {
            if (!clearButton || !state.descriptorInput || state.isBusy) {
                return;
            }

            state.isBusy = true;
            syncButtons();
            state.descriptorInput.value = '';
            state.hasSavedDescriptor = false;
            updateBadge(false);
            setStatus('Empreinte faciale retiree.', 'success');
            state.isBusy = false;
            syncButtons();
            return;
        }

        if (!clearButton || !state.clearEndpoint || state.isBusy) {
            return;
        }

        state.isBusy = true;
        syncButtons();
        setStatus('Suppression de l\'empreinte en cours...', 'info');

        try {
            var result = await postJson(state.clearEndpoint, {});
            var message = result.data && result.data.message
                ? result.data.message
                : 'Erreur lors de la suppression de l\'empreinte.';

            if (!result.ok || !result.data || result.data.success !== true) {
                setStatus(message, 'error');
                return;
            }

            state.hasSavedDescriptor = false;
            updateBadge(false);
            setStatus(message, 'success');
        } catch (error) {
            console.error('Face clear error:', error);
            setStatus('Erreur de communication avec le serveur.', 'error');
        } finally {
            state.isBusy = false;
            syncButtons();
        }
    }

    startButton.addEventListener('click', function () {
        startCamera();
    });

    submitButton.addEventListener('click', function () {
        submitFaceAction();
    });

    if (clearButton && clearEndpoint) {
        clearButton.addEventListener('click', function () {
            clearFaceAction();
        });
    }

    window.addEventListener('beforeunload', function () {
        stopCamera();
    });

    setStartButtonState(false);
    syncButtons();
}

function initHomeTopicButtons() {
    var topicButtons = document.querySelectorAll('.home-topic-btn');
    if (!topicButtons.length) {
        return;
    }

    var titleEl = document.getElementById('homeTopicTitle');
    var textEl = document.getElementById('homeTopicText');
    var descriptionCard = document.getElementById('homeTopicDescription');

    if (!titleEl || !textEl || !descriptionCard) {
        return;
    }

    topicButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var topicTitle = button.getAttribute('data-topic-title') || 'Sujet';
            var topicDescription = button.getAttribute('data-topic-description') || '';

            topicButtons.forEach(function (item) {
                item.classList.remove('active');
            });

            button.classList.add('active');
            titleEl.textContent = topicTitle;
            textEl.textContent = topicDescription;

            descriptionCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    });
}

function initHomeWeatherCard() {
    var card = document.querySelector('[data-weather-card="true"]');
    if (!card) {
        return;
    }

    var endpoint = card.getAttribute('data-weather-endpoint') || '';
    if (!endpoint) {
        return;
    }

    var badgeEl = document.getElementById('homeWeatherBadge');
    var iconEl = document.getElementById('homeWeatherIcon');
    var locationEl = document.getElementById('homeWeatherLocation');
    var tempEl = document.getElementById('homeWeatherTemp');
    var conditionEl = document.getElementById('homeWeatherCondition');
    var feelsLikeEl = document.getElementById('homeWeatherFeelsLike');
    var humidityEl = document.getElementById('homeWeatherHumidity');
    var windEl = document.getElementById('homeWeatherWind');
    var updatedEl = document.getElementById('homeWeatherUpdated');
    var titleEl = document.getElementById('homeWeatherTitle');
    var adviceEl = document.getElementById('homeWeatherAdvice');

    function setState(status, title, advice) {
        card.classList.remove('is-loading', 'is-good', 'is-caution', 'is-bad', 'is-error');
        card.classList.add('is-' + status);

        if (badgeEl) {
            if (status === 'good') {
                badgeEl.textContent = 'Sport conseille';
            } else if (status === 'caution') {
                badgeEl.textContent = 'Avec prudence';
            } else if (status === 'bad') {
                badgeEl.textContent = 'A eviter';
            } else if (status === 'error') {
                badgeEl.textContent = 'Indisponible';
            } else {
                badgeEl.textContent = 'Chargement...';
            }
        }

        if (titleEl && title) {
            titleEl.textContent = title;
        }

        if (adviceEl && advice) {
            adviceEl.textContent = advice;
        }
    }

    function updateWeatherCard(weather, usedFallback) {
        if (locationEl) {
            locationEl.textContent = usedFallback
                ? weather.location + ' (position par defaut)'
                : weather.location;
        }

        if (tempEl) {
            tempEl.textContent = weather.temperature_c.toFixed(1) + 'Â°C';
        }

        if (conditionEl) {
            conditionEl.textContent = weather.condition;
        }

        if (feelsLikeEl) {
            feelsLikeEl.textContent = weather.feels_like_c.toFixed(1) + 'Â°C';
        }

        if (humidityEl) {
            humidityEl.textContent = weather.humidity + '%';
        }

        if (windEl) {
            windEl.textContent = weather.wind_kmh.toFixed(1) + ' km/h';
        }

        if (updatedEl) {
            updatedEl.textContent = weather.updated_at;
        }

        if (iconEl) {
            iconEl.innerHTML = '<i class="' + weather.icon_class + '"></i>';
        }

        setState(weather.sport_status, weather.sport_title, weather.sport_advice);
    }

    function showError(message) {
        if (locationEl) {
            locationEl.textContent = 'Meteo indisponible';
        }

        if (conditionEl) {
            conditionEl.textContent = message;
        }

        setState('error', 'Impossible de charger la meteo', message);
    }

    async function fetchWeather(lat, lon, usedFallback) {
        var url = endpoint;
        if (typeof lat === 'number' && typeof lon === 'number') {
            url += '&lat=' + encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lon);
        }

        try {
            var response = await fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            var data = null;
            try {
                data = await response.json();
            } catch (parseError) {
                data = null;
            }

            if (!response.ok || !data || data.success !== true || !data.weather) {
                showError(data && data.message ? data.message : 'La reponse meteo est indisponible pour le moment.');
                return;
            }

            updateWeatherCard(data.weather, usedFallback);
        } catch (error) {
            console.error('Weather fetch failed:', error);
            showError('Impossible de contacter le service meteo pour le moment.');
        }
    }

    if (!navigator.geolocation || typeof navigator.geolocation.getCurrentPosition !== 'function') {
        fetchWeather(null, null, true);
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function (position) {
            fetchWeather(position.coords.latitude, position.coords.longitude, false);
        },
        function () {
            fetchWeather(null, null, true);
        },
        {
            enableHighAccuracy: false,
            timeout: 7000,
            maximumAge: 900000
        }
    );
}

function initVoiceControl() {
    var dockEl = document.getElementById('voiceControlDock');
    var panelEl = document.getElementById('voiceControlPanel');
    var toggleButton = document.getElementById('voiceToggleBtn');
    var statusLabel = document.getElementById('voiceStatusLabel');
    var statusBadge = document.getElementById('voiceStatusBadge');
    var transcriptEl = document.getElementById('voiceTranscript');
    var lastActionEl = document.getElementById('voiceLastAction');
    var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    if (!dockEl || !panelEl || !toggleButton || !statusLabel || !statusBadge || !transcriptEl || !lastActionEl) {
        return;
    }

    if (!SpeechRecognition) {
        statusLabel.textContent = 'Non pris en charge';
        statusBadge.textContent = 'Indisponible';
        statusBadge.classList.add('is-error');
        transcriptEl.textContent = 'La reconnaissance vocale n est pas disponible dans ce navigateur.';
        toggleButton.disabled = true;
        return;
    }

    var recognition = new SpeechRecognition();
    var isListening = false;
    var shouldRestart = false;
    var manualStop = false;
    var restartTimer = null;
    var scrollTicking = false;
    var scrollIdleTimer = null;
    var lastScrollY = window.pageYOffset || window.scrollY || 0;
    var storageKey = 'smartNutritionTheme';
    var routeMap = [
        { action: 'home', aliases: ['accueil', 'home', 'page accueil'] },
        { action: 'profile', aliases: ['profil', 'profile', 'mon profil'] },
        { action: 'login', aliases: ['connexion', 'se connecter', 'login'] },
        { action: 'register', aliases: ['inscription', 'creer compte', 's inscrire'] },
        { action: 'forgot', aliases: ['mot de passe oublie', 'oubli mot de passe'] },
        { action: 'admin-dashboard', aliases: ['dashboard', 'tableau de bord', 'admin dashboard'] },
        { action: 'users-list', aliases: ['utilisateurs', 'liste utilisateurs', 'gestion utilisateurs'] },
        { action: 'auth-management', aliases: ['authentification', 'module authentification'] },
        { action: 'recipes-management', aliases: ['recettes', 'recette alimentation'] },
        { action: 'foods-management', aliases: ['ecommerce', 'boutique', 'produits'] },
        { action: 'recommendations-management', aliases: ['communaute', 'community'] },
        { action: 'tracking-management', aliases: ['activite sportif', 'activite sportive', 'sport'] },
        { action: 'planner-management', aliases: ['planning', 'agenda'] },
        { action: 'logout', aliases: ['deconnexion', 'se deconnecter', 'logout'] }
    ];

    recognition.lang = 'fr-FR';
    recognition.continuous = true;
    recognition.interimResults = true;
    recognition.maxAlternatives = 1;

    function updateVoiceScrollUI() {
        var scrollTop = window.pageYOffset || window.scrollY || document.documentElement.scrollTop || 0;
        var docHeight = Math.max(
            document.body.scrollHeight,
            document.documentElement.scrollHeight,
            document.body.offsetHeight,
            document.documentElement.offsetHeight
        );
        var viewportHeight = window.innerHeight || document.documentElement.clientHeight || 0;
        var maxScroll = Math.max(docHeight - viewportHeight, 0);
        var progress = maxScroll > 0 ? scrollTop / maxScroll : 0;
        var progressPercent = Math.round(progress * 100);
        var direction = 'idle';

        if (scrollTop > lastScrollY + 4) {
            direction = 'down';
        } else if (scrollTop < lastScrollY - 4) {
            direction = 'up';
        }

        lastScrollY = scrollTop;

        dockEl.classList.toggle('is-scroll-down', direction === 'down');
        dockEl.classList.toggle('is-scroll-up', direction === 'up');
        dockEl.classList.toggle('is-near-top', progress <= 0.04);
        dockEl.classList.toggle('is-deep-page', progress >= 0.55);
        dockEl.classList.add('is-scroll-active');

        if (scrollIdleTimer) {
            clearTimeout(scrollIdleTimer);
        }

        scrollIdleTimer = setTimeout(function () {
            dockEl.classList.remove('is-scroll-active', 'is-scroll-down', 'is-scroll-up');
            if ((window.pageYOffset || window.scrollY || 0) <= 24) {
                dockEl.classList.add('is-near-top');
            }
        }, 180);

        if (!isListening) {
            if (direction === 'down') {
                transcriptEl.textContent = 'Interface vocale dynamique: vous descendez dans la page.';
            } else if (direction === 'up') {
                transcriptEl.textContent = 'Interface vocale dynamique: vous remontez dans la page.';
            } else if (progressPercent <= 5) {
                transcriptEl.textContent = 'Cliquez sur le micro puis dites une commande.';
            }
        }
    }

    function onVoiceScroll() {
        if (scrollTicking) {
            return;
        }

        scrollTicking = true;
        window.requestAnimationFrame(function () {
            updateVoiceScrollUI();
            scrollTicking = false;
        });
    }

    function normalizeVoiceText(value) {
        var text = (value || '').toLowerCase();
        if (typeof text.normalize === 'function') {
            text = text.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        }

        text = text
            .replace(/[\.,;:!?\/\\()\[\]"]/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();

        return text;
    }

    function setVoiceState(label, badge, transcript, action, isError) {
        statusLabel.textContent = label;
        statusBadge.textContent = badge;
        statusBadge.classList.toggle('is-listening', isListening);
        statusBadge.classList.toggle('is-error', !!isError);
        toggleButton.classList.toggle('is-listening', isListening);
        toggleButton.setAttribute('aria-pressed', isListening ? 'true' : 'false');

        if (transcript) {
            transcriptEl.textContent = transcript;
        }

        if (action) {
            lastActionEl.textContent = action;
        }
    }

    function buildActionUrl(action) {
        return '/projet-web-25-26/index.php?action=' + encodeURIComponent(action);
    }

    function setTheme(theme) {
        if (typeof applyTheme === 'function') {
            applyTheme(theme, storageKey);
            setVoiceState('Actif', 'Ok', 'Commande executee.', 'Theme ' + (theme === 'dark' ? 'sombre active.' : 'clair active.'), false);
            return true;
        }

        return false;
    }

    function isVisibleElement(element) {
        if (!element) {
            return false;
        }

        if (element.disabled) {
            return false;
        }

        if (element.offsetParent !== null) {
            return true;
        }

        return window.getComputedStyle(element).position === 'fixed';
    }

    function getElementVoiceLabel(element) {
        var label = '';

        if (element.getAttribute('aria-label')) {
            label = element.getAttribute('aria-label');
        } else if (element.value && (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA')) {
            label = element.value;
        } else {
            label = element.textContent || '';
        }

        if (!label && element.placeholder) {
            label = element.placeholder;
        }

        return normalizeVoiceText(label);
    }

    function flashElement(element) {
        if (!element) {
            return;
        }

        element.classList.add('gesture-hover');
        setTimeout(function () {
            element.classList.remove('gesture-hover');
        }, 900);
    }

    function findBestInteractiveElement(query) {
        var normalizedQuery = normalizeVoiceText(query);
        if (!normalizedQuery) {
            return null;
        }

        var selectors = 'a, button, .btn, [role="button"], input[type="submit"], input[type="button"], input[type="reset"]';
        var elements = Array.prototype.slice.call(document.querySelectorAll(selectors)).filter(isVisibleElement);
        var exactMatch = null;
        var partialMatch = null;

        elements.some(function (element) {
            var label = getElementVoiceLabel(element);
            if (!label) {
                return false;
            }

            if (label === normalizedQuery) {
                exactMatch = element;
                return true;
            }

            if (!partialMatch && (label.indexOf(normalizedQuery) !== -1 || normalizedQuery.indexOf(label) !== -1)) {
                partialMatch = element;
            }

            return false;
        });

        return exactMatch || partialMatch;
    }

    function clickInteractiveElement(query) {
        var target = findBestInteractiveElement(query);
        if (!target) {
            return false;
        }

        flashElement(target);
        target.click();
        setVoiceState('Actif', 'Ok', 'Commande executee.', 'Clic sur: ' + query + '.', false);
        return true;
    }

    function findField(query) {
        var normalizedQuery = normalizeVoiceText(query);
        if (!normalizedQuery) {
            return null;
        }

        var labels = Array.prototype.slice.call(document.querySelectorAll('label'));
        for (var i = 0; i < labels.length; i++) {
            var labelText = normalizeVoiceText(labels[i].textContent || '');
            if (!labelText || (labelText.indexOf(normalizedQuery) === -1 && normalizedQuery.indexOf(labelText) === -1)) {
                continue;
            }

            var fieldId = labels[i].getAttribute('for');
            if (fieldId) {
                var targetField = document.getElementById(fieldId);
                if (targetField && isVisibleElement(targetField)) {
                    return targetField;
                }
            }

            var nestedField = labels[i].querySelector('input, textarea, select');
            if (nestedField && isVisibleElement(nestedField)) {
                return nestedField;
            }
        }

        var fields = Array.prototype.slice.call(document.querySelectorAll('input, textarea, select')).filter(isVisibleElement);
        var exactMatch = null;
        var partialMatch = null;

        fields.some(function (field) {
            var descriptors = [
                field.name || '',
                field.id || '',
                field.placeholder || '',
                field.getAttribute('aria-label') || ''
            ].join(' ');
            var normalizedDescriptor = normalizeVoiceText(descriptors);

            if (!normalizedDescriptor) {
                return false;
            }

            if (normalizedDescriptor === normalizedQuery) {
                exactMatch = field;
                return true;
            }

            if (!partialMatch && (normalizedDescriptor.indexOf(normalizedQuery) !== -1 || normalizedQuery.indexOf(normalizedDescriptor) !== -1)) {
                partialMatch = field;
            }

            return false;
        });

        return exactMatch || partialMatch;
    }

    function focusField(query) {
        var field = findField(query);
        if (!field) {
            return false;
        }

        field.focus();
        flashElement(field);
        setVoiceState('Actif', 'Ok', 'Commande executee.', 'Champ active: ' + query + '.', false);
        return true;
    }

    function writeInFocusedField(text) {
        var field = document.activeElement;
        if (!field || ['INPUT', 'TEXTAREA'].indexOf(field.tagName) === -1) {
            setVoiceState('Actif', 'Alerte', 'Aucun champ texte actif.', 'Dites par exemple: selectionne email.', true);
            return true;
        }

        if ((field.type || '').toLowerCase() === 'password') {
            setVoiceState('Actif', 'Alerte', 'La saisie vocale du mot de passe est desactivee.', 'Utilisez le clavier pour les champs sensibles.', true);
            return true;
        }

        field.value = text;
        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.dispatchEvent(new Event('change', { bubbles: true }));
        setVoiceState('Actif', 'Ok', 'Texte saisi.', 'Contenu ecrit dans le champ actif.', false);
        return true;
    }

    function clearFocusedField() {
        var field = document.activeElement;
        if (!field || ['INPUT', 'TEXTAREA'].indexOf(field.tagName) === -1) {
            return false;
        }

        if ((field.type || '').toLowerCase() === 'password') {
            return false;
        }

        field.value = '';
        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.dispatchEvent(new Event('change', { bubbles: true }));
        setVoiceState('Actif', 'Ok', 'Champ efface.', 'Le champ actif a ete vide.', false);
        return true;
    }

    function submitClosestForm() {
        var form = document.activeElement ? document.activeElement.closest('form') : null;
        if (!form) {
            var visibleForms = Array.prototype.slice.call(document.querySelectorAll('form')).filter(isVisibleElement);
            form = visibleForms[0] || null;
        }

        if (!form) {
            return false;
        }

        var submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
        if (submitButton && !submitButton.disabled) {
            flashElement(submitButton);
            submitButton.click();
        } else {
            form.requestSubmit ? form.requestSubmit() : form.submit();
        }

        setVoiceState('Actif', 'Ok', 'Formulaire envoye.', 'Validation du formulaire lancee.', false);
        return true;
    }

    function fillSearch(text) {
        var searchField = document.querySelector('input[type="search"], input[id*="search"], input[name*="search"], input[placeholder*="Recherche"], input[placeholder*="recherche"]');
        if (!searchField || !isVisibleElement(searchField)) {
            return false;
        }

        searchField.focus();
        searchField.value = text;
        searchField.dispatchEvent(new Event('input', { bubbles: true }));
        searchField.dispatchEvent(new Event('change', { bubbles: true }));
        setVoiceState('Actif', 'Ok', 'Recherche mise a jour.', 'Recherche de: ' + text + '.', false);
        return true;
    }

    function navigateToRoute(target) {
        var normalizedTarget = normalizeVoiceText(target);
        var chosenRoute = null;

        routeMap.some(function (route) {
            return route.aliases.some(function (alias) {
                var normalizedAlias = normalizeVoiceText(alias);
                if (normalizedAlias === normalizedTarget || normalizedAlias.indexOf(normalizedTarget) !== -1 || normalizedTarget.indexOf(normalizedAlias) !== -1) {
                    chosenRoute = route;
                    return true;
                }

                return false;
            });
        });

        if (!chosenRoute) {
            return false;
        }

        setVoiceState('Actif', 'Ok', 'Navigation en cours.', 'Ouverture de: ' + target + '.', false);
        window.location.href = buildActionUrl(chosenRoute.action);
        return true;
    }

    function openOrClickTarget(target) {
        if (navigateToRoute(target)) {
            return true;
        }

        if (clickInteractiveElement(target)) {
            return true;
        }

        if (focusField(target)) {
            return true;
        }

        return false;
    }

    function showVoiceHelp() {
        setVoiceState(
            'Actif',
            'Aide',
            'Commandes: ouvre profil, clique deconnexion, descendre, monter, mode sombre, selectionne email, ecrire test, envoyer formulaire.',
            'Aide vocale affichee.',
            false
        );
        return true;
    }

    function handleVoiceCommand(command) {
        var normalizedCommand = normalizeVoiceText(command);
        var target = '';

        if (!normalizedCommand) {
            return;
        }

        if (normalizedCommand === 'aide' || normalizedCommand === 'aide vocale' || normalizedCommand === 'commandes vocales') {
            showVoiceHelp();
            return;
        }

        if (normalizedCommand === 'arreter' || normalizedCommand === 'arreter ecoute' || normalizedCommand === 'arreter l ecoute' || normalizedCommand === 'stop ecoute') {
            stopListening(true);
            setVoiceState('Inactif', 'Stop', 'Ecoute arretee.', 'Le controle vocal a ete coupe.', false);
            return;
        }

        if (normalizedCommand === 'descendre' || normalizedCommand === 'scroll bas' || normalizedCommand === 'aller en bas') {
            window.scrollBy({ top: window.innerHeight * 0.75, behavior: 'smooth' });
            setVoiceState('Actif', 'Ok', 'Defilement vers le bas.', 'Page descendue.', false);
            return;
        }

        if (normalizedCommand === 'monter' || normalizedCommand === 'scroll haut' || normalizedCommand === 'aller en haut') {
            window.scrollBy({ top: -window.innerHeight * 0.75, behavior: 'smooth' });
            setVoiceState('Actif', 'Ok', 'Defilement vers le haut.', 'Page remontee.', false);
            return;
        }

        if (normalizedCommand === 'retour' || normalizedCommand === 'page precedente') {
            setVoiceState('Actif', 'Ok', 'Retour a la page precedente.', 'Historique navigateur utilise.', false);
            window.history.back();
            return;
        }

        if (normalizedCommand === 'actualiser' || normalizedCommand === 'rafraichir') {
            setVoiceState('Actif', 'Ok', 'Actualisation de la page.', 'Rechargement en cours.', false);
            window.location.reload();
            return;
        }

        if (normalizedCommand.indexOf('mode sombre') !== -1) {
            if (setTheme('dark')) {
                return;
            }
        }

        if (normalizedCommand.indexOf('mode clair') !== -1 || normalizedCommand.indexOf('mode claire') !== -1) {
            if (setTheme('light')) {
                return;
            }
        }

        if (normalizedCommand === 'envoyer formulaire' || normalizedCommand === 'valider formulaire' || normalizedCommand === 'soumettre formulaire') {
            if (submitClosestForm()) {
                return;
            }
        }

        if (normalizedCommand === 'effacer champ' || normalizedCommand === 'vider champ') {
            if (clearFocusedField()) {
                return;
            }
        }

        if (normalizedCommand.indexOf('chercher ') === 0) {
            target = normalizedCommand.substring('chercher '.length).trim();
            if (fillSearch(target)) {
                return;
            }
        }

        if (normalizedCommand.indexOf('rechercher ') === 0) {
            target = normalizedCommand.substring('rechercher '.length).trim();
            if (fillSearch(target)) {
                return;
            }
        }

        if (normalizedCommand.indexOf('selectionne ') === 0) {
            target = normalizedCommand.substring('selectionne '.length).trim();
            if (focusField(target)) {
                return;
            }
        }

        if (normalizedCommand.indexOf('selectionner ') === 0) {
            target = normalizedCommand.substring('selectionner '.length).trim();
            if (focusField(target)) {
                return;
            }
        }

        if (normalizedCommand.indexOf('champ ') === 0) {
            target = normalizedCommand.substring('champ '.length).trim();
            if (focusField(target)) {
                return;
            }
        }

        if (normalizedCommand.indexOf('ecrire ') === 0) {
            target = command.substring(command.toLowerCase().indexOf('ecrire ') + 'ecrire '.length).trim();
            if (writeInFocusedField(target)) {
                return;
            }
        }

        if (normalizedCommand.indexOf('saisir ') === 0) {
            target = command.substring(command.toLowerCase().indexOf('saisir ') + 'saisir '.length).trim();
            if (writeInFocusedField(target)) {
                return;
            }
        }

        if (normalizedCommand.indexOf('cliquer ') === 0) {
            target = normalizedCommand.substring('cliquer '.length).trim();
            if (clickInteractiveElement(target)) {
                return;
            }
        }

        if (normalizedCommand.indexOf('ouvre ') === 0) {
            target = normalizedCommand.substring('ouvre '.length).trim();
            if (openOrClickTarget(target)) {
                return;
            }
        }

        if (normalizedCommand.indexOf('ouvrir ') === 0) {
            target = normalizedCommand.substring('ouvrir '.length).trim();
            if (openOrClickTarget(target)) {
                return;
            }
        }

        if (normalizedCommand.indexOf('aller a ') === 0) {
            target = normalizedCommand.substring('aller a '.length).trim();
            if (openOrClickTarget(target)) {
                return;
            }
        }

        if (openOrClickTarget(normalizedCommand)) {
            return;
        }

        setVoiceState('Actif', 'Alerte', 'Commande non reconnue.', 'Essayez: ouvre accueil, cliquer connexion, mode sombre, descendre.', true);
    }

    function startListening() {
        if (isListening) {
            return;
        }

        manualStop = false;
        shouldRestart = true;
        if (restartTimer) {
            clearTimeout(restartTimer);
            restartTimer = null;
        }

        try {
            recognition.start();
        } catch (error) {
            setVoiceState('Erreur', 'Erreur', 'Impossible de demarrer le micro.', 'Le navigateur a refuse le lancement de l ecoute.', true);
        }
    }

    function stopListening(isManual) {
        shouldRestart = false;
        manualStop = !!isManual;
        if (restartTimer) {
            clearTimeout(restartTimer);
            restartTimer = null;
        }

        try {
            recognition.stop();
        } catch (error) {
        }
    }

    recognition.onstart = function () {
        isListening = true;
        setVoiceState('Ecoute active', 'Ecoute', 'Je vous ecoute. Dites une commande.', 'Exemples: ouvre profil, cliquer deconnexion, mode sombre.', false);
    };

    recognition.onend = function () {
        isListening = false;
        if (shouldRestart && !manualStop) {
            restartTimer = setTimeout(function () {
                startListening();
            }, 300);
            return;
        }

        setVoiceState('Inactif', 'Pret', 'Cliquez sur le micro puis dites une commande.', 'Le controle vocal est en veille.', false);
        manualStop = false;
    };

    recognition.onerror = function (event) {
        isListening = false;

        if (event.error === 'not-allowed' || event.error === 'service-not-allowed') {
            shouldRestart = false;
            setVoiceState('Refuse', 'Bloque', 'L acces au microphone a ete refuse.', 'Autorisez le microphone dans le navigateur pour utiliser la voix.', true);
            return;
        }

        if (event.error === 'no-speech') {
            setVoiceState('Ecoute active', 'Ecoute', 'Aucune parole detectee. Reessayez.', 'Le micro reste actif.', false);
            return;
        }

        if (event.error === 'audio-capture') {
            shouldRestart = false;
            setVoiceState('Erreur', 'Micro', 'Aucun microphone detecte.', 'Branchez ou activez un micro.', true);
            return;
        }

        setVoiceState('Erreur', 'Erreur', 'Erreur de reconnaissance vocale.', 'Code: ' + event.error + '.', true);
    };

    recognition.onresult = function (event) {
        var interimTranscript = '';
        var finalTranscript = '';

        for (var i = event.resultIndex; i < event.results.length; i++) {
            var transcript = event.results[i][0].transcript || '';
            if (event.results[i].isFinal) {
                finalTranscript += transcript + ' ';
            } else {
                interimTranscript += transcript + ' ';
            }
        }

        if (interimTranscript.trim() !== '') {
            transcriptEl.textContent = 'J entends: ' + interimTranscript.trim();
        }

        if (finalTranscript.trim() !== '') {
            var cleanCommand = finalTranscript.trim();
            transcriptEl.textContent = 'Commande: ' + cleanCommand;
            handleVoiceCommand(cleanCommand);
        }
    };

    toggleButton.addEventListener('click', function () {
        if (isListening) {
            stopListening(true);
            return;
        }

        startListening();
    });

    window.addEventListener('scroll', onVoiceScroll, { passive: true });
    window.addEventListener('resize', onVoiceScroll);
    dockEl.classList.add('is-near-top');
    updateVoiceScrollUI();
}

function initNotifications() {
    var centers = document.querySelectorAll('.notification-center');
    if (!centers.length) {
        return;
    }

    centers.forEach(function (center) {
        if (center.dataset.notificationReady === 'true') {
            return;
        }

        center.dataset.notificationReady = 'true';

        var endpoint = center.getAttribute('data-notification-endpoint') || '';
        var toggle = center.querySelector('#notificationToggle, .notification-toggle');
        var dropdown = center.querySelector('#notificationDropdown, .notification-dropdown');
        var badge = center.querySelector('#notificationBadge, .notification-badge');
        var list = center.querySelector('#notificationList, .notification-list');
        var markAll = center.querySelector('#notificationMarkAll, .notification-mark-all');
        var showOlder = center.querySelector('#notificationShowOlder, .notification-show-older');
        var initialVisibleCount = 5;
        var olderVisible = false;
        var latestItems = [];

        if (!endpoint || !toggle || !dropdown || !badge || !list) {
            return;
        }

        function escapeHtml(value) {
            var div = document.createElement('div');
            div.textContent = value || '';
            return div.innerHTML;
        }

        function renderNotifications(items) {
            latestItems = Array.isArray(items) ? items : [];
            var visibleItems = olderVisible ? latestItems : latestItems.slice(0, initialVisibleCount);

            if (!visibleItems.length) {
                list.innerHTML = '<p class="notification-empty">Aucune notification pour le moment.</p>';
                if (showOlder) showOlder.hidden = true;
                return;
            }

            list.innerHTML = visibleItems.map(function (item) {
            var unreadClass = Number(item.is_read) ? '' : ' is-unread';
            var time = item.created_at ? new Date(String(item.created_at).replace(' ', 'T')).toLocaleString() : '';
            return '' +
                '<button type="button" class="notification-item' + unreadClass + '" data-id="' + escapeHtml(item.id) + '" data-link="' + escapeHtml(item.link_url || '') + '">' +
                    '<span class="notification-item-title">' + escapeHtml(item.title) + '</span>' +
                    '<span class="notification-item-message">' + escapeHtml(item.message) + '</span>' +
                    '<span class="notification-item-time">' + escapeHtml(time) + '</span>' +
                '</button>';
            }).join('');

            if (showOlder) {
                showOlder.hidden = latestItems.length <= initialVisibleCount;
                showOlder.textContent = olderVisible ? 'Anciennes notifications affichees' : 'Voir les anciennes notifications';
                showOlder.disabled = olderVisible;
            }
        }

        function updateBadge(count) {
            var unreadCount = Number(count || 0);
            badge.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
            badge.hidden = unreadCount === 0;
        }

        function positionDropdown() {
            var dropdownWidth = Math.min(360, window.innerWidth - 28);
            dropdown.style.width = dropdownWidth + 'px';
            dropdown.style.top = '';
            dropdown.style.right = '';
            dropdown.style.left = '';
            dropdown.style.transform = '';
        }

        function fetchNotifications() {
            fetch(endpoint + '?action=list', {
                cache: 'no-store',
                credentials: 'same-origin'
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (!data || data.success !== true) return;
                    updateBadge(data.unreadCount);
                    renderNotifications(data.notifications || []);
                })
                .catch(function () {});
        }

        function markRead(id, callback) {
            fetch(endpoint + '?action=mark_read', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + encodeURIComponent(id)
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data && data.success) {
                        updateBadge(data.unreadCount);
                        fetchNotifications();
                    }
                    if (callback) callback();
                })
                .catch(function () {
                    if (callback) callback();
                });
        }

        toggle.addEventListener('click', function () {
            var isOpen = !dropdown.hidden;
            dropdown.hidden = isOpen;
            toggle.setAttribute('aria-expanded', String(!isOpen));
            if (!isOpen) {
                positionDropdown();
                fetchNotifications();
            }
        });

        list.addEventListener('click', function (event) {
            var item = event.target.closest('.notification-item');
            if (!item) return;

            markRead(item.dataset.id, function () {
                if (item.dataset.link) {
                    window.location.href = item.dataset.link;
                }
            });
        });

        if (markAll) {
            markAll.addEventListener('click', function () {
                fetch(endpoint + '?action=mark_all_read', {
                    method: 'POST',
                    credentials: 'same-origin'
                })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (data && data.success) {
                            updateBadge(data.unreadCount);
                            fetchNotifications();
                        }
                    })
                    .catch(function () {});
            });
        }

        if (showOlder) {
            showOlder.addEventListener('click', function () {
                olderVisible = true;
                dropdown.classList.add('is-expanded');
                renderNotifications(latestItems);
            });
        }

        document.addEventListener('click', function (event) {
            if (!center.contains(event.target)) {
                dropdown.hidden = true;
                olderVisible = false;
                dropdown.classList.remove('is-expanded');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });

        window.addEventListener('resize', function () {
            if (!dropdown.hidden) positionDropdown();
        });

        fetchNotifications();
        window.setInterval(fetchNotifications, 10000);
    });
}

function initAdminModuleButtons() {
    var moduleButtons = document.querySelectorAll('.admin-module-btn');
    if (!moduleButtons.length) {
        return;
    }

    var titleEl = document.getElementById('adminModuleDescriptionTitle');
    var textEl = document.getElementById('adminModuleDescriptionText');
    var descriptionBox = document.getElementById('adminModuleDescription');

    if (!titleEl || !textEl || !descriptionBox) {
        return;
    }

    moduleButtons.forEach(function (button) {
        var wasActive = button.classList.contains('active');

        function updateModuleDescription() {
            var moduleTitle = button.getAttribute('data-module-title') || 'Module';
            var moduleDescription = button.getAttribute('data-module-description') || '';

            moduleButtons.forEach(function (item) {
                item.classList.remove('is-previewed');
            });

            button.classList.add('is-previewed');
            titleEl.textContent = moduleTitle;
            textEl.textContent = moduleDescription;
        }

        function clearModulePreview() {
            button.classList.remove('is-previewed');
            if (wasActive) {
                button.classList.add('active');
            }
        }

        button.addEventListener('mouseenter', updateModuleDescription);
        button.addEventListener('focus', updateModuleDescription);
        button.addEventListener('mouseleave', clearModulePreview);
        button.addEventListener('blur', clearModulePreview);
    });
}

function initAdminUsersList() {
    var page = document.querySelector('.users-list-page');
    if (!page) {
        return;
    }

    var searchInput = document.getElementById('usersSearchInput');
    var exportButton = page.querySelector('[data-users-export]');
    var sortButton = page.querySelector('[data-users-sort]');
    var resultCount = document.getElementById('usersResultsCount');
    var table = page.querySelector('[data-users-table]');
    var tbody = table ? table.querySelector('tbody') : null;
    var noUsersRow = page.querySelector('[data-no-users-row]');
    var rows = page.querySelectorAll('[data-user-row]');
    var searchEndpoint = page.getAttribute('data-users-search-endpoint') || '';
    var isRemoteSearch = searchEndpoint !== '';
    var debounceTimer = null;
    var requestToken = 0;
    var initialRowsHtml = tbody ? tbody.innerHTML : '';
    var sortEnabled = false;
    var sortDirection = 'desc';

    if (!searchInput || !table || !tbody) {
        return;
    }

    function buildSearchText(row) {
        return (row.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
    }

    function normalizeValue(value) {
        if (value === null || value === undefined) {
            return '';
        }

        return String(value);
    }

    function formatWithUnit(value, unit) {
        var text = normalizeValue(value);
        if (unit) {
            return text + ' ' + unit;
        }

        return text;
    }

    function compareStrings(a, b) {
        return a.localeCompare(b, 'fr', { sensitivity: 'base' });
    }

    function normalizeSortText(value) {
        return normalizeValue(value).trim();
    }

    function compareUsersByName(a, b) {
        var nomA = normalizeSortText(a.nom || '');
        var nomB = normalizeSortText(b.nom || '');
        var cmp = compareStrings(nomA, nomB);

        if (cmp === 0) {
            var prenomA = normalizeSortText(a.prenom || '');
            var prenomB = normalizeSortText(b.prenom || '');
            cmp = compareStrings(prenomA, prenomB);
        }

        return sortDirection === 'desc' ? -cmp : cmp;
    }

    function sortUsersArray(users) {
        return users.slice().sort(compareUsersByName);
    }

    function getCellText(row, index) {
        if (!row || !row.cells || !row.cells[index]) {
            return '';
        }

        return (row.cells[index].textContent || '').trim();
    }

    function compareRowsByName(rowA, rowB) {
        var cmp = compareStrings(getCellText(rowA, 0), getCellText(rowB, 0));
        if (cmp === 0) {
            cmp = compareStrings(getCellText(rowA, 1), getCellText(rowB, 1));
        }

        return sortDirection === 'desc' ? -cmp : cmp;
    }

    function sortLocalRows() {
        var rowArray = Array.prototype.slice.call(tbody.querySelectorAll('[data-user-row]'));
        rowArray.sort(compareRowsByName);
        rowArray.forEach(function (row) {
            tbody.appendChild(row);
        });
        rows = rowArray;
    }

    function updateSortButtonLabel() {
        if (!sortButton) {
            return;
        }

        var directionLabel = sortDirection === 'desc' ? 'Z-A' : 'A-Z';
        sortButton.innerHTML = '<i class="fa-solid fa-sort"></i> Trier Nom ' + directionLabel;
        sortButton.setAttribute('aria-pressed', sortEnabled ? 'true' : 'false');
    }

    function updateResultCount(visibleRows) {
        if (!resultCount) {
            return;
        }

        resultCount.textContent = visibleRows + ' utilisateur(s) affichÃ©(s)';
    }

    function ensureNoUsersRow() {
        if (noUsersRow) {
            return noUsersRow;
        }

        noUsersRow = document.createElement('tr');
        noUsersRow.setAttribute('data-no-users-row', '');

        var cell = document.createElement('td');
        cell.colSpan = 10;
        cell.className = 'text-center';
        cell.textContent = 'Aucun utilisateur trouve';
        noUsersRow.appendChild(cell);

        return noUsersRow;
    }

    function applyLocalFilter() {
        var query = (searchInput.value || '').trim().toLowerCase();
        var visibleRows = 0;

        rows.forEach(function (row) {
            var matches = query === '' || buildSearchText(row).indexOf(query) !== -1;
            row.style.display = matches ? '' : 'none';
            if (matches) {
                visibleRows += 1;
            }
        });

        if (noUsersRow) {
            noUsersRow.style.display = visibleRows === 0 ? '' : 'none';
        }

        updateResultCount(visibleRows);
    }

    function createTextCell(text) {
        var cell = document.createElement('td');
        cell.textContent = text;
        return cell;
    }

    function createActionCell(user) {
        var cell = document.createElement('td');
        cell.className = 'users-actions';

        var editLink = document.createElement('a');
        editLink.href = '/projet-web-25-26/index.php?action=edit-user&id=' + encodeURIComponent(normalizeValue(user.id));
        editLink.className = 'btn-edit';
        editLink.innerHTML = '<i class="fa-solid fa-pen"></i> Modifier';

        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '/projet-web-25-26/index.php?action=delete-user';
        form.className = 'inline-form';
        form.setAttribute('novalidate', 'novalidate');
        form.onsubmit = function () {
            return confirm('Supprimer cet utilisateur ?');
        };

        var idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = normalizeValue(user.id);

        var deleteButton = document.createElement('button');
        deleteButton.type = 'submit';
        deleteButton.className = 'btn-delete-user';
        deleteButton.innerHTML = '<i class="fa-solid fa-trash"></i> Supprimer';

        form.appendChild(idInput);
        form.appendChild(deleteButton);

        cell.appendChild(editLink);
        cell.appendChild(form);

        return cell;
    }

    function renderRemoteRows(users) {
        while (tbody.firstChild) {
            tbody.removeChild(tbody.firstChild);
        }

        if (!users.length) {
            tbody.appendChild(ensureNoUsersRow());
            updateResultCount(0);
            return;
        }

        var fragment = document.createDocumentFragment();
        var usersToRender = sortEnabled ? sortUsersArray(users) : users;

        usersToRender.forEach(function (user) {
            var row = document.createElement('tr');
            row.setAttribute('data-user-row', '');

            row.appendChild(createTextCell(normalizeValue(user.nom)));
            row.appendChild(createTextCell(normalizeValue(user.prenom)));
            row.appendChild(createTextCell(normalizeValue(user.date_naissance)));
            row.appendChild(createTextCell(normalizeValue(user.sexe)));
            row.appendChild(createTextCell(normalizeValue(user.age)));
            row.appendChild(createTextCell(formatWithUnit(user.poids, 'kg')));
            row.appendChild(createTextCell(formatWithUnit(user.taille, 'cm')));
            row.appendChild(createTextCell(normalizeValue(user.objectif)));
            row.appendChild(createTextCell(normalizeValue(user.email)));
            row.appendChild(createActionCell(user));

            fragment.appendChild(row);
        });

        tbody.appendChild(fragment);
        updateResultCount(users.length);
    }

    function buildSearchUrl(query) {
        var separator = searchEndpoint.indexOf('?') === -1 ? '?' : '&';
        return searchEndpoint + separator + 'search=' + encodeURIComponent(query);
    }

    function requestRemoteSearch() {
        var query = (searchInput.value || '').trim();
        var currentToken = ++requestToken;

        fetch(buildSearchUrl(query), {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Recherche indisponible.');
                }
                return response.json();
            })
            .then(function (payload) {
                if (currentToken !== requestToken) {
                    return;
                }

                if (!payload || payload.success !== true || !Array.isArray(payload.users)) {
                    throw new Error('Donnees invalides.');
                }

                renderRemoteRows(payload.users);
            })
            .catch(function () {
                if (currentToken !== requestToken) {
                    return;
                }

                if (initialRowsHtml) {
                    tbody.innerHTML = initialRowsHtml;
                    rows = page.querySelectorAll('[data-user-row]');
                    if (sortEnabled) {
                        sortLocalRows();
                    }
                    applyLocalFilter();
                    return;
                }

                renderRemoteRows([]);
            });
    }

    function applyFilter() {
        if (isRemoteSearch) {
            if (debounceTimer) {
                clearTimeout(debounceTimer);
            }
            debounceTimer = setTimeout(requestRemoteSearch, 250);
            return;
        }

        applyLocalFilter();
    }

    searchInput.addEventListener('input', applyFilter);

    if (sortButton) {
        updateSortButtonLabel();
        sortButton.addEventListener('click', function () {
            if (!sortEnabled) {
                sortEnabled = true;
            } else {
                sortDirection = sortDirection === 'desc' ? 'asc' : 'desc';
            }

            updateSortButtonLabel();

            if (isRemoteSearch) {
                requestRemoteSearch();
                return;
            }

            sortLocalRows();
        });
    }

    if (isRemoteSearch) {
        requestRemoteSearch();
    } else {
        applyLocalFilter();
    }

    if (exportButton) {
        exportButton.addEventListener('click', function () {
            var url = '/projet-web-25-26/index.php?action=users-report&search=' + encodeURIComponent(searchInput.value || '');
            window.open(url, '_blank', 'noopener');
        });
    }
}

function initBackgroundParallax() {
    var body = document.body;
    var ticking = false;

    function updateBackground(e) {
        var x = e.clientX / window.innerWidth;
        var y = e.clientY / window.innerHeight;

        var blueX = 18 - (x - 0.5) * 6;
        var blueY = 14 - (y - 0.5) * 6;
        var orangeX = 82 + (x - 0.5) * 6;
        var orangeY = 88 + (y - 0.5) * 6;

        body.style.backgroundPosition =
            '0 0, ' +
            blueX.toFixed(2) + '% ' + blueY.toFixed(2) + '%, ' +
            orangeX.toFixed(2) + '% ' + orangeY.toFixed(2) + '%, ' +
            '0 0';

        ticking = false;
    }

    document.addEventListener('mousemove', function (e) {
        if (!ticking) {
            window.requestAnimationFrame(function () {
                updateBackground(e);
            });
            ticking = true;
        }
    });
}

function initAdvancedBackground() {
    var canvas = document.querySelector('.bg-canvas');
    if (!canvas) {
        canvas = document.createElement('canvas');
        canvas.className = 'bg-canvas';
        canvas.style.position = 'fixed';
        canvas.style.top = '0';
        canvas.style.left = '0';
        canvas.style.width = '100%';
        canvas.style.height = '100%';
        canvas.style.pointerEvents = 'none';
        canvas.style.zIndex = '-1';
        document.body.prepend(canvas);
    }

    var ctx = canvas.getContext('2d');
    var width = 0;
    var height = 0;
    var particles = [];
    var foodParticles = [];
    var foodIcons = ['ðŸŽ', 'ðŸ¥•', 'ðŸ¥¦', 'ðŸŒ', 'ðŸ¥—', 'ðŸ‡', 'ðŸ¥‘', 'ðŸ“'];
    var time = 0;

    function resize() {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
        createParticles();
        createFoodParticles();
    }

    function createParticles() {
        particles = [];
        var count = Math.floor((width * height) / 10000);

        for (var i = 0; i < count; i++) {
            var colorType = Math.random() > 0.8 ? 'orange' : (Math.random() > 0.5 ? 'blue' : 'green');
            particles.push({
                x: Math.random() * width,
                y: Math.random() * height,
                vx: (Math.random() - 0.5) * 0.5,
                vy: (Math.random() - 0.5) * 0.5,
                size: Math.random() * 2 + 0.5,
                colorType: colorType,
                phase: Math.random() * Math.PI * 2
            });
        }
    }

    function createFoodParticles() {
        foodParticles = [];
        var count = Math.max(8, Math.min(18, Math.floor((width * height) / 95000)));

        for (var i = 0; i < count; i++) {
            foodParticles.push({
                icon: foodIcons[Math.floor(Math.random() * foodIcons.length)],
                x: Math.random() * width,
                y: Math.random() * height,
                vx: (Math.random() - 0.5) * 0.22,
                vy: (Math.random() - 0.5) * 0.22,
                size: Math.random() * 14 + 14,
                alpha: Math.random() * 0.26 + 0.12,
                spin: (Math.random() - 0.5) * 0.003,
                angle: Math.random() * Math.PI * 2,
                phase: Math.random() * Math.PI * 2
            });
        }
    }

    function particleColor(type, alpha) {
        if (type === 'orange') {
            return 'rgba(243, 156, 18, ' + alpha + ')';
        }
        if (type === 'green') {
            return 'rgba(46, 204, 113, ' + alpha + ')';
        }
        return 'rgba(52, 152, 219, ' + alpha + ')';
    }

    function animate() {
        ctx.clearRect(0, 0, width, height);
        time += 0.005;

        var isLightTheme = document.body.classList.contains('theme-light');
        var waveBlueStroke = isLightTheme
            ? 'rgba(43, 108, 176, 0.18)'
            : 'rgba(52, 152, 219, 0.05)';
        var waveGreenStroke = isLightTheme
            ? 'rgba(47, 133, 90, 0.14)'
            : 'rgba(46, 204, 113, 0.04)';
        var linkBaseColor = isLightTheme
            ? 'rgba(43, 108, 176, '
            : 'rgba(52, 152, 219, ';
        var linkAlphaFactor = isLightTheme ? 0.22 : 0.1;

        ctx.beginPath();
        ctx.strokeStyle = waveBlueStroke;
        ctx.lineWidth = 2;
        for (var x1 = 0; x1 < width; x1 += 10) {
            var y1 = height / 2 + Math.sin(x1 * 0.01 + time) * 100 + Math.sin(x1 * 0.003 + time * 0.5) * 100;
            ctx.lineTo(x1, y1);
        }
        ctx.stroke();

        ctx.beginPath();
        ctx.strokeStyle = waveGreenStroke;
        for (var x2 = 0; x2 < width; x2 += 10) {
            var y2 = height / 2 + Math.sin(x2 * 0.012 + time + 2) * 120 + Math.sin(x2 * 0.005 + time * 0.8) * 80;
            ctx.lineTo(x2, y2);
        }
        ctx.stroke();

        foodParticles.forEach(function (f) {
            f.x += f.vx;
            f.y += f.vy;
            f.angle += f.spin;

            if (f.x < -30) {
                f.x = width + 30;
            }
            if (f.x > width + 30) {
                f.x = -30;
            }
            if (f.y < -30) {
                f.y = height + 30;
            }
            if (f.y > height + 30) {
                f.y = -30;
            }

            var pulse = 0.86 + Math.sin(time * 2 + f.phase) * 0.14;

            ctx.save();
            ctx.translate(f.x, f.y);
            ctx.rotate(f.angle);
            ctx.globalAlpha = f.alpha;
            ctx.shadowBlur = 18;
            ctx.shadowColor = 'rgba(46, 204, 113, 0.35)';
            ctx.font = Math.floor(f.size * pulse) + 'px Segoe UI Emoji';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(f.icon, 0, 0);
            ctx.restore();
        });

        particles.forEach(function (p, index) {
            p.x += p.vx;
            p.y += p.vy;

            if (p.x < 0) {
                p.x = width;
            }
            if (p.x > width) {
                p.x = 0;
            }
            if (p.y < 0) {
                p.y = height;
            }
            if (p.y > height) {
                p.y = 0;
            }

            var pulse = Math.sin(time * 2 + p.phase) * 0.5 + 1;
            var opacity = p.colorType === 'orange' ? 0.8 : 0.4;
            ctx.fillStyle = particleColor(p.colorType, opacity);
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.size * pulse, 0, Math.PI * 2);
            ctx.fill();

            for (var j = index + 1; j < particles.length; j++) {
                var p2 = particles[j];
                var dx = p.x - p2.x;
                var dy = p.y - p2.y;
                var dist = Math.sqrt(dx * dx + dy * dy);

                if (dist < 120) {
                    var alpha = 1 - dist / 120;
                    if (alpha > 0) {
                        ctx.strokeStyle = linkBaseColor + (alpha * linkAlphaFactor) + ')';
                        ctx.beginPath();
                        ctx.moveTo(p.x, p.y);
                        ctx.lineTo(p2.x, p2.y);
                        ctx.stroke();
                    }
                }
            }
        });

        window.requestAnimationFrame(animate);
    }

    window.addEventListener('resize', resize);
    resize();
    animate();
}

function initFullScreenHandControl() {
    var statusEl = document.getElementById('gestureStatus');
    var video = document.getElementById('gestureVideoHidden');
    var hiddenCanvas = document.getElementById('gestureCanvasHidden');
    var cursor = document.getElementById('gestureCursor');

    if (!video || !cursor) {
        return;
    }

    var state = {
        stream: null,
        hands: null,
        isRunning: false,
        isProcessing: false,
        hoverElement: null,
        invertX: true,
        lastClickAt: 0,
        pinchActive: false,
        smoothX: window.innerWidth * 0.5,
        smoothY: window.innerHeight * 0.5,
        targetX: window.innerWidth * 0.5,
        targetY: window.innerHeight * 0.5
    };

    var smoothing = 0.3;
    var clickCooldownMs = 700;
    var pinchThreshold = 0.055;

    window.addEventListener('resize', function () {
        state.targetX = clamp(state.targetX, 0, window.innerWidth);
        state.targetY = clamp(state.targetY, 0, window.innerHeight);
        state.smoothX = clamp(state.smoothX, 0, window.innerWidth);
        state.smoothY = clamp(state.smoothY, 0, window.innerHeight);
    });

    function setStatus(message, level) {
        if (!statusEl) {
            return;
        }

        statusEl.textContent = 'Statut: ' + message;
        statusEl.classList.remove('ok', 'warn', 'err');
        if (level) {
            statusEl.classList.add(level);
        }
    }

    async function processLoop() {
        if (!state.isRunning) {
            return;
        }

        if (!state.isProcessing && video.readyState >= 2) {
            try {
                state.isProcessing = true;
                await state.hands.send({ image: video });
            } catch (error) {
                console.error(error);
                setStatus('Erreur de suivi. Veuillez relancer le mode geste.', 'err');
                stopTracking();
                return;
            } finally {
                state.isProcessing = false;
            }
        }

        requestAnimationFrame(processLoop);
    }

    function cursorLoop() {
        if (!state.isRunning) {
            return;
        }

        state.smoothX += (state.targetX - state.smoothX) * smoothing;
        state.smoothY += (state.targetY - state.smoothY) * smoothing;

        cursor.style.left = state.smoothX + 'px';
        cursor.style.top = state.smoothY + 'px';

        var hovered = document.elementFromPoint(state.smoothX, state.smoothY);
        var clickable = hovered
            ? hovered.closest('button, a, .btn, [role="button"], input[type="submit"], input[type="button"]')
            : null;

        updateHover(clickable);
        requestAnimationFrame(cursorLoop);
    }

    function onResults(results) {
        if (!results.multiHandLandmarks || results.multiHandLandmarks.length === 0) {
            cursor.classList.remove('active', 'click');
            clearHover();
            return;
        }

        var landmarks = results.multiHandLandmarks[0];

        var indexTip = landmarks[8];
        var thumbTip = landmarks[4];

        var normalizedX = indexTip.x;
        if (state.invertX) {
            normalizedX = 1 - normalizedX;
        }

        var normalizedY = indexTip.y;

        state.targetX = clamp(normalizedX * window.innerWidth, 0, window.innerWidth);
        state.targetY = clamp(normalizedY * window.innerHeight, 0, window.innerHeight);

        cursor.classList.add('active');

        var pinchDistance = Math.hypot(indexTip.x - thumbTip.x, indexTip.y - thumbTip.y);
        var isPinch = pinchDistance < pinchThreshold;
        var now = performance.now();

        if (isPinch && !state.pinchActive && now - state.lastClickAt > clickCooldownMs) {
            var hovered = document.elementFromPoint(state.smoothX, state.smoothY);
            var clickable = hovered
                ? hovered.closest('button, a, .btn, [role="button"], input[type="submit"], input[type="button"]')
                : null;

            if (clickable) {
                clickable.click();
                pulseCursorClick();
                setStatus('Clic geste sur: ' + getElementLabel(clickable), 'ok');
                state.lastClickAt = now;
            }
        }

        state.pinchActive = isPinch;
    }

    function pulseCursorClick() {
        cursor.classList.add('click');
        setTimeout(function () {
            cursor.classList.remove('click');
        }, 140);
    }

    function updateHover(nextElement) {
        if (state.hoverElement === nextElement) {
            return;
        }

        if (state.hoverElement) {
            state.hoverElement.classList.remove('gesture-hover');
        }

        state.hoverElement = nextElement;

        if (state.hoverElement) {
            state.hoverElement.classList.add('gesture-hover');
        }
    }

    function clearHover() {
        if (state.hoverElement) {
            state.hoverElement.classList.remove('gesture-hover');
            state.hoverElement = null;
        }
    }

    function getElementLabel(element) {
        var text = (element.textContent || '').trim();
        if (text.length > 0) {
            return text.slice(0, 40);
        }

        if (element.getAttribute('aria-label')) {
            return element.getAttribute('aria-label');
        }

        return element.tagName.toLowerCase();
    }

    function stopTracking() {
        state.isRunning = false;

        if (state.stream) {
            state.stream.getTracks().forEach(function (track) {
                track.stop();
            });
            state.stream = null;
        }

        video.srcObject = null;
        cursor.classList.remove('active', 'click');
        clearHover();
        setStatus('mode geste inactif', 'warn');
    }

    async function startTracking() {
        if (state.isRunning) {
            return;
        }

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            setStatus('L\'API camera n\'est pas prise en charge par ce navigateur.', 'err');
            return;
        }

        if (!window.Hands) {
            setStatus('MediaPipe Hands n\'est pas charge.', 'err');
            return;
        }

        try {
            setStatus('Demarrage de la camera cachee (auto)...', 'warn');

            state.stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 960 },
                    height: { ideal: 540 },
                    facingMode: 'user'
                },
                audio: false
            });

            video.srcObject = state.stream;
            await video.play();

            hiddenCanvas.width = 2;
            hiddenCanvas.height = 2;

            state.hands = new window.Hands({
                locateFile: function (file) {
                    return 'https://cdn.jsdelivr.net/npm/@mediapipe/hands/' + file;
                }
            });

            state.hands.setOptions({
                maxNumHands: 1,
                modelComplexity: 1,
                minDetectionConfidence: 0.65,
                minTrackingConfidence: 0.65
            });

            state.hands.onResults(onResults);
            state.isRunning = true;
            setStatus('Geste actif (auto, axe inverse).', 'ok');

            requestAnimationFrame(processLoop);
            requestAnimationFrame(cursorLoop);
        } catch (error) {
            console.error(error);
            setStatus('Impossible d\'acceder a la camera. Autorisez la permission pour continuer.', 'err');
        }
    }

    window.smartGestureController = {
        start: startTracking,
        stop: stopTracking,
        isRunning: function () {
            return state.isRunning;
        }
    };

    function startWhenHandsReady(retries) {
        if (window.Hands) {
            startTracking();
            return;
        }

        if (retries <= 0) {
            setStatus('Mode geste indisponible (connexion requise).', 'warn');
            return;
        }

        setTimeout(function () {
            startWhenHandsReady(retries - 1);
        }, 500);
    }

    startWhenHandsReady(20);
}

function clamp(value, min, max) {
    return Math.max(min, Math.min(max, value));
}
