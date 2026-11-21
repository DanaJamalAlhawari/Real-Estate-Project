const userId = document.querySelector('meta[name="makan-user-id"]')?.content;

function connectInbox() {
    if (!userId || !window.Echo || !window.bootstrap?.Toast) return;
    const container = document.createElement("div");
    container.className = "toast-container position-fixed top-0 end-0 p-3";
    container.setAttribute("aria-live", "polite");

    container.innerHTML = `
        <div class="toast" role="status" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">New message</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"
                        aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <p class="mb-2" data-inbox-preview></p>
                <a class="fw-semibold" data-inbox-link>Open conversation</a>
            </div>
        </div>
    `;

    document.body.append(container);

    const toast = window.bootstrap.Toast.getOrCreateInstance(
        container.querySelector(".toast"),
        { delay: 5000 },
    );

    const inbox = window.Echo.private(`inbox.${userId}`);
    const notify = (title, preview, url, linkLabel) => {
        container.querySelector(".toast-header strong").textContent = title;
        container.querySelector("[data-inbox-preview]").textContent = preview;
        const link = container.querySelector("[data-inbox-link]");
        link.href = url;
        link.textContent = linkLabel;
        toast.show();
    };

    inbox.listen(
        "InboxMessageReceived",
        (event) => {
            const conversationId = String(event.conversation_id);
            const activeConversation =
                document.querySelector("[data-chat-form]")?.dataset
                    .conversationId;

            if (activeConversation === conversationId) return;

            const messagesLink = document.querySelector(
                "[data-inbox-messages-link]",
            );
            const headerBadge = messagesLink?.querySelector(
                "[data-inbox-unread]",
            );

            if (headerBadge) {
                const unread = Number(headerBadge.textContent) + 1;
                headerBadge.textContent = String(unread);
                headerBadge.hidden = false;
                messagesLink.setAttribute(
                    "aria-label",
                    `Messages, ${unread} unread`,
                );
            }

            const badge = document.querySelector(
                `[data-unread-for="${conversationId}"]`,
            );

            if (badge) {
                const unread = Number(badge.textContent) + 1;
                badge.textContent = String(unread);
                badge.setAttribute("aria-label", `${unread} unread messages`);
                badge.hidden = false;
            }

            notify("New message", event.preview, event.url, "Open conversation");
        },
    );
    inbox.listen("BookingActivity", (event) => {
        notify("Booking update", event.preview, event.url, "View bookings");
    });
}

if (document.readyState === "complete") {
    connectInbox();
} else {
    window.addEventListener("load", connectInbox, { once: true });
}
