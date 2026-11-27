<div class="modal fade"
    id="makanFeedbackModal"
    tabindex="-1"
    aria-labelledby="makanFeedbackTitle"
    aria-describedby="makanFeedbackMessage"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered"
        style="max-width: 420px;">

        <div class="modal-content rounded-4 shadow">

            <div class="modal-header border-0 px-4 py-3
                d-flex justify-content-between align-items-center">

                <h5 class="modal-title fw-bold mb-0"
                    id="makanFeedbackTitle">
                </h5>

                <button type="button"
                    class="btn-close m-0"
                    data-bs-dismiss="modal"
                    aria-label="{{ __('Close') }}">
                </button>

            </div>

            <div class="modal-body text-center px-4 py-3">

                <div class="mb-3">
                    <i id="makanFeedbackIcon"
                        class="bi bi-exclamation-circle text-danger"
                        style="font-size: 3rem; line-height: 1;"
                        aria-hidden="true">
                    </i>
                </div>

                <p id="makanFeedbackMessage"
                    class="mb-0"
                    style="white-space: pre-line;
                        overflow-wrap: anywhere;
                        font-size: 1rem;">
                </p>

            </div>

            <div class="modal-footer border-0
                justify-content-center gap-2 px-4 py-3">

                <button type="button"
                    id="makanFeedbackCancel"
                    class="btn btn-outline-secondary px-4"
                    data-bs-dismiss="modal"
                    hidden>
                    {{ __('Cancel') }}
                </button>

                <button type="button"
                    id="makanFeedbackConfirm"
                    class="btn btn-danger px-4">
                    {{ __('OK') }}
                </button>

            </div>

        </div>

    </div>

</div>