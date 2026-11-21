const chatForm = document.querySelector("[data-chat-form]");

if (chatForm) {
    const thread = chatForm.closest(".chat-thread");
    const messages = thread.querySelector(".chat-body");
    const input = chatForm.elements.namedItem("body");
    const replyInput = chatForm.elements.namedItem("reply_to_id");
    const replyPreview = thread.querySelector("[data-reply-preview]");
    const replyText = thread.querySelector("[data-reply-text]");
    const cancelReply = thread.querySelector("[data-cancel-reply]");
    const sendButton = chatForm.querySelector('button[type="submit"]');

    requestAnimationFrame(() => {
        messages.scrollTop = messages.scrollHeight;
    });

    const status = document.createElement("p");
    status.className = "small text-danger px-3 mb-0";
    status.setAttribute("role", "status");
    chatForm.before(status);

    function clearReply() {
        replyInput.value = "";
        replyText.textContent = "";
        replyPreview.hidden = true;
    }

    messages.addEventListener("click", (event) => {
        const button = event.target.closest("[data-reply-button]");
        if (!button) return;

        const row = button.closest("[data-message-id]");
        const messageText = row.querySelector("[data-message-text]");

        replyInput.value = row.dataset.messageId;
        replyText.textContent = messageText.textContent.trim();
        replyPreview.hidden = false;
        input.focus();
    });

    cancelReply.addEventListener("click", () => {
        clearReply();
        input.focus();
    });

    function appendMessage(message) {
        const id = String(message.id);
        const alreadyShown = [
            ...messages.querySelectorAll("[data-message-id]"),
        ].some((row) => row.dataset.messageId === id);

        if (alreadyShown) return;

        const isMine =
            String(message.sender_id) === chatForm.dataset.currentUserId;
        const bubble = document.createElement("div");
        bubble.className = "msg";
        bubble.classList.toggle("me", isMine);

        if (message.reply_to) {
            const quote = document.createElement("blockquote");
            quote.className = "small border-start border-2 ps-2 mb-1";
            quote.textContent = message.reply_to.body;
            bubble.append(quote);
        }

        const messageText = document.createElement("span");
        messageText.setAttribute("data-message-text", "");
        messageText.textContent = message.body;
        bubble.append(messageText);

        const replyButton = document.createElement("button");
        replyButton.type = "button";
        replyButton.className = "chat-reply-button";
        replyButton.setAttribute("data-reply-button", "");
        replyButton.setAttribute("aria-label", "Reply to message");
        replyButton.title = "Reply";

        const replyIcon = document.createElement("i");
        replyIcon.className = "bi bi-reply";
        replyIcon.setAttribute("aria-hidden", "true");
        replyButton.append(replyIcon);

        const row = document.createElement("div");
        row.className = "chat-message-row";
        row.classList.toggle("is-mine", isMine);
        row.dataset.messageId = id;
        const reactionButton = document.createElement("button");
        reactionButton.type = "button";
        reactionButton.className = "chat-reply-button";
        reactionButton.setAttribute("data-reaction-button", "");
        reactionButton.setAttribute("aria-label", "React to message");
        reactionButton.title = "React";

        const reactionIcon = document.createElement("i");
        reactionIcon.className = "bi bi-emoji-smile";
        reactionIcon.setAttribute("aria-hidden", "true");
        reactionButton.append(reactionIcon);

        row.append(bubble, replyButton, reactionButton);

        messages.append(row);
        messages.scrollTop = messages.scrollHeight;
    }
    const reactionPicker = document.createElement("dialog");
    reactionPicker.className = "chat-reaction-picker";
    reactionPicker.setAttribute("aria-label", "Choose a reaction");

    reactionPicker.innerHTML = `
    <form method="dialog" class="d-flex flex-wrap gap-1">
        <button class="btn btn-light" value="👍" aria-label="Thumbs up">👍</button>
        <button class="btn btn-light" value="❤️" aria-label="Heart">❤️</button>
        <button class="btn btn-light" value="😂" aria-label="Laugh">😂</button>
        <button class="btn btn-light" value="😮" aria-label="Surprised">😮</button>
        <button class="btn btn-light" value="😢" aria-label="Sad">😢</button>
        <button class="btn btn-light" value="🙏" aria-label="Folded hands">🙏</button>
        <button class="btn btn-outline-secondary" value="">Cancel</button>
    </form>
`;

    thread.append(reactionPicker);

    messages.addEventListener("click", (event) => {
        const button = event.target.closest("[data-reaction-button]");
        if (!button) return;

        const row = button.closest("[data-message-id]");
        reactionPicker.dataset.messageId = row.dataset.messageId;
        reactionPicker.returnValue = "";
        if (!reactionPicker.open) reactionPicker.show();

        const bubble = row.querySelector(".msg").getBoundingClientRect();
        const { width, height } = reactionPicker.getBoundingClientRect();
        const isMine = row.classList.contains("is-mine");

        const left = isMine ? bubble.right - width : bubble.left;
        const below = bubble.bottom + 8;
        const top =
            below + height <= window.innerHeight - 8
                ? below
                : Math.max(8, bubble.top - height - 8);

        reactionPicker.style.left = `${Math.max(
            8,
            Math.min(left, window.innerWidth - width - 8),
        )}px`;
        reactionPicker.style.top = `${top}px`;
    });
    document.addEventListener("click", (event) => {
        if (
            reactionPicker.open &&
            !reactionPicker.contains(event.target) &&
            !event.target.closest("[data-reaction-button]")
        ) {
            reactionPicker.close("");
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && reactionPicker.open) {
            reactionPicker.close("");
        }
    });

    messages.addEventListener("scroll", () => {
        if (reactionPicker.open) {
            reactionPicker.close("");
        }
    });
    function updateReaction({ message_id, user_id, emoji }) {
        const row = messages.querySelector(`[data-message-id="${message_id}"]`);
        if (!row) return;

        let reactions = row.querySelector("[data-message-reactions]");

        if (!reactions) {
            if (emoji === null) return;

            reactions = document.createElement("div");
            reactions.className = "d-flex flex-wrap gap-1";
            reactions.setAttribute("data-message-reactions", "");
            row.querySelector(".msg").append(reactions);
        }

        const existing = reactions.querySelector(
            `[data-reaction-user="${user_id}"]`,
        );

        if (emoji === null) {
            existing?.remove();
            return;
        }

        const item = existing ?? document.createElement("span");
        item.dataset.reactionUser = String(user_id);
        item.textContent = emoji;

        if (!existing) reactions.append(item);
    }
    reactionPicker.addEventListener("close", async () => {
        const emoji = reactionPicker.returnValue;
        const messageId = reactionPicker.dataset.messageId;
        if (!emoji || !messageId) return;

        const url = chatForm.dataset.reactionUrl.replace(
            "__MESSAGE_ID__",
            messageId,
        );

        const data = new FormData();
        data.set("_token", chatForm.elements.namedItem("_token").value);
        data.set("emoji", emoji);

        try {
            const response = await fetch(url, {
                method: "POST",
                headers: { Accept: "application/json" },
                body: data,
            });

            if (!response.ok) {
                const error = await response.json().catch(() => ({}));
                throw new Error(
                    error.errors?.emoji?.[0] ??
                        `Could not save reaction (${response.status}).`,
                );
            }

            updateReaction(await response.json());
            status.textContent = "";
        } catch (error) {
            status.textContent = error.message;
        }
    });

    const conversationId = chatForm.dataset.conversationId;
    window.Echo.private(`conversations.${conversationId}`).listen(
        "MessageReactionChanged",
        updateReaction,
    );

    window.Echo.private(`conversations.${conversationId}`).listen(
        "MessageSent",
        async (message) => {
            appendMessage(message);

            const isMine =
                String(message.sender_id) === chatForm.dataset.currentUserId;
            if (isMine || document.visibilityState !== "visible") return;

            const data = new FormData();
            data.set("_token", chatForm.elements.namedItem("_token").value);
            data.set("message_id", String(message.id));

            try {
                const response = await fetch(chatForm.dataset.readUrl, {
                    method: "POST",
                    headers: { Accept: "application/json" },
                    body: data,
                });

                if (!response.ok) {
                    throw new Error(`Mark as read failed (${response.status})`);
                }
            } catch (error) {
                console.error(error);
            }
        },
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
                    error.errors?.body?.[0] ??
                        error.errors?.reply_to_id?.[0] ??
                        "Could not send the message.",
                );
            }

            const message = await response.json();
            appendMessage(message);

            if (replyInput.value === data.get("reply_to_id")) {
                clearReply();
            }

            if (input.value.trim() === body) {
                input.value = "";
            }
        } catch (error) {
            status.textContent = error.message;
        } finally {
            sendButton.disabled = false;
        }
    });
}
