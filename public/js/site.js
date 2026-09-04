(() => {
    const header = document.querySelector('.site-header');
    const scrollTop = document.querySelector('.scroll-top');
    const searchPanel = document.querySelector('#site-search-panel');
    const searchInput = document.querySelector('#site-search-input');
    const searchToggles = [...document.querySelectorAll('[data-search-toggle]')];
    const searchClose = document.querySelector('[data-search-close]');
    const bookingTriggers = [...document.querySelectorAll('[data-tour-booking-trigger]')];
    const cardBookingModal = document.querySelector('#tour-card-booking-modal');
    const cardBookingTitle = cardBookingModal?.querySelector('[data-tour-booking-title]');
    const cardBookingTourId = cardBookingModal?.querySelector('[data-tour-booking-tour-id]');

    const setSearchOpen = (open) => {
        if (!searchPanel) return;

        searchPanel.hidden = !open;
        searchToggles.forEach((toggle) => toggle.setAttribute('aria-expanded', open ? 'true' : 'false'));

        if (open) {
            searchInput?.focus();
        }
    };

    const updateScrollState = () => {
        header?.classList.toggle('is-scrolled', window.scrollY > 12);
        scrollTop?.classList.toggle('is-visible', window.scrollY > 360);
    };

    window.addEventListener('scroll', updateScrollState, { passive: true });
    scrollTop?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    searchToggles.forEach((toggle) => toggle.addEventListener('click', () => {
        const mobileNavigation = document.getElementById('site-mobile-navigation');
        if (mobileNavigation && window.bootstrap) {
            bootstrap.Offcanvas.getInstance(mobileNavigation)?.hide();
        }

        setSearchOpen(searchPanel?.hidden ?? false);
    }));
    searchClose?.addEventListener('click', () => setSearchOpen(false));
    bookingTriggers.forEach((trigger) => trigger.addEventListener('click', () => {
        if (cardBookingTitle) cardBookingTitle.textContent = trigger.dataset.tourName || 'Đặt tour';
        if (cardBookingTourId) cardBookingTourId.value = trigger.dataset.tourId || '';
    }));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setSearchOpen(false);
    });

    if (window.bootstrap) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => {
            new bootstrap.Tooltip(element);
        });

        const mobileNavigation = document.getElementById('site-mobile-navigation');
        mobileNavigation?.querySelectorAll('a:not(.dropdown-toggle)').forEach((link) => {
            link.addEventListener('click', () => {
                bootstrap.Offcanvas.getInstance(mobileNavigation)?.hide();
            });
        });
    }

    updateScrollState();
})();
