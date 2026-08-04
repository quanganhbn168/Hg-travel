(() => {
    const initHomeSwipers = () => {
        if (typeof window.Swiper === 'undefined') return;

        document.querySelectorAll('[data-home-swiper]').forEach((slider) => {
            if (slider.swiper) return;

            const slideCount = Number(slider.dataset.slideCount || slider.querySelectorAll('.swiper-slide').length);
            const hasMultipleSlides = slideCount > 1;
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const previousButton = slider.querySelector('[data-swiper-prev]');
            const nextButton = slider.querySelector('[data-swiper-next]');
            const dots = [...slider.querySelectorAll('[data-swiper-dot]')];

            if (!hasMultipleSlides) {
                slider.classList.add('is-single');
                return;
            }

            const syncDots = (swiper) => {
                const activeIndex = swiper.realIndex;

                dots.forEach((dot, index) => {
                    const active = index === activeIndex;
                    dot.classList.toggle('is-active', active);
                    dot.setAttribute('aria-current', active ? 'true' : 'false');
                });
            };

            const swiper = new window.Swiper(slider, {
                slidesPerView: 1,
                spaceBetween: 0,
                loop: true,
                effect: 'fade',
                fadeEffect: { crossFade: true },
                speed: 700,
                grabCursor: true,
                watchOverflow: true,
                observer: true,
                observeParents: true,
                keyboard: { enabled: true },
                a11y: {
                    enabled: true,
                    prevSlideMessage: 'Slide trước',
                    nextSlideMessage: 'Slide sau',
                },
                autoplay: prefersReducedMotion ? false : {
                    delay: 6000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                navigation: {
                    prevEl: previousButton,
                    nextEl: nextButton,
                },
                on: {
                    init: syncDots,
                    slideChange: syncDots,
                },
            });

            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => swiper.slideToLoop(index));
            });
        });
    };

    const initPromoSwipers = () => {
        if (typeof window.Swiper === 'undefined') return;

        document.querySelectorAll('[data-promo-swiper]').forEach((slider) => {
            if (slider.swiper) return;

            const slideCount = slider.querySelectorAll('.swiper-slide').length;
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const sliderWrap = slider.closest('.home-promo-slider-wrap');
            const previousButton = sliderWrap?.querySelector('[data-promo-prev]');
            const nextButton = sliderWrap?.querySelector('[data-promo-next]');

            if (slideCount <= 1) {
                slider.classList.add('is-single');
                sliderWrap?.classList.add('is-single');
                return;
            }

            new window.Swiper(slider, {
                slidesPerView: 1.08,
                spaceBetween: 16,
                loop: slideCount > 3,
                speed: 650,
                grabCursor: true,
                watchOverflow: true,
                observer: true,
                observeParents: true,
                keyboard: { enabled: true },
                autoplay: prefersReducedMotion ? false : {
                    delay: 5200,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                navigation: {
                    prevEl: previousButton,
                    nextEl: nextButton,
                },
                a11y: {
                    enabled: true,
                    prevSlideMessage: 'Ưu đãi trước',
                    nextSlideMessage: 'Ưu đãi tiếp theo',
                },
                breakpoints: {
                    576: { slidesPerView: 2 },
                    992: { slidesPerView: 3 },
                },
            });
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initHomeSwipers();
            initPromoSwipers();
        }, { once: true });
    } else {
        initHomeSwipers();
        initPromoSwipers();
    }
})();
