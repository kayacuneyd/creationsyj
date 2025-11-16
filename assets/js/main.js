document.addEventListener('DOMContentLoaded', function () {
    // Basic mobile nav toggle (if needed later)
    var navToggle = document.querySelector('[data-nav-toggle]');
    var navMenu = document.querySelector('[data-nav-menu]');

    if (navToggle && navMenu) {
        var navLinks = navMenu.querySelectorAll('a');

        function closeNav() {
            navMenu.classList.remove('is-open');
            navToggle.classList.remove('active');
            document.body.classList.remove('nav-open');
        }

        navToggle.addEventListener('click', function () {
            var isOpen = navMenu.classList.toggle('is-open');
            navToggle.classList.toggle('active', isOpen);
            document.body.classList.toggle('nav-open', isOpen);
        });

        navLinks.forEach(function (link) {
            link.addEventListener('click', closeNav);
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 768 && navMenu.classList.contains('is-open')) {
                closeNav();
            }
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

    // Lightbox Gallery
    var lightbox = document.querySelector('.lightbox');
    var lightboxImage = document.querySelector('.lightbox-image');
    var lightboxCounter = document.querySelector('.lightbox-counter');
    var galleryImages = document.querySelectorAll('[data-gallery-image]');
    var currentImageIndex = 0;
    var imageSources = [];
    var imageSourcesWebp = [];

    if (lightbox && galleryImages.length > 0) {
        // Collect all image sources (JPEG and WebP)
        galleryImages.forEach(function (img) {
            var jpegSrc = img.getAttribute('data-full-image') || img.src;
            imageSources.push(jpegSrc);
            
            // Try to find WebP version
            var webpSrc = jpegSrc.replace(/\.(jpg|jpeg|png)$/i, '.webp');
            // Check if it's in a subdirectory and adjust path
            if (jpegSrc.indexOf('/large/') !== -1) {
                webpSrc = jpegSrc.replace('/large/', '/large/webp/').replace(/\.(jpg|jpeg|png)$/i, '.webp');
            }
            imageSourcesWebp.push(webpSrc);
        });

        // Open lightbox on image click
        galleryImages.forEach(function (img, index) {
            img.addEventListener('click', function () {
                currentImageIndex = index;
                openLightbox();
            });
        });

        function openLightbox() {
            if (lightboxImage && imageSources[currentImageIndex]) {
                // Use WebP if available, fallback to JPEG
                var webpSrc = imageSourcesWebp[currentImageIndex];
                var jpegSrc = imageSources[currentImageIndex];
                
                var picture = lightboxImage.parentElement;
                if (picture && picture.tagName === 'PICTURE') {
                    var source = picture.querySelector('.lightbox-source');
                    if (source) {
                        source.srcset = webpSrc;
                    }
                }
                lightboxImage.src = jpegSrc;
                
                updateCounter();
                lightbox.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeLightbox() {
            lightbox.classList.remove('active');
            document.body.style.overflow = '';
        }

        function updateCounter() {
            if (lightboxCounter && imageSources.length > 1) {
                lightboxCounter.textContent = (currentImageIndex + 1) + ' / ' + imageSources.length;
            } else if (lightboxCounter) {
                lightboxCounter.textContent = '';
            }
        }

        function showNext() {
            if (currentImageIndex < imageSources.length - 1) {
                currentImageIndex++;
            } else {
                currentImageIndex = 0;
            }
            if (lightboxImage) {
                var webpSrc = imageSourcesWebp[currentImageIndex];
                var jpegSrc = imageSources[currentImageIndex];
                var picture = lightboxImage.parentElement;
                if (picture && picture.tagName === 'PICTURE') {
                    var source = picture.querySelector('.lightbox-source');
                    if (source) {
                        source.srcset = webpSrc;
                    }
                }
                lightboxImage.src = jpegSrc;
            }
            updateCounter();
        }

        function showPrev() {
            if (currentImageIndex > 0) {
                currentImageIndex--;
            } else {
                currentImageIndex = imageSources.length - 1;
            }
            if (lightboxImage) {
                var webpSrc = imageSourcesWebp[currentImageIndex];
                var jpegSrc = imageSources[currentImageIndex];
                var picture = lightboxImage.parentElement;
                if (picture && picture.tagName === 'PICTURE') {
                    var source = picture.querySelector('.lightbox-source');
                    if (source) {
                        source.srcset = webpSrc;
                    }
                }
                lightboxImage.src = jpegSrc;
            }
            updateCounter();
        }

        // Close button
        var closeBtn = document.querySelector('.lightbox-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', closeLightbox);
        }

        // Next/Prev buttons
        var nextBtn = document.querySelector('.lightbox-next');
        var prevBtn = document.querySelector('.lightbox-prev');
        if (nextBtn) {
            nextBtn.addEventListener('click', showNext);
        }
        if (prevBtn) {
            prevBtn.addEventListener('click', showPrev);
        }

        // Close on background click
        lightbox.addEventListener('click', function (e) {
            if (e.target === lightbox) {
                closeLightbox();
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', function (e) {
            if (lightbox.classList.contains('active')) {
                if (e.key === 'Escape') {
                    closeLightbox();
                } else if (e.key === 'ArrowRight') {
                    showNext();
                } else if (e.key === 'ArrowLeft') {
                    showPrev();
                }
            }
        });
    }
});

