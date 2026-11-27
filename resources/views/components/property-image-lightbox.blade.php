<div
    class="modal fade property-lightbox"
    id="propertyImageLightbox"
    tabindex="-1"
    aria-labelledby="propertyLightboxTitle"
    aria-hidden="true"
>
    <div class="modal-dialog modal-fullscreen">

        <div class="modal-content">

            <!-- Header -->
            <div class="property-lightbox-header">

                <h2
                    id="propertyLightboxTitle"
                    class="property-lightbox-title"
                >
                    {{ __('Property photos') }}
                </h2>

                <span
                    id="propertyLightboxCounter"
                    class="property-lightbox-counter"
                    aria-live="polite"
                    dir="ltr"
                ></span>

                <button
                    type="button"
                    class="property-lightbox-close"
                    data-bs-dismiss="modal"
                    aria-label="{{ __('Close') }}"
                >
                    <i class="bi bi-x-lg"></i>
                </button>

            </div>

            <!-- Main image -->
            <div class="property-lightbox-stage">

                <button
                    type="button"
                    class="property-lightbox-arrow property-lightbox-prev"
                    id="propertyLightboxPrev"
                    aria-label="{{ __('Previous photo') }}"
                    hidden
                >
                    <i class="bi bi-chevron-left"></i>
                </button>

                <img
                    id="propertyLightboxImage"
                    class="property-lightbox-image"
                    alt=""
                    draggable="false"
                >

                <button
                    type="button"
                    class="property-lightbox-arrow property-lightbox-next"
                    id="propertyLightboxNext"
                    aria-label="{{ __('Next photo') }}"
                    hidden
                >
                    <i class="bi bi-chevron-right"></i>
                </button>

            </div>

            <!-- Thumbnail navigation -->
            <div
                id="propertyLightboxThumbnails"
                class="property-lightbox-thumbnails"
                role="group"
                aria-label="{{ __('Property photos') }}"
            ></div>

        </div>

    </div>
</div>