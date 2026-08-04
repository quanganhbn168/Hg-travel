(() => {
    const activateTabs = (selector, panelSelector, activeClass = 'is-active') => {
        const tabs = [...document.querySelectorAll(selector)];
        if (!tabs.length) return;

        const activate = (key) => {
            tabs.forEach((tab) => {
                const active = tab.dataset.homeTab === key || tab.dataset.destinationTab === key;
                tab.classList.toggle(activeClass, active);
                tab.setAttribute('aria-selected', active ? 'true' : 'false');
            });

            document.querySelectorAll(panelSelector).forEach((panel) => {
                panel.classList.toggle(activeClass, panel.dataset.homePanel === key);
            });

            if (panelSelector === '[data-destination-item]') {
                document.querySelectorAll(panelSelector).forEach((item) => {
                    item.hidden = key !== 'all' && item.dataset.destinationItem !== key;
                });

                const visibleItems = [...document.querySelectorAll(panelSelector)].filter((item) => !item.hidden);
                document.querySelectorAll('.destination-bento-item').forEach((item) => {
                    item.classList.toggle('is-bento-featured', item === visibleItems[0]);
                });
            }
        };

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                activate(tab.dataset.homeTab || tab.dataset.destinationTab);
            });
        });
    };

    const initHomeParallax = () => {
        const sections = [...document.querySelectorAll('[data-home-parallax]')];
        if (!sections.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        let frameRequested = false;
        const update = () => {
            frameRequested = false;

            sections.forEach((section) => {
                const backdrop = section.querySelector('.home-parallax-backdrop');
                if (!backdrop) return;

                const bounds = section.getBoundingClientRect();
                if (bounds.bottom < -160 || bounds.top > window.innerHeight + 160) return;

                const progress = (window.innerHeight - bounds.top) / (window.innerHeight + bounds.height);
                const distance = Number.parseFloat(section.dataset.parallaxDistance || '72');
                const offset = (progress - 0.5) * -distance;
                backdrop.style.transform = `translate3d(0, ${offset}px, 0) scale(1.1)`;
            });
        };
        const requestUpdate = () => {
            if (frameRequested) return;
            frameRequested = true;
            window.requestAnimationFrame(update);
        };

        window.addEventListener('scroll', requestUpdate, { passive: true });
        window.addEventListener('resize', requestUpdate);
        requestUpdate();
    };

    const initHomeAos = () => {
        if (!window.AOS) return;

        document.querySelectorAll('main > section:not([data-home-swiper])').forEach((section, index) => {
            if (!section.dataset.aos) {
                section.dataset.aos = 'fade-up';
                section.dataset.aosDelay = String(Math.min(index * 60, 240));
            }
        });

        window.AOS.init({
            once: true,
            offset: 80,
            duration: 650,
            easing: 'ease-out-cubic',
        });
    };

    const init = () => {
        activateTabs('[data-home-tab]', '[data-home-panel]');
        activateTabs('[data-destination-tab]', '[data-destination-item]');
        initHomeAos();
        initHomeParallax();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
})();
