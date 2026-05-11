document.addEventListener('DOMContentLoaded', function () {
    // Highlight active nav link based on current URL
    var currentUrl = window.location.href;
    var navLinks = document.querySelectorAll('.admin-nav-link');
    navLinks.forEach(function(link) {
        if (link.href && currentUrl.indexOf(link.getAttribute('href')) !== -1) {
            link.classList.add('active');
        }
    });
});
