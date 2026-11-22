<div class="makan-assistant" data-makan-assistant>
    <section class="makan-assistant__panel" id="makan-assistant-panel" aria-labelledby="makan-assistant-title" hidden>
        <header class="makan-assistant__header">
            <span class="makan-assistant__avatar"><i class="bi bi-stars" aria-hidden="true"></i></span>
            <h2 id="makan-assistant-title">Makan Assistant</h2>
            <button class="btn btn-sm makan-assistant__close" type="button" data-assistant-close aria-label="Close assistant"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        </header>
        <div class="makan-assistant__messages" role="log" aria-label="Conversation" aria-relevant="additions" data-assistant-messages>
            <p class="makan-assistant__message">Hi! Ask me about finding a home or using Makan.</p>
        </div>
        <p class="makan-assistant__status" role="status" data-assistant-status hidden></p>
        <form class="makan-assistant__form" action="{{ route('assistant.chat') }}" method="post" data-assistant-form>
            @csrf
            <label class="visually-hidden" for="makan-assistant-input">Message Makan Assistant</label>
            <input id="makan-assistant-input" class="form-control" name="message" maxlength="1000" placeholder="Ask a question…" required>
            <button class="btn btn-primary btn-square" type="submit" aria-label="Send message"><i class="bi bi-send" aria-hidden="true"></i></button>
        </form>
    </section>
    <button class="makan-assistant__launcher" type="button" aria-controls="makan-assistant-panel" aria-expanded="false" aria-label="Open Makan Assistant" data-assistant-toggle>
        <i class="bi bi-chat-dots-fill" aria-hidden="true"></i>
    </button>
</div>
