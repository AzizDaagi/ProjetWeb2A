document.addEventListener('DOMContentLoaded', function () {
    var toggleButton = document.getElementById('themeToggle');
    if (toggleButton) {
        toggleButton.addEventListener('click', function () {
            document.body.classList.toggle('theme-light');
            document.body.classList.toggle('theme-dark');
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
