(() => {
    const widget = document.querySelector("[data-makan-assistant]");
    if (!widget) return;

    const launcher = widget.querySelector("[data-assistant-toggle]");
    const panel = widget.querySelector("#makan-assistant-panel");
    const close = widget.querySelector("[data-assistant-close]");
    const log = widget.querySelector("[data-assistant-messages]");
    const status = widget.querySelector("[data-assistant-status]");
    const form = widget.querySelector("[data-assistant-form]");
    const input = form.elements.message;
    const send = form.querySelector("button[type=submit]");

    function setOpen(open) {
        panel.hidden = !open;
        launcher.setAttribute("aria-expanded", String(open));
        launcher.setAttribute("aria-label", open ? window.makanTranslate("Close Makan Assistant") : window.makanTranslate("Open Makan Assistant"));
        const focusTarget = open ? input : launcher;
        focusTarget.focus({ preventScroll: true });
        if (open) log.scrollTop = log.scrollHeight;
    }

    function showMessage(content, fromUser = false) {
        const message = document.createElement("p");
        message.className = "makan-assistant__message";
        if (fromUser) message.classList.add("makan-assistant__message--user");
        message.textContent = content;
        log.append(message);
        log.scrollTop = log.scrollHeight;
        return message;
    }

    launcher.addEventListener("click", () => setOpen(panel.hidden));
    close.addEventListener("click", () => setOpen(false));
    widget.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && !panel.hidden) setOpen(false);
    });

    form.addEventListener("submit", async (event) => {
        event.preventDefault();
        const message = input.value.trim();
        if (!message || send.disabled) return;

        const userMessage = showMessage(message, true);
        input.value = "";
        input.readOnly = true;
        send.disabled = true;
        status.hidden = false;
        status.textContent = window.makanTranslate("Makan Assistant is replying…");

        try {
            const response = await fetch(form.action, {
                method: "POST",
                headers: {
                    "Accept": "application/json",
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": form.elements._token.value,
                },
                body: JSON.stringify({ message }),
            });
            const data = await response.json();
            if (!response.ok || typeof data.reply !== "string") {
                status.textContent = response.status === 429
                    ? window.makanTranslate("Too many messages. Wait a minute and try again.")
                    : response.status === 401 || response.status === 419
                        ? window.makanTranslate("Your session expired. Refresh the page and sign in again.")
                        : data.message || window.makanTranslate("The assistant is temporarily unavailable.");
                userMessage.remove();
                input.value = message;
                return;
            }

            showMessage(data.reply);
            status.hidden = true;
        } catch {
            userMessage.remove();
            input.value = message;
            status.textContent = window.makanTranslate("The assistant could not connect. Try again.");
        } finally {
            input.readOnly = false;
            send.disabled = false;
            if (!panel.hidden && document.activeElement === document.body) input.focus();
        }
    });
})();
