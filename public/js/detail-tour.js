(() => {
    const initTourGalleries = () => {
        if (typeof window.Swiper === 'undefined') return;

        document.querySelectorAll('[data-tour-gallery]').forEach((gallery) => {
            const mainElement = gallery.querySelector('.tour-media-gallery-main');
            const thumbsElement = gallery.querySelector('.tour-media-gallery-thumbs');
            const previousButton = gallery.querySelector('.tour-gallery-control-prev');
            const nextButton = gallery.querySelector('.tour-gallery-control-next');

            if (!mainElement || mainElement.swiper) return;

            const thumbs = thumbsElement
                ? new window.Swiper(thumbsElement, {
                    slidesPerView: 'auto',
                    spaceBetween: 10,
                    watchSlidesProgress: true,
                    slideToClickedSlide: true,
                    freeMode: true,
                })
                : null;

            const options = {
                slidesPerView: 1,
                spaceBetween: 0,
                speed: 450,
                navigation: {
                    prevEl: previousButton,
                    nextEl: nextButton,
                },
            };

            if (thumbs) {
                options.thumbs = { swiper: thumbs };
            }

            new window.Swiper(mainElement, options);
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTourGalleries, { once: true });
    } else {
        initTourGalleries();
    }
})();
