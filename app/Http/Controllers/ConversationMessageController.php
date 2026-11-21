<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\RealEstateUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Events\InboxMessageReceived;
use Illuminate\Validation\Rule;
use App\Events\MessageReactionChanged;
use App\Models\Property;

class ConversationMessageController extends Controller
{
    public function store(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        abort_unless(
            $user instanceof RealEstateUser && $conversation->hasParticipant($user),
            403
        );

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'reply_to_id' => [
                'nullable',
                'integer',
                Rule::exists('messages', 'id')
                    ->where('conversation_id', $conversation->getKey()),
            ],
        ]);
        $message = DB::transaction(function () use ($conversation, $user, $data) {
            $message = $conversation->messages()->make([
                'body' => $data['body'],
            ]);

            $message->sender()->associate($user);
            $message->reply_to_id = $data['reply_to_id'] ?? null;
            $message->save();

            $conversation->last_message_at = $message->created_at;
            $conversation->save();

            return $message;
        });

        rescue(fn() => MessageSent::dispatch($message));
        $recipientId = (int) $user->getKey() === (int) $conversation->tenant_id
            ? (int) $conversation->agent_id
            : (int) $conversation->tenant_id;

        rescue(fn() => InboxMessageReceived::dispatch($message, $recipientId));

        return response()->json($message->chatPayload(), 201);
    }
    public function markRead(Request $request, Conversation $conversation): \Illuminate\Http\Response
    {
        $user = $request->user();

        abort_unless(
            $user instanceof RealEstateUser && $conversation->hasParticipant($user),
            403
        );

        $data = $request->validate([
            'message_id' => ['required', 'integer', 'min:1'],
        ]);

        $conversation->messages()
            ->whereKey($data['message_id'])
            ->where('sender_id', '!=', $user->getKey())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->noContent();
    }
    public function toggleReaction(
        Request $request,
        Conversation $conversation,
        int $messageId
    ): \Illuminate\Http\JsonResponse {
        $user = $request->user();

        abort_unless(
            $user instanceof RealEstateUser && $conversation->hasParticipant($user),
            403
        );

        $data = $request->validate([
            'emoji' => ['required', 'string', 'in:👍,❤️,😂,😮,😢,🙏'],
        ]);

        $userId = (int) $user->getKey();

        $emoji = \Illuminate\Support\Facades\DB::transaction(
            function () use ($conversation, $messageId, $userId, $data) {
                $message = $conversation->messages()
                    ->lockForUpdate()
                    ->findOrFail($messageId);

                $reaction = $message->reactions()
                    ->where('user_id', $userId)
                    ->first();

                if ($reaction?->emoji === $data['emoji']) {
                    $reaction->delete();

                    return null;
                }

                $message->reactions()->updateOrCreate(
                    ['user_id' => $userId],
                    ['emoji' => $data['emoji']]
                );

                return $data['emoji'];
            }

        );
        rescue(fn() => MessageReactionChanged::dispatch(
            (int) $conversation->getKey(),
            $messageId,
            $userId,
            $emoji,
        ));

        return response()->json([
            'message_id' => $messageId,
            'user_id' => $userId,
            'emoji' => $emoji,
        ]);
    }
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user instanceof RealEstateUser, 403);

        [$view, $query] = match ($user->role) {
            'tenant' => ['seeker.chat', $user->tenantConversations()],
            'agent' => ['owner.chat', $user->agentConversations()],
            default => abort(403),
        };

        $conversations = $query
            ->when($user->role === 'agent', fn($query) => $query->whereHas('messages'))
            ->withCount([
                'messages as unread_count' => fn($messages) => $messages
                    ->where('sender_id', '!=', $user->getKey())
                    ->where('is_read', false),
            ])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->get();

        return view($view, [
            'conversations' => $conversations,
            'homes' => [],
        ]);
    }
    public function show(Request $request, Conversation $conversation): View
    {
        $user = $request->user();

        abort_unless(
            $user instanceof RealEstateUser && $conversation->hasParticipant($user),
            403
        );

        $isTenant = (int) $user->getKey() === (int) $conversation->tenant_id;
        $view = $isTenant ? 'seeker.chat' : 'owner.chat';

        $messages = $conversation->messages()
            ->with(['replyTo', 'reactions'])
            ->latest('id')
            ->limit(50)
            ->get()
            ->reverse();

        $lastLoadedMessage = $messages->last();

        if ($lastLoadedMessage !== null) {
            $conversation->messages()
                ->where('id', '<=', $lastLoadedMessage->id)
                ->where('sender_id', '!=', $user->getKey())
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        }

        $query = $isTenant
            ? $user->tenantConversations()
            : $user->agentConversations();
        $conversations = $query
            ->when($user->role === 'agent', fn($query) => $query->whereHas('messages'))
            ->withCount([
                'messages as unread_count' => fn($messages) => $messages
                    ->where('sender_id', '!=', $user->getKey())
                    ->where('is_read', false),
            ])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->get();

        $chatProperty = $conversation->property_id === null
            ? null
            : Property::query()
            ->where('verification_status', 'verified')
            ->whereIn('status', ['available', 'partially_rented'])
            ->find($conversation->property_id, ['id', 'title', 'price']);
        $ownerName = '';

        if ($isTenant) {
            $profile = RealEstateUser::query()
                ->with('agentProfile')
                ->find($conversation->agent_id)
                ?->agentProfile;

            $ownerName = trim(($profile?->first_name ?? '') . ' ' . ($profile?->last_name ?? ''));
        }
        $tenantName = '';

        if (! $isTenant) {
            $profile = RealEstateUser::query()
                ->with('tenantProfile')
                ->find($conversation->tenant_id)
                ?->tenantProfile;

            $tenantName = trim(($profile?->first_name ?? '') . ' ' . ($profile?->last_name ?? ''));
        }
        return view($view, [
            'tenantName' => $tenantName,
            'ownerName' => $ownerName,
            'conversation' => $conversation,
            'chatProperty' => $chatProperty,
            'messages' => $messages,
            'conversations' => $conversations,
            'currentUserId' => (int) $user->getKey(),
            'homes' => [],
        ]);
    }
}
