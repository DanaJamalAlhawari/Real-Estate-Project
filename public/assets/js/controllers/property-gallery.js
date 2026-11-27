(function () {
    "use strict";

    function initialize() {
        const lightbox = document.getElementById(
            "propertyImageLightbox"
        );

        if (!lightbox || !window.bootstrap?.Modal) {
            return;
        }

        const modal = bootstrap.Modal.getOrCreateInstance(lightbox);

        const mainImage = document.getElementById("propertyLightboxImage");
        const title = document.getElementById("propertyLightboxTitle");
        const counter = document.getElementById("propertyLightboxCounter");
        const thumbnails = document.getElementById("propertyLightboxThumbnails");

        const prevButton = document.getElementById("propertyLightboxPrev");
        const nextButton = document.getElementById("propertyLightboxNext");
        const closeButton = lightbox.querySelector(".property-lightbox-close");

        const translate = text =>
            window.makanTranslate?.(text) ?? text;

        let activeImages = [];
        let activeIndex = 0;
        let activeTrigger = null;

        function normalizeIndex(index, length) {
            return length
                ? ((index % length) + length) % length
                : 0;
        }

        function isRTL() {
            return document.documentElement.dir === "rtl";
        }

        function getDirection(key) {
            if (key === "ArrowRight") {
                return isRTL() ? -1 : 1;
            }

            if (key === "ArrowLeft") {
                return isRTL() ? 1 : -1;
            }

            return 0;
        }

        // ==============================
        // Card carousel
        // ==============================

        function getSlides(carousel) {
            return Array.from(
                carousel.querySelectorAll(".property-carousel-slide")
            );
        }

        function showCarouselSlide(carousel, index) {
            const slides = getSlides(carousel);

            if (!slides.length) return;

            index = normalizeIndex(index, slides.length);

            carousel.dataset.currentIndex = String(index);

            slides.forEach((slide, i) => {
                slide.hidden = i !== index;
            });

            carousel.querySelectorAll("[data-carousel-dot]")
                .forEach(dot => {
                    const active =
                        Number(dot.dataset.carouselDot) === index;

                    dot.classList.toggle("is-active", active);

                    dot.setAttribute(
                        "aria-current",
                        active ? "true" : "false"
                    );
                });

            const carouselCounter = carousel.querySelector(
                "[data-carousel-counter]"
            );

            if (carouselCounter) {
                carouselCounter.textContent =
                    `${index + 1} / ${slides.length}`;
            }
        }

        function moveCarousel(carousel, direction) {
            const current = Number(
                carousel.dataset.currentIndex || 0
            );

            showCarouselSlide(
                carousel,
                current + direction
            );
        }

        // ==============================
        // Lightbox
        // ==============================

        function renderThumbnails() {
            thumbnails.replaceChildren();

            thumbnails.hidden = activeImages.length <= 1;

            if (activeImages.length <= 1) return;

            activeImages.forEach((image, index) => {
                const button = document.createElement("button");

                button.type = "button";
                button.className = "property-lightbox-thumbnail";

                button.setAttribute(
                    "aria-label",
                    `${translate("View photo")} ${index + 1}`
                );

                const img = document.createElement("img");

                img.src = image.src;
                img.alt = image.alt;
                img.loading = "lazy";
                img.draggable = false;

                button.appendChild(img);

                button.addEventListener("click", () => {
                    showLightboxImage(index);
                });

                thumbnails.appendChild(button);
            });
        }

        function showLightboxImage(index) {
            if (!activeImages.length) return;

            activeIndex = normalizeIndex(
                index,
                activeImages.length
            );

            const image = activeImages[activeIndex];

            mainImage.src = image.src;
            mainImage.alt = image.alt;

            counter.textContent =
                `${activeIndex + 1} / ${activeImages.length}`;

            prevButton.hidden = activeImages.length <= 1;
            nextButton.hidden = activeImages.length <= 1;

            Array.from(thumbnails.children)
                .forEach((button, i) => {
                    const active = i === activeIndex;

                    button.classList.toggle("is-active", active);

                    button.setAttribute(
                        "aria-current",
                        active ? "true" : "false"
                    );
                });

            thumbnails.children[activeIndex]?.scrollIntoView({
                block: "nearest",
                inline: "nearest",
                behavior: "instant"
            });
        }

        function openLightbox(trigger) {
            const gallery = trigger.closest(
                "[data-property-carousel], [data-property-gallery]"
            );

            if (!gallery) return;

            activeImages = Array.from(
                gallery.querySelectorAll("[data-gallery-open]")
            ).map(item => {
                const img = item.querySelector("img");

                return {
                    src: img?.getAttribute("src"),
                    alt: img?.alt || ""
                };
            });

            if (!activeImages.length ||
                activeImages.some(image => !image.src)) {
                return;
            }

            activeTrigger = trigger;

            const index = Number(
                trigger.dataset.slideIndex || 0
            );

            title.textContent =
                gallery.dataset.galleryTitle ||
                translate("Property photos");

            renderThumbnails();
            showLightboxImage(index);

            modal.show();
        }

        // ==============================
        // Click events
        // ==============================

        document.addEventListener("click", event => {
            const previous = event.target.closest(
                "[data-carousel-prev]"
            );

            const next = event.target.closest(
                "[data-carousel-next]"
            );

            const dot = event.target.closest(
                "[data-carousel-dot]"
            );

            const trigger = event.target.closest(
                "[data-gallery-open]"
            );

            if (previous || next || dot) {
                const control = previous || next || dot;

                const carousel = control.closest(
                    "[data-property-carousel]"
                );

                if (!carousel) return;

                event.preventDefault();
                event.stopPropagation();

                if (previous) {
                    moveCarousel(carousel, -1);
                } else if (next) {
                    moveCarousel(carousel, 1);
                } else {
                    showCarouselSlide(
                        carousel,
                        Number(dot.dataset.carouselDot)
                    );
                }

                return;
            }

            if (trigger) {
                event.preventDefault();
                event.stopPropagation();

                openLightbox(trigger);
            }
        });

        prevButton.addEventListener("click", () => {
            showLightboxImage(activeIndex - 1);
        });

        nextButton.addEventListener("click", () => {
            showLightboxImage(activeIndex + 1);
        });

        // ==============================
        // Keyboard navigation
        // ==============================

        document.addEventListener("keydown", event => {
            const direction = getDirection(event.key);

            if (!direction) return;

            if (lightbox.classList.contains("show")) {
                event.preventDefault();

                showLightboxImage(activeIndex + direction);

                return;
            }

            const carousel = event.target.closest(
                "[data-property-carousel]"
            );

            if (carousel) {
                event.preventDefault();

                moveCarousel(carousel, direction);
            }
        });

        // ==============================
        // Touch navigation
        // ==============================

        let touchStart = null;
        let suppressClickUntil = 0;

        document.addEventListener("touchstart", event => {
            touchStart = null;

            if (event.touches.length !== 1) return;

            const surface = event.target.closest(
                ".property-carousel-track, .property-lightbox-image"
            );

            if (!surface) return;

            touchStart = {
                x: event.touches[0].clientX,
                y: event.touches[0].clientY,
                surface
            };
        }, { passive: true });

        document.addEventListener("touchcancel", () => {
            touchStart = null;
        }, { passive: true });

        document.addEventListener("touchend", event => {
            if (!touchStart) return;

            const touch = event.changedTouches[0];

            const dx = touch.clientX - touchStart.x;
            const dy = touch.clientY - touchStart.y;

            const surface = touchStart.surface;

            touchStart = null;

            if (Math.abs(dx) < 50 ||
                Math.abs(dx) <= Math.abs(dy)) {
                return;
            }

            let direction = dx < 0 ? 1 : -1;

            if (isRTL()) {
                direction *= -1;
            }

            if (surface === mainImage) {
                showLightboxImage(activeIndex + direction);
            } else {
                const carousel = surface.closest(
                    "[data-property-carousel]"
                );

                if (carousel) {
                    moveCarousel(carousel, direction);
                }
            }

            suppressClickUntil = Date.now() + 400;
        }, { passive: true });

        document.addEventListener("click", event => {
            if (
                Date.now() < suppressClickUntil &&
                event.target.closest("[data-gallery-open]")
            ) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        }, true);

        // ==============================
        // Modal lifecycle
        // ==============================

        lightbox.addEventListener("shown.bs.modal", () => {
            closeButton?.focus();
        });

        lightbox.addEventListener("hide.bs.modal", () => {
            const focused = document.activeElement;

            if (lightbox.contains(focused)) {
                focused.blur();
            }
        });

        lightbox.addEventListener("hidden.bs.modal", () => {
            mainImage.removeAttribute("src");
            mainImage.alt = "";

            thumbnails.replaceChildren();
            counter.textContent = "";

            activeImages = [];
            activeIndex = 0;

            if (activeTrigger?.isConnected) {
                activeTrigger.focus({
                    preventScroll: true
                });
            }

            activeTrigger = null;
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener(
            "DOMContentLoaded",
            initialize
        );
    } else {
        initialize();
    }
})();