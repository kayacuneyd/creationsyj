document.addEventListener('DOMContentLoaded', function () {
    // Basic mobile nav toggle (if needed later)
    var navToggle = document.querySelector('[data-nav-toggle]');
    var navMenu = document.querySelector('[data-nav-menu]');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function () {
            navMenu.classList.toggle('is-open');
        });
    }

    // Simple client-side product search enhancement (optional)
    var searchInput = document.querySelector('[data-product-search]');
    var productCards = document.querySelectorAll('[data-product-card]');

    if (searchInput && productCards.length) {
        searchInput.addEventListener('input', function () {
            var term = searchInput.value.toLowerCase();
            productCards.forEach(function (card) {
                var title = card.getAttribute('data-title') || '';
                if (title.toLowerCase().indexOf(term) !== -1) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});


