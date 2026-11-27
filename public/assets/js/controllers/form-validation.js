(function () {
    "use strict";

    const translate = (text) =>
        window.makanTranslate?.(text) ?? text;

    function getFieldLabel(field) {
        const label = field.labels?.[0];

        if (label) {
            return label.textContent
                .replace(/\*/g, "")
                .replace(/\s+/g, " ")
                .trim();
        }

        return field.getAttribute("aria-label")
            || field.name
            || field.id;
    }

    function initialize() {
        const forms = document.querySelectorAll(
            "form[data-popup-validation]"
        );

        forms.forEach((form) => {
            let scheduled = false;

            form.addEventListener("invalid", function (event) {
                if (!window.MakanPopup) {
                    return;
                }

                event.preventDefault();

                if (scheduled) {
                    return;
                }

                scheduled = true;

                setTimeout(function () {
                    scheduled = false;

                    const invalidFields = Array.from(form.elements)
                        .filter((field) =>
                            field.willValidate &&
                            !field.validity.valid
                        );

                    if (!invalidFields.length) {
                        return;
                    }

                    const messages = invalidFields.map((field) => {
                        if (
                            field.id === "fDefaultPhoto" &&
                            field.validity.valueMissing
                        ) {
                            return translate(
                                "Please upload a default property photo."
                            );
                        }

                        return getFieldLabel(field);
                    });

                    const uniqueMessages = [...new Set(messages)];

                    MakanPopup.error(
                        translate(
                            "Please complete or correct the following fields:"
                        ) +
                        "\n\n" +
                        uniqueMessages
                            .map((message) => "• " + message)
                            .join("\n")
                    );

                }, 0);

            }, true);
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initialize);
    } else {
        initialize();
    }
})();