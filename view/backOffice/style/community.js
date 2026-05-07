function initCommunityPage() {
    [
        initBackofficeTables,
        initThemeToggle,
        initAutoDismissAlerts,
        initFormSubmitLock,
        initNotifications,
        initAnimatedBackground
    ].forEach(init => {
        try {
            init();
        } catch (error) {
            console.error('Erreur initialisation community.js:', error);
        }
    });

    document.body.classList.add('is-ready');
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCommunityPage);
} else {
    initCommunityPage();
}

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
        btn.innerHTML = isLight ? '<i class="fa-solid fa-sun"></i> Clair' : '<i class="fa-solid fa-moon"></i> Sombre';
        btn.title = isLight ? 'Passer au mode sombre' : 'Passer au mode clair';
        btn.setAttribute('aria-pressed', String(!isLight));
        btn.setAttribute('aria-label', isLight ? 'Passer au mode sombre' : 'Passer au mode clair');
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
                submitBtn.textContent = 'Traitement...';
            }
        });
    });
}

function initNotifications() {
    const center = document.querySelector('.notification-center');
    if (!center) return;

    const endpoint = center.dataset.notificationEndpoint;
    const toggle = document.getElementById('notificationToggle');
    const dropdown = document.getElementById('notificationDropdown');
    const badge = document.getElementById('notificationBadge');
    const list = document.getElementById('notificationList');
    const markAll = document.getElementById('notificationMarkAll');
    const showOlder = document.getElementById('notificationShowOlder');
    const initialVisibleCount = 5;
    let olderVisible = false;

    if (!endpoint || !toggle || !dropdown || !badge || !list) return;

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value || '';
        return div.innerHTML;
    }

    function getNotificationGroup(createdAt) {
        if (!createdAt) return 'Plus anciennes';

        const created = new Date(createdAt.replace(' ', 'T'));
        const now = new Date();
        const diffMs = now.getTime() - created.getTime();
        const dayMs = 24 * 60 * 60 * 1000;

        if (diffMs < dayMs && created.getDate() === now.getDate()) {
            return 'Aujourd hui';
        }
        if (diffMs < 7 * dayMs) {
            return 'Semaine derniere';
        }
        if (diffMs < 31 * dayMs) {
            return 'Mois dernier';
        }
        return 'Plus anciennes';
    }

    function renderNotifications(items) {
        if (!items || items.length === 0) {
            list.innerHTML = '<p class="notification-empty">Aucune notification pour le moment.</p>';
            if (showOlder) showOlder.hidden = true;
            return;
        }

        const visibleItems = olderVisible ? items : items.slice(0, initialVisibleCount);
        const groupedHtml = [];
        let currentGroup = '';

        visibleItems.forEach(item => {
            const group = getNotificationGroup(item.created_at);
            if (group !== currentGroup) {
                currentGroup = group;
                groupedHtml.push(`<div class="notification-group-label">${escapeHtml(group)}</div>`);
            }

            const unreadClass = Number(item.is_read) ? '' : ' is-unread';
            const time = item.created_at ? new Date(item.created_at.replace(' ', 'T')).toLocaleString() : '';
            groupedHtml.push(`
                <button type="button" class="notification-item${unreadClass}" data-id="${item.id}" data-link="${escapeHtml(item.link_url || '')}">
                    <span class="notification-item-title">${escapeHtml(item.title)}</span>
                    <span class="notification-item-message">${escapeHtml(item.message)}</span>
                    <span class="notification-item-time">${escapeHtml(time)}</span>
                </button>
            `);
        });

        list.innerHTML = groupedHtml.join('');

        if (showOlder) {
            showOlder.hidden = items.length <= initialVisibleCount;
            showOlder.textContent = olderVisible ? 'Anciennes notifications affichees' : 'Voir les anciennes notifications';
            showOlder.disabled = olderVisible;
        }
    }

    function updateBadge(count) {
        const unreadCount = Number(count || 0);
        badge.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
        badge.hidden = unreadCount === 0;
    }

    function positionDropdown() {
        const rect = toggle.getBoundingClientRect();
        const dropdownWidth = Math.min(360, window.innerWidth - 28);
        const right = Math.max(14, window.innerWidth - rect.right);
        const top = Math.min(rect.bottom + 10, window.innerHeight - 120);

        dropdown.style.width = dropdownWidth + 'px';
        dropdown.style.right = right + 'px';
        dropdown.style.top = Math.max(12, top) + 'px';
    }

    function fetchNotifications() {
        fetch(endpoint + '?action=list', { cache: 'no-store' })
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;
                updateBadge(data.unreadCount);
                renderNotifications(data.notifications || []);
            })
            .catch(() => {});
    }

    function markRead(id, callback) {
        fetch(endpoint + '?action=mark_read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(id)
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateBadge(data.unreadCount);
                    fetchNotifications();
                }
                if (callback) callback();
            })
            .catch(() => {
                if (callback) callback();
            });
    }

    toggle.addEventListener('click', () => {
        const isOpen = !dropdown.hidden;
        dropdown.hidden = isOpen;
        toggle.setAttribute('aria-expanded', String(!isOpen));
        if (!isOpen) {
            positionDropdown();
            fetchNotifications();
        }
    });

    list.addEventListener('click', event => {
        const item = event.target.closest('.notification-item');
        if (!item) return;

        const link = item.dataset.link || '';
        markRead(item.dataset.id, () => {
            if (link) {
                window.location.href = link;
            }
        });
    });

    if (markAll) {
        markAll.addEventListener('click', () => {
            fetch(endpoint + '?action=mark_all_read', {
                method: 'POST'
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        updateBadge(data.unreadCount);
                        fetchNotifications();
                    }
                })
                .catch(() => {});
        });
    }

    if (showOlder) {
        showOlder.addEventListener('click', () => {
            olderVisible = true;
            dropdown.classList.add('is-expanded');
            fetchNotifications();
        });
    }

    document.addEventListener('click', event => {
        if (!center.contains(event.target)) {
            dropdown.hidden = true;
            olderVisible = false;
            dropdown.classList.remove('is-expanded');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });

    window.addEventListener('resize', () => {
        if (!dropdown.hidden) {
            positionDropdown();
        }
    });

    fetchNotifications();
    window.setInterval(fetchNotifications, 4000);
}

