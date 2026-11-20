const chatForm = document.querySelector("[data-chat-form]");

if (chatForm) {
    const input = chatForm.elements.namedItem("body");
    const sendButton = chatForm.querySelector('button[type="submit"]');
    const messages = chatForm.closest(".chat-thread").querySelector(".chat-body");

    const status = document.createElement("p");
    status.className = "small text-danger px-3 mb-0";
    status.setAttribute("role", "status");
    chatForm.before(status);

    chatForm.addEventListener("submit", async (event) => {
        event.preventDefault();

        const body = input.value.trim();
        if (!body || sendButton.disabled) return;

        const data = new FormData(chatForm);
        data.set("body", body);

        sendButton.disabled = true;
        status.textContent = "";

        try {
            const response = await fetch(chatForm.action, {
                method: "POST",
                headers: { Accept: "application/json" },
                body: data,
            });

            if (!response.ok) {
                const error = await response.json().catch(() => ({}));
                throw new Error(error.errors?.body?.[0] ?? "Could not send the message.");
            }

            const message = await response.json();
            const bubble = document.createElement("div");
            bubble.className = "msg me";
            bubble.dataset.messageId = message.id;
            bubble.textContent = message.body;

            messages.append(bubble);
            messages.scrollTop = messages.scrollHeight;

            if (input.value.trim() === body) input.value = "";
        } catch (error) {
            status.textContent = error.message;
        } finally {
            sendButton.disabled = false;
        }
    });
}