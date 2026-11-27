<?php

namespace App\Http\Middleware;

use App\Models\Conversation;
use App\Models\RealEstateUser;
use App\Services\Moderation\ContactPatternDetector;
use App\Services\Moderation\MessageModerationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ModerateChatMessage
{
    public function __construct(
        private readonly MessageModerationService $moderation,
    ) {
    }

    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        $conversation = $request->route('conversation');

        // Ensure the user is authenticated.
        abort_unless($user instanceof RealEstateUser, 403);

        // Ensure the conversation exists.
        abort_unless($conversation instanceof Conversation, 404);

        // Only conversation participants can send messages.
        abort_unless($conversation->hasParticipant($user), 403);

        // Admin support conversations are excluded.
        if ($user->role === 'admin') {
            return $next($request);
        }

        $otherParticipant =
            (int) $conversation->tenant_id === (int) $user->getKey()
                ? $conversation->agent()
                : $conversation->tenant();

        if ($otherParticipant->where('role', 'admin')->exists()) {
            return $next($request);
        }

        // Validate the message before sending it to Ollama.
        $validated = $request->validate([
            'body' => [
                'required',
                'string',
                'max:2000',
            ],

            'reply_to_id' => [
                'nullable',
                'integer',
                Rule::exists('messages', 'id')
                    ->where(
                        'conversation_id',
                        $conversation->getKey()
                    ),
            ],
        ]);

        // Perform moderation.
        $result = $this->moderation->moderate(
            $validated['body']
        );

        // Preserve the original message flow.
        if ($result['status'] === MessageModerationService::ALLOW) {
            return $next($request);
        }

        // Block messages that violate communication rules.
        if ($result['status'] === MessageModerationService::BLOCK) {
            $message = $this->violationMessage($result['reason']);

            return response()->json([
                'code' => 'MESSAGE_MODERATION_BLOCKED',
                'reason' => $result['reason'],
                'message' => $message,
                'errors' => [
                    'body' => [$message],
                ],
            ], 422);
        }

        // Do not send messages when moderation is unavailable.
        $message = __(
            'Message moderation is temporarily unavailable. Please try again.'
        );

        return response()->json([
            'code' => 'MESSAGE_MODERATION_UNAVAILABLE',
            'reason' => MessageModerationService::SERVICE_UNAVAILABLE,
            'message' => $message,
        ], 503);
    }

    private function violationMessage(?string $reason): string
    {
        return match ($reason) {

            ContactPatternDetector::PHONE_NUMBER => __(
                'Sharing phone numbers is not allowed. Please edit your message.'
            ),

            ContactPatternDetector::EMAIL_ADDRESS => __(
                'Sharing email addresses is not allowed. Please edit your message.'
            ),

            ContactPatternDetector::SOCIAL_CONTACT => __(
                'Sharing social media accounts or contact links is not allowed. Please edit your message.'
            ),

            default => __(
                'Communication must remain within the Makan platform. Please edit your message to comply with the communication rules.'
            ),

        };
    }
}