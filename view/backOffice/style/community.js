document.addEventListener('DOMContentLoaded', function () {
    initAnimatedBackground();
    initThemeToggle();
    document.body.classList.add('is-ready');
    initAutoDismissAlerts();
    initFormSubmitLock();
});

function initThemeToggle() {
    const storageKey = 'communityTheme';
    let savedTheme = localStorage.getItem(storageKey);
    const prefersLight = window.matchMedia('(prefers-color-scheme: light)').matches;
    let initialTheme = savedTheme || (prefersLight ? 'light' : 'dark');
    applyTheme(initialTheme);

    const toggleButtons = document.querySelectorAll('#themeToggle, #themeToggleFloating');
    toggleButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const nextTheme = document.body.classList.contains('theme-light') ? 'dark' : 'light';
            applyTheme(nextTheme);
            localStorage.setItem(storageKey, nextTheme);
        });
    });
}

function applyTheme(theme) {
    const isLight = theme === 'light';
    document.body.classList.toggle('theme-light', isLight);
    document.querySelectorAll('#themeToggle, #themeToggleFloating').forEach(btn => {
        btn.innerHTML = isLight ? '<i class="fa-solid fa-sun"></i> Light' : '<i class="fa-solid fa-moon"></i> Dark';
        btn.title = isLight ? 'Dark mode' : 'Light mode';
    });
}

function initAutoDismissAlerts() {
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 400);
        }, 5000);
    });
}

function initFormSubmitLock() {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', () => {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';
            }
        });
    });
}

function initAnimatedBackground() {
    var stage = document.querySelector('.bg-stage');
    if (!stage) {
        stage = document.createElement('div');
        stage.className = 'bg-stage';
        document.body.prepend(stage);
    }

    var canvas = stage.querySelector('.bg-canvas');
    if (!canvas) {
        canvas = document.createElement('canvas');
        canvas.className = 'bg-canvas';
        stage.appendChild(canvas);
    }

    var ctx = canvas.getContext('2d');
    if (!ctx) {
        return;
    }

    var width = 0;
    var height = 0;
    var particles = [];
    var foodParticles = [];
    var foodIcons = ['🍎', '🥕', '🥦', '🍌', '🥗', '🍇', '🥑', '🍓'];
    var time = 0;

    function resize() {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
        stage.style.height = window.innerHeight + 'px';
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

// Community specific
function togglePostImage(id) {
    const img = document.querySelector(`#post-image-${id}`);
    if (img) img.style.display = img.style.display === 'none' ? 'block' : 'none';
}
