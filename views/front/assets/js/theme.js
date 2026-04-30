(function () {
    var storageKey = 'theme';
    var oldStorageKey = 'smartNutritionTheme';

    function getSavedTheme() {
        var theme = null;

        try {
            theme = localStorage.getItem(storageKey);

            if (theme !== 'dark' && theme !== 'light') {
                theme = localStorage.getItem(oldStorageKey);
            }
        } catch (error) {
            theme = null;
        }

        return theme === 'light' ? 'light' : 'dark';
    }

    function saveTheme(theme) {
        try {
            localStorage.setItem(storageKey, theme);
            localStorage.setItem(oldStorageKey, theme);
        } catch (error) {
            // Ignore storage errors.
        }
    }

    function updateToggle(theme) {
        var toggle = document.getElementById('themeToggle');

        if (!toggle) {
            return;
        }

        toggle.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
        toggle.title = theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode';
        toggle.innerHTML = theme === 'dark'
            ? '<i class="fa-solid fa-moon"></i> Dark'
            : '<i class="fa-solid fa-sun"></i> Light';
    }

    function applyTheme(theme) {
        var isDark = theme === 'dark';

        document.body.classList.toggle('dark', isDark);
        document.body.classList.toggle('theme-dark', isDark);
        document.body.classList.toggle('theme-light', !isDark);
        document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';

        updateToggle(theme);
        saveTheme(theme);
    }

    function initTheme() {
        var theme = getSavedTheme();
        var toggle = document.getElementById('themeToggle');

        applyTheme(theme);
        document.body.classList.add('is-ready');

        if (!toggle || toggle.dataset.themeBound === 'true') {
            return;
        }

        toggle.dataset.themeBound = 'true';
        toggle.addEventListener('click', function () {
            var nextTheme = document.body.classList.contains('dark') ? 'light' : 'dark';
            applyTheme(nextTheme);
        });
    }

    window.SmartNutritionTheme = {
        init: initTheme,
        apply: applyTheme
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTheme);
    } else {
        initTheme();
    }
})();
