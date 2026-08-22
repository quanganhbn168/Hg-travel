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

    const initSchedulePickers = () => {
        document.querySelectorAll('[data-tour-schedule-picker]').forEach((picker) => {
            const options = [...picker.querySelectorAll('[data-schedule-option]')];
            const cta = picker.querySelector('[data-schedule-booking-cta]');
            const selectedLabel = picker.querySelector('[data-schedule-selected-label]');
            const bookingForm = document.querySelector('[data-tour-booking-form]');
            const bookingScheduleInput = bookingForm?.querySelector('[data-tour-booking-schedule]');
            const bookingScheduleSummary = bookingForm?.querySelector('[data-tour-booking-schedule-summary]');
            const bookingScheduleLabel = bookingForm?.querySelector('[data-tour-booking-schedule-label]');

            if (!options.length) return;

            const selectSchedule = (option) => {
                options.forEach((scheduleOption) => {
                    scheduleOption.closest('[data-schedule-row]')?.classList.toggle('is-selected', scheduleOption === option);
                });

                if (cta) {
                    cta.removeAttribute('aria-disabled');
                }

                if (selectedLabel && option.dataset.scheduleLabel) {
                    selectedLabel.innerHTML = `<i class="bi bi-check-circle"></i> Đã chọn ${option.dataset.scheduleLabel}`;
                }

                if (bookingScheduleInput) bookingScheduleInput.value = option.value;
                if (bookingScheduleLabel && option.dataset.scheduleLabel) bookingScheduleLabel.textContent = `Đã chọn ${option.dataset.scheduleLabel}`;
                if (bookingScheduleSummary) {
                    bookingScheduleSummary.classList.remove('is-empty');
                    bookingScheduleSummary.querySelector('i')?.classList.replace('bi-exclamation-circle', 'bi-calendar2-check');
                }
            };

            options.forEach((option) => option.addEventListener('change', () => selectSchedule(option)));
            const initialOption = options.find((option) => option.checked && !option.disabled)
                || options.find((option) => !option.disabled);
            if (initialOption) selectSchedule(initialOption);
        });
    };

    const initTourBookingModal = () => {
        const modalElement = document.querySelector('#tour-booking-modal[data-open-on-load]');

        if (modalElement && window.bootstrap?.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initTourGalleries();
            initSchedulePickers();
            initTourBookingModal();
        }, { once: true });
    } else {
        initTourGalleries();
        initSchedulePickers();
        initTourBookingModal();
    }
})();
