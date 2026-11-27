(function () {
    "use strict";

    const translate = (text) => window.makanTranslate?.(text) ?? text;

    const queue = [];

    let modal = null;
    let active = false;
    let current = null;
    let previousFocus = null;

    const types = {
        error: {
            title: "Error",
            icon: "bi bi-x-circle-fill text-danger",
            button: "btn-danger",
        },

        success: {
            title: "Success",
            icon: "bi bi-check-circle-fill text-success",
            button: "btn-success",
        },

        warning: {
            title: "Warning",
            icon: "bi bi-exclamation-triangle-fill text-warning",
            button: "btn-warning",
        },

        info: {
            title: "Information",
            icon: "bi bi-info-circle-fill text-primary",
            button: "btn-primary",
        },

        confirm: {
            title: "Confirm action",
            icon: "bi bi-question-circle-fill text-warning",
            button: "btn-danger",
        },
    };

    function showNext() {
        if (active || queue.length === 0 || !modal) {
            return;
        }

        current = queue.shift();
        active = true;

        const config = types[current.type];

        document.getElementById("makanFeedbackTitle").textContent = translate(
            current.title || config.title,
        );

        document.getElementById("makanFeedbackMessage").textContent =
            current.message;

        document.getElementById("makanFeedbackIcon").className = config.icon;

        const confirmButton = document.getElementById("makanFeedbackConfirm");

        const cancelButton = document.getElementById("makanFeedbackCancel");

        confirmButton.className = `btn ${config.button} px-4`;

        confirmButton.textContent = translate(
            current.type === "confirm" ? "Confirm" : "OK",
        );

        cancelButton.hidden = current.type !== "confirm";

        modal.show();
    }

    function enqueue(type, message, title = null, callback = null) {
        message = String(message);

        if (type !== "confirm") {
            const isDuplicate = (item) =>
                item &&
                item.type === type &&
                item.message === message &&
                item.title === title;

            if (isDuplicate(current) || queue.some(isDuplicate)) {
                return;
            }
        }

        queue.push({
            type,
            message,
            title,
            callback,
            confirmed: false,
        });

        showNext();
    }

    window.MakanPopup = {
        error(message, title = null) {
            enqueue("error", message, title);
        },

        success(message, title = null) {
            enqueue("success", message, title);
        },

        warning(message, title = null) {
            enqueue("warning", message, title);
        },

        info(message, title = null) {
            enqueue("info", message, title);
        },

        confirm(message, callback, title = null) {
            enqueue("confirm", message, title, callback);
        },
    };

    function initialize() {
        const element = document.getElementById("makanFeedbackModal");

        if (!element || !window.bootstrap?.Modal) {
            console.error("Makan Popup could not initialize.");
            return;
        }

        modal = bootstrap.Modal.getOrCreateInstance(element);
        element.addEventListener("show.bs.modal", function () {
            previousFocus = document.activeElement;
        });

        element.addEventListener("shown.bs.modal", function () {
            const buttonId =
                current?.type === "confirm"
                    ? "makanFeedbackCancel"
                    : "makanFeedbackConfirm";

            document.getElementById(buttonId)?.focus();
        });

        element.addEventListener("hide.bs.modal", function () {
            const focusedElement = document.activeElement;

            if (element.contains(focusedElement)) {
                focusedElement.blur();
            }
        });

        document
            .getElementById("makanFeedbackConfirm")
            .addEventListener("click", function () {
                if (current) {
                    current.confirmed = true;
                }

                modal.hide();
            });

        element.addEventListener("hidden.bs.modal", function () {
            const completed = current;

            current = null;
            active = false;
            if (
                previousFocus instanceof HTMLElement &&
                previousFocus.isConnected &&
                !element.contains(previousFocus)
            ) {
                previousFocus.focus({ preventScroll: true });
            }

            previousFocus = null;

            try {
                if (
                    completed?.confirmed &&
                    typeof completed.callback === "function"
                ) {
                    completed.callback();
                }
            } finally {
                showNext();
            }
        });

        showNext();
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initialize);
    } else {
        initialize();
    }
})();
