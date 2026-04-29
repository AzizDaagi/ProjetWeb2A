document.addEventListener('DOMContentLoaded', function () {
    document.body.classList.add('is-ready');

    runInitSafely(initThemeToggle, 'theme-toggle');
    runInitSafely(initAutoDismissAlerts, 'alerts');
    runInitSafely(initFormSubmitLock, 'form-lock');
    runInitSafely(initFaceAuth, 'face-auth');
    runInitSafely(initHomeTopicButtons, 'home-topics');
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
    var savedTheme = null;

    try {
        savedTheme = localStorage.getItem(storageKey);
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

    var toggleButton = document.getElementById('themeToggle');
    if (!toggleButton) {
        return;
    }

    toggleButton.addEventListener('click', function () {
        var nextTheme = document.body.classList.contains('theme-light')
            ? 'dark'
            : 'light';
        applyTheme(nextTheme, storageKey);
    });
}

function applyTheme(theme, storageKey) {
    var isLight = theme === 'light';

    document.body.classList.toggle('theme-light', isLight);
    document.body.classList.toggle('theme-dark', !isLight);
    document.documentElement.style.colorScheme = isLight ? 'light' : 'dark';

    var toggleButton = document.getElementById('themeToggle');
    if (toggleButton) {
        toggleButton.setAttribute('aria-pressed', isLight ? 'true' : 'false');
        toggleButton.title = isLight ? 'Passer en mode sombre' : 'Passer en mode clair';
        toggleButton.innerHTML = isLight
            ? '<i class="fa-solid fa-sun"></i> Clair'
            : '<i class="fa-solid fa-moon"></i> Sombre';
    }

    try {
        localStorage.setItem(storageKey, isLight ? 'light' : 'dark');
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
        button.addEventListener('click', function () {
            var moduleTitle = button.getAttribute('data-module-title') || 'Module';
            var moduleDescription = button.getAttribute('data-module-description') || '';

            moduleButtons.forEach(function (item) {
                item.classList.remove('active');
            });

            button.classList.add('active');
            titleEl.textContent = moduleTitle;
            textEl.textContent = moduleDescription;

            descriptionBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    });
}

function initAdminUsersList() {
    var page = document.querySelector('.users-list-page');
    if (!page) {
        return;
    }

    var searchInput = document.getElementById('usersSearchInput');
    var exportButton = page.querySelector('[data-users-export]');
    var resultCount = document.getElementById('usersResultsCount');
    var table = page.querySelector('[data-users-table]');
    var rows = page.querySelectorAll('[data-user-row]');
    var noUsersRow = page.querySelector('[data-no-users-row]');

    if (!searchInput || !table) {
        return;
    }

    function buildSearchText(row) {
        return (row.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
    }

    function updateResultCount(visibleRows) {
        if (!resultCount) {
            return;
        }

        resultCount.textContent = visibleRows + ' utilisateur(s) affiché(s)';
    }

    function applyFilter() {
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

    searchInput.addEventListener('input', applyFilter);
    applyFilter();

    if (exportButton) {
        exportButton.addEventListener('click', function () {
            var url = '/smart_nutrition/index.php?action=users-report&search=' + encodeURIComponent(searchInput.value || '');
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
    var foodIcons = ['🍎', '🥕', '🥦', '🍌', '🥗', '🍇', '🥑', '🍓'];
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
