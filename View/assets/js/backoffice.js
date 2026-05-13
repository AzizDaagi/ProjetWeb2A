document.addEventListener('DOMContentLoaded', function () {
    var toggleButton = document.getElementById('themeToggle');
    
    // Restore theme preference from localStorage
    var savedTheme = localStorage.getItem('admin-theme-preference');
    if (savedTheme === 'light') {
        document.body.classList.remove('theme-dark');
        document.body.classList.add('theme-light');
        if (toggleButton) {
            toggleButton.innerHTML = '<i class="fa-solid fa-sun"></i>';
        }
    } else if (savedTheme === 'dark') {
        document.body.classList.remove('theme-light');
        document.body.classList.add('theme-dark');
        if (toggleButton) {
            toggleButton.innerHTML = '<i class="fa-solid fa-moon"></i>';
        }
    }
    
    if (toggleButton) {
        toggleButton.addEventListener('click', function () {
            document.body.classList.toggle('theme-light');
            document.body.classList.toggle('theme-dark');
            var isLight = document.body.classList.contains('theme-light');
            localStorage.setItem('admin-theme-preference', isLight ? 'light' : 'dark');
            toggleButton.innerHTML = isLight
                ? '<i class="fa-solid fa-sun"></i>'
                : '<i class="fa-solid fa-moon"></i>';
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

    // Search functionality
    var searchInput = document.querySelector('.admin-search-wrap input');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var searchTerm = searchInput.value.toLowerCase().trim();
            
            // Search in tables (products, pending, orders)
            var tableRows = document.querySelectorAll('.users-table tbody tr');
            tableRows.forEach(function (row) {
                var text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
            
            // Search in order cards
            var orderCards = document.querySelectorAll('.order-list-card');
            orderCards.forEach(function (card) {
                var text = card.textContent.toLowerCase();
                card.style.display = text.includes(searchTerm) ? '' : 'none';
            });

            // Show/hide empty message for tables
            var tables = document.querySelectorAll('.users-table');
            tables.forEach(function (table) {
                var visibleRows = table.querySelectorAll('tbody tr[style="display: "]');
                var hiddenRows = table.querySelectorAll('tbody tr[style="display: none"]');
                var allRows = table.querySelectorAll('tbody tr');
                
                // If all rows are hidden and there are rows, show a "no results" message
                if (allRows.length > 0 && hiddenRows.length === allRows.length && searchTerm !== '') {
                    var tbody = table.querySelector('tbody');
                    if (!tbody.querySelector('.search-no-results')) {
                        var emptyRow = document.createElement('tr');
                        emptyRow.className = 'search-no-results';
                        emptyRow.innerHTML = '<td colspan="100%" style="text-align: center; padding: 20px; color: var(--admin-muted);">No results found for "' + searchInput.value.replace(/"/g, '&quot;') + '"</td>';
                        tbody.appendChild(emptyRow);
                    }
                } else {
                    var noResultsRow = table.querySelector('tbody .search-no-results');
                    if (noResultsRow) {
                        noResultsRow.remove();
                    }
                }
            });

            // Show/hide empty message for orders
            var orderStacks = document.querySelectorAll('.order-list-stack');
            orderStacks.forEach(function (stack) {
                var visibleCards = stack.querySelectorAll('.order-list-card:not([style="display: none"])');
                var hiddenCards = stack.querySelectorAll('.order-list-card[style="display: none"]');
                var allCards = stack.querySelectorAll('.order-list-card');
                
                if (allCards.length > 0 && hiddenCards.length === allCards.length && searchTerm !== '') {
                    if (!stack.querySelector('.search-no-results')) {
                        var noResultsDiv = document.createElement('div');
                        noResultsDiv.className = 'search-no-results';
                        noResultsDiv.style.cssText = 'text-align: center; padding: 40px 20px; color: var(--admin-muted);';
                        noResultsDiv.innerHTML = 'No orders found for "' + searchInput.value.replace(/"/g, '&quot;') + '"';
                        stack.appendChild(noResultsDiv);
                    }
                } else {
                    var noResultsDiv = stack.querySelector('.search-no-results');
                    if (noResultsDiv) {
                        noResultsDiv.remove();
                    }
                }
            });
        });
    }
});
