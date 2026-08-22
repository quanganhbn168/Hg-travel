(() => {
    const initHomeGallery = () => {
        if (typeof window.GLightbox !== 'function' || !document.querySelector('.glightbox')) {
            return;
        }

        window.hgHomeGallery?.destroy?.();
        window.hgHomeGallery = window.GLightbox({
            selector: '.glightbox',
            touchNavigation: true,
            loop: true,
            zoomable: true,
            openEffect: 'zoom',
            closeEffect: 'fade',
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHomeGallery, { once: true });
    } else {
        initHomeGallery();
    }
})();