function triggerModerationJobs() {
    fetch('/Web/controller/moderationJobController.php', {
        method: 'POST',
        cache: 'no-store'
    })
        .then(res => res.json())
        .then(data => {
            if (data.hasPending) {
                window.setTimeout(triggerModerationJobs, 700);
            }
        })
        .catch(() => {});
}

function initBackofficeTables() {
    document.querySelectorAll('[data-filter-table]').forEach(table => {
        const tableId = table.dataset.filterTable;
        const controls = document.querySelector(`[data-table-controls="${tableId}"]`);
        const tbody = table.querySelector('tbody');
        if (!tableId || !controls || !tbody) return;

        const searchInput = controls.querySelector('[data-table-search]');
        const sortSelect = controls.querySelector('[data-table-sort]');
        const filterSelects = Array.from(controls.querySelectorAll('[data-table-filter]'));
        const countTarget = document.querySelector(`[data-table-count="${tableId}"]`);
        const originalRows = Array.from(tbody.querySelectorAll('.js-filter-row'));
        const rowGroups = originalRows.map(row => {
            const details = row.dataset.rowId
                ? tbody.querySelector(`[data-details-for="${row.dataset.rowId}"]`)
                : null;

            return {
                row: row,
                details: details,
                originalIndex: originalRows.indexOf(row)
            };
        });

        let emptyRow = tbody.querySelector('.admin-empty-row');
        if (!emptyRow) {
            emptyRow = document.createElement('tr');
            emptyRow.className = 'admin-empty-row';
            emptyRow.hidden = true;

            const cell = document.createElement('td');
            cell.colSpan = table.querySelectorAll('thead th').length || 1;
            cell.textContent = 'Aucun resultat trouve.';
            emptyRow.appendChild(cell);
        }

        function normalize(value) {
            return String(value || '')
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .trim();
        }

        function getDateValue(row) {
            const value = row.dataset.date || '';
            const timestamp = Date.parse(value.replace(' ', 'T'));
            return Number.isNaN(timestamp) ? 0 : timestamp;
        }

        function getNumberValue(row, key) {
            return Number(row.dataset[key] || 0);
        }

        function aiRank(row) {
            const status = normalize(row.dataset.ai);
            const imageStatus = normalize(row.dataset.imageAi);
            const ranking = {
                review: 0,
                error: 1,
                blocked: 2,
                missing: 3,
                allowed: 4
            };

            return Math.min(
                ranking[status] ?? 5,
                ranking[imageStatus] ?? 5
            );
        }

        function compareText(a, b, key) {
            return normalize(a.row.dataset[key]).localeCompare(normalize(b.row.dataset[key]), 'fr');
        }

        function sortGroups(groups) {
            const sortValue = sortSelect ? sortSelect.value : '';
            const sorted = groups.slice();

            sorted.sort((a, b) => {
                let result = 0;

                if (sortValue === 'date_asc') {
                    result = getDateValue(a.row) - getDateValue(b.row);
                } else if (sortValue === 'date_desc') {
                    result = getDateValue(b.row) - getDateValue(a.row);
                } else if (sortValue === 'title_asc') {
                    result = compareText(a, b, 'title');
                } else if (sortValue === 'author_asc') {
                    result = compareText(a, b, 'author');
                } else if (sortValue === 'reporter_asc') {
                    result = compareText(a, b, 'reporter');
                } else if (sortValue === 'reason_asc') {
                    result = compareText(a, b, 'reason');
                } else if (sortValue === 'status_asc') {
                    result = compareText(a, b, 'status');
                } else if (sortValue === 'comments_desc') {
                    result = getNumberValue(b.row, 'comments') - getNumberValue(a.row, 'comments');
                } else if (sortValue === 'ai_review') {
                    result = aiRank(a.row) - aiRank(b.row);
                }

                return result || a.originalIndex - b.originalIndex;
            });

            return sorted;
        }

        function matchesFilters(group) {
            const searchValue = normalize(searchInput ? searchInput.value : '');
            const rowSearch = normalize(group.row.dataset.search);

            if (searchValue && !rowSearch.includes(searchValue)) {
                return false;
            }

            return filterSelects.every(select => {
                const key = select.dataset.tableFilter;
                const selected = normalize(select.value);
                if (!key || !selected) return true;
                return normalize(group.row.dataset[key]) === selected;
            });
        }

        function render() {
            const visibleGroups = sortGroups(rowGroups).filter(matchesFilters);

            rowGroups.forEach(group => {
                group.row.hidden = true;
                if (group.details) {
                    group.details.hidden = true;
                }
            });

            visibleGroups.forEach(group => {
                group.row.hidden = false;
                tbody.appendChild(group.row);

                if (group.details) {
                    group.details.hidden = false;
                    tbody.appendChild(group.details);
                }
            });

            emptyRow.hidden = visibleGroups.length > 0;
            tbody.appendChild(emptyRow);

            if (countTarget) {
                countTarget.textContent = String(visibleGroups.length);
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', render);
        }
        if (sortSelect) {
            sortSelect.addEventListener('change', render);
        }
        filterSelects.forEach(select => {
            select.addEventListener('change', render);
        });

        render();
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

// ===== News Carousel (auto + manual) =====
function initNewsCarousel() {
    const carousel = document.getElementById('newsCarousel');
    if (!carousel) return;
    if (carousel.dataset.carouselReady === 'true') return;
    carousel.dataset.carouselReady = 'true';

    const track = carousel.querySelector('.news-carousel-track');
    const slides = Array.from(carousel.querySelectorAll('.news-carousel-slide'));
    if (!track || slides.length === 0) return;

    const prevBtn = carousel.querySelector('[data-carousel-direction="prev"]');
    const nextBtn = carousel.querySelector('[data-carousel-direction="next"]');
    const dotsWrap = carousel.querySelector('.news-carousel-dots');

    const interval = Number(carousel.dataset.interval || 4000);

    let index = 0;
    let timer = null;

    function renderDots() {
        if (!dotsWrap) return;
        dotsWrap.innerHTML = '';

        slides.forEach((_, i) => {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'news-carousel-dot' + (i === index ? ' is-active' : '');
            dot.setAttribute('aria-label', 'Aller a l actualite ' + (i + 1));
            dot.addEventListener('click', () => goTo(i, true));
            dotsWrap.appendChild(dot);
        });
    }


    function updateUI() {
        if (dotsWrap) {
            const dots = Array.from(dotsWrap.querySelectorAll('.news-carousel-dot'));
            dots.forEach((d, i) => d.classList.toggle('is-active', i === index));
        }
    }

    function goTo(i, fromUser) {
        index = (i + slides.length) % slides.length;

        const viewport = carousel.querySelector('.news-carousel-viewport');
        // Slide width is exactly the viewport width (CSS sets slide flex-basis 100%).
        const slideWidth = viewport ? viewport.getBoundingClientRect().width : carousel.getBoundingClientRect().width;
        const offsetPx = index * slideWidth;

        track.style.transform = `translateX(-${offsetPx}px)`;
        updateUI();

        if (fromUser) {
            pause();
            play();
        }
    }


    function updateLayoutTransform() {
        const viewport = carousel.querySelector('.news-carousel-viewport');
        const viewportWidth = viewport ? viewport.clientWidth : carousel.clientWidth;
        const offsetPx = index * viewportWidth;
        track.style.transform = `translateX(-${offsetPx}px)`;
        updateUI();
    }


    function next() {
        goTo(index + 1, true);
    }

    function prev() {
        goTo(index - 1, true);
    }

    function play() {
        if (timer) return;
        if (slides.length <= 1) return;
        timer = window.setInterval(() => {
            // Don't move if user is actively interacting (e.g. hovering or modal open)
            if (carousel.matches(':hover')) return;
            goTo(index + 1, false);
        }, interval);
    }


    function pause() {
        if (!timer) return;
        window.clearInterval(timer);
        timer = null;
    }

    if (prevBtn) prevBtn.addEventListener('click', prev);
    if (nextBtn) nextBtn.addEventListener('click', next);

    carousel.addEventListener('mouseenter', pause);
    carousel.addEventListener('mouseleave', play);
    window.addEventListener('resize', updateLayoutTransform);

    window.addEventListener('visibilitychange', () => {
        if (document.hidden) pause();
        else play();
    });

    // initial state
    renderDots();
    updateUI();
    goTo(0, false);
    play();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNewsCarousel);
} else {
    initNewsCarousel();
}

