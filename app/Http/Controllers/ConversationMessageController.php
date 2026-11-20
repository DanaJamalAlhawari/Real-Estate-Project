<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\RealEstateUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

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
        ]);

        $message = DB::transaction(function () use ($conversation, $user, $data) {
            $message = $conversation->messages()->make([
                'body' => $data['body'],
            ]);

            $message->sender()->associate($user);
            $message->save();

            $conversation->last_message_at = $message->created_at;
            $conversation->save();

            return $message;
        });

        rescue(fn() => MessageSent::dispatch($message));

        return response()->json([
            'id' => $message->id,
            'body' => $message->body,
            'sender_id' => $message->sender_id,
        ], 201);
    }
    public function show(Request $request, Conversation $conversation): View
    {
        $user = $request->user();

        abort_unless(
            $user instanceof RealEstateUser && $conversation->hasParticipant($user),
            403
        );

        $view = (int) $user->getKey() === (int) $conversation->tenant_id
            ? 'seeker.chat'
            : 'owner.chat';

        $messages = $conversation->messages()
            ->latest('id')
            ->limit(50)
            ->get()
            ->reverse();

        return view($view, [
            'conversation' => $conversation,
            'messages' => $messages,
            'currentUserId' => (int) $user->getKey(),
            'homes' => [],
        ]);
    }
}
