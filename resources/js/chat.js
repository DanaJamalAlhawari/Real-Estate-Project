const chatForm = document.querySelector("[data-chat-form]");

if (chatForm) {
    const input = chatForm.elements.namedItem("body");
    const sendButton = chatForm.querySelector('button[type="submit"]');
    const messages = chatForm
        .closest(".chat-thread")
        .querySelector(".chat-body");

    const status = document.createElement("p");
    status.className = "small text-danger px-3 mb-0";
    status.setAttribute("role", "status");
    chatForm.before(status);
    chatForm.before(status);

    function appendMessage(message) {
        const id = String(message.id);

        const alreadyShown = [
            ...messages.querySelectorAll("[data-message-id]"),
        ].some((bubble) => bubble.dataset.messageId === id);

        if (alreadyShown) return;

        const bubble = document.createElement("div");
        bubble.classList.add("msg");

        if (String(message.sender_id) === chatForm.dataset.currentUserId) {
            bubble.classList.add("me");
        }

        bubble.dataset.messageId = id;
        bubble.textContent = message.body;

        messages.append(bubble);
        messages.scrollTop = messages.scrollHeight;
    }
    const conversationId = chatForm.dataset.conversationId;

    window.Echo.private(`conversations.${conversationId}`).listen(
        "MessageSent",
        appendMessage,
    );

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
                throw new Error(
                    error.errors?.body?.[0] ?? "Could not send the message.",
                );
            }

            const message = await response.json();
            appendMessage(message);
            if (input.value.trim() === body) input.value = "";
        } catch (error) {
            status.textContent = error.message;
        } finally {
            sendButton.disabled = false;
        }
    });
}
