document.addEventListener('DOMContentLoaded', function () {
    var body = document.body;
    var toggleButton = document.getElementById('themeToggle');
    var storedTheme = window.localStorage.getItem('backoffice-theme');

    if (storedTheme === 'light') {
        body.classList.remove('theme-dark');
        body.classList.add('theme-light');
    }

    if (toggleButton) {
        toggleButton.setAttribute('aria-pressed', body.classList.contains('theme-light') ? 'true' : 'false');

        toggleButton.addEventListener('click', function () {
            var isLight = body.classList.toggle('theme-light');
            body.classList.toggle('theme-dark', !isLight);
            toggleButton.setAttribute('aria-pressed', isLight ? 'true' : 'false');
            window.localStorage.setItem('backoffice-theme', isLight ? 'light' : 'dark');
        });
    }

    var moduleButtons = document.querySelectorAll('.admin-module-btn');
    var titleEl = document.getElementById('adminModuleDescriptionTitle');
    var textEl = document.getElementById('adminModuleDescriptionText');

    moduleButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            moduleButtons.forEach(function (item) {
                item.classList.remove('active');
            });

            button.classList.add('active');

            if (titleEl) {
                titleEl.textContent = button.getAttribute('data-module-title') || 'Module';
            }

            if (textEl) {
                textEl.textContent = button.getAttribute('data-module-description') || '';
            }
        });
    });
});
