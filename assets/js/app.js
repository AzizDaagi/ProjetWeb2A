document.addEventListener('DOMContentLoaded', function () {
    initThemeToggle();
    document.body.classList.add('is-ready');

    initAutoDismissAlerts();
    initFormSubmitLock();
    initBackgroundParallax();
    initAdvancedBackground();
    initFullScreenHandControl();
});

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
        toggleButton.title = isLight ? 'Switch to dark mode' : 'Switch to light mode';
        toggleButton.innerHTML = isLight
            ? '<i class="fa-solid fa-sun"></i> Light'
            : '<i class="fa-solid fa-moon"></i> Dark';
    }

    try {
        localStorage.setItem(storageKey, isLight ? 'light' : 'dark');
    } catch (error) {
        // Ignore storage errors (private mode, blocked storage, etc.)
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

    form.addEventListener('submit', function () {
        var submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Processing...';
        }
    });
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

        ctx.beginPath();
        ctx.strokeStyle = 'rgba(52, 152, 219, 0.05)';
        ctx.lineWidth = 2;
        for (var x1 = 0; x1 < width; x1 += 10) {
            var y1 = height / 2 + Math.sin(x1 * 0.01 + time) * 100 + Math.sin(x1 * 0.003 + time * 0.5) * 100;
            ctx.lineTo(x1, y1);
        }
        ctx.stroke();

        ctx.beginPath();
        ctx.strokeStyle = 'rgba(46, 204, 113, 0.04)';
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
                        ctx.strokeStyle = 'rgba(52, 152, 219, ' + (alpha * 0.1) + ')';
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

        statusEl.textContent = 'Status: ' + message;
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
                setStatus('Tracking error. Please restart gesture mode.', 'err');
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
                setStatus('Gesture click on: ' + getElementLabel(clickable), 'ok');
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
        setStatus('gesture mode inactive', 'warn');
    }

    async function startTracking() {
        if (state.isRunning) {
            return;
        }

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            setStatus('Camera API is not supported on this browser.', 'err');
            return;
        }

        if (!window.Hands) {
            setStatus('MediaPipe Hands not loaded.', 'err');
            return;
        }

        try {
            setStatus('Starting hidden camera (auto)...', 'warn');

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
            setStatus('Gesture active (auto, inverted axis).', 'ok');

            requestAnimationFrame(processLoop);
            requestAnimationFrame(cursorLoop);
        } catch (error) {
            console.error(error);
            setStatus('Unable to access camera. Allow permission to continue.', 'err');
        }
    }

    window.smartGestureController = {
        start: startTracking,
        stop: stopTracking,
        isRunning: function () {
            return state.isRunning;
        }
    };

    startTracking();
}

function clamp(value, min, max) {
    return Math.max(min, Math.min(max, value));
}
